<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class testcontroller extends Controller
{
    public function testhalamanlogin()
    {
        return view ('auth.login');
    }

    public function testhalamanregister()
    {
        return view ('auth.register');
    }

    public function testdashboard()
    {
        return view ('dashboard.index');
    }
}
