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

            $user = Auth::user();


            $cliente = DB::table('clientes')->where('id_usuario', $user->id)->first();
            if (!$cliente) {
                return response()->json(['error' => 'No se encontró un perfil de cliente para este usuario.'], 404);
            }

            $codigoPedido = null;


            DB::statement('CALL crear_pedido_inicial(?, ?)', [
                $cliente->identificacion,
                &
                $codigoPedido
            ]);
            DB::beginTransaction();
            try {

                DB::statement("CALL crear_pedido_inicial(?, ?)", [$cliente->identificacion, 0]);


                $codigoPedido = DB::selectOne("SELECT currval('pedidos_codigo_seq') AS codigo_pedido")->codigo_pedido;

                DB::commit(); // Commit the transaction if everything is successful
            } catch (Exception $e) {
                DB::rollBack(); // Rollback on error
                throw $e; // Re-throw to be caught by the outer catch block
            }

            // 4. Devuelve el nuevo código de pedido que generó la base de datos.
            return response()->json([
                'mensaje' => 'Pedido inicial creado exitosamente.',
                'codigoPedido' => $codigoPedido
            ], 201);

        } catch (Exception $e) {
            // Log the error for debugging purposes
            \Log::error("Error al crear el pedido inicial: " . $e->getMessage(), ['exception' => $e]);

            return response()->json([
                'error' => 'Error al crear el pedido inicial.',
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
            // 1. Obtiene la identidad del usuario a partir del token. ¡Esto es seguro!
            $user = Auth::user();
            $cliente = DB::table('clientes')->where('id_usuario', $user->id)->first();

            if (!$cliente) {
                return response()->json(['error' => 'No se encontró un perfil de cliente para este usuario.'], 404);
            }

            // 2. Llama a la función de la BD con la identificación segura.
            $pedidos = DB::select('SELECT * FROM obtener_pedidos_completos_cliente(?)', [$cliente->identificacion]);

            // 3. Devuelve los pedidos.
            return response()->json($pedidos, 200);

        } catch (Exception $e) {
            return response()->json([
                'error' => 'No se pudieron cargar los pedidos.',
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

        } catch (Exception $e) {
            return response()->json([
                'error' => 'No se pudo procesar el pedido.',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

    public function pedidosParaResena()
    {
        try {
            // 1. Identificar al usuario de forma segura con el token
            $user = Auth::user();
            $cliente = DB::table('clientes')->where('id_usuario', $user->id)->first();

            if (!$cliente) {
                return response()->json(['error' => 'Perfil de cliente no encontrado.'], 404);
            }

            // 2. Llamar a la función de la BD que creamos, pasándole la identificación segura
            $pedidos = DB::select('SELECT * FROM public.obtener_pedidos_para_resena(?)', [$cliente->identificacion]);

            // 3. Devolver la lista de pedidos
            return response()->json($pedidos, 200);

        } catch (Exception $e) {
            return response()->json([
                'error' => 'No se pudieron cargar los pedidos para reseñar.',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

    public function limpiarCarrito(Request $request, $codigoPedido)
    {
        try {
            // Optional: Basic authorization check. Ensure the user owns this pedido.
            // This requires fetching the pedido and checking its identificacion_cliente against Auth::user()
            $user = Auth::user();
            $cliente = DB::table('clientes')->where('id_usuario', $user->id)->first();

            if (!$cliente) {
                return response()->json(['error' => 'No se encontró un perfil de cliente para este usuario.'], 404);
            }

            $pedido = DB::table('pedidos')
                ->where('codigo', $codigoPedido)
                ->where('identificacion_cliente', $cliente->identificacion)
                ->first();

            if (!$pedido) {
                return response()->json(['error' => 'Pedido no encontrado o no pertenece a este usuario.'], 404);
            }

            // Call the PostgreSQL procedure to clear the cart items
            DB::statement('CALL limpiar_carrito_por_pedido(?)', [$codigoPedido]);

            return response()->json([
                'mensaje' => 'Carrito limpiado exitosamente para el pedido ' . $codigoPedido
            ], 200);

        } catch (Exception $e) {
            \Log::error("Error al limpiar el carrito para pedido {$codigoPedido}: " . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'error' => 'Error al limpiar el carrito.',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

}
