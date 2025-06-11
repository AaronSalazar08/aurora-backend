<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class FacturaController extends Controller
{
    public function todas()
    {
        try {
            $facturas = DB::select('SELECT * FROM mostrar_todas_las_facturas()');
            return response()->json($facturas, 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'No se pudieron obtener las facturas.',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

    public function porCliente($id)
    {
        try {
            $facturas = DB::select('SELECT * FROM mostrar_facturas_por_cliente(?)', [$id]);
            return response()->json($facturas, 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'No se pudieron obtener las facturas del cliente.',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }
}