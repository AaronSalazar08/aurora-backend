<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User; // <-- IMPORTANTE: Aún necesitamos el modelo User para generar el token

class LoginController extends Controller
{
    /**
     * Maneja la petición de inicio de sesión usando la función de la base de datos.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        // 1. Validar la entrada
        $request->validate([
            'nombre' => 'required|string',
            'clave' => 'required|string',
        ]);

        try {
            // 2. Llamar a la función de la base de datos en lugar de Auth::attempt
            $userData = DB::select('SELECT * FROM login(?, ?)', [
                $request->nombre,
                $request->clave,
            ]);

            // 3. Verificar si la función devolvió un usuario
            if (empty($userData)) {
                // Si la función devuelve un array vacío, las credenciales son incorrectas
                return response()->json(['mensaje' => 'Las credenciales proporcionadas son incorrectas.'], 401);
            }

            // La función devolvió un usuario, tomamos el primer resultado
            $userFromDb = $userData[0];

            // 4. INDISPENSABLE: Obtener la instancia completa del modelo User
            // Para poder usar ->createToken(), necesitamos el objeto Eloquent, no solo datos crudos.
            $user = User::find($userFromDb->id);
            if (!$user) {
                // Esto no debería pasar si la función login funciona, pero es una buena verificación
                return response()->json(['mensaje' => 'Usuario encontrado pero no se pudo cargar el modelo.'], 500);
            }

            // 5. Generar el token de acceso de Sanctum
            $token = $user->createToken('auth_token_cliente')->plainTextToken;

            // 6. Obtener los datos del cliente asociado
            $cliente = DB::table('clientes')->where('id_usuario', $user->id)->first();

            // 7. Devolver la respuesta completa
            return response()->json([
                'mensaje' => 'Inicio de sesión exitoso',
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => [
                    'id' => $user->id,
                    'nombre' => $user->nombre,
                    'id_tipo' => $user->id_tipo,
                ],
                'cliente' => $cliente
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Ocurrió un error en el servidor al intentar iniciar sesión.',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }
}