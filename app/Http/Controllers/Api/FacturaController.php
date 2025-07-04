<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Mail;
use App\Mail\FacturaProcesadaMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
class FacturaController extends Controller
{
    public function listarFacturas()
    {
        try {
            $facturas = DB::select('SELECT * FROM mostrar_todas_las_facturas()');
            return response()->json($facturas, 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'No se pudieron obtener las facturas.',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

    public function misFacturas()
    {
        try {
            // Obtener el cliente autenticado de forma segura
            $user = Auth::user();
            $cliente = DB::table('clientes')->where('id_usuario', $user->id)->first();

            if (!$cliente) {
                return response()->json(['error' => 'Perfil de cliente no encontrado.'], 404);
            }

            // Consultar facturas y unir con clientes para obtener el nombre
            $facturas = DB::table('facturas as f')
                ->join('clientes as c', 'f.identificacion_cliente', '=', 'c.identificacion')
                ->where('f.identificacion_cliente', $cliente->identificacion)
                ->select(
                    'f.id',
                    'f.codigo_pedido',
                    'f.fecha as fecha_factura',
                    'f.monto_final',
                    'c.nombre',
                    'c.primer_apellido'
                )
                ->orderBy('f.id', 'desc')
                ->get();

            return response()->json($facturas, 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'No se pudieron cargar las facturas del cliente.',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

    public function porCliente($id)
    {
        try {
            $facturas = DB::select('SELECT * FROM mostrar_facturas_por_cliente(?)', [$id]);
            return response()->json($facturas, 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'No se pudieron obtener las facturas del cliente.',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

    public function detalle($id)
    {
        try {
            $detalle = DB::select('SELECT * FROM mostrar_detalle_factura(?)', [$id]);
            if (empty($detalle)) {
                return response()->json(['mensaje' => 'Factura no encontrada.'], 404);
            }
            return response()->json($detalle[0], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'No se pudo obtener el detalle de la factura.',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

    public function procesarFactura(Request $request, $codigo)
    {
        // ... (la validación del código del pedido sigue igual)
        if (!ctype_digit((string) $codigo)) {
            return response()->json(['error' => 'Código de pedido inválido.'], 400);
        }
        $codigoPedido = (int) $codigo;

        try {
            // ... (la verificación del pedido y la llamada al procedure siguen igual)
            DB::statement('CALL public.procesar_factura(?::int)', [$codigoPedido]);

            $row = DB::selectOne('SELECT public.get_factura_detalle(?::int) AS detalle', [$codigoPedido]);

            if (!$row || $row->detalle === null) {
                return response()->json(['error' => 'Factura creada, pero no se pudo obtener el detalle.'], 500);
            }

            $detalle = json_decode($row->detalle, true);
            $productos = $detalle['productos'] ?? [];

            // --- INICIO DE LA MEJORA ---
            // Encapsulamos el envío de correo para que no detenga el proceso principal si falla.
            try {
                if (isset($detalle['cliente']['identificacion'])) {
                    $ident = $detalle['cliente']['identificacion'];
                    $correoRow = DB::selectOne("
                        SELECT ce.nombre as email 
                        FROM clientes c
                        JOIN contacto co ON co.identificacion_cliente = c.identificacion
                        JOIN correo_electronico ce ON ce.id = co.id_correo_electronico
                        WHERE c.identificacion = ?
                    ", [$ident]);

                    if ($correoRow && isset($correoRow->email)) {
                        Mail::to($correoRow->email)->send(new FacturaProcesadaMail($detalle, $productos));
                    }
                }
            } catch (\Exception $emailException) {
                // Si el correo falla, lo registramos en los logs de Laravel en lugar de fallar.
                Log::error("Fallo al enviar correo de factura para pedido {$codigoPedido}: " . $emailException->getMessage());
            }
            // --- FIN DE LA MEJORA ---

            // Retornar el detalle de la factura al frontend (esto se ejecutará incluso si el email falla)
            return response()->json([
                'mensaje' => 'Factura procesada exitosamente.',
                'factura' => $detalle
            ], 200);

        } catch (QueryException $e) {
            // ... (El manejo de errores de la base de datos sigue igual)
            // ...
            return response()->json([
                'error' => 'Error de base de datos al procesar la factura.',
                'detalle' => $e->getMessage()
            ], 500);

        } catch (\Exception $e) {
            // Este bloque ahora solo capturará otros errores inesperados.
            Log::error("Error inesperado en procesarFactura para pedido {$codigoPedido}: " . $e->getMessage());
            return response()->json([
                'error' => 'Error inesperado al procesar la factura.',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }



    public function actualizar(Request $request, $id)
    {
        $request->validate([
            'codigo_pedido' => 'required|integer',
            'id_productoscomprados' => 'required|integer',
            'identificacion_cliente' => 'required|integer',
            'monto' => 'required|numeric',
            'impuesto' => 'nullable|numeric',
            'descuento' => 'nullable|numeric',
            'monto_final' => 'required|numeric',
            'fecha' => 'required|date',
        ]);

        try {
            DB::statement(
                'CALL actualizar_factura(?, ?, ?, ?, ?, ?, ?, ?, ?)',
                [
                    $id,
                    $request->codigo_pedido,
                    $request->id_productoscomprados,
                    $request->identificacion_cliente,
                    $request->monto,
                    $request->impuesto,
                    $request->descuento,
                    $request->monto_final,
                    $request->fecha,
                ]
            );

            return response()->json(['mensaje' => 'Factura actualizada correctamente.'], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'No se pudo actualizar la factura.',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

    public function eliminar($id)
    {
        try {
            DB::statement('CALL eliminar_factura(?)', [$id]);

            return response()->json(['mensaje' => 'Factura eliminada correctamente.'], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'No se pudo eliminar la factura.',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }


    public function verDetalle($id)
    {
        if (!ctype_digit((string) $id)) {
            return response()->json(['error' => 'Número de factura inválido.'], 400);
        }

        try {
            // Primero, obtenemos la factura para encontrar su codigo_pedido
            $factura = DB::table('facturas')->where('id', $id)->first();

            if (!$factura) {
                return response()->json(['error' => 'La factura no existe.'], 404);
            }

            // Ahora, usamos el codigo_pedido para llamar a la función que devuelve el JSON completo
            $codigoPedido = $factura->codigo_pedido;
            $resultado = DB::selectOne('SELECT public.get_factura_detalle(?::int) AS detalle', [$codigoPedido]);

            if (!$resultado || $resultado->detalle === null) {
                return response()->json(['error' => 'No se pudo obtener el detalle completo para la factura.'], 404);
            }

            // Decodificamos el string JSON de la base de datos y lo enviamos como un objeto JSON real
            $detalleJson = json_decode($resultado->detalle, true);

            return response()->json($detalleJson, 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error inesperado al obtener el detalle de la factura.',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

}

