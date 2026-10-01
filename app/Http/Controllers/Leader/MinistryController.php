<?php

namespace App\Http\Controllers\Leader;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MinistryController extends Controller
{
    public function index()
    {
        return view('leader.ministries.index');
    }

    public function create()
    {
        return view('leader.ministries.create');
    }

    public function store(Request $request)
    {
        //
    }

    public function show(string $id)
    {
        return view('leader.ministries.show');
    }

    public function edit(string $id)
    {
        return view('leader.ministries.edit');
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
