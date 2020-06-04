<?php

namespace ccxt;

use Exception; // a common import
use \ccxt\ExchangeError;
use \ccxt\ArgumentsRequired;
use \ccxt\InvalidOrder;
use \ccxt\OrderNotFound;

class coinone extends Exchange {

    public function describe() {
        return $this->deep_extend(parent::describe (), array(
            'id' => 'coinone',
            'name' => 'CoinOne',
            'countries' => array( 'KR' ), // Korea
            // 'enableRateLimit' => false,
            'rateLimit' => 667,
            'version' => 'v2',
            'has' => array(
                'CORS' => false,
                'createMarketOrder' => false,
                // 'fetchClosedOrders' => false, // not implemented yet
                'fetchCurrencies' => false,
                'fetchMarkets' => true,
                // 'fetchMyTrades' => false, // not implemented yet
                // 'fetchOpenOrders' => false, // not implemented yet
                'fetchOrder' => true,
                'fetchOrderBook' => true,
                'fetchOrderBooks' => false,
                // 'fetchOrders' => false, // not implemented yet
                'fetchTicker' => true,
                'fetchTickers' => true,
                'fetchTrades' => true,
            ),
            'urls' => array(
                'logo' => 'https://user-images.githubusercontent.com/1294454/38003300-adc12fba-323f-11e8-8525-725f53c4a659.jpg',
                'api' => 'https://api.coinone.co.kr',
                'www' => 'https://coinone.co.kr',
                'doc' => 'https://doc.coinone.co.kr',
            ),
            'requiredCredentials' => array(
                'apiKey' => true,
                'secret' => true,
            ),
            'api' => array(
                'public' => array(
                    'get' => array(
                        'orderbook/',
                        'trades/',
                        'ticker/',
                    ),
                ),
                'private' => array(
                    'post' => array(
                        'account/btc_deposit_address/',
                        'account/balance/',
                        'account/daily_balance/',
                        'account/user_info/',
                        'account/virtual_account/',
                        'order/cancel_all/',
                        'order/cancel/',
                        'order/limit_buy/',
                        'order/limit_sell/',
                        'order/complete_orders/',
                        'order/limit_orders/',
                        'order/order_info/',
                        'transaction/auth_number/',
                        'transaction/history/',
                        'transaction/krw/history/',
                        'transaction/btc/',
                        'transaction/coin/',
                    ),
                ),
            ),
            'fees' => array(
                'trading' => array(
                    'tierBased' => false,
                    'percentage' => true,
                    'taker' => 0.002,
                    'maker' => 0.002,
                ),
            ),
            'precision' => array(
                'price' => 4,
                'amount' => 4,
                'cost' => 8,
            ),
            'exceptions' => array(
                '405' => '\\ccxt\\OnMaintenance', // array("errorCode":"405","status":"maintenance","result":"error")
                '104' => '\\ccxt\\OrderNotFound',
                '108' => '\\ccxt\\BadSymbol', // array("errorCode":"108","errorMsg":"Unknown CryptoCurrency","result":"error")
                '107' => '\\ccxt\\BadRequest', // array("errorCode":"107","errorMsg":"Parameter error","result":"error")
            ),
        ));
    }

    public function fetch_markets($params = array ()) {
        $request = array(
            'currency' => 'all',
        );
        $response = $this->publicGetTicker ($request);
        $result = array();
        $quoteId = 'krw';
        $quote = $this->safe_currency_code($quoteId);
        $baseIds = is_array($response) ? array_keys($response) : array();
        for ($i = 0; $i < count($baseIds); $i++) {
            $baseId = $baseIds[$i];
            $ticker = $this->safe_value($response, $baseId, array());
            $currency = $this->safe_value($ticker, 'currency');
            if ($currency === null) {
                continue;
            }
            $base = $this->safe_currency_code($baseId);
            $result[] = array(
                'id' => $baseId,
                'symbol' => $base . '/' . $quote,
                'base' => $base,
                'quote' => $quote,
                'baseId' => $baseId,
                'quoteId' => $quoteId,
                'active' => true,
            );
        }
        return $result;
    }

    public function fetch_balance($params = array ()) {
        $this->load_markets();
        $response = $this->privatePostAccountBalance ($params);
        $result = array( 'info' => $response );
        $balances = $this->omit($response, array(
            'errorCode',
            'result',
            'normalWallets',
        ));
        $currencyIds = is_array($balances) ? array_keys($balances) : array();
        for ($i = 0; $i < count($currencyIds); $i++) {
            $currencyId = $currencyIds[$i];
            $balance = $balances[$currencyId];
            $code = $this->safe_currency_code($currencyId);
            $account = $this->account();
            $account['free'] = $this->safe_float($balance, 'avail');
            $account['total'] = $this->safe_float($balance, 'balance');
            $result[$code] = $account;
        }
        return $this->parse_balance($result);
    }

