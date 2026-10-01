<?php

namespace App\Http\Controllers\Leader;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LeadershipController extends Controller
{
    public function index()
    {
        return view('leader.leadership.index');
    }

    public function create()
    {
        return view('leader.leadership.create');
    }

    public function store(Request $request)
    {
        //
    }

    public function show(string $id)
    {
        return view('leader.leadership.show');
    }

    public function edit(string $id)
    {
        return view('leader.leadership.edit');
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
