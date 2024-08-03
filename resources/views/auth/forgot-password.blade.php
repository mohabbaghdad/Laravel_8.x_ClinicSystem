
@extends('components.app')
@section('content')
    <div class="n-designs">
        <div class="container-fluid">
            <div class="form-box">
                <div class="button-box">
                    <h2>Reset Password</h2>
                </div>
                <p style="max-width: 80%; margin-left: 10%">
                    Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.
                </p>
                <form id="signup" class="input-group mt-3" method="POST" action="{{ route('password.email') }}" style="margin-top: -20%">
                        @csrf
                    <input class="input-field" placeholder="E-mail" type="email" name="email" :value="old('email')" required />
                    <div class="flex items-center justify-end mt-2">
                        <a class="underline text-sm text-gray-600 hover:text-gray-900" href="{{ route('login') }}">
                            Already registered?
                        </a>
                    </div>
                    <button type="submit" class="submit-btn mt-3" onclick="alert('\nPassword reset link sent has been sent to your mail\n\nPlease check your mail ');">
                        <span>Reset Password</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection
