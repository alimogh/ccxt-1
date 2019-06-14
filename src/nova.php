<?php

namespace ccxt;

use Exception as Exception; // a common import

class nova extends Exchange {

    public function describe () {
        return array_replace_recursive (parent::describe (), array (
            'id' => 'nova',
            'name' => 'Novaexchange',
            'countries' => array ( 'TZ' ), // Tanzania
            'rateLimit' => 2000,
            'version' => 'v2',
            'has' => array (
                'CORS' => false,
                'createMarketOrder' => false,
                'createDepositAddress' => true,
                'fetchDepositAddress' => true,
            ),
            'urls' => array (
                'logo' => 'https://user-images.githubusercontent.com/1294454/30518571-78ca0bca-9b8a-11e7-8840-64b83a4a94b2.jpg',
                'api' => 'https://novaexchange.com/remote',
                'www' => 'https://novaexchange.com',
                'doc' => 'https://novaexchange.com/remote/faq',
            ),
            'api' => array (
                'public' => array (
                    'get' => array (
                        'markets/',
                        'markets/{basecurrency}/',
                        'market/info/{pair}/',
                        'market/orderhistory/{pair}/',
                        'market/openorders/{pair}/buy/',
                        'market/openorders/{pair}/sell/',
                        'market/openorders/{pair}/both/',
                        'market/openorders/{pair}/{ordertype}/',
                    ),
                ),
                'private' => array (
                    'post' => array (
                        'getbalances/',
                        'getbalance/{currency}/',
                        'getdeposits/',
                        'getwithdrawals/',
                        'getnewdepositaddress/{currency}/',
                        'getdepositaddress/{currency}/',
                        'myopenorders/',
                        'myopenorders_market/{pair}/',
                        'cancelorder/{orderid}/',
                        'withdraw/{currency}/',
                        'trade/{pair}/',
                        'tradehistory/',
                        'getdeposithistory/',
                        'getwithdrawalhistory/',
                        'walletstatus/',
                        'walletstatus/{currency}/',
                    ),
                ),
            ),
        ));
    }

    public function fetch_markets ($params = array ()) {
        $response = $this->publicGetMarkets ();
        $markets = $response['markets'];
        $result = array();
        for ($i = 0; $i < count ($markets); $i++) {
            $market = $markets[$i];
            $id = $market['marketname'];
            list($quote, $base) = explode('_', $id);
            $symbol = $base . '/' . $quote;
            $active = true;
            if ($market['disabled']) {
                $active = false;
            }
            $result[] = array (
                'id' => $id,
                'symbol' => $symbol,
                'base' => $base,
                'quote' => $quote,
                'active' => $active,
                'info' => $market,
            );
        }
        return $result;
    }

    public function fetch_order_book ($symbol, $limit = null, $params = array ()) {
        $this->load_markets();
        $orderbook = $this->publicGetMarketOpenordersPairBoth (array_merge (array (
            'pair' => $this->market_id($symbol),
        ), $params));
        return $this->parse_order_book($orderbook, null, 'buyorders', 'sellorders', 'price', 'amount');
    }

    public function fetch_ticker ($symbol, $params = array ()) {
        $this->load_markets();
        $response = $this->publicGetMarketInfoPair (array_merge (array (
            'pair' => $this->market_id($symbol),
        ), $params));
        $ticker = $response['markets'][0];
        $timestamp = $this->milliseconds ();
        $last = $this->safe_float($ticker, 'last_price');
        return array (
            'symbol' => $symbol,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'high' => $this->safe_float($ticker, 'high24h'),
            'low' => $this->safe_float($ticker, 'low24h'),
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
            'percentage' => $this->safe_float($ticker, 'change24h'),
            'average' => null,
            'baseVolume' => null,
            'quoteVolume' => $this->safe_float($ticker, 'volume24h'),
            'info' => $ticker,
        );
    }

    public function parse_trade ($trade, $market) {
        $timestamp = $trade['unix_t_datestamp'] * 1000;
        return array (
            'info' => $trade,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'symbol' => $market['symbol'],
            'id' => null,
            'order' => null,
            'type' => null,
            'side' => strtolower($trade['tradetype']),
            'price' => $this->safe_float($trade, 'price'),
            'amount' => $this->safe_float($trade, 'amount'),
        );
    }

    public function fetch_trades ($symbol, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $response = $this->publicGetMarketOrderhistoryPair (array_merge (array (
            'pair' => $market['id'],
        ), $params));
        return $this->parse_trades($response['items'], $market, $since, $limit);
    }

