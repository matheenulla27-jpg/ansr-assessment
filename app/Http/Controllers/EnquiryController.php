<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreEnquiryRequest;
use App\Services\EnquiryService;
use Illuminate\Http\RedirectResponse;

class EnquiryController extends Controller
{
    public function store(StoreEnquiryRequest $request, EnquiryService $enquiries): RedirectResponse
    {
        $data = $request->validated();

        $enquiries->send(
            productId: (int) $data['product_id'],
            name: $data['name'],
            email: $data['email'],
            phone: $data['phone'] ?? null,
            message: $data['message'],
        );

        return back()->with('message', 'Thank you');
    }
}
