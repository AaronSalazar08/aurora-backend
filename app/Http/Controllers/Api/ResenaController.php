<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ResenaController extends Controller
{
    public function buscarResena($id)
    {
        try {
            $resultado = DB::select('SELECT * FROM buscar_resena(?)', [$id]);

            if (empty($resultado)) {
                return response()->json(['mensaje' => 'Reseña no encontrada.'], 404);
            }

            return response()->json($resultado[0], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'No se pudo obtener la reseña.',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }
}
