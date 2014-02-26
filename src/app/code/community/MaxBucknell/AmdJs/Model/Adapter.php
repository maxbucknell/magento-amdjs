<?php

class MaxBucknell_AmdJs_Model_Adapter implements \AmdJs\Adapter
{
    public function getSourceBaseDir()
    {
        $basePath = Mage::getBaseDir();
        $configOption = Mage::getStoreConfig('dev/amdjs/sources');
        return $basePath.DS.$configOption;
    }

    public function getBuiltBaseDir()
    {
        return Mage::getBaseDir('media').DS.'amdjs-cache';
    }

    public function getBuiltBaseUrl()
    {
        return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).DS.'amdjs-cache';
    }

    public function getAliases()
    {
        return json_decode(Mage::getStoreConfig('dev/amdjs/aliases'));
    }

    public function isConcatenationEnabled()
    {
        $devMode = Mage::getStoreConfig('dev/amdjs/devMode') && Mage::getIsDeveloperMode();
        return !$devMode && Mage::getStoreConfig('dev/amdjs/concatenate');
    }

    public function getOutputTransformers()
    {
        $devMode = Mage::getStoreConfig('dev/amdjs/devMode') && Mage::getIsDeveloperMode();
        $minificationEnabled = Mage::getStoreConfig('dev/amdjs/minification');
        $minifier = Mage::getStoreConfig('dev/amdjs/minifier');

        $transformers = array();

        if ($minificationEnabled && !$devMode && $minifier !== '') {
            $filename = Mage::getBaseDir().DS.'var'.DS.'input.js';
            if (strpos($minifierCommand, '{{file}}') !== false) {
                $minifier = str_replace('{{file}}', $filename, $minifier);
            } else {
                $minifier .= ' '.$filename;
            }

            $minify = function ($input) use ($filename, $minifier) {
                file_put_contents($filename, $input);
                return shell_exec($minifierCommand);
            }

            $transformers[] = $minify;
        }

        return $transformers;
    }

    public function isCachingEnabled()
    {
        $devMode = Mage::getStoreConfig('dev/amdjs/devMode') && Mage::getIsDeveloperMode();
        return !$devMode && Mage::getStoreConfig('dev/amdjs/cache');
    }

    public function splitDefaultModules()
    {
        return Mage::getStoreConfig('dev/amdjs/split');
    }

    public function getDefaultModules()
    {
        $update = Mage::app()->getLayout()->getUpdate();
        $design = Mage::getSingleton('core/design_package');
        $layout = $update->getFileLayoutUpdatesXml(
            $design->getArea(),
            $design->getPackageName(),
            $design->getTheme('layout'),
            Mage::app()->getStore()->getId()
        );

        $modules = $layout->xpath("//block[@name=\"amdjs_modules\"]/action/*|//reference[@name=\"amdjs_modules\"]/action/*");

        $defaultModules = array();
        foreach ($modules as $module) {
            if ($el->xpath("ancestor::*[parent::layouts]")[0]->getName() == 'default') {
                $defaultModules[] = $el->__toString();
            }
        }

        return $defaultModules;
    }

    public function loadFromCache($hash)
    {
        $result = $this->_loadCache($hash);

        if ($result) {
            return json_decode($result);
        } else {
            return false;
        }
    }

    public function cacheModules($hash, $urls)
    {
        $result = json_encode($urls);
        $this->saveCache($hash, $urls, array('maxbucknell_amdjs'));
    }

    public function clearCache()
    {
        $this->_cleanCache(array('maxbucknell_amdjs'));
    }
}
