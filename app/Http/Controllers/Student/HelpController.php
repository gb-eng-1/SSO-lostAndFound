<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;

/**
 * Student Help and Support page.
 */
class HelpController extends Controller
{
    /** GET /student/help */
    public function index()
    {
        return view('student.help');
    }
}
