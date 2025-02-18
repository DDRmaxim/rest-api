<?php

namespace App\Entity;

class Customer
{
    public function __construct(
        public \DateTime $birthdate,
        public string $gender,
    ) {
    }
}