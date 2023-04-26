<?php die('ok');

require realpath(__DIR__ . '/../application/config/constants.php');
require PATH_SCRIPT . '/autoload.php';

$request = request('setWebhook', [
    'allowed_updates' => ['message', 'callback_query'],
    'secret_token' => dict('tg.token.secret'),
    'url' => ROOT_URL . '/callback',
]);

echo json_encode($request);
