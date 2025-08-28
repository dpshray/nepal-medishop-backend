
<!DOCTYPE html>
<html>
<head>
    <title>Reset Your Password</title>
</head>
<body>
    <div class="title"><h1>WhiteandBlack Trend</h1></div>
    <div class="header">
    <h5>Hello {{ $user->name}}!</h5>
    </div>
    <p>We received a request to reset your password for your WhiteandBlack Trend.</p>
    <p><strong>Your Token:</strong> {{ $token }}</p>
    {{-- <p>
        <a href="{{ $resetUrl }}" style="background: #3490dc; color: white; padding: 10px 20px; text-decoration: none;">Reset Password</a>
    </p> --}}
    <p>If you did not request a password reset, please ignore this email.</p>
    <p>Regards,<br>WhiteandBlack Trend</p>
</body>
<style>
.title{
    text-align: center;
}
.header{
    font-weight: bold;
}

</style>
</html>
