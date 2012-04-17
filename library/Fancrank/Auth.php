<?php

class Fancrank_Auth implements Zend_Auth_Adapter_Interface
{
    **
     * Sets username and password for authentication
     *
     * @return void
     */

    protected $_username;
    protected $_password;

    public function __construct($username, $password)
    {
        $this->_username = $username;
        $this->_password = md5($password);
    }
 
    /**
     * Performs an authentication attempt
     *
     * @throws Zend_Auth_Adapter_Exception If authentication cannot
     *                                     be performed
     * @return Zend_Auth_Result
     */
    public function authenticate()
    {
        $users_model = new Model_Users;

        if ($users_model->findByUsernameAndPassword($this->_username, $this->_password)) {
            return true;
        } else {
            return false;
        }
    }
}