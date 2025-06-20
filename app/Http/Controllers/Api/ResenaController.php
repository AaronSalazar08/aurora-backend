<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

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

    public function agregarResena(Request $request)
    {
        // 1. Validar los datos de la reseña
        $request->validate([
            'codigo_pedido' => 'required|integer|exists:pedidos,codigo',
            'calificacion' => 'required|integer|between:1,5',
            'comentario' => 'nullable|string|max:500',
        ]);

        // 2. Obtener el cliente autenticado para asegurar que la reseña es suya
        $user = Auth::user();
        $cliente = DB::table('clientes')->where('id_usuario', $user->id)->first();
        if (!$cliente) {
            return response()->json(['error' => 'Cliente no encontrado.'], 404);
        }

        // 3. **Verificación de seguridad CRÍTICA**
        //    Asegurarse de que el pedido que se está reseñando pertenezca al cliente autenticado.
        $pedido = DB::table('pedidos')
            ->where('codigo', $request->codigo_pedido)
            ->where('identificacion_cliente', $cliente->identificacion)
            ->first();

        if (!$pedido) {
            return response()->json(['error' => 'Acción no autorizada. Este pedido no pertenece al usuario.'], 403); // 403 Forbidden
        }

        try {
            // 4. Llamar al procedimiento para agregar la reseña
            DB::statement(
                'CALL agregar_resena(?, ?, ?, ?)',
                [
                    $request->codigo_pedido,
                    $cliente->identificacion, // <-- Usamos la identificación segura obtenida del token
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


}
