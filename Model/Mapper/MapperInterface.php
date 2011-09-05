<?php

namespace Glitch\Model\Mapper;
use Glitch\Model as Model;


interface MapperInterface
{
    /**
     * Create a new entity using the data supplied.
     * Using the Type parameter a specific class to be used
     * can be determined. Same goes for the datamapper param.
     *
     * @param Array|ArrayAccess $data
     * @param String|Closure $type to instantiate
     * @param String|Closure $dataMapper class to use
     * @return Glitch_Model_EntityInterface
     */
    public function createEntity($data = null, $type = null, $dataMapper = null);

    /**
     * Create a result set using the data supplied.
     * @param Array $data
     */
    public function createResultSet($data);

    /**
     * Save the supplied entity
     * @param Glitch_Model_EntityInterface $entity
     * @param Boolean $forceInsert Force to add the entity as if it were new to the datastore
     */
    public function save(Model\Entity\EntityInterface $entity, $forceNew = false);

    public function delete(Model\Entity\EntityInterface $entity);

    public function toArray(Model\Entity\EntityInterface $entity, $dataMapper = null);
}
