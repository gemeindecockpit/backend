<?php

# PHP class
# Output
# Data outputs in different formats
# like json etc.

class DataOutput {

	public function __construct() 
	{
        return;
    }


	public function output_as_json($data_array)
	{
		$json_string = json_encode($data_array);

		return $json_string;
	}
	//adds a field to the data that will be converted to json. Mainly used for HATEOAS-Links
	public function add_field_to_output_array($field){
		
	}
	//returns a link to the current resource
	public function get_current_self_link(){
		return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	}
}

?>