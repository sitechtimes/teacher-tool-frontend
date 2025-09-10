<?php
// Include PhpSpreadsheet
require __DIR__ . '/vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

// basic upload error handling like W3Schools
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_FILES['sheet']) && $_FILES['sheet']['error'] == UPLOAD_ERR_OK) {

        // you can move it somewhere permanent if you want:
        // $target_file = "uploads/" . basename($_FILES["sheet"]["name"]);
        // move_uploaded_file($_FILES["sheet"]["tmp_name"], $target_file);

        // but for PhpSpreadsheet you can read directly from tmp_name
        $tmpName = $_FILES['sheet']['tmp_name'];

        try {
            $spreadsheet = IOFactory::load($tmpName);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            // show like W3Schools “open and read file”
            echo "<table border='1'>";
            foreach ($rows as $row) {
                echo "<tr>";
                foreach ($row as $cell) {
                    echo "<td>" . htmlspecialchars($cell) . "</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
        } catch (Exception $e) {
            echo "Error reading file: " . $e->getMessage();
        }
    } else {
        echo "No file selected or upload error.";
    }
}
?>
