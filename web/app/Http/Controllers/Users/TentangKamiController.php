<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TentangKamiController extends Controller
{
    public function index()
    {
        return view('roles.users.tentangKami.index', [
            'title' => 'Tentang Kami'
        ]);
    }
}
