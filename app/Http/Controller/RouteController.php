<?php

use Psr\Container\ContainerInterface;

foreach(glob(__DIR__ . "/../Models/*.php") as $filename) {
    require_once($filename);
}

function assoc_array_to_indexed($assoc_array) {
    $indexed_array = [];
    foreach($assoc_array as $value) {
        $indexed_array[] = $value;
    }
    return $indexed_array;
}

/**
 * Class ResponseCodes provides constants for the return
 * to the route controller class.
 */
class ResponseCodes {
    const OK = 200;
    const NO_MATCH = 901; # no real response code
    const BAD_REQUEST = 400;
    const FORBIDDEN = 403;
    const NOT_FOUND = 404;
    const SERVER_ERROR = 500;
}

class RouteController {
    protected $container;

    // constructor receives container instance
    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    /**
     * $errno should be
     * - 'NOT_FOUND' if a resource is not found
     * - 'FORBIDDEN' if the session user aren't allowed to execute the operation
     * - true if a database error occurred
     * - false if everything is fine
     *
     * @param $response
     * @param $errno
     * @return mixed
     */
    protected function return_response($response, $errno) {
        switch (true) {
            case ($errno === ResponseCodes::NO_MATCH):
                $response->getBody()->write('User in request does not match user in url.');
                return $response->withStatus(ResponseCodes::BAD_REQUEST);
            case ($errno === ResponseCodes::BAD_REQUEST):
                $response->getBody()->write('Request not match required format.');
                return $response->withStatus(ResponseCodes::BAD_REQUEST);
            case ($errno === ResponseCodes::NOT_FOUND):
                return $response->withStatus(ResponseCodes::NOT_FOUND);
            case ($errno === ResponseCodes::FORBIDDEN):
                return $response->withStatus(ResponseCodes::FORBIDDEN);
            case ($errno === ResponseCodes::SERVER_ERROR):
                return $response->withStatus(ResponseCodes::SERVER_ERROR);
            case ($errno == false || $errno === ResponseCodes::OK):
                return $response->withHeader(
                    'Content-Type',
                    'application/json'
                )->withStatus(ResponseCodes::OK);
            case($errno === true):
                return $response->withStatus(ResponseCodes::BAD_REQUEST);
            default:
                $response->getBody()->write('Internal error.');
                return $response->withStatus(ResponseCodes::SERVER_ERROR);
        }

    }

    /**
     * Checks if all keys of key_arr are present in assoc_array.
     * @param $assoc_array
     * @param $key_arr
     * @return false
     */
    protected function assoc_array_keys_exist($assoc_array, $key_arr) {
        foreach ($key_arr as $key) {
            if (!isset($assoc_array[$key]))
                return false;
        }
        return true;
    }

    /**
     * Checks if the array contains only numeric values.
     * @param $array
     * @return bool
     */
    protected function array_values_are_numeric($array) {
        foreach ($array as $value) {
            if (!is_numeric($value))
                return false;
        }
        return true;
    }

    /**
     * Checks if the assoc_array has the correct format.
     *
     * format is a associative array, if assoc_array should contain:
     * - attr1 as numeric value
     * - attr2 as numeric array
     * - attr3 as associative array with keys attr3_1 (numeric) and attr3_2 (numeric array)
     *
     * format looks like:
     * [attr1=>false, attr2=>true, attr3=>[[attr3_1=>false, attr3_2=>true]]]
     * @param $assoc_array
     * @param $format
     * @return bool if the format is correct
     */
    protected function check_correct_format($assoc_array, $format) {
        static $i = 0;
        $i++;
        if (!$this->assoc_array_keys_exist($assoc_array, array_keys($format)))
            return false;
        foreach ($format as $key => $value) {
            if (is_array($value) && $this->array_values_are_numeric(array_keys($value))) {
                foreach ($assoc_array[$key] as $val) {
                    if (!$this->check_correct_format($val, $value[0])) {
                        return false;
                    }
                }
            } else if (is_array($value) && !$this->check_correct_format($assoc_array[$key], $value)) {
                return false;
            } else if ($value === false && !is_numeric($assoc_array[$key])) {
                return false;
            } else if ($value === true && !$this->array_values_are_numeric($assoc_array[$key])) {
                return false;
            } else if ($value === null && !is_string($assoc_array[$key])) {
                return false;
            }
        }
        return true;
    }

   public function home($request, $response, $args) {
       $response->getBody()->write(json_encode(array(
         'data' => $_SERVER['SERVER_NAME'] .'/data',
        'config' => $_SERVER['SERVER_NAME'] . '/config',
         'user' => $_SERVER['SERVER_NAME'] . '/user',
          'login' => $_SERVER['SERVER_NAME'] . '/login',
           'logout' => $_SERVER['SERVER_NAME'] . '/logout')));
       return $response->withHeader('Content-type', 'application/json');
   }

   public static function get_link(...$args) {
	array_walk_recursive($args, \RouteController::class . '::encode_items_url');
	return $_SERVER['SERVER_NAME'] . '/' . implode('/', $args);
   }

   public static function encode_items_url(&$item, $key){
	$item = rawurlencode($item);
   }

   public static function encode_items(&$item, $key){
	  $item = utf8_encode($item);
	}

	public static function assoc_array_to_indexed($assoc_array) {
	    $indexed_array = [];
	    foreach($assoc_array as $value) {
	        $indexed_array[] = $value;
	    }
	    return $indexed_array;
	}
}
