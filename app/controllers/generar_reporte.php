<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../public/fpdf/fpdf.php';
require_once __DIR__ . '/../config/db_connect.php';

use App\Controllers\ReporteController;

try {
    $reporte = new ReporteController();
    $reporte->generarReporte();
} catch (Exception $e) {
    echo "Error al generar el reporte: " . $e->getMessage();
}