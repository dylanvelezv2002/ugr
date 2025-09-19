<?php
include('../includes/db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file']['tmp_name'];
    $success = true;

    if (($handle = fopen($file, 'r')) !== false) {
        $headers = fgetcsv($handle, 1000, ',');
        $required = ['nombre_material', 'codigo_onu', 'guia_emergencia', 'aloha_name', 'etiqueta_dot'];
        $indexes = [];

        foreach ($required as $campo) {
            $index = array_search($campo, $headers);
            if ($index === false) {
                $success = false;
                break;
            }
            $indexes[$campo] = $index;
        }

        if ($success) {
            while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                $nombre_material = trim($row[$indexes['nombre_material']]);
                $codigo_onu = trim($row[$indexes['codigo_onu']]);
                $guia_emergencia = trim($row[$indexes['guia_emergencia']]);
                $aloha_name = trim($row[$indexes['aloha_name']]);
                $etiqueta_dot = trim($row[$indexes['etiqueta_dot']]);

                $stmt = $conn->prepare("INSERT INTO guias (nombre_material, codigo_onu, guia_emergencia, aloha_name, etiqueta_dot) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sssss", $nombre_material, $codigo_onu, $guia_emergencia, $aloha_name, $etiqueta_dot);
                $stmt->execute();
            }
        }

        fclose($handle);
    } else {
        $success = false;
    }

    if ($success) {
        header("Location: ../guides.php?import=success");
    } else {
        header("Location: ../guides.php?import=error");
    }
    exit;
} else {
    header("Location: ../guides.php?import=error");
    exit;
}
