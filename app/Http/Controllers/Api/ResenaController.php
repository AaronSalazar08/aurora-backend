<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ResenaController extends Controller
{
    /**
     * Lista todas las reseñas.
     */
    public function listarResenas()
    {
        try {
            $resenas = DB::select('SELECT * FROM mostrar_todas_las_resenas()');
            return response()->json($resenas, 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'No se pudieron obtener las reseñas.',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Muestra el detalle de una reseña por su ID.
     */
    public function verdetalleResena($id)
    {
        try {
            $resultado = DB::select('SELECT * FROM mostrar_detalle_resena(?)', [$id]);

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

    /**
     * Agrega una nueva reseña.
     */
    public function agregarResena(Request $request)
    {
        $request->validate([
            'codigo_pedido' => 'required|integer|exists:pedidos,codigo',
            'identificacion_cliente' => 'required|integer|exists:clientes,identificacion',
            'calificacion' => 'required|integer|between:1,5',
            'comentario' => 'nullable|string',
        ]);

        try {
            DB::statement(
                'CALL agregar_resena(?, ?, ?, ?)',
                [
                    $request->codigo_pedido,
                    $request->identificacion_cliente,
                    $request->calificacion,
                    $request->comentario,
                ]
            );

            return response()->json(['mensaje' => 'Reseña creada correctamente.'], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'No se pudo crear la reseña.',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Elimina una reseña por su ID.
     */
    public function eliminarResena($id)
    {
        try {
            DB::statement('CALL eliminar_resena(?)', [$id]);
            return response()->json(['mensaje' => 'Reseña eliminada correctamente.'], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'No se pudo eliminar la reseña.',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }
}
