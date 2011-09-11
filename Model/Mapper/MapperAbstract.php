<?php
/**
 * Database to model mapper
 *
 * @category    Glitch
 * @package     Glitch_Model
 */
namespace Glitch\Model\Mapper;
use Glitch\Model\RdbmsMapperAbstractTest\FormatMapper;

use Glitch\Model as Model;

abstract class MapperAbstract
    implements MapperInterface
{
    protected $_formatMappers = array();

    abstract protected function _getDefaultFormatMapperName($obj = null, $data = null);

    abstract protected function _createEntity($type = null, $data = null);

    public function createEntity($data = null, $type = null, $formatMapper = null)
    {
        $obj = $this->_createEntity($type, $data);
        if (null !== $data) {
            $formatMapper = $this->_getFormatMapperName($formatMapper, $obj, $data);
            $formatMapper::populate($obj, $data);
        }

        return $obj;
    }

    /**
     * @param string|callbac|Model\FormatMapperInterface $formatMapper
     * @param Model\Entity\EntityInterface $obj (optional) entity to derive the formatmapper to use from
     * @param mixed $data (optional) may be used to derive format mapper to use from
     * @return class name of format mapper
     * @todo implement a repository so that there wont be a zillion instances of one class
     */
    protected function _getFormatMapperName(
                            $formatMapperName = null,
                            Model\Entity\EntityInterface $obj = null,
                            $data = null)
    {
        if (null == $formatMapperName) {
            $formatMapperName = $this->_getDefaultFormatMapperName($obj, $data);
        } elseif(is_callable($formatMapperName)) {
            $formatMapperName = call_user_func_array($formatMapperName, array($obj, $data));
        }

        if ($formatMapperName instanceof Model\FormatMapperInterface) {
            return $formatMapperName;
        }

        $formatMapper = new $formatMapperName();
        if (!$formatMapper instanceof Model\FormatMapperInterface) {
            throw new \RuntimeException('Invalid Format Mapper requested');
        }

        return $formatMapper;
    }

    public function yieldEntity(Model\Entity\EntityInterface $entity, $formatMapper = null)
    {
        $formatMapper = $this->_getFormatMapperName($formatMapper, $entity);
        return $formatMapper::yield($entity);
    }

}
