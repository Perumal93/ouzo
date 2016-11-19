<?php
namespace Ouzo\Uri;

use Ouzo\Config;
use Ouzo\Routing\Route;
use Ouzo\Routing\RouteRule;
use Ouzo\Utilities\Arrays;
use Ouzo\Utilities\Strings;

class JsUriHelperGenerator
{
    const INDENT = '    ';

    private $generatedFunctions = '';

    /** @var RouteRule[] */
    public $routeRules;

    public function __construct()
    {
        $this->routeRules = $routes = Route::getRoutes();
        $this->generatedFunctions .= 'function checkParameter(parameter) {
' . self::INDENT . 'if (parameter === null) {
' . self::INDENT . self::INDENT . 'throw new Error("Uri helper: Missing parameters");
' . self::INDENT . '}
}' . "\n\n";
        $this->generateFunctions();
    }

    private function generateFunctions()
    {
        $namesAlreadyGenerated = [];
        foreach ($this->routeRules as $routeRule) {
            $name = $routeRule->getName();
            if (!in_array($name, $namesAlreadyGenerated)) {
                $this->generatedFunctions .= $this->createFunction($routeRule);
            }
            $namesAlreadyGenerated[] = $name;
        }
    }

    private function createFunction(RouteRule $routeRule)
    {
        $url = $this->applicationHttpPath();
        $name = $routeRule->getName();
        $uri = $routeRule->getUri();
        $uriWithVariables = preg_replace('/:(\w+)/', '" + $1 + "', $uri);
        $parameters = $this->prepareParameters($uri);
        $parametersString = implode(', ', $parameters);
        $checkParametersStatement = $this->createCheckParameters($parameters);

        $function = <<<FUNCTION
function $name($parametersString) {
{$checkParametersStatement}return "{$url}{$uriWithVariables}";
}\n\n
FUNCTION;
        return $name ? $function : '';
    }

    function applicationHttpPath()
    {
        $systemPrefix = $this->addEndingSlash(Config::getValue("global", "prefix_system"));
        $applicationPrefix = Config::getValue("global", "prefix_application");
        return $this->addEndingSlash("$systemPrefix$applicationPrefix");
    }

    private function addEndingSlash($path)
    {
        if (Strings::endsWith($path, '/')) {
            return $path;
        }
        return $path . '/';
    }

    private function prepareParameters($uri)
    {
        preg_match_all('#:(\w+)#', $uri, $matches);
        return Arrays::getValue($matches, 1, []);
    }

    private function createCheckParameters($parameters)
    {
        if ($parameters) {
            $checkFunctionParameters = Arrays::map($parameters, function ($element) {
                return self::INDENT . "checkParameter($element);";
            });
            return implode("\n", $checkFunctionParameters) . "\n" . self::INDENT;
        }
        return self::INDENT;
    }

    public function getGeneratedFunctions()
    {
        return trim($this->generatedFunctions) . "\n";
    }

    public function saveToFile($path)
    {
        return file_put_contents($path, $this->getGeneratedFunctions());
    }

    public static function generate()
    {
        return new self();
    }
}
