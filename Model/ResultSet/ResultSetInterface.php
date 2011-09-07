<?php

namespace Glitch\Model\ResultSet;

interface ResultSetInterface extends Countable, Iterator
{
    public function __construct($resultSet = null,
                                \Glitch\Model\Mapper\MapperInterface $mapper);

    public function getMapper();

    public function getResultSet();
}
