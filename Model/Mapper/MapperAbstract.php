<?php
/**
 * Database to model mapper
 *
 * @category    Glitch
 * @package     Glitch_Model
 */
namespace Glitch\Model\Mapper;
use Glitch\Model as Model;

abstract class MapperAbstract
    implements MapperInterface
{
    protected $_dataMappers = array();

    public function createEntity($data = null, $type = null, $dataMapper = null)
    {
        $obj = $this->_create($type, $data);
        if (null !== $data) {
            $dataMapper = $this->_getDataMapperInstance($dataMapper, $data, $obj);
            $dataMapper->hydrate($obj, $data);
        }

        return $obj;
    }

    protected function _getDataMapperInstance(
                            $dataMapperName,
                            Glitch\Model\Entity\EntityInterface $obj,
                            $data = null)
    {
        if (null == $dataMapperName) {
            $dataMapperName = $this->_getDefaultDataMapperName($obj, $data);
        } elseif(is_callable($dataMapperName)) {
            $dataMapperName = call_user_func_array($obj, $data);
        }

        if(is_string($dataMapperName) &&
           array_key_exists(strtolower($dataMapperName), $this->_dataMappers))
        {
            $dataMapper = $this->_dataMappers[strtolower($dataMapperName)];

        } elseif(is_string($dataMapperName)) {
            $dataMapper = new $dataMapperName();
            $this->_dataMappers[strtolower($dataMapper)] = $dataMapper;

        } elseif ($dataMapperName instanceof Glitch\Model\DataMapperInterface) {
            $this->_dataMappers[strtolower(get_class($dataMapper))] = $dataMapperName;
            return $dataMapperName;

        } elseif (!array_key_exists(strtolower($dataMapperName), $this->_dataMappers)) {
            throw new RuntimeException(
                'The requested datamapper could not be found'
            );
        }

        return $dataMapper;
    }

    abstract protected function _getDefaultDataMapperName($obj = null, $data = null);

    abstract protected function _createEntity($type = null, $data = null);

    /**
     * Create a new instance of the DomainResultSet that this
     * mapper is responsible for
     *
     * @param mixed $data
     * @return Glitch_Model_DomainResultSetAbstract
     */
    abstract public function createResultSet($data);


    /**
     * Save the DomainObject
     *
     * Store the DomainObject in persistent storage. Either insert
     * or update the store as required.
     *
     * @param Glitch_Model_DomainObjectAbstract $obj
     * @param bool $force
     * @return mixed
     */
    public function save(Model\EntityInterface $obj, $force = false)
    {
        if (null === $obj->getId() || true === $force) {
            $result = $this->_insert($obj);
            if ($result === false) {
                return false;
            } elseif($obj instanceof Model\Entity\Rdbms) {
                $obj->setId($result);
                return true;
            } else {
                return true;
            }
        }

        return $this->_update($obj);
    }

     public function toArray(Model\Entity\EntityInterface $entity, $dataMapper = null)
     {
        $dataMapper = $this->_getDataMapperInstance($dataMapper, $entity);
        return $dataMapper->toArray($entity);
     }

    /**
     * Delete the DomainObject
     *
     * Delete the DomainObject from persistent storage.
     *
     * @param Glitch_Model_DomainObjectAbstract $obj
     * @return boolean
     */
    abstract public function delete(Model\Entity\EntityInterface $obj);

}
