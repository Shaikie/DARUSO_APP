<?php

namespace Database\Seeders;

use App\Models\Committee;
use App\Models\CommitteeMember;
use App\Models\LeaderAssignment;
use App\Models\LeadershipTerm;
use App\Models\Ministry;
use App\Models\Position;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Populates the active leadership term with assignments and committee members.
 *
 * Everything is resolved by name from the organisation tables, which is what
 * makes the representatives page data-driven rather than hard-coded.
 */
class LeadershipSeeder extends Seeder
{
    public function run(): void
    {
        $term = LeadershipTerm::where('is_active', true)->first();

        if ($term === null) {
            return;
        }

        $termId = $term->getKey();

        $assign = function (string $email, string $positionName, ?string $ministryName = null) use ($termId): void {
            $user = User::where('email', $email)->first();
            $position = Position::where('name', $positionName)->first();

            if ($user === null || $position === null) {
                return;
            }

            $ministry = $ministryName !== null ? Ministry::where('name', $ministryName)->first() : null;

            LeaderAssignment::firstOrCreate([
                'user_id' => $user->getKey(),
                'position_id' => $position->getKey(),
                'leadership_term_id' => $termId,
            ], [
                'ministry_id' => $ministry?->getKey(),
            ]);
        };

        $addMember = function (string $email, string $committeeName, ?string $role = null) use ($termId): void {
            $user = User::where('email', $email)->first();
            $committee = Committee::where('name', $committeeName)->first();

            if ($user === null || $committee === null) {
                return;
            }

            CommitteeMember::firstOrCreate([
                'user_id' => $user->getKey(),
                'committee_id' => $committee->getKey(),
                'leadership_term_id' => $termId,
            ], [
                'role_in_committee' => $role,
            ]);
        };

        $assign('secretary@daruso.local', 'Secretary General');
        $assign('secretary@daruso.local', 'Minister', 'Academic Ministry');
        $assign('ministry@daruso.local', 'Minister', 'Welfare Ministry');
        $assign('committee@daruso.local', 'Minister', 'Finance Ministry');

        $addMember('secretary@daruso.local', 'Finance Committee', 'Chairperson');
        $addMember('committee@daruso.local', 'Finance Committee', 'Member');
        $addMember('ministry@daruso.local', 'Disciplinary Committee', 'Member');
        $addMember('committee@daruso.local', 'Publicity Committee', 'Chairperson');
    }
}
