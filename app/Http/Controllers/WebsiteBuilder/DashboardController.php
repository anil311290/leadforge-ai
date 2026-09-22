<?php

namespace App\Http\Controllers\WebsiteBuilder;

use App\Http\Controllers\Controller;
use App\Models\BusinessWebsite;

class DashboardController extends Controller
{
    public function index()
    {
        $websites = BusinessWebsite::latest()->limit(6)->get();

        return view('website-builder.dashboard', compact('websites'));
    }
}
