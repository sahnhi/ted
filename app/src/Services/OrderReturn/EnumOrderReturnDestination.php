<?php

declare(strict_types=1);

namespace App\Services\OrderReturn;

final class EnumOrderReturnDestination
{
    public const DESTINATION_PHENIX = 'phenix';
    public const DESTINATION_EMMAUS = 'emmaus';
    public const DESTINATION_TRASH = 'trash';
    public const DESTINATION_RESTOCK = 'restock';
    public const DESTINATION_UNTRACK = 'untrack';
    public const DESTINATION_SUPPLIER = 'supplier';
    public const DESTINATION_WAITING = 'waiting';

    public const ALL_DESTINATION_TYPE = [
        'app.ui.order_return_destination.restock' => self::DESTINATION_RESTOCK,
        'app.ui.order_return_destination.phenix' => self::DESTINATION_PHENIX,
        'app.ui.order_return_destination.emmaus' => self::DESTINATION_EMMAUS,
        'app.ui.order_return_destination.untrack' => self::DESTINATION_UNTRACK,
        'app.ui.order_return_destination.trash' => self::DESTINATION_TRASH,
        'app.ui.order_return_destination.supplier' => self::DESTINATION_SUPPLIER,
        'app.ui.order_return_destination.waiting' => self::DESTINATION_WAITING,
    ];

    public const AVAILABLE_DESTINATION_TYPE = [
        'app.ui.order_return_destination.restock' => self::DESTINATION_RESTOCK,
        'app.ui.order_return_destination.phenix' => self::DESTINATION_PHENIX,
        'app.ui.order_return_destination.emmaus' => self::DESTINATION_EMMAUS,
        'app.ui.order_return_destination.untrack' => self::DESTINATION_UNTRACK,
        'app.ui.order_return_destination.trash' => self::DESTINATION_TRASH,
        'app.ui.order_return_destination.supplier' => self::DESTINATION_SUPPLIER,
    ];
}
