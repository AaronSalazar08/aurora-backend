<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class ProductoFiltroController extends Controller
{
    public function ropa()
    {
        return response()->json(DB::select('SELECT * FROM mostrar_productos_ropa()'));
    }

    public function accesorios()
    {
        return response()->json(DB::select('SELECT * FROM mostrar_productos_accesorios()'));
    }

    public function moda()
    {
        return response()->json(DB::select('SELECT * FROM mostrar_productos_moda()'));
    }
} 
