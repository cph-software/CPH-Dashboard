<?php

namespace App\Services;

use App\Exports\SimpleArrayExport;
use Maatwebsite\Excel\Facades\Excel;

class ExcelExportService
{
    /**
     * Check if Maatwebsite Excel is available
     */
    private static function hasMaatwebsiteExcel()
    {
        return class_exists('\Maatwebsite\Excel\Facades\Excel');
    }

    /**
     * Generate Excel file based on available libraries
     */
    public static function generateExcelFile($data, $headers, $filename)
    {
        if (self::hasMaatwebsiteExcel()) {
            return self::generateWithMaatwebsite($data, $headers, $filename);
        } else {
            return self::generateWithXML($data, $headers, $filename);
        }
    }

    /**
     * Generate using Maatwebsite Excel (preferred method)
     */
    private static function generateWithMaatwebsite($data, $headers, $filename)
    {
        // Laravel-Excel 3.x expects an Export object, not a closure-based sheet builder.
        // $data is exported as rows, $headers as the heading row.
        return Excel::download(new SimpleArrayExport($data, $headers), $filename);
    }

    /**
     * Generate using XML Spreadsheet (fallback method)
     */
    private static function generateWithXML($data, $headers, $filename)
    {
        ob_start();
        
        // XML Header
        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"' . "\n";
        echo ' xmlns:o="urn:schemas-microsoft-com:office:office"' . "\n";
        echo ' xmlns:x="urn:schemas-microsoft-com:office:excel"' . "\n";
        echo ' xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"' . "\n";
        echo ' xmlns:html="http://www.w3.org/TR/REC-html40">' . "\n";
        echo '<Worksheet ss:Name="Sheet1">' . "\n";
        echo '<Table>' . "\n";
        
        // Add header row
        echo '<Row>' . "\n";
        foreach ($headers as $header) {
            $headerContent = htmlspecialchars((string)$header, ENT_QUOTES, 'UTF-8');
            echo '<Cell><Data ss:Type="String">' . $headerContent . '</Data></Cell>' . "\n";
        }
        echo '</Row>' . "\n";
        
        // Add data rows
        foreach ($data as $row) {
            echo '<Row>' . "\n";
            foreach ($row as $cell) {
                $cellContent = htmlspecialchars((string)$cell, ENT_QUOTES, 'UTF-8');
                echo '<Cell><Data ss:Type="String">' . $cellContent . '</Data></Cell>' . "\n";
            }
            echo '</Row>' . "\n";
        }
        
        // Close XML tags
        echo '</Table>' . "\n";
        echo '</Worksheet>' . "\n";
        echo '</Workbook>' . "\n";
        
        $content = ob_get_clean();
        
        return response($content)
            ->header('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->header('Content-Disposition', 'attachment;filename="' . $filename . '"')
            ->header('Cache-Control', 'max-age=0')
            ->header('Expires', '0')
            ->header('Pragma', 'public')
            ->header('Content-Transfer-Encoding', 'binary')
            ->header('Content-Length', strlen($content));
    }
}
