<?php

namespace App\Jobs;

use App\Models\SalesmanLocationLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FetchLocationAddress implements ShouldQueue
{
    use Queueable;

    protected $logId;

    /**
     * Create a new job instance.
     */
    public function __construct($logId)
    {
        $this->logId = $logId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $locationLog = SalesmanLocationLog::find($this->logId);

        if (!$locationLog || $locationLog->address) {
            return; // Already processed or deleted
        }

        $lat = round($locationLog->latitude, 4);
        $lng = round($locationLog->longitude, 4);

        // 1. Check if we already have an address for these coordinates
        // Using raw queries for ROUND to group locations roughly within ~11 meters
        $cachedLog = SalesmanLocationLog::where('id', '!=', $this->logId)
            ->whereNotNull('address')
            ->whereRaw('ROUND(latitude, 4) = ?', [$lat])
            ->whereRaw('ROUND(longitude, 4) = ?', [$lng])
            ->latest('id')
            ->first();

        if ($cachedLog && $cachedLog->address) {
            $locationLog->address = $cachedLog->address;
            $locationLog->save();
            return;
        }

        // 2. Fetch from Nominatim API
        try {
            $url = "https://nominatim.openstreetmap.org/reverse";
            
            $response = Http::withHeaders([
                'Accept-Language' => 'en',
                'User-Agent' => 'SeraAccessories/1.0' // Nominatim requires User-Agent
            ])->get($url, [
                'format' => 'json',
                'lat' => $locationLog->latitude,
                'lon' => $locationLog->longitude,
                'zoom' => 18,
                'addressdetails' => 1,
            ]);

            if ($response->successful() && isset($response->json()['display_name'])) {
                $locationLog->address = '<i class="fas fa-map-marker-alt" style="color: var(--text-muted); margin-right: 5px;"></i> ' . $response->json()['display_name'];
                $locationLog->save();
            } else {
                Log::warning("Nominatim API failed for log ID: {$this->logId}", ['response' => $response->body()]);
            }
        } catch (\Exception $e) {
            Log::error("Geocoding Error for log ID {$this->logId}: " . $e->getMessage());
        }

        // 3. Sleep to respect OpenStreetMap's 1 request per second rule
        sleep(2);
    }
}
