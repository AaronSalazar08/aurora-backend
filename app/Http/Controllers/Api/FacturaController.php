<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

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
            DB::statement('CALL procesar_factura(?)', [$request->codigo_pedido]);

            return response()->json(['mensaje' => 'Factura procesada exitosamente.'], 200);
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
