<?php

namespace ccxt;

use Exception as Exception; // a common import

class bcex extends Exchange {

    public function describe () {
        return array_replace_recursive (parent::describe (), array (
            'id' => 'bcex',
            'name' => 'BCEX',
            'countries' => array ( 'CN', 'CA' ),
            'version' => '1',
            'has' => array (
                'fetchBalance' => true,
                'fetchMarkets' => true,
                'createOrder' => true,
                'cancelOrder' => true,
                'fetchTicker' => true,
                'fetchTickers' => false,
                'fetchTrades' => true,
                'fetchOrder' => true,
                'fetchOrders' => true,
                'fetchOpenOrders' => true,
            ),
            'urls' => array (
                'logo' => 'https://user-images.githubusercontent.com/1294454/43362240-21c26622-92ee-11e8-9464-5801ec526d77.jpg',
                'api' => 'https://www.bcex.top',
                'www' => 'https://www.bcex.top',
                'doc' => 'https://www.bcex.top/api_market/market/',
                'fees' => 'http://bcex.udesk.cn/hc/articles/57085',
                'referral' => 'https://www.bcex.top/user/reg/type/2/pid/758978',
            ),
            'api' => array (
                'public' => array (
                    'get' => array (
                        'Api_Market/getPriceList', // tickers
                        'Api_Order/ticker', // last ohlcv candle (ticker)
                        'Api_Order/depth', // orderbook
                        'Api_Market/getCoinTrade', // ticker
                        'Api_Order/marketOrder', // trades...
                    ),
                    'post' => array (
                        'Api_Market/getPriceList', // tickers
                        'Api_Order/ticker', // last ohlcv candle (ticker)
                        'Api_Order/depth', // orderbook
                        'Api_Market/getCoinTrade', // ticker
                        'Api_Order/marketOrder', // trades...
                    ),
                ),
                'private' => array (
                    'post' => array (
                        'Api_Order/cancel',
                        'Api_Order/coinTrust', // limit order
                        'Api_Order/orderList', // open / all orders (my trades?)
                        'Api_Order/orderInfo',
                        'Api_Order/tradeList', // open / all orders
                        'Api_Order/trustList', // ?
                        'Api_User/userBalance',
                    ),
                ),
            ),
            'fees' => array (
                'trading' => array (
                    'tierBased' => false,
                    'percentage' => true,
                    'bid' => 0.0,
                    'ask' => 0.02 / 100,
                ),
                'funding' => array (
                    'tierBased' => false,
                    'percentage' => false,
                    'withdraw' => array (
                        'ckusd' => 0.0,
                        'other' => 0.05 / 100,
                    ),
                    'deposit' => array (),
                ),
                'exceptions' => array (
                    '该币不存在,非法操作' => '\\ccxt\\ExchangeError', // array ( code => 1, msg => "该币不存在,非法操作" ) - returned when a required symbol parameter is missing in the request (also, maybe on other types of errors as well)
                    '公钥不合法' => '\\ccxt\\AuthenticationError', // array ( code => 1, msg => '公钥不合法' ) - wrong public key
                ),
            ),
        ));
    }

    public function fetch_markets () {
        $response = $this->publicGetApiMarketGetPriceList ();
        $result = array ();
        $keys = is_array ($response) ? array_keys ($response) : array ();
        for ($i = 0; $i < count ($keys); $i++) {
            $currentMarketId = $keys[$i];
            $currentMarkets = $response[$currentMarketId];
            for ($j = 0; $j < count ($currentMarkets); $j++) {
                $market = $currentMarkets[$j];
                $baseId = $market['coin_from'];
                $quoteId = $market['coin_to'];
                $base = strtoupper ($baseId);
                $quote = strtoupper ($quoteId);
                $base = $this->common_currency_code($base);
                $quote = $this->common_currency_code($quote);
                $id = $baseId . '2' . $quoteId;
                $symbol = $base . '/' . $quote;
                $active = true;
                $precision = array (
                    'amount' => null, // todo => might need this for proper order placement
                    'price' => null, // todo => find a way to get these values
                );
                $limits = array (
                    'amount' => array (
                        'min' => null, // todo
                        'max' => null,
                    ),
                    'price' => array (
                        'min' => null, // todo
                        'max' => null,
                    ),
                    'cost' => array (
                        'min' => null, // todo
                        'max' => null,
                    ),
                );
                $result[] = array (
                    'id' => $id,
                    'symbol' => $symbol,
                    'base' => $base,
                    'quote' => $quote,
                    'baseId' => $baseId,
                    'quoteId' => $quoteId,
                    'active' => $active,
                    'precision' => $precision,
                    'limits' => $limits,
                    'info' => $market,
                );
            }
        }
        return $result;
    }

