<?php

namespace App\Http\Controllers\Leader;

use App\Enums\PermissionName;
use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Reports and activity statistics.
 *
 * Every figure comes from an aggregate query in ReportService; no report loads a
 * full table into memory to count rows.
 */
class ReportController extends Controller
{
    public function __construct(
        private readonly ReportService $reports,
    ) {}

    public function index(Request $request): View
    {
        abort_unless(
            $request->user()->hasPermission(PermissionName::ReportView->value),
            403,
            'You do not have permission to view reports.'
        );

        return view('leader.reports.index', [
            'overview' => $this->reports->overview(),
            'auditActivity' => $this->reports->auditActivity(),
            'complaintCategories' => Complaint::query()
                ->select('category')
                ->selectRaw('count(*) as aggregate')
                ->groupBy('category')
                ->orderByDesc('aggregate')
                ->get(),
        ]);
    }
}
