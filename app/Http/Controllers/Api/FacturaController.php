<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Mail;
use App\Mail\FacturaProcesadaMail;

class FacturaController extends Controller
{
    public function todas()
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

   public function procesarFactura(Request $request)
    {
        $request->validate([
            'codigo_pedido' => 'required|integer'
        ]);

        try {
            // Ejecutar el procedimiento almacenado que crea la factura
        DB::statement('CALL procesar_factura(?)', [$request->codigo_pedido]);

            // Obtener la última factura creada, forzando los campos de dinero como numeric
        $factura = DB::selectOne('
            SELECT 
                id, 
                codigo_pedido,
                id_productoscomprados,
                identificacion_cliente,
                CAST(monto AS numeric) AS monto,
                CAST(impuesto AS numeric) AS impuesto,
                CAST(descuento AS numeric) AS descuento,
                CAST(monto_final AS numeric) AS monto_final,
                fecha
            FROM facturas 
            ORDER BY id DESC 
            LIMIT 1
        ');

        // Obtener productos del carrito (con cast para evitar problema del tipo money)
        $productos = DB::select("
            SELECT 
                p.nombre, 
                pec.cantidad, 
                CAST(pec.precio AS numeric) AS precio
            FROM productos_en_carrito pec
            JOIN productos p ON p.codigo = pec.codigo_producto
            WHERE pec.codigo_pedido = ?
        ", [$request->codigo_pedido]);

        // Obtener correo y nombre del cliente
        $correo = DB::selectOne("
            SELECT ce.nombre as email, c.nombre || ' ' || c.primer_apellido AS cliente_nombre
            FROM clientes c
            JOIN contacto co ON co.identificacion_cliente = c.identificacion
            JOIN correo_electronico ce ON ce.id = co.id_correo_electronico
            WHERE c.identificacion = ?
        ", [$factura->identificacion_cliente]);

        // Convertir los campos a float de forma segura
        $factura->monto = (float) $factura->monto;
        $factura->impuesto = (float) $factura->impuesto;
        $factura->descuento = (float) $factura->descuento;
        $factura->monto_final = (float) $factura->monto_final;

        foreach ($productos as $prod) {
            $prod->precio = (float) $prod->precio;
        }

        // Agregar nombre completo del cliente al objeto factura
        $factura->cliente_nombre = $correo->cliente_nombre;

        // Enviar el correo
        Mail::to($correo->email)->send(new FacturaProcesadaMail($factura, $productos));

        return response()->json(['mensaje' => 'Factura procesada y enviada por correo exitosamente.'], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'No se pudo procesar la factura.',
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
}

