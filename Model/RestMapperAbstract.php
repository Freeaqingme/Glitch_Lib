<?php

abstract class Glitch_Model_RestMapperAbstract
    extends Glitch_Model_MapperAbstract
{
    protected $_defaultHeaderFields = array(
        'Accept' => 'text/xml',
        'Accept-Charset' => 'utf-8',
        'Content-Type' => 'text/xml; charset=utf-8'
    );

    protected $_xpath = null;
    protected $_baseUrl;

    /**
     * Save the DomainObject
     *
     * Store the DomainObject in persistent storage. Either insert
     * or update the store as required.
     *
     * @param Glitch_Model_DomainObjectAbstract $obj
     * @param bool $force
     * @return mixed
     */
    public function save(Glitch_Model_DomainObjectAbstract $obj, $force = false)
    {
        throw new exception(
        	'The method save() has not (yet) been implemented for RestMappers'
        );
    }

    protected function _postToApi($url, $accept = null, $body = null,
                                  $headers = array())
    {
        return $this->_performRequest($url,
                                      Zend_Http_Client::POST,
                                      $body,
                                      array('Accept' => $accept) + $headers
                      );
    }

    protected function _getFromApi($url, $accept = null, $body = null,
                                   $headers = array())
    {
        return $this->_performRequest($url,
                                      Zend_Http_Client::GET,
                                      $body,
                                      array('Accept' => $accept) + $headers
                      );

    }

    protected function _deleteFromApi($url, $accept = null, $body = null,
                                      $headers = array())
    {
        return $this->_performRequest($url,
                                      Zend_Http_Client::DELETE,
                                      $body,
                                      array('Accept' => $accept) + $headers
                      );
    }

    protected function _putToApi($url, $accept = null, $body = null,
                                 $headers = array())
    {
        return $this->_performRequest($url,
                                      Zend_Http_Client::PUT,
                                      $body,
                                      array('Accept' => $accept) + $headers
                      );
    }

    /**
     * @return Zend_Http_Client
     */
    protected function _createClientInstance()
    {
        return Glitch_Oauth_Consumer::getHttpClient();
    }

    /**
     * @param array|string $accept header. Defaults to 'text/xml'
     * @param unknown_type $contentType header. Defaults to 'text/xml; charset=utf-8
     * @param unknown_type $acceptCharset header. Defaults to 'utf-8'
     * @return Zend_Http_Client
     */
    protected function _performRequest(
        $url,
        $method = Zend_Http_Client::GET,
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

        return new Glitch_Model_Rest_Result($response);
    }

    protected function _assembleHeaders($headers)
    {
        if (isset($headers['Accept'])) {
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
        return Glitch_Controller_Response_Renderer::renderFile($file, array('data' => $vars));
    }

    protected function _fetchCollection($uri, $acceptHeader, $xpath, $dataMapperName, $callback = null)
    {
        $collectionResults = $this->_getFromApi($uri, $acceptHeader);
        if($collectionResults->getStatus() == 404) {
            return null;
        }

        $collectionXpath = new DOMXPath($collectionResults->getDom());

        $collectionNodeList = $collectionXpath->query($xpath);
        if ($collectionNodeList->length < 1 ) {
            return null;
        }

        $resourceEntities = array();
        foreach ($collectionNodeList as $resource) {
            $doc = new DOMDocument();
            $doc->appendChild($doc->importNode($resource, true));
            $entity = $this->_create($resource, $callback);
            $entity->setMapper($this);
            $resourceEntities[] = $this->_getDataMapperInstance($dataMapperName)->toEntity($entity, $doc);
        }

        $this->_xpath = $collectionXpath;
        return $resourceEntities;
    }

    protected function _fetchResource($linkRel, $acceptHeader, $xpath, $dataMapperName)
    {
        $resourceXpath = '/*/link[@rel="'.$linkRel.'"]/@href';
        return $this->_fetchResourceByXpath($resourceXpath, $acceptHeader, $xpath, $dataMapperName);
    }

    protected function _fetchResourceByXpath($resourceXpath, $acceptHeader, $xpath, $dataMapperName, $typeCallback = null)
    {
        $collectionResource = $this->_xpath->query($xpath);

        if ($collectionResource->length < 1 ) {
            return null;
        }

        $collectionResourceDom = new DOMDocument();
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
    }


    protected function _fetchResourceDirect($uri, $acceptHeader, $xpath, $dataMapperName, $object = null)
    {
        $resourceResults = $this->_getFromApi($uri,  $acceptHeader);
        if($resourceResults->getStatus() == 404) {
            return null;
        }
        $resourceXpath = new DOMXPath($resourceResults->getDom());
        //check wether resource is returned, if not it doesn't exist so don't call objects
        $resourceNodeList = $resourceXpath->query($xpath);
        if ($resourceNodeList->length < 1) {
            return null;
        }

        if ($object === null) {
            $object = $this->_create();
        }

        $doc = new DOMDocument();
        $doc->appendChild($doc->importNode($resourceNodeList->item(0),true));
        return $this->_getDataMapperInstance($dataMapperName)->toEntity($object, $doc );

    }

    protected function _fetchCollectionPost($uri, $acceptHeader, $postValue, $xpath, $dataMapperName, $headers = array())
    {

        $collectionResults = $this->_postToApi($uri, $acceptHeader, $postValue, $headers);
        if($collectionResults->getStatus() == 404) {
            return null;
        }

        $collectionXpath = new DOMXPath($collectionResults->getDom());
        //check wether postalcode is returned, if not it doesn't exist so don't call objects
        $collectionNodeList = $collectionXpath->query($xpath);
        if ($collectionNodeList->length < 1 ) {
            return null;
        }

        $resourceEntities = array();
        foreach ($collectionNodeList as $resource) {
            $doc = new DOMDocument();
            $doc->appendChild($doc->importNode($resource,true));
            $resourceEntities[] = $this->_getDataMapperInstance($dataMapperName)->toEntity($this->_create(), $doc);
        }

        $this->_xpath = $collectionXpath;
        return $resourceEntities;

    }

    protected function _getBaseUrl($entrypoint = null)
    {
        if (null === $this->_baseUrl) {
            throw new Glitch_Exception('No base url specified.', 500);
        }

        $res = $this->_getFromApi($this->_baseUrl, 'application/vnd.Glitch.entrypoints+xml');

        $xpath = new DOMXPath($res->getDom());

        return $xpath->query('/entrypoints/link[@rel="' . $entrypoint . '"]/@href')->item(0)->nodeValue;
    }

    protected function setBaseUrl($url) {
        $this->_baseUrl = $url;
        return $this;
    }

    abstract protected function _getRendererDir();
}
