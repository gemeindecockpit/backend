<?php

# Config file
# for gobal constants

# DEBUGMODE on or off
# values true, false
define('DEBUG_MODE', false);

# Access to MySQL Database
define('DB_HOST', 'localhost');
define('DB_NAME', 'wevsvirus');
define('DB_USER', 'alx');
define('DB_USER_PASSWORD', 'Testing123,');


# SALT value
define('SALT', 'wevsvirus');

# deeze NUTS defines
define('NUTS0', 'deutschland');
define('NUTS1', 'niedersachsen');
define('NUTS2', 'braunschweig');
define('NUTS3', 'braunschweig');
define('NUTS_FULL', NUTS0 . '/' . NUTS1 . '/'. NUTS2 . '/'. NUTS3);
define('PLZ', '38300');

# more defines here


//Debugging
//#TODO: remove debugging gedöns
define('DUMMY_FEUERWEHR', array('id' => 1,
	 'name' => 'feuerwerk',
	 'description' => 'testetst',
	 'type' => 'feuerwehr',
	 'contact' => 'feuer@wehr.com',
	 'links' => array('self' => 'litwinow.xyz/config/38000/feuerwehr/feuerwerk/',
				'fields' => array('Einsatzkraefte' => 'litwinow.xyz/config/38000/feuerwehr/feuerwerk/einsatzkraefte/',
								'Autos broom broom' => 'litwinow.xyz/config/38000/feuerwehr/feuerwerk/autos%20broom%20broom'))));

define('DUMMY_FEUERWEHR2', array('id' => 2,
	 'name' => 'Wir machen Nass',
	 'description' => 'heh',
	 'type' => 'feuerwehr',
	 'contact' => 'feuer@nass.com',
	 'links' => array('self' => 'litwinow.xyz/config/38000/feuerwehr/wir%20machen%20nass/')));

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

define('ALLEN_ORGANISATION', array(
  'links' => array(
      'self' => 'litwinow.xyz/config/',
      '38300' => array(
        'feuerwehr' => array(
          'feuerwerk' => 'litwinow.xyz/config/38000/feuerwehr/feuerwerk/',
          'wir machen nass' => 'litwinow.xyz/config/38000/feuerwehr/wir%20machen%20nass/'
        )
      )
    )
  )
);

?>
