<?php

# -- api
function request(string $method, array $parameters): object {
    $bot_token = dict('tg.token.bot');
    $method_url = "https://api.telegram.org/bot$bot_token/$method";
    return Request::makeJson($method_url, $parameters);
}

# -- keyboard
function getKeyboard(array $buttons, int $mode = 0): string {
    $array = [];
    if ($mode & KEYBOARD_REMOVE) {
        $array['remove_keyboard'] = true;
    } elseif ($mode & KEYBOARD_INLINE) {
        $array['inline_keyboard'] = $buttons;
    } else {
        $array['keyboard'] = $buttons;
        $array['resize_keyboard'] = true;
    }

    return json_encode($array);
}

function getButton(string $text): array {
    return ['text' => $text];
}

function getButtonInline(
    string  $label,
    ?string $data = null,
    ?string $link = null,
): array {
    $button = ['text' => $label];

    if ($data !== null && $link === null) {
        $button['callback_data'] = $data;
    }

    if ($link !== null && $data === null) {
        $button['url'] = $link;
    }

    return $button;
}
