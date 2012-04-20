<?php
class Fancrank_Controller_Request_Http extends REST_Controller_Request_Http
{
    public function setParam($key, $value)
    {
        $key = (string) $key;

        $this->_params[$key] = $value;

        return $this;
    }
}
