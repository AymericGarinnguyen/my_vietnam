<?php

namespace Models;

class OrderModel extends Model
{
    protected string $table = 'orders';

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
                INNER JOIN orders_details ON orders.order_id = orders_details.order_id
                INNER JOIN users ON orders.user_id = users.user_id
                GROUP BY orders.order_id";
        // if $order is not null, add request order to the variable $sql
        if ($order) {
            $sql .= " ORDER BY $order";
        }
        $query = $this->pdo->query($sql);
        $result = $query->fetchAll();
        return $result;
    }

    /**
     * Returns entry according to $item
     * 
     * @param array $select
     * @param string $item
     * @param string $value
     * @return array Returns array
     */
    public function findOrder(string $value): array
    {
        $query = $this->pdo->prepare(
            "SELECT orders.order_id, orders.created_at, 
                    CONCAT(users.firstname, users.lastname) AS client, 
                    users.address_line1, users.address_line2, users.phone, users.zipcode, users.city, products.name, quantity
            FROM {$this->table}
            INNER JOIN orders_details ON orders.order_id = orders_details.order_id
            INNER JOIN products ON orders_details.product_id = products.product_id
            INNER JOIN users ON orders.user_id = users.user_id
            WHERE orders.order_id = :order"
        );
        $query->execute([":order" => $value]);
        $result = $query->fetchAll();

        return $result;
    }

    /**
     * Insert order in the database
     * 
     * @param int $user_id
     * @return void
     */
    public function insertOrder(int $user_id): void
    {
        $query = $this->pdo->prepare(
            "INSERT INTO {$this->table} (created_at, user_id)
            VALUES (NOW(), :id)"
        );
        $query->execute([":id" => $user_id]);
    }

    /**
     * Insert details order in the database
     * 
     * @param int $quantity
     * @param int $product_id
     * @return void
     */
    public function insertDetails(int $quantity, int $product_id): void
    {
        $query = $this->pdo->prepare(
            "INSERT INTO orders_details (order_id, quantity, product_id)
            VALUES (LAST_INSERT_ID(), :quantity, :product_id)"
        );
        $query->execute([
            ":quantity" => $quantity,
            ":product_id" => $product_id
        ]);
    }
}
