<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class PageController extends Controller
{
    public function contact(): View
    {
        return view('storefront.contact');
    }

    public function privacy(): View
    {
        return view('storefront.privacy');
    }
}
