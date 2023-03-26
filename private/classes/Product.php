<?php

namespace classes;

use DateTime;
use classes\Order;
use classes\DBClass;
use DateTimeImmutable;
use DateTimeInterface;
use classes\Delivery as Delivery;

class Product extends DBClass {

    private $id, $name, $deliveries, $price, $orders;
    private $deliveredAmount, $totalOrderedAmount, $totalRemainder;
    private $objResultDate, $strResultDate, $ordersStartDate;

    public function __construct(array $args = []){
        $this->id = (int)$args['id'] ?? 0;
        $this->name = (string)$args['name'] ?? '';
    }

    public function __toString()
    {
        return htmlspecialchars($this->getName()) . ": " . $this->getAmount() . " шт., цена " .  $this->getPrice();
    }

    public function getName(): string {
        return $this->name;
    }

    public function getId(): int {
        return $this->id;
    }

    public function getStrResultDate(): string {
        return $this->strResultDate;
    }

    public function getAmount(): int {
        return $this->totalRemainder ?? 0;
    }

    public function getPrice(): float {
        return $this->price;
    }

    static public function all(): array {
        $sql = "SELECT * FROM products";
        $result_set = self::$db->query($sql);
        $products = [];
        while ($record = $result_set->fetch_assoc()){
            $products[] = new self($record);
        }
        return $products;
    }

    public function setOrdersStartDate(string $ordersStartDate): void {
        $this->ordersStartDate = new DateTimeImmutable($ordersStartDate);
    }

    public function getOrdersStartDate(): DateTimeImmutable {
        return $this->ordersStartDate;
    }

    public function getResultOnDate(string $resultDate): void {
        
        $this->setResultDate($resultDate);

        $this->deliveries = Delivery::getAllDeliveriesForProduct($this);
        $this->deliveredAmount = Delivery::getDeliveredAmount($this->deliveries, $this->objResultDate);

        if (isset($this->ordersStartDate)){
            $this->orders = $this->getOrders();
            Delivery::getDeliveriesRemainders($this->deliveries, $this->totalOrderedAmount);
            $this->totalRemainder = $this->getRemainderOnDate($this->objResultDate);
        } else {
            $this->totalRemainder = $this->deliveredAmount;
        }
        
        if ($this->totalRemainder > 0) {
            $this->calculatePrice(); 
        }
    }

    private function setResultDate(string $date): void {
        $this->strResultDate = $date;
        $this->objResultDate = new DateTimeImmutable($date);
    }

    // Orders
    private function getOrders(): array {
        $existingOrders = Order::getExistingOrdersForProduct($this);
        $nonEmptyExistingOrders = Order::getNonEmptyOrders($existingOrders);
        $this->totalOrderedAmount = array_sum(array_map(function ($order) { return $order->getAmount(); }, $nonEmptyExistingOrders));
        $startDateForNewOrders = (!empty($existingOrders)) ? end($existingOrders)->getDate()->modify('+1 day') : $this->ordersStartDate;
        $newOrders = $this->createNewOrders($startDateForNewOrders, sizeof($nonEmptyExistingOrders));
        return array_merge($nonEmptyExistingOrders, $newOrders);
    }

    private function createNewOrders(DateTimeInterface $startDate, int $currentOrdersLength): array {
        if ($this->name === 'Левый носок') {
            return $this->createFibonacciOrders($startDate, $currentOrdersLength);
        }
    }

    private function createFibonacciOrders(DateTimeImmutable $startDate, int $currentOrdersLength): array {
        $currentDate = DateTime::createFromImmutable($startDate);
        $i = $currentOrdersLength > 0 ? $currentOrdersLength + 1 : 1;
        $totalDeliveredAmount = Delivery::getDeliveredAmount($this->deliveries);
        $newOrders = [];
        while ($this->totalOrderedAmount < $totalDeliveredAmount && $currentDate < $this->objResultDate){
            $newOrder = new Order($this->id, $currentDate);
            $desireAmount = Order::fibonacci($i);
            $actualAmount = $this->getActualOrderAmountOnDate($desireAmount, $currentDate);
            $newOrder->setAmount($actualAmount);
            $newOrder->save();
            if ($actualAmount > 0) {
                $newOrders[] = $newOrder; 
                $this->totalOrderedAmount += $actualAmount;
                $i++;
            }
            $currentDate->modify('+1 day');
        }
        return $newOrders;
    }
    private function getActualOrderAmountOnDate(int $desiredAmount, DateTimeInterface $date): int{
        $remainders = $this->getRemainderOnDate($date);
        return $desiredAmount > $remainders ? $remainders : $desiredAmount;
    }

    public function getRemainderOnDate(DateTimeInterface $date): int {
        $result = Delivery::getDeliveredAmount($this->deliveries, $date) - $this->totalOrderedAmount;
        return $result > 0 ? $result : 0;
    }
    // END Orders

    // Price
    private function calculatePrice(): void {
        $deliveriesLength = sizeof($this->deliveries);
        if ($deliveriesLength == 0) {
            $this->price = 0;
        } else {
            $firstIndex = $this->getFirstDeliveryIndex($deliveriesLength);
            if ($firstIndex == $deliveriesLength) {
                $this->price = 0;
                return;
            }
            $this->price = $this->getMiddlePrice($firstIndex);
            $this->price += $this->getMargin();
            $this->price = round($this->price, 2);
        }
    }

    private function getFirstDeliveryIndex(int $deliveriesLength): int {
        $i = 0;
        $deliveries = $this->deliveries;
        while ($deliveries[$i]->getCurrentAmount() == 0 
                && $i < $deliveriesLength 
                && $this->objResultDate >= $deliveries[$i]->getDate()){
            $i++;
        }
        return $i;
    }

    private function getMiddlePrice(int $first): float{
        $prices = [];
        for ($i = $first; $i < sizeof($this->deliveries); $i++){
            $prices[] = $this->deliveries[$i]->getCostPrice();
        }
        return array_sum($prices)/sizeof($prices);
    }

    private function getMargin(float $margin = 0.3) : float{
        return $this->price * $margin;
    }
    // END Price



}




    