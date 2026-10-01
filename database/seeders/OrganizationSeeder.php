<?php

namespace Database\Seeders;

use App\Models\Committee;
use App\Models\LeadershipTerm;
use App\Models\Ministry;
use App\Models\Position;
use Illuminate\Database\Seeder;

/**
 * Seeds the database-driven organisation structure.
 *
 * Idempotent: rerunning the seeder reuses existing records rather than
 * duplicating them, so `db:seed` is safe to repeat during development.
 */
class OrganizationSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        LeadershipTerm::updateOrCreate(
            ['name' => '2024/2025'],
            [
                'start_date' => $now->copy()->subYears(1)->startOfYear()->toDateString(),
                'end_date' => $now->copy()->startOfYear()->subDay()->toDateString(),
                'is_active' => false,
            ],
        );

        LeadershipTerm::updateOrCreate(
            ['name' => $now->format('Y').'/'.((int) $now->format('Y') + 1)],
            [
                'start_date' => $now->copy()->startOfYear()->toDateString(),
                'end_date' => $now->copy()->addYear()->subDay()->toDateString(),
                'is_active' => true,
            ],
        );

        foreach ([
            ['name' => 'President', 'description' => 'Presides over DARUSO meetings and represents the organisation.', 'hierarchy_level' => 1],
            ['name' => 'Vice President', 'description' => 'Supports the President and acts in their absence.', 'hierarchy_level' => 2],
            ['name' => 'Secretary General', 'description' => 'Coordinates records, communication and day-to-day operations.', 'hierarchy_level' => 3],
            ['name' => 'Deputy Secretary General', 'description' => 'Assists the Secretary General with records and communication.', 'hierarchy_level' => 4],
            ['name' => 'Treasurer', 'description' => 'Oversees finances, budgets and disbursement of funds.', 'hierarchy_level' => 5],
            ['name' => 'Auditor', 'description' => 'Reviews financial records and reports to the assembly.', 'hierarchy_level' => 6],
            ['name' => 'Publicity Secretary', 'description' => 'Manages public communications and publicity materials.', 'hierarchy_level' => 7],
            ['name' => 'Minister', 'description' => 'Leads a designated ministry portfolio.', 'hierarchy_level' => 8],
        ] as $position) {
            Position::updateOrCreate(['name' => $position['name']], $position);
        }

        foreach ([
            ['name' => 'Academic Ministry', 'description' => 'Academic affairs, examinations and student academic welfare.'],
            ['name' => 'Welfare Ministry', 'description' => 'Student welfare, hardship cases and support programmes.'],
            ['name' => 'Hostel Ministry', 'description' => 'Hostel affairs, accommodation and hostel student representation.'],
            ['name' => 'Health Ministry', 'description' => 'Health outreach, first aid and wellbeing campaigns.'],
            ['name' => 'Infrastructure Ministry', 'description' => 'Campus infrastructure, facilities and maintenance requests.'],
            ['name' => 'Finance Ministry', 'description' => 'Budgeting, fundraising and financial transparency.'],
            ['name' => 'Publicity Ministry', 'description' => 'Communication, media and publicity campaigns.'],
            ['name' => 'Discipline Ministry', 'description' => 'Disciplinary matters and code of conduct enforcement.'],
        ] as $ministry) {
            Ministry::updateOrCreate(['name' => $ministry['name']], $ministry);
        }

        foreach ([
            ['name' => 'Finance Committee', 'description' => 'Reviews budgets, accounts and financial proposals.'],
            ['name' => 'Disciplinary Committee', 'description' => 'Handles disciplinary cases and recommends sanctions.'],
            ['name' => 'Publicity Committee', 'description' => 'Designs and publishes communication material.'],
            ['name' => 'Admissions Committee', 'description' => 'Handles admissions, registration and records.'],
            ['name' => 'Election Committee', 'description' => 'Oversees elections and electoral processes.'],
        ] as $committee) {
            Committee::updateOrCreate(['name' => $committee['name']], $committee);
        }
    }
}
