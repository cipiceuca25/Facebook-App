<?php
class Collectors_Bootstrap extends Zend_Application_Module_Bootstrap
{
    public function __initSources()
    {
        return new Zend_Config_Json(APPLICATION_PATH . '/configs/sources.json', APPLICATION_ENV, array('allowModifications' => true));
    }
}
