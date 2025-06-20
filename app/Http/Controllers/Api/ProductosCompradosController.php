<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\QueryException;

class ProductosCompradosController extends Controller
{
    /**
     * Inserta un registro en productos_comprados recibiendo solo id_productos_en_carrito.
     * Devuelve JSON con el nuevo id insertado.
     */
    public function agregasProductosComprados(Request $request)
    {
        // 1) Validar que venga id_productos_en_carrito y exista en la tabla productos_en_carrito
        $validator = Validator::make($request->all(), [
            'id_productos_en_carrito' => [
                'required',
                'integer',
                'exists:productos_en_carrito,id'
            ],
        ], [
            'id_productos_en_carrito.exists' => 'El id_productos_en_carrito no corresponde a un registro válido.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Error de validación',
                'detalle' => $validator->errors(),
            ], 422);
        }

        $idEnCarrito = $request->input('id_productos_en_carrito');

        try {
            // 2) Llamar al procedimiento almacenado que solo acepta un parámetro IN
            // No devuelve nada, por lo que usamos statement en lugar de select
            DB::statement('CALL public.agregar_producto_comprado(?::int)', [
                $idEnCarrito
            ]);

            // 3) Responder con mensaje de éxito
            return response()->json([
                'message' => 'Producto comprado registrado exitosamente.'
            ], 201);

        } catch (QueryException $e) {
            // Error en la consulta SQL, p.ej. violación de FK o RAISE en el procedimiento
            $msg = $e->getMessage();

            // Detectar mensaje personalizado de FK lanzado en el procedimiento: 
            // 'No existe producto_en_carrito con id = X'
            if (strpos($msg, 'No existe producto_en_carrito') !== false) {
                // Limpiar prefijo ERROR: si se desea
                $cleanMsg = preg_replace('/ERROR:\s*/i', '', $msg);
                return response()->json([
                    'error' => 'Referencia inválida',
                    'detalle' => $cleanMsg
                ], 400);
            }

            // Otros errores de BD
            return response()->json([
                'error' => 'Error de base de datos al insertar productos_comprados.',
                'detalle' => $msg,
            ], 500);

        } catch (\Exception $e) {
            // Cualquier otro error inesperado
            return response()->json([
                'error' => 'Error inesperado.',
                'detalle' => $e->getMessage(),
            ], 500);
        }
    }
}
