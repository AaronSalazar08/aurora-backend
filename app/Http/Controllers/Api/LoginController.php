<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    /**
     * Maneja el login llamando únicamente a la función almacenada.
     */
    public function login(Request $request)
    {
        // 1) Validar entrada
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:30',
            'clave' => 'required|string|min:8',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'clave.required' => 'La clave es obligatoria.',
            'clave.min' => 'La clave debe tener al menos 8 caracteres.',
        ]);

        if ($validator->fails()) {
            return response()->json(
                $validator->errors(),
                422
            );
        }

        try {
            $rows = DB::select('SELECT * FROM login(?, ?)', [
                $request->input('nombre'),
                $request->input('clave'),
            ]);

            if (empty($rows)) {
                return response()->json(['mensaje' => 'Credenciales incorrectas'], 401);
            }

            $user = $rows[0];

            // 👉 Nueva consulta para obtener la identificacion del cliente
            $clienteRow = DB::selectOne(
                'SELECT identificacion FROM clientes WHERE id_usuario = ?',
                [$user->id]
            );
            $identificacion = $clienteRow->identificacion ?? null;

            return response()->json([
                'id' => $user->id,
                'nombre' => $user->nombre,
                'id_tipo' => $user->id_tipo,
                'identificacion' => $identificacion,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error interno: ' . $e->getMessage()
            ], 500);
        }
    }
}
