<?php

# -- messages
function validateMessageArray(array $message): array {
    if (isAssoc($message)) {
        return $message;
    }

    $message_media = generateMessageMedia($message[2] ?? null);
    $text_field = empty($message_media) ? 'text' : 'caption';

    $validated_message = [
        $text_field => $message[0] ?? getTemplate('default.undefined'),
        'reply_markup' => $message[1] ?? null,
    ];

    if (!empty($message_media)) {
        [$media_field, $media_value] = $message_media;
        $validated_message[$media_field] = $media_value;
    }

    if (!empty($message_media) && is_array($message_media[1])) {
        unset($validated_message[$text_field]);
    }

    return $validated_message;
}

function processingCallbackQuery(object $data, array $event_data): bool {
    if (!isset($event_data['field'])) {
        request('answerCallbackQuery', [
            'callback_query_id' => $data->callback_query->id,
        ]);

        return editMessage($data, $event_data);
    }

    return request('answerCallbackQuery', [
        'callback_query_id' => $data->callback_query->id,
        $event_data['field'] => $event_data['value'],
    ])->ok;
}

function generateEventData(array $message): array {
    $available = [
        MESSAGE_ACTION_SNACKBAR => 'text',
        MESSAGE_ACTION_LINK => 'url',
    ];

    return array_key_exists($message[1] ?? null, $available)
        ? ['field' => $available[$message[1]], 'value' => $message[0]]
        : $message;
}

function generateMessageMedia(mixed $media): array {
    if (empty($media)) {
        return [];
    }

    [$field, $value] = ['photo', $media];
    if (is_array($media)) {
        $field = 'media';

        $value = [];
        foreach ($media as $item) {
            $value[] = [
                'type' => 'photo',
                'media' => $item,
            ];
        }

        $value = json_encode($value);
    }

    return [$field, $value];
}

function sendMessage(int $chat_id, array $message): object {
    $validated = validateMessageArray($message);

    return request(
        match (true) {
            isset($validated['photo']) => 'sendPhoto',
            isset($validated['media']) => 'sendMediaGroup',
            default => 'sendMessage',
        },
        $validated + [
            'chat_id' => $chat_id,
            'parse_mode' => 'markdown',
            'disable_web_page_preview' => true,
        ],
    );
}

function editMessage(object $data, array $message): bool {
    $data_message = $data->callback_query->message ?? $data->message;
    if (isset($data_message->media)) {
        return false;
    }

    $chat_id = getCurrentChatId();
    $message_id = getCurrentMessageId();
    $validated = validateMessageArray($message);

    // if there are no pictures between the messages
    $isset_data_photo = isset($data_message->photo);
    $isset_validated_photo = isset($validated['photo']);

    $first_case = $isset_data_photo && !$isset_validated_photo;
    $second_case = !$isset_data_photo && $isset_validated_photo;

    switch (true) {
        case $first_case || $second_case:
            request('deleteMessage', [
                'chat_id' => $chat_id,
                'message_id' => $message_id,
            ]);

            return sendMessage($chat_id, $message)->ok;
        case $isset_data_photo && $isset_validated_photo:
            $request = request('editMessageMedia', [
                'chat_id' => $chat_id,
                'message_id' => $message_id,
                'media' => json_encode([
                    'type' => 'photo',
                    'media' => $validated['photo'],
                    'caption' => $validated['caption'],
                ]),
                'reply_markup' => $validated['reply_markup'],
            ]);
            break;
        default:
            $parameters = $validated + compact('chat_id', 'message_id');
            $request = request('editMessageText', $parameters);
            break;
    }

    if ($request->ok === false) {
        $request = sendMessage($chat_id, $validated);
    }

    return $request->ok;
}

# -- callback data
function createCallbackData(int $user_id, mixed $data): void {
    if (empty($data)) {
        return;
    }

    $filepath = getCallbackDataPath($user_id);
    $encoded_data = json_encode($data, JSON_UNESCAPED_UNICODE);
    file_put_contents($filepath, $encoded_data);
}

function getCallbackData(int $user_id): array {
    $filepath = getCallbackDataPath($user_id);
    if (file_exists($filepath)) {
        $data = json_decode(file_get_contents($filepath));
        unlink($filepath);
    } else {
        $data = [];
    }

    return $data;
}

function getCallbackDataPath(int $user_id): string {
    $directory = mb_substr($user_id, 0, 3);
    $path = PATH_DATA . DIRECTORY_SEPARATOR . $directory;
    if (!file_exists($path)) {
        mkdir($path, permissions: 0755, recursive: true);
    }

    return $path . DIRECTORY_SEPARATOR . "$user_id.json";
}

# -- different fields from global data
function getCurrentUserId(): int {
    global $data;

    $object = $data->message ?? $data->callback_query;
    return $object->from->id ?? 0;
}

function getCurrentChatId(): int {
    global $data;

    $message = $data->message ?? $data->callback_query->message;
    return $message->chat->id ?? 0;
}

function getCurrentMessageId(): int {
    global $data;

    $message = $data->message ?? $data->callback_query->message;
    return $message->message_id ?? 0;
}
