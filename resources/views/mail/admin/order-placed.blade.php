<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>New Order Received – Medishop – Order #{{ $order->order_code }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #0f172a;
            font-family: Arial, Helvetica, sans-serif;
        }

        table {
            border-collapse: collapse;
        }

        .container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #020617;
            border-radius: 12px;
            border: 1px solid #581c87;
            overflow: hidden;
        }

        .header {
            padding: 20px;
            border-bottom: 1px solid #581c87;
        }

        .brand {
            color: #f8fafc;
            font-size: 18px;
            font-weight: bold;
        }

        .subtext {
            color: #cbd5f5;
            font-size: 13px;
            margin-top: 4px;
        }

        .content {
            padding: 24px;
            color: #e5e7eb;
        }

        h1 {
            color: #f8fafc;
            font-size: 22px;
            margin: 0 0 10px 0;
        }

        h2 {
            color: #f8fafc;
            font-size: 16px;
            margin-top: 30px;
        }

        p {
            color: #cbd5f5;
            font-size: 14px;
            line-height: 1.6;
        }

        .box {
            border: 1px solid #581c87;
            border-radius: 10px;
            padding: 16px;
            margin-bottom: 24px;
        }

        .label {
            color: #a855f7;
            font-size: 11px;
            text-transform: uppercase;
            margin-bottom: 2px;
        }

        .value {
            color: #f8fafc;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .items th {
            text-align: left;
            font-size: 11px;
            color: #cbd5f5;
            padding: 10px;
            background-color: #3b0764;
        }

        .items td {
            padding: 10px;
            font-size: 13px;
            color: #e5e7eb;
            border-bottom: 1px solid #581c87;
        }

        .items th:last-child,
        .items td:last-child {
            text-align: right;
        }

        .footer {
            padding: 16px;
            border-top: 1px solid #581c87;
            font-size: 12px;
            color: #94a3b8;
            text-align: center;
        }
    </style>
</head>

<body>

<table width="100%">
    <tr>
        <td align="center">

            <table class="container" width="100%">
                <tr>
                    <td class="header">
                        <div class="brand">Medishop Order Alert</div>
                        <div class="subtext">
                            New order received — Order {{ $order->order_code }}
                        </div>
                    </td>
                </tr>

                <tr>
                    <td class="content">

                        <h1>New Order Received</h1>
                        <p>
                            A new order has been placed on your Medishop store.
                            Below are the order details:
                        </p>

                        <div class="box">
                            <div class="label">Order Code</div>
                            <div class="value">{{ $order->order_code }}</div>

                            <div class="label">Order Date</div>
                            <div class="value">{{ $order->created_at->format('d M Y') }}</div>

                            <div class="label">Customer</div>
                            <div class="value">
                                {{ $order->customer_name }}<br>
                                {{ $order->mail }}<br>
                                {{ $order->mob_no }}
                            </div>

                            <div class="label">Payment Method</div>
                            <div class="value">{{ $order->payment_method }}</div>
                        </div>

                        <h2>Order Items</h2>

                        <table width="100%" class="items">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Qty</th>
                                    <th>Price</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->orderItems as $item)
                                <tr>
                                    <td>{{ $item->item_name }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>NPR {{ $item->price }}</td>
                                    <td>NPR {{ $item->total }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <p style="margin-top:20px;">
                            <strong>Total Amount:</strong> NPR {{ $order->price }}
                        </p>

                    </td>
                </tr>

                <tr>
                    <td class="footer">
                        © {{ date('Y') }} Medishop · This is an automated order notification.
                    </td>
                </tr>
            </table>

        </td>
    </tr>
</table>

</body>
</html>
