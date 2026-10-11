<?php

namespace App\Mail;

use App\Models\MenuItem;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProductAnnouncementMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public MenuItem $menuItem) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'New on the menu: '.$this->menuItem->name);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.products.added');
    }
}
