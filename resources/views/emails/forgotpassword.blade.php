<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Your Password</title>
    <style>
        /* Add your CSS styles here */
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
        }
        a {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
        }
        a:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <h1>Reset Your Password</h1>
    <p>Hello {{ $user->name }},</p>
    <p>You are receiving this email because we received a password reset request for your account. If you did not request a password reset, no further action is required.</p>
    
    <p>To reset your password, please click the button below:</p>
    <a href="{{ route('password.reset', ['token' => $token]) }}" style="color: white;">Reset Password</a>

    <p>If you’re having trouble clicking the "Reset Password" button, copy and paste the URL below into your web browser:</p>
    <p><a href="{{ route('password.reset', ['token' => $token]) }}">{{ route('password.reset', ['token' => $token]) }}</a></p>

    <p>If you did not request a password reset, please ignore this email or contact support if you have questions.</p>

    <p>Thank you,<br>The {{ config('app.name') }} Team</p>
</body>
</html>
