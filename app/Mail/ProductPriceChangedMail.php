<?php

namespace App\Mail;

use App\Models\MenuItem;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProductPriceChangedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public MenuItem $menuItem, public string $previousPrice) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Menu update: '.$this->menuItem->name.' price changed');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.products.price-changed');
    }
}
