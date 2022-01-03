<?php

namespace Models;

class RegionModel extends Model
{
    protected string $table = 'regions';


    /**
     * Returns all entries in table according to an item
     * 
     * @param string $region
     * @return array
     */
    public function findAllByItem(string $region): array
    {
        $query = $this->pdo->prepare(
            "SELECT title, picture, texts, region 
                FROM {$this->table} 
                WHERE region = :region 
                ORDER BY id"
        );
        $query->execute([":region" => $region]);
        $result = $query->fetchAll();
        return $result;
    }
}
