<?php

namespace App\Entity;

use Money\Money;

class Product
{
    public Money $group_cost;

    public function __construct(
        public Money $price,
        public int $quantity
    ) {
        $this->group_cost = $price->multiply($quantity);
    }
}