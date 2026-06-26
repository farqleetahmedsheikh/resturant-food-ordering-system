<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;

class SeoController extends Controller
{
    public function redirectRestaurant(): RedirectResponse
    {
        return redirect()->route('home', status: 301);
    }

    public function redirectRestaurantMenu(): RedirectResponse
    {
        return redirect()->route('menu', status: 301);
    }

    public function sitemap(): Response
    {
        $urls = collect([
            [
                'loc' => route('home'),
                'priority' => '1.0',
                'changefreq' => 'daily',
            ],
            [
                'loc' => route('menu'),
                'priority' => '0.9',
                'changefreq' => 'daily',
            ],
            [
                'loc' => route('contact'),
                'priority' => '0.6',
                'changefreq' => 'monthly',
            ],
        ]);

        MenuItem::query()
            ->where('is_available', true)
            ->orderByDesc('updated_at')
            ->limit(100)
            ->get(['slug', 'updated_at'])
            ->each(function (MenuItem $item) use ($urls): void {
                $urls->push([
                    'loc' => route('menu.show', $item),
                    'priority' => '0.7',
                    'changefreq' => 'weekly',
                    'lastmod' => $item->updated_at?->toAtomString(),
                ]);
            });

        return response(view('sitemap', ['urls' => $urls])->render(), 200)
            ->header('Content-Type', 'application/xml');
    }
}
