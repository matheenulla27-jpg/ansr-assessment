<h1>Product enquiry</h1>

<h2>{{ $product->title }}</h2>
<p>Product ID: {{ $product->id }}</p>
<p>Category: {{ $product->category }}</p>

<h2>Customer details</h2>
<p>Name: {{ $name }}</p>
<p>Email: {{ $email }}</p>
<p>Phone: {{ $phone ?: 'Not provided' }}</p>

<h2>Message</h2>
<p>{{ $customerMessage }}</p>
