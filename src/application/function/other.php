<?php

# -- write
function saveObjectToDatabase(
    object $object,
    string $table,
    string $primary_field,
    array  $skip_fields,
): void {
    if (empty($object) || empty($object->$primary_field)) {
        return;
    }

    $fields = [];
    $params = [];

    $skip_fields = array_merge($skip_fields, ['id', 'changed_at', 'created_at']);
    foreach (exportOrderObjectData($object) as $k => $v) {
        if (in_array($k, $skip_fields)) {
            continue;
        }

        $fields[] = sprintf('%s = ?', $k);
        $params[] = $v;
    }

    if (empty($fields) || empty($params)) {
        return;
    }

    // WARN: not safe
    $fields = implode(', ', $fields);
    Database::execute(
        "UPDATE $table SET $fields, changed_at = ? WHERE $primary_field = ?",
        array_merge($params, [time(), $object->$primary_field]),
    );
}

# -- other
function isAssoc(array $array): bool {
    if ($array === []) {
        return false;
    }

    return array_keys($array) !== range(0, count($array) - 1);
}

function strposArray(string $haystack, array $needle): bool|int {
    foreach ($needle as $value) {
        if (($pos = strpos($haystack, $value)) !== false) {
            return $pos;
        }
    }
    return false;
}

function getTemplate(string $name, mixed ...$values): mixed {
    return dict("template.$name", $values);
}

function asArray($something, bool $unique = true, string $delimiter = ','): array {
    if (empty($something) && $something !== 0) {
        return [];
    }

    if (!is_array($something)) {
        $something = explode($delimiter, $something);
    }

    if ($unique) {
        $something = array_unique($something);
    }

    return $something;
}

function exportOrderObjectData(object $object): array {
    $reflect = new ReflectionClass($object);
    $data = $reflect->getProperty('data');

    return $data->getValue($object);
}

# -- system
function checkBearerToken(): bool {
    throw new SystemException(
        getTemplate('system.error-bearer'),
        ERROR_BEARER_CODE,
    );
}

function getHeader(string $header) {
    return getallheaders()[$header] ?? null;
}

function getRequestParams(): array {
    // TODO: cast values as correct types
    // TODO: validate for xss
    return $_REQUEST;
}

function getRequestParts(?int $index = null): string|array {
    $parts = [];
    $http_request_uri = $_SERVER['REQUEST_URI'];

    [$http_request_uri,] = explode('?', $http_request_uri, 2);
    $segments = explode('/', $http_request_uri);

    foreach ($segments as $key => $segment) {
        if ($segment === '') {
            continue;
        }

        if ($key % 2) {
            $parts[] = $segment;
        }
    }

    return $parts[$index] ?? $parts;
}

function returnError(string $message, int $system_code, int $http_code = 400): void {
    jout([
        'ok' => false,
        'error' => [
            'code' => $system_code,
            'message' => $message,
        ],
    ], $http_code);
}

function returnSuccess(mixed $response, int $http_code = 200): void {
    jout(['ok' => true, 'response' => $response], $http_code);
}

function dbConnect(): void {
    Database::setup(
        sprintf(
            'mysql:host=%s;dbname=%s;charset=utf8',
            dict('main.host'),
            dict('main.database')
        ),
        dict('main.user'),
        dict('main.password'),
    );
}









