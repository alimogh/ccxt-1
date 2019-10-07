<?php

namespace ccxt;

use Exception as Exception; // a common import

class gateio extends Exchange {

    public function describe () {
        return array_replace_recursive (parent::describe (), array (
            'id' => 'gateio',
            'name' => 'Gate.io',
            'countries' => array ( 'CN' ),
            'version' => '2',
            'rateLimit' => 1000,
            'has' => array (
                'CORS' => false,
                'createMarketOrder' => false,
                'fetchTickers' => true,
                'withdraw' => true,
                'fetchDeposits' => true,
                'fetchWithdrawals' => true,
                'fetchTransactions' => true,
                'createDepositAddress' => true,
                'fetchDepositAddress' => true,
                'fetchClosedOrders' => false,
                'fetchOHLCV' => true,
                'fetchOpenOrders' => true,
                'fetchOrderTrades' => true,
                'fetchOrders' => true,
                'fetchOrder' => true,
                'fetchMyTrades' => true,
            ),
            'timeframes' => array (
                '1m' => '60',
                '5m' => '300',
                '10m' => '600',
                '15m' => '900',
                '30m' => '1800',
                '1h' => '3600',
                '2h' => '7200',
                '4h' => '14400',
                '6h' => '21600',
                '12h' => '43200',
                '1d' => '86400',
                '1w' => '604800',
            ),
            'urls' => array (
                'logo' => 'https://user-images.githubusercontent.com/1294454/31784029-0313c702-b509-11e7-9ccc-bc0da6a0e435.jpg',
                'api' => array (
                    'public' => 'https://data.gate.io/api',
                    'private' => 'https://data.gate.io/api',
                ),
                'www' => 'https://gate.io/',
                'doc' => 'https://gate.io/api2',
                'fees' => array (
                    'https://gate.io/fee',
                    'https://support.gate.io/hc/en-us/articles/115003577673',
                ),
                'referral' => 'https://www.gate.io/signup/2436035',
            ),
            'api' => array (
                'public' => array (
                    'get' => array (
                        'candlestick2/{id}',
                        'pairs',
                        'marketinfo',
                        'marketlist',
                        'tickers',
                        'ticker/{id}',
                        'orderBook/{id}',
                        'trade/{id}',
                        'tradeHistory/{id}',
                        'tradeHistory/{id}/{tid}',
                    ),
                ),
                'private' => array (
                    'post' => array (
                        'balances',
                        'depositAddress',
                        'newAddress',
                        'depositsWithdrawals',
                        'buy',
                        'sell',
                        'cancelOrder',
                        'cancelAllOrders',
                        'getOrder',
                        'openOrders',
                        'tradeHistory',
                        'withdraw',
                    ),
                ),
            ),
            'fees' => array (
                'trading' => array (
                    'tierBased' => true,
                    'percentage' => true,
                    'maker' => 0.002,
                    'taker' => 0.002,
                ),
            ),
            'exceptions' => array (
                '4' => '\\ccxt\\DDoSProtection',
                '7' => '\\ccxt\\NotSupported',
                '8' => '\\ccxt\\NotSupported',
                '9' => '\\ccxt\\NotSupported',
                '15' => '\\ccxt\\DDoSProtection',
                '16' => '\\ccxt\\OrderNotFound',
                '17' => '\\ccxt\\OrderNotFound',
                '21' => '\\ccxt\\InsufficientFunds',
            ),
            // https://gate.io/api2#errCode
            'errorCodeNames' => array (
                '1' => 'Invalid request',
                '2' => 'Invalid version',
                '3' => 'Invalid request',
                '4' => 'Too many attempts',
                '5' => 'Invalid sign',
                '6' => 'Invalid sign',
                '7' => 'Currency is not supported',
                '8' => 'Currency is not supported',
                '9' => 'Currency is not supported',
                '10' => 'Verified failed',
                '11' => 'Obtaining address failed',
                '12' => 'Empty params',
                '13' => 'Internal error, please report to administrator',
                '14' => 'Invalid user',
                '15' => 'Cancel order too fast, please wait 1 min and try again',
                '16' => 'Invalid order id or order is already closed',
                '17' => 'Invalid orderid',
                '18' => 'Invalid amount',
                '19' => 'Not permitted or trade is disabled',
                '20' => 'Your order size is too small',
                '21' => 'You don\'t have enough fund',
            ),
            'options' => array (
                'fetchTradesMethod' => 'public_get_tradehistory_id', // 'public_get_tradehistory_id_tid'
                'limits' => array (
                    'cost' => array (
                        'min' => array (
                            'BTC' => 0.0001,
                            'ETH' => 0.001,
                            'USDT' => 1,
                        ),
                    ),
                ),
            ),
        ));
    }

