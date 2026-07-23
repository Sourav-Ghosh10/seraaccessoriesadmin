@extends('layouts.app')

@section('title', 'Create App Popup')

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
                <h3 style="margin: 0; font-size: 22px; font-weight: 700; color: #fff;">Create Popup Announcement</h3>
                <p style="margin: 2px 0 0 0; font-size: 13px; color: var(--text-muted);">Create a new image announcement for the mobile application</p>
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

    <form action="{{ route('app-popups.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        {{-- Section 1: Upload Image --}}
        <div class="form-section-title">
            <i class="fas fa-image" style="color: var(--primary);"></i> Upload Image Section
        </div>
        
        <div class="form-group" style="margin-bottom: 30px;">
            <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; font-weight: 600; display: block; margin-bottom: 8px;">
                Popup Announcement Image <span style="color: #ef4444;">*</span>
            </label>
            <div class="image-preview-container" onclick="document.getElementById('imageInput').click()">
                <input type="file" name="banner_image" id="imageInput" accept="image/*" required style="display: none;" onchange="previewImage(this)">
                
                <div id="previewPlaceholder" class="preview-placeholder">
                    <i class="fas fa-cloud-upload-alt"></i>
                    <div style="font-weight: 600; font-size: 15px; color: #fff;">Click to upload popup banner image</div>
                    <div style="font-size: 12px; color: var(--text-muted);">Recommended: PNG, JPG, WEBP (Max 10MB)</div>
                </div>

                <img id="imagePreview" src="#" alt="Popup Preview" style="display: none;">
            </div>
            @error('banner_image')
                <span style="color: #ef4444; font-size: 12px; margin-top: 5px; display: block;">{{ $message }}</span>
            @enderror
        </div>



        <div style="display: flex; justify-content: flex-end; gap: 15px; border-top: 1px solid rgba(255,255,255,0.08); padding-top: 25px;">
            <a href="{{ route('app-popups.index') }}" class="btn glass" style="padding: 12px 25px; text-decoration: none; color: #fff; font-weight: 500;">
                Cancel
            </a>
            <button type="submit" class="btn btn-primary" style="padding: 12px 35px; font-weight: 600; box-shadow: 0 10px 15px -3px rgba(154, 90, 58, 0.3);">
                <i class="fas fa-save" style="margin-right: 8px;"></i> Create & Publish Popup
            </button>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
function previewImage(input) {
    const preview = document.getElementById('imagePreview');
    const placeholder = document.getElementById('previewPlaceholder');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
            if (placeholder) {
                placeholder.style.display = 'none';
            }
        }
        
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endsection
