<?php

namespace Glitch\Model\Entity;
use Glitch\Model;

abstract class EntityAbstract
    implements EntityInterface,RdbmsInterface
{

    protected $_id = null;

    protected $_mapper = null;

    public function __construct(Model\Mapper\MapperInterface $mapper)
    {
        $this->setMapper($mapper);
    }

    public function getContext() {
        return $this->getMapper()->getContext();
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

    public function setMapper(Model\Mapper\MapperInterface $mapper)
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
     * Converts the Entity to a data array
     *
     * @return array
     */
    public function toArray()
    {
        $values = array();
        $methods = get_class_methods($this);
        foreach($methods as $method) {
            if(substr($method, 0, 3) == 'get') {
                $values[lcfirst(substr($method,3))] = $this->$method();
            }
        }

        return $values;
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
            throw new \LogicException('Cannot re-set ID, it is immutable');
        }

        $this->_id = $id;
        return $this;
    }

}
