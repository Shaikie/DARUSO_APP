<?php

namespace App\Http\Controllers\Leader;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index()
    {
        return view('leader.students.index');
    }

    public function create()
    {
        return view('leader.students.create');
    }

    public function store(Request $request)
    {
        //
    }

    public function show(string $id)
    {
        return view('leader.students.show');
    }

    public function edit(string $id)
    {
        return view('leader.students.edit');
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
