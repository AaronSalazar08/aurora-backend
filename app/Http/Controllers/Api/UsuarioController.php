<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
class UsuarioController extends Controller
{


    public function listarPersonalEnvios()
    {
        try {
            $usuarios = DB::select('SELECT * FROM listar_personal_envios()');
            return response()->json($usuarios, 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'No se pudieron cargar los usuarios.',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }


    public function agregarPersonalEnvios(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'clave' => 'required|string|min:8|max:100'
        ]);

        try {
            DB::statement("CALL agregar_usuario_personal_envios(?, ?)", [
                $request->input('nombre'),
                $request->input('clave')
            ]);

            return response()->json([
                'mensaje' => 'Usuario de envíos agregado correctamente.'
            ], 201);

        } catch (\Exception $e) {
            $mensajeError = $e->getMessage();


            if (str_contains($mensajeError, 'ya existe')) {
                return response()->json([
                    'error' => 'El usuario ya existe. Intente con otro nombre.'
                ], 409);
            }

            if (str_contains($mensajeError, 'Tipo_usuario Personal Envios no existe')) {
                return response()->json([
                    'error' => 'El tipo de usuario "Personal Envios" no está definido en la base de datos.'
                ], 500);
            }

            return response()->json([
                'error' => 'Error inesperado al registrar el usuario.',
                'detalle' => $mensajeError
            ], 500);
        }
    }

    public function eliminarPersonalEnvios($id)
    {
        // 1. Validar que el ID sea un número entero válido.
        if (!ctype_digit((string) $id) || $id < 1) {
            return response()->json(['error' => 'El ID de usuario proporcionado no es válido.'], 400);
        }

        try {
            // 2. Llamar al procedimiento almacenado para eliminar el usuario.
            DB::statement('CALL public.eliminar_personal_envios(?)', [$id]);

            // 3. Si no hay excepciones, la eliminación fue exitosa.
            return response()->json(['mensaje' => 'Usuario de personal de envíos eliminado correctamente.'], 200);

        } catch (\Exception $e) {
            // 4. Manejar los errores específicos que definimos en el procedimiento.
            $mensajeError = $e->getMessage();

            if (str_contains($mensajeError, 'No se encontró un usuario')) {
                return response()->json(['error' => 'El usuario que intenta eliminar no existe.'], 404);
            }

            if (str_contains($mensajeError, 'no es Personal de Envíos')) {
                return response()->json(['error' => 'Acción no permitida: Este usuario no es personal de envíos.'], 403);
            }

            // 5. Devolver un error genérico para cualquier otro problema.
            return response()->json([
                'error' => 'Error inesperado al eliminar el usuario.',
                'detalle' => $mensajeError
            ], 500);
        }
    }





    public function AgregueUnUsuario(Request $request)
    {
        // 1. Validación de los datos de entrada
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

            $resultado_existe = DB::select('SELECT usuario_existe(?) AS usuario_ya_existe', [$request->nombre]);


            $usuarioExiste = $resultado_existe[0]->usuario_ya_existe ?? false;


            if ($usuarioExiste) { // Ahora esto compara el valor booleano 'true' o 'false'
                return response()->json(['mensaje' => 'El usuario ya existe'], 409);
            } else {

                DB::statement('CALL agregar_usuario(?, ?)', [
                    $request->nombre,
                    $request->clave
                ]);

                return response()->json(['mensaje' => 'Usuario insertado correctamente'], 201);
            }

        } catch (\Exception $e) {

            return response()->json(['error' => 'Error al insertar el usuario: ' . $e->getMessage()], 500);
        }
    }


    public function index()
    {
        $usuarios = DB::select('SELECT * FROM obtener_usuarios();');
        return response()->json($usuarios);
    }
}