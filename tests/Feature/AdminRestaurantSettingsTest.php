<?php

namespace Tests\Feature;

use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminRestaurantSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_upload_valid_logo_and_replacement_deletes_old_file(): void
    {
        Storage::fake('public');

        Storage::disk('public')->put('restaurant/logos/old-logo.jpg', 'old');

        $restaurant = Restaurant::create($this->restaurantPayload([
            'logo' => 'restaurant/logos/old-logo.jpg',
        ]));

        $admin = $this->user('admin');

        $this->actingAs($admin)
            ->from(route('admin.settings.restaurant.edit'))
            ->put(route('admin.settings.restaurant.update'), $this->settingsPayload([
                'logo' => UploadedFile::fake()->image('arcade-logo.jpg', 800, 800)->size(350),
            ]))
            ->assertRedirect(route('admin.settings.restaurant.edit'));

        $restaurant->refresh();

        $this->assertNotNull($restaurant->logo);
        $this->assertNotSame('restaurant/logos/old-logo.jpg', $restaurant->logo);
        $this->assertStringStartsWith('restaurant/logos/', $restaurant->logo);
        $this->assertStringEndsWith('.jpg', $restaurant->logo);
        $this->assertStringNotContainsString('http', $restaurant->logo);

        Storage::disk('public')->assertMissing('restaurant/logos/old-logo.jpg');
        Storage::disk('public')->assertExists($restaurant->logo);
    }

    public function test_admin_can_upload_valid_jpeg_logo_and_png_cover_image(): void
    {
        Storage::fake('public');

        $restaurant = Restaurant::create($this->restaurantPayload());
        $admin = $this->user('admin');

        $this->actingAs($admin)
            ->put(route('admin.settings.restaurant.update'), $this->settingsPayload([
                'logo' => UploadedFile::fake()->image('arcade-logo.jpeg', 800, 800)->size(350),
                'cover_image' => UploadedFile::fake()->image('cover.png', 1600, 900)->size(700),
            ]))
            ->assertSessionHasNoErrors();

        $restaurant->refresh();

        $this->assertStringStartsWith('restaurant/logos/', (string) $restaurant->logo);
        $this->assertMatchesRegularExpression('/\.jpe?g$/', (string) $restaurant->logo);
        $this->assertStringStartsWith('restaurant/covers/', (string) $restaurant->cover_image);
        $this->assertStringEndsWith('.png', (string) $restaurant->cover_image);
        Storage::disk('public')->assertExists($restaurant->logo);
        Storage::disk('public')->assertExists($restaurant->cover_image);
    }

    public function test_invalid_logo_uploads_are_rejected(): void
    {
        Storage::fake('public');

        Restaurant::create($this->restaurantPayload());
        $admin = $this->user('admin');

        foreach ([
            UploadedFile::fake()->createWithContent('shell.php', '<?php echo "bad";'),
            UploadedFile::fake()->createWithContent('vector.svg', '<svg xmlns="http://www.w3.org/2000/svg"></svg>'),
            UploadedFile::fake()->createWithContent('renamed.jpg', 'this is not a real image'),
            UploadedFile::fake()->image('huge.png', 4000, 4000)->size(100),
        ] as $file) {
            $this->actingAs($admin)
                ->from(route('admin.settings.restaurant.edit'))
                ->put(route('admin.settings.restaurant.update'), $this->settingsPayload([
                    'logo' => $file,
                ]))
                ->assertRedirect(route('admin.settings.restaurant.edit'))
                ->assertSessionHasErrors('logo');
        }
    }

    public function test_admin_can_remove_logo_and_cover_image_files(): void
    {
        Storage::fake('public');

        Storage::disk('public')->put('restaurant/logos/logo.jpg', 'logo');
        Storage::disk('public')->put('restaurant/covers/cover.png', 'cover');

        $restaurant = Restaurant::create($this->restaurantPayload([
            'logo' => 'restaurant/logos/logo.jpg',
            'cover_image' => 'restaurant/covers/cover.png',
        ]));

        $admin = $this->user('admin');

        $this->actingAs($admin)
            ->delete(route('admin.settings.restaurant.logo.destroy'))
            ->assertRedirect();

        $restaurant->refresh();
        $this->assertNull($restaurant->logo);
        $this->assertSame('restaurant/covers/cover.png', $restaurant->cover_image);
        Storage::disk('public')->assertMissing('restaurant/logos/logo.jpg');
        Storage::disk('public')->assertExists('restaurant/covers/cover.png');

        $this->actingAs($admin)
            ->delete(route('admin.settings.restaurant.cover.destroy'))
            ->assertRedirect();

        $restaurant->refresh();
        $this->assertNull($restaurant->cover_image);
        Storage::disk('public')->assertMissing('restaurant/covers/cover.png');
    }

    public function test_admin_can_remove_logo_and_cover_image_while_saving_settings(): void
    {
        Storage::fake('public');

        Storage::disk('public')->put('restaurant/logos/logo.jpg', 'logo');
        Storage::disk('public')->put('restaurant/covers/cover.png', 'cover');

        $restaurant = Restaurant::create($this->restaurantPayload([
            'logo' => 'restaurant/logos/logo.jpg',
            'cover_image' => 'restaurant/covers/cover.png',
        ]));

        $this->actingAs($this->user('admin'))
            ->from(route('admin.settings.restaurant.edit'))
            ->put(route('admin.settings.restaurant.update'), $this->settingsPayload([
                'remove_logo' => '1',
                'remove_cover_image' => '1',
            ]))
            ->assertRedirect(route('admin.settings.restaurant.edit'))
            ->assertSessionHas('status');

        $restaurant->refresh();

        $this->assertNull($restaurant->logo);
        $this->assertNull($restaurant->cover_image);
        Storage::disk('public')->assertMissing('restaurant/logos/logo.jpg');
        Storage::disk('public')->assertMissing('restaurant/covers/cover.png');
    }

    public function test_removing_missing_logo_does_not_fail(): void
    {
        Storage::fake('public');

        $restaurant = Restaurant::create($this->restaurantPayload([
            'logo' => 'restaurant/logos/missing.jpg',
        ]));

        $this->actingAs($this->user('admin'))
            ->delete(route('admin.settings.restaurant.logo.destroy'))
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertNull($restaurant->refresh()->logo);
    }

    public function test_non_admin_cannot_remove_restaurant_images(): void
    {
        Storage::fake('public');

        Storage::disk('public')->put('restaurant/logos/logo.jpg', 'logo');

        $restaurant = Restaurant::create($this->restaurantPayload([
            'logo' => 'restaurant/logos/logo.jpg',
        ]));

        $this->actingAs($this->user('customer'))
            ->delete(route('admin.settings.restaurant.logo.destroy'))
            ->assertForbidden();

        $this->assertSame('restaurant/logos/logo.jpg', $restaurant->refresh()->logo);
        Storage::disk('public')->assertExists('restaurant/logos/logo.jpg');
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function restaurantPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Arcade Kebab House',
            'email' => 'orders@example.com',
            'phone' => '+61 400 000 000',
            'address' => 'Address to be configured',
            'formatted_address' => 'Address to be configured',
            'timezone' => 'Australia/Sydney',
            'opening_time' => '12:00',
            'closing_time' => '23:00',
            'delivery_fee' => 4.99,
            'minimum_order_amount' => 18,
            'is_open' => true,
        ], $overrides);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function settingsPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Arcade Kebab House',
            'email' => 'orders@example.com',
            'phone' => '+61 400 000 000',
            'address' => 'Address to be configured',
            'formatted_address' => 'Address to be configured',
            'short_description' => 'Kebabs, takeaway and delivery.',
            'opening_time' => '12:00',
            'closing_time' => '23:00',
            'timezone' => 'Australia/Sydney',
            'latitude' => null,
            'longitude' => null,
            'delivery_fee' => '4.99',
            'minimum_order_amount' => '18.00',
            'is_open' => '1',
        ], $overrides);
    }

    private function user(string $role): User
    {
        return User::create([
            'name' => ucfirst($role).' User',
            'email' => $role.'-settings@example.com',
            'phone' => '+61 400 000 000',
            'password' => Hash::make('password'),
            'role' => $role,
            'is_active' => true,
        ]);
    }
}
