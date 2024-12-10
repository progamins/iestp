<?php

namespace App\Controllers;

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../public/fpdf/fpdf.php';
require_once __DIR__ . '/../config/db_connect.php';

use FPDF;
use PDO;
use Exception;

class ReporteController extends FPDF
{
    private $conn;
    private $institucion = 'IESTP SULLANA';
    private $titulo = 'INSTITUTO DE EDUCACION SUPERIOR TECNOLOGICO PUBLICO SULLANA';
    private const MARGIN_LEFT = 10;
    private const MARGIN_RIGHT = 10;
    private const MARGIN_TOP = 10;
    private const BLUE_COLOR = [30, 98, 147];
    
    public function __construct()
    {
        try {
            parent::__construct('L', 'mm', 'A3');
            $this->SetMargins(self::MARGIN_LEFT, self::MARGIN_TOP, self::MARGIN_RIGHT);
            $this->SetAutoPageBreak(true, 20);
            
            global $conn;
            if (!$conn) {
                throw new Exception("No hay conexión a la base de datos");
            }
            $this->conn = $conn;
        } catch (Exception $e) {
            die("Error en el constructor: " . $e->getMessage());
        }
    }

    private function cleanText($text)
    {
        // Si el texto no está en UTF-8, intentar convertirlo usando mb_convert_encoding
        if (!mb_detect_encoding($text, 'UTF-8', true)) {
            $text = mb_convert_encoding($text, 'UTF-8', mb_detect_encoding($text, 'ISO-8859-1, CP1252, ASCII'));
        }
        
        // Mapa de caracteres para normalización
        $chars = [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
            'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U',
            'ñ' => 'n', 'Ñ' => 'N', 'ü' => 'u', 'Ü' => 'U'
        ];
        
        // Normalizar el texto
        $normalized = strtr($text, $chars);
        
        // Asegurar que cualquier carácter no ASCII se maneje correctamente
        return iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $normalized);
    }

    private function drawInstitutionalHeader()
    {
        $pageWidth = $this->GetPageWidth() - self::MARGIN_LEFT - self::MARGIN_RIGHT;
        
        // Logo
        $logoPath = __DIR__ . '/../../public/images/logo.png';
        if (file_exists($logoPath)) {
            $this->Image($logoPath, self::MARGIN_LEFT, self::MARGIN_TOP, 25);
        }

        // Títulos centrados
        $this->SetY(self::MARGIN_TOP);
        $this->SetFont('Arial', 'B', 16);
        $this->Cell($pageWidth, 8, $this->institucion, 0, 1, 'C');
        
        $this->SetFont('Arial', 'B', 12);
        $this->Cell($pageWidth, 6, $this->titulo, 0, 1, 'C');

        // Línea azul horizontal
        $this->SetDrawColor(self::BLUE_COLOR[0], self::BLUE_COLOR[1], self::BLUE_COLOR[2]);
        $this->SetLineWidth(0.5);
        $this->Line(self::MARGIN_LEFT, $this->GetY() + 2, $pageWidth + self::MARGIN_LEFT, $this->GetY() + 2);
        $this->Ln(6);

        // Título del reporte
        $this->SetFont('Arial', 'B', 14);
        $this->Cell($pageWidth, 8, 'REPORTE GENERAL DE ESTUDIANTES', 0, 1, 'C');
        
        // Fecha de generación
        $this->SetFont('Arial', '', 10);
        $this->Cell($pageWidth, 6, 'Generado el: ' . date('d/m/Y H:i:s'), 0, 1, 'R');
        
        $this->Ln(2);
    }


    public function Header()
    {
        $this->drawInstitutionalHeader();
        
        $pageWidth = $this->GetPageWidth() - self::MARGIN_LEFT - self::MARGIN_RIGHT;
        
        // Encabezados de tabla
        $this->SetFillColor(self::BLUE_COLOR[0], self::BLUE_COLOR[1], self::BLUE_COLOR[2]);
        $this->SetTextColor(255, 255, 255);
        $this->SetFont('Arial', 'B', 11);

        // Fixed header array with correct text
        $header = [
            'ID' => $pageWidth * 0.05,
            'DNI' => $pageWidth * 0.09,
            'Nombre del Estudiante' => $pageWidth * 0.18,
            'Institucion de Procedencia' => $pageWidth * 0.20,
            'Programa de Estudio' => $pageWidth * 0.20,
            'Año de Ingreso' => $pageWidth * 0.08,  // Corrected text
            'Celular' => $pageWidth * 0.08,
            'Codigo QR' => $pageWidth * 0.12
        ];

        // Special handling for the "Año de Ingreso" text
        foreach ($header as $col => $width) {
            if ($col === 'Año de Ingreso') {
                // Force the correct encoding for "Año"
                $text = chr(65) . chr(241) . chr(111) . " de Ingreso"; // "Año de Ingreso" with correct encoding
            } else {
                $text = $col;
            }
            $this->Cell($width, 10, $text, 1, 0, 'C', true);
        }
        $this->Ln();
        
        $this->SetFillColor(255, 255, 255);
        $this->SetTextColor(0, 0, 0);
    }
    public function generarReporte()
    {
        try {
            if (!$this->conn) {
                throw new Exception("No hay conexion a la base de datos");
            }

            $this->AliasNbPages();
            $this->AddPage();

            $sql = "SELECT 
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
            ORDER BY e.programa, e.anio_ingreso, e.nombre";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $estudiantes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($estudiantes)) {
                $currentProgram = '';
                
                foreach ($estudiantes as $row) {
                    if ($currentProgram != $row['programa']) {
                        $currentProgram = $row['programa'];
                        // Estilo del encabezado de programa
                        $this->SetFont('Arial', 'B', 10);
                        $this->SetFillColor(240, 240, 245);
                        $this->Cell(0, 8, "Programa: {$this->cleanText($currentProgram)}", 1, 1, 'L', true);
                        $this->SetFillColor(245, 245, 250);
                    }

                    $pageWidth = $this->GetPageWidth() - self::MARGIN_LEFT - self::MARGIN_RIGHT;
                    $this->SetFont('Arial', '', 9);
                    
                    // Filas de datos con fondo alternado
                    $this->Cell($pageWidth * 0.05, 12, $row['id'], 1, 0, 'C', true);
                    $this->Cell($pageWidth * 0.09, 12, $row['dni'], 1, 0, 'C', true);
                    $this->Cell($pageWidth * 0.18, 12, $this->cleanText($row['nombre']), 1, 0, 'L', true);
                    $this->Cell($pageWidth * 0.20, 12, $this->cleanText($row['ie_procedencia']), 1, 0, 'L', true);
                    $this->Cell($pageWidth * 0.20, 12, $this->cleanText($row['programa']), 1, 0, 'L', true);
                    $this->Cell($pageWidth * 0.08, 12, $row['anio_ingreso'], 1, 0, 'C', true);
                    $this->Cell($pageWidth * 0.08, 12, $row['celular'], 1, 0, 'C', true);

                    // Celda para QR
                    $x = $this->GetX();
                    $y = $this->GetY();
                    $this->Cell($pageWidth * 0.12, 12, '', 1, 0, 'C', true);
                    
                    if (!empty($row['qr_code_path'])) {
                        $qr_path = __DIR__ . '/../../public/' . $row['qr_code_path'];
                        if (file_exists($qr_path)) {
                            $this->Image($qr_path, $x + ($pageWidth * 0.02), $y + 1, 10, 10);
                        }
                    }
                    
                    $this->Ln();
                }
            } else {
                $this->Cell(0, 10, 'No se encontraron registros.', 1, 1, 'C');
            }

            ob_end_clean();
            $this->Output('I', 'reporte_estudiantes_iestp_sullana.pdf');
            
        } catch (Exception $e) {
            die("Error al generar el reporte: " . $e->getMessage());
        }
    }
}