    public function fetch_markets ($params = array ()) {
        $response = $this->publicGetMarketinfo ($params);
        $markets = $this->safe_value($response, 'pairs');
        if (!$markets) {
            throw new ExchangeError($this->id . ' fetchMarkets got an unrecognized response');
        }
        $result = array();
        for ($i = 0; $i < count ($markets); $i++) {
            $market = $markets[$i];
            $keys = is_array($market) ? array_keys($market) : array();
            $id = $keys[0];
            $details = $market[$id];
            // all of their symbols are separated with an underscore
            // but not boe_eth_eth (BOE_ETH/ETH) which has two underscores
            // https://github.com/ccxt/ccxt/issues/4894
            $parts = explode('_', $id);
            $numParts = is_array ($parts) ? count ($parts) : 0;
            $baseId = $parts[0];
            $quoteId = $parts[1];
            if ($numParts > 2) {
                $baseId = $parts[0] . '_' . $parts[1];
                $quoteId = $parts[2];
            }
            $base = $this->safe_currency_code($baseId);
            $quote = $this->safe_currency_code($quoteId);
            $symbol = $base . '/' . $quote;
            $precision = array (
                'amount' => 8,
                'price' => $details['decimal_places'],
            );
            $amountLimits = array (
                'min' => $details['min_amount'],
                'max' => null,
            );
            $priceLimits = array (
                'min' => pow(10, -$details['decimal_places']),
                'max' => null,
            );
            $defaultCost = $amountLimits['min'] * $priceLimits['min'];
            $minCost = $this->safe_float($this->options['limits']['cost']['min'], $quote, $defaultCost);
            $costLimits = array (
                'min' => $minCost,
                'max' => null,
            );
            $limits = array (
                'amount' => $amountLimits,
                'price' => $priceLimits,
                'cost' => $costLimits,
            );
            $active = true;
            $result[] = array (
                'id' => $id,
                'symbol' => $symbol,
                'base' => $base,
                'quote' => $quote,
                'baseId' => $baseId,
                'quoteId' => $quoteId,
                'info' => $market,
                'active' => $active,
                'maker' => $details['fee'] / 100,
                'taker' => $details['fee'] / 100,
                'precision' => $precision,
                'limits' => $limits,
            );
        }
        return $result;
    }

    public function fetch_balance ($params = array ()) {
        $this->load_markets();
        $response = $this->privatePostBalances ($params);
        $result = array( 'info' => $response );
        $available = $this->safe_value($response, 'available', array());
        if (gettype ($available) === 'array' && count (array_filter (array_keys ($available), 'is_string')) == 0) {
            $available = array();
        }
        $locked = $this->safe_value($response, 'locked', array());
        $currencyIds = is_array($available) ? array_keys($available) : array();
        for ($i = 0; $i < count ($currencyIds); $i++) {
            $currencyId = $currencyIds[$i];
            $code = $this->safe_currency_code($currencyId);
            $account = $this->account ();
            $account['free'] = $this->safe_float($available, $currencyId);
            $account['used'] = $this->safe_float($locked, $currencyId);
            $result[$code] = $account;
        }
        return $this->parse_balance($result);
    }

    public function fetch_order_book ($symbol, $limit = null, $params = array ()) {
        $this->load_markets();
        $request = array (
            'id' => $this->market_id($symbol),
        );
        $response = $this->publicGetOrderBookId (array_merge ($request, $params));
        return $this->parse_order_book($response);
    }

    public function parse_ohlcv ($ohlcv, $market = null, $timeframe = '1m', $since = null, $limit = null) {
        // they return array ( Timestamp, Volume, Close, High, Low, Open )
        return [
            intval ($ohlcv[0]),   // t
            floatval ($ohlcv[5]), // o
            floatval ($ohlcv[3]), // h
            floatval ($ohlcv[4]), // l
            floatval ($ohlcv[2]), // c
            floatval ($ohlcv[1]), // v
        ];
    }

