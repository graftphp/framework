<?php

namespace GraftPHP\Framework;

use GraftPHP\Framework\View;

class Framework
{

    public static function Route()
    {
        // work out which controller and method to send this request to
        $path = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : explode('?', $_SERVER['REQUEST_URI'])[0];

        // remove last slash if this isn't the index page
        if ($path != '/') {
            if (substr($path, -1) == '/') {
                $path = substr_replace($path, "", -1);
            }
        }

        // routes to check
        $routes = GRAFT_ROUTES;

        // additional vendor routes, if set
        if (is_array(GRAFT_VENDOR_SETTINGS)) {
            foreach(GRAFT_VENDOR_SETTINGS as $vs) {
                $obj = new $vs;
                $routes = array_merge($obj->routes, $routes);
                unset($obj);
            }
        }

        // check routes for a match with the current request
        foreach ($routes as $route) {
            $pattern = '|^' . str_replace('{}', '(.+)', $route[0]) . '$|';
            preg_match($pattern, $path, $result);

            if (count($result) > 0) {
                array_shift($result);
                $obj = new $route[1];
                call_user_func_array( [$obj, $route[2]], $result );
                break;
            }
        }

        if (!isset($obj)) {
            // we didn't route anywhere!
            Framework::Error404();
        }
    }

    public static function Error404()
    {
        header("HTTP/1.0 404 Not Found");
        View::render(GRAFT_CONFIG['404Template']);
    }

}
