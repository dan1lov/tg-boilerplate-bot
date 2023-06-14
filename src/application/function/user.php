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

function saveUser(object $user, array $skip_fields = []): void {
    saveObjectToDatabase($user, 'users', 'id', $skip_fields);
}

# -- get
function getUserOrCreate(int $user_id): bool|object {
    return getUser($user_id) ?: createUser($user_id);
}

function getUser(int $user_id): array|bool|object {
    $user = Database::getRow('SELECT * FROM users WHERE id = ?', [$user_id]);
    if (empty($user)) {
        return false;
    }

    // *optional* special actions on the object
    $user['username'] ??= getTemplate('part.default-username');

    return new Scenarios(PATH_SCENARIOS, "UserObject-$user_id", $user);
}

function getUsers(mixed $user_ids): array {
    $users = [];
    foreach (asArray($user_ids) as $user_id) {
        $user = getUser($user_id);
        if (empty($user)) {
            continue;
        }

        $users[] = $user;
    }

    return $users;
}
