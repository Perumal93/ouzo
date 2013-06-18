<?php
namespace Thulium;

use Thulium\Utilities\Strings;

class Uri
{
    private $_pathProvider;

    public function __construct($pathProvider = null)
    {
        $this->_pathProvider = $pathProvider == null ? new \Thulium\Uri\PathProvider() : $pathProvider;
    }

    public function getParams()
    {
        $path = $this->_pathProvider->getPath();
        $pathElements = $this->_parsePath($path, 3);
        return $this->_splitParamsKeyValueMap($pathElements);
    }

    private function _splitParamsKeyValueMap($pathElements)
    {
        $paramsArray = array();

        if (!empty($pathElements[2])) {
            $paramsParts = explode('/', $pathElements[2]);

            $k = 0;
            for ($i = 0; $i < (int)floor((count($paramsParts) / 2)); $i++) {
                $tmpKey = $paramsParts[$k];
                $tmpValue = $paramsParts[$k + 1];

                if (!empty($tmpValue)) {
                    $paramsArray[$tmpKey] = $tmpValue;
                }

                $k = $k + 2;
            }
            return $paramsArray;
        }
        return $paramsArray;
    }

    public function getParam($param)
    {
        $params = $this->getParams();
        return isset($params[$param]) ? $params[$param] : null;
    }

    public function getRawController()
    {
        $path = $this->_pathProvider->getPath();
        $pathElements = $this->_parsePath($path);
        return (isset($pathElements[0]) ? $pathElements[0] : null);
    }

    public function getController()
    {
        $rawController = $this->getRawController();
        return isset($rawController) ? Strings::underscoreToCamelCase($rawController) : null;
    }


    public function getAction()
    {
        $path = $this->_pathProvider->getPath();
        $pathElements = $this->_parsePath($path);

        return isset($pathElements[1]) ? $pathElements[1] : null;
    }

    private function _parsePath($path = null, $limit = null)
    {
        $parsedPath = null;
        $prefixSystem = Config::load()->getConfig('global');

        if ($path != null) {
            $pathWithoutPrefix = str_replace($prefixSystem['prefix_system'], '', $path);
            $parsedPath = preg_split('#/#', $pathWithoutPrefix, $limit, PREG_SPLIT_NO_EMPTY);
        }

        return $parsedPath;
    }
}