    public function fetch_order_book($symbol, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market($symbol);
        $request = array(
            'currency' => $market['id'],
            'format' => 'json',
        );
        $response = $this->publicGetOrderbook (array_merge($request, $params));
        $timestamp = $this->safe_timestamp($response, 'timestamp');
        return $this->parse_order_book($response, $timestamp, 'bid', 'ask', 'price', 'qty');
    }

    public function fetch_tickers($symbols = null, $params = array ()) {
        $this->load_markets();
        $request = array(
            'currency' => 'all',
            'format' => 'json',
        );
        $response = $this->publicGetTicker (array_merge($request, $params));
        $result = array();
        $ids = is_array($response) ? array_keys($response) : array();
        $timestamp = $this->safe_timestamp($response, 'timestamp');
        for ($i = 0; $i < count($ids); $i++) {
            $id = $ids[$i];
            $symbol = $id;
            $market = null;
            if (is_array($this->markets_by_id) && array_key_exists($id, $this->markets_by_id)) {
                $market = $this->markets_by_id[$id];
                $symbol = $market['symbol'];
                $ticker = $response[$id];
                $result[$symbol] = $this->parse_ticker($ticker, $market);
                $result[$symbol]['timestamp'] = $timestamp;
            }
        }
        return $result;
    }

    public function fetch_ticker($symbol, $params = array ()) {
        $this->load_markets();
        $market = $this->market($symbol);
        $request = array(
            'currency' => $market['id'],
            'format' => 'json',
        );
        $response = $this->publicGetTicker (array_merge($request, $params));
        return $this->parse_ticker($response, $market);
    }

    public function parse_ticker($ticker, $market = null) {
        $timestamp = $this->safe_timestamp($ticker, 'timestamp');
        $first = $this->safe_float($ticker, 'first');
        $last = $this->safe_float($ticker, 'last');
        $average = null;
        if ($first !== null && $last !== null) {
            $average = $this->sum($first, $last) / 2;
        }
        $previousClose = $this->safe_float($ticker, 'yesterday_last');
        $change = null;
        $percentage = null;
        if ($last !== null && $previousClose !== null) {
            $change = $last - $previousClose;
            if ($previousClose !== 0) {
                $percentage = $change / $previousClose * 100;
            }
        }
        $symbol = ($market !== null) ? $market['symbol'] : null;
        return array(
            'symbol' => $symbol,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601($timestamp),
            'high' => $this->safe_float($ticker, 'high'),
            'low' => $this->safe_float($ticker, 'low'),
            'bid' => null,
            'bidVolume' => null,
            'ask' => null,
            'askVolume' => null,
            'vwap' => null,
            'open' => $first,
            'close' => $last,
            'last' => $last,
            'previousClose' => $previousClose,
            'change' => $change,
            'percentage' => $percentage,
            'average' => $average,
            'baseVolume' => $this->safe_float($ticker, 'volume'),
            'quoteVolume' => null,
            'info' => $ticker,
        );
    }

    public function parse_trade($trade, $market = null) {
        $timestamp = $this->safe_timestamp($trade, 'timestamp');
        $symbol = ($market !== null) ? $market['symbol'] : null;
        $is_ask = $this->safe_string($trade, 'is_ask');
        $side = null;
        if ($is_ask === '1') {
            $side = 'sell';
        } else if ($is_ask === '0') {
            $side = 'buy';
        }
        $price = $this->safe_float($trade, 'price');
        $amount = $this->safe_float($trade, 'qty');
        $cost = null;
        if ($price !== null) {
            if ($amount !== null) {
                $cost = $price * $amount;
            }
        }
        return array(
            'id' => $this->safe_string($trade, 'id'),
            'info' => $trade,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601($timestamp),
            'order' => null,
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
            'currency' => $market['id'],
            'format' => 'json',
        );
        $response = $this->publicGetTrades (array_merge($request, $params));
        return $this->parse_trades($response['completeOrders'], $market, $since, $limit);
    }

