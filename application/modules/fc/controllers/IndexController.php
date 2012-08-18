<?php

class Fc_IndexController extends Fancrank_Admin_Controller_BaseController
{

    public function preDispatch()
    {
        $this->view->layout()->disableLayout();
    }

    public function indexAction()
    {
        //get the amount of fanpages
        $fanpage_model = new Model_Fanpages;
        $this->view->fanpages = $fanpage_model->countAll();

        $fans_model = new Model_Fans;
        $this->view->fans = $fans_model->countAll();
    }
}

