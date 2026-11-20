<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;

class ScanController extends Controller
{
    public function __invoke()
    {
        return view('student.scan', [
            // Pola alamat QR ruangan, dipakai pemindai untuk mengenali stiker.
            'roomUrlPrefix' => url('/r').'/',
        ]);
    }
}
