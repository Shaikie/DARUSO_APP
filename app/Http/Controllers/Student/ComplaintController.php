<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ComplaintController extends Controller
{
    public function index()
    {
        return view('student.complaints.index');
    }

    public function create()
    {
        return view('student.complaints.create');
    }

    public function store(Request $request)
    {
        //
    }

    public function show(string $id)
    {
        return view('student.complaints.show');
    }

    public function edit(string $id)
    {
        return view('student.complaints.edit');
    }

    public function update(Request $request, string $id)
    {
        //
    }

    public function destroy(string $id)
    {
        //
    }
}
