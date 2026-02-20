<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SistemaExternoResource;
use App\Models\SistemaExterno;

class SistemaExternoController extends Controller
{
    public function index()
    {
        return SistemaExternoResource::collection(
            SistemaExterno::where('activo', 1)->orderBy('codigo')->get()
        );
    }
}
