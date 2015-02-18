<?php

class MaxBucknell_AMDJS_Block_Modules extends Mage_Core_Block_Template
{
    /**
     * @var array The modules that are being used in this request
     */
    protected $_modules = array();

    protected function _construct ()
    {
        parent::_construct();
        $this->setTemplate('maxbucknell/amdjs/modules.phtml');
    }

    /**
     * Add a module to this page.
     *
     * @param string $moduleName
     * @return void
     */
    public function addModule ($moduleName)
    {
        $this->_modules[$moduleName] = $moduleName;
    }

    /**
     * Stop using a module on this page.
     *
     * @param string $moduleName
     * @return void
     */
    public function removeModule($moduleName)
    {
        unset($this->_modules[$moduleName]);
    }

    protected function _beforeToHtml()
    {
        $helper = Mage::helper('MaxBucknell_AMDJS');

        if (!$helper->isModuleSetCached($this->_modules)) {
            $helper->cacheModuleSet($this->_modules);
        }

        $this->assign('requireJSUrl', $this->getJsUrl('maxbucknell/amdjs/lib/bower_components/requirejs/require.js'));
        $this->assign('compiledScriptURL', $helper->getBuiltFileUrl($this->_modules));
        $this->assign('modules', array_values($this->_modules));
    }
}