    public function create_order($symbol, $type, $side, $amount, $price = null, $params = array ()) {
        $this->load_markets();
        if ($type !== 'limit') {
            throw new ExchangeError($this->id . ' allows limit orders only');
        }
        $this->load_markets();
        $request = array(
            'price' => $price,
            'currency' => $this->market_id($symbol),
            'qty' => $amount,
        );
        $method = 'privatePostOrder' . $this->capitalize($type) . $this->capitalize($side);
        $response = $this->$method (array_merge($request, $params));
        $id = $this->safe_string($response, 'orderId');
        if ($id !== null) {
            $id = strtoupper($id);
        }
        $timestamp = $this->milliseconds();
        $cost = $price * $amount;
        $order = array(
            'info' => $response,
            'id' => $id,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601($timestamp),
            'lastTradeTimestamp' => null,
            'symbol' => $symbol,
            'type' => $type,
            'side' => $side,
            'price' => $price,
            'cost' => $cost,
            'average' => null,
            'amount' => $amount,
            'filled' => null,
            'remaining' => $amount,
            'status' => 'open',
            'fee' => null,
            'clientOrderId' => null,
            'trades' => null,
        );
        $this->orders[$id] = $order;
        return $order;
    }

    public function fetch_order($id, $symbol = null, $params = array ()) {
        $this->load_markets();
        $result = null;
        $market = null;
        if ($symbol === null) {
            if (is_array($this->orders) && array_key_exists($id, $this->orders)) {
                $market = $this->market($this->orders[$id]['symbol']);
            } else {
                throw new ArgumentsRequired($this->id . ' fetchOrder() requires a $symbol argument for order ids missing in the .orders cache (the order was created with a different instance of this class or within a different run of this code).');
            }
        } else {
            $market = $this->market($symbol);
        }
        try {
            $request = array(
                'order_id' => $id,
                'currency' => $market['id'],
            );
            $response = $this->privatePostOrderOrderInfo (array_merge($request, $params));
            $result = $this->parse_order($response);
            $this->orders[$id] = $result;
        } catch (Exception $e) {
            if ($e instanceof OrderNotFound) {
                if (is_array($this->orders) && array_key_exists($id, $this->orders)) {
                    $this->orders[$id]['status'] = 'canceled';
                    $result = $this->orders[$id];
                } else {
                    throw $e;
                }
            } else {
                throw $e;
            }
        }
        return $result;
    }

    public function parse_order_status($status) {
        $statuses = array(
            'live' => 'open',
            'partially_filled' => 'open',
            'filled' => 'closed',
        );
        return $this->safe_string($statuses, $status, $status);
    }

    public function parse_order($order, $market = null) {
        //
        //     {
        //         "index" => "0",
        //         "orderId" => "68665943-1eb5-4e4b-9d76-845fc54f5489",
        //         "$timestamp" => "1449037367",
        //         "$price" => "444000.0",
        //         "qty" => "0.3456",
        //         "type" => "ask",
        //         "feeRate" => "-0.0015"
        //     }
        //
        $info = $this->safe_value($order, 'info');
        $id = $this->safe_string_upper($info, 'orderId');
        $timestamp = $this->safe_timestamp($info, 'timestamp');
        $status = $this->parse_order_status($this->safe_string($order, 'status'));
        $cost = null;
        $side = $this->safe_string($info, 'type');
        if (mb_strpos($side, 'ask') !== false) {
            $side = 'sell';
        } else {
            $side = 'buy';
        }
        $price = $this->safe_float($info, 'price');
        $amount = $this->safe_float($info, 'qty');
        $remaining = $this->safe_float($info, 'remainQty');
        $filled = null;
        if ($amount !== null) {
            if ($remaining !== null) {
                $filled = $amount - $remaining;
            }
            if ($price !== null) {
                $cost = $price * $amount;
            }
        }
        $currency = $this->safe_string($info, 'currency');
        $fee = array(
            'currency' => $currency,
            'cost' => $this->safe_float($info, 'fee'),
            'rate' => $this->safe_float($info, 'feeRate'),
        );
        $symbol = null;
        if ($market === null) {
            $marketId = strtolower($currency);
            if (is_array($this->markets_by_id) && array_key_exists($marketId, $this->markets_by_id)) {
                $market = $this->markets_by_id[$marketId];
            }
        }
        if ($market !== null) {
            $symbol = $market['symbol'];
        }
        return array(
            'info' => $order,
            'id' => $id,
            'clientOrderId' => null,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601($timestamp),
            'lastTradeTimestamp' => null,
            'symbol' => $symbol,
            'type' => 'limit',
            'side' => $side,
            'price' => $price,
            'cost' => $cost,
            'amount' => $amount,
            'filled' => $filled,
            'remaining' => $remaining,
            'status' => $status,
            'fee' => $fee,
            'average' => null,
            'trades' => null,
        );
    }

