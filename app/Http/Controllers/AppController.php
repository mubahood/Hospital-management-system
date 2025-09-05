<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AppController extends Controller
{
    /**
     * Serve the React application for all /app/* routes
     */
    public function index()
    {
        return view('app');
    }
}
