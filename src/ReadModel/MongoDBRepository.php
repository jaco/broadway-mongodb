<?php

namespace Madewithlove\Broadway\MongoDB\ReadModel;

use Broadway\ReadModel\Identifiable;
use Broadway\ReadModel\Repository;
use Broadway\Serializer\Serializer;
use MongoDB\Database;
use MongoDB\Driver\Cursor;

class MongoDBRepository implements Repository
{
    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * @var Database
     */
    protected $database;

    /**
     * @var string
     */
    protected $collection;


    public function __construct(Serializer $serializer, Database $database, string $collection)
    {
        $this->serializer = $serializer;
        $this->database = $database;
        $this->collection = $collection;
    }

    public function save(Identifiable $data): void
    {
        $id = $data->getId();

        $payload = $this->serializer->serialize($data);
        $payload['id'] = $id;

        $item = $this->find($id);

        if ($item) {
            $this->newQuery()->replaceOne(['id' => $id], $payload);
        } else {
            $this->newQuery()->insertOne($payload);
        }
    }

    public function find($id): ?Identifiable
    {
        $result = $this->newQuery()->findOne([
            'id' => $id,
        ]);

        if (!$result) {
            return null;
        }

        return $this->deserialize($result);
    }


    public function findBy(array $fields): array
    {
        return $this->deserializeAll($this->newQuery()->find($fields));
    }

    public function findAll(): array
    {
        return $this->findBy([]);
    }

    public function remove($id): void
    {
        $this->newQuery()->deleteOne([
            'id' => $id,
        ]);
    }


    private function deserialize($result)
    {
        return $this->serializer->deserialize($result);
    }

    private function deserializeAll(Cursor $cursor)
    {
        $items = [];

        foreach ($cursor as $result) {
            $items[] = $this->deserialize($result);
        }

        return $items;
    }

    private function newQuery()
    {
        return $this->database->selectCollection($this->collection);
    }
}
