<?php

/*
| Strings used by the browser scripts. They are embedded in each page as JSON
| and read with t('key') in resources/js/ui.js.
*/

return [

    'session_expired' => 'Your session has expired. Please refresh the page and try again.',
    'generic_error' => 'Something went wrong. Please try again.',
    'search_failed' => 'Search failed. Please check your connection and try again.',
    'confirm' => 'Are you sure?',

    'cart' => [
        'full' => 'Your cart is full. Please order the current items first.',
        'added' => ':name added to your cart',
        'view' => 'View cart',
        'price_each' => ':price each',
        'remove_item' => 'Remove :name',
        'items_removed' => 'Some products in your cart are no longer available and have been removed.',
        'prices_failed' => 'We could not load the latest prices. Please check your connection and refresh the page.',
        'clear_confirm' => 'Remove all products from your cart?',
        'empty' => 'Your cart is empty.',
    ],

    'admin' => [
        'visible' => 'Visible',
        'hidden' => 'Hidden',
        'image_type' => ':name is not a JPG, PNG or WebP image.',
        'image_size' => ':name is larger than 5 MB.',
        'images_max' => 'A product can have up to :max images.',
        'remove_new_first' => 'Remove a new image first. A product can have up to :max images.',
        'new' => 'New',
        'remove' => 'Remove',
        'undo' => 'Undo',
        'logo_invalid' => 'The logo must be a JPG, PNG or WebP image under 2 MB.',
    ],

];