    public function parse_trade ($trade, $market = null) {
        $symbol = null;
        if ($market !== null) {
            $symbol = $market['symbol'];
        }
        $timestamp = $this->safe_integer_2($trade, 'date', 'created');
        if ($timestamp !== null) {
            $timestamp = $timestamp * 1000;
        }
        $id = $this->safe_string($trade, 'tid');
        $orderId = $this->safe_string($trade, 'order_id');
        $amount = $this->safe_float_2($trade, 'number', 'amount');
        $price = $this->safe_float($trade, 'price');
        $cost = null;
        if ($price !== null) {
            if ($amount !== null) {
                $cost = $amount * $price;
            }
        }
        $side = $this->safe_string($trade, 'type');
        return array (
            'info' => $trade,
            'id' => $id,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'symbol' => $symbol,
            'type' => null,
            'side' => $side,
            'price' => $price,
            'amount' => $amount,
            'cost' => $cost,
            'order' => $orderId,
            'fee' => null,
        );
    }

    public function fetch_trades ($symbol, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $request = array (
            'symbol' => $this->market_id($symbol),
        );
        if ($limit !== null) {
            $request['limit'] = $limit;
        }
        $market = $this->market ($symbol);
        $response = $this->publicPostApiOrderMarketOrder (array_merge ($request, $params));
        return $this->parse_trades($response['data'], $market, $since, $limit);
    }

    public function fetch_balance ($params = array ()) {
        $this->load_markets();
        $response = $this->privatePostApiUserUserBalance ($params);
        $data = $response['data'];
        $keys = is_array ($data) ? array_keys ($data) : array ();
        $result = array ( );
        for ($i = 0; $i < count ($keys); $i++) {
            $key = $keys[$i];
            $amount = $this->safe_float($data, $key);
            $parts = explode ('_', $key);
            $currencyId = $parts[0];
            $lockOrOver = $parts[1];
            $code = strtoupper ($currencyId);
            if (is_array ($this->currencies_by_id) && array_key_exists ($currencyId, $this->currencies_by_id)) {
                $code = $this->currencies_by_id[$currencyId]['code'];
            } else {
                $code = $this->common_currency_code($code);
            }
            if (!(is_array ($result) && array_key_exists ($code, $result))) {
                $account = $this->account ();
                $result[$code] = $account;
            }
            if ($lockOrOver === 'lock') {
                $result[$code]['used'] = floatval ($amount);
            } else {
                $result[$code]['free'] = floatval ($amount);
            }
        }
        $keys = is_array ($result) ? array_keys ($result) : array ();
        for ($i = 0; $i < count ($keys); $i++) {
            $key = $keys[$i];
            $total = $this->sum ($result[$key]['used'], $result[$key]['total']);
            $result[$key]['total'] = $total;
        }
        $result['info'] = $data;
        return $this->parse_balance($result);
    }

    public function fetch_ticker ($symbol, $params = array ()) {
        $this->load_markets();
        $market = $this->markets[$symbol];
        $request = array (
            'part' => $market['quoteId'],
            'coin' => $market['baseId'],
        );
        $response = $this->publicPostApiMarketGetCoinTrade (array_merge ($request, $params));
        $timestamp = $this->milliseconds ();
        return array (
            'symbol' => $symbol,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'high' => $this->safe_float($response, 'max'),
            'low' => $this->safe_float($response, 'min'),
            'bid' => $this->safe_float($response, 'buy'),
            'bidVolume' => null,
            'ask' => $this->safe_float($response, 'sale'),
            'askVolume' => null,
            'vwap' => null,
            'open' => null,
            'close' => $this->safe_float($response, 'price'),
            'last' => $this->safe_float($response, 'price'),
            'previousClose' => null,
            'change' => null,
            'percentage' => $this->safe_float($response, 'change_24h'),
            'average' => null,
            'baseVolume' => $this->safe_float($response, 'volume_24h'),
            'quoteVolume' => null,
            'info' => $response,
        );
    }

    public function fetch_order_book ($symbol, $limit = null, $params = array ()) {
        $this->load_markets();
        $marketId = $this->market_id($symbol);
        $request = array (
            'symbol' => $marketId,
        );
        $response = $this->publicPostApiOrderDepth (array_merge ($request, $params));
        $data = $response['data'];
        $orderbook = $this->parse_order_book($data, $data['date'] * 1000);
        return $orderbook;
    }

    public function fetch_my_trades ($symbol = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array (
            'symbol' => $market['id'],
        );
        $response = $this->privatePostApiOrderOrderList (array_merge ($request, $params));
        return $this->parse_trades($response['data'], $market, $since, $limit);
    }

    public function parse_order_status ($status) {
        $statuses = array (
            '0' => 'open',
            '1' => 'open', // partially filled
            '2' => 'closed',
            '3' => 'canceled',
        );
        if (is_array ($statuses) && array_key_exists ($status, $statuses)) {
            return $statuses[$status];
        }
        return $status;
    }

    public function fetch_order ($id, $symbol = null, $params = array ()) {
        $this->load_markets();
        $request = array (
            'symbol' => $this->market_id($symbol),
            'trust_id' => $id,
        );
        $response = $this->privatePostApiOrderOrderInfo (array_merge ($request, $params));
        $order = $response['data'];
        $timestamp = $order['created'] * 1000;
        $status = $this->parseStatus ($order['status']);
        $result = array (
            'info' => $order,
            'id' => $id,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'lastTradeTimestamp' => null,
            'symbol' => $symbol,
            'type' => $order['flag'],
            'side' => null,
            'price' => $order['price'],
            'cost' => null,
            'average' => null,
            'amount' => $order['number'],
            'filled' => $order['numberdeal'],
            'remaining' => $order['numberover'],
            'status' => $status,
            'fee' => null,
        );
        return $result;
    }

