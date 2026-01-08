<?php
class CSVHandler
{
    private $filename;
    private $headers = ['drink_date', 'brand', 'tea_name', 'flavor'];
    public function __construct($filename = '')
    {
        $this->filename = $filename;
    }
    public function setFilename($filename)
    {
        $this->filename = $filename;
    }
    public function validateCSV($filepath)
    {
        $errors = [];
        if (!file_exists($filepath)) {
            return ["File not found: $filepath"];
        }
        if (filesize($filepath) > 5 * 1024 * 1024) {
            $errors[] = "File too large (max 5MB)";
        }
        $file = fopen($filepath, 'r');
        if (!$file) {
            return ["Cannot open file"];
        }
        $first_line = fgetcsv($file);
        if (!$first_line) {
            fclose($file);
            return ["Empty CSV file"];
        }
        $is_header_row = false;
        if (count($first_line) >= 4) {
            $first_cell = strtolower(trim($first_line[0]));
            if ($first_cell === 'date' || $first_cell === 'drink_date') {
                $is_header_row = true;
            }
        }
        if (!$is_header_row) {
            $validation = $this->validateRow($first_line);
            if ($validation !== true) {
                $errors[] = "Row 1: $validation";
            }
        }
        $row_num = $is_header_row ? 1 : 2;
        while (($row = fgetcsv($file)) !== FALSE) {
            $validation = $this->validateRow($row);
            if ($validation !== true) {
                $errors[] = "Row $row_num: $validation";
            }
            $row_num++;
        }
        fclose($file);
        return empty($errors) ? true : $errors;
    }
    private function validateRow($row)
    {
        if (count($row) < 3) {
            return "Row must have at least 3 columns (date, brand, name)";
        }
        $date = trim($row[0]);
        if (
            !filter_var($date, FILTER_VALIDATE_REGEXP, [
                'options' => ['regexp' => '/^\d{4}-\d{2}-\d{2}$/']
            ])
        ) {
            return "Invalid date format: '$date'. Use YYYY-MM-DD";
        }
        $date_parts = explode('-', $date);
        if (!checkdate($date_parts[1], $date_parts[2], $date_parts[0])) {
            return "Invalid date: $date";
        }
        $brand = trim($row[1]);
        if (empty($brand)) {
            return "Brand cannot be empty";
        }
        $brand_clean = str_replace(' ', '', $brand);
        if (!ctype_alpha($brand_clean)) {
            return "Brand should contain only letters and spaces.";
        }
        $name = trim($row[2]);
        if (empty($name)) {
            return "Tea name cannot be empty";
        }

        return true;
    }
    public function importToDB($mysqli, $filepath)
    {
        $imported = 0;
        $errors = [];
        $mysqli->begin_transaction();

        try {
            $mysqli->query("DELETE FROM teas");
            $file = fopen($filepath, 'r');

            if (!$file) {
                throw new Exception("Cannot open file");
            }
            $first_row = fgetcsv($file);

            $has_header = (count($first_row) >= 4 &&
                (strtolower($first_row[0]) === 'date' ||
                    strtolower($first_row[0]) === 'drink_date'));

            if (!$has_header) {
                if ($this->validateRow($first_row) === true) {
                    $this->insertRow($mysqli, $first_row);
                    $imported++;
                }
            }

            while (($row = fgetcsv($file)) !== FALSE) {
                if ($this->validateRow($row) === true) {
                    $this->insertRow($mysqli, $row);
                    $imported++;
                }
            }
            fclose($file);

            $filename = basename($filepath);
            $stmt = $mysqli->prepare("INSERT INTO import_history (filename, rows_imported) VALUES (?, ?)");
            $stmt->bind_param("si", $filename, $imported);
            $stmt->execute();
            $mysqli->commit();

            return [
                'success' => true,
                'imported' => $imported,
                'message' => "Successfully imported $imported teas!"
            ];

        } catch (Exception $e) {
            $mysqli->rollback();
            return [
                'success' => false,
                'imported' => 0,
                'message' => "Import failed: " . $e->getMessage()
            ];
        }
    }
    private function insertRow($mysqli, $row)
    {
        $date = trim($row[0]);
        $brand = trim($row[1]);
        $name = trim($row[2]);
        $flavor = isset($row[3]) ? trim($row[3]) : '';

        $stmt = $mysqli->prepare("
            INSERT INTO teas (drink_date, brand, tea_name, flavor) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("ssss", $date, $brand, $name, $flavor);
        $stmt->execute();
    }
    public function exportFromDB($mysqli)
    {
        $result = $mysqli->query("
            SELECT drink_date, brand, tea_name, flavor 
            FROM teas 
            ORDER BY drink_date
        ");

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = [
                $row['drink_date'],
                $row['brand'],
                $row['tea_name'],
                $row['flavor']
            ];
        }

        return $data;
    }
    public function getTemplate()
    {
        return [
            $this->headers,
            ['2025-01-15', 'Fructus', 'Earl Grey', 'Bergamot'],
            ['2025-01-16', 'dmBio', 'Peppermint', 'Mint'],
            ['2025-01-17', 'Lord Nelson', 'Green Tea', 'Green Tea']
        ];
    }
}
?>