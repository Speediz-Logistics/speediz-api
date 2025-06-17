<?php

namespace App\Http\Controllers\Admin;

use App\Constants\ConstUserRole;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Package;
use App\Models\Revenue;
use App\Models\User;
use App\Models\Vendor;
use App\Traits\BaseApiResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    use BaseApiResponse;

    //index
    public function index(Request $request)
    {
        //total vendors count
        $totalVendors = User::query()->where('role', ConstUserRole::VENDOR)->count();
        $totalPackages = Package::query()->count();
        $totalCompletedPackages = Package::query()->where('status', 'completed')->count();
        $totalPendingPackages = Package::query()->where('status', 'pending')->count();

        //total customers count
        $totalUsers = User::query()->where('role', ConstUserRole::VENDOR)->count() + User::query()->where('role', ConstUserRole::DELIVERY)->count();

        $vendors = User::query()->where('role', ConstUserRole::VENDOR)->get();

        $vendorsData = [];
        foreach ($vendors as $vendor) {
            $vendorData = [
                'vendor_id' => $vendor->id,
                'vendor_name' => Vendor::query()->where('user_id', $vendor->id)->first()?->first_name . ' ' . Vendor::query()->where('user_id', $vendor->id)->first()?->last_name,
                'vendor_address' => Vendor::query()->where('user_id', $vendor->id)->first()?->address,
                'total_delivery' => Package::query()->where('vendor_id', $vendor->id)->where('status', 'completed')->count(),
                'amount' => $amount = Package::query()
                    ->where('vendor_id', $vendor->id)
                    ->where('status', 'completed')
                    ->with('shipment')
                    ->get()
                    ->sum(fn($package) => $package->shipment->delivery_fee ?? 0),
            ];
            $vendorsData[] = $vendorData;
        }

        //package graph logic from Package model count by month for last 12 months
        $package_per_month = $this->packageChart();

        return $this->success([
            'total_users' => $totalUsers,
            'total_packages' => $totalPackages,
            'total_vendors' => $totalVendors,
            'total_sales' => $totalCompletedPackages,
            'package_per_month' => $package_per_month,
            'recent_vendors' => array_slice($vendorsData, 0, 2),
        ], 'Dashboard', 'Dashboard data fetched successfully');
    }

    private function packageChart()
    {
        $rawData = Package::query()
            ->selectRaw('COUNT(id) as total, YEAR(created_at) as year, MONTH(created_at) as month')
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        $monthLabels = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
        ];

        // Create a collection with all months from January to December
        $months = collect(range(1, 12))->mapWithKeys(fn($m) => [
            $m => ['month' => $monthLabels[$m], 'total' => 0]
        ]);

        // Populate the data from the query results
        $rawData->each(function ($item) use ($months) {
            $months->put($item->month, [
                'month' => $months[$item->month]['month'],
                'total' => $item->total
            ]);
        });

        return [
            'labels' => $months->pluck('month')->toArray(),
            'data' => $months->pluck('total')->toArray()
        ];
    }
}
