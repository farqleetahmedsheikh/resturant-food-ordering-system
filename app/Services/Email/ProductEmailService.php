<?php

namespace App\Services\Email;

use App\Mail\ProductAnnouncementMail;
use App\Mail\ProductPriceChangedMail;
use App\Models\MenuItem;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ProductEmailService
{
    public function sendProductAdded(MenuItem $menuItem): void
    {
        $this->sendToCustomers(new ProductAnnouncementMail($menuItem->loadMissing(['category', 'restaurant'])), 'product_added', $menuItem);
    }

    public function sendPriceChanged(MenuItem $menuItem, string $previousPrice): void
    {
        $this->sendToCustomers(new ProductPriceChangedMail($menuItem->loadMissing(['category', 'restaurant']), $previousPrice), 'product_price_changed', $menuItem);
    }

    private function sendToCustomers(object $mailable, string $event, MenuItem $menuItem): void
    {
        User::query()
            ->where('role', 'customer')
            ->where('is_active', true)
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->select(['id', 'name', 'email'])
            ->chunkById(100, function ($customers) use ($mailable, $event, $menuItem): void {
                foreach ($customers as $customer) {
                    try {
                        Mail::to($customer->email, $customer->name)->send(clone $mailable);
                    } catch (\Throwable $exception) {
                        Log::warning('Customer product email could not be sent.', [
                            'event' => $event,
                            'menu_item_id' => $menuItem->id,
                            'customer_id' => $customer->id,
                            'recipient' => $customer->email,
                            'error' => $exception->getMessage(),
                        ]);
                    }
                }
            });
    }
}
