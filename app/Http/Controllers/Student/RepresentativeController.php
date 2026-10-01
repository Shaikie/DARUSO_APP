<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\CommitteeMember;
use App\Models\LeaderAssignment;
use App\Models\LeadershipTerm;
use App\Models\Ministry;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Student-facing representatives directory.
 *
 * Representatives are resolved from the active leadership term, positions and
 * assignments — nothing is hard-coded. Contact details are deliberately omitted:
 * a student sees who represents them and for what, not everyone's phone number.
 */
class RepresentativeController extends Controller
{
    public function index(Request $request): View
    {
        $term = LeadershipTerm::where('is_active', true)->first()
            ?? LeadershipTerm::orderByDesc('start_date')->first();

        $assignments = $term !== null
            ? LeaderAssignment::query()
                ->with(['user', 'position', 'ministry'])
                ->where('leadership_term_id', $term->getKey())
                ->join('positions', 'positions.id', '=', 'leader_assignments.position_id')
                ->orderBy('positions.hierarchy_level')
                ->select('leader_assignments.*')
                ->get()
            : collect();

        $committees = $term !== null
            ? CommitteeMember::query()
                ->with(['user', 'committee'])
                ->where('leadership_term_id', $term->getKey())
                // Ordered after loading: the committee table is not joined here,
                // so ordering in SQL would reference a missing FROM entry.
                ->get()
                ->sortBy(fn (CommitteeMember $member): string => $member->committee?->name ?? '')
                ->values()
            : collect();

        return view('student.representatives.index', [
            'term' => $term,
            'assignments' => $assignments,
            'committees' => $committees,
            'ministries' => Ministry::orderBy('name')->get(),
            'positions' => Position::orderBy('hierarchy_level')->get(),
        ]);
    }
}
