<?php
/**
 * Glitch API
 *
 * This source file is proprietary and protected by international
 * copyright and trade secret laws. No part of this source file may
 * be reproduced, copied, adapted, modified, distributed, transferred,
 * translated, disclosed, displayed or otherwise used by anyone in any
 * form or by any means without the express written authorization of
 * 4worx software innovators BV (www.4worx.com)
 *
 * @category    Glitch
 * @package     Glitch_Model
 * @author      4worx <info@4worx.com>
 * @copyright   2010, 4worx
 * @version     $Id$
 */

/**
 * Abstract iterable and countable result set that generates (and caches)
 *
 * @category    Glitch
 * @package     Glitch_Model
 */
abstract class Glitch_Model_DomainResultSetAbstract implements Countable, Iterator
{
    /**
     * Data mapper
     *
     * @var Glitch_Model_MapperAbstract
     */
    protected $_mapper = null;

    /**
     * Result set
     *
     * @var array|Iterator
     */
    protected $_resultSet = null;

    /**
     * Constructor
     *
     * @param array|Iterator $resultSet
     * @param Glitch_Model_MapperAbstract $mapper
     * @return void
     */
    public function __construct($resultSet = null, Glitch_Model_MapperAbstract $mapper = null)
    {
        if (null !== $resultSet) {
            $this->setResultSet($resultSet);
        }

        if (null !== $mapper) {
            $this->setMapper($mapper);
        }
    }

    /**
     * Sets the result set
     *
     * @param array|Iterator $resultSet
     * @return Glitch_Model_DomainResultSetAbstract
     * @throws InvalidArgumentException
     */
    public function setResultSet($resultSet)
    {
        if (!is_array($resultSet) && !$resultSet instanceof Iterator) {
            throw new InvalidArgumentException('Result set is invalid');
        }

        $this->_resultSet = $resultSet;

        return $this;
    }

    /**
     * Gets the result set
     *
     * @return array|Iterator
     */
    public function getResultSet()
    {
        return $this->_resultSet;
    }

    /**
     * Sets the data mapper
     *
     * @param Glitch_Model_MapperAbstract $mapper
     * @return Glitch_Model_DomainResultSetAbstract
     */
    public function setMapper(Glitch_Model_MapperAbstract $mapper)
    {
        $this->_mapper = $mapper;

        return $this;
    }

    /**
     * Gets the data mapper
     *
     * @return Glitch_Model_MapperAbstract
     */
    public function getMapper()
    {
        if (null === $this->_mapper) {
            $class = get_class($this);
            $mapper = substr($class, 0, strrpos($class, '_')) . '_Mapper';
            $this->setMapper(new $mapper());
        }
        return $this->_mapper;
    }

    /**
     * Counts the entries in the result set
     *
     * @return int
     */
    public function count()
    {
        return count($this->_resultSet);
    }

    /**
     * From the resultset of data get the current item
     *
     * @return Glitch_Model_DomainObjectAbstract|boolean
     */
    public function current()
    {
        // Check if the index is out of bounds
        if (!isset($this->_resultSet[$this->key()])) {
            return false;
        }

        $result = $this->_resultSet[$this->key()];

        //If result is already an entity, return the entity
        if ($result instanceof Glitch_Model_DomainObjectAbstract) {
            return $result;
        }

        // Let the mapper create object and populate with the result
        $mapper = $this->getMapper();
        return $mapper->create($result);
    }

    /**
     * Return current key
     *
     * @return mixed
     */
    public function key()
    {
        if ($this->_resultSet instanceof Iterator) {
            return $this->_resultSet->key();
        }
        return key($this->_resultSet);
    }

    /**
     * Get next item
     *
     * @return mixed
     */
    public function next()
    {
        if ($this->_resultSet instanceof Iterator) {
           return $this->_resultSet->next();
        }
        return next($this->_resultSet);
    }

    /**
     * Rewind resultset
     *
     * @return mixed
     */
    public function rewind()
    {
        if ($this->_resultSet instanceof Iterator) {
            return $this->_resultSet->rewind();
        }
        return reset($this->_resultSet);
    }

    /**
     * Check if current is valid
     *
     * @return bool
     */
    public function valid()
    {
        return (bool) $this->current();
    }
}
