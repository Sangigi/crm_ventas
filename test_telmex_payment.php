<?php
// telmexPayment_curl.php - Versión con cURL directo

$config = [
    'version' => "01",
    'terminalId' => "4418653",
    'clerkId' => "e55it7",
    'productId' => "5121001",
    'accountId' => "",
    'amount' => "",
    'invoiceNo' => "00540",
    'languageOption' => "1"
];

// ========== FUNCIÓN PARA ENVIAR SOAP CON cURL ==========
function enviarSoapConCurl($params, $wsdlPath) {
    // Extraer la URL del servicio del WSDL
    $wsdlContent = file_get_contents($wsdlPath);
    $serviceURL = '';
    
    if (preg_match('/<soap:address location="([^"]+)"/', $wsdlContent, $matches)) {
        $serviceURL = $matches[1];
    } elseif (preg_match('/<wsdlsoap:address location="([^"]+)"/', $wsdlContent, $matches)) {
        $serviceURL = $matches[1];
    }
    
    if (empty($serviceURL)) {
        return ['error' => 'No se pudo extraer la URL del servicio del WSDL'];
    }
    
    // Construir el XML SOAP manualmente
    $soapXml = '<?xml version="1.0" encoding="UTF-8"?>';
    $soapXml .= '<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" ';
    $soapXml .= 'xmlns:ns1="urn:debisys-soap-services">';
    $soapXml .= '<soap:Body>';
    $soapXml .= '<ns1:BillPaymentUserFee>';
    $soapXml .= "<version>{$params[0]}</version>";
    $soapXml .= "<terminalId>{$params[1]}</terminalId>";
    $soapXml .= "<clerkId>{$params[2]}</clerkId>";
    $soapXml .= "<productId>{$params[3]}</productId>";
    $soapXml .= "<amount>{$params[4]}</amount>";
    $soapXml .= "<account>{$params[5]}</account>";
    $soapXml .= "<invoiceNo>{$params[6]}</invoiceNo>";
    $soapXml .= "<language>{$params[7]}</language>";
    $soapXml .= '</ns1:BillPaymentUserFee>';
    $soapXml .= '</soap:Body>';
    $soapXml .= '</soap:Envelope>';
    
    // Configurar cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $serviceURL);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $soapXml);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: text/xml; charset=utf-8',
        'SOAPAction: "urn:debisys-soap-services#BillPaymentUserFee"',
        'Content-Length: ' . strlen($soapXml)
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 300);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 300);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_VERBOSE, true);
    
    // Capturar verbose output
    $verbose = fopen('php://temp', 'w+');
    curl_setopt($ch, CURLOPT_STDERR, $verbose);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    
    rewind($verbose);
    $verboseLog = stream_get_contents($verbose);
    fclose($verbose);
    
    curl_close($ch);
    
    return [
        'response' => $response,
        'http_code' => $httpCode,
        'curl_error' => $curlError,
        'verbose_log' => $verboseLog,
        'sent_xml' => $soapXml
    ];
}

