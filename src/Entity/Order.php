<?php

namespace App\Entity;

use Money\Money;

class Order
{
    public Money $total_cost;

    public function __construct(
        public Customer $customer,
        public \DateTime $delivery,
        public array $products
    ) {
        $this->total_cost = Money::RUB(0);

        foreach ($products as $product) {
            $this->total_cost = $this->total_cost->add($product->group_cost);
        }
    }
}