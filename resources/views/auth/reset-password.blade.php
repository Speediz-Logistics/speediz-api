<x-guest-layout>
    <div>
        <x-slot name="logo">
        </x-slot>

        <form method="POST" action="{{ url('/api/delivery/reset-password') }}">
            @csrf

            <input type="hidden" name="token" value="{{ $token }}">
            <input type="hidden" name="email" value="{{ $email }}">

            <input type="password" name="password" placeholder="New Password" required>
            <br><br>
            <input type="password" name="password_confirmation" placeholder="Confirm Password" required>
            <br><br>

            <button type="submit">Reset Password</button>
        </form>
    </div>
</x-guest-layout>
