<?php

namespace Glitch\Model\Collection;

interface CollectionInterface extends \Countable, \Iterator
{
    public function __construct($resultSet,
                                \Glitch\Model\Mapper\MapperInterface $mapper);

    public function getMapper();

    public function getResultSet();
}
