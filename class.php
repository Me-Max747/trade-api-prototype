<?php

class ApiTradePayeer
{
    private array $arParams = array();
    private array $arError = array();

    public function __construct($arParams = array())
    {
        $this->arParams = $arParams;
    }

    /**
     * @param array $arRequest
     * @return array
     * @throws Exception
     */
    private function request(array $arRequest = array()): array
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
            $this->arError = $arResponse["error"];
            throw new Exception($arResponse["error"]["code"]);
        }

        return $arResponse ?? array();
    }

    /**
     * @return array
     */
    public function getError(): array
    {
        return $this->arError;
    }

    /**
     * @return array
     * @throws Exception
     */
    public function info(): array
    {
        return $this->request(array(
            "method" => "info",
        ));
    }

    /**
     * @param string $sPair
     * @return array
     * @throws Exception
     */
    public function orders(string $sPair = "BTC_USDT"): array
    {
        $arResponse = $this->request(array(
            "method" => "orders",
            "post" => array(
                "pair" => $sPair,
            ),
        ));

        return $arResponse["pairs"] ?? array();
    }

    /**
     * @return array
     * @throws Exception
     */
    public function account(): array
    {
        $arResponse = $this->request(array(
            "method" => "account",
        ));

        return $arResponse["balances"] ?? array();
    }

    /**
     * @param array $arPost
     * @return array
     * @throws Exception
     */
    public function orderCreate(array $arPost = array()): array
    {
        return $this->request(array(
            "method" => "order_create",
            "post" => $arPost,
        ));
    }

    /**
     * @param array $arPost
     * @return array
     * @throws Exception
     */
    public function orderStatus(array $arPost = array()): array
    {
        $arResponse = $this->request(array(
            "method" => "order_status",
            "post" => $arPost,
        ));

        return $arResponse["order"] ?? array();
    }

    /**
     * @param array $arPost
     * @return array
     * @throws Exception
     */
    public function myOrders(array $arPost = array()): array
    {
        $arResponse = $this->request(array(
            "method" => "my_orders",
            "post" => $arPost,
        ));

        return $arResponse["items"] ?? array();
    }
}