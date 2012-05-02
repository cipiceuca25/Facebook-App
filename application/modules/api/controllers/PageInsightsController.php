<?php

//not sure if this is needed yet as we can recollect this data everytime a page owner logs in?
class Api_UserInsightsController extends Fancrank_API_Controller_BaseController
{
	public function preDispatch()
	{
		//this is a special resource for facebook callbacks on real time updates.  
		//This needs to have special secuirty steps to confirm that facebook is actually accessing this and no body else
	}

	//this action is for pages category changes
	public function cateogryAction()
	{

	}

	//page changed there name
	public function nameAction()
	{

	}

	//page change there picture
	public function pictureAction()
	{

	}
}