// Procesar formulario (similar a tu código original)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $telefono = $_POST['telefono'] ?? '';
    $monto = $_POST['monto'] ?? '';
    
    $wsdlPath = __DIR__ . "/webServices.wsdl";
    if (!file_exists($wsdlPath)) {
        die("WSDL no encontrado en: $wsdlPath");
    }
    
    $config['accountId'] = $telefono;
    $config['amount'] = number_format(floatval($monto), 2, '.', '');
    
    $params = [
        $config['version'], $config['terminalId'], $config['clerkId'],
        $config['productId'], $config['amount'], $config['accountId'],
        $config['invoiceNo'], $config['languageOption']
    ];
    
    $resultadoCurl = enviarSoapConCurl($params, $wsdlPath);
    
    // Intentar parsear la respuesta XML
    $respuestaParseada = ['success' => false, 'raw_response' => $resultadoCurl['response']];
    
    if ($resultadoCurl['http_code'] == 200 && !empty($resultadoCurl['response'])) {
        // Limpiar posibles errores de encoding en la respuesta
        $cleanResponse = preg_replace('/[^\x{0009}\x{000A}\x{000D}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]+/u', ' ', $resultadoCurl['response']);
        
        $xml = simplexml_load_string($cleanResponse);
        if ($xml !== false) {
            // Buscar en el namespace
            $namespaces = $xml->getNamespaces(true);
            $soapBody = $xml->children('http://schemas.xmlsoap.org/soap/envelope/')->Body;
            if ($soapBody) {
                $responseData = $soapBody->children('urn:debisys-soap-services')->BillPaymentUserFeeResponse;
                if ($responseData) {
                    $respuestaParseada = [
                        'success' => ((string)$responseData->ResponseCode === '00'),
                        'responseCode' => (string)$responseData->ResponseCode,
                        'responseMessage' => (string)$responseData->ResponseMessage,
                        'transactionId' => (string)$responseData->TransactionId,
                        'controlNo' => (string)$responseData->ControlNo
                    ];
                }
            }
        }
    }
    
    // Mostrar resultados
    echo "<!DOCTYPE html><html><head><title>Resultado Pago</title>";
    echo "<style>body{font-family:monospace;padding:20px;} pre{background:#f0f0f0;padding:10px;overflow-x:auto;}</style>";
    echo "</head><body>";
    echo "<h2>📊 Resultado de la transacción</h2>";
    
    echo "<h3>✅ Estado HTTP: {$resultadoCurl['http_code']}</h3>";
    
    if ($respuestaParseada['success']) {
        echo "<div style='background:#d4edda;padding:15px;border-radius:8px;'>";
        echo "<strong>✅ PAGO EXITOSO!</strong><br>";
        echo "Código: {$respuestaParseada['responseCode']}<br>";
        echo "Mensaje: {$respuestaParseada['responseMessage']}<br>";
        echo "Transacción: {$respuestaParseada['transactionId']}<br>";
        echo "</div>";
    } else {
        echo "<div style='background:#f8d7da;padding:15px;border-radius:8px;'>";
        echo "<strong>❌ Error en el pago</strong><br>";
        if (!empty($resultadoCurl['curl_error'])) {
            echo "Error cURL: {$resultadoCurl['curl_error']}<br>";
        }
        echo "Código HTTP: {$resultadoCurl['http_code']}<br>";
        echo "</div>";
    }
    
    // Mostrar detalles técnicos
    echo "<hr><h3>🔍 Diagnóstico completo</h3>";
    echo "<details><summary>📨 XML Enviado</summary><pre>" . htmlspecialchars($resultadoCurl['sent_xml']) . "</pre></details>";
    echo "<details><summary>📬 Respuesta del Servidor</summary><pre>" . htmlspecialchars($resultadoCurl['response']) . "</pre></details>";
    echo "<details><summary>🐛 Verbose cURL</summary><pre>" . htmlspecialchars($resultadoCurl['verbose_log']) . "</pre></details>";
    
    echo "<br><a href='?'>← Volver</a>";
    echo "</body></html>";
    exit;
}
?>

<!-- Formulario HTML (similar al tuyo) -->
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Pago TELMEX - cURL Directo</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; }
        input, button { width: 100%; padding: 10px; margin: 10px 0; }
        button { background: #4CAF50; color: white; border: none; cursor: pointer; }
        .info { background: #e3f2fd; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <h2>📞 Pago TELMEX - Diagnóstico</h2>
    
    <div class="info">
        <strong>Terminal:</strong> <?php echo $config['terminalId']; ?><br>
        <strong>Producto:</strong> <?php echo $config['productId']; ?>
    </div>
    
    <form method="POST">
        <label>📱 Teléfono (10 dígitos):</label>
        <input type="text" name="telefono" value="5512345678" pattern="\d{10}" required>
        
        <label>💰 Monto:</label>
        <input type="number" name="monto" value="50.00" step="0.01" required>
        
        <button type="submit">💳 Procesar Pago</button>
    </form>
    
    <p><small>Usando cURL directo - evita problemas de parseo SOAP</small></p>
</body>
</html>