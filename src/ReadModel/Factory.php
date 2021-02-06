<?php

namespace Madewithlove\Broadway\MongoDB\ReadModel;

use Broadway\ReadModel\Repository;
use Broadway\ReadModel\RepositoryFactory;
use Broadway\ReadModel\RepositoryFactoryInterface;
use Broadway\Serializer\Serializer;
use MongoDB\Database;

class Factory implements RepositoryFactory
{
    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * @var Database
     */
    protected $connection;

    public function __construct(Serializer $serializer, Database $connection)
    {
        $this->serializer = $serializer;
        $this->connection = $connection;
    }

    public function create(string $name, string $class = MongoDBRepository::class): Repository
    {
        return new $class(
            $this->serializer,
            $this->connection,
            $name
        );
    }
}
