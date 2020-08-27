<?php

# PHP class
# Split URI
# splits an given URI in
# path and query variables

class URISplit {

    public $hostname;
    public $uri;
    public $path_vars;
    public $query_vars;


    public function __construct()
    {
        $this->hostname = $_SERVER['HTTP_HOST'];
        $this->uri = $_SERVER['REQUEST_URI'];
        $this->uri = str_replace("%20", " ",$this->uri);

        $this->path();
        $this->query();

        return;
    }


    public function path()
    {
        $path_string = explode('?', $this->uri);
        if(isset($path_string[0])){
            $this->path_vars = explode('/', substr($path_string[0], 1));
		}
        if ($this->hostname === 'localhost') {
            $path_index_new = 0;
            $temp_path_vars = array();
            for($path_index_old = 1; $path_index_old < sizeof($this->path_vars); $path_index_old++) {
                if($this->path_vars[$path_index_old]!='') {
                    $temp_path_vars[$path_index_new++] = $this->path_vars[$path_index_old];
                }
            }
            $this->path_vars = $temp_path_vars;
        }
        return;
    }


    public function query()
    {
        $query_string = explode('?', $this->uri);
		    if(sizeof($query_string) > 1){
			  $vars = urldecode($query_string[1]);
              foreach (explode('&', $vars) as $var) {
                $t = explode('=', $var);
                $this->query_vars[$t[0]] = $t[1];
              }
		}
        return;
    }


    public function parse_path()
    {
        $path = array();

        if(isset($_SERVER['REQUEST_URI']))
        {
            $request_path = explode('?', $_SERVER['REQUEST_URI']);

            $path['base'] = rtrim(dirname($_SERVER['SCRIPT_NAME']), '\/');
            $path['call_utf8'] = substr(urldecode($request_path[0]), strlen($path['base']) + 1);
            $path['call'] = utf8_decode($path['call_utf8']);

            if ($path['call'] == basename($_SERVER['PHP_SELF']))
            {
                $path['call'] = '';
            }

            $path['call_parts'] = explode('/', $path['call']);

            $path['query_utf8'] = urldecode($request_path[1]);
            $path['query'] = utf8_decode(urldecode($request_path[1]));
            $vars = explode('&', $path['query']);

            foreach ($vars as $var)
            {
                $t = explode('=', $var);
                $path['query_vars'][$t[0]] = $t[1];
            }
        }

        return $path;
    }

}

?>
