<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Services\RestaurantAvailabilityService;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function index(RestaurantAvailabilityService $availability): View
    {
        $restaurant = Restaurant::current();
        $availabilityStatus = $availability->status($restaurant);

        return view('pages.contact', compact('restaurant', 'availabilityStatus'));
    }
}
