<?php

//not sure if this is needed yet as we can recollect this data everytime a page owner logs in?
class Api_UserSubscriptionController extends Fancrank_API_Controller_BaseController
{
	public function preDispatch()
	{
		//this is a special resource for facebook callbacks on real time updates.  
		//This needs to have special secuirty steps to confirm that facebook is actually accessing this and no body else
	}

	//the object passes will come here
	public function postAction()
	{
		//do a switch on every type expected and handle them in private functions to be defined below
		//currently we should have...feed, friends, activities, interests, music, books, movies, television, likes, checkins

		//log $this->getAllParams() to see what object I am recieving;
		 Log::Info('facebook data %s', $this->getAllParams());

	}

	//for facebook verification
	public function getAction()
	{
		 //$_GET["hub_mode"],$_GET["hub_challenge"] and $_GET["hub_verify_token"]

		//verify the token (something we generate for the user and sent to facebook when subcribed (maybe a md5 hash of the entire user? ))

		//return the hub challenge back to facebook
		echo $this->_getParam('hub_challenge');
	}
	//this should be handeled in a way where eventually collections will happen only when users and pages are setup. Eliminating cron and freeing up resources.
}