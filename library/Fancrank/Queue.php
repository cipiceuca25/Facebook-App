<?php
class Fancrank_Queue extends Zend_Queue
{
    /**
     * Send a message to the queue
     *
     * @param  mixed $message message
     * @return Zend_Queue_Message
     * @throws Zend_Queue_Exception
     */
    public function send($message, $timeout = null){
        return $this->getAdapter()->send($message, null, $timeout);
    }
}
