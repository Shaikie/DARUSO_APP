<?php

namespace App\Http\Controllers\Leader;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function index()
    {
        return view('leader.announcements.index');
    }

    public function create()
    {
        return view('leader.announcements.create');
    }

    public function store(Request $request)
    {
        //
    }

    public function edit(string $id)
    {
        return view('leader.announcements.edit');
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
