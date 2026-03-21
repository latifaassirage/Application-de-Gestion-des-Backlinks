<?php

namespace App\Http\Controllers;

use App\Models\BacklinkType;
use Illuminate\Http\Request;

class BacklinkTypeController extends Controller
{
    public function index()
    {
        return response()->json(BacklinkType::all());
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|unique:backlink_types,name']);
        $type = \App\Models\BacklinkType::create(['name' => $request->name]);
        return response()->json($type);
    }
}