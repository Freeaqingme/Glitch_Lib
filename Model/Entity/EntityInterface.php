<?php

namespace Glitch\Model\Entity;
use Glitch\Model;

interface EntityInterface
{
    public function __construct(Model\Mapper\MapperInterface $mapper);

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
    public function toArray();

}
