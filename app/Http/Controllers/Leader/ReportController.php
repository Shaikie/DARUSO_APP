<?php

namespace App\Http\Controllers\Leader;

use App\Http\Controllers\Controller;

class ReportController extends Controller
{
    public function index()
    {
        return view('leader.reports.index');
    }
}
