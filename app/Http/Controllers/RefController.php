<?php

namespace App\Http\Controllers;

use App\Models\Ref;
use Illuminate\Http\Request;

class RefController extends Controller
{
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            '*.name' => 'required|string|max:50|unique:refs,name', // Add other rules as necessary
        ]);
        foreach ($validatedData as $data) {
            Ref::create($data);
        }

        // Return a response or redirect
        return response()->json(['message' => 'Data saved successfully']);
    }
}
