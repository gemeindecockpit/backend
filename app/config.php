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

# SALT value
define('SALT', 'wevsvirus');

# deeze NUTS defines
define('NUTS0', 'deutschland');
define('NUTS1', 'niedersachsen');
define('NUTS2', 'braunschweig');
define('NUTS3', 'braunschweig');
define('NUTS_FULL', NUTS0 . '/' . NUTS1 . '/'. NUTS2 . '/'. NUTS3);
define('PLZ', '38300');

?>
