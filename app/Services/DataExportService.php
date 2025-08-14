<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Response;
use Carbon\Carbon;
use PDF;

class DataExportService
{
    /**
     * Export data to various formats
     */
    public function export($data, $format, $filename = null, $headers = [])
    {
        $filename = $filename ?: 'hospital_data_' . date('Y-m-d_H-i-s');
        
        switch (strtolower($format)) {
            case 'pdf':
                return $this->exportToPdf($data, $filename, $headers);
            case 'excel':
                return $this->exportToExcel($data, $filename, $headers);
            case 'csv':
                return $this->exportToCsv($data, $filename, $headers);
            default:
                throw new \InvalidArgumentException("Unsupported export format: {$format}");
        }
    }

    /**
     * Export data to PDF
     */
    public function exportToPdf($data, $filename, $headers = [])
    {
        // Convert collection to array if needed
        if ($data instanceof Collection) {
            $data = $data->toArray();
        }

        // Generate headers from first data row if not provided
        if (empty($headers) && !empty($data)) {
            $headers = array_keys($data[0]);
        }

        $html = $this->generatePdfHtml($data, $headers, $filename);
        
        $pdf = PDF::loadHTML($html);
        $pdf->setPaper('A4', 'landscape');
        
        return $pdf->download($filename . '.pdf');
    }

    /**
     * Export data to Excel
     */
    public function exportToExcel($data, $filename, $headers = [])
    {
        // Convert collection to array if needed
        if ($data instanceof Collection) {
            $data = $data->toArray();
        }

        // Generate headers from first data row if not provided
        if (empty($headers) && !empty($data)) {
            $headers = array_keys($data[0]);
        }

        $csvContent = $this->generateCsvContent($data, $headers);
        
        return Response::make($csvContent, 200, [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'attachment; filename="' . $filename . '.xlsx"',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Export data to CSV
     */
    public function exportToCsv($data, $filename, $headers = [])
    {
        // Convert collection to array if needed
        if ($data instanceof Collection) {
            $data = $data->toArray();
        }

        // Generate headers from first data row if not provided
        if (empty($headers) && !empty($data)) {
            $headers = array_keys($data[0]);
        }

        $csvContent = $this->generateCsvContent($data, $headers);
        
        return Response::make($csvContent, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '.csv"',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Generate CSV content
     */
    private function generateCsvContent($data, $headers)
    {
        $output = fopen('php://temp', 'r+');
        
        // Add UTF-8 BOM for proper Excel encoding
        fputs($output, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));
        
        // Add headers
        fputcsv($output, $headers);
        
        // Add data rows
        foreach ($data as $row) {
            $csvRow = [];
            foreach ($headers as $header) {
                $value = isset($row[$header]) ? $row[$header] : '';
                
                // Handle nested arrays or objects
                if (is_array($value) || is_object($value)) {
                    $value = json_encode($value);
                }
                
                // Clean up the value
                $csvRow[] = $this->cleanCsvValue($value);
            }
            fputcsv($output, $csvRow);
        }
        
        rewind($output);
        $csvContent = stream_get_contents($output);
        fclose($output);
        
        return $csvContent;
    }

    /**
     * Generate HTML for PDF
     */
    private function generatePdfHtml($data, $headers, $title)
    {
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>' . htmlspecialchars($title) . '</title>
            <style>
                body { font-family: Arial, sans-serif; font-size: 12px; }
                .header { text-align: center; margin-bottom: 20px; }
                .header h1 { color: #0066cc; margin: 0; }
                .header p { margin: 5px 0; color: #666; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f8f9fa; font-weight: bold; color: #333; }
                tr:nth-child(even) { background-color: #f9f9f9; }
                .footer { margin-top: 20px; text-align: center; font-size: 10px; color: #666; }
                .no-data { text-align: center; padding: 20px; color: #999; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>' . env('APP_NAME', 'Hospital Management System') . '</h1>
                <h2>' . htmlspecialchars($title) . '</h2>
                <p>Generated on: ' . Carbon::now()->format('F j, Y g:i A') . '</p>
                <p>Total Records: ' . count($data) . '</p>
            </div>';

        if (empty($data)) {
            $html .= '<div class="no-data">No data available to display.</div>';
        } else {
            $html .= '<table>';
            
            // Headers
            $html .= '<thead><tr>';
            foreach ($headers as $header) {
                $html .= '<th>' . htmlspecialchars(ucwords(str_replace('_', ' ', $header))) . '</th>';
            }
            $html .= '</tr></thead>';
            
            // Data rows
            $html .= '<tbody>';
            foreach ($data as $row) {
                $html .= '<tr>';
                foreach ($headers as $header) {
                    $value = isset($row[$header]) ? $row[$header] : '';
                    
                    // Handle nested arrays or objects
                    if (is_array($value) || is_object($value)) {
                        $value = json_encode($value);
                    }
                    
                    $html .= '<td>' . htmlspecialchars($this->cleanCsvValue($value)) . '</td>';
                }
                $html .= '</tr>';
            }
            $html .= '</tbody></table>';
        }

        $html .= '
            <div class="footer">
                <p>This report was automatically generated by ' . env('APP_NAME', 'Hospital Management System') . '</p>
            </div>
        </body>
        </html>';

        return $html;
    }

    /**
     * Clean CSV value
     */
    private function cleanCsvValue($value)
    {
        // Convert to string
        $value = (string) $value;
        
        // Remove HTML tags
        $value = strip_tags($value);
        
        // Replace multiple spaces with single space
        $value = preg_replace('/\s+/', ' ', $value);
        
        // Trim whitespace
        $value = trim($value);
        
        return $value;
    }

    /**
     * Get available export formats
     */
    public function getAvailableFormats()
    {
        return [
            'pdf' => 'PDF Document',
            'excel' => 'Excel Spreadsheet',
            'csv' => 'CSV File'
        ];
    }

    /**
     * Validate export format
     */
    public function isValidFormat($format)
    {
        return in_array(strtolower($format), ['pdf', 'excel', 'csv']);
    }
}
