<?php
namespace Thulium;

use Exception;
use Thulium\Db\Stats;

class FrontController
{
    public static $requestId;
    public static $userId;

    private $_uri;
    private $_uriAction;
    private $_defaults;
    private $_currentController;

    public $redirectHandler;
    public $sessionInitializer;
    public $downloadHandler;

    public function __construct()
    {
        self::$requestId = uniqid();

        $this->_uri = new Uri();
        $this->_uriAction = $this->_uri->getAction();
        $this->_defaults = Config::load()->getConfig('global');

        $this->redirectHandler = new RedirectHandler();
        $this->sessionInitializer = new SessionInitializer();
        $this->downloadHandler = new DownloadHandler();
        $this->controllerResolver = new ControllerResolver();
    }

    public function init()
    {
        if ($this->_isNoAction()) {
            $this->_redirectToIndex();
        } else {
            $this->_currentController = $this->controllerResolver->getCurrentController();

            $this->sessionInitializer->startSession();

            $this->_logRequest();

            $this->_startOutputBuffer();

            $this->_invokeInit();
            $this->_invokeBeforeMethods();
            $this->_invokeAction();
            $this->_invokeAfterMethods();

            $this->_doActionOnResponse();
        }
    }

    public function getCurrentController()
    {
        return $this->_currentController;
    }

    private function _isNoAction()
    {
        return !$this->_uriAction;
    }

    private function _redirectToIndex()
    {
        $this->_redirect('/' . $this->_uri->getRawController() . '/index');
    }

    private function _redirect($url)
    {
        $prefixSystem = Config::load()->getConfig('global');
        $url = $prefixSystem['prefix_system'] . $url;
        $this->redirectHandler->redirect($url);
    }

    private function _getCurrentAction()
    {
        return $this->_uriAction ? $this->_uriAction : $this->_defaults['action'];
    }

    private function _invokeInit()
    {
        if (method_exists($this->_currentController, 'init')) {
            $this->_currentController->init();
        }
    }

    private function _invokeBeforeMethods()
    {
        foreach ($this->_currentController->before as $method) {
            $this->_currentController->$method();
        }
    }

    private function _invokeAfterMethods()
    {
        foreach ($this->_currentController->after as $method) {
            $this->_currentController->$method();
        }
    }

    private function _invokeAction()
    {
        $currentAction = $this->_getCurrentAction();
        if (method_exists($this->_currentController, $currentAction)) {
            $this->_currentController->$currentAction();
        } else {
            throw new FrontControllerException('No action [' . $currentAction . '] defined in controller [' . get_class($this->_currentController) . '].');
        }
    }

    private function _doActionOnResponse()
    {
        $controller = $this->_currentController;
        switch ($controller->getStatusResponse()) {
            case 'show':
                $controller->display();
                $this->_showOutputBuffer();
                break;
            case 'redirect':
                $this->_redirect($controller->getRedirectLocation());
                break;
            case 'redirectOld':
                $this->redirectHandler->redirect($controller->getRedirectLocation());
                break;
            case 'file':
                $this->downloadHandler->downloadFile($controller->getFileData());
                break;
        }
    }

    private function _startOutputBuffer()
    {
        ob_start();
    }

    private function _showOutputBuffer()
    {
        $page = ob_get_contents();
        ob_end_clean();
        echo $page;
    }

    private function _logRequest()
    {
        self::$userId = isset($_SESSION['id_user_ses']) ? $_SESSION['id_user_ses'] : null;
        Logger::getPanelLogger()
            ->addInfo('[Request:/' . $this->_uri->getRawController() . '/' . $this->_uriAction . ']', array_merge($_POST, $_GET, $this->_uri->getParams()));
    }

}

class FrontControllerException extends \Exception
{
}