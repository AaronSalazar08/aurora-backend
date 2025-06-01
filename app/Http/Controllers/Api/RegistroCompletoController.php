<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RegistroCompletoController extends Controller
{
    /**
     * Método completo: realiza todo el proceso (usuario, dirección, teléfono, correo)
     */
    public function registrarTodo(Request $request)
    {
        try {
            DB::beginTransaction();

            // Paso 1: Determinar tipo de usuario
            $clave = $request->input('clave');
            $tipo_usuario = $this->determinarTipoUsuario($clave);

            // Paso 2: Insertar usuario
            $usuarioId = DB::table('usuario')->insertGetId([
                'nombre' => $request->input('nombre'),
                'clave' => $clave,
                'id_tipo' => $tipo_usuario
            ]);

            // Paso 3: Insertar dirección
            $direccionId = DB::table('direccion')->insertGetId([
                'id_pais' => $request->input('id_pais'),
                'id_provincia' => $request->input('id_provincia'),
                'id_canton' => $request->input('id_canton'),
                'id_distrito' => $request->input('id_distrito'),
                'id_barrio' => $request->input('id_barrio'),
                'detalle_especifico' => $request->input('detalle_especifico')
            ]);

            // Paso 4: Insertar teléfono
            DB::table('telefono')->insert([
                'numero' => $request->input('telefono'),
                'id_tipo' => $request->input('id_tipo_telefono')
            ]);

            // Paso 5: Insertar correo electrónico
            DB::table('correo_electronico')->insert([
                'nombre' => $request->input('correo'),
                'id_tipo' => $request->input('id_tipo_correo')
            ]);

            DB::commit();

            return response()->json([
                'mensaje' => 'Usuario registrado correctamente',
                'usuario_id' => $usuarioId,
                'direccion_id' => $direccionId
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'error' => 'Ocurrió un error durante el registro',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Método para registrar solo el usuario
     */
    public function registrarUsuario(Request $request)
    {
        try {
            $clave = $request->input('clave');
            $tipo_usuario = $this->determinarTipoUsuario($clave);

            $usuarioId = DB::table('usuario')->insertGetId([
                'nombre' => $request->input('nombre'),
                'clave' => $clave,
                'id_tipo' => $tipo_usuario
            ]);

            return response()->json([
                'mensaje' => 'Usuario registrado',
                'usuario_id' => $usuarioId
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al registrar usuario',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Método para registrar solo la dirección
     */
    public function registrarDireccion(Request $request)
    {
        try {
            $direccionId = DB::table('direccion')->insertGetId([
                'id_pais' => $request->input('id_pais'),
                'id_provincia' => $request->input('id_provincia'),
                'id_canton' => $request->input('id_canton'),
                'id_distrito' => $request->input('id_distrito'),
                'id_barrio' => $request->input('id_barrio'),
                'detalle_especifico' => $request->input('detalle_especifico')
            ]);

            return response()->json([
                'mensaje' => 'Dirección registrada',
                'direccion_id' => $direccionId
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al registrar dirección',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Método para registrar solo el teléfono
     */
    public function registrarTelefono(Request $request)
    {
        try {
            DB::table('telefono')->insert([
                'numero' => $request->input('numero'),
                'id_tipo' => $request->input('id_tipo')
            ]);

            return response()->json([
                'mensaje' => 'Teléfono registrado'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al registrar teléfono',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Método para registrar solo el correo electrónico
     */
    public function registrarCorreo(Request $request)
    {
        try {
            DB::table('correo_electronico')->insert([
                'nombre' => $request->input('nombre'),
                'id_tipo' => $request->input('id_tipo')
            ]);

            return response()->json([
                'mensaje' => 'Correo registrado'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al registrar correo',
                'detalle' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Método privado reutilizable para determinar el tipo de usuario por la clave
     */
    private function determinarTipoUsuario($clave)
    {
        if (DB::table('usuario')->where('clave', $clave)->where('id_tipo', 1)->exists()) {
            return 1;
        }

        if (DB::table('usuario')->where('clave', $clave)->where('id_tipo', 2)->exists()) {
            return 2;
        }

        return 3; // Valor por defecto
    }
}
