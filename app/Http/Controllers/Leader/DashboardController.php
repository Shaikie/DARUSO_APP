<?php

namespace App\Http\Controllers\Leader;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        return view('leader.dashboard');
    }
}