    public function cancel_order($id, $symbol = null, $params = array ()) {
        $this->load_markets();
        $order = $this->safe_value($this->orders, $id);
        $amount = null;
        $price = null;
        $side = null;
        if ($order === null) {
            if ($symbol === null) {
                // eslint-disable-next-line quotes
                throw new InvalidOrder($this->id . " cancelOrder could not find the $order $id " . $id . " in orders cache. The $order was probably created with a different instance of this class earlier. The `$symbol` argument is missing. To cancel the $order, pass a $symbol argument and array('price' => 12345, 'qty' => 1.2345, 'is_ask' => 0) in the $params argument of cancelOrder.");
            }
            $price = $this->safe_float($params, 'price');
            if ($price === null) {
                // eslint-disable-next-line quotes
                throw new InvalidOrder($this->id . " cancelOrder could not find the $order $id " . $id . " in orders cache. The $order was probably created with a different instance of this class earlier. The `$price` parameter is missing. To cancel the $order, pass a $symbol argument and array('price' => 12345, 'qty' => 1.2345, 'is_ask' => 0) in the $params argument of cancelOrder.");
            }
            $amount = $this->safe_float($params, 'qty');
            if ($amount === null) {
                // eslint-disable-next-line quotes
                throw new InvalidOrder($this->id . " cancelOrder could not find the $order $id " . $id . " in orders cache. The $order was probably created with a different instance of this class earlier. The `qty` ($amount) parameter is missing. To cancel the $order, pass a $symbol argument and array('price' => 12345, 'qty' => 1.2345, 'is_ask' => 0) in the $params argument of cancelOrder.");
            }
            $side = $this->safe_float($params, 'is_ask');
            if ($side === null) {
                // eslint-disable-next-line quotes
                throw new InvalidOrder($this->id . " cancelOrder could not find the $order $id " . $id . " in orders cache. The $order was probably created with a different instance of this class earlier. The `is_ask` ($side) parameter is missing. To cancel the $order, pass a $symbol argument and array('price' => 12345, 'qty' => 1.2345, 'is_ask' => 0) in the $params argument of cancelOrder.");
            }
        } else {
            $price = $order['price'];
            $amount = $order['amount'];
            $side = ($order['side'] === 'buy') ? 0 : 1;
            $symbol = $order['symbol'];
        }
        $request = array(
            'order_id' => $id,
            'price' => $price,
            'qty' => $amount,
            'is_ask' => $side,
            'currency' => $this->market_id($symbol),
        );
        $this->orders[$id]['status'] = 'canceled';
        return $this->privatePostOrderCancel (array_merge($request, $params));
    }

    public function sign($path, $api = 'public', $method = 'GET', $params = array (), $headers = null, $body = null) {
        $request = $this->implode_params($path, $params);
        $query = $this->omit($params, $this->extract_params($path));
        $url = $this->urls['api'] . '/';
        if ($api === 'public') {
            $url .= $request;
            if ($query) {
                $url .= '?' . $this->urlencode($query);
            }
        } else {
            $this->check_required_credentials();
            $url .= $this->version . '/' . $request;
            $nonce = (string) $this->nonce();
            $json = $this->json(array_merge(array(
                'access_token' => $this->apiKey,
                'nonce' => $nonce,
            ), $params));
            $payload = base64_encode($this->encode($json));
            $body = $this->decode($payload);
            $secret = strtoupper($this->secret);
            $signature = $this->hmac($payload, $this->encode($secret), 'sha512');
            $headers = array(
                'content-type' => 'application/json',
                'X-COINONE-PAYLOAD' => $payload,
                'X-COINONE-SIGNATURE' => $signature,
            );
        }
        return array( 'url' => $url, 'method' => $method, 'body' => $body, 'headers' => $headers );
    }

    public function handle_errors($code, $reason, $url, $method, $headers, $body, $response, $requestHeaders, $requestBody) {
        if ($response === null) {
            return;
        }
        if (is_array($response) && array_key_exists('result', $response)) {
            $result = $response['result'];
            if ($result !== 'success') {
                //
                //    array(  "$errorCode" => "405",  "status" => "maintenance",  "$result" => "error")
                //
                $errorCode = $this->safe_string($response, 'errorCode');
                $feedback = $this->id . ' ' . $body;
                $this->throw_exactly_matched_exception($this->exceptions, $errorCode, $feedback);
                throw new ExchangeError($feedback);
            }
        } else {
            throw new ExchangeError($this->id . ' ' . $body);
        }
    }
}
