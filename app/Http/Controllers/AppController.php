<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AppController extends Controller
{
    /**
     * Handle login page
     */
    public function login()
    {
        // Check if admin is already authenticated
        if (Auth::guard('admin')->check()) {
            return redirect()->route('app.dashboard');
        }
        
        // Return the Livewire login component view
        return view('app.login');
    }
    
    /**
     * Handle dashboard page
     */
    public function dashboard()
    {
        // Check if admin is authenticated
        if (!Auth::guard('admin')->check()) {
            return redirect()->route('app.login');
        }
        
        // Return the Livewire dashboard component view
        return view('app.dashboard');
    }
    
    /**
     * Serve the React application for all /app/* routes (legacy)
     */
    public function index()
    {
        return view('app');
    }
}
