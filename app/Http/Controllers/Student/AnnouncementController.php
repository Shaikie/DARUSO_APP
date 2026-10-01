<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;

class AnnouncementController extends Controller
{
    public function index()
    {
        return view('student.announcements.index');
    }

    public function show(string $id)
    {
        return view('student.announcements.show');
    }
}
