<?php

# -- generator
function getKeyboardByCallbackData(array $callback_data, int $mode = 0): string {
    $buttons = [];

    foreach ($callback_data as $row => $row_data) {
        foreach ($row_data as $col => $data) {
            $is_link = array_key_exists('link', $data);

            $button_label = array_shift($data); // always before `raw_allowed`
            $raw_allowed = $mode & KEYBOARD_RAW && count($data) <= 3;
            $raw_data = json_encode(array_values($data));

            $inline_data = match (true) {
                $mode & KEYBOARD_RAW && $raw_allowed => $raw_data,
                default => "$row-$col",
            };

            $buttons[$row][$col] = $mode & KEYBOARD_INLINE
                ? getButtonInline(
                    $button_label,
                    data: !$is_link ? $inline_data : null,
                    link: $is_link ? $data['link'] : null,
                )
                : getButton($data['button-label']);
        }
    }

    return getKeyboard($buttons, $mode);
}

# -- menu
function getKeyboardMenuDefault(): string {
    $data = [[
        [
            'button-label' => getTemplate('button.snackbar'),
            'command' => 'snackbar',
        ],
        [
            'button-label' => getTemplate('button.next-page'),
            'command' => 'second-page',
        ],
    ], [
        [
            'button-label' => getTemplate('button.signup'),
            'command' => 'signup',
        ],
    ]];

    createCallbackData($GLOBALS['user_id'], $data);
    return getKeyboardByCallbackData($data, KEYBOARD_INLINE);
}

# -- second page
function getKeyboardSecondPageDefault(): string {
    $data = [[
        [
            'button-label' => getTemplate('button.open-link'),
            'link' => getTemplate('part.link-vk'),
        ],
    ], [
        [
            'button-label' => getTemplate('button.back'),
            'command' => 'menu'
        ],
    ]];

    createCallbackData($GLOBALS['user_id'], $data);
    return getKeyboardByCallbackData($data, KEYBOARD_INLINE | KEYBOARD_RAW);
}

# -- signup
function getKeyboardSignupConfirm(string $username): string {
    $data = [[
        [
            'button-label' => getTemplate('button.yes'),
            'command' => 'signup',
            'action' => 'confirm',
            'username' => $username,
        ],
        [
            'button-label' => getTemplate('button.another'),
            'command' => 'signup',
            'action' => null,
        ],
    ], [
        [
            'button-label' => getTemplate('button.cancel'),
            'command' => 'signup',
            'action' => 'cancel',
        ],
    ]];

    createCallbackData($GLOBALS['user_id'], $data);
    return getKeyboardByCallbackData($data, KEYBOARD_INLINE);
}

function getKeyboardSignupBack(): string {
    $data = [[
        [
            'button-label' => getTemplate('button.to-start'),
            'command' => 'menu',
        ],
    ]];

    createCallbackData($GLOBALS['user_id'], $data);
    return getKeyboardByCallbackData($data, KEYBOARD_INLINE);
}
