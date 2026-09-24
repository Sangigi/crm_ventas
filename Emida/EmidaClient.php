<?php

class EmidaClient {

    private $wsdl;
    private $terminalId;
    private $clerkId;
    private $merchantId;
    private $password;
    private $logFile = __DIR__ . "/emida_log.txt";

    public function __construct($config)
    {
        $this->wsdl       = $config["wsdl"];
        $this->terminalId = $config["terminalId"];
        $this->clerkId    = $config["clerkId"];
        $this->merchantId = $config["merchantId"];
        $this->password   = $config["password"];
    }

    /**
     * Método general para consumir cualquier servicio SOAP
     */
    private function call($method, $params)
    {
        try {
            $client = new SoapClient($this->wsdl, [
                'trace'      => true,
                'exceptions' => true,
                'cache_wsdl' => WSDL_CACHE_NONE,
                'encoding'   => 'UTF-8'
            ]);

            $response = $client->__soapCall($method, [$params]);

            $this->log("✔ MÉTODO: $method\nREQUEST:\n" . print_r($params, true) . 
                       "\nRESPONSE:\n" . print_r($response, true));

            return $response;

        } catch (SoapFault $e) {
            $this->log("❌ ERROR en $method : " . $e->getMessage());
            return [
                "error" => true,
                "message" => $e->getMessage()
            ];
        }
    }

    /**
     * LOG de actividad
     */
    private function log($text)
    {
        file_put_contents($this->logFile, 
            "==============================\n" .
            date("Y-m-d H:i:s") . "\n" .
            $text . "\n",
            FILE_APPEND
        );
    }

    /* ===========================
     *   MÉTODOS DE EMIDA
     * ========================== */

    /** Descargar catálogo de productos */
    public function productFlow()
    {
        return $this->call("ProductFlowInfoService", [
            "version"    => "01",
            "terminalId" => $this->terminalId,
            "invoiceNo"  => rand(1, 99999),
            "language"   => "1",
            "clerkId"    => $this->clerkId
        ]);
    }

    /** Recarga / Venta */
    public function pinDistSale($productId, $amount, $accountId)
    {
        return $this->call("PinDistSale", [
            "version"       => "01",
            "terminalId"    => $this->terminalId,
            "clerkId"       => $this->clerkId,
            "productId"     => $productId,
            "accountId"     => $accountId,
            "amount"        => $amount,
            "invoiceNo"     => rand(1,99999),
            "languageOption"=> "1"
        ]);
    }

    /** Pago de servicios */
    public function billPayment($productId, $amount, $accountId)
    {
        return $this->call("BillpaymentUserFee", [
            "version"       => "01",
            "terminalId"    => $this->terminalId,
            "clerkId"       => $this->clerkId,
            "productId"     => $productId,
            "amount"        => $amount,
            "accountId"     => $accountId,
            "invoiceNo"     => rand(1,99999),
            "languageOption"=> "1"
        ]);
    }

    /** Balance */
    public function getBalance()
    {
        return $this->call("GetAccountBalance", [
            "version"    => "01",
            "terminalId" => $this->terminalId,
            "merchantId" => $this->merchantId
        ]);
    }

    /** Consultar transacción */
    public function lookupInvoice($invoice)
    {
        return $this->call("LookupTransactionByInvoiceNo", [
            "version"    => "01",
            "terminalId" => $this->terminalId,
            "clerkId"    => $this->clerkId,
            "invoiceNo"  => $invoice
        ]);
    }

    /** Mensajes de Emida */
    public function getMessages()
    {
        return $this->call("GetActiveMessage", [
            "version"        => "01",
            "siteId"         => $this->terminalId,
            "clerkId"        => $this->clerkId,
            "marca"          => "terrecargamos",
            "invoiceNo"      => rand(1,99999),
            "languageOption" => "2"
        ]);
    }
}
?>
