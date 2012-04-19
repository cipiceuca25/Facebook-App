<?php

class App_IndexController extends Fancrank_Admin_Controller_BaseController
{

    public function indexAction()
    {
        //load the landing page and grab the top fan data
       $topfan_model = new Model_TopFans;
       //$topfans = $topfan->getTopFan($fanpage_id);
       
       //$this->view->topfans = $topfans;
        
    }
}

