<!DOCTYPE html>
<html>
<head>
    <title>Stripe Checkout Test</title>
</head>
<body>

<h2>Stripe Checkout Test</h2>

<form action="{{ route('stripe.checkout') }}" method="POST">
    @csrf

    <label>Booking Type:</label>
    <select name="booking_type" required>
        <option value="room">Room</option>
        <option value="service">Service</option>
    </select>
    <br><br>

    <label>Booking ID:</label>
    <input type="number" name="booking_id" placeholder="1" required>
    <br><br>

    <label>Amount (PHP):</label>
    <input type="number" name="amount" placeholder="5000" required>
    <br><br>

    <button type="submit">Pay with Stripe</button>
</form>

</body>
</html>
