<?php

class Fancrank_Queue_Adapter extends Zend_Queue_Adapter_Db
{
    private function AbstractConstruct($options, Zend_Queue $queue = null)
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        }

        /*
         * Verify that adapter parameters are in an array.
         */
        if (!is_array($options)) {
            // require_once 'Zend/Queue/Exception.php';
            throw new Zend_Queue_Exception('Adapter options must be an array or Zend_Config object');
        }

        // set the queue
        if ($queue !== null) {
            $this->setQueue($queue);
        }

        $adapterOptions = array();
        $driverOptions  = array();

        // Normalize the options and merge with the defaults
        if (array_key_exists('options', $options)) {
            if (!is_array($options['options'])) {
                // require_once 'Zend/Queue/Exception.php';
                throw new Zend_Queue_Exception("Configuration array 'options' must be an array");
            }

            // Can't use array_merge() because keys might be integers
            foreach ($options['options'] as $key => $value) {
                $adapterOptions[$key] = $value;
            }
        }
        if (array_key_exists('driverOptions', $options)) {
            // can't use array_merge() because keys might be integers
            foreach ((array)$options['driverOptions'] as $key => $value) {
                $driverOptions[$key] = $value;
            }
        }
        $this->_options = array_merge($this->_options, $options);
        $this->_options['options']       = $adapterOptions;
        $this->_options['driverOptions'] = $driverOptions;
    }

    public function __construct($options, Zend_Queue $queue = null)
    {
        $this->AbstractConstruct($options, $queue);

        if (!isset($this->_options['options'][Zend_Db_Select::FOR_UPDATE])) {
            // turn off auto update by default
            $this->_options['options'][Zend_Db_Select::FOR_UPDATE] = false;
        }

        if (!is_bool($this->_options['options'][Zend_Db_Select::FOR_UPDATE])) {
            // require_once 'Zend/Queue/Exception.php';
            throw new Zend_Queue_Exception('Options array item: Zend_Db_Select::FOR_UPDATE must be boolean');
        }

        $this->_queueTable = new Zend_Queue_Adapter_Db_Queue(array(
            'db' => Zend_Db_Table::getDefaultAdapter()
        ));

        $this->_messageTable = new Zend_Queue_Adapter_Db_Message(array(
            'db' => Zend_Db_Table::getDefaultAdapter()
        ));

    }

    public function send($message, Zend_Queue $queue = null, $timeout = null)
    {
        $message = Zend_Json::encode($message);

        // insure no duplicates
        $select = $this->_messageTable->select();
        $select->where('md5 = ?', md5($message));
        $result = $this->_messageTable->fetchRow($select);

        // only proceed if no results were found
        if ($result === null) {
            if ($this->_messageRow === null) {
                $this->_messageRow = $this->_messageTable->createRow();
            }

            if ($queue === null) {
                $queue = $this->_queue;
            }

            if (is_scalar($message)) {
                $message = (string) $message;
            }
            if (is_string($message)) {
                $message = trim($message);
            }

            if (!$this->isExists($queue->getName())) {
                // require_once 'Zend/Queue/Exception.php';
                throw new Zend_Queue_Exception('Queue does not exist:' . $queue->getName());
            }

            $msg           = clone $this->_messageRow;
            $msg->queue_id = $this->getQueueId($queue->getName());
            $msg->created  = time();
            $msg->body     = $message;
            $msg->md5      = md5($message);

            $msg->timeout  = $timeout;
            $msg->handle   = md5(uniqid(rand(), true));

            try {
                $msg->save();
            } catch (Exception $e) {
                // require_once 'Zend/Queue/Exception.php';
                throw new Zend_Queue_Exception($e->getMessage(), $e->getCode(), $e);
            }

            $options = array(
                'queue' => $queue,
                'data'  => $msg->toArray(),
            );

            $classname = $queue->getMessageClass();

            if (!class_exists($classname)) {
                // require_once 'Zend/Loader.php';
                Zend_Loader::loadClass($classname);
            }

            return new $classname($options);
        }
    }
}