    public function fetch_ohlcv ($symbol, $timeframe = '1m', $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array (
            'id' => $market['id'],
            'group_sec' => $this->timeframes[$timeframe],
        );
        // max $limit = 1001
        if ($limit !== null) {
            $periodDurationInSeconds = $this->parse_timeframe($timeframe);
            $hours = intval (($periodDurationInSeconds * $limit) / 3600);
            $request['range_hour'] = max (0, $hours - 1);
        }
        $response = $this->publicGetCandlestick2Id (array_merge ($request, $params));
        //
        //     {
        //         "elapsed" => "15ms",
        //         "result" => "true",
        //         "$data" => array (
        //             array ( "1553930820000", "1.005299", "4081.05", "4086.18", "4081.05", "4086.18" ),
        //             array ( "1553930880000", "0.110923277", "4095.2", "4095.23", "4091.15", "4091.15" ),
        //             ...
        //             array ( "1553934420000", "0", "4089.42", "4089.42", "4089.42", "4089.42" ),
        //         )
        //     }
        //
        $data = $this->safe_value($response, 'data', array());
        return $this->parse_ohlcvs($data, $market, $timeframe, $since, $limit);
    }

    public function parse_ticker ($ticker, $market = null) {
        $timestamp = $this->milliseconds ();
        $symbol = null;
        if ($market) {
            $symbol = $market['symbol'];
        }
        $last = $this->safe_float($ticker, 'last');
        $percentage = $this->safe_float($ticker, 'percentChange');
        $open = null;
        $change = null;
        $average = null;
        if (($last !== null) && ($percentage !== null)) {
            $relativeChange = $percentage / 100;
            $open = $last / $this->sum (1, $relativeChange);
            $change = $last - $open;
            $average = $this->sum ($last, $open) / 2;
        }
        return array (
            'symbol' => $symbol,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'high' => $this->safe_float($ticker, 'high24hr'),
            'low' => $this->safe_float($ticker, 'low24hr'),
            'bid' => $this->safe_float($ticker, 'highestBid'),
            'bidVolume' => null,
            'ask' => $this->safe_float($ticker, 'lowestAsk'),
            'askVolume' => null,
            'vwap' => null,
            'open' => $open,
            'close' => $last,
            'last' => $last,
            'previousClose' => null,
            'change' => $change,
            'percentage' => $percentage,
            'average' => $average,
            'baseVolume' => $this->safe_float($ticker, 'quoteVolume'), // gateio has them reversed
            'quoteVolume' => $this->safe_float($ticker, 'baseVolume'),
            'info' => $ticker,
        );
    }

    public function handle_errors ($code, $reason, $url, $method, $headers, $body, $response, $requestHeaders, $requestBody) {
        if ($response === null) {
            return;
        }
        $resultString = $this->safe_string($response, 'result', '');
        if ($resultString !== 'false') {
            return;
        }
        $errorCode = $this->safe_string($response, 'code');
        if ($errorCode !== null) {
            $exceptions = $this->exceptions;
            $errorCodeNames = $this->errorCodeNames;
            if (is_array($exceptions) && array_key_exists($errorCode, $exceptions)) {
                $message = '';
                if (is_array($errorCodeNames) && array_key_exists($errorCode, $errorCodeNames)) {
                    $message = $errorCodeNames[$errorCode];
                } else {
                    $message = $this->safe_string($response, 'message', '(unknown)');
                }
                throw new $exceptions[$errorCode]($message);
            }
        }
    }

    public function fetch_tickers ($symbols = null, $params = array ()) {
        $this->load_markets();
        $response = $this->publicGetTickers ($params);
        $result = array();
        $ids = is_array($response) ? array_keys($response) : array();
        for ($i = 0; $i < count ($ids); $i++) {
            $id = $ids[$i];
            list($baseId, $quoteId) = explode('_', $id);
            $base = strtoupper($baseId);
            $quote = strtoupper($quoteId);
            $base = $this->safe_currency_code($base);
            $quote = $this->safe_currency_code($quote);
            $symbol = $base . '/' . $quote;
            $market = null;
            if (is_array($this->markets) && array_key_exists($symbol, $this->markets)) {
                $market = $this->markets[$symbol];
            }
            if (is_array($this->markets_by_id) && array_key_exists($id, $this->markets_by_id)) {
                $market = $this->markets_by_id[$id];
            }
            $result[$symbol] = $this->parse_ticker($response[$id], $market);
        }
        return $result;
    }

    public function fetch_ticker ($symbol, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $ticker = $this->publicGetTickerId (array_merge (array (
            'id' => $market['id'],
        ), $params));
        return $this->parse_ticker($ticker, $market);
    }

