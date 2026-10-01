<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;

class MeetingController extends Controller
{
    public function index()
    {
        return view('student.meetings.index');
    }

    public function show(string $id)
    {
        return view('student.meetings.show');
    }
}
