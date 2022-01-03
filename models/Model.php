<?php

namespace Models;

abstract class Model
{
    protected \PDO $pdo;
    protected string $table;

    public function __construct()
    {
        $this->pdo = \Models\DatabaseModel::getPdo();
    }

    /**
     * Returns all entries in table
     * 
     * @param array $select
     * @param string||null $order
     * @return array
     */
    public function findAll(array $select, string $order = null): array
    {
        // change array $select to a string
        $selectImplode = implode(",", $select);
        // write the request in the variable $sql
        $sql = "SELECT $selectImplode FROM {$this->table}";
        // if $order is not null, add request order to the variable $sql
        if ($order) {
            $sql .= " ORDER BY $order";
        }
        $query = $this->pdo->query($sql);
        $result = $query->fetchAll();
        return $result;
    }

    /**
     * Returns entry according to an item
     * 
     * @param array $select
     * @param string $item
     * @param string $value
     * @return array||boolean Returns array if entry is found or false if not
     */
    public function findByItem(array $select, string $item, string $value)
    {
        // change array $select to a string
        $selectImplode = implode(",", $select);
        $query = $this->pdo->prepare(
            "SELECT $selectImplode 
            FROM {$this->table} 
            WHERE $item = :$item"
        );
        $query->execute([":$item" => $value]);
        $result = $query->fetch();
        return $result;
    }

    /**
     * Insert in the database
     * 
     * @param array $structure
     * @param array $values
     * @param array $params
     * @return void
     */
    public function insert(array $structure, array $values, array $params): void
    {
        // change array $structure to a string
        $structureImplode = implode(",", $structure);
        // change array $value to a string
        $valuesImplode = implode(",", $values);
        $query = $this->pdo->prepare(
            "INSERT INTO {$this->table} ($structureImplode)
            VALUES ($valuesImplode)"
        );
        $query->execute($params);
    }

    /**
     * Update in the database
     * 
     * @param array $structure
     * @param string $item
     * @param array $params
     * @return void
     */
    public function update(array $structure, string $item, array $params): void
    {
        // change array $structure to a string
        $structureImplode = implode(",", $structure);
        // write the request in the variable $sql
        $sql = "UPDATE {$this->table} SET $structureImplode WHERE $item = :$item";
        $query = $this->pdo->prepare($sql);
        $query->execute($params);
    }

    /**
     * Delete in the database
     * 
     * @param string $id
     * @param  string $tableId
     * @return void
     */
    public function delete(string $id, string $tableId): void
    {
        $query = $this->pdo->query(
            "DELETE FROM {$this->table} 
            WHERE $tableId = $id"
        );
    }
}
