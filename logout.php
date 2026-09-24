<?php
// logout.php?modulo=agencia|ventas - Cierra por completo la sesión de ese módulo.
require_once __DIR__ . '/auth.php';

$modulo = $_GET['modulo'] ?? 'agencia';
if (!in_array($modulo, AUTH_MODULOS, true)) {
    $modulo = 'agencia';
}
iniciarSesionModulo($modulo);

$_SESSION = [];
$p = session_get_cookie_params();
setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
session_destroy();

header('Cache-Control: no-store');
header('Location: index.html');
exit;