    public function parse_order ($order, $market = null) {
        $id = $this->safe_string($order, 'id');
        $timestamp = $order['datetime'] * 1000;
        $iso8601 = $this->iso8601 ($timestamp);
        $symbol = $market['symbol'];
        $type = null;
        $side = $order['type'];
        $price = $order['price'];
        $average = $order['avg_price'];
        $amount = $order['amount'];
        $remaining = $order['amount_outstanding'];
        $filled = $amount - $remaining;
        $status = $this->safe_string($order, 'status');
        $status = $this->parse_order_status($status);
        $cost = $filled * $price;
        $fee = null;
        $result = array (
            'info' => $order,
            'id' => $id,
            'timestamp' => $timestamp,
            'datetime' => $iso8601,
            'lastTradeTimestamp' => null,
            'symbol' => $symbol,
            'type' => $type,
            'side' => $side,
            'price' => $price,
            'cost' => $cost,
            'average' => $average,
            'amount' => $amount,
            'filled' => $filled,
            'remaining' => $remaining,
            'status' => $status,
            'fee' => $fee,
        );
        return $result;
    }

    public function fetch_orders_by_type ($type, $symbol = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $request = array (
            'type' => $type,
        );
        $market = null;
        if ($symbol !== null) {
            $market = $this->market ($symbol);
            $request['symbol'] = $market['id'];
        }
        $response = $this->privatePostApiOrderTradeList (array_merge ($request, $params));
        if (is_array ($response) && array_key_exists ('data', $response)) {
            return $this->parse_orders($response['data'], $market, $since, $limit);
        }
        return array ();
    }

    public function fetch_open_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        return $this->fetch_orders_by_type ('open', $symbol, $since, $limit, $params);
    }

    public function fetch_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        return $this->fetch_orders_by_type ('all', $symbol, $since, $limit, $params);
    }

    public function create_order ($symbol, $type, $side, $amount, $price = null, $params = array ()) {
        $this->load_markets();
        $order = array (
            'symbol' => $this->market_id($symbol),
            'type' => $side,
            'price' => $price,
            'number' => $amount,
        );
        $response = $this->privatePostApiOrderCoinTrust (array_merge ($order, $params));
        $data = $response['data'];
        return array (
            'info' => $response,
            'id' => $this->safe_string($data, 'order_id'),
        );
    }

    public function cancel_order ($id, $symbol = null, $params = array ()) {
        $this->load_markets();
        $request = array ();
        if ($symbol !== null) {
            $request['symbol'] = $symbol;
        }
        if ($id !== null) {
            $request['order_id'] = $id;
        }
        $results = $this->privatePostApiOrderCancel (array_merge ($request, $params));
        return $results;
    }

    public function sign ($path, $api = 'public', $method = 'GET', $params = array (), $headers = null, $body = null) {
        $url = $this->urls['api'] . '/' . $this->implode_params($path, $params);
        $query = $this->omit ($params, $this->extract_params($path));
        if ($api === 'public') {
            if ($query) {
                $url .= '?' . $this->urlencode ($query);
            }
        } else {
            $this->check_required_credentials();
            $payload = $this->urlencode (array ( 'api_key' => $this->apiKey ));
            if ($query) {
                $payload .= '&' . $this->urlencode ($this->keysort ($query));
            }
            $auth = $payload . '&secret_key=' . $this->secret;
            $signature = $this->hash ($this->encode ($auth));
            $body = $payload . '&sign=' . $signature;
            $headers = array (
                'Content-Type' => 'application/x-www-form-urlencoded',
            );
        }
        return array ( 'url' => $url, 'method' => $method, 'body' => $body, 'headers' => $headers );
    }

    public function handle_errors ($code, $reason, $url, $method, $headers, $body) {
        if (gettype ($body) !== 'string')
            return; // fallback to default error handler
        if (strlen ($body) < 2)
            return; // fallback to default error handler
        if (($body[0] === '{') || ($body[0] === '[')) {
            $response = json_decode ($body, $as_associative_array = true);
            $feedback = $this->id . ' ' . $body;
            $code = $this->safe_value($response, 'code');
            if ($code !== null) {
                if ($code !== 0) {
                    //
                    // array ( $code => 1, msg => "该币不存在,非法操作" ) - returned when a required symbol parameter is missing in the request (also, maybe on other types of errors as well)
                    // array ( $code => 1, msg => '公钥不合法' ) - wrong public key
                    //
                    $message = $this->safe_string($response, 'msg');
                    $exceptions = $this->exceptions;
                    if (is_array ($exceptions) && array_key_exists ($message, $exceptions)) {
                        throw new $exceptions[$message] ($feedback);
                    } else {
                        throw new ExchangeError ($feedback);
                    }
                }
            }
        }
    }
}
