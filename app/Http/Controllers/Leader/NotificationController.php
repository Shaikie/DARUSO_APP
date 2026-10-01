<?php

namespace App\Http\Controllers\Leader;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index()
    {
        return view('leader.notifications.index');
    }

    public function create()
    {
        return view('leader.notifications.create');
    }

    public function store(Request $request)
    {
        //
    }

    public function show(string $id)
    {
        return view('leader.notifications.show');
    }

    public function edit(string $id)
    {
        return view('leader.notifications.edit');
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
