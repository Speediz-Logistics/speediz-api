<!DOCTYPE html>
<html>
<head>
    <title>Password Reset Failed</title>
    <style>
        body { background:#0f172a; display:flex; justify-content:center; align-items:center; height:100vh; color:white; font-family:sans-serif; }
        .box { background:white; color:#0f172a; padding:40px; border-radius:14px; text-align:center; width:350px; }
        a { display:inline-block; margin-top:20px; padding:10px 20px; background:#ef4444; color:white; text-decoration:none; border-radius:8px; }
    </style>
</head>
<body>
<div class="box">
    <h2>Reset Failed ❌</h2>
    <p>{{ $message ?? 'The reset link is invalid or expired.' }}</p>
    <a href="{{ url('/forgot-password') }}">Try Again</a>
</div>
</body>
</html>
