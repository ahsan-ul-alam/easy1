<?php

/*
| WhatsApp order message templates. Amounts and counts are formatted before
| they are inserted, so Bengali messages use Bengali digits automatically.
*/

return [

    'greeting' => 'আসসালামু আলাইকুম,',
    'money' => '৳:amount',
    'quantity' => ':countটি',

    'single' => [
        'intro' => 'আমি নিচের পণ্যটি অর্ডার করতে চাই।',
        'product' => 'পণ্যের নাম: :name',
        'quantity' => 'পরিমাণ: :quantity',
        'unit_price' => 'প্রতি পিসের দাম: :price',
        'total' => 'মোট মূল্য: :total',
        'link' => 'পণ্যের লিংক:',
        'closing' => 'অনুগ্রহ করে অর্ডারটি নিশ্চিত করুন।',
    ],

    'cart' => [
        'intro' => 'আমি নিচের পণ্যগুলো অর্ডার করতে চাই।',
        'details' => 'অর্ডারের বিবরণ:',
        'quantity' => 'পরিমাণ: :quantity',
        'unit_price' => 'একক মূল্য: :price',
        'subtotal' => 'মোট: :subtotal',
        'compact_line' => ':number. :name × :quantity = :subtotal',
        'more' => '...এবং আরও :countটি পণ্য (সম্পূর্ণ তালিকা চাইলে জানাব)',
        'total_items' => 'মোট পণ্য: :count',
        'grand_total' => 'সর্বমোট মূল্য: :total',
        'closing' => 'অনুগ্রহ করে আমার অর্ডারটি নিশ্চিত করুন।',
    ],

];
