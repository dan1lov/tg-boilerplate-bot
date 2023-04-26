<?php

// generator
function getKeyboardByCallbackData(array $callback_data, int $mode = 0): string {
    $buttons = [];

    foreach ($callback_data as $row => $row_data) {
        foreach ($row_data as $col => $data) {
            $is_link = array_key_exists('link', $data);

            $buttons[$row][$col] = $mode === KEYBOARD_INLINE
                ? getButtonInline(
                    $data['button-label'],
                    data: !$is_link ? "$row-$col" : null,
                    link: $is_link ? $data['link'] : null,
                )
                : getButton($data['button-label']);
        }
    }

    return getKeyboard($buttons, $mode);
}

// menu
function getKeyboardMenuDefault(): string {
    global $user_id;

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

    createCallbackData($user_id, $data);
    return getKeyboardByCallbackData($data, KEYBOARD_INLINE);
}

// second page
function getKeyboardSecondPageDefault(): string {
    global $user_id;

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

    createCallbackData($user_id, $data);
    return getKeyboardByCallbackData($data, KEYBOARD_INLINE);
}

// signup
function getKeyboardSignupConfirm(string $username): string {
    global $user_id;

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

    createCallbackData($user_id, $data);
    return getKeyboardByCallbackData($data, KEYBOARD_INLINE);
}

function getKeyboardSignupBack(): string {
    global $user_id;

    $data = [[
        [
            'button-label' => getTemplate('button.to-start'),
            'command' => 'menu',
        ],
    ]];

    createCallbackData($user_id, $data);
    return getKeyboardByCallbackData($data, KEYBOARD_INLINE);
}
