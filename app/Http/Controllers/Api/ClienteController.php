<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ClienteController extends Controller
{

    public function actualizarCliente(Request $request, $identificacion)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'primer_apellido' => 'required|string|max:255',
            'segundo_apellido' => 'required|string|max:255',
        ]);

        try {
            DB::statement("CALL actualizar_cliente(?, ?, ?, ?)", [
                $identificacion,
                $request->input('nombre'),
                $request->input('primer_apellido'),
                $request->input('segundo_apellido'),
            ]);

            return response()->json(['mensaje' => 'Cliente actualizado exitosamente.'], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'No se pudo actualizar el cliente.',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

    public function eliminarCliente($identificacion)
    {
        try {
            DB::statement("CALL eliminar_cliente(?)", [$identificacion]);

            return response()->json(['mensaje' => 'Cliente eliminado exitosamente.'], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'No se pudo eliminar el cliente.',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

    public function porUsuario($idUsuario)
    {
        try {
            $rows = DB::select(
                <<<'SQL'
                SELECT 
                  c.identificacion,
                  c.nombre,
                  c.primer_apellido,
                  c.segundo_apellido,
                  c.id_direccion,
                  c.id_contacto,
                  c.cliente_con_descuento_proxima_facturacion
                FROM clientes AS c
                WHERE c.id_usuario = ?
                SQL,
                [$idUsuario]
            );
            if (empty($rows)) {
                return response()->json(['mensaje' => 'Cliente no encontrado'], 404);
            }
            return response()->json($rows[0], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error recuperando datos de cliente.',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }
}
