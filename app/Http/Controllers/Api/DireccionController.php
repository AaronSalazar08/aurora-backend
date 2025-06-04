<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DireccionController extends Controller
{
    public function ObtenerPais()
    {
        $paises = DB::select('SELECT * FROM mostrar_pais();');
        return response()->json($paises);
    }

    public function ObtenerProvincia(Request $request)
    {
        $validator = Validator::make($request->query(), [
            'pais_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $provincias = DB::select('SELECT * FROM mostrar_provincia(?)', [$request->query('pais_id')]);
        return response()->json($provincias);
    }

    public function ObtenerCanton(Request $request)
    {
        $validator = Validator::make($request->query(), [
            'provincia_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $cantones = DB::select('SELECT * FROM mostrar_canton(?)', [$request->query('provincia_id')]);
        return response()->json($cantones);
    }

    public function ObtenerDistrito(Request $request)
    {
        $validator = Validator::make($request->query(), [
            'canton_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $distritos = DB::select('SELECT * FROM mostrar_distrito(?)', [$request->query('canton_id')]);
        return response()->json($distritos);
    }

    public function ObtenerBarrio(Request $request)
    {
        $validator = Validator::make($request->query(), [
            'distrito_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $barrios = DB::select('SELECT * FROM mostrar_barrio(?)', [$request->query('distrito_id')]);
        return response()->json($barrios);
    }
}
