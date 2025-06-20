<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
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

    public function agregarPedidoAuto(Request $request)
    {
        try {
            // Validación de entrada
            $data = $request->validate([
                'fecha_compra' => 'required|date_format:Y-m-d', // Ensure date format
                'identificacion_cliente' => 'required|integer', // Ensure it's required and an integer
                'id_estado' => 'sometimes|integer',
                'id_metodopago' => 'sometimes|integer',
            ]);

            // Define default values if not provided in the request
            $idEstado = $data['id_estado'] ?? 1;
            $idMetodoPago = $data['id_metodopago'] ?? 1;

            // Llamada al procedimiento con los 5 placeholders, incluyendo el de salida
            $rows = DB::select(
                'CALL public.agregar_pedidos_auto(?, ?, ?, ?, ?)',
                [
                    $data['fecha_compra'],
                    $idEstado,
                    $data['identificacion_cliente'], // This is where the error occurs if validation fails
                    $idMetodoPago,
                    null
                ]
            );

            // El OUT p_codigo viene como propiedad 'p_codigo' en la primera fila
            $codigoPedido = $rows[0]->p_codigo ?? null;

            if ($codigoPedido) {
                return response()->json([
                    'message' => 'Pedido creado exitosamente.',
                    'codigoPedido' => $codigoPedido
                ], 201);
            } else {
                return response()->json([
                    'error' => 'Pedido creado, pero no se pudo obtener el código.',
                    'detalle' => 'La base de datos no devolvió el código del pedido.'
                ], 500);
            }

        } catch (ValidationException $e) {
            // *** THIS IS THE CRUCIAL PART ***
            // Catch validation errors specifically and return them
            return response()->json([
                'error' => 'Error de validación de entrada.',
                'detalle' => $e->errors(), // This will show you exactly why validation failed
                'request_received' => $request->all() // Good for debugging what Laravel actually received
            ], 422); // 422 Unprocessable Entity is the standard status code for validation errors
        } catch (\Throwable $e) {
            // Catch any other general exceptions (e.g., database connection issues, procedure errors)
            return response()->json([
                'error' => 'No se pudo crear el pedido por un error interno.',
                'detalle' => $e->getMessage(),
                // 'trace' => $e->getTraceAsString(), // Uncomment for detailed debugging
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
