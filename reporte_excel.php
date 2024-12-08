<?php
require 'vendor/autoload.php'; // Asegúrate de que la ruta es correcta
include 'db_connect.php'; // Asegúrate de que este archivo está configurado para SQL Server

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Crear nueva hoja de cálculo
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Reporte de Estudiantes');

// Encabezados de la tabla
$sheet->setCellValue('A1', 'ID')
      ->setCellValue('B1', 'Número')
      ->setCellValue('C1', 'DNI')
      ->setCellValue('D1', 'Nombre')
      ->setCellValue('E1', 'Año de Ingreso')
      ->setCellValue('F1', 'Celular')
      ->setCellValue('G1', 'Institución de Procedencia')
      ->setCellValue('H1', 'Programa de Estudio');

// Consulta SQL para obtener los datos de los estudiantes
$sql = "SELECT id, numero, dni, nombre, anio_ingreso, celular, ie_procedencia, programa 
        FROM estudiantes";
$stmt = $conn->prepare($sql); // Preparar la consulta para SQL Server
$stmt->execute(); // Ejecutar la consulta
$estudiantes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Verifica si hay datos
if (!empty($estudiantes)) {
    $rowNum = 2; // Inicia en la fila 2, ya que la 1 es para los encabezados
    foreach ($estudiantes as $row) {
        $sheet->setCellValue('A' . $rowNum, $row['id'])
              ->setCellValue('B' . $rowNum, $row['numero'])
              ->setCellValue('C' . $rowNum, $row['dni'])
              ->setCellValue('D' . $rowNum, $row['nombre'])
              ->setCellValue('E' . $rowNum, $row['anio_ingreso'])
              ->setCellValue('F' . $rowNum, $row['celular'])
              ->setCellValue('G' . $rowNum, $row['ie_procedencia'])
              ->setCellValue('H' . $rowNum, $row['programa']);
        $rowNum++;
    }
} else {
    $sheet->setCellValue('A2', 'No se encontraron registros.');
}

// Limpia cualquier salida previa
ob_end_clean();

// Descargar el archivo
$filename = 'reporte_estudiantes.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment; filename=\"$filename\"");
header('Cache-Control: max-age=0');

// Escribir el archivo
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>
