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
define('DOMAIN', 'litwinow.xyz' );

define('URI_CONFIG', DOMAIN . '/config' );

define('URI_WF_PLZ', URI_CONFIG . '/38300' );

define('URI_WF_FEUERWEHREN', URI_WF_PLZ . '/feuerwehr' );

define('URI_WF_FEUERWEHR', URI_WF_FEUERWEHREN . '/WF%20Feuerwehr' );
define('URI_FREIWILLIGE_FEUERWEHR', URI_WF_FEUERWEHREN . '/Freiwillige%20Feuerwehr' );

define('URI_EINSATZKRAEFTE', URI_WF_FEUERWEHR . '/einsatzkraefte' );
define('URI_FAHRZEUGE', URI_WF_FEUERWEHR . '/fahrzeuge' );





# SALT value
define('SALT', 'wevsvirus');

# more defines here
//Debugging

/*
Template for JSON define:

define('NAME', array(
		'id' =>,
		'links' => array(
			'self' =>,
			'next' => array(

			)
		)
	)
);

*/

//All organisations
define('ALLEN_ORGANISATION', array(
  'links' => array(
      'self' => URI_CONFIG,
      'PLZ' => array(
        '38300' => URI_WF_PLZ
      )
    )
  )
);

// All Organisations in a PLZ
define('PLZ_38300', array(
		'links' => array(
			'self' => URI_WF_PLZ,
			'next' => array(
				'feuerwehr' => URI_WF_FEUERWEHREN
			)
		)
	)
);


// All Organisations of a specific type (in a certain plz)
define('WF_FEUERWEHREN', array(
		'links' => array(
			'self' => URI_WF_FEUERWEHREN,
			'next' => array(
				'WF Feuerwehr' => URI_WF_FEUERWEHR,
				'Freiwillige Feuerwehr' => URI_FREIWILLIGE_FEUERWEHR
			)
		)
	)
);


// confic of the specific organisations
define('WF_FEUERWEHR', array(
	 'id' => 1,
	 'name' => 'WF Feuerwehr',
	 'description' => 'testetst',
	 'type' => 'feuerwehr',
	 'contact' => 'feuer@wehr.com',
	 'links' => array(
		 'self' => URI_WF_FEUERWEHR,
			'fields' => array(
				'Einsatzkraefte' => URI_EINSATZKRAEFTE,
				'Fahrzeuge' => URI_FAHRZEUGE
			)
		)
	)
);

define('FREIWILLIGE_FEUERWEHR', array(
	 'id' => 2,
	 'name' => 'Freiwillige Feuerwehr',
	 'description' => 'heh',
	 'type' => 'feuerwehr',
	 'contact' => 'feuer@nass.com',
	 'links' => array(
		 'self' => URI_FREIWILLIGE_FEUERWEHR
 		)
	)
);


// config of the fields
define('WF_FEUERWEHR_FELD1', array(
	 'id' => 1,
	 'name' => 'Einsatzkraefte',
	 'max_value' => 5.0,
	 'yellow_value' => 4.0,
	 'red_value' => 3.0,
	 'relational_flag' => true,
	 'links' => array(
		 'self' => URI_EINSATZKRAEFTE
 		)
	)
);

 define('WF_FEUERWEHR_FELD2', array(
   'id' => 2,
	 'name' => 'Autos broom broom',
	 'max_value' => 'null',
	 'yellow_value' => 4.0,
	 'red_value' => 3.0,
	 'relational_flag' => false,
	 'links' => array(
		 'self' => URI_FAHRZEUGE
 		)
 	)
);


// the actual data
define('EINSATZKRAEFTE_2020_08_15', array(
	 'field_id' => 1,
	 'field_name' => 'einsatkraefte',
	 'user_id' => '1',
	 'username' => 'testus',
	 'value' => '3',
	 'date' => '11-12-2020',
	 'links' => array(
		 'self' => URI_EINSATZKRAEFTE . '/2020/08/15'
		)
 	)
);

define('EINSATZKRAEFTE_2020_08_16', array(
	 'field_id' => 1,
	 'field_name' => 'einsatkraefte',
	 'user_id' => '1',
	 'username' => 'testus',
	 'value' => '3',
	 'date' => '12-12-2020',
	 'links' => array(
		 'self' => URI_EINSATZKRAEFTE . '/2020/08/16'
	 )
 )
);



?>
