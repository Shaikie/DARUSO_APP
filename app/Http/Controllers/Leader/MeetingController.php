<?php

namespace App\Http\Controllers\Leader;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MeetingController extends Controller
{
    public function index()
    {
        return view('leader.meetings.index');
    }

    public function create()
    {
        return view('leader.meetings.create');
    }

    public function store(Request $request)
    {
        //
    }

    public function show(string $id)
    {
        return view('leader.meetings.show');
    }

    public function edit(string $id)
    {
        return view('leader.meetings.edit');
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
