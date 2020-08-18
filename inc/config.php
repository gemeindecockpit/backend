<?php

# Config file
# for gobal constants

# DEBUGMODE on or off
# values true, false
define('DEBUG_MODE', false);

# Access to MySQL Database
define('DB_HOST', 'localhost');
define('DB_NAME', 'gemeindecockpit');
define('DB_USER', 'alx');
define('DB_USER_PASSWORD', 'testing');
define('NUTS0', 'deutschland');
define('NUTS1', 'niedersachsen');
define('NUTS2', 'braunschweig');
define('NUTS3', 'braunschweig');



# SALT value
define('SALT', 'wevsvirus');

# more defines here
//Debugging
define('DUMMY_FEUERWEHR', array = ('id' => 1, 'name' => 'feuerwerk', 'description' => 'testetst', 'type' => 'feuerwehr', 'contact' => 'feuer@wehr.com'));
?>


