<?php

# PHP class
# Output
# Data outputs in different formats
# like json etc.

class DataOutput {
	private $links_array;
	
	public function __construct() 
	{
		$links_array = [];
		$this->add_keyvalue_to_links_array('self', $this->get_current_self_link());
        return;
    }


	public function output_as_json($data_array)
	{
		$data_array['links'] = $this->$links_array;
		$json_string = json_encode($data_array);
		return $json_string;
	}
	//adds a field to the data that will be converted to json. Mainly used for HATEOAS-Links
	public function add_keyvalue_to_links_array($key, $value){
		$links_array[$key] = $value;
	}
	//returns a link to the current resource
	//TODO look into alternatives for http_host and request_uri. The client can set HTTP_HOST and REQUEST_URI to any arbitrary value it wants.
	//TODO check if params are also shown
	public function get_current_self_link(){
		return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
	}
}

?>