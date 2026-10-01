<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;

class EventController extends Controller
{
    public function index()
    {
        return view('student.events.index');
    }

    public function show(string $id)
    {
        return view('student.events.show');
    }
}
