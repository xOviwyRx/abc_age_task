<?php
namespace classes;

use classes\DBClass;
use classes\Product;
use DateTimeImmutable;
use DateTimeInterface;

class Order extends DBClass {
    private $id, $productId, $amount, $date;
    
    public function __construct(int $productId, string|DateTimeInterface $date, int $id = 0, int $amount = 0){
        $this->productId = $productId;
        $this->date = is_string($date)? $date : $date->format('Y-m-d');
        $this->amount = $amount;
        $this->id = $id;
    }

    public function setAmount(int $amount): void {
        $this->amount = $amount;
    }

    public function getAmount(): int {
        return $this->amount;
    }

    public function getDate(): DateTimeImmutable {
        return new DateTimeImmutable($this->date);
    }

    public function save(): void {
        $sql = "INSERT INTO orders (product_id, amount, date) ";
        $sql .= "VALUES (?, ?, ?)";
        $pst = self::$db->prepare($sql);
        $pst->bind_param("iis", $this->productId, $this->amount, $this->date);
        $pst->execute();
        $pst->close();
    }

    static public function getNonEmptyOrders(array $orders): array {
        return array_filter($orders, function ($orders){ return $orders->amount > 0; });
    }

    static public function getExistingOrdersForProduct(Product $product): array {
        $sql = "SELECT * FROM orders ";
        $sql .= "WHERE product_id = {$product->getId()} AND date < '{$product->getStrResultDate()}'";
        $result_set = self::$db->query($sql);
        $orders = [];
        while ($result = $result_set->fetch_assoc()){
            $orders[] = new self($result['product_id'], $result['date'], $result['id'], $result['amount']);
        }
        $result_set->free();
        return $orders;
    }

    static public function fibonacci(int $num): int{
        if ($num == 0){
            return 0;
        }
        if ($num == 1){
            return 1;
        }
        return self::fibonacci($num - 1) + self::fibonacci($num - 2);
    }
}