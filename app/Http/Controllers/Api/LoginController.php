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
            // 2) Llamar a la función SQL
            $rows = DB::select('SELECT * FROM login(?, ?)', [
                $request->input('nombre'),
                $request->input('clave'),
            ]);

            // 3) Verificar credenciales
            if (empty($rows)) {
                return response()->json(
                    ['mensaje' => 'Credenciales incorrectas'],
                    401
                );
            }

            // 4) Devolver el primer registro (id, nombre, id_tipo)
            $user = $rows[0];
            return response()->json([
                'id' => $user->id,
                'nombre' => $user->nombre,
                'id_tipo' => $user->id_tipo,
            ], 200);

        } catch (\Exception $e) {
            // 5) Error interno
            return response()->json([
                'error' => 'Error interno: ' . $e->getMessage()
            ], 500);
        }
    }
}
