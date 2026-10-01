<?php

namespace Database\Seeders;

use App\Enums\RoleName;
use App\Enums\StudentStatus;
use App\Models\LeaderProfile;
use App\Models\Role;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeds the documented development accounts plus a realistic student cohort.
 *
 * Idempotent via firstOrCreate on the email, so repeated seeding never
 * duplicates a user or a registration number.
 */
class UserSeeder extends Seeder
{
    /** Documented development password. */
    private const PASSWORD = 'password';

    public function run(): void
    {
        $roles = Role::pluck('id', 'name');

        // Documented accounts from docs/setup.md.
        $admin = $this->createUser('admin@daruso.local', 'System Administrator', [
            RoleName::Administrator->value,
        ]);

        $secretary = $this->createUser('secretary@daruso.local', 'Amina Secretary', [
            RoleName::SecretaryGeneral->value,
        ], leader: true);

        $ministryLeader = $this->createUser('ministry@daruso.local', 'Joseph Ministry', [
            RoleName::MinistryLeader->value,
        ], leader: true);

        $committeeLeader = $this->createUser('committee@daruso.local', 'Grace Committee', [
            RoleName::CommitteeLeader->value,
        ], leader: true);

        $student = $this->createUser('student@daruso.local', 'John Student', [
            RoleName::Student->value,
        ], profile: [
            'registration_number' => 'REG-2025-0001',
            'college' => 'College of Science',
            'school_faculty' => 'School of Engineering',
            'programme' => 'Computer Science',
            'year_of_study' => 2,
            'hostel' => 'Hostel A',
            'gender' => 'male',
            'status' => StudentStatus::Active->value,
        ]);

        // Additional students so listings, filtering and targeting are meaningful.
        $cohort = [
            ['Alice Johnson', 'alice@daruso.local', 'REG-2025-0002', 'Computer Science', 2, 'Hostel A', 'female'],
            ['Bob Smith', 'bob@daruso.local', 'REG-2025-0003', 'Electrical Engineering', 3, 'Hostel B', 'male'],
            ['Carol White', 'carol@daruso.local', 'REG-2025-0004', 'Mechanical Engineering', 1, 'Hostel A', 'female'],
            ['David Brown', 'david@daruso.local', 'REG-2025-0005', 'Civil Engineering', 4, 'Hostel C', 'male'],
            ['Erin Green', 'erin@daruso.local', 'REG-2025-0006', 'Computer Science', 3, 'Hostel B', 'female'],
            ['Frank Hall', 'frank@daruso.local', 'REG-2025-0007', 'Electrical Engineering', 2, 'Hostel C', 'male'],
            ['Grace King', 'grace@daruso.local', 'REG-2025-0008', 'Mechanical Engineering', 4, 'Hostel A', 'female'],
            ['Henry Wright', 'henry@daruso.local', 'REG-2025-0009', 'Computer Science', 1, null, 'male'],
            ['Ivy Scott', 'ivy@daruso.local', 'REG-2025-0010', 'Civil Engineering', 2, 'Hostel B', 'female'],
            ['Jack Adams', 'jack@daruso.local', 'REG-2025-0011', 'Mechanical Engineering', 3, 'Hostel C', 'male'],
            ['Karen Baker', 'karen@daruso.local', 'REG-2025-0012', 'Computer Science', 4, 'Hostel A', 'female'],
            ['Liam Nelson', 'liam@daruso.local', 'REG-2025-0013', 'Electrical Engineering', 1, 'Hostel B', 'male'],
        ];

        foreach ($cohort as [$name, $email, $registration, $programme, $year, $hostel, $gender]) {
            $this->createUser($email, $name, [RoleName::Student->value], profile: [
                'registration_number' => $registration,
                'college' => 'College of Science',
                'school_faculty' => 'School of Engineering',
                'programme' => $programme,
                'year_of_study' => $year,
                'hostel' => $hostel,
                'gender' => $gender,
                'status' => StudentStatus::Active->value,
            ]);
        }

        unset($admin, $secretary, $ministryLeader, $committeeLeader, $student, $roles);
    }

    /**
     * @param  array<int, string>  $roleNames
     * @param  array<string, mixed>|null  $profile
     */
    private function createUser(
        string $email,
        string $name,
        array $roleNames,
        bool $leader = false,
        ?array $profile = null,
    ): User {
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                // Hashed by the model's `hashed` cast on assignment.
                'password' => self::PASSWORD,
                'email_verified_at' => now(),
            ],
        );

        $user->roles()->sync(Role::whereIn('name', $roleNames)->pluck('id'));

        if ($leader) {
            LeaderProfile::firstOrCreate(
                ['user_id' => $user->getKey()],
                ['bio' => $name.' serves on the DARUSO leadership team.'],
            );
        }

        if ($profile !== null) {
            StudentProfile::firstOrCreate(
                ['user_id' => $user->getKey()],
                $profile,
            );
        }

        return $user;
    }
}
