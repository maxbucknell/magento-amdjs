<?php

require_once(Mage::getBaseDir('lib').DS.'amd-packager-php/Packager.php');

class MaxBucknell_AMDJS_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getModuleCacheDir()
    {
        return Mage::getBaseDir('media').DS.'amdjs-cache';
    }

    public function getModuleSetFilename($modules)
    {
        $hash = $this->_hashModuleSet($modules);
        return Mage::getBaseDir('media').DS.'amdjs-cache'.DS.$hash.'.js';
    }

    public function getModuleSetURL($modules)
    {
        $hash = $this->_hashModuleSet($modules);
        return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).DS.'amdjs-cache'.DS.$hash.'.js';
    }

    public function isModuleSetCached($modules)
    {
        $hash = $this->_hashModuleSet($modules);
        return $this->_loadCache($hash) !== false;
    }

    public function cacheModuleSet($modules)
    {
        $hash = $this->_hashModuleSet($modules);

        $filename = $this->getModuleSetFilename($modules);
        $baseDir = Mage::getBaseDir().DS.'js'.DS.'maxbucknell'.DS.'amdjs'.DS.'modules';

        Mage::app()->getConfig()->getOptions()->createDirIfNotExists($this->getModuleCacheDir());

        $packager = new Packager();
        $packager->setBaseUrl($baseDir);
        $builder = $packager->req($modules);

        file_put_contents($filename);
    }

    public function clearModuleSetCache($modules)
    {
        $hash = $this->_hashModuleSet($modules);
        $this->_removeCache($hash);
    }

    protected function _hashModuleSet($modules)
    {
        ksort($modules);
        return md5(implode($modules));
    }
}
