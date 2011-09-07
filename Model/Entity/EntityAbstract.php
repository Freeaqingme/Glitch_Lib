<?php

namespace Glitch\Model\Entity;

use Glitch\Model as Model;

abstract class EntityAbstract
    implements EntityInterface,Rdbms
{
    const CONTEXT_REST = 'rest';
    const CONTEXT_RDBMS = 'rdbms';

    protected $_id = null;

    protected $_context;

    protected $_mapper = null;

    public function __construct(Mapper\MapperInterface $mapper, $context = null)
    {
        $this->setMapper($mapper);

        if($context) {
            $this->setContext($context);
        }
    }

    public function setContext($context)
    {
        if ($this->getContext() != null && $this->getContext() != $context) {
            throw new \RuntimeException('Cannot change context once set');
        } elseif($context != self::CONTEXT_RDBMS && $context != self::CONTEXT_REST) {
            throw new \RuntimeException('Unknown context was tried to set');
        }

        $this->_context = $context;
    }

    public function getContext() {
        return $this->_context;
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

}
