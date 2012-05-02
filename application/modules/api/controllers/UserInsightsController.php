<?php

//user by facebook to to real time subscriptions
class Api_UserInsightsController extends Fancrank_API_Controller_BaseController
{
	public function preDispatch()
	{
		//this is a special resource for facebook callbacks on real time updates.  
		//This needs to have special secuirty steps to confirm that facebook is actually accessing this and no body else
	}

	public function subscribeAction()
	{

	}

	public function deleteAction()
	{
		
	}

	//this action is for users likes
	public function likesAction()
	{

	}

	public function  photosAction()
	{

	}

	public function feedAction()
	{

	}

	public function feedAction()
	{

	}

	//this should be handeled in a way where eventually collections will happen only when users and pages are setup. Eliminating cron and freeing up resources.
}