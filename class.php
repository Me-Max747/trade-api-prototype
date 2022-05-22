<?php

class ApiTradePayeer
{
    private array $arParams = array();
    private array $arErrors = array();

    public function __construct($arParams = array())
    {
        $this->arParams = $arParams;
    }

    /**
     * @throws Exception
     */
    private function request($arRequest = array()): ?array
    {
        $arRequest["post"]["ts"] = round(microtime(true) * 1000);

        $arPost = json_encode($arRequest["post"]);

        $sSign = hash_hmac("sha256", $arRequest["method"].$arPost, $this->arParams["key"]);

        $rsCurlHandler = curl_init();
        curl_setopt($rsCurlHandler, CURLOPT_URL, "https://payeer.com/api/trade/".$arRequest["method"]);
        curl_setopt($rsCurlHandler, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($rsCurlHandler, CURLOPT_HEADER, false);
        curl_setopt($rsCurlHandler, CURLOPT_POST, true);
        curl_setopt($rsCurlHandler, CURLOPT_POSTFIELDS, $arPost);
        curl_setopt($rsCurlHandler, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            "API-ID: ".$this->arParams["id"],
            "API-SIGN: ".$sSign
        ));

        $sCurlResponse = curl_exec($rsCurlHandler);
        curl_close($rsCurlHandler);

        $arResponse = json_decode($sCurlResponse, true);

        if($arResponse["success"] !== true)
        {
            $this->arErrors[] = $arResponse["error"];
            throw new Exception($arResponse["error"]["code"]);
        }

        return $arResponse;
    }

    public function getError(): array
    {
        return $this->arErrors;
    }

    public function info(): ?array
    {
        return $this->request(array(
            "method" => "info",
        ));
    }

    public function orders($sPair = "BTC_USDT"): ?array
    {
        $arResponse = $this->request(array(
            "method" => "orders",
            "post" => array(
                "pair" => $sPair,
            ),
        ));

        return $arResponse["pairs"];
    }

    public function account(): ?array
    {
        $arResponse = $this->request(array(
            "method" => "account",
        ));

        return $arResponse["balances"];
    }

    public function orderCreate($arPost = array()): ?array
    {
        return $this->request(array(
            "method" => "order_create",
            "post" => $arPost,
        ));
    }

    public function orderStatus($arPost = array())
    {
        $arResponse = $this->request(array(
            "method" => "order_status",
            "post" => $arPost,
        ));

        return $arResponse["order"];
    }

    public function myOrders($arPost = array())
    {
        $arResponse = $this->request(array(
            "method" => "my_orders",
            "post" => $arPost,
        ));

        return $arResponse["items"];
    }
}