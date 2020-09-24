<?php

# Config file
# for gobal constants

# DEBUGMODE on or off
# values true, false
define('DEBUG_MODE', false);

# Access to MySQL Database

define('DB_HOST', 'db01.kadzioch-media-it.de');
define('DB_NAME', 'gemeindecockpit');
define('DB_USER', 'gemeindecockpit');
define('DB_USER_PASSWORD', '6ScY4IbCrqBMcxB8');

/*
define('DB_HOST', 'localhost');
define('DB_NAME', 'gemeindecockpit');
define('DB_USER', 'root');
define('DB_USER_PASSWORD', 'root');
*/
# SALT value
define('SALT', 'wevsvirus');

define('NUTS_0', '/{nuts0}');
define('NUTS_01', '/{nuts0}/{nuts1}');
define('NUTS_012', '/{nuts0}/{nuts1}/{nuts2}');
define('NUTS_FULL', '/{nuts0}/{nuts1}/{nuts2}/{nuts3}');
define('ORG_TYPE', '/{org_type}');
define('ORG_NAME', '/{org_name}');
define('ORG_FULL_LINK', NUTS_FULL . ORG_TYPE . ORG_NAME);
define('FIELD_NAME', '/{field_name}');
define('YEAR', '/{year:[1,2,3][0-9][0-9][0-9]}');
define('MONTH', '/{month:[0-1][0-9]}');
define('DAY', '/{day:[0-3][0-9]}');
define('DATE_FULL', YEAR . MONTH . DAY);

?>
