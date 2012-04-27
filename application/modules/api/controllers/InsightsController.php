<?php

//user by facebook to to real time subscriptions
class Api_InsightsController extends Fancrank_API_Controller_BaseController
{
	public function preDispatch()
	{
		//this is a special resource for facebook callbacks on real time updates.  
		//This needs to have special secuirty steps to confirm that facebook is actually accessing this and no body else
	}

	//this action is for users likes
	public function userlikesAction()
	{

	}

	//other actions should include...
	//-page photos
	//-page feed
	//-page checkins
	//and should be handeled in a way where eventually collections will happen only when users and pages are setup. Eliminating cron and freeing up resources.
}