<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class PedidoController extends Controller
{
    public function agregarPedido(Request $request)
    {
        try {
            DB::statement('CALL agregar_pedidos(?, ?, ?, ?, ?)', [
                $request->codigo,
                $request->fecha_compra,
                $request->id_estado,
                $request->identificacion_cliente,
                $request->id_metodopago
            ]);

            return response()->json(['mensaje' => 'Pedido agregado correctamente.'], 200);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'No se pudo agregar el pedido.',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

    public function actualizarPedido(Request $request, $codigo)
    {
        try {
            DB::statement('CALL actualizar_pedidos(?, ?, ?, ?, ?)', [
                $codigo,
                $request->fecha_compra,
                $request->id_estado,
                $request->identificacion_cliente,
                $request->id_metodopago
            ]);

            return response()->json(['mensaje' => 'Pedido actualizado correctamente.'], 200);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'No se pudo actualizar el pedido.',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

    public function eliminarPedido($codigo)
    {
        try {
            DB::statement('CALL eliminar_pedido(?)', [$codigo]);

            return response()->json(['mensaje' => 'Pedido eliminado correctamente.'], 200);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'No se pudo eliminar el pedido.',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

    public function buscarPedido($codigo)
    {
        try {
            $resultado = DB::select('SELECT * FROM buscar_pedido(?)', [$codigo]);

            if (count($resultado) === 0) {
                return response()->json(['mensaje' => 'Pedido no encontrado.'], 404);
            }

            return response()->json($resultado[0], 200);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'No se pudo buscar el pedido.',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }
}
