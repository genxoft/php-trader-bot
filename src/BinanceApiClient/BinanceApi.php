<?php

declare(strict_types=1);

namespace App\BinanceApiClient;

use App\BinanceApiClient\Exception\ApiRequestException;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use InvalidArgumentException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\ResponseInterface;

class BinanceApi implements BinanceApiInterface
{
    private ClientInterface $httpClient;

    public function __construct(
        readonly private string $apiKey,
        readonly private string $apiSecret,
        ?string $baseUri = null,
        readonly private bool $testMode = true,
    ) {
        if ($baseUri === null) {
            $baseUri = self::BASE_URI;
        }
        $this->httpClient = new Client([
            'base_uri' => $baseUri,
        ]);
    }

    /**
     * @return mixed
     * @throws ApiRequestException
     */
    public function ping(): array
    {
        $response = $this->request('GET', '/api/v3/ping');
        return $this->decodeResponse($response);
    }

    /**
     * @return mixed
     * @throws ApiRequestException
     */
    public function time(): array
    {
        $response = $this->request('GET', '/api/v3/time');
        return $this->decodeResponse($response);
    }

    /**
     * @inheritDoc
     * @throws ApiRequestException
     */
    public function getKlines(string $symbol, string $interval, ?int $startTime = null, ?int $endTime = null, int $limit = 500): KlineCollection
    {
        $params =  [
            'symbol' => $symbol,
            'interval' => $interval,
        ];

        if ($startTime !== null) {
            if ($startTime < 0) {
                throw new InvalidArgumentException("Invalid startTime");
            }
            $params['startTime'] = $startTime;
        }
        if ($endTime !== null) {
            if ($endTime < 0) {
                throw new InvalidArgumentException("Invalid endTime");
            }
            $params['startTime'] = $endTime;
        }
        if ($limit !== null) {
            if ($limit < 1 || $limit > 1000) {
                throw new InvalidArgumentException("Invalid limit");
            }
            $params['limit'] = $limit;
        }
        $response = $this->request('GET', '/api/v3/klines', $params);

        $data = $this->decodeResponse($response);

        return KlineCollection::buildFromApiData($data);
    }

    /**
     * @inheritDoc
     * @throws ApiRequestException
     */
    public function postOrder(
        string $symbol,
        Side $side,
        OrderType $type = OrderType::MARKET,
        ?TimeInForce $timeInForce = null,
        ?string $quantity = null,
        ?string $quoteOrderQty = null,
        ?string $price = null,
        ?string $newClientOrderId = null,
        ?string $stopPrice = null,
        ?int $trailingDelta = null,
        ?string $icebergQty = null,
        ?OrderResponseType $newOrderRespType = OrderResponseType::RESULT
    ): array {
        $uri = $this->testMode ? '/api/v3/order/test' : '/api/v3/order';

        $params = [
            'symbol'            => $symbol,
            'side'              => $side->value,
            'type'              => $type->value,
            'timeInForceEnum'   => $timeInForce,
            'quantity'          => $quantity,
            'quoteOrderQty'     => $quoteOrderQty,
            'price'             => $price,
            'newClientOrderId'  => $newClientOrderId,
            'trailingDelta'     => $trailingDelta,
            'icebergQty'        => $icebergQty,
            'newOrderRespType'  => $newOrderRespType->value,
            'timestamp'         => time() * 1000,

        ];

        $response = $this->request('POST', $uri, $params, true);

        return $this->decodeResponse($response);
    }

    /**
     * @param string $method
     * @param string $url
     * @param array $params
     * @param bool $signed
     * @return ResponseInterface
     * @throws ApiRequestException
     */
    protected function request(string $method, string $url, array $params = [], bool $signed = false): ResponseInterface
    {
        try {
            $response = match ($method) {
                'GET', 'DELETE' => $this->httpClient->get($url, [
                    'headers' => [
                        'X-MBX-APIKEY' => $this->apiKey,
                    ],
                    'query' => $this->buildQueryString($params, $signed),
                ]),
                'POST', 'PUT', 'PATH' => $this->httpClient->post($url, [
                    'headers' => [
                        'X-MBX-APIKEY' => $this->apiKey,
                    ],
                    'body' => $this->buildQueryString($params, $signed),
                ]),
                default => throw new InvalidArgumentException('Invalid method'),
            };
        } catch (ClientExceptionInterface $e) {
            throw new ApiRequestException("HTTP client Exception", 0, $e);
        }
        if ($response->getStatusCode() !== 200) {
            throw new ApiRequestException(sprintf("Request returns %s: %s", $response->getStatusCode(), $response->getReasonPhrase()));
        }

        return $response;
    }

    /**
     * @internal
     * @param array $params
     * @param bool $signed
     * @return string
     */
    protected function buildQueryString(array $params = [], bool $signed = false): string
    {
        if (empty($params)) {
            return '';
        }

        $params = array_filter($params, static function (mixed $val) {
            return $val !== null;
        });

        $queryString = http_build_query($params);
        if ($signed) {
            $queryString .= '&signature=' . $this->signature($queryString);
        }
        return $queryString;
    }

    /**
     * @internal
     * @param string $queryString
     * @return string
     */
    protected function signature(string $queryString): string
    {
        return hash_hmac('sha256', $queryString, $this->apiSecret);
    }

    /**
     * @internal
     * @param ResponseInterface $response
     * @return array
     * @throws ApiRequestException
     */
    protected function decodeResponse(ResponseInterface $response): array
    {
        if (!str_contains($response->getHeader('Content-type')[0], 'application/json')) {
            throw new ApiRequestException("Invalid response content type");
        }

        $content = $response->getBody()->getContents();

        return json_decode($content, true);
    }
}
