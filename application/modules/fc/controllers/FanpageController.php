<?php
class Fc_FanpageController extends Fancrank_Fc_Controller_BaseController
{
	protected $_fanpageId;
	
    public function preDispatch() {
    	parent::preDispatch();
		$this->view->layout()->disableLayout();
		$this->_fanpageId = $this->_getParam('id');
		if (empty($this->_fanpageId)) {
			throw new Exception('missing fanpage id');
		}
	}

	public function indexAction() {
		echo 'yes';	
	}
	
    public function forceupdateAction() {
		if (isset($_GET['update']) && $_GET['update'] == 'now') {
			echo 'update';
			$cmd = sprintf('php %s/update_fanpage.php -p%s &', APPLICATION_PATH .'/../', $this->_fanpageId);
			echo shell_exec($cmd);
			$this->_helper->json(array('message'=>'start collect'));
		}
    }
}

?>