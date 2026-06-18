<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\State;
use Illuminate\Http\Request;

class CityController extends Controller
{
    public function index(Request $request)
    {
        $query = City::with('state');
        
        if ($request->has('search') && $request->search != '') {
            $query->where('city', 'like', '%' . $request->search . '%');
        }

        $cities = $query->orderBy('status', 'desc')->orderBy('city', 'asc')->paginate(10);
        
        if ($request->ajax()) {
            return response()->json([
                'cities' => $cities->items(),
                'pagination' => (string) $cities->links()
            ]);
        }

        $states = State::orderBy('name')->get();
        return view('cities', compact('cities', 'states'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'city' => 'required|string|max:255',
            'state_id' => 'nullable|integer',
        ]);

        $validated['status'] = 0; // Default inactive
        City::create($validated);

        return response()->json(['success' => true, 'message' => 'City added successfully!']);
    }

    public function update(Request $request, $id)
    {
        $city = City::findOrFail($id);
        
        $validated = $request->validate([
            'city' => 'required|string|max:255',
            'state_id' => 'nullable|integer',
        ]);

        $city->update($validated);

        return response()->json(['success' => true, 'message' => 'City updated successfully!']);
    }

    public function toggleStatus($id)
    {
        $city = City::findOrFail($id);
        $city->status = !$city->status;
        $city->save();

        return response()->json(['success' => true, 'message' => 'Status updated successfully!', 'status' => $city->status]);
    }
}
