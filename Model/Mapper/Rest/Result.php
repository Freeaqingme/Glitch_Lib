<?php

namespace Glitch\Model\Mapper\Rest;
class Result
{
    protected $_httpResponse;

    protected $_dom;


    public function __construct(\Zend_Http_Response $response)
    {
        $this->_httpResponse = $response;
    }

    public function getHttpResponse()
    {
        return $this->_httpResponse;
    }

    public function __call($method, $args)
    {
        return call_user_func_array(array($this->getHttpResponse(), $method), $args);
    }

    /**
     * @return DOMDocument
     */
    public function getDom()
    {
        if (null === $this->_dom) {
            $this->_dom = new \DOMDocument();
            $loaded = $this->_dom->loadXML($this->_httpResponse->getBody());
            if(!$loaded) {
                $this->_dom = null;
                throw new \Glitch_Exception('Error parsing REST XML response', 500);
            }
        }

        return $this->_dom;
    }

}
