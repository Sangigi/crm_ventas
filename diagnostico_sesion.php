<?php
// diagnostico_sesion.php
// NO incluir config_sesion.php aún - queremos ver el estado real

echo "<h1>🔍 DIAGNÓSTICO COMPLETO DE SESIÓN</h1>";

// 1. Mostrar configuración ANTES de iniciar sesión
echo "<h2>1. Configuración PHP (antes de session_start):</h2>";
echo "<ul>";
echo "<li>session.gc_maxlifetime: " . ini_get('session.gc_maxlifetime') . " segundos</li>";
echo "<li>session.cookie_lifetime: " . ini_get('session.cookie_lifetime') . " segundos</li>";
echo "<li>session.save_path: " . (session_save_path() ?: 'default') . "</li>";
echo "<li>session.cookie_secure: " . (ini_get('session.cookie_secure') ? 'On' : 'Off') . "</li>";
echo "<li>session.cookie_httponly: " . (ini_get('session.cookie_httponly') ? 'On' : 'Off') . "</li>";
echo "</ul>";

// 2. Verificar si hay una cookie de sesión existente
echo "<h2>2. Cookie de sesión:</h2>";
if (isset($_COOKIE['PHPSESSID'])) {
    echo "<p style='color: green;'>✅ Cookie PHPSESSID encontrada: " . $_COOKIE['PHPSESSID'] . "</p>";
} else {
    echo "<p style='color: orange;'>⚠️ No hay cookie PHPSESSID</p>";
}

// 3. Iniciar sesión con configuración forzada
$session_lifetime = 604800;
ini_set('session.gc_maxlifetime', $session_lifetime);
ini_set('session.cookie_lifetime', $session_lifetime);

session_set_cookie_params([
    'lifetime' => $session_lifetime,
    'path' => '/',
    'domain' => '',
    'secure' => false,
    'httponly' => true,
    'samesite' => 'Lax'
]);

session_start();

echo "<h2>3. Estado después de session_start():</h2>";
echo "<ul>";
echo "<li>Session ID: " . session_id() . "</li>";
echo "<li>Session status: " . session_status() . " (" . (session_status() == PHP_SESSION_ACTIVE ? "ACTIVA" : "INACTIVA") . ")</li>";
echo "</ul>";

// 4. Mostrar contenido de $_SESSION
echo "<h2>4. Contenido de \$_SESSION:</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

// 5. Verificar si hay variables específicas de login
echo "<h2>5. Verificación de login:</h2>";
if (isset($_SESSION['usuario_id'])) {
    echo "<p style='color: green;'>✅ usuario_id existe: " . $_SESSION['usuario_id'] . "</p>";
    echo "<p>Rol: " . ($_SESSION['rol'] ?? 'no definido') . "</p>";
    
    if (isset($_SESSION['ultimo_acceso'])) {
        $inactividad = time() - $_SESSION['ultimo_acceso'];
        echo "<p>Último acceso: " . date('Y-m-d H:i:s', $_SESSION['ultimo_acceso']) . "</p>";
        echo "<p>Inactividad: " . floor($inactividad/60) . " minutos, " . ($inactividad%60) . " segundos</p>";
    }
} else {
    echo "<p style='color: red;'>❌ NO hay usuario_id en la sesión</p>";
}

// 6. Verificar permisos de escritura en el directorio de sesiones
echo "<h2>6. Permisos del directorio de sesiones:</h2>";
$save_path = session_save_path();
if (empty($save_path)) {
    $save_path = sys_get_temp_dir();
}
echo "<p>Directorio: " . $save_path . "</p>";
echo "<p>¿Escribible? " . (is_writable($save_path) ? "✅ Sí" : "❌ No") . "</p>";

// 7. Crear una prueba de sesión persistente
if (!isset($_SESSION['test_time'])) {
    $_SESSION['test_time'] = time();
    $_SESSION['test_date'] = date('Y-m-d H:i:s');
    echo "<p style='color: blue;'>🆕 Sesión de prueba creada: " . $_SESSION['test_date'] . "</p>";
} else {
    $edad = time() - $_SESSION['test_time'];
    echo "<p style='color: green;'>✅ Sesión de prueba existe desde: " . $_SESSION['test_date'] . "</p>";
    echo "<p>Tiempo transcurrido: " . floor($edad/60) . " minutos, " . ($edad%60) . " segundos</p>";
}

// 8. Enlaces para pruebas
echo "<hr>";
echo "<a href='diagnostico_sesion.php'>🔄 Refrescar</a> | ";
echo "<a href='diagnostico_sesion.php?reset=1'>🗑️ Resetear prueba</a> | ";
echo "<a href='login.php'>🔐 Ir a login</a> | ";
echo "<a href='opcionesAdmin.php'>📊 Ir a Admin</a>";

if (isset($_GET['reset'])) {
    session_destroy();
    echo "<p style='color: orange;'>🔄 Sesión destruida. <a href='diagnostico_sesion.php'>Recargar</a></p>";
}
?>