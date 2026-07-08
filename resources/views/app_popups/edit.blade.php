@extends('layouts.app')

@section('title', 'Edit App Popup')

@section('styles')
<style>
.form-card {
    max-width: 850px;
    margin: 0 auto;
}
.form-section-title {
    font-size: 16px;
    font-weight: 600;
    color: #fff;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 1px solid rgba(255,255,255,0.08);
    display: flex;
    align-items: center;
    gap: 8px;
}
.form-control:focus {
    outline: none;
    border-color: var(--primary) !important;
    box-shadow: 0 0 0 3px rgba(154, 90, 58, 0.15);
}
.image-preview-container {
    border: 2px dashed rgba(255,255,255,0.15);
    border-radius: 12px;
    padding: 25px;
    text-align: center;
    background: rgba(255,255,255,0.02);
    cursor: pointer;
    transition: all 0.2s;
    position: relative;
    overflow: hidden;
}
.image-preview-container:hover {
    border-color: var(--primary);
    background: rgba(255,255,255,0.04);
}
#imagePreview {
    max-width: 100%;
    max-height: 260px;
    border-radius: 8px;
    margin: 0 auto;
    box-shadow: 0 10px 20px rgba(0,0,0,0.3);
}
.preview-placeholder {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
    color: var(--text-muted);
}
.preview-placeholder i {
    font-size: 36px;
    color: var(--primary);
    opacity: 0.8;
}
/* Custom Select styling */
select.form-control {
    background-color: #1e293b !important;
    color: #fff !important;
}
select.form-control option {
    background-color: #0f172a;
    color: #fff;
}
</style>
@endsection

@section('content')
<div class="card form-card animate-fade">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <div style="width: 4px; height: 28px; background: var(--primary); border-radius: 2px;"></div>
            <div>
                <h3 style="margin: 0; font-size: 22px; font-weight: 700; color: #fff;">Edit Popup Announcement</h3>
                <p style="margin: 2px 0 0 0; font-size: 13px; color: var(--text-muted);">Update announcement: <strong style="color: #fff;">{{ $popup->title }}</strong></p>
            </div>
        </div>
        <a href="{{ route('app-popups.index') }}" class="btn glass" style="padding: 8px 16px; font-size: 13px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; color: #fff;">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger" style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); color: #ef4444; padding: 15px; border-radius: 10px; margin-bottom: 25px; font-size: 14px;">
            <div style="font-weight: 600; margin-bottom: 8px; display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-exclamation-triangle"></i> Please fix the following errors:
            </div>
            <ul style="margin: 0; padding-left: 20px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('app-popups.update', $popup->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        {{-- Section 1: Basic Info --}}
        <div class="form-section-title">
            <i class="fas fa-info-circle" style="color: var(--primary);"></i> Basic Information
        </div>
        
        <div class="form-group" style="margin-bottom: 20px;">
            <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; font-weight: 600; display: block; margin-bottom: 8px;">
                Popup Title <span style="color: #ef4444;">*</span>
            </label>
            <input type="text" name="title" class="form-control" value="{{ old('title', $popup->title) }}" required placeholder="e.g. Big Diwali Sale! Up to 50% Off" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #fff; padding: 12px 15px; width: 100%;">
            @error('title')
                <span style="color: #ef4444; font-size: 12px; margin-top: 5px; display: block;">{{ $message }}</span>
            @enderror
        </div>

        <div class="form-group" style="margin-bottom: 30px;">
            <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; font-weight: 600; display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                <span>Description <span style="color: #ef4444;">*</span></span>
                <span id="wordCountDisplay" style="color: var(--text-muted); font-size: 11px; font-weight: 400; text-transform: none;">0 / 300 characters</span>
            </label>
            <textarea name="description" id="descriptionInput" class="form-control" rows="4" required maxlength="300" placeholder="Enter detailed announcement message or instructions (max 300 characters)..." style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #fff; padding: 12px 15px; width: 100%; font-family: inherit;" oninput="updateCharCount(this)">{{ old('description', $popup->description) }}</textarea>
            @error('description')
                <span style="color: #ef4444; font-size: 12px; margin-top: 5px; display: block;">{{ $message }}</span>
            @enderror
        </div>

        {{-- Section 2: Status --}}
        <div class="form-section-title">
            <i class="fas fa-toggle-on" style="color: var(--primary);"></i> Display Settings
        </div>

        <div class="form-group" style="margin-bottom: 35px; max-width: 300px;">
            <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; font-weight: 600; display: block; margin-bottom: 8px;">
                Active Status <span style="color: #ef4444;">*</span>
            </label>
            <select name="status" class="form-control" required style="background: #1e293b; border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; color: #fff; padding: 12px 15px; width: 100%;">
                <option value="1" {{ old('status', $popup->status ? '1' : '0') == '1' ? 'selected' : '' }}>Active (On)</option>
                <option value="0" {{ old('status', $popup->status ? '1' : '0') == '0' ? 'selected' : '' }}>Inactive (Off)</option>
            </select>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 15px; border-top: 1px solid rgba(255,255,255,0.08); padding-top: 25px;">
            <a href="{{ route('app-popups.index') }}" class="btn glass" style="padding: 12px 25px; text-decoration: none; color: #fff; font-weight: 500;">
                Cancel
            </a>
            <button type="submit" class="btn btn-primary" style="padding: 12px 35px; font-weight: 600; box-shadow: 0 10px 15px -3px rgba(154, 90, 58, 0.3);">
                <i class="fas fa-save" style="margin-right: 8px;"></i> Update & Publish Popup
            </button>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
function updateCharCount(textarea) {
    const charCount = textarea.value.length;
    const counter = document.getElementById('wordCountDisplay');
    
    if (counter) {
        counter.textContent = `${charCount} / 300 characters`;
        if (charCount > 300) {
            counter.style.color = '#ef4444';
            textarea.style.borderColor = '#ef4444';
        } else {
            counter.style.color = 'var(--text-muted)';
            textarea.style.borderColor = 'rgba(255,255,255,0.1)';
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const textarea = document.getElementById('descriptionInput');
    if (textarea) {
        updateCharCount(textarea);
    }
});
</script>
@endsection
