<?php
/**
 * Same as Zend_Rest_Route but does the reverse in terms of checking for restful controllers
 * basically, it excludes, instead of includes controllers to be restful.
 * used in Bootstra.php:
 *
 * $restRoute = new Zend_Rest_Route($frontController, array(), array('api' => array('home', 'docs')));
 *
 */
class Fancrank_Rest_Route extends Zend_Rest_Route
{
    /**
     * Determine if a specified module + controller combination supports
     * RESTful routing
     *
     * @param string $moduleName
     * @param string $controllerName
     * @return bool
     */
    protected function _checkRestfulController($moduleName, $controllerName)
    {
        if ($this->_allRestful()) {
            return true;
        }
        if ($this->_fullRestfulModule($moduleName)) {
            return true;
        }
        if ($this->_checkRestfulModule($moduleName)
            && $this->_restfulControllers
            && (false === array_search($controllerName, $this->_restfulControllers[$moduleName]))
        ) {
            return true;
        }
        return false;
    }
}
