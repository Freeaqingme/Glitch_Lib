<?php

namespace Glitch\Model\Entity;
use Glitch\Model;

class EntityInterface
{
    public function __construct(Mapper\MapperInterface $mapper, $context = null);

    public function setContext($context);

    public function getContext();

    public function getMapper();

    /**
     * Proxy method to mapper implementation of save
     *
     * @param bool $force
     * @return mixed
     */
    public function save($force = false);

    /**
     * Proxy method to mapper implementation of delete
     *
     * @return boolean
     */
    public function delete();

    /**
     * Converts the DomainObject back to a data array
     *
     * To be implemented by the concrete mapper class
     *
     * @param Glitch_Model_DomainObjectAbstract $obj
     * @return array
     */
    public function toArray($dataMapper = null);

}
