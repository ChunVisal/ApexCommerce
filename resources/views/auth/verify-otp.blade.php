@extends('layouts.guest')

@section('content')
    <div class="h-screen w-screen flex items-center justify-center" x-data="otpForm()">

        <div class="w-full max-w-md bg-white p-10">

            <div class="flex items-center gap-3 mb-8">
                <img src="/images/logo.png" alt="Logo" class="w-25 h-20 object-cover" onerror="this.style.display='none'">
            </div>

            <h2 class="text-lg font-semibold text-gray-800 mb-1">Enter Verification Code</h2>
            <p class="text-sm text-gray-500 mb-6">We sent a 6-digit code to your phone.</p>

            @if ($errors->any())
                <div class="bg-red-50 text-red-600 text-sm px-4 py-2 rounded mb-4">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.verify-otp.submit') }}" x-ref="form">
                @csrf
                <input type="hidden" name="otp" :value="digits.join('')">

                <div class="flex justify-center gap-2 mb-4">
                    <template x-for="(digit, index) in digits" :key="index">
                        <input type="text" inputmode="numeric" maxlength="1" x-model="digits[index]"
                            @input="onInput($event, index)" @keydown.backspace="onBackspace($event, index)"
                            @paste="onPaste($event)"
                            class="otp-box w-12 h-14 text-center text-xl font-bold border border-gray-300 rounded focus:outline-none focus:border-[#0F6E8C]">
                    </template>
                </div>

                {{-- Countdown Timer --}}
                <p class="text-center text-xs text-gray-500 mb-4">
                    <span x-show="secondsLeft > 0">
                        Code expires in <span class="font-semibold text-red-400" x-text="formattedTime"></span>
                    </span>
                    <span x-show="secondsLeft <= 0" class="text-red-500 font-semibold">
                        Code expired. Please request a new one.
                    </span>
                </p>

                <button type="submit" :disabled="digits.join('').length !== 6"
                    class="w-full bg-p hover:bg-[#0E5A93] text-white font-semibold py-2 rounded transition text-sm disabled:opacity-40 disabled:cursor-not-allowed">
                    Verify
                </button>

                <div class="text-center mt-4">
                    <a href="{{ route('forget-password') }}" class="text-p text-sm">Resend Code</a>
                </div>
            </form>
        </div>
    </div>
@endsection

<script>
    function otpForm() {
        return {
            digits: ['', '', '', '', '', ''],
            secondsLeft: 0,

            get formattedTime() {
                const m = Math.floor(this.secondsLeft / 60);
                const s = this.secondsLeft % 60;

                return m + ':' + s.toString().padStart(2, '0');
            },

            init() {
                const expiresAt = new Date('{{ $otpExpiresAt }}').getTime();

                const updateTimer = () => {
                    const now = new Date().getTime();
                    const remaining = Math.floor((expiresAt - now) / 1000);

                    this.secondsLeft = remaining > 0 ? remaining : 0;
                };

                updateTimer();

                const timer = setInterval(() => {
                    updateTimer();

                    if (this.secondsLeft <= 0) {
                        clearInterval(timer);
                    }
                }, 1000);
            },

            onInput(event, index) {
                const value = event.target.value.replace(/[^0-9]/g, '');

                this.digits[index] = value.slice(-1);

                // Get ALL OTP inputs from the form
                const boxes = this.$root.querySelectorAll('.otp-box');

                // Move to next box
                if (value && index < 5) {
                    boxes[index + 1].focus();
                }

                // Submit automatically after 6 digits
                if (this.digits.join('').length === 6) {
                    this.$nextTick(() => {
                        this.$refs.form.requestSubmit();
                    });
                }
            },

            onBackspace(event, index) {
                if (!this.digits[index] && index > 0) {
                    const boxes = this.$root.querySelectorAll('.otp-box');

                    boxes[index - 1].focus();
                }
            },

            onPaste(event) {
                event.preventDefault();

                const pasted = event.clipboardData
                    .getData('text')
                    .replace(/[^0-9]/g, '')
                    .slice(0, 6);

                this.digits = pasted
                    .split('')
                    .concat(['', '', '', '', '', ''])
                    .slice(0, 6);

                const boxes = this.$root.querySelectorAll('.otp-box');

                if (pasted.length === 6) {
                    this.$nextTick(() => {
                        this.$refs.form.requestSubmit();
                    });
                } else if (pasted.length > 0) {
                    this.$nextTick(() => {
                        boxes[pasted.length].focus();
                    });
                }
            },
        };
    }
</script>
