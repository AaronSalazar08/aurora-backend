<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PedidoEstadoController extends Controller
{
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
            'nuevo_estado' => 'required|string|in:En proceso,Enviado,Entregado',
        ]);

        try {
            DB::statement('CALL actualizar_estado_pedido_personal_envios(?, ?)', [
                $request->codigo_pedido,
                $request->nuevo_estado
            ]);

            return response()->json(['mensaje' => 'Estado de pedido actualizado correctamente (envíos).'], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'No se pudo actualizar el estado del pedido.',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }
}