<?php
/*
 * Copyright (c) Ouzo contributors, http://ouzoframework.org
 * This file is made available under the MIT License (view the LICENSE file for more information).
 */
namespace Ouzo;

use Ouzo\Utilities\Path;

class Layout
{
    /** @var View */
    public $view;

    /** @var string */
    private $_renderContent;

    /** @var string */
    private $_layout;

    public function __construct(View $view)
    {
        $this->view = $view;
    }

    public function setLayout($layout)
    {
        $this->_layout = $layout;
        return $this;
    }

    public function unsetLayout()
    {
        $this->_layout = null;
        return $this;
    }

    public function setRenderContent($renderContent)
    {
        $this->_renderContent = $renderContent;
        return $this;
    }

    public function renderLayout()
    {
        if ($this->_layout) {
            $layoutPath = Path::join(ROOT_PATH, ApplicationPaths::getLayoutPath(), $this->_layout . '.phtml');
            /** @noinspection PhpIncludeInspection */
            require_once($layoutPath);
        }
    }

    public function layoutContent()
    {
        return $this->_renderContent;
    }

    public function renderAjax($content = '')
    {
        if ($content) {
            $this->setRenderContent($content);
        }
        $this->setLayout('ajax_layout');
    }
}
