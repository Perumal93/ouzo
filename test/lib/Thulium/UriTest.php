<?php
class UriTest extends PHPUnit_Framework_TestCase
{
    private $_pathProviderMock;
    private $_uri;

    public function setUp()
    {
        $this->_pathProviderMock = $this->getMock('\Thulium\Uri\PathProvider', array('getPath'));

        $this->_uri = new \Thulium\Uri($this->_pathProviderMock);
    }

    /**
     * @test
     */
    public function shouldExtractController()
    {
        //given
        $this->_path( \Thulium\Config::getPrefixSystem().'/user/add/id/5/name/john' );

        //then
        $this->assertEquals('User', $this->_uri->getController());
        $this->assertEquals('user', $this->_uri->getRawController());
    }

    /**
     * @test
     */
    public function shouldExtractAction()
    {
        //given
        $this->_path( \Thulium\Config::getPrefixSystem().'/user/add/id/5/name/john' );

        //then
        $this->assertEquals('add', $this->_uri->getAction());
    }

    /**
     * @test
     */
    public function shouldGetParamValueByName()
    {
        //given
        $this->_path( \Thulium\Config::getPrefixSystem().'/user/add/id/5/name/john' );

        //then
        $this->assertEquals('john', $this->_uri->getParam('name'));
        $this->assertEquals(5, $this->_uri->getParam('id'));
    }

    /**
     * @test
     */
    public function shouldGetNullValueByNonExistingNameWhenAnyParamsPassed()
    {
        //given
        $this->_path( \Thulium\Config::getPrefixSystem().'/user/add/id/5' );

        //then
        $this->assertNull($this->_uri->getParam('surname'));
    }

    /**
     * @test
     */
    public function shouldGetNullValueByNonExistingNameWhenNoParamsPassed()
    {
        //given
        $this->_path( \Thulium\Config::getPrefixSystem().'/user/add' );

        //then
        $this->assertNull($this->_uri->getParam('surname'));
    }

    /**
     * @test
     */
    public function shouldHandleOddNumberOfParameters()
    {
        //given
        $this->_path( \Thulium\Config::getPrefixSystem().'/user/add/id/5/name' );

        //when
        $param = $this->_uri->getParam('name');

        //then
        $this->assertNull($param);
    }

    /**
     * @test
     */
    public function shouldSplitPathWithoutLimit()
    {
        //given
        $reflectionOfUri = $this->_privateMethod('_parsePath');

        //when
        $paramsExpected = array('user', 'add', 'id', '5', 'name', 'john');
        $callMethod = $reflectionOfUri->invoke(new \Thulium\Uri(), '/user/add/id/5/name/john');

        //then
        $this->assertEquals($paramsExpected, $callMethod);
    }

    /**
     * @test
     */
    public function shouldSplitPathWithLimit()
    {
        //given
        $reflectionOfUri = $this->_privateMethod('_parsePath');

        //when
        $paramsExpected = array('user', 'add', 'id/5/name/john');
        $callMethod = $reflectionOfUri->invoke(new \Thulium\Uri(), '/user/add/id/5/name/john', 3);

        //then
        $this->assertEquals($paramsExpected, $callMethod);
    }

    /**
     * @test
     */
    public function shouldGetAllParams()
    {
        //given
        $this->_path( \Thulium\Config::getPrefixSystem().'/user/add/id/5/name/john/surname/smith/' );

        //when
        $params = $this->_uri->getParams();
        $paramsExpected = array('id' => 5, 'name' => 'john', 'surname' => 'smith');

        //then
        $this->assertEquals($paramsExpected, $params);
    }

    private function _path($path)
    {
        $this->_pathProviderMock->expects($this->any())
            ->method('getPath')
            ->will($this->returnValue($path));
    }

    private function _privateMethod($testMethod)
    {
        $reflectionOfUri = new ReflectionMethod('\Thulium\Uri', $testMethod);
        $reflectionOfUri->setAccessible(true);
        return $reflectionOfUri;
    }
}