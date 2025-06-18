<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class PedidoController extends Controller
{


    public function listarPedidos()
    {
        try {
            $pedidos = DB::select('SELECT * FROM mostrar_todos_los_pedidos()');
            return response()->json($pedidos, 200);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'No se pudieron cargar los pedidos.',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

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

    public function pedidosPorCliente($cedula)
{
    try {
        $pedidos = DB::select('SELECT * FROM mostrar_pedidos_del_cliente(?)', [$cedula]);

        return response()->json($pedidos);
    } catch (Exception $e) {
        return response()->json([
            'error' => 'No se pudieron obtener los pedidos del cliente.',
            'detalle' => $e->getMessage()
        ], 500);
    }
}

public function pendientes() {
    return response()->json(DB::select('SELECT * FROM mostrar_pedidos_pendientes()'));
}

public function enProceso() {
    return response()->json(DB::select('SELECT * FROM mostrar_pedidos_en_proceso()'));
}

public function enviados() {
    return response()->json(DB::select('SELECT * FROM mostrar_pedidos_enviados()'));
}

public function entregados() {
    return response()->json(DB::select('SELECT * FROM mostrar_pedidos_entregados()'));
}


}
