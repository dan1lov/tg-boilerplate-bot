<?php

# -- set default timezone
date_default_timezone_set('Europe/Moscow');

# -- core
require PATH_LIB . '/Database.php';
require PATH_LIB . '/Request.php';
require PATH_LIB . '/Scenarios.php';
require PATH_LIB . '/Straight.php';
require PATH_LIB . '/SystemException.php';

# -- configs
require PATH_CONFIG . '/database.php';
require PATH_CONFIG . '/main.php';
require PATH_CONFIG . '/routes.php';
require PATH_CONFIG . '/templates.php';

# -- functions
require PATH_FUNCTION . '/callback.php';
require PATH_FUNCTION . '/command.php';
require PATH_FUNCTION . '/keyboard.php';
require PATH_FUNCTION . '/other.php';
require PATH_FUNCTION . '/telegram.php';
require PATH_FUNCTION . '/user.php';

# -- controllers
require_once PATH_CONTROLLER . '/callback.php';
