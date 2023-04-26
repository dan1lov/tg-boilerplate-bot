<?php

/* -- framework -- */
const ROOT_URL = "http-root-directory";

define("PATH_SYSTEM", realpath(__DIR__ . '/../../'));
const PATH_APP = PATH_SYSTEM . '/application';
const PATH_DATA = PATH_SYSTEM . '/callback_data';
const PATH_LIB = PATH_SYSTEM . '/libraries';
const PATH_TEMP = PATH_SYSTEM . '/temporary';

const PATH_CONFIG = PATH_APP . '/config';
const PATH_CONTROLLER = PATH_APP . '/controller';
const PATH_FUNCTION = PATH_APP . '/function';
const PATH_SCRIPT = PATH_APP . '/script';

const PATH_SCENARIOS = PATH_TEMP . '/scenarios';

/* -- keyboard -- */
const KEYBOARD_REMOVE = 1 << 0;
const KEYBOARD_INLINE = 1 << 1;

/* -- message actions -- */
const MESSAGE_ACTION_SNACKBAR = 'show_snackbar';
const MESSAGE_ACTION_LINK = 'open_link';

/* -- error-codes -- */
const ERROR_INTERNAL_CODE = 1;
const ERROR_BEARER_CODE = 2;
const ERROR_ROUTE_CODE = 3;
