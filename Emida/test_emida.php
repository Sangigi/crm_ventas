<?php

class EmidaRPC
{
    private $endpoint = "https://ws.terecargamos.com:8448/soap/servlet/rpcrouter";

    private $terminalId = "4418653";
    private $clerkId    = "e55it7";

    private function send($xml)
    {
        $headers = [
            "Content-Type: text/xml; charset=utf-8",
            "SOAPAction: ''"
        ];

        $ch = curl_init($this->endpoint);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 40);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            return [
                "success" => false,
                "error" => curl_error($ch)
            ];
        }

        curl_close($ch);

        return [
            "success" => true,
            "raw" => $response
        ];
    }

    // ===================================
    // MÉTODO CORRECTO PARA PRODUCTOS
    // ===================================
    public function ProductFlowInfoService()
    {
        $json = json_encode([
            "version"   => "1",
            "terminalId"=> $this->terminalId,
            "invoiceNo" => "01",
            "language"  => "1",
            "clerkId"   => $this->clerkId
        ]);

        $xml = '
        <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/">
            <soapenv:Body>
                <executeCommand xmlns="urn:debisys-soap-services">
                    <command xsi:type="xsd:string" 
                        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                        xmlns:xsd="http://www.w3.org/2001/XMLSchema">
                        ProductFlowInfoService
                    </command>

                    <parameters xsi:type="xsd:string"
                        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                        xmlns:xsd="http://www.w3.org/2001/XMLSchema">
                        '.$json.'
                    </parameters>
                </executeCommand>
            </soapenv:Body>
        </soapenv:Envelope>';

        return $this->send(trim($xml));
    }

    // ===================================
    // PinDistSale (sí es método directo)
    // ===================================
    public function PinDistSale($productId, $accountId, $amount, $invoiceNo)
    {
        $xml = '
        <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/">
            <soapenv:Body>
                <PinDistSale xmlns="urn:debisys-soap-services">
                    <version>01</version>
                    <terminalId>'.$this->terminalId.'</terminalId>
                    <clerkId>'.$this->clerkId.'</clerkId>
                    <productId>'.$productId.'</productId>
                    <accountId>'.$accountId.'</accountId>
                    <amount>'.$amount.'</amount>
                    <invoiceNo>'.$invoiceNo.'</invoiceNo>
                    <languageOption>1</languageOption>
                </PinDistSale>
            </soapenv:Body>
        </soapenv:Envelope>';

        return $this->send($xml);
    }

    // ===================================
    // LookupTransactionByInvoiceNo
    // ===================================
    public function LookupTransactionByInvoiceNo($invoiceNo)
    {
        $xml = '
        <soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/">
            <soapenv:Body>
                <LookUpTransactionByInvoiceRequest xmlns="urn:debisys-soap-services">
                    <Version>01</Version>
                    <TerminalId>'.$this->terminalId.'</TerminalId>
                    <ClerkId>'.$this->clerkId.'</ClerkId>
                    <InvoiceNo>'.$invoiceNo.'</InvoiceNo>
                </LookUpTransactionByInvoiceRequest>
            </soapenv:Body>
        </soapenv:Envelope>';

        return $this->send($xml);
    }
}

// =====================
// PRUEBA DE PRODUCTOS
// =====================
$emida = new EmidaRPC();
print_r($emida->ProductFlowInfoService());

?>
