<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()
    {
        // Muestra la vista con el botón “Actualizar Alumnos”
        return view('home');
    }

    public function syncAlumno(Request $request)
    {
        // Preparamos un bloque anónimo PL/SQL para invocar sp_sync_alumno
        $rowsMerged = 0;
    $timeMs     = 0;

    $pdo = DB::connection('oracle')->getPdo();
    $stmt = $pdo->prepare("
        DECLARE
            v_rows  NUMBER;
            v_time  NUMBER;
        BEGIN
            sp_sync_alumno_tiempo(v_rows, v_time);
            :out_rows := v_rows;
            :out_time := v_time;
        END;
    ");
    $stmt->bindParam(':out_rows', $rowsMerged, \PDO::PARAM_INT | \PDO::PARAM_INPUT_OUTPUT, 32);
    $stmt->bindParam(':out_time', $timeMs,     \PDO::PARAM_INT | \PDO::PARAM_INPUT_OUTPUT, 32);
    $stmt->execute();

    return response()->json([
        'message'      => "Se procesaron {$rowsMerged} filas en {$timeMs} ms.",
        'rows_merged'  => $rowsMerged,
        'time_elapsed' => $timeMs,
    ]);
    }
}
