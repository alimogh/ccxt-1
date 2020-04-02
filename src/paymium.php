<?php

namespace ccxt;

use Exception; // a common import
use \ccxt\ExchangeError;

class paymium extends Exchange {

    public function describe() {
        return $this->deep_extend(parent::describe (), array(
            'id' => 'paymium',
            'name' => 'Paymium',
            'countries' => array( 'FR', 'EU' ),
            'rateLimit' => 2000,
            'version' => 'v1',
            'has' => array(
                'CORS' => true,
            ),
            'urls' => array(
                'logo' => 'https://user-images.githubusercontent.com/1294454/27790564-a945a9d4-5ff9-11e7-9d2d-b635763f2f24.jpg',
                'api' => 'https://paymium.com/api',
                'www' => 'https://www.paymium.com',
                'fees' => 'https://www.paymium.com/page/help/fees',
                'doc' => array(
                    'https://github.com/Paymium/api-documentation',
                    'https://www.paymium.com/page/developers',
                ),
            ),
            'api' => array(
                'public' => array(
                    'get' => array(
                        'countries',
                        'data/{id}/ticker',
                        'data/{id}/trades',
                        'data/{id}/depth',
                        'bitcoin_charts/{id}/trades',
                        'bitcoin_charts/{id}/depth',
                    ),
                ),
                'private' => array(
                    'get' => array(
                        'merchant/get_payment/{UUID}',
                        'user',
                        'user/addresses',
                        'user/addresses/{btc_address}',
                        'user/orders',
                        'user/orders/{UUID}',
                        'user/price_alerts',
                    ),
                    'post' => array(
                        'user/orders',
                        'user/addresses',
                        'user/payment_requests',
                        'user/price_alerts',
                        'merchant/create_payment',
                    ),
                    'delete' => array(
                        'user/orders/{UUID}/cancel',
                        'user/price_alerts/{id}',
                    ),
                ),
            ),
            'markets' => array(
                'BTC/EUR' => array( 'id' => 'eur', 'symbol' => 'BTC/EUR', 'base' => 'BTC', 'quote' => 'EUR', 'baseId' => 'btc', 'quoteId' => 'eur' ),
            ),
            'fees' => array(
                'trading' => array(
                    'maker' => 0.002,
                    'taker' => 0.002,
                ),
            ),
        ));
    }

    public function fetch_balance($params = array ()) {
        $this->load_markets();
        $response = $this->privateGetUser ($params);
        $result = array( 'info' => $response );
        $currencies = is_array($this->currencies) ? array_keys($this->currencies) : array();
        for ($i = 0; $i < count($currencies); $i++) {
            $code = $currencies[$i];
            $currencyId = $this->currency_id($code);
            $free = 'balance_' . $currencyId;
            if (is_array($response) && array_key_exists($free, $response)) {
                $account = $this->account();
                $used = 'locked_' . $currencyId;
                $account['free'] = $this->safe_float($response, $free);
                $account['used'] = $this->safe_float($response, $used);
                $result[$code] = $account;
            }
        }
        return $this->parse_balance($result);
    }

    public function fetch_order_book($symbol, $limit = null, $params = array ()) {
        $this->load_markets();
        $request = array(
            'id' => $this->market_id($symbol),
        );
        $response = $this->publicGetDataIdDepth (array_merge($request, $params));
        return $this->parse_order_book($response, null, 'bids', 'asks', 'price', 'amount');
    }

