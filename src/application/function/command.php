<?php

function getBotCommands(): array {
    return [
        'menu' => [ // required command, do not remove
            'aliases' => getTemplate('menu.aliases'),
            'function' => 'botCommand_menu',
            'fromAdminLevel' => 0,
        ],
        'snackbar' => [
            'aliases' => getTemplate('snackbar.aliases'),
            'function' => 'botCommand_snackbar',
            'fromAdminLevel' => 0,
        ],
        'second-page' => [
            'aliases' => getTemplate('second-page.aliases'),
            'function' => 'botCommand_secondPage',
            'fromAdminLevel' => 0,
        ],
        'signup' => [
            'aliases' => getTemplate('signup.aliases'),
            'function' => 'botCommand_signup',
            'fromAdminLevel' => 0,
        ],
    ];
}

function botCommand_menu(): array {
    global $user;

    $message = getTemplate('menu.default', $user->username);
    $keyboard = getKeyboardMenuDefault();
    $media = getTemplate('menu.image');

    return [$message, $keyboard, $media];
}

function botCommand_snackbar(): array {
    return [
        getTemplate('snackbar.default'),
        MESSAGE_ACTION_SNACKBAR,
    ];
}

function botCommand_secondPage(): array {
    $message = getTemplate('second-page.default');
    $keyboard = getKeyboardSecondPageDefault();

    return [$message, $keyboard];
}

function botCommand_signup(mixed $payload, mixed $temp): array {
    global $user;
    $action = $temp->action ?? $payload->action ?? null;

    switch ($action) {
        case 'enter':
            $text = getEnteredText();
            if (!isset($text)) {
                $temp->save();

                return [getTemplate('signup.empty-username')];
            }

            $message = getTemplate('signup.that-username', $text);
            $keyboard = getKeyboardSignupConfirm($text);
            break;
        case 'confirm':
            $username = $payload->username ?? die('ok');
            $user->username = $username;
            saveUser($user);

            $message = getTemplate('signup.success');
            $keyboard = getKeyboardSignupBack();
            break;
        case 'cancel':
            $message = getTemplate('signup.cancel');
            $keyboard = getKeyboardSignupBack();
            break;

        default:
            $temp = new Scenarios(PATH_SCENARIOS, $user->id);
            $temp->__onetime = true;
            $temp->command = 'signup';
            $temp->action = 'enter';
            $temp->save();

            $message = getTemplate('signup.default');
            break;
    }

    return [$message, $keyboard ?? null];
}
