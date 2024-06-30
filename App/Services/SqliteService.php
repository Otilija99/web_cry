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

    public function create(string $table, array $data): void
    {
        $this->database->insert($table, $data);
    }

    public function update(string $table, array $data, array $where): void
    {
        $this->database->update($table, $data, $where);
    }

    public function findBy(string $table, string $column, $value): array
    {
        return $this->database->select($table, '*', [$column => $value]);
    }
}