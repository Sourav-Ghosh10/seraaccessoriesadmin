<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Estimate;
use App\Models\OrderRequest;
use Illuminate\Support\Facades\Storage;

class DeleteProcessedRequests extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'requests:delete-processed {--days= : Optional number of days to filter recent processed requests}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete all estimate requests and order requests that have already been processed.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $estimatesQuery = Estimate::where('status', '!=', 'Pending');
        $orderRequestsQuery = OrderRequest::where('status', '!=', 'Pending');

        if ($this->option('days')) {
            $days = (int) $this->option('days');
            if ($days > 0) {
                $estimatesQuery->where('updated_at', '>=', now()->subDays($days));
                $orderRequestsQuery->where('updated_at', '>=', now()->subDays($days));
            }
        }

        $processedEstimates = $estimatesQuery->get();
        $processedOrderRequests = $orderRequestsQuery->get();

        $deletedEstimatesCount = 0;
        foreach ($processedEstimates as $estimate) {
            $this->deleteAssociatedFiles($estimate->file_path);
            $this->deleteAssociatedFiles($estimate->response_file_path);
            $estimate->delete();
            $deletedEstimatesCount++;
        }

        $deletedOrdersCount = 0;
        foreach ($processedOrderRequests as $orderReq) {
            $this->deleteAssociatedFiles($orderReq->file_path);
            $orderReq->delete();
            $deletedOrdersCount++;
        }

        $this->info("=== Processed Requests Cleanup Summary ===");
        $this->info("Deleted Estimate Requests count: {$deletedEstimatesCount}");
        $this->info("Deleted Order Requests count: {$deletedOrdersCount}");

        return Command::SUCCESS;
    }

    private function deleteAssociatedFiles($paths)
    {
        if (empty($paths)) {
            return;
        }

        $pathsArray = is_array($paths) ? $paths : [$paths];

        foreach ($pathsArray as $path) {
            if (!empty($path) && is_string($path)) {
                Storage::disk('public')->delete($path);
                $fullUploadPath = base_path('uploads/' . ltrim(str_replace('\\', '/', $path), '/'));
                if (file_exists($fullUploadPath) && is_file($fullUploadPath)) {
                    @unlink($fullUploadPath);
                }
            }
        }
    }
}
