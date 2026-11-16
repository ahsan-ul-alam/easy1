<?php

/*
| WhatsApp order message templates. Amounts and counts are formatted before
| they are inserted, so Bengali messages use Bengali digits automatically.
*/

return [

    'greeting' => 'Hello,',
    'money' => 'BDT :amount',
    'quantity' => ':count',

    'single' => [
        'intro' => 'I would like to order the following product.',
        'product' => 'Product: :name',
        'quantity' => 'Quantity: :quantity',
        'unit_price' => 'Unit Price: :price',
        'total' => 'Total: :total',
        'link' => 'Product Link:',
        'closing' => 'Please confirm my order.',
    ],

    'cart' => [
        'intro' => 'I would like to place an order.',
        'details' => 'Order Details:',
        'quantity' => 'Quantity: :quantity',
        'unit_price' => 'Unit Price: :price',
        'subtotal' => 'Subtotal: :subtotal',
        'compact_line' => ':number. :name x :quantity = :subtotal',
        'more' => '...and :count more product (full list on request)|...and :count more products (full list on request)',
        'total_items' => 'Total Items: :count',
        'grand_total' => 'Grand Total: :total',
        'closing' => 'Please confirm my order.',
    ],

];
