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
define('DOMAIN', 'litwinow.xyz');




# SALT value
define('SALT', 'wevsvirus');

# more defines here
//Debugging
define('DUMMY_FEUERWEHR', array('id' => 1,
	 'name' => 'feuerwerk',
	 'description' => 'testetst',
	 'type' => 'feuerwehr',
	 'contact' => 'feuer@wehr.com',
	 'links' => array('self' => 'litwinow.xyz/config/38000/feuerwehr/feuerwerk/',
				'fields' => array('Einsatzkraefte' => 'litwinow.xyz/config/38000/feuerwehr/feuerwerk/einsatzkraefte/',
								'Autos broom broom' => 'litwinow.xyz/config/38000/feuerwehr/feuerwerk/autos%20broom%20broom'))));

define('DUMMY_FEUERWEHR_FELD1', array('id' => 1,
	 'name' => 'Einsatzkraefte',
	 'max_value' => 5.0,
	 'yellow_value' => 4.0,
	 'red_value' => 3.0,
	 'relational_flag' => true,
	 'links' => array('self' => 'litwinow.xyz/config/38000/feuerwehr/feuerwerk/einsatzkraefte/')));
 
 define('DUMMY_FEUERWEHR_FELD2', array('id' => 2,
		 'name' => 'Autos broom broom',
		 'max_value' => 'null',
		 'yellow_value' => 4.0,
		 'red_value' => 3.0,
		 'relational_flag' => false,
		 'links' => array('self' => 'litwinow.xyz/config/38000/feuerwehr/feuerwerk/autos%20broom%20broom/')));
		 
define('EINSATZKRAEFTE_2020_08_15', array('field_id' => 1,
		 'field_name' => 'einsatkraefte',
		 'user_id' => '1',
		 'username' => 'testus',
		 'value' => '3',
		 'date' => '11-12-2020',
		 'links' => array('self' => 'litwinow.xyz/data/38000/feuerwehr/feuerwerk/einsatzkraefte/2020/12/11/')));

define('EINSATZKRAEFTE_2020_08_16', array('field_id' => 1,
		 'field_name' => 'einsatkraefte',
		 'user_id' => '1',
		 'username' => 'testus',
		 'value' => '3',
		 'date' => '12-12-2020',
		 'links' => array('self' => 'litwinow.xyz/data/38000/feuerwehr/feuerwerk/einsatzkraefte/2020/12/12/')));

?>


