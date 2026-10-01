<?php

namespace App\Http\Controllers\Leader;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function index()
    {
        return view('leader.documents.index');
    }

    public function create()
    {
        return view('leader.documents.create');
    }

    public function store(Request $request)
    {
        //
    }

    public function show(string $id)
    {
        return view('leader.documents.show');
    }

    public function edit(string $id)
    {
        return view('leader.documents.edit');
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
