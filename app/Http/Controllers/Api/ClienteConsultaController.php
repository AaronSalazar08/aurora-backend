<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class ClienteConsultaController extends Controller
{
    public function todos()
    {
        try {
            $clientes = DB::select('SELECT * FROM mostrar_todos_los_clientes()');
            return response()->json($clientes);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'No se pudieron obtener los clientes.',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

    public function porIdentificacion($cedula)
    {
        try {
            $cliente = DB::select('SELECT * FROM buscar_cliente_por_identificacion(?)', [$cedula]);
            return response()->json($cliente);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'No se pudo obtener el cliente.',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

    public function porUsuario($id_usuario)
    {
        try {
            $cliente = DB::select('SELECT * FROM obtener_cliente_por_usuario(?)', [$id_usuario]);
            return response()->json($cliente);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'No se pudo obtener el cliente.',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }
}