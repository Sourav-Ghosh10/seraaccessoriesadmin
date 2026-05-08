@extends('layouts.app')

@section('content')
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h3>Distributor Registration</h3>
        <button class="btn btn-primary"><i class="fas fa-plus"></i> Add Distributor</button>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Distributor Name</th>
                    <th>Contact Person</th>
                    <th>Service Area</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($distributors as $distributor)
                <tr>
                    <td>#{{ $distributor['id'] }}</td>
                    <td>{{ $distributor['name'] }}</td>
                    <td>{{ $distributor['contact'] }}</td>
                    <td>{{ $distributor['area'] }}</td>
                    <td><span class="badge badge-success">{{ $distributor['status'] }}</span></td>
                    <td>
                        <button class="btn glass" style="padding: 5px 10px; font-size: 12px;"><i class="fas fa-edit"></i></button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
