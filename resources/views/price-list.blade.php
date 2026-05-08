@extends('layouts.app')

@section('content')
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h3>Price List Management</h3>
        <button class="btn btn-primary"><i class="fas fa-file-upload"></i> Upload New Price List</button>
    </div>

    <div class="grid">
        <div class="card" style="display: flex; align-items: center; gap: 20px;">
            <div style="font-size: 40px; color: var(--accent);">
                <i class="fas fa-file-pdf"></i>
            </div>
            <div style="flex: 1;">
                <h4 style="margin-bottom: 5px;">Latest Price List v2.4</h4>
                <p style="font-size: 12px; color: var(--text-muted);">Uploaded on: 2024-04-25</p>
            </div>
            <button class="btn btn-primary" style="padding: 10px 15px;"><i class="fas fa-download"></i></button>
        </div>
    </div>

    <h4 style="margin-top: 40px; margin-bottom: 20px;">Version History</h4>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Version</th>
                    <th>Upload Date</th>
                    <th>Size</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>v2.4</td>
                    <td>2024-04-25</td>
                    <td>1.2 MB</td>
                    <td><button class="btn glass" style="padding: 5px 10px; font-size: 12px;"><i class="fas fa-download"></i></button></td>
                </tr>
                <tr>
                    <td>v2.3</td>
                    <td>2024-03-10</td>
                    <td>1.1 MB</td>
                    <td><button class="btn glass" style="padding: 5px 10px; font-size: 12px;"><i class="fas fa-download"></i></button></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
