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
        $request->validate([
            'fecha_compra' => 'required|date',
            'identificacion_cliente' => 'required|integer|exists:clientes,identificacion',
            // 'id_estado' y 'id_metodopago' los puedes omitir si aún no los defines
        ]);

        try {
            // Llamamos al procedure que devuelve el código
            $result = DB::select('CALL public.agregar_pedidos_auto(?, ?, ?, ?)', [
                $request->input('fecha_compra'),
                $request->input('id_estado', 1),              // provisionales
                $request->input('identificacion_cliente'),
                $request->input('id_metodopago', 1)
            ]);
            // $result[0]->p_codigo contendrá el nuevo código
            $nuevoCodigo = $result[0]->p_codigo ?? null;

            return response()->json([
                'mensaje' => 'Pedido creado correctamente.',
                'codigoPedido' => $nuevoCodigo
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'No se pudo crear el pedido.',
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

    public function pendientes()
    {
        return response()->json(DB::select('SELECT * FROM mostrar_pedidos_pendientes()'));
    }

    public function enProceso()
    {
        return response()->json(DB::select('SELECT * FROM mostrar_pedidos_en_proceso()'));
    }

    public function enviados()
    {
        return response()->json(DB::select('SELECT * FROM mostrar_pedidos_enviados()'));
    }

    public function entregados()
    {
        return response()->json(DB::select('SELECT * FROM mostrar_pedidos_entregados()'));
    }

    public function procesarDesdeCarrito(Request $request)
    {
        $request->validate([
            'identificacion_cliente' => 'required|integer|exists:clientes,identificacion',
            'id_metodopago' => 'required|integer',
        ]);

        try {
            // Llamamos al procedimiento que envuelve todo el flujo
            DB::statement('CALL public.procesar_pedido_desde_carrito(?, ?)', [
                $request->identificacion_cliente,
                $request->id_metodopago,
            ]);

            return response()->json([
                'mensaje' => 'Pedido procesado y factura generada correctamente.'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'No se pudo procesar el pedido.',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }


}
