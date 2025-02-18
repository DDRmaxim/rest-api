<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\Customer;
use App\Entity\Product;
use App\Service\Gender;
use Money\Money;
use Money\Currencies\ISOCurrencies;
use Money\Formatter\IntlMoneyFormatter;

class OrderController extends AbstractController
{
    /**
     * Скидка для пенсионеров, мужчины: ≥63, женщины: ≥58
     * @var int
     */
    const DiscountPensioners = 5;

    /**
     * Скидка на ранний заказ, за неделю и более
     * @var int
     */
    const DiscountEarlyOrder = 4;

    /**
     * Скидка на количество товаров, больше 10 единиц одного товара
     * @var int
     */
    const DiscountCountProducts = 3;
    
    private Order $order;

    public function cost()
    {
        $this->response->httpCode(200);

        $total_cost = $this->calcTotalCost();

        $currencies = new ISOCurrencies();
        $numberFormatter = new \NumberFormatter('ru_RU', \NumberFormatter::CURRENCY);
        $moneyFormatter = new IntlMoneyFormatter($numberFormatter, $currencies);

        $real_cost = floatval($total_cost->getAmount()) / 100;

        return $this->response->json([
            "total_cost" => [
                "real" => $real_cost,
                "format" => $moneyFormatter->format($total_cost)
            ]
        ]);
    }

    private function calcTotalCost(): Money
    {
        $total_cost = $this->getDiscountPensioners($this->order->total_cost);
        $total_cost = $this->getDiscountEarlyOrder($total_cost);
        $total_cost = $this->getDiscountCountProducts($total_cost);

        return $total_cost;
    }

    private function getDiscountPensioners(Money $cost): Money
    {
        $now = new \DateTime();
        $age = $now->diff($this->order->customer->birthdate);

        $discount = 0;

        if ($this->order->customer->gender === Gender::MALE->value && $age->y >= 63) {
            $discount = self::DiscountPensioners;
        }
        
        if ($this->order->customer->gender === Gender::FEMALE->value && $age->y >= 58) {
            $discount = self::DiscountPensioners;
        }

        list($discounted, $cession) = $cost->allocate([100 - $discount, $discount]);

        return $discounted;
    }

    private function getDiscountEarlyOrder(Money $cost): Money
    {
        $now = new \DateTime();
        $days = $now->diff($this->order->delivery)->days;

        $discount = 0;

        if ($days >= 7) {
            $discount = self::DiscountEarlyOrder;
        }

        list($discounted, $cession) = $cost->allocate([100 - $discount, $discount]);

        return $discounted;
    }

    private function getDiscountCountProducts(Money $cost): Money
    {
        $discount = 0;

        foreach ($this->order->products as $product) {
            if ($product->quantity > 10) {
                $discount = self::DiscountCountProducts;
            }
        }

        list($discounted, $cession) = $cost->allocate([100 - $discount, $discount]);

        return $discounted;
    }

    private function fillingCustomer(): Customer
    {
        return new Customer(
            new \DateTime($this->request->data->order->customer->birthdate),
            $this->request->data->order->customer->gender
        );
    }

    private function fillingDelivery(): \DateTime
    {
        return new \DateTime($this->request->data->order->delivery);
    }

    private function fillingProducts(): array
    {
        $products = [];

        foreach ($this->request->data->order->products as $product) {
            $this->validateProduct($product);

            $amount = preg_replace("/[^0-9]/", '', $product->price);
            $price = Money::RUB($amount);

            $products[] = new Product($price, $product->quantity);
        }

        return $products;
    }

    public function fillingData()
    {
        $customer = $this->fillingCustomer();
        $delivery = $this->fillingDelivery();
        $products = $this->fillingProducts();

        $this->order = new Order(
            $customer,
            $delivery,
            $products
        );
    }

    public function validateProduct(object $product)
    {
        if (empty($product->price)) {
            $this->error("Empty price");
        }

        if (empty($product->quantity)) {
            $this->error("Empty quantity");
        }

        if ($product->price < 0) {
            $this->error("Invalid price");
        }

        if ($product->quantity < 1) {
            $this->error("Invalid quantity");
        }
    }

    public function validateData()
    {
        if (empty($this->request->data->order)) {
            $this->error("Empty order");
        }

        if (empty($this->request->data->order->customer)) {
            $this->error("Empty customer");
        }

        if (empty($this->request->data->order->customer->birthdate)) {
            $this->error("Empty birthdate");
        }

        if (empty($this->request->data->order->customer->gender)) {
            $this->error("Empty gender");
        }

        if (!in_array($this->request->data->order->customer->gender, Gender::getArray())) {
            $this->error("Invalid gender");
        }

        if (empty($this->request->data->order->delivery)) {
            $this->error("Empty delivery");
        }

        if (empty($this->request->data->order->products)) {
            $this->error("Empty products");
        }
    }
}