@extends('layouts.app')

@section('content')
    <div class="auth-page">
        <div class="auth-card glass animate-fade">
            <div class="logo" style="justify-content: center; margin-bottom: 20px; flex-direction: column;">
                <img src="{{ asset('assets/images/logo.jpg') }}" alt="Logo" style="width: 100px;">
            </div>
            <h3 style="text-align: center; margin-bottom: 30px; font-weight: 600;">Welcome Back</h3>

            <form action="{{ route('login.post') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" placeholder="admin@shera.com" required value="{{ old('email') }}">
                    @error('email')
                        <div style="color: var(--danger); font-size: 13px; font-weight: 600; margin-top: 5px;">
                            <i class="fas fa-exclamation-circle"></i> {{ $message }}
                        </div>
                    @enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="••••••••" required>
                    @error('password')
                        <div style="color: var(--danger); font-size: 13px; font-weight: 600; margin-top: 5px;">
                            <i class="fas fa-exclamation-circle"></i> {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="form-group"
                    style="display: flex; justify-content: space-between; align-items: center; margin-top: -10px;">
                    <label
                        style="font-size: 12px; color: var(--text-muted); cursor: pointer; display: flex; align-items: center; gap: 5px;">
                        <input type="checkbox" name="remember"> Remember me
                    </label>
                    <a href="#" style="font-size: 12px; color: var(--primary); text-decoration: none;">Forgot Password?</a>
                </div>

                <button type="submit" class="btn btn-primary"
                    style="width: 100%; justify-content: center; margin-top: 20px; padding: 14px;">
                    Sign In
                </button>
            </form>

            <div style="margin-top: 30px; text-align: center; font-size: 13px; color: var(--text-muted);">
                Demo Roles:
                <span style="color: var(--primary); cursor: pointer;">Admin</span> •
                <span style="color: var(--primary); cursor: pointer;">Operations</span> •
                <span style="color: var(--primary); cursor: pointer;">Account</span>
            </div>
        </div>
    </div>
@endsection