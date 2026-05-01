<?php

namespace App\Enums;

enum OrderStatusEnum: string
{

    case Pending = 'pending';
    case Placed = 'placed';
    case Confirmed = 'confirmed';
    case Shipped = 'shipped';
    case Cancelled = 'cancelled';
    case ForDelivery = 'forDelivery';
    case Delivered = 'delivered';
    case Completed = 'completed';



    public static function getValues(): array
    {
        return array_map(fn(self $status) => $status->value, self::cases());
    }
    
 

}
