<?php

namespace Models;

class ForumModel extends Model
{
    protected string $table = 'forum';

    /**
     * Returns all entries in table according  to an item
     * 
     * @param array $select
     * @param string $item
     * @param string $value
     * @param string||null $order
     * @param string||null $add
     * @return array
     */
    public function findAllByItem(array $select, string $item, string $value, string $order = null, string $add = null): array
    {
        // change array $select to a string
        $selectImplode = implode(",", $select);
        // write the request in the variable $sql
        $sql = "SELECT $selectImplode 
                    FROM {$this->table}
                    INNER JOIN forum_categories ON forum.categories_id = forum_categories.categories_id
                    INNER JOIN users ON forum.user_id = users.user_id
                    WHERE forum.$item = :$item";
        // if $add is not null, add request AND to the variable $sql
        if ($add !== null) {
            $sql .= " AND $add = 1";
        }
        // if $order is not null, add request order to the variable $sql
        if ($order) {
            $sql .= " ORDER BY $order";
        }
        $query = $this->pdo->prepare($sql);
        $query->execute([":$item" => $value]);
        $result = $query->fetchAll();
        return $result;
    }

    /**
     * Returns entry according to $item
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
            INNER JOIN forum_categories ON forum.categories_id = forum_categories.categories_id
            INNER JOIN users ON forum.user_id = users.user_id
            WHERE $item = :$item"
        );
        $query->execute([":$item" => $value]);
        $result = $query->fetch();
        return $result;
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
        $sql = "SELECT $selectImplode 
                FROM {$this->table}
                INNER JOIN forum_categories ON forum.categories_id = forum_categories.categories_id
                INNER JOIN users ON forum.user_id = users.user_id";
        // if $order is not null, add request order to the variable $sql
        if ($order) {
            $sql .= " ORDER BY $order";
        }
        $query = $this->pdo->query($sql);
        $result = $query->fetchAll();
        return $result;
    }

    /**
     * Returns last message by categories
     * 
     * @return array
     */
    public function findLastMessage(): array
    {
        $query = $this->pdo->query(
            "SELECT categories_id, MAX(created_at) AS created_at
                                    FROM {$this->table}
                                    WHERE is_checked = 1
                                    GROUP BY categories_id
                                    ORDER BY categories_id"
        );
        $result = $query->fetchAll();
        return $result;
    }

    /**
     * Count the number of comments
     * 
     * @param string $id
     * @return array
     */
    public function countComments(string $id): array
    {
        $query = $this->pdo->prepare(
            "SELECT COUNT(*) AS nb_comments 
                FROM {$this->table}
                WHERE categories_id = :categories_id AND is_checked = 1"
        );
        $query->execute([":categories_id" => $id]);
        $result = $query->fetch();
        return $result;
    }

    /**
     * Returns all comments in table according  id and pagination
     * 
     * @param array $select
     * @param string $item
     * @param string $value
     * @param string||null $order
     * @param string||null $add
     * @return array
     */
    public function findCommentsById(string $id, int $firstComment, int $nbPerPage): array
    {
        $query = $this->pdo->prepare(
            "SELECT title, message, f.created_at, firstname, lastname, category
                    FROM {$this->table} f
                    INNER JOIN forum_categories c ON f.categories_id = c.categories_id
                    INNER JOIN users u ON f.user_id = u.user_id
                    WHERE f.categories_id = :forum_id AND is_checked = 1
                    ORDER BY f.created_at DESC
                    LIMIT $firstComment, $nbPerPage"
        );

        $query->execute([
            ":forum_id" => $id
        ]);
        
        $result = $query->fetchAll();
        return $result;
    }
}
