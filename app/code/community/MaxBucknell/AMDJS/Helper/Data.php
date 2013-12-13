<?php

require_once(Mage::getBaseDir('lib').DS.'amd-packager-php/Packager.php');

class MaxBucknell_AMDJS_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getSourceBaseDir()
    {
        return Mage::getBaseDir().DS.'js'.DS.'maxbucknell'.DS.'amdjs'.DS.'modules';
    }

    public function getSourceFileName($module)
    {
        return $this->getSourceBaseDir().DS.$module.'.js';
    }

    public function getBuiltBaseDir()
    {
        return Mage::getBaseDir('media').DS.'amdjs-cache';
    }

    public function getBuiltFileName($modules)
    {
        return $this->getBuiltBaseDir().DS.$this->_getModuleHash($modules).'.js';
    }

    public function getBuiltBaseUrl()
    {
        return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).DS.'amd-cache';
    }

    public function getBuiltFileUrl($modules)
    {
        return $this->getBuiltBaseUrl().DS.$this->_getModuleHash($modules).'.js';
    }

    protected function _getModuleHash($modules)
    {
        ksort($modules);
        return md5(implode($modules));
    }

    protected function _createDir()
    {
        Mage::app()->getConfig()->getOptions()->createDirIfNotExists($this->getBuiltBaseDir());
    }

    protected function _build($modules)
    {
        $filename = $this->getBuiltFileName($modules);

        $this->_createDir();

        $packager = new Packager();
        $packager->setBaseUrl($this->getSourceBaseDir());
        $builder = $packager->req($modules);

        $output = $builder->output();

        file_put_contents($filename, $output);
    }

    public function isModuleSetCached($modules)
    {
        return true;

        $hash = $this->_hashModuleSet($modules);
        return $this->_loadCache($hash) !== false;
    }

    public function cacheModuleSet($modules)
    {
        $this->_build($modules);
    }

    public function clearModuleSetCache($modules)
    {
        $hash = $this->_hashModuleSet($modules);
        $this->_removeCache($hash);
    }
}
