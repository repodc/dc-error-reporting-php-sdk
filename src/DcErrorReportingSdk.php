<?php

namespace DcTec\DcErrorReportingPhpSdk;

use Throwable;

class DcErrorReportingSdk {
    private string $apiUrl = 'https://dc-error-reporting.dctec.dev';

    private string $systemName;
    private string $environment;
    private $token;

    public function __construct(string $systemName, string $environment, $token) {
        $this->systemName = $systemName;
        $this->environment = $environment;
        $this->token = $token;
    }

    public function send(Throwable $th) {
        try {
            if (!$this->token || (!$this->environment || $this->environment === 'local')) {
                return;
            }

            $data = [
                'system_name' => $this->systemName,
                'environment' => $this->environment,
                'requested_url' => $this->getServerRequestedUrl(),
                'error' => $th->__toString(),
            ];

            $ch = curl_init($this->apiUrl . '/api/error_report');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->token
            ]);

            $response = curl_exec($ch);

            if (curl_errno($ch)) {
                echo 'Curl error: ' . curl_error($ch);
            }

            curl_close($ch);

            return $response;
        } catch (\Throwable $th) {
            // Se der erro nada vai ser feito
        }
    }

    private function getServerRequestedUrl() {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'];
        $base_url = $protocol . "://" . $host;

        $current_url = $base_url . $_SERVER['REQUEST_URI'];

        return $current_url;
    }
}
