<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{



    // Display a listing of users
    public function index(Request $request)
    {
        if (!$request->user() || $request->user()->role !== 'admin') {
            return response()->json(['message' => 'Access denied'], 403);
        }
        $users = User::all(); // Get all users
        return response()->json($users);
    }

    // Store a newly created user
    public function store(Request $request)
    {
        if (!$request->user() || $request->user()->role !== 'admin') {
            return response()->json(['message' => 'Access denied'], 403);
        }
        // Validate the incoming request data
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|string|in:user,admin' // Ensure role is valid
        ]);

        // Create the user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role
        ]);

        return response()->json($user, 201); // Created response
    }

    // Display a specific user
    public function show($id, Request $request)
    {
        if (!$request->user() || $request->user()->role !== 'admin') {
            return response()->json(['message' => 'Access denied'], 403);
        }
        $user = User::findOrFail($id); // Find user by ID
        return response()->json($user);
    }

    // Update an existing user
    public function update(Request $request, $id)
    {
        if (!$request->user() || $request->user()->role !== 'admin') {
            return response()->json(['message' => 'Access denied'], 403);
        }
        $user = User::findOrFail($id); // Find user by ID

        // Validate the incoming request data
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|string|min:6|confirmed',
            'role' => 'required|string|in:user,admin'
        ]);

        // Update user details
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password ? Hash::make($request->password) : $user->password,
            'role' => $request->role
        ]);

        return response()->json($user);
    }

    // Delete a user
    public function destroy($id, Request $request)
    {
        if (!$request->user() || $request->user()->role !== 'admin') {
            return response()->json(['message' => 'Access denied'], 403);
        }
        $user = User::findOrFail($id); // Find user by ID
        $user->delete(); // Delete the user

        return response()->json(['message' => 'User deleted successfully']);
    }
}

