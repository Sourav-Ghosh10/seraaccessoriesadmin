@extends('layouts.app')

@section('title', 'System Settings')

@section('content')
<div class="card animate-fade" style="max-width: 600px; margin: 0 auto;">
    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 30px;">
        <div style="width: 4px; height: 24px; background: var(--primary); border-radius: 2px;"></div>
        <h3 style="margin: 0; font-size: 20px; font-weight: 700; color: #fff;">Global Application Settings</h3>
    </div>

    @if(session('success'))
        <div class="alert alert-success" style="background: rgba(34, 197, 94, 0.1); border: 1px solid rgba(34, 197, 94, 0.2); color: #22c55e; padding: 15px; border-radius: 12px; margin-bottom: 25px; font-size: 14px;">
            <i class="fas fa-check-circle" style="margin-right: 8px;"></i> {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('settings.update') }}" method="POST">
        @csrf
        
        <div class="form-group" style="margin-bottom: 25px;">
            <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; font-weight: 600; display: block; margin-bottom: 10px;">WhatsApp Contact Number</label>
            <div style="position: relative;">
                <span style="position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 14px; font-weight: 500;">
                    <i class="fab fa-whatsapp" style="color: #25d366; font-size: 16px; margin-right: 5px;"></i>
                </span>
                <input type="text" name="whatsapp_number" class="form-control" value="{{ old('whatsapp_number', $whatsappNumber) }}" 
                       style="background: rgba(255,255,255,0.03); border: 1px solid var(--glass-border); border-radius: 8px; color: #fff; padding: 12px 15px 12px 40px; font-size: 15px; width: 100%;" 
                       placeholder="e.g. 919876543210">
            </div>
            <p style="font-size: 12px; color: var(--text-muted); margin-top: 8px; line-height: 1.4;">
                This WhatsApp number is used in the mobile application to let dealers/salesmen contact support. Enter the number with country code without any spaces or symbols (e.g. <code>919876543210</code>).
            </p>
            @error('whatsapp_number')
                <span style="color: #ef4444; font-size: 12px; margin-top: 5px; display: block;">{{ $message }}</span>
            @enderror
        </div>

        <div style="display: flex; justify-content: flex-end; margin-top: 35px; border-top: 1px solid rgba(255,255,255,0.05); padding-top: 20px;">
            <button type="submit" class="btn btn-primary" style="padding: 12px 35px; box-shadow: 0 10px 15px -3px rgba(154, 90, 58, 0.3); font-weight: 600;">
                <i class="fas fa-save" style="margin-right: 8px;"></i> Save Settings
            </button>
        </div>
    </form>
</div>

<style>
.form-control:focus { outline: none; border-color: var(--primary) !important; background: rgba(255,255,255,0.05) !important; }
</style>
@endsection
