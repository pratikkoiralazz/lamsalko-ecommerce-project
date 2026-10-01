<?php
require_once __DIR__ . '/../config/esewa.php';

function verify_esewa_transaction(string $transaction_uuid, string $amount): bool {
    $product_code = ESEWA_MERCHANT_CODE;
    $url = ESEWA_VERIFY_URL . "?product_code={$product_code}&total_amount={$amount}&transaction_uuid={$transaction_uuid}";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code === 200 && $response) {
        $data = json_decode($response, true);
        if (isset($data['status']) && strtoupper($data['status']) === 'COMPLETE') {
            return true;
        }
    }
    return false;
}