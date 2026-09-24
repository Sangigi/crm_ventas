<?php
// disk_info.php - ELIMINAR después de usar
echo "<h1>Información de Disco</h1>";

// Espacio en el directorio actual
$current_dir = __DIR__;
$free_current = disk_free_space($current_dir);
$total_current = disk_total_space($current_dir);

echo "<h2>Directorio actual: $current_dir</h2>";
echo "<p>Espacio libre: " . round($free_current / 1073741824, 2) . " GB</p>";
echo "<p>Espacio total: " . round($total_current / 1073741824, 2) . " GB</p>";
echo "<p>Uso: " . round((1 - $free_current/$total_current) * 100, 2) . "%</p>";

// Directorio de sesiones original
$session_dir = '/var/cpanel/php/sessions/ea-php83';
if (is_dir($session_dir)) {
    echo "<h2>Directorio de sesiones: $session_dir</h2>";
    $free_session = disk_free_space($session_dir);
    echo "<p>Espacio libre: " . round($free_session / 1048576, 2) . " MB</p>";
    
    // Contar archivos de sesión
    $files = glob($session_dir . '/sess_*');
    echo "<p>Archivos de sesión: " . count($files) . "</p>";
} else {
    echo "<p>No se puede acceder al directorio de sesiones</p>";
}
?>