    public function fetch_balance ($params = array ()) {
        $this->load_markets();
        $response = $this->privatePostGetbalances ($params);
        $balances = $this->safe_value($response, 'balances');
        $result = array( 'info' => $response );
        for ($i = 0; $i < count ($balances); $i++) {
            $balance = $balances[$i];
            $currencyId = $this->safe_string($balance, 'currency');
            $code = $this->common_currency_code($currencyId);
            $lockbox = $this->safe_float($balance, 'amount_lockbox');
            $trades = $this->safe_float($balance, 'amount_trades');
            $account = array (
                'free' => $this->safe_float($balance, 'amount'),
                'used' => $this->sum ($lockbox, $trades),
                'total' => $this->safe_float($balance, 'amount_total'),
            );
            $result[$code] = $account;
        }
        return $this->parse_balance($result);
    }

    public function create_order ($symbol, $type, $side, $amount, $price = null, $params = array ()) {
        if ($type === 'market') {
            throw new ExchangeError($this->id . ' allows limit orders only');
        }
        $this->load_markets();
        $amount = (string) $amount;
        $price = (string) $price;
        $market = $this->market ($symbol);
        $request = array (
            'tradetype' => strtoupper($side),
            'tradeamount' => $amount,
            'tradeprice' => $price,
            'tradebase' => 1,
            'pair' => $market['id'],
        );
        $response = $this->privatePostTradePair (array_merge ($request, $params));
        $tradeItems = $this->safe_value($response, 'tradeitems', array());
        $tradeItemsByType = $this->index_by($tradeItems, 'type');
        $created = $this->safe_value($tradeItemsByType, 'created', array());
        $orderId = $this->safe_string($created, 'orderid');
        return array (
            'info' => $response,
            'id' => $orderId,
        );
    }

    public function cancel_order ($id, $symbol = null, $params = array ()) {
        $request = array (
            'orderid' => $id,
        );
        return $this->privatePostCancelorder (array_merge ($request, $params));
    }

    public function create_deposit_address ($code, $params = array ()) {
        $this->load_markets();
        $currency = $this->currency ($code);
        $request = array (
            'currency' => $currency['id'],
        );
        $response = $this->privatePostGetnewdepositaddressCurrency (array_merge ($request, $params));
        $address = $this->safe_string($response, 'address');
        $this->check_address($address);
        $tag = $this->safe_string($response, 'tag');
        return array (
            'currency' => $code,
            'address' => $address,
            'tag' => $tag,
            'info' => $response,
        );
    }

    public function fetch_deposit_address ($code, $params = array ()) {
        $this->load_markets();
        $currency = $this->currency ($code);
        $request = array (
            'currency' => $currency['id'],
        );
        $response = $this->privatePostGetdepositaddressCurrency (array_merge ($request, $params));
        $address = $this->safe_string($response, 'address');
        $this->check_address($address);
        $tag = $this->safe_string($response, 'tag');
        return array (
            'currency' => $code,
            'address' => $address,
            'tag' => $tag,
            'info' => $response,
        );
    }

    public function sign ($path, $api = 'public', $method = 'GET', $params = array (), $headers = null, $body = null) {
        $url = $this->urls['api'] . '/' . $this->version . '/';
        if ($api === 'private') {
            $url .= $api . '/';
        }
        $url .= $this->implode_params($path, $params);
        $query = $this->omit ($params, $this->extract_params($path));
        if ($api === 'public') {
            if ($query) {
                $url .= '?' . $this->urlencode ($query);
            }
        } else {
            $this->check_required_credentials();
            $nonce = (string) $this->nonce ();
            $url .= '?' . $this->urlencode (array( 'nonce' => $nonce ));
            $signature = $this->hmac ($this->encode ($url), $this->encode ($this->secret), 'sha512', 'base64');
            $body = $this->urlencode (array_merge (array (
                'apikey' => $this->apiKey,
                'signature' => $signature,
            ), $query));
            $headers = array (
                'Content-Type' => 'application/x-www-form-urlencoded',
            );
        }
        return array( 'url' => $url, 'method' => $method, 'body' => $body, 'headers' => $headers );
    }

    public function request ($path, $api = 'public', $method = 'GET', $params = array (), $headers = null, $body = null) {
        $response = $this->fetch2 ($path, $api, $method, $params, $headers, $body);
        if (is_array($response) && array_key_exists('status', $response)) {
            if ($response['status'] !== 'success') {
                throw new ExchangeError($this->id . ' ' . $this->json ($response));
            }
        }
        return $response;
    }
}
