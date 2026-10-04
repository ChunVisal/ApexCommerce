<?php

return [
    'admin.dashboard' => ['Dashboard', null],

    'admin.products'      => ['Products', 'admin.dashboard'],
    'admin.products.uoms' => ['Product UOMs', 'admin.products'],

    'admin.inventory'                     => ['Inventory', 'admin.dashboard'],
    'admin.inventory.stock-movements'     => ['Stock Movements', 'admin.inventory'],
    'admin.inventory.financial-movements' => ['Financial Movements', 'admin.inventory'],

    'admin.users'       => ['Users', 'admin.dashboard'],
    'admin.customers'   => ['Customers', 'admin.dashboard'],
    'admin.reports'     => ['Reports', 'admin.dashboard'],
    'admin.activitylog' => ['Activity Log', 'admin.dashboard'],
    'admin.settings'    => ['Settings', 'admin.dashboard'],

    'cashier.pos'       => ['POS', null],
    'cashier.orders'    => ['Orders', 'cashier.pos'],
    'cashier.products'  => ['Products', 'cashier.pos'],
    'cashier.customers' => ['Customers', 'cashier.pos'],
];