    public function parse_trade ($trade, $market = null) {
        $timestamp = $this->safe_timestamp_2($trade, 'timestamp', 'time_unix');
        $id = $this->safe_string_2($trade, 'tradeID', 'id');
        // take either of orderid or $orderId
        $orderId = $this->safe_string_2($trade, 'orderid', 'orderNumber');
        $price = $this->safe_float($trade, 'rate');
        $amount = $this->safe_float($trade, 'amount');
        $type = $this->safe_string($trade, 'type');
        $cost = null;
        if ($price !== null) {
            if ($amount !== null) {
                $cost = $price * $amount;
            }
        }
        $symbol = null;
        if ($market !== null) {
            $symbol = $market['symbol'];
        }
        return array (
            'id' => $id,
            'info' => $trade,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'symbol' => $symbol,
            'order' => $orderId,
            'type' => null,
            'side' => $type,
            'takerOrMaker' => null,
            'price' => $price,
            'amount' => $amount,
            'cost' => $cost,
            'fee' => null,
        );
    }

    public function fetch_trades ($symbol, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array (
            'id' => $market['id'],
        );
        $method = $this->safe_string($this->options, 'fetchTradesMethod', 'public_get_tradehistory_id');
        $response = $this->$method (array_merge ($request, $params));
        return $this->parse_trades($response['data'], $market, $since, $limit);
    }

