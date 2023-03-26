<?php
namespace classes;

use classes\DBClass;
use classes\Product;
use DateTimeImmutable;
use DateTimeInterface;

class Delivery extends DBClass {

    private $id, $number, $productId, $initialAmount, $currentAmount, $costPrice, $date;

    public function __construct(array $args = []){
        $this->id = isset($args['id']) ? (int)$args['id'] : 0;
        $this->productId = isset($args['product_id']) ? (int)$args['product_id'] : 0;
        $this->number = isset($args['number']) ? (string)$args['number'] : '';
        $this->initialAmount = $this->currentAmount = isset($args['amount']) ? (int)$args['amount'] : 0;
        $this->costPrice = isset($args['cost_price']) ? (float)$args['cost_price'] : 0;
        $this->date = isset($args['date']) ? new DateTimeImmutable($args['date']) : 0;
    }

    public function getCurrentAmount(): int {
        return $this->currentAmount;
    }

    public function getDate(): DateTimeImmutable {
        return $this->date;
    }

    public function getCostPrice(): float {
        return $this->costPrice/$this->initialAmount;
    }

    public function setCurrentAmount($value): void {
        $value < 0 ? $this->currentAmount = 0 : $this->currentAmount = (int)$value;
    }

    static public function getAllDeliveriesForProduct(Product $product): array {
        $sql = "SELECT * FROM deliveries WHERE product_id = {$product->getId()} AND date <= '{$product->getStrResultDate()}'";
        $result_set = self::$db->query($sql);
        $result = [];
        while ($record = $result_set->fetch_assoc()){
            $result[] = new self($record);
        }
        $result_set->free();
        return $result;
    }

    static public function getDeliveredAmount(array $deliveries, DateTimeInterface $date = null): int{
        $result = 0;
        foreach ($deliveries as $delivery) {
            if (is_null($date) || $date >= $delivery->date) { 
                $result += $delivery->initialAmount;
            }
        }
        return $result;
    }

    static private function getNextNonEmptyDelivery(array $deliveries): self|bool {
        return current(array_filter($deliveries, function ($delivery){ return $delivery->getCurrentAmount() > 0; }));
    }

    static public function getDeliveriesRemainders(array $deliveries, int $totalOrderedAmount): void {
        self::initializeCurrentDeliveryAmount($deliveries);
        $delivery = self::getNextNonEmptyDelivery($deliveries);
        $currentOrderedAmount = $totalOrderedAmount;

        while ($delivery && $currentOrderedAmount > 0) {
            $sub = $delivery->getCurrentAmount() - $currentOrderedAmount;
            if ($sub < 0){
                $currentOrderedAmount -= $delivery->getCurrentAmount();
                $delivery->setCurrentAmount(0);
                $delivery = self::getNextNonEmptyDelivery($deliveries);
            } else {
                $currentOrderedAmount = 0;
                $delivery->setCurrentAmount($sub);
            }
        }
    }

    static private function initializeCurrentDeliveryAmount(array $deliveries): void {
        foreach ($deliveries as $delivery){
            $delivery->setCurrentAmount($delivery->initialAmount);
        }
    }

}