    public function fetch_ticker($symbol, $params = array ()) {
        $request = array(
            'id' => $this->market_id($symbol),
        );
        $ticker = $this->publicGetDataIdTicker (array_merge($request, $params));
        $timestamp = $this->safe_timestamp($ticker, 'at');
        $vwap = $this->safe_float($ticker, 'vwap');
        $baseVolume = $this->safe_float($ticker, 'volume');
        $quoteVolume = null;
        if ($baseVolume !== null && $vwap !== null) {
            $quoteVolume = $baseVolume * $vwap;
        }
        $last = $this->safe_float($ticker, 'price');
        return array(
            'symbol' => $symbol,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601($timestamp),
            'high' => $this->safe_float($ticker, 'high'),
            'low' => $this->safe_float($ticker, 'low'),
            'bid' => $this->safe_float($ticker, 'bid'),
            'bidVolume' => null,
            'ask' => $this->safe_float($ticker, 'ask'),
            'askVolume' => null,
            'vwap' => $vwap,
            'open' => $this->safe_float($ticker, 'open'),
            'close' => $last,
            'last' => $last,
            'previousClose' => null,
            'change' => null,
            'percentage' => $this->safe_float($ticker, 'variation'),
            'average' => null,
            'baseVolume' => $baseVolume,
            'quoteVolume' => $quoteVolume,
            'info' => $ticker,
        );
    }

    public function parse_trade($trade, $market) {
        $timestamp = $this->safe_timestamp($trade, 'created_at_int');
        $id = $this->safe_string($trade, 'uuid');
        $symbol = null;
        if ($market !== null) {
            $symbol = $market['symbol'];
        }
        $side = $this->safe_string($trade, 'side');
        $price = $this->safe_float($trade, 'price');
        $amountField = 'traded_' . strtolower($market['base']);
        $amount = $this->safe_float($trade, $amountField);
        $cost = null;
        if ($price !== null) {
            if ($amount !== null) {
                $cost = $amount * $price;
            }
        }
        return array(
            'info' => $trade,
            'id' => $id,
            'order' => null,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601($timestamp),
            'symbol' => $symbol,
            'type' => null,
            'side' => $side,
            'takerOrMaker' => null,
            'price' => $price,
            'amount' => $amount,
            'cost' => $cost,
            'fee' => null,
        );
    }

    public function fetch_trades($symbol, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market($symbol);
        $request = array(
            'id' => $market['id'],
        );
        $response = $this->publicGetDataIdTrades (array_merge($request, $params));
        return $this->parse_trades($response, $market, $since, $limit);
    }

    public function create_order($symbol, $type, $side, $amount, $price = null, $params = array ()) {
        $this->load_markets();
        $request = array(
            'type' => $this->capitalize($type) . 'Order',
            'currency' => $this->market_id($symbol),
            'direction' => $side,
            'amount' => $amount,
        );
        if ($type !== 'market') {
            $request['price'] = $price;
        }
        $response = $this->privatePostUserOrders (array_merge($request, $params));
        return array(
            'info' => $response,
            'id' => $response['uuid'],
        );
    }

    public function cancel_order($id, $symbol = null, $params = array ()) {
        $request = array(
            'UUID' => $id,
        );
        return $this->privateDeleteUserOrdersUUIDCancel (array_merge($request, $params));
    }

    public function sign($path, $api = 'public', $method = 'GET', $params = array (), $headers = null, $body = null) {
        $url = $this->urls['api'] . '/' . $this->version . '/' . $this->implode_params($path, $params);
        $query = $this->omit($params, $this->extract_params($path));
        if ($api === 'public') {
            if ($query) {
                $url .= '?' . $this->urlencode($query);
            }
        } else {
            $this->check_required_credentials();
            $nonce = (string) $this->nonce();
            $auth = $nonce . $url;
            if ($method === 'POST') {
                if ($query) {
                    $body = $this->json($query);
                    $auth .= $body;
                }
            }
            $headers = array(
                'Api-Key' => $this->apiKey,
                'Api-Signature' => $this->hmac($this->encode($auth), $this->encode($this->secret)),
                'Api-Nonce' => $nonce,
                'Content-Type' => 'application/json',
            );
        }
        return array( 'url' => $url, 'method' => $method, 'body' => $body, 'headers' => $headers );
    }

    public function request($path, $api = 'public', $method = 'GET', $params = array (), $headers = null, $body = null) {
        $response = $this->fetch2($path, $api, $method, $params, $headers, $body);
        if (is_array($response) && array_key_exists('errors', $response)) {
            throw new ExchangeError($this->id . ' ' . $this->json($response));
        }
        return $response;
    }
}
