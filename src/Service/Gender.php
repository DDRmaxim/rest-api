<?php

namespace App\Service;

enum Gender: string
{
    case MALE = "male";
    case FEMALE = "female";

    public static function getArray(): array
    {
        $entity = [];

        foreach (self::cases() as $case) {
            $entity[] = $case->value;
        }

        return $entity;
    }
}