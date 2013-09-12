<?php

namespace Ouzo;


class SimpleTestController extends Controller
{

}

class ControllerResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldResolveAction()
    {
        //given
        $controllerResolver = new ControllerResolver('\\Ouzo\\');

        $config = Config::load()->getConfig('global');
        $_SERVER['REQUEST_URI'] = "{$config['prefix_system']}/simple_test/action1";

        //when
        $currentController = $controllerResolver->getCurrentController();

        //then
        $this->assertEquals('action1', $currentController->currentAction);
    }

    /**
     * @test
     */
    public function shouldUseDefaultActionIfNoActionInUrl()
    {
        //given
        $controllerResolver = new ControllerResolver('\\Ouzo\\');

        $config = Config::load()->getConfig('global');
        $_SERVER['REQUEST_URI'] = "{$config['prefix_system']}/simple_test";

        //when
        $currentController = $controllerResolver->getCurrentController();

        //then
        $this->assertEquals($config['action'], $currentController->currentAction);
    }
}