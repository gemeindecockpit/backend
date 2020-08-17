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

}

?>