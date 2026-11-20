<?php

declare(strict_types=1);

namespace App\Mail;

use App\DTOs\ProductData;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EnquiryMailable extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ProductData $product,
        public string $name,
        public string $email,
        public ?string $phone,
        public string $customerMessage,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Product enquiry: '.$this->product->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.enquiry',
            with: [
                'product' => $this->product,
                'name' => $this->name,
                'email' => $this->email,
                'phone' => $this->phone,
                'customerMessage' => $this->customerMessage,
            ],
        );
    }
}
