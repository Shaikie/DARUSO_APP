<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;

class DocumentController extends Controller
{
    public function index()
    {
        return view('student.documents.index');
    }

    public function show(string $id)
    {
        return view('student.documents.show');
    }
}
