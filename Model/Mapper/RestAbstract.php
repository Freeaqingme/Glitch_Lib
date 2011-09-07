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






























    /**
     *     protected function _postToApi($url, $accept = null, $body = null,
                                  $headers = array())
    {
        return $this->_performRequest($url,
                                      \Zend_Http_Client::POST,
                                      $body,
                                      array('Accept' => $accept) + $headers
                      );
    }

    protected function _getFromApi($url, $accept = null, $headers = array())
    {
        return $this->_performRequest($url,
                                      \Zend_Http_Client::GET,
                                      null,
                                      array('Accept' => $accept) + $headers
                      );

    }

    protected function _deleteFromApi($url, $accept = null, $body = null,
                                      $headers = array())
    {
        return $this->_performRequest($url,
                                      \Zend_Http_Client::DELETE,
                                      $body,
                                      array('Accept' => $accept) + $headers
                      );
    }

    protected function _putToApi($url, $accept = null, $body = null,
                                 $headers = array())
    {
        return $this->_performRequest($url,
                                      \Zend_Http_Client::PUT,
                                      $body,
                                      array('Accept' => $accept) + $headers
                      );
    }
     */

    /*
 * Became: _fetchFromResultByXpath()
 * protected function _fetchEntitiesByUri($uri, $acceptHeader, $xpath, $entityType = null, $dataMapper = null)
    {
        $collectionResults = $this->_getFromApi($uri, $acceptHeader);
        if($collectionResults->getStatus() == 404) {
            return null;
        }

        $collectionXpath = new \DOMXPath($collectionResults->getDom());

        $collectionNodeList = $collectionXpath->query($xpath);
        if ($collectionNodeList->length < 1 ) {
            return null;
        }

        $resourceEntities = array();
        foreach ($collectionNodeList as $resource) {
            $doc = new \DOMDocument();
            $doc->appendChild($doc->importNode($resource, true));
            $resourceEntities[] = $this->createEntity($doc, $entityType, $dataMapper);
        }

//        $this->_xpath = $collectionXpath;
        return $resourceEntities;
    }*/


        /**
     * @todo
     * Enter description here ...
     * @param unknown_type $linkRel
     * @param unknown_type $acceptHeader
     * @param unknown_type $xpath
     * @param unknown_type $dataMapperName
     */
//    protected function _fetchResource($linkRel, $acceptHeader, $xpath, $dataMapperName)
//    {
//        $resourceXpath = '/*/link[@rel="'.$linkRel.'"]/@href';
//        return $this->_fetchResourceByXpath($resourceXpath, $acceptHeader, $xpath, $dataMapperName);
//    }

/*
 * This method has probably been made redundant now
 *
 *  protected function _fetchResourceByXpath($resourceXpath, $acceptHeader, $xpath, $dataMapperName, $typeCallback = null)
    {
        $collectionResource = $this->_xpath->query($xpath);

        if ($collectionResource->length < 1 ) {
            return null;
        }

        $collectionResourceDom = new \DOMDocument();
        $collectionResourceDom->appendChild($collectionResourceDom->importNode($collectionResource->item(0),true));

        $xpath = new DOMXPath($collectionResourceDom);
        $resourceUri = $xpath->query($resourceXpath)->item(0)->nodeValue;

        //get postalcode
        $resourceResults = $this->_getFromApi($resourceUri, $acceptHeader);
        if($resourceResults->getStatus() == 404) {
            return null;
        }

        $obj = $this->_create($resourceResults->getDom(), $typeCallback);
        return $this->_getDataMapperInstance($dataMapperName)->toEntity($obj, $resourceResults->getDom());
    }*/



    /*
     * Became: _returnCollectionFromUri()
     *     protected function _fetchCollectionPost($uri, $acceptHeader, $postValue, $xpath, $dataMapperName, $headers = array())
    {
        $collectionResults = $this->_postToApi($uri, $acceptHeader, $postValue, $headers);
        if($collectionResults->getStatus() == 404) {
            return null;
        }

        $collectionXpath = new \DOMXPath($collectionResults->getDom());
        //check wether postalcode is returned, if not it doesn't exist so don't call objects
        $nodeList = $collectionXpath->query($xpath);
        if ($nodeList->length < 1 ) {
            return null;
        }

        $resourceEntities = array();
        foreach ($nodeList as $resource) {
            $doc = new \DOMDocument();
            $doc->appendChild($doc->importNode($resource,true));
            $resourceEntities[] = $this->_getDataMapperInstance($dataMapperName)->toEntity($this->_create(), $doc);
        }

        $this->_xpath = $collectionXpath;
        return $resourceEntities;

    }*/


/*    protected function _getBaseUrl($entrypoint = null)
    {
        if (null === $this->_baseUrl) {
            throw new \Glitch_Exception('No base url specified.', 500);
        }

        $res = $this->_getFromApi($this->_baseUrl, 'application/vnd.Glitch.entrypoints+xml');

        $xpath = new \DOMXPath($res->getDom());

        return $xpath->query('/entrypoints/link[@rel="' . $entrypoint . '"]/@href')->item(0)->nodeValue;
    }

    protected function setBaseUrl($url) {
        $this->_baseUrl = $url;
        return $this;
    }*/


        /*
     * Became: _returnResourceFromUri()
     *
     * protected function _fetchResourceDirect($uri, $acceptHeader, $xpath, $dataMapperName, $object = null)
    {
        $resourceResults = $this->_getFromApi($uri,  $acceptHeader);
        if($resourceResults->getStatus() == 404) {
            return null;
        }
        $resourceXpath = new \DOMXPath($resourceResults->getDom());
        //check wether resource is returned, if not it doesn't exist so don't call objects
        $resourceNodeList = $resourceXpath->query($xpath);
        if ($resourceNodeList->length < 1) {
            return null;
        }

        if ($object === null) {
            $object = $this->_create();
        }

        $doc = new \DOMDocument();
        $doc->appendChild($doc->importNode($resourceNodeList->item(0),true));
        return $this->_getDataMapperInstance($dataMapperName)->toEntity($object, $doc );

    }*/


}