    public function fetch_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        $response = $this->privatePostOpenOrders ($params);
        return $this->parse_orders($response['orders'], null, $since, $limit);
    }

    public function fetch_order ($id, $symbol = null, $params = array ()) {
        $this->load_markets();
        $request = array (
            'orderNumber' => $id,
            'currencyPair' => $this->market_id($symbol),
        );
        $response = $this->privatePostGetOrder (array_merge ($request, $params));
        return $this->parse_order($response['order']);
    }

    public function parse_order_status ($status) {
        $statuses = array (
            'cancelled' => 'canceled',
            // 'closed' => 'closed', // these two $statuses aren't actually needed
            // 'open' => 'open', // as they are mapped one-to-one
        );
        return $this->safe_string($statuses, $status, $status);
    }

    public function parse_order ($order, $market = null) {
        //
        //    array ('amount' => '0.00000000',
        //     'currencyPair' => 'xlm_usdt',
        //     'fee' => '0.0113766632239302 USDT',
        //     'feeCurrency' => 'USDT',
        //     'feePercentage' => 0.18,
        //     'feeValue' => '0.0113766632239302',
        //     'filledAmount' => '30.14004987',
        //     'filledRate' => 0.2097,
        //     'initialAmount' => '30.14004987',
        //     'initialRate' => '0.2097',
        //     'left' => 0,
        //     'orderNumber' => '998307286',
        //     'rate' => '0.2097',
        //     'status' => 'closed',
        //     'timestamp' => 1531158583,
        //     'type' => 'sell'),
        //
        $id = $this->safe_string($order, 'orderNumber');
        $symbol = null;
        $marketId = $this->safe_string($order, 'currencyPair');
        if (is_array($this->markets_by_id) && array_key_exists($marketId, $this->markets_by_id)) {
            $market = $this->markets_by_id[$marketId];
        }
        if ($market !== null) {
            $symbol = $market['symbol'];
        }
        $timestamp = $this->safe_timestamp($order, 'timestamp');
        $status = $this->parse_order_status($this->safe_string($order, 'status'));
        $side = $this->safe_string($order, 'type');
        $price = $this->safe_float($order, 'filledRate');
        $amount = $this->safe_float($order, 'initialAmount');
        $filled = $this->safe_float($order, 'filledAmount');
        // In the $order $status response, this field has a different name.
        $remaining = $this->safe_float_2($order, 'leftAmount', 'left');
        $feeCost = $this->safe_float($order, 'feeValue');
        $feeCurrencyId = $this->safe_string($order, 'feeCurrency');
        $feeCurrencyCode = $this->safe_currency_code($feeCurrencyId);
        $feeRate = $this->safe_float($order, 'feePercentage');
        if ($feeRate !== null) {
            $feeRate = $feeRate / 100;
        }
        return array (
            'id' => $id,
            'datetime' => $this->iso8601 ($timestamp),
            'timestamp' => $timestamp,
            'status' => $status,
            'symbol' => $symbol,
            'type' => 'limit',
            'side' => $side,
            'price' => $price,
            'cost' => null,
            'amount' => $amount,
            'filled' => $filled,
            'remaining' => $remaining,
            'trades' => null,
            'fee' => array (
                'cost' => $feeCost,
                'currency' => $feeCurrencyCode,
                'rate' => $feeRate,
            ),
            'info' => $order,
        );
    }

    public function create_order ($symbol, $type, $side, $amount, $price = null, $params = array ()) {
        if ($type === 'market') {
            throw new ExchangeError($this->id . ' allows limit orders only');
        }
        $this->load_markets();
        $method = 'privatePost' . $this->capitalize ($side);
        $market = $this->market ($symbol);
        $request = array (
            'currencyPair' => $market['id'],
            'rate' => $price,
            'amount' => $amount,
        );
        $response = $this->$method (array_merge ($request, $params));
        return $this->parse_order(array_merge (array (
            'status' => 'open',
            'type' => $side,
            'initialAmount' => $amount,
        ), $response), $market);
    }

    public function cancel_order ($id, $symbol = null, $params = array ()) {
        if ($symbol === null) {
            throw new ArgumentsRequired($this->id . ' cancelOrder requires $symbol argument');
        }
        $this->load_markets();
        $request = array (
            'orderNumber' => $id,
            'currencyPair' => $this->market_id($symbol),
        );
        return $this->privatePostCancelOrder (array_merge ($request, $params));
    }

    public function query_deposit_address ($method, $code, $params = array ()) {
        $this->load_markets();
        $currency = $this->currency ($code);
        $method = 'privatePost' . $method . 'Address';
        $request = array (
            'currency' => $currency['id'],
        );
        $response = $this->$method (array_merge ($request, $params));
        $address = $this->safe_string($response, 'addr');
        $tag = null;
        if (($address !== null) && (mb_strpos($address, 'address') !== false)) {
            throw new InvalidAddress($this->id . ' queryDepositAddress ' . $address);
        }
        if ($code === 'XRP') {
            $parts = explode(' ', $address);
            $address = $parts[0];
            $tag = $parts[1];
        }
        return array (
            'currency' => $currency,
            'address' => $address,
            'tag' => $tag,
            'info' => $response,
        );
    }

    public function create_deposit_address ($code, $params = array ()) {
        return $this->query_deposit_address ('New', $code, $params);
    }

    public function fetch_deposit_address ($code, $params = array ()) {
        return $this->query_deposit_address ('Deposit', $code, $params);
    }

    public function fetch_open_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = null;
        if ($symbol !== null) {
            $market = $this->market ($symbol);
        }
        $response = $this->privatePostOpenOrders ($params);
        return $this->parse_orders($response['orders'], $market, $since, $limit);
    }

    public function fetch_order_trades ($id, $symbol = null, $since = null, $limit = null, $params = array ()) {
        if ($symbol === null) {
            throw new ArgumentsRequired($this->id . ' fetchMyTrades requires a $symbol argument');
        }
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array (
            'currencyPair' => $market['id'],
            'orderNumber' => $id,
        );
        $response = $this->privatePostTradeHistory (array_merge ($request, $params));
        return $this->parse_trades($response['trades'], $market, $since, $limit);
    }

    public function fetch_my_trades ($symbol = null, $since = null, $limit = null, $params = array ()) {
        if ($symbol === null) {
            throw new ArgumentsRequired($this->id . ' fetchMyTrades requires $symbol param');
        }
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array (
            'currencyPair' => $market['id'],
        );
        $response = $this->privatePostTradeHistory (array_merge ($request, $params));
        return $this->parse_trades($response['trades'], $market, $since, $limit);
    }

    public function withdraw ($code, $amount, $address, $tag = null, $params = array ()) {
        $this->check_address($address);
        $this->load_markets();
        $currency = $this->currency ($code);
        $request = array (
            'currency' => $currency['id'],
            'amount' => $amount,
            'address' => $address, // Address must exist in you AddressBook in security settings
        );
        if ($tag !== null) {
            $request['address'] .= ' ' . $tag;
        }
        $response = $this->privatePostWithdraw (array_merge ($request, $params));
        return array (
            'info' => $response,
            'id' => null,
        );
    }

    public function sign ($path, $api = 'public', $method = 'GET', $params = array (), $headers = null, $body = null) {
        $prefix = ($api === 'private') ? ($api . '/') : '';
        $url = $this->urls['api'][$api] . $this->version . '/1/' . $prefix . $this->implode_params($path, $params);
        $query = $this->omit ($params, $this->extract_params($path));
        if ($api === 'public') {
            if ($query) {
                $url .= '?' . $this->urlencode ($query);
            }
        } else {
            $this->check_required_credentials();
            $nonce = $this->nonce ();
            $request = array( 'nonce' => $nonce );
            $body = $this->urlencode (array_merge ($request, $query));
            $signature = $this->hmac ($this->encode ($body), $this->encode ($this->secret), 'sha512');
            $headers = array (
                'Key' => $this->apiKey,
                'Sign' => $signature,
                'Content-Type' => 'application/x-www-form-urlencoded',
            );
        }
        return array( 'url' => $url, 'method' => $method, 'body' => $body, 'headers' => $headers );
    }

    public function fetch_transactions_by_type ($type = null, $code = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $request = array();
        if ($since !== null) {
            $request['start'] = $since;
        }
        $response = $this->privatePostDepositsWithdrawals (array_merge ($request, $params));
        $transactions = null;
        if ($type === null) {
            $deposits = $this->safe_value($response, 'deposits', array());
            $withdrawals = $this->safe_value($response, 'withdraws', array());
            $transactions = $this->array_concat($deposits, $withdrawals);
        } else {
            $transactions = $this->safe_value($response, $type, array());
        }
        $currency = null;
        if ($code !== null) {
            $currency = $this->currency ($code);
        }
        return $this->parse_transactions($transactions, $currency, $since, $limit);
    }

    public function fetch_transactions ($code = null, $since = null, $limit = null, $params = array ()) {
        return $this->fetch_transactions_by_type (null, $code, $since, $limit, $params);
    }

    public function fetch_deposits ($code = null, $since = null, $limit = null, $params = array ()) {
        return $this->fetch_transactions_by_type ('deposits', $code, $since, $limit, $params);
    }

    public function fetch_withdrawals ($code = null, $since = null, $limit = null, $params = array ()) {
        return $this->fetch_transactions_by_type ('withdraws', $code, $since, $limit, $params);
    }

    public function parse_transaction ($transaction, $currency = null) {
        //
        // deposit
        //
        //     {
        //         'id' => 'd16520849',
        //         'currency' => 'NEO',
        //         'address' => False,
        //         'amount' => '1',
        //         'txid' => '01acf6b8ce4d24a....',
        //         'timestamp' => '1553125968',
        //         'status' => 'DONE',
        //         'type' => 'deposit'
        //     }
        //
        // withdrawal
        //
        //     {
        //         'id' => 'w5864259',
        //         'currency' => 'ETH',
        //         'address' => '0x72632f462....',
        //         'amount' => '0.4947',
        //         'txid' => '0x111167d120f736....',
        //         'timestamp' => '1553123688',
        //         'status' => 'DONE',
        //         'type' => 'withdrawal'
        //     }
        //
        $currencyId = $this->safe_string($transaction, 'currency');
        $code = $this->safe_currency_code($currencyId, $currency);
        $id = $this->safe_string($transaction, 'id');
        $txid = $this->safe_string($transaction, 'txid');
        $amount = $this->safe_float($transaction, 'amount');
        $address = $this->safe_string($transaction, 'address');
        $timestamp = $this->safe_timestamp($transaction, 'timestamp');
        $status = $this->parse_transaction_status ($this->safe_string($transaction, 'status'));
        $type = $this->parse_transaction_type ($id[0]);
        return array (
            'info' => $transaction,
            'id' => $id,
            'txid' => $txid,
            'currency' => $code,
            'amount' => $amount,
            'address' => $address,
            'tag' => null,
            'status' => $status,
            'type' => $type,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'fee' => null,
        );
    }

    public function parse_transaction_status ($status) {
        $statuses = array (
            'PEND' => 'pending',
            'REQUEST' => 'pending',
            'CANCEL' => 'failed',
            'DONE' => 'ok',
        );
        return $this->safe_string($statuses, $status, $status);
    }

    public function parse_transaction_type ($type) {
        $types = array (
            'd' => 'deposit',
            'w' => 'withdrawal',
        );
        return $this->safe_string($types, $type, $type);
    }

    public function request ($path, $api = 'public', $method = 'GET', $params = array (), $headers = null, $body = null) {
        $response = $this->fetch2 ($path, $api, $method, $params, $headers, $body);
        if (is_array($response) && array_key_exists('result', $response)) {
            $result = $response['result'];
            $message = $this->id . ' ' . $this->json ($response);
            if ($result === null) {
                throw new ExchangeError($message);
            }
            if (gettype ($result) === 'string') {
                if ($result !== 'true') {
                    throw new ExchangeError($message);
                }
            } else if (!$result) {
                throw new ExchangeError($message);
            }
        }
        return $response;
    }
}
