<?php

namespace Tests\Unit;

use App\Models\Restaurant;
use App\Services\RestaurantAvailabilityService;
use Carbon\CarbonImmutable;
use Tests\TestCase;

class RestaurantAvailabilityServiceTest extends TestCase
{
    public function test_noon_to_11pm_schedule_is_closed_at_midnight(): void
    {
        $status = $this->statusAt('2026-06-26 00:00:00', [
            'opening_time' => '12:00',
            'closing_time' => '23:00',
        ]);

        $this->assertFalse($status['is_open']);
        $this->assertSame('Opens today at 12:00 PM', $status['label']);
    }

    public function test_noon_to_11pm_schedule_is_open_at_opening_and_closed_at_closing(): void
    {
        $this->assertTrue($this->statusAt('2026-06-26 12:00:00')['is_open']);
        $this->assertTrue($this->statusAt('2026-06-26 22:59:00')['is_open']);
        $this->assertFalse($this->statusAt('2026-06-26 23:00:00')['is_open']);
    }

    public function test_overnight_schedule_stays_open_after_midnight(): void
    {
        $status = $this->statusAt('2026-06-26 01:00:00', [
            'opening_time' => '17:00',
            'closing_time' => '02:00',
        ]);

        $this->assertTrue($status['is_open']);
        $this->assertSame('Open now', $status['label']);
    }

    public function test_overnight_schedule_opens_later_same_day_after_closing(): void
    {
        $status = $this->statusAt('2026-06-26 03:00:00', [
            'opening_time' => '17:00',
            'closing_time' => '02:00',
        ]);

        $this->assertFalse($status['is_open']);
        $this->assertSame('Opens today at 5:00 PM', $status['label']);
    }

    public function test_manual_closure_overrides_schedule(): void
    {
        $status = $this->statusAt('2026-06-26 13:00:00', [
            'is_open' => false,
        ]);

        $this->assertFalse($status['is_open']);
        $this->assertSame('Ordering paused', $status['label']);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function statusAt(string $localDateTime, array $overrides = []): array
    {
        $restaurant = new Restaurant(array_merge([
            'name' => 'Arcade Kebab House',
            'timezone' => 'Australia/Sydney',
            'opening_time' => '12:00',
            'closing_time' => '23:00',
            'is_open' => true,
        ], $overrides));

        return app(RestaurantAvailabilityService::class)->status(
            $restaurant,
            CarbonImmutable::parse($localDateTime, 'Australia/Sydney'),
        );
    }
}
