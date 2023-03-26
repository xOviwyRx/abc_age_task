<?php
require 'config/config.php';
require 'autoload.php';

use classes\Order;
use classes\Product;
use classes\Delivery;
use classes\DBConnection;

try {
    $db = new DBConnection();
    Product::setDatabase($db);
    Order::setDatabase($db);
    Delivery::setDatabase($db);
} catch (mysqli_sql_exception $e) {
    die("Database connection error: {$e->getMessage()}");
}