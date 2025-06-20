<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;

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

    public function actualizarMetodoPago(Request $request, $codigo)
    {
        try {
            // 1) Validar entrada: id_metodopago debe existir en metodos_pago(id)
            $data = $request->validate([
                'id_metodopago' => 'required|integer|exists:metodos_pago,id',
            ], [
                'id_metodopago.exists' => 'El método de pago especificado no existe.',
            ]);

            $idMetodoPago = $data['id_metodopago'];
            $codigoPedido = (int) $codigo;

            // 2) Verificar existencia del pedido antes de llamar al procedimiento
            $pedidoExists = DB::table('pedidos')
                ->where('codigo', $codigoPedido)
                ->exists();
            if (!$pedidoExists) {
                return response()->json([
                    'error' => 'Pedido no encontrado',
                    'detalle' => "No existe un pedido con código = $codigoPedido."
                ], 404);
            }

            // 3) Llamar al procedimiento. No devuelve filas, así que usamos statement.
            DB::statement('CALL public.actualizar_metodo_pago(?::int, ?::int)', [
                $codigoPedido,
                $idMetodoPago
            ]);

            // 4) Si llegamos aquí sin excepción, es éxito
            return response()->json([
                'message' => 'Método de pago actualizado correctamente.'
            ], 200);

        } catch (ValidationException $e) {
            // Error de validación de entrada
            return response()->json([
                'error' => 'Error de validación de entrada.',
                'detalle' => $e->errors(),
            ], 422);

        } catch (QueryException $e) {
            // Excepción de la base de datos (incluye RAISE EXCEPTION dentro del procedimiento)
            $msg = $e->getMessage();

            // Detectar mensaje de RAISE en el procedimiento: 'Método de pago con id = ... no existe'
            if (strpos($msg, 'Método de pago con id =') !== false) {
                // Limpiar prefijo ERROR: si se desea
                $cleanMsg = preg_replace('/ERROR:\s*/i', '', $msg);
                return response()->json([
                    'error' => 'Método de pago inválido',
                    'detalle' => $cleanMsg
                ], 400);
            }

            // Otros errores de BD
            return response()->json([
                'error' => 'Error de base de datos al actualizar método de pago.',
                'detalle' => $msg,
            ], 500);

        } catch (\Throwable $e) {
            // Cualquier otro error inesperado
            return response()->json([
                'error' => 'Error inesperado al actualizar método de pago.',
                'detalle' => $e->getMessage(),
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

    public function misPedidos(Request $request)
    {
        try {
            // 1. Obtener el usuario autenticado a través del token (Sanctum)
            $user = Auth::user();

            if (!$user) {
                return response()->json(['error' => 'No autenticado.'], 401);
            }

            // 2. Encontrar el perfil de cliente asociado usando el Query Builder
            // CAMBIO: Reemplazamos el modelo `Cliente::where(...)` por `DB::table(...)`
            $cliente = DB::table('clientes')->where('id_usuario', $user->id)->first();

            if (!$cliente) {
                return response()->json(['error' => 'No se encontró un perfil de cliente para este usuario.'], 404);
            }

            // 3. Usar la identificación del cliente para llamar a la función de la base de datos
            $identificacionCliente = $cliente->identificacion;
            $pedidos = DB::select('SELECT * FROM obtener_pedidos_completos_cliente(?)', [$identificacionCliente]);

            // 4. Devolver los pedidos como una respuesta JSON exitosa
            return response()->json($pedidos, 200);

        } catch (Exception $e) {
            // 5. Manejar cualquier error inesperado
            return response()->json([
                'error' => 'Ocurrió un error al procesar la solicitud.',
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
