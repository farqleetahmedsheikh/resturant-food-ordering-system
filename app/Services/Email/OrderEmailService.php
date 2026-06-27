<?php

namespace App\Services\Email;

use App\Mail\OrderConfirmedMail;
use App\Mail\OrderDeliveredMail;
use App\Mail\OrderPlacedMail;
use App\Models\Order;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OrderEmailService
{
    public function sendOrderPlaced(Order $order): void
    {
        $this->send($order, new OrderPlacedMail($this->mailReadyOrder($order)), 'order_placed');
    }

    public function sendOrderConfirmed(Order $order): void
    {
        $this->send($order, new OrderConfirmedMail($this->mailReadyOrder($order)), 'order_confirmed');
    }

    public function sendOrderDelivered(Order $order): void
    {
        $this->send($order, new OrderDeliveredMail($this->mailReadyOrder($order)), 'order_delivered');
    }

    private function send(Order $order, Mailable $mailable, string $event): void
    {
        $email = $this->recipientEmail($order);

        if (! $email) {
            return;
        }

        try {
            Mail::to($email, $order->customer_name ?: $order->user?->name)->send($mailable);
        } catch (\Throwable $exception) {
            Log::warning('Customer order email could not be sent.', [
                'event' => $event,
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'recipient' => $email,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function recipientEmail(Order $order): ?string
    {
        return $order->customer_email ?: $order->user?->email;
    }

    private function mailReadyOrder(Order $order): Order
    {
        return $order->loadMissing(['items', 'restaurant', 'user', 'rider', 'delivery']);
    }
}
