<?php

namespace App\Enums;

enum RatingStatusEnum: string
{
   
    case PUBLISHED = 'Published';
    case NOT_PUBLISHED = 'Not Published';

public static function getValues(): array
{
    return array_map(fn(self $status) => $status->value, self::cases());
}



}
