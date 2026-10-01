<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;

class NotificationController extends Controller
{
    public function index()
    {
        return view('student.notifications.index');
    }

    public function show(string $id)
    {
        return view('student.notifications.show');
    }
}
