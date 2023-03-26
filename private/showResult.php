<?php
if (isset($_GET['date'])){
    if ($dateValid){
        $filteredProducts = array_filter($products, function ($product) { return $product->getAmount() > 0;});
        if (sizeof($filteredProducts) == 0) {
            echo "Нет товаров на складе";
        } else {
            foreach ($filteredProducts as $product){
                echo "$product <br>";
            }
        }
    } else {
        echo '<div class="error">Невалидная дата</div>';
    }
}   