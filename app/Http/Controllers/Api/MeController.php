<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;

class MeController extends Controller
{
    public function show()
    {
        return new UserResource(auth()->user()->load('roles'));
    }
}
