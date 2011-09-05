<?php

namespace Glitch\Model\Mapper;

abstract class Rdbms extends MapperAbstract
{
	/**
     * Zend_Db configuration options
     *
     * @var array
     */
    protected $_options = array();

    protected $_primaryKey;
    protected $_name;
    protected $_mapper;
    protected $_lastInsertId;

    public function __construct()
    {
        $this->_setOptions($this->_name);
    }

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
                        ->where($this->_primaryKey.' = ?', $id);

        $obj = $table->fetchRow($select);
        if (null === $obj) return null;

        return $this->create($obj->toArray());
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
        return $this->createResultSet($table->fetchAll($select));
    }

    /**
     * Return current table
     *
     * @return Zend_Db_Table
     */
    protected function _getDbTable()
    {
        return new Zend_Db_Table($this->_options);
    }

    /**
     * Set the configuration options for Zend_Db
     *
     * @param $name
     * @return void
     */
    protected function _setOptions($name)
    {
        $this->_options[Zend_Db_Table::NAME] = $name;
        $this->_options[Zend_Db_Table::PRIMARY] = $this->_primaryKey;
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
    protected function _insert(Glitch_Model_DomainObjectAbstract $obj)
    {
        $table = $this->_getDbTable();

        $data = $this->toArray($obj);
        $table->getAdapter()->quoteInto($this->_primaryKey.'= ?', $obj->getId());

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
    protected function _update(Glitch_Model_DomainObjectAbstract $obj)
    {
        $table = $this->_getDbTable();

        $data = $this->toArray($obj);
        $where = $table->getAdapter()->quoteInto($this->_primaryKey.'= ?', $obj->getId());
        return $table->update($data, $where);
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
    protected function _delete(Glitch_Model_DomainObjectAbstract $obj)
    {
        $table = $this->_getDbTable();

        $where = $table->getAdapter()->quoteInto($this->_primaryKey.'=?', $obj->getId());
        return $table->delete($where);
    }
}
