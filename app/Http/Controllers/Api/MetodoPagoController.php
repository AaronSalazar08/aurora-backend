<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MetodoPagoController extends Controller
{
    public function listarTipos()
    {
        try {
            $tipos = DB::select('SELECT * FROM mostrar_tipo_pago()');
            return response()->json($tipos);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'No se pudieron obtener los métodos de pago.',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }
}
