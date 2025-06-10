<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    public function InicieUnaSesion(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:30',
            'clave' => 'required|string|min:8',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.string' => 'El nombre debe ser una cadena de texto.',
            'nombre.max' => 'El nombre no puede tener más de 30 caracteres.',
            'clave.required' => 'La clave es obligatoria.',
            'clave.string' => 'La clave debe ser una cadena de texto.',
            'clave.min' => 'La clave debe tener al menos 8 caracteres.',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try {
            $resultado = DB::select('SELECT * FROM login(?, ?)', [
                $request->nombre,
                $request->clave,
            ]);

            if (count($resultado) > 0) {
                $user = $resultado[0];
                return response()->json([
                    'mensaje' => 'Bienvenido ' . $user->nombre,
                    'id_tipo' => $user->id_tipo,
                ], 200);
            } else {
                return response()->json(['mensaje' => 'Credenciales incorrectas'], 401);
            }
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al iniciar sesión: ' . $e->getMessage()
            ], 500);
        }
    }
}
