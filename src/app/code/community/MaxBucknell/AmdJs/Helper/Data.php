<?php

use AmdJs\Modules;

class MaxBucknell_AmdJs_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getUrls($ids)
    {
        $modules = new Modules(Mage::getModel('MaxBucknell_AmdJs/Adapter'), $ids);
        return $modules->getUrls($ids);
    }
}
