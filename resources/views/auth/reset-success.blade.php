<!DOCTYPE html>
<html>
<head>
    <title>Password Reset Successful</title>
    <style>
        body { background:#0f172a; display:flex; justify-content:center; align-items:center; height:100vh; color:white; font-family:sans-serif; }
        .box { background:white; color:#0f172a; padding:40px; border-radius:14px; text-align:center; width:350px; }
        a { display:inline-block; margin-top:20px; padding:10px 20px; background:#2563eb; color:white; text-decoration:none; border-radius:8px; }
    </style>
</head>
<body>
<div class="box">
    <h2>Password Reset Successful 🎉</h2>
    <p>Your password has been updated securely.</p>
    <a href="{{ config('app.frontend_url') ?? '/' }}">Go to Login</a>
</div>
</body>
</html>
