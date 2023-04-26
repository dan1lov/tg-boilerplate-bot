<?php

# -- write
function createUser(int $user_id, string $username = null): bool|object {
    Database::execute(
        <<<SQL
        INSERT IGNORE INTO users
            (id, username, changed_at, created_at)
        VALUES (:id, :username, :unix, :unix)
        SQL,
        ['id' => $user_id, 'username' => $username, 'unix' => time()],
    );

    return getUser($user_id);
}

function saveUser(object $user): void {
    $skip_fields = ['id', 'changed_at', 'created_at'];
    saveObjectToDatabase($user, 'users', 'id', $skip_fields);
}

# -- get
function getUserOrCreate(int $user_id): bool|object {
    // TODO: introduce `last_active`
    return getUser($user_id) ?: createUser($user_id);
}

function getUser(int $user_id): array|bool|object {
    $user = Database::getRow('SELECT * FROM users WHERE id = ?', [$user_id]);
    if (empty($user)) {
        return false;
    }

    // *optional* special actions on the object
    $user['username'] ??= getTemplate('part.default-username');

    return new Scenarios(PATH_SCENARIOS, "userObject-$user_id", $user);
}

function getUsers(mixed $user_ids): array {
    $user_ids = asArray($user_ids);
    if (empty($user_ids)) {
        return [];
    }

    $users = [];
    foreach ($user_ids as $user_id) {
        $user = getUser($user_id);
        if (empty($user)) {
            continue;
        }

        $users[] = $user;
    }

    return $users;
}
