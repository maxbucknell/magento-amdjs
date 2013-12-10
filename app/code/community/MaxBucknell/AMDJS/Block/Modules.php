<?php

require_once(Mage::getBaseDir('lib').DS.'amd-packager-php/Packager.php');

class MaxBucknell_AMDJS_Block_Modules extends Mage_Core_Block_Template
{
    protected $_modules;

    protected function _construct ()
    {
        parent::_construct();
        $this->_modules = array();
        $this->setTemplate('redbox/modules/loader.phtml');
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
        $helper = Mage::helper('boxes');

        if (!$helper->isModuleSetCached($this->_modules)) {
            $helper->cacheModuleSet($this->_modules);
        }

        $this->assign('requireJSUrl', $this->getJsUrl('js/lib/require.js'));

        $filename = $helper->getModuleSetFilename($this->_modules);
        $this->assign('compiledScript', $helper->getModuleSetURL);
    }
}
