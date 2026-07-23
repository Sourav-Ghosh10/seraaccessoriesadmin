<?php

namespace App\Http\Controllers;

use App\Models\AppPopup;
use App\Http\Requests\StoreAppPopupRequest;
use App\Http\Requests\UpdateAppPopupRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AppPopupController extends Controller
{
    /**
     * Display a listing of the popups.
     */
    public function index(Request $request)
    {
        $query = AppPopup::query();

        // Search by ID
        if ($request->filled('search')) {
            $query->where('id', $request->search);
        }

        // Filter by Status (1 = Active, 0 = Inactive)
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $popups = $query->orderBy('created_at', 'desc')
                        ->paginate(10);

        if ($request->ajax()) {
            return response()->json([
                'html' => view('app_popups.table', compact('popups'))->render(),
                'pagination' => (string) $popups->links(),
                'total' => $popups->total(),
            ]);
        }

        return view('app_popups.index', compact('popups'));
    }

    /**
     * Show the form for creating a new popup.
     */
    public function create()
    {
        $existing = AppPopup::first();
        if ($existing) {
            return redirect()->route('app-popups.edit', $existing->id)
                             ->with('info', 'An announcement already exists. Please edit and update this one instead of creating a new popup.');
        }

        return view('app_popups.create');
    }

    /**
     * Store a newly created popup in storage.
     */
    public function store(StoreAppPopupRequest $request)
    {
        $existing = AppPopup::first();
        if ($existing) {
            return redirect()->route('app-popups.edit', $existing->id)
                             ->with('info', 'An announcement already exists. Please edit and update this one instead of creating a new popup.');
        }

        $validated = $request->validated();
        if ($request->hasFile('banner_image') && $request->file('banner_image')->isValid()) {
            $path = $request->file('banner_image')->store('app_popups', 'public');
            $validated['banner_image'] = $path;
            $validated['image'] = $path;
        } elseif ($request->hasFile('image') && $request->file('image')->isValid()) {
            $path = $request->file('image')->store('app_popups', 'public');
            $validated['banner_image'] = $path;
            $validated['image'] = $path;
        }
        $validated['status'] = 1;
        AppPopup::create($validated);

        return redirect()->route('app-popups.index')
                         ->with('success', 'Popup announcement created successfully!');
    }

    /**
     * Show the form for editing the specified popup.
     */
    public function edit($id)
    {
        $popup = AppPopup::findOrFail($id);
        return view('app_popups.edit', compact('popup'));
    }

    /**
     * Update the specified popup in storage.
     */
    public function update(UpdateAppPopupRequest $request, $id)
    {
        $popup = AppPopup::findOrFail($id);
        $validated = $request->validated();

        if ($request->hasFile('banner_image') && $request->file('banner_image')->isValid()) {
            if ($popup->banner_image && Storage::disk('public')->exists($popup->banner_image)) {
                Storage::disk('public')->delete($popup->banner_image);
            }
            if ($popup->image && $popup->image !== $popup->banner_image && Storage::disk('public')->exists($popup->image)) {
                Storage::disk('public')->delete($popup->image);
            }
            $path = $request->file('banner_image')->store('app_popups', 'public');
            $validated['banner_image'] = $path;
            $validated['image'] = $path;
        } elseif ($request->hasFile('image') && $request->file('image')->isValid()) {
            if ($popup->banner_image && Storage::disk('public')->exists($popup->banner_image)) {
                Storage::disk('public')->delete($popup->banner_image);
            }
            if ($popup->image && $popup->image !== $popup->banner_image && Storage::disk('public')->exists($popup->image)) {
                Storage::disk('public')->delete($popup->image);
            }
            $path = $request->file('image')->store('app_popups', 'public');
            $validated['banner_image'] = $path;
            $validated['image'] = $path;
        }

        $popup->update($validated);

        return redirect()->route('app-popups.index')
                         ->with('success', 'Popup announcement updated successfully!');
    }

    /**
     * Remove the specified popup from storage.
     */
    public function destroy($id)
    {
        $popup = AppPopup::findOrFail($id);
        if ($popup->banner_image && Storage::disk('public')->exists($popup->banner_image)) {
            Storage::disk('public')->delete($popup->banner_image);
        }
        if ($popup->image && $popup->image !== $popup->banner_image && Storage::disk('public')->exists($popup->image)) {
            Storage::disk('public')->delete($popup->image);
        }
        $popup->delete();

        return response()->json([
            'success' => true,
            'message' => 'Popup announcement deleted successfully!'
        ]);
    }

    /**
     * Toggle active status of the popup.
     */
    public function toggleStatus($id)
    {
        $popup = AppPopup::findOrFail($id);
        $popup->status = !$popup->status;
        $popup->save();

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully!',
            'status' => $popup->status
        ]);
    }
}
