<?php

namespace App\Http\Controllers\Leader;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CommitteeController extends Controller
{
    public function index()
    {
        return view('leader.committees.index');
    }

    public function create()
    {
        return view('leader.committees.create');
    }

    public function store(Request $request)
    {
        //
    }

    public function show(string $id)
    {
        return view('leader.committees.show');
    }

    public function edit(string $id)
    {
        return view('leader.committees.edit');
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
