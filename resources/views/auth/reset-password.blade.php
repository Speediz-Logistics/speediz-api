<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
        * { box-sizing: border-box; font-family: system-ui, -apple-system, BlinkMacSystemFont, sans-serif; }

        body {
            margin: 0;
            min-height: 100vh;
            background: linear-gradient(135deg, #0f172a, #1e293b);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .card {
            width: 100%;
            max-width: 420px;
            background: #fff;
            border-radius: 14px;
            padding: 32px;
            box-shadow: 0 20px 40px rgba(0,0,0,.2);
        }

        h2 {
            margin: 0 0 10px;
            text-align: center;
            color: #0f172a;
        }

        p {
            margin: 0 0 25px;
            text-align: center;
            color: #64748b;
            font-size: 14px;
        }

        .input-group {
            margin-bottom: 18px;
        }

        input {
            width: 100%;
            padding: 12px 14px;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            font-size: 15px;
            transition: .2s;
        }

        input:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37,99,235,.15);
        }

        button {
            width: 100%;
            padding: 12px;
            background: #2563eb;
            border: none;
            border-radius: 8px;
            color: white;
            font-weight: 600;
            cursor: pointer;
            transition: .2s;
        }

        button:hover {
            background: #1d4ed8;
        }

        .footer-text {
            margin-top: 18px;
            text-align: center;
            font-size: 13px;
            color: #94a3b8;
        }
    </style>
</head>
<body>

<div class="card">
    <h2>Reset Your Password</h2>
    <p>Create a strong new password to secure your account.</p>

    <form method="POST" action="{{ url('/api/delivery/reset-password') }}">
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">
        <input type="hidden" name="email" value="{{ $email }}">

        <div class="input-group">
            <input type="password" name="password" placeholder="New Password" required>
        </div>

        <div class="input-group">
            <input type="password" name="password_confirmation" placeholder="Confirm Password" required>
        </div>

        <button type="submit">Reset Password</button>
    </form>

    <div class="footer-text">
        &copy; {{ date('Y') }} MAD by La Seavyong
    </div>
</div>

</body>
</html>
