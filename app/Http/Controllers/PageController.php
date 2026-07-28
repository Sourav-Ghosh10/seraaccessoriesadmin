<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OrderRequest;
use App\Models\Member;
use App\Models\Order;
use App\Models\RedeemRequest;


class PageController extends Controller
{
    public function login() { return view('auth.login'); }
    
    public function dashboard(Request $request) { 
        if ($request->has('role')) {
            session(['role' => $request->role]);
        }
        $role = session('role', 'Admin');

        // Dynamic metrics
        $totalDealers = \App\Models\Member::where('role', 'dealer')->count();
        $totalSalesmen = \App\Models\Member::where('role', 'salesman')->count();
        $totalOrders = \App\Models\Order::count();
        $pendingOrders = \App\Models\OrderRequest::where('status', 'Pending')->count();

        $deliveredOrders = \App\Models\Order::where('status', 'Delivered')->count();
        $invoicePending = \App\Models\Order::where('status', '!=', 'Cancelled')->whereDoesntHave('invoice')->count();

        // Top Dealer
        $topDealerModel = \App\Models\Member::where('role', 'dealer')
            ->withSum(['orders' => function($q) {
                $q->where('status', '!=', 'Cancelled');
            }], 'amount')
            ->orderByDesc('orders_sum_amount')
            ->first();

        if ($topDealerModel) {
            $topDealerName = $topDealerModel->name;
        } else {
            $topDealerName = 'John Doe';
        }

        // Top Salesman
        $topSalesmanModel = \App\Models\Member::where('role', 'salesman')
            ->withSum('rewardTransactions', 'points')
            ->orderByDesc('reward_transactions_sum_points')
            ->first();

        if ($topSalesmanModel) {
            $topSalesmanName = $topSalesmanModel->name;
        } else {
            $topSalesmanName = 'Alice Smith';
        }

        // Last 6 months sales data
        $months = [];
        $salesData = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months[] = $date->format('M');
            
            $sum = \App\Models\Order::where('status', '!=', 'Cancelled')
                ->whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->count();
                
            $salesData[] = (float) $sum;
        }

