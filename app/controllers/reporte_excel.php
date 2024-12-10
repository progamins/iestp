<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../config/db_connect.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Crear nueva hoja de cálculo
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Reporte de Estudiantes');

// Encabezados de la tabla - Todos los campos disponibles
$sheet->setCellValue('A1', 'ID')
      ->setCellValue('B1', 'Número')
      ->setCellValue('C1', 'Nombre')
      ->setCellValue('D1', 'DNI')
      ->setCellValue('E1', 'Año de Ingreso')
      ->setCellValue('F1', 'Celular')
      ->setCellValue('G1', 'Email')
      ->setCellValue('H1', 'Email Corporativo')
      ->setCellValue('I1', 'Institución de Procedencia')
      ->setCellValue('J1', 'Dirección')
      ->setCellValue('K1', 'Distrito')
      ->setCellValue('L1', 'Trabaja')
      ->setCellValue('M1', 'Dependiente')
      ->setCellValue('N1', 'Apoderado')
      ->setCellValue('O1', 'Celular Apoderado')
      ->setCellValue('P1', 'Programa')
      ->setCellValue('Q1', 'Usuario')
      ->setCellValue('R1', 'Carnet ID')
      ->setCellValue('S1', 'Semestre Actual')
      ->setCellValue('T1', 'Última Actualización');

// Dar formato a los encabezados
$sheet->getStyle('A1:T1')->getFont()->setBold(true);

// Consulta SQL para obtener todos los datos de los estudiantes
$sql = "SELECT 
            id, 
            numero, 
            nombre, 
            dni, 
            anio_ingreso, 
            celular, 
            email, 
            email_corporativo, 
            ie_procedencia, 
            direccion, 
            distrito, 
            trabaja, 
            dependiente, 
            apoderado, 
            celular_apoderado, 
            programa, 
            usuario, 
            carnet_id, 
            semestre_actual, 
            ultima_actualizacion
        FROM estudiantes";

$stmt = $conn->prepare($sql);
$stmt->execute();
$estudiantes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Verifica si hay datos
if (!empty($estudiantes)) {
    $rowNum = 2; // Inicia en la fila 2, ya que la 1 es para los encabezados
    foreach ($estudiantes as $row) {
        $sheet->setCellValue('A' . $rowNum, $row['id'])
              ->setCellValue('B' . $rowNum, $row['numero'])
              ->setCellValue('C' . $rowNum, $row['nombre'])
              ->setCellValue('D' . $rowNum, $row['dni'])
              ->setCellValue('E' . $rowNum, $row['anio_ingreso'])
              ->setCellValue('F' . $rowNum, $row['celular'])
              ->setCellValue('G' . $rowNum, $row['email'])
              ->setCellValue('H' . $rowNum, $row['email_corporativo'])
              ->setCellValue('I' . $rowNum, $row['ie_procedencia'])
              ->setCellValue('J' . $rowNum, $row['direccion'])
              ->setCellValue('K' . $rowNum, $row['distrito'])
              ->setCellValue('L' . $rowNum, $row['trabaja'])
              ->setCellValue('M' . $rowNum, $row['dependiente'])
              ->setCellValue('N' . $rowNum, $row['apoderado'])
              ->setCellValue('O' . $rowNum, $row['celular_apoderado'])
              ->setCellValue('P' . $rowNum, $row['programa'])
              ->setCellValue('Q' . $rowNum, $row['usuario'])
              ->setCellValue('R' . $rowNum, $row['carnet_id'])
              ->setCellValue('S' . $rowNum, $row['semestre_actual'])
              ->setCellValue('T' . $rowNum, $row['ultima_actualizacion']);
        $rowNum++;
    }

    // Autoajustar el ancho de las columnas
    foreach (range('A', 'T') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }
} else {
    $sheet->setCellValue('A2', 'No se encontraron registros.');
}

// Limpia cualquier salida previa
ob_end_clean();

// Configurar las cabeceras para la descarga
$filename = 'reporte_estudiantes_completo.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');

// Escribir el archivo
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
