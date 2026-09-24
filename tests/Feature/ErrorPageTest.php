<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ErrorPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_404_page_is_branded(): void
    {
        $this->get('/page-that-does-not-exist')
            ->assertNotFound()
            ->assertSee('This page is off the menu.')
            ->assertSee('Browse Menu');
    }

    public function test_403_page_is_branded(): void
    {
        Route::get('/test-forbidden-page', fn () => abort(403));

        $this->get('/test-forbidden-page')
            ->assertForbidden()
            ->assertSee('You do not have access to this area.')
            ->assertSee('Access denied');
    }
}
