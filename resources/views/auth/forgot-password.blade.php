@extends('layouts.guest')

@section('content')
    <div class="h-screen w-screen flex items-center justify-center">

        <div class="w-full max-w-md bg-white p-10">

            <!-- Logo -->
            <div class="flex items-center gap-3 mb-8">
                <img src="{{ 'images/logo.png' }}" alt="Logo" class="w-25 h-20 object-cover"
                    onerror="this.style.display='none'">
            </div>

            <h2 class="text-lg font-semibold text-gray-800 mb-1">Forgot Password</h2>
            <p class="text-sm text-gray-500 mb-6">Enter your phone number and we'll send you a code to reset your
                password.</p>

            @if ($errors->any())
                <div class="bg-red-50 text-red-600 text-sm px-4 py-2 rounded mb-4">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.send-otp') }}">
                @csrf

                <div class="mb-4">
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Phone Number</label>
                    <input type="text" name="phone" value="{{ old('phone') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-[#0F6E8C] text-sm"
                        placeholder="0XX XXX XXX" required autofocus>
                    <div class="mt-2 text-xs text-gray-400">
                        <span class="block">cashier: <span class="font-semibold text-gray-600">066777999</span></span>
                        <span class="block">admin: <span class="font-semibold text-gray-600">0888093342</span></span>
                    </div>
                </div>


                <button type="submit"
                    class="w-full bg-p hover:bg-[#0E5A93] text-white font-semibold py-2 rounded transition text-sm">
                    Send Code
                </button>

                <div class="text-center mt-4">
                    <a href="{{ route('login') }}" class="text-p text-sm">Back to Login</a>
                </div>
            </form>
        </div>
    </div>
@endsection
