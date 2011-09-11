<?php
/**
 * Glitch mapper interface
 *
 * @category    Glitch
 * @package     Glitch_Model
 * @subpackage  Glitch_Model_DataMapperInterface
 */

namespace Glitch\Model;

interface FormatMapperInterface
{
	/**
     * Use the input data to set the entity
     *
     * @param Glitch_Model_DomainObjectAbstract $obj
     * @param mixed $data
     * @return Glitch_Model_DomainObjectAbstract
     */
    public static function populate(Entity\EntityInterface $obj, $data);

    /**
     * Convert the entity to the desired structure.
     */
    public static function yield(Entity\EntityInterface $obj);

}
