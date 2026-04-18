<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Officer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class OfficerController extends Controller
{
    public function index()
    {
        $officers = Officer::orderBy('full_name')->get();
        return view('admin.officers.index', compact('officers'));
    }

    public function create()
    {
        return view('admin.officers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:100|unique:officers,username',
            'full_name' => 'required|string|max:255',
            'password' => 'required|string|min:4',
        ]);

        Officer::create([
            'username' => $request->username,
            'full_name' => $request->full_name,
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('admin.officers.index')->with('success', 'Officer created.');
    }

    public function edit(Officer $officer)
    {
        return view('admin.officers.edit', compact('officer'));
    }

    public function update(Request $request, Officer $officer)
    {
        $request->validate([
            'username' => 'required|string|max:100|unique:officers,username,' . $officer->id,
            'full_name' => 'required|string|max:255',
            'password' => 'nullable|string|min:4',
        ]);

        $officer->username = $request->username;
        $officer->full_name = $request->full_name;
        $officer->is_active = $request->boolean('is_active');
        if ($request->filled('password')) {
            $officer->password = Hash::make($request->password);
        }
        $officer->save();

        return redirect()->route('admin.officers.index')->with('success', 'Officer updated.');
    }

    public function destroy(Officer $officer)
    {
        $officer->delete();
        return redirect()->route('admin.officers.index')->with('success', 'Officer deleted.');
    }
}
