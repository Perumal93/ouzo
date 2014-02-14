<?php
namespace Ouzo;

use Ouzo\Utilities\Files;
use Ouzo\Utilities\Path;

class Bootstrap
{
    public function __construct()
    {
        error_reporting(E_ALL);
        putenv('environment=prod');
    }

    public function addConfig($config)
    {
        Config::registerConfig($config);
        return $this;
    }

    public function runApplication()
    {
        set_exception_handler('\Ouzo\Error::exceptionHandler');
        set_error_handler('\Ouzo\Error::errorHandler');
        register_shutdown_function('\Ouzo\Error::shutdownHandler');

        $loader = new Loader();
        $loader
            ->setIncludePath('application/')
            ->setIncludePath('vendor/letsdrink/ouzo/src/')
            ->setIncludePath('locales/')
            ->register();

        $this->_includeRoutes();

        $controller = new FrontController();
        $controller->init();
    }

    private function _includeRoutes()
    {
        $routesPath = Path::join(ROOT_PATH, 'config', 'routes.php');
        Files::loadIfExists($routesPath);
    }
}