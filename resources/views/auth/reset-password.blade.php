@extends('layouts.guest')

@section('content')
    <div class="h-screen w-screen flex items-center justify-center">

        <div class="w-full max-w-md bg-white p-10">

            <div class="flex items-center gap-3 mb-8">
                <img src="{{ 'images/logo.png' }}" alt="Logo" class="w-25 h-20 object-cover"
                    onerror="this.style.display='none'">
            </div>

            <h2 class="text-lg font-semibold text-gray-800 mb-1">Set New Password</h2>
            <p class="text-sm text-gray-500 mb-6">Optional — you can also skip this and change it later from your
                profile.</p>

            @if ($errors->any())
                <div class="bg-red-50 text-red-600 text-sm px-4 py-2 rounded mb-4">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.reset.submit') }}">
                @csrf

                <div class="mb-3">
                    <label class="block text-xs font-semibold text-gray-600 mb-1">New Password</label>
                    <input type="password" name="password"
                        class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-[#0F6E8C] text-sm"
                        placeholder="••••••••" required>
                </div>

                <div class="mb-4">
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Confirm Password</label>
                    <input type="password" name="password_confirmation"
                        class="w-full px-4 py-2 border border-gray-300 rounded focus:outline-none focus:border-[#0F6E8C] text-sm"
                        placeholder="••••••••" required>
                </div>

                <div class="flex gap-2">
                    <a href="{{ route('admin.dashboard') }}"
                        class="flex-1 text-center py-2 border border-gray-300 text-gray-600 font-semibold rounded hover:bg-gray-50 transition text-sm">
                        Skip
                    </a>
                    <button type="submit"
                        class="flex-1 bg-p hover:bg-[#0E5A93] text-white font-semibold py-2 rounded transition text-sm">
                        Reset Password
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
