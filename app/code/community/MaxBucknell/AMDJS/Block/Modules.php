<?php

require_once(Mage::getBaseDir('lib').DS.'amd-packager-php/Packager.php');

class MaxBucknell_AMDJS_Block_Modules extends Mage_Core_Block_Template
{
    protected $_modules;

    protected function _construct ()
    {
        parent::_construct();
        $this->_modules = array();
        $this->setTemplate('maxbucknell/amdjs/modules.phtml');
    }

    public function addModule ($moduleName)
    {
        $this->_modules[$moduleName] = $moduleName;
    }

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

        $this->assign('requireJSUrl', $this->getJsUrl('maxbucknell/amdjs/lib/require-2.1.9.js'));
        $this->assign('compiledScriptURL', $helper->getModuleSetURL());
    }
}
