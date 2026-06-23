<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function index(): View
    {
        $restaurant = Restaurant::where('is_active', true)->first();

        return view('pages.contact', compact('restaurant'));
    }
}
