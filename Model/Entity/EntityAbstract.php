<?php

namespace Glitch\Model\Entity;

use Glitch\Model as Model;

abstract class EntityAbstract
    implements EntityInterface,Rdbms
{
    /**
     * ID of object
     *
     * @var mixed
     */
    protected $_id = null;

    /**
     *
     *
     * @var Glitch_Model_DBMapperAbstract
     */
    protected $_mapper = null;


    public function __construct(Model\Mapper\MapperInterface $mapper)
    {
        $this->setMapper($mapper);
    }

    /**
     * Get the ID of this object (unique to the
     * object type)
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * Set the id for this object.
     *
     * This method implements the fluent interface
     *
     * @param mixed $id
     * @return Glitch_Model_DomainObjectAbstract
     * @throws LogicException If the id on the object is already set
     */
    public function setId($id)
    {
        if (null !== $this->getId()) {
            throw new \LogicException('ID is immutable');
        }

        $this->_id = $id;
        return $this;
    }

    /**
     * Retrieve the related mapper of the current domain object
     *
     * @return Glitch_Model_MapperAbstract
     */
    public function getMapper()
    {
        return $this->_mapper;
    }

    /**
     *
     * @param Glitch_Model_MapperAbstract $mapper
     */
    public function setMapper(Glitch\Model\Mapper\MapperInterface $mapper)
    {
        $this->_mapper = $mapper;
    }

    /**
     * Proxy method to mapper implementation of save
     *
     * @param bool $force
     * @return mixed
     */
    public function save($force = false)
    {
    	return $this->getMapper()->save($this, $force);
    }

    /**
     * Proxy method to mapper implementation of delete
     *
     * @return boolean
     */
    public function delete()
    {
    	return $this->getMapper()->delete($this);
    }

    /**
     * Converts the DomainObject back to a data array
     *
     * To be implemented by the concrete mapper class
     *
     * @param Glitch_Model_DomainObjectAbstract $obj
     * @return array
     */
    public function toArray($class = 'Resource')
    {
        return $this->getMapper()->toArray($this, $class);
    }

}
