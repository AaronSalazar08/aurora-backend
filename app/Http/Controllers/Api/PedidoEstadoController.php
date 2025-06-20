<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PedidoEstadoController extends Controller
{

    public function listarEstadosAprobacion()
    {
        try {
            $estados = DB::select('SELECT * FROM listar_estados_pendiente_cancelado()');
            return response()->json($estados, 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'No se pudieron cargar los estados.',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }


    public function actualizarEstadoAdmin(Request $request)
    {
        $request->validate([
            'codigo_pedido' => 'required|integer',
            'nuevo_estado' => 'required|string|in:Pendiente,Cancelado',
        ]);

        try {
            DB::statement('CALL actualizar_estado_pedido_admin(?, ?)', [
                $request->codigo_pedido,
                $request->nuevo_estado
            ]);

            return response()->json(['mensaje' => 'Estado de pedido actualizado correctamente (admin).'], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'No se pudo actualizar el estado del pedido.',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

    public function actualizarEstadoEnvios(Request $request)
    {
        $request->validate([
            'codigo_pedido' => 'required|integer',
            'nuevo_estado' => 'required|integer|in:2,3,4',
        ]);

        try {
            DB::statement(
                'CALL actualizar_estado_pedido_personal_envios(?, ?)',
                [
                    $request->codigo_pedido,
                    $request->nuevo_estado
                ]
            );

            return response()->json([
                'mensaje' => 'Estado de pedido actualizado correctamente (envíos).'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'No se pudo actualizar el estado del pedido.',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }



    public function simularCambioDeEstado()
    {
        try {
            // Obtener pedidos que aún no están en "Entregado" o "Cancelado"
            $pedidos = DB::select("
            SELECT codigo, id_estado
            FROM pedidos
           WHERE id_estado IN (
    SELECT id FROM estado_pedidos WHERE tipo IN ('Pendiente', 'En proceso', 'Enviado')
)

        ");

            foreach ($pedidos as $pedido) {
                $nuevoEstado = match ($pedido->id_estado) {
                    1 => 2, // Pendiente → En proceso
                    2 => 3, // En proceso → Enviado
                    3 => 4, // Enviado → Entregado
                    default => $pedido->id_estado,
                };


                DB::update("UPDATE pedidos SET id_estado = ? WHERE codigo = ?", [$nuevoEstado, $pedido->codigo]);
            }

            return response()->json(['mensaje' => 'Estados actualizados automáticamente.'], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'No se pudieron actualizar los estados.',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

}