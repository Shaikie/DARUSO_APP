<?php

namespace App\Http\Controllers\Leader;

use App\Http\Controllers\Controller;

class AuditLogController extends Controller
{
    public function index()
    {
        return view('leader.audit-logs.index');
    }
}
