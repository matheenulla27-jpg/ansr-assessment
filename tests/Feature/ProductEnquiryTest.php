<?php

declare(strict_types=1);

use App\Contracts\ProductRepositoryInterface;
use App\DTOs\ProductData;
use App\Mail\EnquiryMailable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

final readonly class ProductEnquiryRepositoryFixture implements ProductRepositoryInterface
{
    /**
     * @param  list<ProductData>  $products
     */
    public function __construct(private array $products = []) {}

    /**
     * @return list<ProductData>
     */
    public function all(): array
    {
        return $this->products;
    }

    public function find(int $id): ?ProductData
    {
        foreach ($this->products as $product) {
            if ($product->id === $id) {
                return $product;
            }
        }

        return null;
    }
}

function productEnquiryProduct(): ProductData
{
    return new ProductData(
        id: 12,
        title: 'Desk Lamp',
        description: 'An adjustable reading lamp.',
        category: 'home',
        price: 35.0,
        image: 'https://example.test/products/12.png',
        ratingRate: 4.3,
        ratingCount: 18,
    );
}

function validProductEnquiry(ProductData $product): array
{
    return [
        'product_id' => $product->id,
        'name' => 'Taylor Customer',
        'email' => 'taylor@example.test',
        'message' => 'I would like to know more about this item.',
    ];
}

it('sends an enquiry with product and customer details with or without a phone number', function (?string $phone): void {
    Http::preventStrayRequests();
    Mail::fake();

    $product = productEnquiryProduct();
    app()->instance(ProductRepositoryInterface::class, new ProductEnquiryRepositoryFixture([$product]));
    config(['services.sales_inbox' => 'sales-inbox@example.test']);

    $data = validProductEnquiry($product);

    if ($phone !== null) {
        $data['phone'] = $phone;
    }

    $this->from(route('products.show', ['id' => $product->id]))
        ->post(route('products.enquiries.store'), $data)
        ->assertRedirect(route('products.show', ['id' => $product->id]))
        ->assertSessionHas('message', 'Thank you');

    Mail::assertSent(EnquiryMailable::class, fn (EnquiryMailable $mail): bool => $mail->hasTo('sales-inbox@example.test')
        && $mail->product->id === $product->id
        && $mail->product->title === 'Desk Lamp'
        && $mail->name === 'Taylor Customer'
        && $mail->email === 'taylor@example.test'
        && $mail->phone === $phone
        && $mail->customerMessage === 'I would like to know more about this item.'
        && str_contains($mail->render(), $mail->customerMessage));
})->with([
    'without phone' => [null],
    'with phone' => ['+1 555 0100'],
]);

it('returns 404 and sends no email for an unknown product enquiry', function (): void {
    Http::preventStrayRequests();
    Mail::fake();
    app()->instance(ProductRepositoryInterface::class, new ProductEnquiryRepositoryFixture);

    $this->post(route('products.enquiries.store'), [
        ...validProductEnquiry(productEnquiryProduct()),
        'product_id' => 999,
    ])->assertNotFound();

    Mail::assertNothingSent();
});

it('rejects invalid enquiry values without sending an email', function (array $invalidValues, array $errorFields): void {
    Http::preventStrayRequests();
    Mail::fake();

    $product = productEnquiryProduct();
    app()->instance(ProductRepositoryInterface::class, new ProductEnquiryRepositoryFixture([$product]));
    $data = [...validProductEnquiry($product), ...$invalidValues];

    $this->from(route('products.show', ['id' => $product->id]))
        ->post(route('products.enquiries.store'), $data)
        ->assertSessionHasErrors($errorFields);

    Mail::assertNothingSent();
})->with([
    'missing product ID' => [['product_id' => null], ['product_id']],
    'non-integer product ID' => [['product_id' => 'not-an-id'], ['product_id']],
    'missing name' => [['name' => ''], ['name']],
    'invalid name type' => [['name' => ['Taylor']], ['name']],
    'overlong name' => [['name' => str_repeat('n', 121)], ['name']],
    'malformed email' => [['email' => 'not-an-email'], ['email']],
    'overlong email' => [['email' => str_repeat('a', 244).'@example.test'], ['email']],
    'invalid phone type' => [['phone' => ['555-0100']], ['phone']],
    'overlong phone' => [['phone' => str_repeat('1', 41)], ['phone']],
    'missing message' => [['message' => ''], ['message']],
    'overlong message' => [['message' => str_repeat('m', 2001)], ['message']],
]);
