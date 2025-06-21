<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\User; // Necesario para createToken()

class LoginController extends Controller
{
    /**
     * Maneja la petición de inicio de sesión usando la función SQL
     * y sin usar nunca bcrypt ni Auth::attempt().
     */
    public function login(Request $request)
    {
        // 1) Validación de entrada
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:30',
            'clave' => 'required|string|min:1',
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.max' => 'El nombre no puede exceder 30 caracteres.',
            'clave.required' => 'La clave es obligatoria.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'mensaje' => 'Datos inválidos',
                'errores' => $validator->errors(),
            ], 422);
        }

        // 2) Invocar tu función SQL login(nombre, clave)
        $rows = DB::select('SELECT * FROM login(?, ?)', [
            $request->nombre,
            $request->clave,
        ]);

        // 3) Credenciales incorrectas si no devolvió filas
        if (empty($rows)) {
            return response()->json([
                'mensaje' => 'Credenciales incorrectas',
            ], 401);
        }

        // 4) Tomar el primer resultado
        $row = $rows[0];

        // 5) Cargar el modelo User para generar el token
        $user = User::find($row->id);
        if (!$user) {
            return response()->json([
                'mensaje' => 'Usuario validado en SQL, pero no existe en Eloquent',
            ], 500);
        }

        // 6) Generar el token de acceso de Sanctum
        $token = $user->createToken('auth_token_cliente')->plainTextToken;

        // 7) Cargar datos adicionales del cliente (si existen)
        $cliente = DB::table('clientes')
            ->where('id_usuario', $user->id)
            ->first();

        // 8) Devolver la respuesta con token y datos de usuario/cliente
        return response()->json([
            'mensaje' => 'Inicio de sesión exitoso',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'nombre' => $user->nombre,
                'id_tipo' => $user->id_tipo,
            ],
            'cliente' => $cliente,
        ], 200);
    }
}
