<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\ProductRepositoryInterface;
use App\DTOs\ProductData;
use App\Mail\EnquiryMailable;
use Illuminate\Support\Facades\Mail;

class EnquiryService
{
    public function __construct(
        private readonly ProductRepositoryInterface $products,
    ) {}

    public function send(
        int $productId,
        string $name,
        string $email,
        ?string $phone,
        string $message,
    ): void {
        $product = $this->products->find($productId);

        if (! $product instanceof ProductData) {
            abort(404);
        }

        Mail::to((string) config('services.sales_inbox'))->send(
            new EnquiryMailable($product, $name, $email, $phone, $message),
        );
    }
}
