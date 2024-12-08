<?php
require('fpdf/fpdf.php');
include 'db_connect.php'; // Asegúrate de que este archivo está configurado para SQL Server

class PDF extends FPDF
{
    // Cabecera de página
    function Header()
    {
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(0, 10, utf8_decode('Reporte General de Estudiantes'), 0, 1, 'C');
        $this->Ln(10);
        $this->SetFont('Arial', 'B', 12);

        $header = [
            'ID' => 15,
            'DNI' => 30,
            'Nombre' => 50,
            'Institución de Procedencia' => 60,
            'Programa de Estudio' => 60,
            'Año de Ingreso' => 30,
            'Celular' => 30,
            'Código QR' => 40
        ];

        foreach ($header as $col => $width) {
            $this->Cell($width, 10, utf8_decode($col), 1, 0, 'C');
        }
        $this->Ln();
    }

    // Pie de página
    function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, utf8_decode('Página ') . $this->PageNo(), 0, 0, 'C');
    }
}

// Crear nuevo PDF
$pdf = new PDF('L', 'mm', 'A3');
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial', '', 10);

// Consulta SQL para obtener los datos de los estudiantes
$sql = "
    SELECT 
        e.id, 
        e.dni, 
        e.nombre, 
        e.ie_procedencia, 
        e.programa, 
        e.anio_ingreso, 
        e.celular, 
        q.qr_code_path 
    FROM estudiantes e
    LEFT JOIN qr_codes q ON e.dni = q.dni_estudiante
";
$stmt = $conn->prepare($sql); // Preparar la consulta para SQL Server
$stmt->execute(); // Ejecutar la consulta

// Obtener los resultados
$estudiantes = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!empty($estudiantes)) {
    foreach ($estudiantes as $row) {
        $pdf->Cell(15, 30, utf8_decode($row['id']), 1, 0, 'C');
        $pdf->Cell(30, 30, utf8_decode($row['dni']), 1, 0, 'C');
        $pdf->Cell(50, 30, utf8_decode($row['nombre']), 1, 0, 'C');
        $pdf->Cell(60, 30, utf8_decode($row['ie_procedencia']), 1, 0, 'C');
        $pdf->Cell(60, 30, utf8_decode($row['programa']), 1, 0, 'C');
        $pdf->Cell(30, 30, utf8_decode($row['anio_ingreso']), 1, 0, 'C');
        $pdf->Cell(30, 30, utf8_decode($row['celular']), 1, 0, 'C');

        // Configuración para el código QR
        $x = $pdf->GetX();
        $y = $pdf->GetY();
        $pdf->Cell(40, 30, '', 1); // Crear celda para el código QR
        if (!empty($row['qr_code_path'])) {
            $pdf->Image($row['qr_code_path'], $x + 10, $y + 5, 20, 20); // Ajustar tamaño y posición del QR
        }

        // Salto de línea para la siguiente fila
        $pdf->Ln();
    }
} else {
    $pdf->Cell(0, 10, utf8_decode('No se encontraron registros.'), 1, 1, 'C');
}

// Limpia cualquier salida previa
ob_end_clean();

// Genera el PDF
$pdf->Output('I', 'reporte_general_estudiantes.pdf');
?>
