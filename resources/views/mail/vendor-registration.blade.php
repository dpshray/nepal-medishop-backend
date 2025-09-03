<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Vendor Account Created</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f8f9fa;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 30px auto;
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .header {
            background: #4CAF50;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .content {
            padding: 25px;
            color: #333;
        }
        .credentials {
            background: #f1f1f1;
            padding: 15px;
            border-radius: 6px;
            margin: 20px 0;
            font-size: 15px;
        }
        .btn {
            display: inline-block;
            padding: 12px 20px;
            background: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
        }
        .footer {
            text-align: center;
            padding: 15px;
            font-size: 12px;
            color: #777;
            background: #f8f9fa;
        }
        .clickme {
            background-color: dodgerblue;
            padding: 8px 20px;
            text-decoration:none;
            font-weight:bold;
            border-radius:5px;
            cursor:pointer;
            color: #fff;
        }
        .clickme-parent-el{
            text-align: center;
            padding: 20px 0px 15px 0px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Welcome to {{ env('APP_NAME') }}</h2>
        </div>
        <div class="content">
            <p>Hello <strong>{{ $vendor->store_name }}</strong>,</p>
            <p>Your vendor account has been created by our admin. Please verifying your email by clicking on button below.</p>
            
            <div class="credentials">
                <p><strong>Username:</strong> {{ $vendor->user->name }}</p>
                <p><strong>Password:</strong> {{ $password }}</p>
                <div class="clickme-parent-el">
                    <a href="{{ $link }}" class="clickme">Verify Your Email Address</a>
                </div>
            </div>
            
            <p>If you face any issues, feel free to contact our support team.</p>
            
            <p>Best regards,<br>
                {{ env('APP_NAME') }} Team</p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} {{ env('APP_NAME') }}. All rights reserved.
        </div>
    </div>
</body>
</html>
