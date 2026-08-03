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

<div class="card animate-fade danger-card" style="max-width: 600px; margin: 30px auto 0; border: 1px solid rgba(220, 53, 69, 0.3);">
    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 30px;">
        <div style="width: 4px; height: 24px; background: #dc3545; border-radius: 2px;"></div>
        <h3 style="margin: 0; font-size: 20px; font-weight: 700; color: #dc3545;">DANGER</h3>
    </div>
    
    <div class="form-group" style="margin-bottom: 25px;">
        <label class="form-label" style="color: var(--text-muted); font-size: 12px; text-transform: uppercase; font-weight: 600; display: block; margin-bottom: 10px;">Select Date Range to Delete Data</label>
        <div style="display: flex; gap: 15px;">
            <div style="flex: 1; position: relative;">
                <label style="color: var(--text-muted); font-size: 11px; margin-bottom: 8px; display: block; text-transform: uppercase;">From</label>
                <input type="date" name="start_date" id="start_date" class="form-control" 
                       style="background: rgba(255,255,255,0.03); border: 1px solid var(--glass-border); border-radius: 8px; color: #fff; padding: 12px 15px; font-size: 15px; width: 100%;">
            </div>
            <div style="flex: 1; position: relative;">
                <label style="color: var(--text-muted); font-size: 11px; margin-bottom: 8px; display: block; text-transform: uppercase;">To</label>
                <input type="date" name="end_date" id="end_date" class="form-control" 
                       style="background: rgba(255,255,255,0.03); border: 1px solid var(--glass-border); border-radius: 8px; color: #fff; padding: 12px 15px; font-size: 15px; width: 100%;">
            </div>
        </div>
    </div>

    <div style="display: flex; justify-content: flex-end; margin-top: 35px; border-top: 1px solid rgba(220, 53, 69, 0.2); padding-top: 20px;">
        <button type="button" id="btn-delete-data" class="btn btn-danger" style="padding: 12px 35px; box-shadow: 0 10px 15px -3px rgba(220, 53, 69, 0.3); font-weight: 600; background: #dc3545; border-color: #dc3545; color: white;">
            <i class="fas fa-trash-alt" style="margin-right: 8px;"></i> Delete
        </button>
    </div>
</div>

<style>
.form-control:focus { outline: none; border-color: var(--primary) !important; background: rgba(255,255,255,0.05) !important; }
.btn-danger { transition: all 0.3s ease !important; }
.btn-danger:hover { background: #c82333 !important; border-color: #bd2130 !important; transform: translateY(-2px) !important; box-shadow: 0 12px 20px -3px rgba(220, 53, 69, 0.5) !important; }
.danger-card { transition: border-color 0.3s ease, box-shadow 0.3s ease; }
.danger-card:hover { border-color: #dc3545 !important; box-shadow: 0 10px 25px -5px rgba(220, 53, 69, 0.2) !important; }
</style>

<script>
    document.getElementById('btn-delete-data').addEventListener('click', function() {
        var startDate = document.getElementById('start_date').value;
        var endDate = document.getElementById('end_date').value;

        if (!startDate || !endDate) {
            Swal.fire({
                icon: 'warning',
                title: 'Missing Dates',
                text: 'Please select both from and to dates.',
                confirmButtonColor: '#dc3545',
                background: '#1e222d',
                color: '#fff'
            });
            return;
        }

        if (startDate > endDate) {
            Swal.fire({
                icon: 'warning',
                title: 'Invalid Range',
                text: 'Start date cannot be greater than end date.',
                confirmButtonColor: '#dc3545',
                background: '#1e222d',
                color: '#fff'
            });
            return;
        }

        Swal.fire({
            title: 'Are you sure?',
            text: "You are about to delete Invoice Management, Delivery Status, and Orders List data from " + startDate + " to " + endDate + ". This action cannot be undone!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!',
            background: '#1e222d',
            color: '#fff'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('{{ route('settings.delete-data') }}', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        start_date: startDate,
                        end_date: endDate
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: 'Deleted!',
                            text: data.message,
                            icon: 'success',
                            background: '#1e222d',
                            color: '#fff',
                            confirmButtonColor: '#dc3545'
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: data.message || 'Something went wrong.',
                            icon: 'error',
                            background: '#1e222d',
                            color: '#fff',
                            confirmButtonColor: '#dc3545'
                        });
                    }
                })
                .catch(error => {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Something went wrong. Please try again later.',
                        icon: 'error',
                        background: '#1e222d',
                        color: '#fff',
                        confirmButtonColor: '#dc3545'
                    });
                });
            }
        });
    });
</script>
@endsection
