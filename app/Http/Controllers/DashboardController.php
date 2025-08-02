<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the user's dashboard.
     */
    public function index(): Response
    {
        // Cukup render halaman Dashboard.jsx
        return Inertia::render('Dashboard');
    }
}
