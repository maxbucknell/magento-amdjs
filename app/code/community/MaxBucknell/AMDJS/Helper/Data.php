<?php
/**
 * @author Max Bucknell <me@maxbucknell.com>
 * @copyright 2013 Max Bucknell
 */

/**
 * PHP implementation of the AMD specification.
 */
require_once(Mage::getBaseDir('lib').DS.'amd-packager-php/Packager.php');

/**
 * A PHP JavaScript Minifier.
 *
 * We only want it to load when not in developer mode.
 */
if (!Mage::getIsDeveloperMode()) {
    require_once(Mage::getBaseDir('lib').DS.'JShrink/Minifier.php');
}

/**
 * Functionality to compile modules with dependencies.
 */
class MaxBucknell_AMDJS_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * The directory containing the source modules
     * @return string
     */
    public function getSourceBaseDir()
    {
        return Mage::getBaseDir().DS.'js'.DS.'maxbucknell'.DS.'amdjs'.DS.'modules';
    }

    /**
     * The location relative to filesystem root of a module definition.
     *
     * @param string module name
     * @return string
     */
    public function getSourceFileName($module)
    {
        return $this->getSourceBaseDir().DS.$module.'.js';
    }

    /**
     * The directory containing the built files
     * @return string
     */
    public function getBuiltBaseDir()
    {
        return Mage::getBaseDir('media').DS.'amdjs-cache';
    }

    /**
     * The location of a set of built modules relative to the filesystem.
     *
     * @param array $modules
     * @return string
     */
    public function getBuiltFileName($modules)
    {
        return $this->getBuiltBaseDir().DS.$this->_getModuleHash($modules).'.js';
    }

    /**
     * The directory containing the source modules
     * @return string
     */
    public function getBuiltBaseUrl()
    {
        return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).DS.'amdjs-cache';
    }

    /**
     * The URL of a set of built modules.
     * @param array $modules
     * @return string
     */
    public function getBuiltFileUrl($modules)
    {
        return $this->getBuiltBaseUrl().DS.$this->_getModuleHash($modules).'.js';
    }

    /**
     * Generate a unique hash of a set of modules.
     * @param array $modules
     * @return string
     */
    protected function _getModuleHash($modules)
    {
        ksort($modules);
        return md5(implode($modules));
    }

    /**
     * Create the dir containing the built modules if it does not exist.
     * @return void
     */
    protected function _createDir()
    {
        Mage::app()->getConfig()->getOptions()->createDirIfNotExists($this->getBuiltBaseDir());
    }

    /**
     * Compile a set of modules.
     *
     * @param array $modules
     */
    protected function _build($modules)
    {
        $filename = $this->getBuiltFileName($modules);

        $this->_createDir();

        $packager = new Packager();
        $packager->setBaseUrl($this->getSourceBaseDir());
        $builder = $packager->req($modules);

        $output = $builder->output();

        $output .= "\n\nrequire(".Mage::helper('core')->jsonEncode(array_keys($modules)).", function () {});\n";

        if (!Mage::getIsDeveloperMode()) {
            $output = Minifier::minify($output);
        }

        file_put_contents($filename, $output);
    }

    /**
     * True if a set of modules has already been compiled.
     *
     * @param array $modules
     * @return boolean
     */
    public function isModuleSetCached($modules)
    {
        return false;

        $hash = $this->_hashModuleSet($modules);
        return $this->_loadCache($hash) !== false;
    }

    /**
     * Compile and cache a set of modules.
     *
     * @param array $modules
     */
    public function cacheModuleSet($modules)
    {
        $this->_build($modules);
    }

    /**
     * Remove a set of modules from the cache.
     *
     * @param array $modules
     */
    public function clearModuleSetCache($modules)
    {
        $hash = $this->_hashModuleSet($modules);
        $this->_removeCache($hash);
    }
}
