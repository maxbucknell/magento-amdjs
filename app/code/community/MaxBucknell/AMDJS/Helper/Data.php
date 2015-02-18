<?php
/**
 * @author Max Bucknell <me@maxbucknell.com>
 * @copyright 2013 Max Bucknell
 */

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
        $basePath = Mage::getBaseDir();
        $configOption = Mage::getStoreConfig('dev/amdjs/sources');
        return $basePath . DS . $configOption;
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
        return Mage::getBaseDir('media') . DS . 'amdjs-cache';
    }

    /**
     * The location of a set of built modules relative to the filesystem.
     *
     * @param array $modules
     * @return string
     */
    public function getBuiltFileName($modules)
    {
        return $this->getBuiltBaseDir() . 
               DS .
               $this->_getModuleHash($modules) .
               '.out.js';
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
        return $this->getBuiltBaseUrl().DS.$this->_getModuleHash($modules).'.out.js';
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
     * Is cache enabled, either by developer mode or in config?
     * @return boolean
     */
    protected function _isCacheEnabled()
    {
        if (Mage::getStoreConfig('dev/amdjs/devMode') && Mage::getIsDeveloperMode()) {
            return false;
        } else {
            return Mage::getStoreConfig('dev/amdjs/cache');
        }
    }

    /**
     * Is minification enabled, either by developer mode or in config?
     * @return boolean
     */
    protected function _isMinificationEnabled()
    {
        if (Mage::getStoreConfig('dev/amdjs/devMode') && Mage::getIsDeveloperMode()) {
            return false;
        } else {
            return Mage::getStoreConfig('dev/amdjs/minify');
        }
    }

    protected function _getOptimizer()
    {
        return $this->_isMinificationEnabled() ? 'uglify2' : 'none';
    }

    /**
     * Return the aliases specified in config.
     *
     * Aliases allow nicer looking module names, while maintaining a
     * more intricate directory structure. Perhaps one is using bower
     * to load dependencies, but grows weary of typing the full path
     * to each module. Simply specify an alias in the backend, under
     * system/config/developer/amd optimization settings/aliases,
     * like so:
     *
     *     {
     *         "jquery": "bower_components/jquery/jquery"
     *     }
     *
     * Then, in your scripts, you can require jquery like so:
     *
     *     define(['jquery'], function ({
     *         // $(some code)
     *     }));
     *
     * and this will map to the actual location of jQuery. Neat!
     *
     * The method returns an associative array, where the key is
     * the alias, and the value the desired value.
     *
     * @return array
     */
    protected function _getAliases()
    {
        $aliasesJSON = Mage::getStoreConfig('dev/amdjs/paths');
        $array = Mage::helper('core')->jsonDecode($aliasesJSON);

        return is_null($array) ? array() : $array;
    }

    protected function _getShims()
    {
        $shimsJSON = Mage::getStoreConfig('dev/amdjs/shims');
        $array = Mage::helper('core')->jsonDecode($shimsJSON);

        return is_null($array) ? array() : $array;
    }

    protected function _getBuildScriptFilename($modules)
    {
        return Mage::getBaseDir('tmp') .
               DS .
               $this->_getModuleHash($modules) .
               '.js';
    }

    /**
     * Compile a set of modules.
     *
     * @param array $modules
     */
    protected function _build($modules)
    {
        $build = array(
            'baseUrl' => $this->getSourceBaseDir(),
            'out' => $this->getBuiltFileName($modules),
            'paths' => $this->_getAliases(),
            'shim' => $this->_getShims(),
            'optimize' => $this->_getOptimizer(),
            'include' => array_values($modules)
        );

        $jsonBuild = Mage::helper('core')->jsonEncode($build);
        $buildScript = '(' . $jsonBuild . ')';
        $filename = $this->_getBuildScriptFilename($modules);
        file_put_contents($filename, $buildScript);

        shell_exec("r.js -o " . $filename);
    }

    /**
     * True if a set of modules has already been compiled.
     *
     * @param array $modules
     * @return boolean
     */
    public function isModuleSetCached($modules)
    {
        if ($this->_isCacheEnabled()) {
            $hash = $this->_getModuleHash($modules);
            return $this->_loadCache($hash) !== false;
        } else {
            return false;
        }


    }

    /**
     * Compile and cache a set of modules.
     *
     * Module caching works by simply storing the hash of the modules.
     * If that value is stored in the cache, then we assume that the
     * correct file is written, and the filename is returned.
     *
     * If a cache is invalidated, it can be cleared, or just cached
     * again to be built. By default, no life is specified for the cache,
     * but any change in modules will change the cache.
     *
     * By default, in developer mode, cache is turned off, but can also
     * be disabled in config.
     *
     * @param array $modules
     */
    public function cacheModuleSet($modules)
    {

        $hash = $this->_getModuleHash($modules);
        $this->_build($modules);

        if ($this->_isCacheEnabled()) {
            $this->_saveCache($hash, $hash, array('amdjs'));
        }
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