        return view('dashboard', compact(
            'role',
            'totalDealers',
            'totalSalesmen',
            'totalOrders',
            'pendingOrders',
            'deliveredOrders',
            'invoicePending',
            'topDealerName',
            'topSalesmanName',
            'months',
            'salesData'
        ));
    }

    public function chartData(Request $request) {
        $filter = $request->query('filter', '6_months');
        $labels = [];
        $data = [];

        if ($filter == 'yearly') {
            // All 12 months of current year
            $year = now()->year;
            for ($i = 1; $i <= 12; $i++) {
                $labels[] = date("M", mktime(0, 0, 0, $i, 10));
                $sum = \App\Models\Order::where('status', '!=', 'Cancelled')
                    ->whereMonth('created_at', $i)
                    ->whereYear('created_at', $year)
                    ->count();
                $data[] = (float) $sum;
            }
        } elseif ($filter == 'monthly' || $filter == 'custom') {
            if ($filter == 'custom' && $request->filled('month')) {
                // $request->month format: "YYYY-MM"
                $parts = explode('-', $request->query('month'));
                $year = $parts[0] ?? now()->year;
                $month = $parts[1] ?? now()->month;
            } else {
                $month = now()->month;
                $year = now()->year;
            }
            
            // Days of the month
            $daysInMonth = \Carbon\Carbon::createFromDate($year, $month, 1)->daysInMonth;
            for ($i = 1; $i <= $daysInMonth; $i++) {
                $labels[] = $i;
                $sum = \App\Models\Order::where('status', '!=', 'Cancelled')
                    ->whereDate('created_at', "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-" . str_pad($i, 2, '0', STR_PAD_LEFT))
                    ->count();
                $data[] = (float) $sum;
            }
        } else {
            // Last 6 months
            for ($i = 5; $i >= 0; $i--) {
                $date = now()->subMonths($i);
                $labels[] = $date->format('M');
                
                $sum = \App\Models\Order::where('status', '!=', 'Cancelled')
                    ->whereMonth('created_at', $date->month)
                    ->whereYear('created_at', $date->year)
                    ->count();
                    
                $data[] = (float) $sum;
            }
        }

        return response()->json(['labels' => $labels, 'data' => $data]);
    }
    
    public function dealers(Request $request) {
        $query = \App\Models\Member::where('role', 'dealer')->with(['salesman', 'city']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('shop', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('id', 'like', "%{$search}%")
                  ->orWhereHas('city', function($q2) use ($search) {
                      $q2->where('city', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('salesman')) {
            $query->where('salesman_id', $request->salesman);
        }

        if ($request->filled('distributor')) {
            $query->where('dist_id', $request->distributor);
        }

        if ($request->filled('city')) {
            $citiesFilter = is_array($request->city) ? $request->city : [$request->city];
            if (!in_array('all', $citiesFilter)) {
                $query->whereIn('city_id', $citiesFilter);
            }
        }

        $dealers = $query->orderBy('id', 'desc')->paginate(10);
        $cities = \App\Models\City::where('status', 1)->orderBy('city')->get();
        $salesmen = \App\Models\Member::where('role', 'salesman')->orderBy('name')->get();
        $distributors = \App\Models\Member::where('role', 'distributor')->orderBy('name')->get();
        return view('dealers', compact('dealers', 'cities', 'salesmen', 'distributors'));
    }

    public function salesmen(Request $request) {
        $query = \App\Models\Member::with('city')->where('role', 'salesman');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%")
                  ->orWhere('mobile', 'like', "%$search%")
                  ->orWhere('ref_code', 'like', "%$search%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $salesmen = $query->orderBy('id', 'desc')->paginate(10);
        $cities = \App\Models\City::where('status', 1)->orderBy('city')->get();
        
        if ($request->ajax()) {
            return view('salesmen_table', compact('salesmen', 'cities'))->render();
        }

        return view('salesmen', compact('salesmen', 'cities'));
    }

    public function salesmanAttendance(Request $request) {
        $date = $request->filled('date') ? $request->date : now()->toDateString();
        
        $query = \App\Models\Member::where('role', 'salesman')
            ->with(['attendances' => function($q) use ($date) {
                $q->whereDate('date', $date);
            }])
            ->orderBy('id', 'desc');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('mobile', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%");
            });
        }

        if ($request->filled('salesman_id')) {
            $query->where('id', $request->salesman_id);
        }

        $salesmenList = $query->paginate(15);
        $allSalesmen = \App\Models\Member::where('role', 'salesman')->orderBy('name')->get();

        return view('salesman_attendance', compact('salesmenList', 'allSalesmen', 'date'));
    }

    public function salesmanAttendanceDetails($id) {
        $attendance = \App\Models\SalesmanAttendance::with('member')->findOrFail($id);
        
        $visits = \App\Models\SalesmanVisit::with('dealer')
            ->where('salesman_id', $attendance->member_id)
            ->whereDate('visit_time', $attendance->date)
            ->orderBy('visit_time', 'asc')
            ->get();
            
        $locations = \App\Models\SalesmanLocationLog::where('attendance_id', $attendance->id)
            ->orderBy('timestamp', 'desc')
            ->get();

        $unlockLogs = \App\Models\SalesmanAttendanceUnlockLog::with('admin')
            ->where('attendance_id', $attendance->id)
            ->orderBy('locked_at', 'desc')
            ->get();
            
        return view('salesman_attendance_details', compact('attendance', 'visits', 'locations', 'unlockLogs'));
    }

    public function unlockSalesmanAttendance($id) {
        $attendance = \App\Models\SalesmanAttendance::findOrFail($id);
        
        if ($attendance->date->isToday() && $attendance->clockout_type === 'automatic') {
            $attendance->update([
                'clock_out_time' => null,
                'clock_out_latitude' => null,
                'clock_out_longitude' => null,
                'clock_out_address' => null,
                'total_hours' => null,
                'clockout_type' => null,
            ]);

            $pendingLog = \App\Models\SalesmanAttendanceUnlockLog::where('attendance_id', $attendance->id)
                ->whereNull('unlocked_at')
                ->latest('locked_at')
                ->first();
            
            if ($pendingLog) {
                $pendingLog->update([
                    'unlocked_at' => now(),
                    'unlocked_by' => auth()->id(),
                ]);
            }

            return redirect()->back()->with('success', 'Attendance unlocked and resumed successfully.');
        }

        return redirect()->back()->with('error', 'Cannot unlock this attendance.');
    }

    public function distributors(Request $request) {
        $query = \App\Models\Member::where('role', 'distributor')->with('city');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%")
                  ->orWhere('dist_id', 'like', "%{$search}%")
                  ->orWhere('gst_no', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where(\DB::raw('LOWER(status)'), strtolower($request->status));
        }

        if ($request->filled('city')) {
            $cityFilter = is_array($request->city) ? $request->city : [$request->city];
            if (!in_array('all', $cityFilter)) {
                $query->whereHas('city', function($q) use ($cityFilter) {
                    $q->whereIn(\DB::raw('LOWER(city)'), array_map('strtolower', $cityFilter));
                });
            }
        }

        $distributors = $query->paginate(10);
        $cities = \App\Models\City::where('status', 1)->orderBy('city')->get();
        return view('distributors', compact('distributors', 'cities'));
    }

    public function distributorStaff(Request $request, $id) {
        $distributor = \App\Models\Member::where('role', 'distributor')->findOrFail($id);
        $query = \App\Models\Member::where('role', 'distributor_staff')
            ->where('dist_id', $distributor->dist_id);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%");
            });
        }

        $staffMembers = $query->paginate(10);
        $cities = \App\Models\City::where('status', 1)->orderBy('city')->get();
        return view('distributor_staff', compact('distributor', 'staffMembers', 'cities'));
    }

    public function complianceDashboard() {
        return view('compliance.dashboard');
    }

    public function siteCompliance() {
        $sites = [
            ['name' => 'Mall A', 'requirement' => 'Fire Equipment Training', 'status' => 'Compliant', 'expiry' => '2024-12-20', 'last_completed' => '2023-12-20', 'score' => 85],
            ['name' => 'Mall A', 'requirement' => 'Evacuation Drill', 'status' => 'Expired', 'expiry' => '2024-05-01', 'last_completed' => '2023-11-01', 'score' => 40],
            ['name' => 'Mall B', 'requirement' => 'Emergency Procedures', 'status' => 'Expiring Soon', 'expiry' => '2024-05-25', 'last_completed' => '2023-11-25', 'score' => 72],
        ];
        return view('compliance.sites', compact('sites'));
    }

    public function expiringCompliance() {
        $expiring = [
            ['site' => 'Mall B', 'training' => 'Emergency Procedures', 'expiry' => '2024-05-25', 'days_left' => 12],
            ['site' => 'Airport Terminal 1', 'training' => 'First Aid', 'expiry' => '2024-06-05', 'days_left' => 23],
        ];
        return view('compliance.expiring', compact('expiring'));
    }

    public function nonCompliantSites() {
        return view('compliance.non-compliant');
    }

    public function complianceReports() {
        return view('compliance.reports');
    }

    public function estimateRequests(Request $request) {
        $query = \App\Models\Estimate::with(['member.salesman', 'member.distributor']);

        $tab = $request->query('tab', 'dealer');
        $query->whereHas('member', function($q) use ($tab) {
            $q->where('role', $tab);
        });

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('request_number', 'like', "%$search%")
                  ->orWhere('id', 'like', "%$search%")
                  ->orWhereHas('member', function($q2) use ($search) {
                      $q2->where('name', 'like', "%$search%")
                         ->orWhere('shop', 'like', "%$search%");
                  });
            });
        }

        if ($request->filled('city_id') || $request->filled('salesman_id') || $request->filled('dist_id')) {
            $query->whereHas('member', function($q) use ($request) {
                if ($request->filled('city_id')) {
                    if (is_array($request->city_id)) {
                        $q->whereIn('city_id', $request->city_id);
                    } else {
                        $q->where('city_id', $request->city_id);
                    }
                }
                if ($request->filled('salesman_id')) {
                    $q->where('salesman_id', $request->salesman_id);
                }
                if ($request->filled('dist_id')) {
                    $q->where('dist_id', $request->dist_id);
                }
            });
        }

        if ($request->filled('date_type')) {
            if ($request->date_type === 'individual' && $request->filled('single_date')) {
                $query->whereDate('created_at', $request->single_date);
            } elseif ($request->date_type === 'range') {
                if ($request->filled('date_from')) {
                    $query->whereDate('created_at', '>=', $request->date_from);
                }
                if ($request->filled('date_to')) {
                    $query->whereDate('created_at', '<=', $request->date_to);
                }
            }
        }

        $estimates = $query->orderBy('id', 'desc')->paginate(10);

        $cities = \App\Models\City::where('status', 1)->orderBy('city')->get();
        $salesmen = \App\Models\Member::where('role', 'salesman')->orderBy('name')->get();
        $distributors = \App\Models\Member::where('role', 'distributor')->orderBy('name')->get();

        return view('estimates.requests', compact('estimates', 'cities', 'salesmen', 'distributors'));
    }

    public function dependentMembers(Request $request) {
        $cityIds = $request->city_ids;
        if (!is_array($cityIds)) {
            $cityIds = $cityIds ? [$cityIds] : [];
        }

        if (empty($cityIds) || in_array('all', $cityIds)) {
            $salesmen = \App\Models\Member::where('role', 'salesman')->orderBy('name')->get(['id', 'name']);
            $distributors = \App\Models\Member::where('role', 'distributor')->orderBy('name')->get(['dist_id', 'name']);
        } else {
            $dealers = \App\Models\Member::where('role', 'dealer')->whereIn('city_id', $cityIds)->get(['salesman_id', 'dist_id']);
            $salesmanIds = $dealers->pluck('salesman_id')->filter()->unique();
            $distIds = $dealers->pluck('dist_id')->filter()->unique();

            $salesmen = \App\Models\Member::where('role', 'salesman')->whereIn('id', $salesmanIds)->orderBy('name')->get(['id', 'name']);
            $distributors = \App\Models\Member::where('role', 'distributor')->whereIn('dist_id', $distIds)->orderBy('name')->get(['dist_id', 'name']);
        }

        return response()->json([
            'salesmen' => $salesmen,
            'distributors' => $distributors
        ]);
    }

    public function orderRequests(Request $request) {
        $query = \App\Models\OrderRequest::with(['member.salesman', 'member.distributor', 'order']);

        $tab = $request->query('tab', 'dealer');
        $query->whereHas('member', function($q) use ($tab) {
            $q->where('role', $tab);
        });

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('request_number', 'like', "%$search%")
                  ->orWhere('id', 'like', "%$search%")
                  ->orWhereHas('member', function($q2) use ($search) {
                      $q2->where('name', 'like', "%$search%")
                         ->orWhere('shop', 'like', "%$search%");
                  });
            });
        }

        if ($request->filled('city_id') || $request->filled('salesman_id') || $request->filled('dist_id')) {
            $query->whereHas('member', function($q) use ($request) {
                if ($request->filled('city_id')) {
                    if (is_array($request->city_id)) {
                        $q->whereIn('city_id', $request->city_id);
                    } else {
                        $q->where('city_id', $request->city_id);
                    }
                }
                if ($request->filled('salesman_id')) {
                    $q->where('salesman_id', $request->salesman_id);
                }
                if ($request->filled('dist_id')) {
                    $q->where('dist_id', $request->dist_id);
                }
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_type')) {
            if ($request->date_type === 'individual' && $request->filled('single_date')) {
                $query->whereDate('created_at', $request->single_date);
            } elseif ($request->date_type === 'range') {
                if ($request->filled('date_from')) {
                    $query->whereDate('created_at', '>=', $request->date_from);
                }
                if ($request->filled('date_to')) {
                    $query->whereDate('created_at', '<=', $request->date_to);
                }
            }
        }

        $orders = $query->orderBy('id', 'desc')->paginate(10);
        $dealers = Member::where('role', 'dealer')->get();
        $cities = \App\Models\City::where('status', 1)->orderBy('city')->get();
        $salesmen = \App\Models\Member::where('role', 'salesman')->orderBy('name')->get();
        $distributors = \App\Models\Member::where('role', 'distributor')->orderBy('name')->get();

        return view('orders.requests', compact('orders', 'dealers', 'cities', 'salesmen', 'distributors'));
    }

    public function redeemRequests(Request $request) {
        if (!\Illuminate\Support\Facades\Schema::hasColumn('redeem_request', 'dealer_file_path')) {
            \Illuminate\Support\Facades\Schema::table('redeem_request', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->string('dealer_file_path')->nullable()->after('status');
                $table->string('distributor_file_path')->nullable()->after('dealer_file_path');
            });
        }
        
        if (!\Illuminate\Support\Facades\Schema::hasColumn('redeem_request', 'salesman_file_path')) {
            \Illuminate\Support\Facades\Schema::table('redeem_request', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->string('salesman_file_path')->nullable()->after('distributor_file_path');
            });
        }
        
        if (!\Illuminate\Support\Facades\Schema::hasColumn('redeem_request', 'deduct_amount')) {
            \Illuminate\Support\Facades\Schema::table('redeem_request', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->decimal('deduct_amount', 10, 2)->nullable()->after('status');
            });
        }

        $query = RedeemRequest::with(['member' => function($q) {
            $q->with(['salesman', 'distributor', 'city'])->withSum('rewardTransactions', 'points');
        }]);

        $tab = strtolower($request->query('tab', 'dealer'));
        $query->whereHas('member', function($q) use ($tab) {
            $q->where('role', $tab);
        });

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('id', 'like', "%$search%")
                  ->orWhereHas('member', function($q2) use ($search) {
                      $q2->where('name', 'like', "%$search%")
                         ->orWhere('shop', 'like', "%$search%")
                         ->orWhere('emp_id', 'like', "%$search%");
                  });
            });
        }

        if ($request->filled('city_id') || $request->filled('salesman_id') || $request->filled('dist_id')) {
            $query->whereHas('member', function($q) use ($request) {
                if ($request->filled('city_id')) {
                    if (is_array($request->city_id)) {
                        $q->whereIn('city_id', $request->city_id);
                    } else {
                        $q->where('city_id', $request->city_id);
                    }
                }
                if ($request->filled('salesman_id')) {
                    $q->where('salesman_id', $request->salesman_id);
                }
                if ($request->filled('dist_id')) {
                    $q->where('dist_id', $request->dist_id);
                }
            });
        }

        if ($request->filled('date_type')) {
            if ($request->date_type === 'individual' && $request->filled('single_date')) {
                $query->whereDate('created_at', $request->single_date);
            } elseif ($request->date_type === 'range') {
                if ($request->filled('date_from')) {
                    $query->whereDate('created_at', '>=', $request->date_from);
                }
                if ($request->filled('date_to')) {
                    $query->whereDate('created_at', '<=', $request->date_to);
                }
            }
        }

        $requests = $query->orderBy('id', 'desc')->paginate(10)->withQueryString();
        $dealers = Member::where('role', 'dealer')->get();
        $cities = \App\Models\City::where('status', 1)->orderBy('city')->get();
        $salesmen = Member::where('role', 'salesman')->orderBy('name')->get();
        $distributors = Member::where('role', 'distributor')->orderBy('name')->get();

        return view('redeem_requests', compact('requests', 'dealers', 'cities', 'salesmen', 'distributors', 'tab'));
    }

    public function ordersList(Request $request) {

        $query = \App\Models\Order::with(['member.salesman', 'member.distributor', 'delivery'])
            ->where('status', '!=', 'Pending')
            ->where('order_number', 'like', 'ORD-%');

        $tab = $request->query('tab', 'dealer');
        $query->whereHas('member', function($q) use ($tab) {
            $q->where('role', $tab);
        });

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%$search%")
                  ->orWhere('id', 'like', "%$search%")
                  ->orWhereHas('member', function($q2) use ($search) {
                      $q2->where('name', 'like', "%$search%")
                         ->orWhere('shop', 'like', "%$search%");
                  });
            });
        }

        if ($request->filled('city_id') || $request->filled('salesman_id') || $request->filled('dist_id')) {
            $query->whereHas('member', function($q) use ($request) {
                if ($request->filled('city_id')) {
                    if (is_array($request->city_id)) {
                        $q->whereIn('city_id', $request->city_id);
                    } else {
                        $q->where('city_id', $request->city_id);
                    }
                }
                if ($request->filled('salesman_id')) {
                    $q->where('salesman_id', $request->salesman_id);
                }
                if ($request->filled('dist_id')) {
                    $q->where('dist_id', $request->dist_id);
                }
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_type')) {
            if ($request->date_type === 'individual' && $request->filled('single_date')) {
                $query->whereDate('created_at', $request->single_date);
            } elseif ($request->date_type === 'range') {
                if ($request->filled('date_from')) {
                    $query->whereDate('created_at', '>=', $request->date_from);
                }
                if ($request->filled('date_to')) {
                    $query->whereDate('created_at', '<=', $request->date_to);
                }
            }
        }

        $finalOrders = $query->orderBy('id', 'desc')->paginate(10);
        $cities = \App\Models\City::where('status', 1)->orderBy('city')->get();
        $salesmen = \App\Models\Member::where('role', 'salesman')->orderBy('name')->get();
        $distributors = \App\Models\Member::where('role', 'distributor')->orderBy('name')->get();

        return view('orders.index', compact('finalOrders', 'cities', 'salesmen', 'distributors'));
    }

    public function showOrder($id) {
        $num = intval(preg_replace('/^.*-(\d+)$/', '$1', $id));
        $order = \App\Models\Order::with(['member', 'items', 'distributor', 'delivery'])
            ->where(function($q) use ($id, $num) {
                $q->where('id', $id)
                  ->orWhere('id', $num)
                  ->orWhere('order_number', $id)
                  ->orWhere('order_number', 'ORD-' . str_pad($num, 4, '0', STR_PAD_LEFT));
            })
            ->firstOrFail();
        return view('orders.show', compact('order'));
    }

    public function createOrder(Request $request) {
        $dealers = \App\Models\Member::where('role', 'dealer')->get();
        $distributors = \App\Models\Member::where('role', 'distributor')->get();
        
        $targetMember = null;
        if ($request->filled('dealer')) {
            $targetMember = \App\Models\Member::find($request->dealer);
        } elseif ($request->filled('from_req')) {
            $req = \App\Models\OrderRequest::find($request->from_req);
            if ($req) {
                $targetMember = $req->member;
            }
        }
        
        $isDistributor = $targetMember && $targetMember->role === 'distributor';

        return view('orders.create', compact('dealers', 'distributors', 'targetMember', 'isDistributor'));
    }

    public function delivery(Request $request)
    {
        $query = Order::with(['member.salesman', 'member.distributor', 'delivery'])
            ->where('status', '!=', 'Pending')
            ->where('status', '!=', 'Cancelled')
            ->where('order_number', 'like', 'ORD-%');

        $tab = $request->query('tab', 'dealer');
        $query->whereHas('member', function($q) use ($tab) {
            $q->where('role', $tab);
        });

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%$search%")
                  ->orWhere('id', 'like', "%$search%")
                  ->orWhereHas('member', function($q2) use ($search) {
                      $q2->where('name', 'like', "%$search%")
                         ->orWhere('shop', 'like', "%$search%");
                  });
            });
        }

        if ($request->filled('city_id') || $request->filled('salesman_id') || $request->filled('dist_id')) {
            $query->whereHas('member', function($q) use ($request) {
                if ($request->filled('city_id')) {
                    if (is_array($request->city_id)) {
                        $q->whereIn('city_id', $request->city_id);
                    } else {
                        $q->where('city_id', $request->city_id);
                    }
                }
                if ($request->filled('salesman_id')) {
                    $q->where('salesman_id', $request->salesman_id);
                }
                if ($request->filled('dist_id')) {
                    $q->where('dist_id', $request->dist_id);
                }
            });
        }

        if ($request->filled('date_type')) {
            if ($request->date_type === 'individual' && $request->filled('single_date')) {
                $query->whereDate('created_at', $request->single_date);
            } elseif ($request->date_type === 'range') {
                if ($request->filled('date_from')) {
                    $query->whereDate('created_at', '>=', $request->date_from);
                }
                if ($request->filled('date_to')) {
                    $query->whereDate('created_at', '<=', $request->date_to);
                }
            }
        }

        if ($request->filled('delivery_status')) {
            $status = $request->delivery_status;
            if ($status === 'out_for_delivery') {
                $query->where('status', 'Out for Delivery');
            } elseif ($status === 'delivered') {
                $query->where('status', 'Delivered');
            } elseif ($status === 'pending') {
                $query->whereIn('status', ['Confirmed', 'Processing', 'Invoiced']);
            } elseif ($status === 'returned') {
                $query->where('status', 'Returned');
            }
        }

        $orders = $query->orderBy('id', 'desc')->paginate(10);
        
        $cities = \App\Models\City::where('status', 1)->orderBy('city')->get();
        $salesmen = \App\Models\Member::where('role', 'salesman')->orderBy('name')->get();
        $distributors = \App\Models\Member::where('role', 'distributor')->orderBy('name')->get();

        return view('delivery', compact('orders', 'cities', 'salesmen', 'distributors'));
    }

    public function invoices(Request $request)
    {
        $query = Order::with(['member.salesman', 'member.distributor', 'invoice', 'creditNote']);

        if ($request->filled('order_status')) {
            $statusFilter = $request->order_status === 'pending' ? 'order_pending' : $request->order_status;
        } elseif ($request->filled('invoice_status')) {
            $statusFilter = $request->invoice_status;
        } else {
            $statusFilter = null;
        }

        if ($statusFilter) {
            $orderStatusMap = [
                'confirmed' => 'Confirmed',
                'order_pending' => 'Pending',
                'out_for_delivery' => 'Out for Delivery',
                'delivered' => 'Delivered',
                'cancelled' => 'Cancelled',
                'canceled' => 'Cancelled',
                'returned' => 'Returned',
            ];

            if (isset($orderStatusMap[$statusFilter])) {
                $query->where('status', $orderStatusMap[$statusFilter]);
            } elseif (in_array($statusFilter, ['invoice_pending', 'pending'], true)) {
                $query->whereDoesntHave('invoice');
            } elseif ($statusFilter === 'complete') {
                $query->whereHas('invoice');
            } elseif ($statusFilter === 'pending_credit_note') {
                $query->where('status', 'Returned')->whereDoesntHave('creditNote');
            }
        } else {
            $query->where('status', '!=', 'Cancelled');
        }

        $tab = $request->query('tab', 'dealer');
        $query->whereHas('member', function($q) use ($tab) {
            $q->where('role', $tab);
        });

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%$search%")
                  ->orWhere('id', 'like', "%$search%")
                  ->orWhereHas('member', function($q2) use ($search) {
                      $q2->where('name', 'like', "%$search%")
                         ->orWhere('shop', 'like', "%$search%");
                  });
            });
        }

        if ($request->filled('city_id') || $request->filled('salesman_id') || $request->filled('dist_id')) {
            $query->whereHas('member', function($q) use ($request) {
                if ($request->filled('city_id')) {
                    if (is_array($request->city_id)) {
                        $q->whereIn('city_id', $request->city_id);
                    } else {
                        $q->where('city_id', $request->city_id);
                    }
                }
                if ($request->filled('salesman_id')) {
                    $q->where('salesman_id', $request->salesman_id);
                }
                if ($request->filled('dist_id')) {
                    $q->where('dist_id', $request->dist_id);
                }
            });
        }

        if ($request->filled('date_type')) {
            if ($request->date_type === 'individual' && $request->filled('single_date')) {
                $query->whereDate('created_at', $request->single_date);
            } elseif ($request->date_type === 'range') {
                if ($request->filled('date_from')) {
                    $query->whereDate('created_at', '>=', $request->date_from);
                }
                if ($request->filled('date_to')) {
                    $query->whereDate('created_at', '<=', $request->date_to);
                }
            }
        }


        $orders = $query->orderBy('id', 'desc')->paginate(10);
        $cities = \App\Models\City::where('status', 1)->orderBy('city')->get();
        $salesmen = \App\Models\Member::where('role', 'salesman')->orderBy('name')->get();
        $distributors = \App\Models\Member::where('role', 'distributor')->orderBy('name')->get();

        return view('invoices', compact('orders', 'cities', 'salesmen', 'distributors'));
    }

    public function rewards(Request $request) {
        // Base query for history of orders with points - with filtering
        $query = \App\Models\Order::with(['member.salesman', 'rewardTransactions.member'])
            ->whereHas('member', function($q) {
                $q->where('role', 'dealer');
            })
            ->where('status', '!=', 'Pending')
            ->where('order_number', 'like', 'ORD-%');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('order_number', 'like', "%$search%")
                  ->orWhere('id', 'like', "%$search%")
                  ->orWhereHas('member', function($q2) use ($search) {
                      $q2->where('name', 'like', "%$search%")
                         ->orWhere('shop', 'like', "%$search%");
                  });
            });
        }

        if ($request->filled('city_id') || $request->filled('salesman_id') || $request->filled('dist_id')) {
            $query->whereHas('member', function($q) use ($request) {
                if ($request->filled('city_id')) {
                    if (is_array($request->city_id)) {
                        $q->whereIn('city_id', $request->city_id);
                    } else {
                        $q->where('city_id', $request->city_id);
                    }
                }
                if ($request->filled('salesman_id')) {
                    $q->where('salesman_id', $request->salesman_id);
                }
                if ($request->filled('dist_id')) {
                    $q->where('dist_id', $request->dist_id);
                }
            });
        }

        if ($request->filled('date_type')) {
            if ($request->date_type === 'individual' && $request->filled('single_date')) {
                $query->whereDate('created_at', $request->single_date);
            } elseif ($request->date_type === 'range') {
                if ($request->filled('date_from')) {
                    $query->whereDate('created_at', '>=', $request->date_from);
                }
                if ($request->filled('date_to')) {
                    $query->whereDate('created_at', '<=', $request->date_to);
                }
            }
        }

        // Get total sums based on the SAME filtered order IDs
        $filteredOrderIds = (clone $query)->pluck('id');
        
        $dealerPointsSum = \App\Models\RewardTransaction::whereIn('order_id', $filteredOrderIds)
            ->whereHas('member', function ($q) {
                $q->where('role', 'dealer');
            })->sum('points');

        $salesmanPointsSum = \App\Models\RewardTransaction::whereIn('order_id', $filteredOrderIds)
            ->whereHas('member', function ($q) {
                $q->where('role', 'salesman');
            })->sum('points');

        $history = $query->orderBy('id', 'desc')->paginate(10);

        // Orders for dropdown
        $allOrders = \App\Models\Order::with(['member.salesman'])
            ->where('status', '!=', 'Pending')
            ->where('order_number', 'like', 'ORD-%')
            ->orderBy('id', 'desc')
            ->get();

        $orders = $allOrders->map(function($ord) {
            return [
                'id' => $ord->id,
                'order_number' => $ord->order_number,
                'dealer' => $ord->member->name . ($ord->member->shop ? ' (' . $ord->member->shop . ')' : ''),
                'salesman' => $ord->member->salesman ? $ord->member->salesman->name : 'No Salesman Assigned'
            ];
        });

        $cities = \App\Models\City::where('status', 1)->orderBy('city')->get();
        $salesmen = \App\Models\Member::where('role', 'salesman')->orderBy('name')->get();
        $distributors = \App\Models\Member::where('role', 'distributor')->orderBy('name')->get();

        return view('rewards', compact('dealerPointsSum', 'salesmanPointsSum', 'history', 'orders', 'cities', 'salesmen', 'distributors'));
    }

    public function priceList() {
        return view('price-list');
    }

    public function passbook(Request $request) {
        $tab = $request->query('tab', 'dealer');
        $role = $tab === 'distributor' ? 'distributor' : 'dealer';

        $query = Member::where('role', $role)->with(['dealerBalance', 'salesman', 'distributor', 'city'])->orderBy('name', 'asc');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('shop', 'like', "%$search%");
            });
        }

        $dealers = $query->paginate(10);
        $allDealers = Member::where('role', $role)->orderBy('name', 'asc')->get();

        return view('passbook', compact('dealers', 'allDealers', 'tab'));
    }

    public function allTransactions(Request $request) {
        $query = \App\Models\PassbookTransaction::with(['member.salesman', 'member.distributor', 'member.city'])->orderBy('created_at', 'desc');

        $salesmen = Member::where('role', 'salesman')->get();
        $admins = \App\Models\User::all();
        $distributors = \App\Models\Member::where('role', 'distributor')->get();

        $transactions = $query->get()->map(function($txn) use ($distributors) {
            $member = $txn->member;
            $distributorName = $distributors->firstWhere('dist_id', $member->dist_id)->name ?? $member->dist_id ?? '';
            
            return [
                'date' => $txn->created_at->format('Y-m-d'),
                'dealer' => $member->shop ?? $member->name,
                'user' => $txn->managed_by,
                'type' => $txn->type,
                'amount' => (float) $txn->amount,
                'ref' => $txn->ref,
                'status' => $txn->status,
                // Member details for modal
                'member_details' => [
                    'name' => $member->name,
                    'email' => $member->email,
                    'mobile' => $member->mobile,
                    'code' => $member->ref_code ?? '',
                    'role' => 'Dealer',
                    'address' => preg_replace('/\r|\n/', ' ', $member->address ?? ''),
                    'shop' => $member->shop ?? '',
                    'city' => $member->city->city ?? '',
                    'gst' => $member->gst_no ?? '',
                    'discount' => $member->discount_percent ?? '',
                    'salesman' => $member->salesman->name ?? '',
                    'distributor' => $distributorName
                ]
            ];
        });

        return view('transactions', compact('transactions', 'salesmen', 'admins', 'distributors'));
    }

    public function updateBalance(Request $request) {
        $request->validate([
            'member_id' => 'required|exists:members,id',
            'adjustment_type' => 'required|in:add,payment',
            'amount' => 'required|numeric|min:0.01',
        ]);

        $dealer = Member::findOrFail($request->member_id);
        $amount = (float) $request->amount;
        $type = $request->adjustment_type;

        $txnInfo = \Illuminate\Support\Facades\DB::transaction(function() use ($dealer, $amount, $type) {
            $balance = $dealer->dealerBalance;
            if (!$balance) {
                $balance = new \App\Models\DealerBalance([
                    'member_id' => $dealer->id,
                    'total_amount' => 0.00,
                    'paid_amount' => 0.00,
                    'due_amount' => 0.00,
                ]);
            }

            if ($type === 'add') {
                $balance->total_amount += $amount;
                $txnType = 'Order';
                $txnStatus = 'Confirmed';
                $ref = 'ORD-' . mt_rand(1000, 9999);
            } else {
                $balance->paid_amount += $amount;
                $txnType = 'Payment';
                $txnStatus = 'Completed';
                $ref = 'TXN-' . mt_rand(1000, 9999);
            }

            $balance->due_amount = $balance->total_amount - $balance->paid_amount;
            $balance->save();

            // Ensure unique reference
            while (\App\Models\PassbookTransaction::where('ref', $ref)->exists()) {
                if ($type === 'add') {
                    $ref = 'ORD-' . mt_rand(1000, 9999);
                } else {
                    $ref = 'TXN-' . mt_rand(1000, 9999);
                }
            }

            $managerName = auth()->user() ? auth()->user()->name : 'System Admin';

            \App\Models\PassbookTransaction::create([
                'member_id' => $dealer->id,
                'managed_by' => $managerName,
                'type' => $txnType,
                'amount' => $amount,
                'ref' => $ref,
                'status' => $txnStatus,
            ]);

            return [
                'ref' => $ref,
                'due_amount' => $balance->due_amount,
            ];
        });

        // Trigger push notification to Dealer
        $title = $type === 'add' ? 'Account Ledger Updated' : 'Payment Received';
        $body = $type === 'add'
            ? "A new bill of ₹ " . number_format($amount, 2) . " (Ref: {$txnInfo['ref']}) has been added to your account. Current outstanding due: ₹ " . number_format($txnInfo['due_amount'], 2) . "."
            : "Your payment of ₹ " . number_format($amount, 2) . " (Ref: {$txnInfo['ref']}) has been successfully received. Remaining outstanding due: ₹ " . number_format($txnInfo['due_amount'], 2) . ".";

        try {
            \App\Services\FcmService::sendPushNotification(
                $dealer,
                $title,
                $body,
                [
                    'type' => 'passbook',
                    'deeplink' => 'my-passbook',
                    'deep_link' => 'my-passbook',
                    'ref' => $txnInfo['ref'],
                    'amount' => (string) $amount,
                    'due_amount' => (string) $txnInfo['due_amount'],
                ]
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed to send passbook update push notification: " . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Dealer balance updated successfully!'
        ]);
    }


    public function users() {
        $users = \App\Models\User::all();
        return view('users', compact('users'));
    }

    public function verifyPayments() {
        $submissions = \App\Models\PaymentSubmission::with(['member.salesman', 'member.distributor', 'member.city'])
            ->orderBy('created_at', 'desc')
            ->get();
        $distributors = \App\Models\Member::where('role', 'distributor')->get();
        return view('payments.verify', compact('submissions', 'distributors'));
    }

    public function approvePayment(Request $request, $id) {
        $submission = \App\Models\PaymentSubmission::findOrFail($id);
        
        if ($submission->status !== 'Pending') {
            return redirect()->back()->with('error', 'This payment has already been processed.');
        }

        \Illuminate\Support\Facades\DB::transaction(function() use ($submission) {
            $submission->status = 'Approved';
            $submission->save();

            $dealer = $submission->member;
            $amount = (float) $submission->amount;

            // Update dealer balance
            $balance = $dealer->dealerBalance;
            if (!$balance) {
                $balance = new \App\Models\DealerBalance([
                    'member_id' => $dealer->id,
                    'total_amount' => 0.00,
                    'paid_amount' => 0.00,
                    'due_amount' => 0.00,
                ]);
            }
            $balance->paid_amount += $amount;
            $balance->due_amount = $balance->total_amount - $balance->paid_amount;
            $balance->save();

            $ref = 'TXN-' . mt_rand(1000, 9999);
            while (\App\Models\PassbookTransaction::where('ref', $ref)->exists()) {
                $ref = 'TXN-' . mt_rand(1000, 9999);
            }

            $managerName = auth()->user() ? auth()->user()->name : 'System Admin';

            \App\Models\PassbookTransaction::create([
                'member_id' => $dealer->id,
                'managed_by' => $managerName,
                'type' => 'Payment',
                'amount' => $amount,
                'ref' => $ref,
                'status' => 'Completed',
            ]);

            // Send push notification
            try {
                \App\Services\FcmService::sendPushNotification(
                    $dealer,
                    'Payment Verified & Approved',
                    "Your payment upload of ₹ " . number_format($amount, 2) . " has been approved! Remaining due: ₹ " . number_format($balance->due_amount, 2) . ".",
                    [
                        'type' => 'passbook',
                        'deeplink' => 'my-passbook',
                        'deep_link' => 'my-passbook',
                        'ref' => $ref,
                        'amount' => (string) $amount,
                        'due_amount' => (string) $balance->due_amount,
                    ]
                );
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Failed to send push notification: " . $e->getMessage());
            }
        });

        return redirect()->back()->with('success', 'Payment receipt approved successfully!');
    }

    public function rejectPayment(Request $request, $id) {
        $submission = \App\Models\PaymentSubmission::findOrFail($id);
        $submission->status = 'Rejected';
        $submission->remarks = $request->input('remarks');
        $submission->save();

        $dealer = $submission->member;
        $amount = (float) $submission->amount;

        // Send push notification
        try {
            \App\Services\FcmService::sendPushNotification(
                $dealer,
                'Payment Receipt Rejected',
                "Your payment of ₹ " . number_format($amount, 2) . " was rejected. Reason: " . ($submission->remarks ?: 'Invalid receipt image.'),
                [
                    'type' => 'passbook',
                    'deeplink' => 'my-passbook',
                    'deep_link' => 'my-passbook',
                ]
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed to send push notification: " . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Payment receipt rejected successfully!');
    }

    public function settings() {
        $whatsappNumber = \App\Models\Setting::get('whatsapp_number', '919876543210');
        return view('settings', compact('whatsappNumber'));
    }

    public function updateSettings(Request $request) {
        $request->validate([
            'whatsapp_number' => 'required|string',
        ]);

        \App\Models\Setting::set('whatsapp_number', $request->whatsapp_number);

        return redirect()->back()->with('success', 'Settings updated successfully!');
    }

    public function checkNewRequests(Request $request) {
        $lastEstimateId = $request->query('last_estimate_id', 0);
        $lastOrderId = $request->query('last_order_id', 0);

        $newEstimates = \App\Models\Estimate::where('id', '>', $lastEstimateId)->count();
        $newOrders = \App\Models\OrderRequest::where('id', '>', $lastOrderId)->count();

        $maxEstimateId = \App\Models\Estimate::max('id') ?? 0;
        $maxOrderId = \App\Models\OrderRequest::max('id') ?? 0;

        $lastReadEstId = \App\Models\Setting::get('last_read_estimate_id', 0);
        $lastReadOrdId = \App\Models\Setting::get('last_read_order_id', 0);

        $pendingEstimatesCount = \App\Models\Estimate::where('id', '>', $lastReadEstId)->where('status', 'Pending')->count();
        $pendingOrdersCount = \App\Models\OrderRequest::where('id', '>', $lastReadOrdId)->where('status', 'Pending')->count();

        return response()->json([
            'new_estimates' => $newEstimates,
            'new_orders' => $newOrders,
            'max_estimate_id' => $maxEstimateId,
            'max_order_id' => $maxOrderId,
            'pending_estimates_count' => $pendingEstimatesCount,
            'pending_orders_count' => $pendingOrdersCount,
        ]);
    }

    public function markNotificationsRead(Request $request) {
        $maxEstimateId = \App\Models\Estimate::max('id') ?? 0;
        $maxOrderId = \App\Models\OrderRequest::max('id') ?? 0;

        \App\Models\Setting::set('last_read_estimate_id', $maxEstimateId);
        \App\Models\Setting::set('last_read_order_id', $maxOrderId);

        return response()->json(['success' => true]);
    }

    public function deleteProcessedRequests(Request $request) {
        $estimatesQuery = \App\Models\Estimate::with(['member.salesman', 'member.distributor'])
            ->where('status', '!=', 'Pending');
        
        $orderRequestsQuery = \App\Models\OrderRequest::with(['member.salesman', 'member.distributor', 'order'])
            ->where('status', '!=', 'Pending');

        if ($request->filled('days')) {
            $days = (int) $request->days;
            if ($days > 0) {
                $estimatesQuery->where('updated_at', '>=', now()->subDays($days));
                $orderRequestsQuery->where('updated_at', '>=', now()->subDays($days));
            }
        }

        if ($request->filled('limit')) {
            $limit = (int) $request->limit;
            if ($limit > 0) {
                $estimatesQuery->limit($limit);
                $orderRequestsQuery->limit($limit);
            }
        }

        $processedEstimates = $estimatesQuery->orderBy('updated_at', 'desc')->get();
        $processedOrderRequests = $orderRequestsQuery->orderBy('updated_at', 'desc')->get();

        $deletedEstimatesCount = 0;
        foreach ($processedEstimates as $estimate) {
            $this->deleteAssociatedCronFiles($estimate->file_path);
            $this->deleteAssociatedCronFiles($estimate->response_file_path);
            $estimate->delete();
            $deletedEstimatesCount++;
        }

        $deletedOrdersCount = 0;
        foreach ($processedOrderRequests as $orderReq) {
            $this->deleteAssociatedCronFiles($orderReq->file_path);
            $orderReq->delete();
            $deletedOrdersCount++;
        }

        return response()->json([
            'success' => true,
            'message' => 'Processed estimate requests and order requests deleted successfully.',
            'counts' => [
                'deleted_estimates_count' => $deletedEstimatesCount,
                'deleted_order_requests_count' => $deletedOrdersCount,
            ],
            'data' => [
                'deleted_estimate_requests' => $processedEstimates,
                'deleted_order_requests' => $processedOrderRequests,
            ],
        ], 200);
    }

    public function deleteStalePendingRequests(Request $request) {
        $days = $request->filled('days') ? (int) $request->days : 7;
        if ($days <= 0) {
            $days = 7;
        }

        $cutoffDate = now()->subDays($days);

        $staleEstimates = \App\Models\Estimate::with(['member.salesman', 'member.distributor'])
            ->where('status', 'Pending')
            ->where('created_at', '<=', $cutoffDate)
            ->get();

        $staleOrderRequests = \App\Models\OrderRequest::with(['member.salesman', 'member.distributor', 'order'])
            ->where('status', 'Pending')
            ->where('created_at', '<=', $cutoffDate)
            ->get();

        $deletedEstimatesCount = 0;
        foreach ($staleEstimates as $estimate) {
            $this->deleteAssociatedCronFiles($estimate->file_path);
            $this->deleteAssociatedCronFiles($estimate->response_file_path);
            $estimate->delete();
            $deletedEstimatesCount++;
        }

        $deletedOrdersCount = 0;
        foreach ($staleOrderRequests as $orderReq) {
            $this->deleteAssociatedCronFiles($orderReq->file_path);
            $orderReq->delete();
            $deletedOrdersCount++;
        }

        return response()->json([
            'success' => true,
            'message' => "Pending estimate requests and order requests older than {$days} days deleted successfully.",
            'counts' => [
                'deleted_stale_estimates_count' => $deletedEstimatesCount,
                'deleted_stale_order_requests_count' => $deletedOrdersCount,
            ],
            'data' => [
                'deleted_estimate_requests' => $staleEstimates,
                'deleted_order_requests' => $staleOrderRequests,
            ],
        ], 200);
    }

    private function deleteAssociatedCronFiles($paths)
    {
        if (empty($paths)) {
            return;
        }

        $pathsArray = is_array($paths) ? $paths : [$paths];

        foreach ($pathsArray as $path) {
            if (!empty($path) && is_string($path)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($path);
                $fullUploadPath = base_path('uploads/' . ltrim(str_replace('\\', '/', $path), '/'));
                if (file_exists($fullUploadPath) && is_file($fullUploadPath)) {
                    @unlink($fullUploadPath);
                }
            }
        }
    }

    public function memberRewardOrders($id)
    {
        $member = \App\Models\Member::findOrFail($id);

        $redeemedPoints = abs(\App\Models\RewardTransaction::where('member_id', $member->id)
            ->where('points', '<', 0)
            ->sum('points'));

        $query = \App\Models\Order::where('status', '!=', 'Pending')
            ->where('order_number', 'like', 'ORD-%');

        if ($member->role === 'dealer') {
            $query->where('member_id', $member->id);
        } elseif ($member->role === 'salesman') {
            $query->whereHas('member', function($q) use ($member) {
                $q->where('salesman_id', $member->id);
            });
        } else {
            return response()->json(['success' => true, 'data' => collect([])]);
        }

        $orders = $query->orderBy('created_at', 'asc')->get();
        $accumulated = 0;

        $mappedOrders = $orders->map(function ($order) use ($member, &$accumulated, $redeemedPoints) {
            $transaction = \App\Models\RewardTransaction::where('member_id', $member->id)
                ->where('order_id', $order->id)
                ->first();
                
            $points = $transaction ? $transaction->points : 0;
            $unlockDays = $transaction ? $transaction->unlock_days : null;
                
            $isEditable = true;
            
            if ($points > 0 && $accumulated < $redeemedPoints) {
                $isEditable = false;
                $accumulated += $points;
            }
            
            return [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'points' => $points,
                'unlock_days' => $unlockDays,
                'date' => $order->created_at->format('d M Y'),
                'editable' => $isEditable
            ];
        });

        $finalOrders = $mappedOrders->reverse()->values();

        return response()->json(['success' => true, 'data' => $finalOrders]);
    }
}
