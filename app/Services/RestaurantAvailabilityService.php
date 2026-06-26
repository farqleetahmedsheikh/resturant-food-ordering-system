<?php

namespace App\Services;

use App\Models\Restaurant;
use Carbon\CarbonImmutable;
use DateTimeZone;

class RestaurantAvailabilityService
{
    /**
     * @return array{is_open: bool, label: string, timezone: string, reason: string|null, opens_at?: string|null, closes_at?: string|null}
     */
    public function status(?Restaurant $restaurant, ?CarbonImmutable $now = null): array
    {
        if (! $restaurant) {
            return [
                'is_open' => false,
                'label' => 'Restaurant unavailable',
                'timezone' => (string) config('app.timezone', 'UTC'),
                'reason' => 'Restaurant settings have not been configured.',
                'opens_at' => null,
                'closes_at' => null,
            ];
        }

        $timezone = $this->timezone($restaurant);
        $now = ($now ?: CarbonImmutable::now($timezone))->setTimezone($timezone);

        if (! $restaurant->is_open) {
            return [
                'is_open' => false,
                'label' => 'Ordering paused',
                'timezone' => $timezone,
                'reason' => 'The restaurant has paused online ordering.',
                'opens_at' => null,
                'closes_at' => null,
            ];
        }

        if (! $restaurant->opening_time || ! $restaurant->closing_time) {
            return [
                'is_open' => true,
                'label' => 'Open for orders',
                'timezone' => $timezone,
                'reason' => null,
                'opens_at' => null,
                'closes_at' => null,
            ];
        }

        [$opensAt, $closesAt] = $this->window($restaurant, $now, $timezone);
        $isOpen = $now->greaterThanOrEqualTo($opensAt) && $now->lessThan($closesAt);
        $nextOpensAt = $isOpen ? null : $this->nextOpening($now, $opensAt);
        $closingSoon = $isOpen && $now->diffInMinutes($closesAt, false) <= 30;

        return [
            'is_open' => $isOpen,
            'label' => $isOpen
                ? ($closingSoon ? 'Closing soon' : 'Open now')
                : $this->openingLabel($nextOpensAt, $now),
            'timezone' => $timezone,
            'reason' => $isOpen ? null : 'The restaurant is outside its configured operating hours.',
            'opens_at' => $nextOpensAt?->toIso8601String(),
            'closes_at' => $isOpen ? $closesAt->toIso8601String() : null,
        ];
    }

    public function isOpen(?Restaurant $restaurant): bool
    {
        return $this->status($restaurant)['is_open'];
    }

    public function timezone(?Restaurant $restaurant): string
    {
        $timezone = $restaurant?->timezone ?: config('app.timezone', 'UTC');

        return in_array($timezone, DateTimeZone::listIdentifiers(), true)
            ? $timezone
            : 'UTC';
    }

    /**
     * @return array{0: CarbonImmutable, 1: CarbonImmutable}
     */
    private function window(Restaurant $restaurant, CarbonImmutable $now, string $timezone): array
    {
        $openingTime = (string) $restaurant->opening_time;
        $closingTime = (string) $restaurant->closing_time;

        $opensAt = CarbonImmutable::parse($now->toDateString().' '.$openingTime, $timezone);
        $closesAt = CarbonImmutable::parse($now->toDateString().' '.$closingTime, $timezone);

        if ($closesAt->lessThanOrEqualTo($opensAt)) {
            $closesAt = $closesAt->addDay();

            if ($now->lessThan(CarbonImmutable::parse($now->toDateString().' '.$closingTime, $timezone))) {
                $opensAt = $opensAt->subDay();
                $closesAt = $closesAt->subDay();
            }
        }

        return [$opensAt, $closesAt];
    }

    private function nextOpening(CarbonImmutable $now, CarbonImmutable $opensAt): CarbonImmutable
    {
        if ($now->lessThan($opensAt)) {
            return $opensAt;
        }

        return $opensAt->addDay();
    }

    private function openingLabel(?CarbonImmutable $opensAt, CarbonImmutable $now): string
    {
        if (! $opensAt) {
            return 'Closed';
        }

        $time = $opensAt->format('g:i A');

        if ($opensAt->isSameDay($now)) {
            return 'Opens today at '.$time;
        }

        if ($opensAt->isSameDay($now->addDay())) {
            return 'Opens tomorrow at '.$time;
        }

        return 'Opens '.$opensAt->format('D, M j').' at '.$time;
    }
}
