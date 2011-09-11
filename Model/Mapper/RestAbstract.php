<?php

namespace Glitch\Model\Mapper;

abstract class RestAbstract
    extends MapperAbstract
{
    protected $_defaultHeaderFields = array(
        'Accept' => 'text/xml',
        'Accept-Charset' => 'utf-8',
        'Content-Type' => 'text/xml; charset=utf-8'
    );

    protected $_lastResult;

    abstract protected function _getRendererDir();

    /**
     * @return Zend_Http_Client
     */
    protected function _createClientInstance()
    {
        return \Glitch_Oauth_Consumer::getHttpClient();
    }

    /**
     * @param array|string $accept header. Defaults to 'text/xml'
     * @param unknown_type $contentType header. Defaults to 'text/xml; charset=utf-8
     * @param unknown_type $acceptCharset header. Defaults to 'utf-8'
     * @return Zend_Http_Client
     */
    protected function _performRequest(
        $url,
        $method = \Zend_Http_Client::GET,
        $body = null,
        $headers = array(),
        $toDom = true)
    {
        $client = $this->_createClientInstance();
        $client->setHeaders($this->_assembleHeaders($headers))
               ->setUri($url);

        if ($body) {
            $client->setRawData($body);
        }

        $response = $client->request($method);

        $result = new Rest\Result($response);
        $this->_setLastResult($result);

        return $result;
    }

    protected function _assembleHeaders($headers)
    {
        if (is_string($headers)) {
            $headers = array('Accept' => $headers);
        } elseif (isset($headers['Accept'])) {
            $headers['Accept'] = implode(',', (array) $headers['Accept']);
        }

        if (isset($headers['Accept-Charset'])) {
            $headers['Accept-Charset'] = implode(',', (array) $headers['Accept-Charset']);
        }

        foreach($this->_defaultHeaderFields as $key => $headerField)
        {
            if (!isset($headers[$key]) || false === $headers[$key]) {
                $headers[$key] = $headerField;
            } elseif (false === $headers[$key]) {
                unset($headers[$key]);
            }
        }

        return $headers;
    }

    protected function _renderXml($viewname, $vars)
    {
        $file = $this->_getRendererDir() . '/' . ucfirst($viewname) .'.xml.phtml';
        return \Glitch_Controller_Response_Renderer::renderFile($file, array('data' => $vars));
    }

    protected function _returnEntityFromUri($uri, $acceptHeader, $xpathQuery,
                                              $method = null, $entityType = null,
                                              $dataMapper = null, $requestBody = null)
    {
        if (!$method) {
            $method = \Zend_Http_Client::GET;
        }

        $result = $this->_performRequest($uri,
                                         $method,
                                         $requestBody,
                                         array('Accept' => $acceptHeader)
                 );

        if($result->getStatus() == 404) {
            return null;
        }

        return $this->_findFromResultByXpath($result, $xpathQuery, $entityType, $dataMapper);
    }

    protected function _returnEntitiesFromUri($uri, $acceptHeader, $xpathQuery,
                                              $method = null, $requestBody = null,
                                              $entityType = null, $dataMapper = null)
    {
        if (!$method) {
            $method = \Zend_Http_Client::GET;
        }

        $result = $this->_performRequest($uri,
                                         $method,
                                         $requestBody,
                                         array('Accept' => $acceptHeader)
                 );

        if($result->getStatus() == 404) {
            return null;
        }

        return $this->_fetchFromResultByXpath($result, $xpathQuery, $entityType, $dataMapper);
    }

    protected function _findFromResultByXpath(\Glitch\Model\Mapper\Rest\Result $result,
                                              $xpathQuery,
                                              $entityType,
                                              $dataMapper)
    {
        $xpath = new \DOMXPath($result->getDom());
        //check wether resource is returned, if not it doesn't exist so don't call objects
        $resourceNodeList = $xpath->query($xpathQuery);
        if ($resourceNodeList->length < 1) {
            return null;
        }

        $doc = new \DOMDocument();
        $doc->appendChild($doc->importNode($resourceNodeList->item(0),true));
        return $this->createEntity($doc, $entityType, $dataMapper);
    }

    protected function _fetchFromResultByXpath(\Glitch\Model\Mapper\Rest\Result $result,
                                               $xpathQuery,
                                               $entityType,
                                               $dataMapper)
    {
        $xpath = new \DOMXPath($result->getDom());
        //check wether resource is returned, if not it doesn't exist so don't call objects
        $nodeList = $xpath->query($xpathQuery);
        if ($nodeList->length < 1) {
            return null;
        }

        $resourceEntities = array();
        foreach ($nodeList as $resource) {
            $resourceEntities[] = $this->createEntity($resource, $entityType, $dataMapper);
        }

        return $resourceEntities;
    }

    protected function _setLastResult(\Glitch\Model\Mapper\Rest\Result $result)
    {
        $this->_lastResult = $result;
    }

    protected function _getLastResult()
    {
        return $this->_lastResult;
    }
}
