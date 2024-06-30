<?php

namespace App\Services;

use Medoo\Medoo;


class SqliteService
{
    private Medoo $database;

    public function __construct()
    {
        $this->database = new Medoo([
            'type' => 'sqlite',
            'database' => 'storage/database.sqlite'
        ]);
    }

    public function create(string $tableName, array $values): void
    {
        $this->database->insert($tableName, $values) === null;
    }

    public function update(string $tableName, array $values, array $where): void
    {
        $this->database->update($tableName, $values, $where);
    }

    public function delete(string $tableName, array $where): void
    {
        $this->database->delete($tableName, $where);
    }

    public function findByUserId(string $tableName, int $id): array
    {
        return $this->database->select($tableName, '*', ["user_id" => $id]) ?? [];
    }

    public function findBy(string $tableName, string $column, string $value): array
    {
        return $this->database->select($tableName, "*", [$column => $value]) ?? [];
    }

}