<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PayMongo Payment</title>
    <script>
        function sendPaymentRequest(event) {
            event.preventDefault(); // Prevent form from reloading the page

            let formData = {
                customer_name: document.getElementById("customer_name").value.trim(),
                user_id: document.getElementById("user_id").value.trim(),
                order_id: document.getElementById("order_id").value.trim(),
                payment_method: document.getElementById("payment_method").value,
                fcm_token: document.getElementById("fcm_token").value.trim(),
                total_price: parseFloat(document.getElementById("total_price").value)
            };

            if (isNaN(formData.total_price) || formData.total_price <= 0) {
                alert("Invalid total price");
                return;
            }

            // Disable button to prevent multiple clicks
            let payButton = document.getElementById("pay_button");
            payButton.disabled = true;
            payButton.innerText = "Processing...";

            // Fetch API Request
            fetch("http://localhost/EZMartOrderingSystem/api/paymongo_status.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify(formData)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert("Redirecting to payment...");
                        window.location.href = data.checkoutUrl; // Open checkout page
                    } else {
                        alert("Error: " + data.message);
                    }
                })
                .catch(error => {
                    console.error("Error:", error);
                    alert("Payment request failed. Check the console for details.");
                })
                .finally(() => {
                    payButton.disabled = false;
                    payButton.innerText = "Pay Now";
                });
        }
    </script>
</head>

<body>
    <h2>PayMongo Payment</h2>
    <form onsubmit="sendPaymentRequest(event)">
        <label>Customer Name:</label>
        <input type="text" id="customer_name" required><br><br>

        <label>User ID:</label>
        <input type="text" id="user_id" required><br><br>

        <label>Order ID:</label>
        <input type="text" id="order_id" required><br><br>

        <label>Payment Method:</label>
        <select id="payment_method" required>
            <option value="Gcash">Gcash</option>
            <option value="PayMaya">PayMaya</option>
        </select><br><br>

        <label>FCM Token (Optional):</label>
        <input type="text" id="fcm_token"><br><br>

        <label>Total Price:</label>
        <input type="number" id="total_price" step="0.01" required><br><br>

        <button type="submit" id="pay_button">Pay Now</button>
    </form>
</body>

</html>