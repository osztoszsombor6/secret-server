<?php

namespace App;

use Controller\Router;
use Controller\SecretController;

/**
 * Class for setting up the application and starting the processing of a request.
 */
class App
{
    private Router $router;
    public function __construct()
    {
        $this->setupApplication();

        $this->router = $this->setupRoutes();
    }
    /**
     * Initialization of the application.
     * @return void
     */
    private function setupApplication()
    {
        date_default_timezone_set('Europe/Budapest');
        spl_autoload_register(array('App\App','myPsr4Autoloader'));
        error_reporting(E_ERROR | E_PARSE);
    }

    /**
     * Method for creating a Router and setting the routes used by the application.
     * @return Router The created Router object.
     */
    private function setupRoutes()
    {
        $router = new Router();
        $router->addRoute('/^\/v1\/secret$/', "POST", [SecretController::class, 'processAddSecretRequest']);
        $router->addRoute('/^\/v1\/secret\/[a-z0-9-]+$/', "GET", [SecretController::class, 'processGetSecretRequest']);
        return $router;
    }

    /**
     * Custom autoloader which generates require statements for used classes.
     *
     * @param string $class The fully-qualified class name.
     *
     * @return void
     */
    public static function myPsr4Autoloader($class)
    {
        $class_path = str_replace('\\', '/', $class);
        $file =  __DIR__ . '/' . $class_path . '.php';
        if (file_exists($file)) {
            require $file;
        }
    }

    /**
     * Function which should be used to process all requests to the secret server.
     * @return void
     */
    public function processRequest()
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        $method = $_SERVER['REQUEST_METHOD'];
        $result = $this->router->process($uri, $method);
        echo $result;
    }
}
