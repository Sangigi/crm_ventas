<?php
// Copia este archivo a la raíz Y a facturacion/, ábrelo en cada una y BÓRRALO al terminar.
session_start();
header('Content-Type: text/plain; charset=utf-8');
echo "Carpeta: " . basename(__DIR__) . "\n";
echo "session_name(): " . session_name() . "\n";
echo "Cookies de sesión que manda el navegador: " . implode(', ', array_filter(array_keys($_COOKIE), fn($k) => $k === 'PHPSESSID' || strpos($k, 'CRM_') === 0)) . "\n";
echo "usuario_id: " . ($_SESSION['usuario_id'] ?? '(no hay)') . "\n";
echo "rol: " . ($_SESSION['rol'] ?? '(no hay)') . "\n";
