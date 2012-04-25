<?php
class Fancrank_Db_Profiler_Log extends Zend_Db_Profiler
{
    /**
     * Constructor
     *
     * @param string $label OPTIONAL Label for the profiling info.
     * @return void
     */
    public function __construct($label = null)
    {
        $formatter = new Zend_Log_Formatter_Simple('%timestamp% [%method%: %path%] (%elapsed%): %message% %params%' . PHP_EOL);

        $file = new Zend_Log_Writer_Stream(DATA_PATH . '/logs/db.log');
        $file->setFormatter($formatter);

        $this->logger = new Zend_Log();
        $this->logger->addWriter($file);

        if (php_sapi_name() == 'cli') {
            // TODO: make into a real path
            $this->logger->setEventItem('path', 'CLI');
            $this->logger->setEventItem('path', 'CLI');
        } else {
            $request = Zend_Controller_Front::getInstance()->getRequest();
            $this->logger->setEventItem('method', $request->getMethod());
            $this->logger->setEventItem('path', $request->getRequestUri());
        }
    }

    /**
     * Intercept the query end and log the profiling data.
     *
     * @param  integer $queryId
     * @throws Zend_Db_Profiler_Exception
     * @return void
     */
    public function queryEnd($queryId)
    {
        $state = parent::queryEnd($queryId);

        if (!$this->getEnabled() || $state == self::IGNORED) {
            return;
        }

        $profile = $this->getQueryProfile($queryId);

        $params = $profile->getQueryParams();

        if (empty($params)) {
            $params = null;
        } else {
            $params = sprintf('[%s]', Zend_Json::encode($params));
        }

        $this->logger->setEventItem('elapsed', round($profile->getElapsedSecs(), 5));
        $this->logger->setEventItem('params', $params);

        $this->logger->info($profile->getQuery());
    }
}
