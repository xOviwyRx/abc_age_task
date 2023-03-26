<?php

use classes\Product;

if (isset($_GET['date'])){
    $dateValid = true;
    $date = $_GET['date'];
    $dateAttr = explode('-', $date);
    if ($dateAttr && sizeof($dateAttr) == 3 && checkdate($dateAttr[1], $dateAttr[2], $dateAttr[0])){

        require '../private/initialize.php';    
        $products = Product::all();

        foreach ($products as $product){
            if ($product->getName() === 'Левый носок') {
                $product->setOrdersStartDate('2021-01-13');
            }
            $product->getResultOnDate($_GET['date']);
        }
    } else {
        $dateValid = false;
    }
}