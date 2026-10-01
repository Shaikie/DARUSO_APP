<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;

class RepresentativeController extends Controller
{
    public function index()
    {
        return view('student.representatives.index');
    }
}
