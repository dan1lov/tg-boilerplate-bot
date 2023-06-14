<?php

# -- system
function __method_callback(): void {
    $data = json_decode(file_get_contents('php://input'));
    $GLOBALS['data'] = $data;

    match (true) {
        property_exists($data, 'message') => __update_message($data),
        property_exists($data, 'callback_query') => __update_callback($data),
        default => null,
    };

    die('ok');
}

# -- update type
function __update_message(object $data): void {
    $GLOBALS['user_id'] = getCurrentUserId();
    $GLOBALS['user'] = getUserOrCreate($GLOBALS['user_id']);

    $message_lower = mb_strtolower($data->message->text);

    $ual = 0;
    $temp = Scenarios::check(PATH_SCENARIOS, $GLOBALS['user_id'], true);

    $message = null;
    $commands = getBotCommands();
    foreach ($commands as $key => $command) {
        $exists = function_exists($command['function']);

        $deny = $ual < $command['fromAdminLevel'];
        $strict = $temp !== false && $temp->command !== $key;

        $word_trigger = strposArray($message_lower, $command['aliases']) !== 0;
        $not_this = $temp === false && $word_trigger;

        if (!$exists || $deny || $strict || $not_this) {
            continue;
        }

        $message = $command['function'](null, $temp);
        break;
    }

    // default command
    if (empty($message)) {
        $message = botCommand_menu();
    }

    // заменить на chat_id здесь
    sendMessage(getCurrentChatId(), $message);
}

function __update_callback(object $data): void {
    $GLOBALS['user_id'] = getCurrentUserId();
    $GLOBALS['user'] = getUserOrCreate($GLOBALS['user_id']);

    $received_callback = $data->callback_query->data;
    $decoded_data = json_decode($received_callback);


    switch (true) {
        case json_last_error() === JSON_ERROR_NONE:
            $payload = (object)[
                'command' => $decoded_data[0] ?? null,
                'action' => $decoded_data[1] ?? null,
                'related_id' => $decoded_data[2] ?? null,
            ];

            $is_correct = true;
            break;
        default:
            [$row, $col] = explode('-', $data->callback_query->data);
            $GLOBALS['callback_data'] = getCallbackData($GLOBALS['user_id']);

            $payload = $GLOBALS['callback_data'][$row][$col] ?? null;
            $is_correct = isset($GLOBALS['callback_data'][$row][$col]);
            break;
    }

    if ($is_correct) {
        $ual = $GLOBALS['user']->ual;
        $temp = Scenarios::check(PATH_SCENARIOS, $GLOBALS['user_id'], true);

        $commands = getBotCommands();
        $command_id = $temp->command ?? $payload->command ?? 'menu';

        $command = $commands[$command_id] ?? null;
        if (isset($command)) {
            $exists = function_exists($command['function']);
            $deny = $ual < $command['fromAdminLevel'];

            if (!$exists || $deny) {
                die('ok');
            }

            $message = $command['function']($payload, $temp);
            processingCallbackQuery($data, generateEventData($message));
            return;
        }
    }

    request('answerCallbackQuery', [
        'callback_query_id' => $data->callback_query->id,
        'text' => getTemplate('default.unknown-command'),
    ]);
}
