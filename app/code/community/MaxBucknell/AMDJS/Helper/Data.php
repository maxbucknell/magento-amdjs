<?php

require_once(Mage::getBaseDir('lib').DS.'amd-packager-php/Packager.php');

class RedboxDigital_Boxes_Helper_Data exends Mage_Core_Helper_Abstract
{
    public function getModuleSetFilename($modules)
    {
        $hash = $this->_hashModuleSet($modules);
        return Mage::getBaseDir('media').DS.'boxes-cache'.DS.$hash.'.js';
    }

    public function getModuleSetURL($modules)
    {
        $hash = $this->_hashModuleSet($modules);
        return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).DS.'boxes-cache'.DS.$hash.'.js';
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
        $baseDir = Mage::getBaseDir('js').DS.'boxes';

        $packager = new Packager();
        $packager->setBaseDir($baseDir);
        $builder = $packager->req($modules);

        file_put_contents($filename, $builder->output());

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
