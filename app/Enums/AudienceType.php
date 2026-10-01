<?php

namespace App\Enums;

/**
 * Targeting vocabulary for announcements, notifications, meetings and events.
 *
 * Broad types (`all_students`, `college`, …) are stored as a single rule and
 * resolved on read, so an "all students" announcement never materialises one
 * row per student. `Individual` rules carry a single user id in
 * `audience_value`; a message to several people is simply several rules.
 */
enum AudienceType: string
{
    case AllStudents = 'all_students';
    case AllLeaders = 'all_leaders';
    case College = 'college';
    case SchoolFaculty = 'school_faculty';
    case Programme = 'programme';
    case YearOfStudy = 'year_of_study';
    case Hostel = 'hostel';
    case Ministry = 'ministry';
    case Committee = 'committee';
    case Position = 'position';
    case Individual = 'individual';

    /**
     * Types whose `audience_value` resolves against the student profile table.
     */
    public function isStudentAttribute(): bool
    {
        return in_array($this, [
            self::College,
            self::SchoolFaculty,
            self::Programme,
            self::YearOfStudy,
            self::Hostel,
        ], true);
    }

    public function requiresValue(): bool
    {
        return $this !== self::AllStudents && $this !== self::AllLeaders;
    }

    public function label(): string
    {
        return match ($this) {
            self::AllStudents => 'All students',
            self::AllLeaders => 'All leaders',
            self::College => 'College',
            self::SchoolFaculty => 'School / Faculty',
            self::Programme => 'Programme',
            self::YearOfStudy => 'Year of study',
            self::Hostel => 'Hostel',
            self::Ministry => 'Ministry',
            self::Committee => 'Committee',
            self::Position => 'Position',
            self::Individual => 'Individual',
        };
    }

    /**
     * Column on `student_profiles` matched by this type, when applicable.
     */
    public function studentColumn(): ?string
    {
        return match ($this) {
            self::College => 'college',
            self::SchoolFaculty => 'school_faculty',
            self::Programme => 'programme',
            self::YearOfStudy => 'year_of_study',
            self::Hostel => 'hostel',
            default => null,
        };
    }

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
