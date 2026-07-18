<?php
// test_path.php
echo "Ruta actual: " . __DIR__ . "\n";
echo "Ruta de Conexion: " . __DIR__ . "/Conect/Conexion.php";
echo "¿Existe? " . (file_exists(__DIR__ . "/Conect/Conexion.php") ? 'SÍ' : 'NO') . "\n";

// Intentar cargar el archivo
if (file_exists(__DIR__ . "/Conect/Conexion.php")) {
    require_once __DIR__ . "/Conect/Conexion.php";
    if (class_exists('Conect\Conexion')) {
        echo "✅ Clase Conexion cargada correctamente\n";
    } else {
        echo "❌ Clase Conexion NO encontrada después de incluir el archivo\n";
    }
} else {
    echo "❌ El archivo Conexion.php no existe en esa ruta\n";
}