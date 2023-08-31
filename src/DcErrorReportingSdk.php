<?php

namespace DcTec\DcErrorReportingPhpSdk;

use Throwable;

class DcErrorReportingSdk {
    private string $apiUrl = 'https://dc-error-reporting.dctec.dev';

    private string $systemName;
    private string $enviroment;

    public function __construct(string $systemName, string $enviroment) {
        $this->systemName = $systemName;
        $this->enviroment = $enviroment;
    }

    public function send(Throwable $th) {
        $data = [
            'system_name' => $this->systemName,
            'environment' => $this->enviroment,
            'error' => $th->__toString(),
        ];
        
        $ch = curl_init($this->apiUrl . '/api/error_report');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
        ]);
        
        $response = curl_exec($ch);
        
        if (curl_errno($ch)) {
            echo 'Curl error: ' . curl_error($ch);
        }
        
        curl_close($ch);

        return $response;
    }
}