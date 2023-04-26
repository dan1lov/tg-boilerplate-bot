<?php

# -- require needed files
require PATH_SCRIPT . '/autoload.php';

try {
    # -- start something
    dbConnect();
    if (getRequestParts(0) !== 'callback') {
        checkBearerToken();
    }

    # -- handle requests
    $_POST['_tunnel'] = 'method';
    if (!fmap(dict('routes'), '_')) {
        throw new SystemException(
            getTemplate('system.error-route'),
            ERROR_ROUTE_CODE,
        );
    }
} catch (SystemException $e) {
    returnError($e->getMessage(), $e->getCode());
} catch (Throwable $e) {
    // TODO: trigger error to logs
    returnError(getTemplate('system.error-internal'), ERROR_INTERNAL_CODE, 500);
}
