<?php

namespace Controller;

/**
 * Class for managing the routing of requests and storing routes used by the application.
 */
class Router
{
    private array $routes = [];

    /**
     * Function for adding routes to the Router, which link requests to the class functions which should process them.
     *
     * @param string $path Regular expression which should match any request uri that will use the route.
     * @param string $method Http method of the request.
     * @param callable|array $callback Array containing a class followed by the method of this class
     * which should be used to process the request.
     * @return void
     */
    public function addRoute(string $path, string $method, callable|array $callback)
    {
        $this->routes[$method][$path] = $callback;
    }

    /**
     * Function for deciding what code should be run based on the request.
     *
     * Uses $routes array to find the correct method to run, in case none match the request,
     * returns empty string with a 404 response code.
     *
     * @param string $path The full request uri.
     * @param string $method Http method of the request.
     * @return string The result of the computation run based on the request.
     */
    public function process(string $path, string $method): string
    {
        $path = explode('?', $path)[0];
        $callback = null;
        foreach ($this->routes[$method] ?? [] as $pattern => $cb) {
            if (preg_match($pattern, $path) == 1) {
                $callback = $this->routes[$method][$pattern] ?? null;
            }
        }

        if ($callback === null) {
            http_response_code(404);
            return "";
        }

        if (is_array($callback)) {
            [$class, $method] = $callback;
            $controller = new $class();
            return $controller->$method();
        }

        return $callback();
    }
}
