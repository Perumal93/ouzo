<?php
/*
 * Copyright (c) Ouzo contributors, http://ouzoframework.org
 * This file is made available under the MIT License (view the LICENSE file for more information).
 */
namespace Ouzo\Tests;

class MockRedirectHandler
{
    private $_location;

    public function redirect($url)
    {
        $this->_location = $url;

        return $this;
    }

    public function getLocation()
    {
        return $this->_location;
    }
}
