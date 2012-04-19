<?php

abstract class Fancrank_App_Controller_BaseController extends Fancrank_Controller_Action
{
	public function preDispatch() {
		die("ere");
	}
    public function init() 
    {
        //add the resource specific javascript file to the layout
        $this->view->headScript()->appendFile('/js/app/'. $this->_request->getControllerName() . '.js');
    }
}

