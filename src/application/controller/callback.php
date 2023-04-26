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

    $GLOBALS['callback_data'] = getCallbackData($GLOBALS['user_id']);
    [$row, $col] = explode('-', $data->callback_query->data);

    if (isset($GLOBALS['callback_data'][$row][$col])) {
        $ual = 0;
        $temp = Scenarios::check(PATH_SCENARIOS, $GLOBALS['user_id'], true);

        $commands = getBotCommands();
        $payload = $GLOBALS['callback_data'][$row][$col];
        $command_id = $temp->command ?? $payload->command ?? 'menu';

        if (isset($commands[$command_id])) {
            $exists = function_exists($commands[$command_id]['function']);
            $deny = $ual < $commands[$command_id]['fromAdminLevel'];

            if (!$exists || $deny) {
                die('ok');
            }

            $message = $commands[$command_id]['function']($payload, $temp);
            processingCallbackQuery($data, generateEventData($message));
            return;
        }
    }

    request('answerCallbackQuery', [
        'callback_query_id' => $data->callback_query->id,
        'text' => getTemplate('default.unknown-command'),
    ]);
}
