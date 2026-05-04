<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class KontakController extends Controller
{
    public function index()
    {
        return view('roles.users.kontak.index', [
            'title' => 'Kontak'
        ]);
    }
}
