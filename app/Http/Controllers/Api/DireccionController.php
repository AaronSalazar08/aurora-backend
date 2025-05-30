<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // Asegúrate de importar el Facade DB

class DireccionController extends Controller
{
    
    public function getPaises()
    {
        try {
            
            $paises = DB::select('SELECT * from mostrar_pais()');
            return response()->json($paises);
        } catch (\Exception $e) {
           
            return response()->json(['error' => 'Error al obtener los países: ' . $e->getMessage()], 500);
        }
    }

    
    
    public function getProvincias($id_pais)
    {
        try {
            
            if (!is_numeric($id_pais) || $id_pais <= 0) {
                return response()->json(['error' => 'El ID del país debe ser un entero positivo.'], 400);
            }

            $provincias = DB::select('SELECT * FROM mostrar_provincia(?)', [$id_pais]);
            return response()->json($provincias);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener las provincias: ' . $e->getMessage()], 500);
        }
    }

   
    public function getCantones($id_provincia)
    {
        try {
            if (!is_numeric($id_provincia) || $id_provincia <= 0) {
                return response()->json(['error' => 'El ID de la provincia debe ser un entero positivo.'], 400);
            }

            $cantones = DB::select('SELECT * FROM mostrar_canton(?)', [$id_provincia]);
            return response()->json($cantones);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener los cantones: ' . $e->getMessage()], 500);
        }
    }   

  
    public function getDistritos($id_canton)
    {
        try {
            if (!is_numeric($id_canton) || $id_canton <= 0) {
                return response()->json(['error' => 'El ID del cantón debe ser un entero positivo.'], 400);
            }

            $distritos = DB::select('SELECT * FROM mostrar_distrito(?)', [$id_canton]);
            return response()->json($distritos);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener los distritos: ' . $e->getMessage()], 500);
        }
    }

   
    public function getBarrios($id_distrito)
    {
        try {
            if (!is_numeric($id_distrito) || $id_distrito <= 0) {
                return response()->json(['error' => 'El ID del distrito debe ser un entero positivo.'], 400);
            }

            $barrios = DB::select('SELECT * FROM mostrar_barrio(?)', [$id_distrito]);
            return response()->json($barrios);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener los barrios: ' . $e->getMessage()], 500);
        }
    }
}
