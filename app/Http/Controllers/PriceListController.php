<?php

namespace App\Http\Controllers;

use App\Models\PriceList;
use App\Models\Member;
use App\Services\FcmService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PriceListController extends Controller
{
    public function index()
    {
        $priceLists = PriceList::orderBy('id', 'desc')->get();
        $latest = $priceLists->first();

        return view('price-list', compact('latest', 'priceLists'));
    }

    public function upload(Request $request)
    {
        $request->validate([
            'price_list_file' => 'required|file|mimes:pdf|max:51200', // max 50MB
        ]);

        try {
            $file = $request->file('price_list_file');
            $originalName = $file->getClientOriginalName();
            $fileSizeRaw = $file->getSize();
            $fileSize = $this->formatBytes($fileSizeRaw);

            // 1. Fetch and delete the previous price list files and records
            $previousLists = PriceList::all();
            foreach ($previousLists as $prev) {
                if ($prev->file_path && Storage::disk('public')->exists($prev->file_path)) {
                    Storage::disk('public')->delete($prev->file_path);
                }
                $prev->delete();
            }

            // 2. Determine version number starting at v2.5 based on auto-incrementing id
            $nextId = (DB::table('price_lists')->max('id') ?? 0) + 1;
            // E.g. if nextId is 1, version is v2.5. If nextId is 2, version is v2.6, etc.
            $version = 'v2.' . (4 + $nextId);

            // 3. Store the new file with unique timestamp to prevent caching issues
            $path = $file->storeAs('pricelists', 'price_list_' . time() . '.pdf', 'public');

            // 4. Create the new database record
            $priceList = PriceList::create([
                'file_name' => $originalName,
                'file_path' => $path,
                'file_size' => $fileSize,
                'version' => $version,
            ]);

            // 5. Broadcast live FCM push notifications to all dealers and salesmen
            $members = Member::whereIn('role', ['dealer', 'salesman'])->get();
            $url = asset('storage/' . $path);

            foreach ($members as $member) {
                FcmService::sendPushNotification(
                    $member,
                    'New Price List Available',
                    "The latest price list ({$version}) has been uploaded. Tap to download.",
                    [
                        'type' => 'price_list',
                        'version' => $version,
                        'url' => $url,
                        'deeplink' => 'price-list',
                        'deep_link' => 'price-list',
                    ]
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'New price list uploaded successfully and notifications broadcasted to all active members!',
                'data' => [
                    'version' => $version,
                    'file_name' => $originalName,
                    'file_size' => $fileSize,
                    'upload_date' => $priceList->created_at->format('Y-m-d'),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Price List Upload Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload price list: ' . $e->getMessage()
            ], 500);
        }
    }

    private function formatBytes($bytes, $precision = 1)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
