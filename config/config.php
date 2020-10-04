<?php

# Config file
# for gobal constants

# DEBUGMODE on or off
# values true, false
define('DEBUG_MODE', false);
define('DEV_MODE', false);
define('CROSS_ORIGIN', false);
$localhost = false;
# Access to MySQL Database
if(!$localhost) {
    define('DB_HOST', 'db01.kadzioch-media-it.de');
    define('DB_NAME', 'gemeindecockpit');
    define('DB_USER', 'gemeindecockpit');
    define('DB_USER_PASSWORD', '6ScY4IbCrqBMcxB8');
} else {
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'gemeindecockpit');
    define('DB_USER', 'root');
    define('DB_USER_PASSWORD', 'root');
}


#location of the folder for the svg icons (no slash at the end)
define('SVG_PATH', '/home/alx/svg');

//in base64
define('DEFAULT_SVG','PD94bWwgdmVyc2lvbj0iMS4wIj8+CjxzdmcgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiBoZWlnaHQ9IjIwMCIgd2lkdGg9IjIwMCIgdmlld0JveD0iLTMwMCAtMzAwIDYwMCA2MDAiPgo8Y2lyY2xlIHN0eWxlPSJzdHJva2U6I0FBQTtzdHJva2Utd2lkdGg6MTA7ZmlsbDojRkZGIiByPSIyOTAiLz4KPHRleHQgc3R5bGU9InRleHQtYW5jaG9yOm1pZGRsZTtmaWxsOiM0NDQ7Zm9udC1zaXplOjI2MHB4O2ZvbnQtZmFtaWx5OnNhbnMtc2VyaWY7Zm9udC13ZWlnaHQ6bm9ybWFsOyIgdHJhbnNmb3JtPSJzY2FsZSguMykiPjx0c3BhbiB5PSItNTAiIHg9IjAiPktFSU4gQklMRDwvdHNwYW4+PHRzcGFuIHk9IjMzMCIgeD0iMCI+VkVSRsOcR0JBUjwvdHNwYW4+CjwvdGV4dD4KPC9zdmc+');
define('MAX_SVG_SIZE', 4096000); //in bytes, the filesize is also limited by the max post size in the php.ini

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
