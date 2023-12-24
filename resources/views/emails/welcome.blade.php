<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Welcome to Our Application</title>
</head>
<body>
    <h1>Welcome to Our Application, {{ $user->name }}!</h1>
    <p>We are excited to have you on board.</p>
    <p>Please click the link below to set your password and get started:</p>
    <a href="{{ route('password.reset', ['token' => $token]) }}">Set Your Password</a>
</body>
</html>

