<?php

namespace ccxt;

use Exception as Exception; // a common import

class coinspot extends Exchange {

    public function describe () {
        return array_replace_recursive (parent::describe (), array (
            'id' => 'coinspot',
            'name' => 'CoinSpot',
            'countries' => array ( 'AU' ), // Australia
            'rateLimit' => 1000,
            'has' => array (
                'CORS' => false,
                'createMarketOrder' => false,
            ),
            'urls' => array (
                'logo' => 'https://user-images.githubusercontent.com/1294454/28208429-3cacdf9a-6896-11e7-854e-4c79a772a30f.jpg',
                'api' => array (
                    'public' => 'https://www.coinspot.com.au/pubapi',
                    'private' => 'https://www.coinspot.com.au/api',
                ),
                'www' => 'https://www.coinspot.com.au',
                'doc' => 'https://www.coinspot.com.au/api',
                'referral' => 'https://www.coinspot.com.au/join/FSM11C',
            ),
            'api' => array (
                'public' => array (
                    'get' => array (
                        'latest',
                    ),
                ),
                'private' => array (
                    'post' => array (
                        'orders',
                        'orders/history',
                        'my/coin/deposit',
                        'my/coin/send',
                        'quote/buy',
                        'quote/sell',
                        'my/balances',
                        'my/orders',
                        'my/buy',
                        'my/sell',
                        'my/buy/cancel',
                        'my/sell/cancel',
                    ),
                ),
            ),
            'markets' => array (
                'BTC/AUD' => array( 'id' => 'btc', 'symbol' => 'BTC/AUD', 'base' => 'BTC', 'quote' => 'AUD', 'baseId' => 'btc', 'quoteId' => 'aud' ),
                'LTC/AUD' => array( 'id' => 'ltc', 'symbol' => 'LTC/AUD', 'base' => 'LTC', 'quote' => 'AUD', 'baseId' => 'ltc', 'quoteId' => 'aud' ),
                'DOGE/AUD' => array( 'id' => 'doge', 'symbol' => 'DOGE/AUD', 'base' => 'DOGE', 'quote' => 'AUD', 'baseId' => 'doge', 'quoteId' => 'aud' ),
            ),
            'commonCurrencies' => array (
                'DRK' => 'DASH',
            ),
        ));
    }

    public function fetch_balance ($params = array ()) {
        $this->load_markets();
        $response = $this->privatePostMyBalances ($params);
        $result = array( 'info' => $response );
        if (is_array($response) && array_key_exists('balance', $response)) {
            $balances = $response['balance'];
            $currencyIds = is_array($balances) ? array_keys($balances) : array();
            for ($i = 0; $i < count ($currencyIds); $i++) {
                $currencyId = $currencyIds[$i];
                $uppercase = strtoupper($currencyId);
                $code = $this->common_currency_code($uppercase);
                $account = array (
                    'free' => $balances[$currencyId],
                    'used' => 0.0,
                    'total' => $balances[$currencyId],
                );
                $result[$code] = $account;
            }
        }
        return $this->parse_balance($result);
    }

    public function fetch_order_book ($symbol, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array (
            'cointype' => $market['id'],
        );
        $orderbook = $this->privatePostOrders (array_merge ($request, $params));
        return $this->parse_order_book($orderbook, null, 'buyorders', 'sellorders', 'rate', 'amount');
    }

    public function fetch_ticker ($symbol, $params = array ()) {
        $this->load_markets();
        $response = $this->publicGetLatest ($params);
        $id = $this->market_id($symbol);
        $id = strtolower($id);
        $ticker = $response['prices'][$id];
        $timestamp = $this->milliseconds ();
        $last = $this->safe_float($ticker, 'last');
        return array (
            'symbol' => $symbol,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'high' => null,
            'low' => null,
            'bid' => $this->safe_float($ticker, 'bid'),
            'bidVolume' => null,
            'ask' => $this->safe_float($ticker, 'ask'),
            'askVolume' => null,
            'vwap' => null,
            'open' => null,
            'close' => $last,
            'last' => $last,
            'previousClose' => null,
            'change' => null,
            'percentage' => null,
            'average' => null,
            'baseVolume' => null,
            'quoteVolume' => null,
            'info' => $ticker,
        );
    }

    public function fetch_trades ($symbol, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $request = array (
            'cointype' => $this->market_id($symbol),
        );
        return $this->privatePostOrdersHistory (array_merge ($request, $params));
    }

    public function create_order ($symbol, $type, $side, $amount, $price = null, $params = array ()) {
        $this->load_markets();
        $method = 'privatePostMy' . $this->capitalize ($side);
        if ($type === 'market') {
            throw new ExchangeError($this->id . ' allows limit orders only');
        }
        $request = array (
            'cointype' => $this->market_id($symbol),
            'amount' => $amount,
            'rate' => $price,
        );
        return $this->$method (array_merge ($request, $params));
    }

    public function cancel_order ($id, $symbol = null, $params = array ()) {
        throw new NotSupported($this->id . ' cancelOrder () is not fully implemented yet');
        // $method = 'privatePostMyBuy';
        // return $this->$method (array( 'id' => $id ));
    }

    public function sign ($path, $api = 'public', $method = 'GET', $params = array (), $headers = null, $body = null) {
        if (!$this->apiKey) {
            throw new AuthenticationError($this->id . ' requires apiKey for all requests');
        }
        $url = $this->urls['api'][$api] . '/' . $path;
        if ($api === 'private') {
            $this->check_required_credentials();
            $nonce = $this->nonce ();
            $body = $this->json (array_merge (array( 'nonce' => $nonce ), $params));
            $headers = array (
                'Content-Type' => 'application/json',
                'key' => $this->apiKey,
                'sign' => $this->hmac ($this->encode ($body), $this->encode ($this->secret), 'sha512'),
            );
        }
        return array( 'url' => $url, 'method' => $method, 'body' => $body, 'headers' => $headers );
    }
}
