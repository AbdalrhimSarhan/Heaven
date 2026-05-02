<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Order Confirmation</title>
</head>
<body>
    <h2>Your order has been confirmed ✅</h2>

    <p><strong>Order ID:</strong> {{ $order->id }}</p>
    <p><strong>Invoice Number:</strong> {{ $invoice->invoice_number }}</p>
    <p><strong>Total Price:</strong> {{ $invoice->total_price }}</p>
    <p><strong>Generated At:</strong> {{ $invoice->generated_at }}</p>

    <hr>

    <p>Thank you for shopping with us.</p>
</body>
</html>