<?php

namespace Glitch\Model\Mapper;
use Glitch\Model as Model;

abstract class RdbmsAbstract extends MapperAbstract
{
	/**
     * Zend_Db configuration options
     *
     * @var array
     */
    protected $_options = array();
    protected $_dbTable;

    protected $_mapper;
    protected $_lastInsertId;

    public function __construct()
    {
        $this->_setOptions($this->getTableName());
    }

    abstract function getTableName();

    /**
     * Fetch a domain object by id
     *
     * @param mixed $id
     * @return Glitch_Model_DomainObjectAbstract
     */
    public function findById($id)
    {
        if ($id === null) {
            return null;
        }

        $table = $this->_getDbTable();
        $select = $table->select()
                        ->where($this->getTableName().'.'.$this->_getPK().' = ?', $id);

        $obj = $table->fetchRow($select);
        if (null === $obj) return null;

        return $this->createEntity($obj->toArray());
    }

    abstract protected function _getPK();

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
    public function save(Model\Entity\EntityInterface $obj, $forceNew = false, $formatMapper = null)
    {
        if ($forceNew
            || ($obj instanceof  Model\Entity\RdbmsInterface && null === $obj->getId()))
        {
            $result = $this->_insert($obj, $formatMapper);
            if (! $result) {
                return false;
            }

            if($obj instanceof Model\Entity\RdbmsInterface) {
                $obj->setId($result);
            }

            return true;
        }

        return $this->_update($obj, $formatMapper);
    }

    /**
     * Fetch all domain objects for the current model
     *
     * @return Glitch_Model_DomainResultSet
     */
    public function fetchAll()
    {
        $table = $this->_getDbTable();
        $select = $table->select();
        return $this->createCollection($table->fetchAll($select));
    }

    /**
     * Return current table
     *
     * @return Zend_Db_Table
     */
    protected function _getDbTable()
    {
        if (!$this->_dbTable) {
            $this->_dbTable = new \Zend_Db_Table($this->_options);
        }

        return $this->_dbTable;
    }

    /**
     * Set the configuration options for Zend_Db
     *
     * @param $name
     * @return void
     */
    protected function _setOptions($name)
    {
        $this->_options[\Zend_Db_Table::NAME] = $name;
        $this->_options[\Zend_Db_Table::PRIMARY] = $this->_getPK();
    }


    /**
     * Insert the DomainObject in persistent storage
     *
     * This may include connecting to the database
     * and running an insert statement.
     *
     * @param Glitch_Model_DomainObjectAbstract $obj
     * @return mixed
     */
    protected function _insert(Model\Entity\EntityInterface $entity, $formatMapper)
    {
        if (!$entity instanceof Model\Entity\RdbmsInterface) {
            throw new \RuntimeException(
                'Unable to delete entity that does not implement RdbmsInterace'
            );
        }


        $table = $this->_getDbTable();

        $formatMapper = $this->_getInsertFormatMapper($formatMapper, $entity);
        $data = $formatMapper::yield($entity);
        $table->getAdapter()->quoteInto(
            $this->getTableName().'.'.$this->_getPK().'= ?', $entity->getId());

        // Store last inserted ID
        $this->_lastInsertId = $table->insert($data);
        return $this->_lastInsertId;
    }

    /**
     * Return the last inserted ID
     *
     * @return last inserted ID
     */
    protected function _getLastInsertId()
    {
        return $this->_lastInsertId;
    }


    /**
     * Update the DomainObject in persistent storage
     *
     * This may include connecting to the database
     * and running an update statement.
     *
     * @param Glitch_Model_DomainObjectAbstract $obj
     * @return mixed
     */
    protected function _update(Model\Entity\EntityInterface $entity)
    {
        if (!$entity instanceof Model\Entity\RdbmsInterface) {
            throw new \RuntimeException(
                'Unable to delete entity that does not implement RdbmsInterace'
            );
        }

        $table = $this->_getDbTable();
        $formatMapper = $this->_getUpdateFormatMapper($formatMapper, $entity);
        $data = $formatMapper::yield($entity);

        $where = $table->getAdapter()->quoteInto(
                    $this->getTableName().'.'.$this->_getPK().'= ?', $entity->getId());
        return $table->update($data, $where);
    }

    protected  function _getInsertFormatMapper($formatMapper, $entity)
    {
        return $this->_getFormatMapperInstance($formatMapper, $entity);
    }

    protected  function _getUpdateFormatMapper($formatMapper, $entity)
    {
        return $this->_getFormatMapperInstance($formatMapper, $entity);
    }


    /**
     * Delete the DomainObject from persistent storage
     *
     * This may include connecting to the database
     * and running a delete statement.
     *
     * @param Glitch_Model_DomainObjectAbstract $obj
     * @return boolean
     */
    public function delete(Model\Entity\EntityInterface $entity)
    {
        if (!$entity instanceof Model\Entity\RdbmsInterface) {
            throw new \RuntimeException(
                'Unable to delete entity that does not implement RdbmsInterace'
            );
        }

        $table = $this->_getDbTable();

        $where = $table->getAdapter()->quoteInto(
                     $this->getTableName().'.'.$this->_getPK() . ' = ?', $entity->getId()
                 );

        return $table->delete($where);
    }

    public function getContext()
    {
        return Model\Mapper\MapperInterface::CONTEXT_RDBMS;
    }
}
