<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $labels = ['Today'];
        $in = Attendance::countAttendance(false);
        $out = Attendance::countAttendance(true);
        $data = User::where('is_admin', false)->count();
        return view('home', compact('data', 'labels', 'in', 'out'));
    }
}
