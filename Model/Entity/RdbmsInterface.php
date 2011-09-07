<?php

namespace Glitch\Model\Entity;

interface RdbmsInterface
    extends EntityInterface
{
    public function getId();

    /**
     * Set the id for this object.
     *
     * This method implements the fluent interface
     *
     * @param mixed $id
     * @return Glitch_Model_DomainObjectAbstract
     * @throws LogicException If the id on the object has already been set
     */
    public function setId($id);

}
