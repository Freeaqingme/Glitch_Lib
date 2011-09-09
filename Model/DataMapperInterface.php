<?php
/**
 * Glitch mapper interface
 *
 * @category    Glitch
 * @package     Glitch_Model
 * @subpackage  Glitch_Model_DataMapperInterface
 */

namespace Glitch\Model\Mapper;

interface DataMapperInterface
{
	/**
     * Use the input data to set the entity
     *
     * @param Glitch_Model_DomainObjectAbstract $obj
     * @param mixed $data
     * @return Glitch_Model_DomainObjectAbstract
     */
    public function hydrate($obj, $data);

    /**
     * Convert the entity to the desired structure.
     * Will usually proxy to fromEntity();
     *
     * @param Glitch_Model_DomainObjectAbstract $obj
     * @return mixed
     */
    public function dehydrate(Glitch\Model\Entity\EntityInterface $obj);

    /**
     * Convert the entity to an array
     * @param Glitch\Model\Entity\EntityInterface $obj
     * @return array
     */
    public function toArray(Glitch\Model\Entity\EntityInterface $obj);
}
