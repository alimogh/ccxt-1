<?php

namespace ccxt;

use Exception as Exception; // a common import

class kucoin extends Exchange {

    public function describe () {
        return array_replace_recursive (parent::describe (), array (
            'id' => 'kucoin',
            'name' => 'KuCoin',
            'countries' => array ( 'SC' ),
            'rateLimit' => 334,
            'version' => 'v2',
            'certified' => true,
            'comment' => 'Platform 2.0',
            'has' => array (
                'fetchMarkets' => true,
                'fetchCurrencies' => true,
                'fetchTicker' => true,
                'fetchTickers' => true,
                'fetchOrderBook' => true,
                'fetchOrder' => true,
                'fetchClosedOrders' => true,
                'fetchOpenOrders' => true,
                'fetchDepositAddress' => true,
                'createDepositAddress' => true,
                'withdraw' => true,
                'fetchDeposits' => true,
                'fetchWithdrawals' => true,
                'fetchBalance' => true,
                'fetchTrades' => true,
                'fetchMyTrades' => true,
                'createOrder' => true,
                'cancelOrder' => true,
                'fetchAccounts' => true,
                'fetchFundingFee' => true,
                'fetchOHLCV' => true,
                'fetchLedger' => true,
            ),
            'urls' => array (
                'logo' => 'https://user-images.githubusercontent.com/1294454/57369448-3cc3aa80-7196-11e9-883e-5ebeb35e4f57.jpg',
                'referral' => 'https://www.kucoin.com/?rcode=E5wkqe',
                'api' => array (
                    'public' => 'https://openapi-v2.kucoin.com',
                    'private' => 'https://openapi-v2.kucoin.com',
                ),
                'test' => array (
                    'public' => 'https://openapi-sandbox.kucoin.com',
                    'private' => 'https://openapi-sandbox.kucoin.com',
                ),
                'www' => 'https://www.kucoin.com',
                'doc' => array (
                    'https://docs.kucoin.com',
                ),
            ),
            'requiredCredentials' => array (
                'apiKey' => true,
                'secret' => true,
                'password' => true,
            ),
            'api' => array (
                'public' => array (
                    'get' => array (
                        'timestamp',
                        'symbols',
                        'market/allTickers',
                        'market/orderbook/level{level}',
                        'market/histories',
                        'market/candles',
                        'market/stats',
                        'currencies',
                        'currencies/{currency}',
                    ),
                    'post' => array (
                        'bullet-public',
                    ),
                ),
                'private' => array (
                    'get' => array (
                        'accounts',
                        'accounts/{accountId}',
                        'accounts/{accountId}/ledgers',
                        'accounts/{accountId}/holds',
                        'deposit-addresses',
                        'deposits',
                        'hist-deposits',
                        'hist-orders',
                        'hist-withdrawals',
                        'withdrawals',
                        'withdrawals/quotas',
                        'orders',
                        'orders/{orderId}',
                        'fills',
                        'limit/fills',
                    ),
                    'post' => array (
                        'accounts',
                        'accounts/inner-transfer',
                        'deposit-addresses',
                        'withdrawals',
                        'orders',
                        'bullet-private',
                    ),
                    'delete' => array (
                        'withdrawals/{withdrawalId}',
                        'orders/{orderId}',
                    ),
                ),
            ),
            'timeframes' => array (
                '1m' => '1min',
                '3m' => '3min',
                '5m' => '5min',
                '15m' => '15min',
                '30m' => '30min',
                '1h' => '1hour',
                '2h' => '2hour',
                '4h' => '4hour',
                '6h' => '6hour',
                '8h' => '8hour',
                '12h' => '12hour',
                '1d' => '1day',
                '1w' => '1week',
            ),
            'exceptions' => array (
                'order_not_exist' => '\\ccxt\\OrderNotFound', // array("code":"order_not_exist","msg":"order_not_exist") ¯\_(ツ)_/¯
                'order_not_exist_or_not_allow_to_cancel' => '\\ccxt\\InvalidOrder', // array("code":"400100","msg":"order_not_exist_or_not_allow_to_cancel")
                'Order size below the minimum requirement.' => '\\ccxt\\InvalidOrder', // array("code":"400100","msg":"Order size below the minimum requirement.")
                'The withdrawal amount is below the minimum requirement.' => '\\ccxt\\ExchangeError', // array("code":"400100","msg":"The withdrawal amount is below the minimum requirement.")
                '400' => '\\ccxt\\BadRequest',
                '401' => '\\ccxt\\AuthenticationError',
                '403' => '\\ccxt\\NotSupported',
                '404' => '\\ccxt\\NotSupported',
                '405' => '\\ccxt\\NotSupported',
                '429' => '\\ccxt\\DDoSProtection',
                '500' => '\\ccxt\\ExchangeError',
                '503' => '\\ccxt\\ExchangeNotAvailable',
                '200004' => '\\ccxt\\InsufficientFunds',
                '230003' => '\\ccxt\\InsufficientFunds', // array("code":"230003","msg":"Balance insufficient!")
                '260100' => '\\ccxt\\InsufficientFunds', // array("code":"260100","msg":"account.noBalance")
                '300000' => '\\ccxt\\InvalidOrder',
                '400000' => '\\ccxt\\BadSymbol',
                '400001' => '\\ccxt\\AuthenticationError',
                '400002' => '\\ccxt\\InvalidNonce',
                '400003' => '\\ccxt\\AuthenticationError',
                '400004' => '\\ccxt\\AuthenticationError',
                '400005' => '\\ccxt\\AuthenticationError',
                '400006' => '\\ccxt\\AuthenticationError',
                '400007' => '\\ccxt\\AuthenticationError',
                '400008' => '\\ccxt\\NotSupported',
                '400100' => '\\ccxt\\BadRequest',
                '411100' => '\\ccxt\\AccountSuspended',
                '415000' => '\\ccxt\\BadRequest', // array("code":"415000","msg":"Unsupported Media Type")
                '500000' => '\\ccxt\\ExchangeError',
            ),
            'fees' => array (
                'trading' => array (
                    'tierBased' => false,
                    'percentage' => true,
                    'taker' => 0.001,
                    'maker' => 0.001,
                ),
                'funding' => array (
                    'tierBased' => false,
                    'percentage' => false,
                    'withdraw' => array(),
                    'deposit' => array(),
                ),
            ),
            'commonCurrencies' => array (
                'HOT' => 'HOTNOW',
                'EDGE' => 'DADI', // https://github.com/ccxt/ccxt/issues/5756
            ),
            'options' => array (
                'version' => 'v1',
                'symbolSeparator' => '-',
                'fetchMyTradesMethod' => 'private_get_fills',
                'fetchBalance' => array (
                    'type' => 'trade', // or 'main'
                ),
            ),
        ));
    }

    public function nonce () {
        return $this->milliseconds ();
    }

    public function load_time_difference () {
        $response = $this->publicGetTimestamp ();
        $after = $this->milliseconds ();
        $kucoinTime = $this->safe_integer($response, 'data');
        $this->options['timeDifference'] = intval ($after - $kucoinTime);
        return $this->options['timeDifference'];
    }

    public function fetch_markets ($params = array ()) {
        $response = $this->publicGetSymbols ($params);
        //
        // { quoteCurrency => 'BTC',
        //   $symbol => 'KCS-BTC',
        //   $quoteMaxSize => '9999999',
        //   $quoteIncrement => '0.000001',
        //   $baseMinSize => '0.01',
        //   $quoteMinSize => '0.00001',
        //   enableTrading => true,
        //   priceIncrement => '0.00000001',
        //   name => 'KCS-BTC',
        //   baseIncrement => '0.01',
        //   $baseMaxSize => '9999999',
        //   baseCurrency => 'KCS' }
        //
        $data = $response['data'];
        $result = array();
        for ($i = 0; $i < count ($data); $i++) {
            $market = $data[$i];
            $id = $this->safe_string($market, 'symbol');
            $baseId = $this->safe_string($market, 'baseCurrency');
            $quoteId = $this->safe_string($market, 'quoteCurrency');
            $base = $this->safe_currency_code($baseId);
            $quote = $this->safe_currency_code($quoteId);
            $symbol = $base . '/' . $quote;
            $active = $this->safe_value($market, 'enableTrading');
            $baseMaxSize = $this->safe_float($market, 'baseMaxSize');
            $baseMinSize = $this->safe_float($market, 'baseMinSize');
            $quoteMaxSize = $this->safe_float($market, 'quoteMaxSize');
            $quoteMinSize = $this->safe_float($market, 'quoteMinSize');
            // $quoteIncrement = $this->safe_float($market, 'quoteIncrement');
            $precision = array (
                'amount' => $this->precision_from_string($this->safe_string($market, 'baseIncrement')),
                'price' => $this->precision_from_string($this->safe_string($market, 'priceIncrement')),
            );
            $limits = array (
                'amount' => array (
                    'min' => $baseMinSize,
                    'max' => $baseMaxSize,
                ),
                'price' => array (
                    'min' => $this->safe_float($market, 'priceIncrement'),
                    'max' => $quoteMaxSize / $baseMinSize,
                ),
                'cost' => array (
                    'min' => $quoteMinSize,
                    'max' => $quoteMaxSize,
                ),
            );
            $result[] = array (
                'id' => $id,
                'symbol' => $symbol,
                'baseId' => $baseId,
                'quoteId' => $quoteId,
                'base' => $base,
                'quote' => $quote,
                'active' => $active,
                'precision' => $precision,
                'limits' => $limits,
                'info' => $market,
            );
        }
        return $result;
    }

    public function fetch_currencies ($params = array ()) {
        $response = $this->publicGetCurrencies ($params);
        //
        //     {
        //         $precision => 10,
        //         $name => 'KCS',
        //         fullName => 'KCS shares',
        //         currency => 'KCS'
        //     }
        //
        $responseData = $response['data'];
        $result = array();
        for ($i = 0; $i < count ($responseData); $i++) {
            $entry = $responseData[$i];
            $id = $this->safe_string($entry, 'currency');
            $name = $this->safe_string($entry, 'fullName');
            $code = $this->safe_currency_code($id);
            $precision = $this->safe_integer($entry, 'precision');
            $result[$code] = array (
                'id' => $id,
                'name' => $name,
                'code' => $code,
                'precision' => $precision,
                'info' => $entry,
            );
        }
        return $result;
    }

    public function fetch_accounts ($params = array ()) {
        $response = $this->privateGetAccounts ($params);
        //
        //     { $code =>   "200000",
        //       $data => array ( array (   balance => "0.00009788",
        //                 available => "0.00009788",
        //                     holds => "0",
        //                  currency => "BTC",
        //                        id => "5c6a4fd399a1d81c4f9cc4d0",
        //                      $type => "trade"                     ),
        //               ...,
        //               {   balance => "0.00000001",
        //                 available => "0.00000001",
        //                     holds => "0",
        //                  currency => "ETH",
        //                        id => "5c6a49ec99a1d819392e8e9f",
        //                      $type => "trade"                     }  ) }
        //
        $data = $this->safe_value($response, 'data');
        $result = array();
        for ($i = 0; $i < count ($data); $i++) {
            $account = $data[$i];
            $accountId = $this->safe_string($account, 'id');
            $currencyId = $this->safe_string($account, 'currency');
            $code = $this->safe_currency_code($currencyId);
            $type = $this->safe_string($account, 'type');  // main or trade
            $result[] = array (
                'id' => $accountId,
                'type' => $type,
                'currency' => $code,
                'info' => $account,
            );
        }
        return $result;
    }

    public function fetch_funding_fee ($code, $params = array ()) {
        $currencyId = $this->currencyId ($code);
        $request = array (
            'currency' => $currencyId,
        );
        $response = $this->privateGetWithdrawalsQuotas (array_merge ($request, $params));
        $data = $response['data'];
        $withdrawFees = array();
        $withdrawFees[$code] = $this->safe_float($data, 'withdrawMinFee');
        return array (
            'info' => $response,
            'withdraw' => $withdrawFees,
            'deposit' => array(),
        );
    }

    public function parse_ticker ($ticker, $market = null) {
        //
        //     {
        //         'buy' => '0.00001168',
        //         'changePrice' => '-0.00000018',
        //         'changeRate' => '-0.0151',
        //         'datetime' => 1550661146316,
        //         'high' => '0.0000123',
        //         'last' => '0.00001169',
        //         'low' => '0.00001159',
        //         'sell' => '0.00001182',
        //         'symbol' => 'LOOM-BTC',
        //         'vol' => '44399.5669'
        //     }
        //
        $percentage = $this->safe_float($ticker, 'changeRate');
        if ($percentage !== null) {
            $percentage = $percentage * 100;
        }
        $last = $this->safe_float($ticker, 'last');
        $symbol = null;
        $marketId = $this->safe_string($ticker, 'symbol');
        if ($marketId !== null) {
            if (is_array($this->markets_by_id) && array_key_exists($marketId, $this->markets_by_id)) {
                $market = $this->markets_by_id[$marketId];
                $symbol = $market['symbol'];
            } else {
                list($baseId, $quoteId) = explode('-', $marketId);
                $base = $this->safe_currency_code($baseId);
                $quote = $this->safe_currency_code($quoteId);
                $symbol = $base . '/' . $quote;
            }
        }
        if ($symbol === null) {
            if ($market !== null) {
                $symbol = $market['symbol'];
            }
        }
        return array (
            'symbol' => $symbol,
            'timestamp' => null,
            'datetime' => null,
            'high' => $this->safe_float($ticker, 'high'),
            'low' => $this->safe_float($ticker, 'low'),
            'bid' => $this->safe_float($ticker, 'buy'),
            'bidVolume' => null,
            'ask' => $this->safe_float($ticker, 'sell'),
            'askVolume' => null,
            'vwap' => null,
            'open' => $this->safe_float($ticker, 'open'),
            'close' => $last,
            'last' => $last,
            'previousClose' => null,
            'change' => $this->safe_float($ticker, 'changePrice'),
            'percentage' => $percentage,
            'average' => null,
            'baseVolume' => $this->safe_float($ticker, 'vol'),
            'quoteVolume' => $this->safe_float($ticker, 'volValue'),
            'info' => $ticker,
        );
    }

    public function fetch_tickers ($symbols = null, $params = array ()) {
        $this->load_markets();
        $response = $this->publicGetMarketAllTickers ($params);
        //
        //     {
        //         "code" => "200000",
        //         "$data" => array (
        //             "date" => 1550661940645,
        //             "$ticker" => array (
        //                 'buy' => '0.00001168',
        //                 'changePrice' => '-0.00000018',
        //                 'changeRate' => '-0.0151',
        //                 'datetime' => 1550661146316,
        //                 'high' => '0.0000123',
        //                 'last' => '0.00001169',
        //                 'low' => '0.00001159',
        //                 'sell' => '0.00001182',
        //                 'symbol' => 'LOOM-BTC',
        //                 'vol' => '44399.5669'
        //             ),
        //         )
        //     }
        //
        $data = $this->safe_value($response, 'data', array());
        $tickers = $this->safe_value($data, 'ticker', array());
        $result = array();
        for ($i = 0; $i < count ($tickers); $i++) {
            $ticker = $this->parse_ticker($tickers[$i]);
            $symbol = $this->safe_string($ticker, 'symbol');
            if ($symbol !== null) {
                $result[$symbol] = $ticker;
            }
        }
        return $result;
    }

    public function fetch_ticker ($symbol, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array (
            'symbol' => $market['id'],
        );
        $response = $this->publicGetMarketStats (array_merge ($request, $params));
        //
        //     {
        //         "code" => "200000",
        //         "data" => array (
        //             'buy' => '0.00001168',
        //             'changePrice' => '-0.00000018',
        //             'changeRate' => '-0.0151',
        //             'datetime' => 1550661146316,
        //             'high' => '0.0000123',
        //             'last' => '0.00001169',
        //             'low' => '0.00001159',
        //             'sell' => '0.00001182',
        //             'symbol' => 'LOOM-BTC',
        //             'vol' => '44399.5669'
        //         ),
        //     }
        //
        return $this->parse_ticker($response['data'], $market);
    }

    public function parse_ohlcv ($ohlcv, $market = null, $timeframe = '1m', $since = null, $limit = null) {
        //
        //     array (
        //         "1545904980",             // Start time of the candle cycle
        //         "0.058",                  // opening price
        //         "0.049",                  // closing price
        //         "0.058",                  // highest price
        //         "0.049",                  // lowest price
        //         "0.018",                  // base volume
        //         "0.000945",               // quote volume
        //     )
        //
        return [
            intval ($ohlcv[0]) * 1000,
            floatval ($ohlcv[1]),
            floatval ($ohlcv[3]),
            floatval ($ohlcv[4]),
            floatval ($ohlcv[2]),
            floatval ($ohlcv[5]),
        ];
    }

    public function fetch_ohlcv ($symbol, $timeframe = '15m', $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $marketId = $market['id'];
        $request = array (
            'symbol' => $marketId,
            'type' => $this->timeframes[$timeframe],
        );
        $duration = $this->parse_timeframe($timeframe) * 1000;
        $endAt = $this->milliseconds (); // required param
        if ($since !== null) {
            $request['startAt'] = intval ((int) floor($since / 1000));
            if ($limit === null) {
                // https://docs.kucoin.com/#get-klines
                // https://docs.kucoin.com/#details
                // For each query, the system would return at most 1500 pieces of data.
                // To obtain more data, please page the data by time.
                $limit = $this->safe_integer($this->options, 'fetchOHLCVLimit', 1500);
            }
            $endAt = $this->sum ($since, $limit * $duration);
        } else if ($limit !== null) {
            $since = $endAt - $limit * $duration;
            $request['startAt'] = intval ((int) floor($since / 1000));
        }
        $request['endAt'] = intval ((int) floor($endAt / 1000));
        $response = $this->publicGetMarketCandles (array_merge ($request, $params));
        $responseData = $this->safe_value($response, 'data', array());
        return $this->parse_ohlcvs($responseData, $market, $timeframe, $since, $limit);
    }

    public function create_deposit_address ($code, $params = array ()) {
        $this->load_markets();
        $currencyId = $this->currencyId ($code);
        $request = array( 'currency' => $currencyId );
        $response = $this->privatePostDepositAddresses (array_merge ($request, $params));
        // BCH array("$code":"200000","$data":array("$address":"bitcoincash:qza3m4nj9rx7l9r0cdadfqxts6f92shvhvr5ls4q7z","memo":""))
        // BTC array("$code":"200000","$data":array("$address":"36SjucKqQpQSvsak9A7h6qzFjrVXpRNZhE","memo":""))
        $data = $this->safe_value($response, 'data', array());
        $address = $this->safe_string($data, 'address');
        // BCH/BSV is returned with a "bitcoincash:" prefix, which we cut off here and only keep the $address
        if ($address !== null) {
            $address = str_replace('bitcoincash:', '', $address);
        }
        $tag = $this->safe_string($data, 'memo');
        $this->check_address($address);
        return array (
            'info' => $response,
            'currency' => $code,
            'address' => $address,
            'tag' => $tag,
        );
    }

    public function fetch_deposit_address ($code, $params = array ()) {
        $this->load_markets();
        $currencyId = $this->currencyId ($code);
        $request = array( 'currency' => $currencyId );
        $response = $this->privateGetDepositAddresses (array_merge ($request, $params));
        // BCH array("$code":"200000","$data":array("$address":"bitcoincash:qza3m4nj9rx7l9r0cdadfqxts6f92shvhvr5ls4q7z","memo":""))
        // BTC array("$code":"200000","$data":array("$address":"36SjucKqQpQSvsak9A7h6qzFjrVXpRNZhE","memo":""))
        $data = $this->safe_value($response, 'data', array());
        $address = $this->safe_string($data, 'address');
        // BCH/BSV is returned with a "bitcoincash:" prefix, which we cut off here and only keep the $address
        if ($address !== null) {
            $address = str_replace('bitcoincash:', '', $address);
        }
        $tag = $this->safe_string($data, 'memo');
        $this->check_address($address);
        return array (
            'info' => $response,
            'currency' => $code,
            'address' => $address,
            'tag' => $tag,
        );
    }

    public function fetch_order_book ($symbol, $limit = null, $params = array ()) {
        $this->load_markets();
        $marketId = $this->market_id($symbol);
        $request = array_merge (array( 'symbol' => $marketId, 'level' => 2 ), $params);
        $response = $this->publicGetMarketOrderbookLevelLevel ($request);
        //
        // { sequence => '1547731421688',
        //   asks => array ( array ( '5c419328ef83c75456bd615c', '0.9', '0.09' ), ... ),
        //   bids => array ( array ( '5c419328ef83c75456bd615c', '0.9', '0.09' ), ... ), }
        //
        $data = $response['data'];
        $timestamp = $this->safe_integer($data, 'sequence');
        // $level can be a string such as 2_20 or 2_100
        $levelString = $this->safe_string($request, 'level');
        $levelParts = explode('_', $levelString);
        $level = intval ($levelParts[0]);
        return $this->parse_order_book($data, $timestamp, 'bids', 'asks', $level - 2, $level - 1);
    }

    public function create_order ($symbol, $type, $side, $amount, $price = null, $params = array ()) {
        $this->load_markets();
        $marketId = $this->market_id($symbol);
        // required param, cannot be used twice
        $clientOid = $this->uuid ();
        $request = array (
            'clientOid' => $clientOid,
            'side' => $side,
            'size' => $this->amount_to_precision($symbol, $amount),
            'symbol' => $marketId,
            'type' => $type,
        );
        if ($type !== 'market') {
            $request['price'] = $this->price_to_precision($symbol, $price);
        }
        $response = $this->privatePostOrders (array_merge ($request, $params));
        //
        //     {
        //         code => '200000',
        //         $data => {
        //             "orderId" => "5bd6e9286d99522a52e458de"
        //         }
        //    }
        //
        $data = $this->safe_value($response, 'data', array());
        $timestamp = $this->milliseconds ();
        return array (
            'id' => $this->safe_string($data, 'orderId'),
            'symbol' => $symbol,
            'type' => $type,
            'side' => $side,
            'amount' => $amount,
            'price' => $price,
            'cost' => null,
            'filled' => null,
            'remaining' => null,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'fee' => null,
            'status' => 'open',
            'clientOid' => $clientOid,
            'info' => $data,
        );
    }

    public function cancel_order ($id, $symbol = null, $params = array ()) {
        $request = array( 'orderId' => $id );
        $response = $this->privateDeleteOrdersOrderId (array_merge ($request, $params));
        return $response;
    }

    public function fetch_orders_by_status ($status, $symbol = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $request = array (
            'status' => $status,
        );
        $market = null;
        if ($symbol !== null) {
            $market = $this->market ($symbol);
            $request['symbol'] = $market['id'];
        }
        if ($since !== null) {
            $request['startAt'] = $since;
        }
        if ($limit !== null) {
            $request['pageSize'] = $limit;
        }
        $response = $this->privateGetOrders (array_merge ($request, $params));
        //
        //     {
        //         code => '200000',
        //         data => {
        //             "currentPage" => 1,
        //             "pageSize" => 1,
        //             "totalNum" => 153408,
        //             "totalPage" => 153408,
        //             "items" => array (
        //                 array (
        //                     "id" => "5c35c02703aa673ceec2a168",   //orderid
        //                     "$symbol" => "BTC-USDT",   //symbol
        //                     "opType" => "DEAL",      // operation type,deal is pending order,cancel is cancel order
        //                     "type" => "$limit",       // order type,e.g. $limit,markrt,stop_limit.
        //                     "side" => "buy",         // transaction direction,include buy and sell
        //                     "price" => "10",         // order price
        //                     "size" => "2",           // order quantity
        //                     "funds" => "0",          // order funds
        //                     "dealFunds" => "0.166",  // deal funds
        //                     "dealSize" => "2",       // deal quantity
        //                     "fee" => "0",            // fee
        //                     "feeCurrency" => "USDT", // charge fee currency
        //                     "stp" => "",             // self trade prevention,include CN,CO,DC,CB
        //                     "stop" => "",            // stop type
        //                     "stopTriggered" => false,  // stop order is triggered
        //                     "stopPrice" => "0",      // stop price
        //                     "timeInForce" => "GTC",  // time InForce,include GTC,GTT,IOC,FOK
        //                     "postOnly" => false,     // postOnly
        //                     "hidden" => false,       // hidden order
        //                     "iceberg" => false,      // iceberg order
        //                     "visibleSize" => "0",    // display quantity for iceberg order
        //                     "cancelAfter" => 0,      // cancel $orders time，requires timeInForce to be GTT
        //                     "channel" => "IOS",      // order source
        //                     "clientOid" => "",       // user-entered order unique mark
        //                     "remark" => "",          // remark
        //                     "tags" => "",            // tag order source
        //                     "isActive" => false,     // $status before unfilled or uncancelled
        //                     "cancelExist" => false,   // order cancellation transaction record
        //                     "createdAt" => 1547026471000  // time
        //                 ),
        //             )
        //         }
        //    }
        $responseData = $this->safe_value($response, 'data', array());
        $orders = $this->safe_value($responseData, 'items', array());
        return $this->parse_orders($orders, $market, $since, $limit);
    }

    public function fetch_closed_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        return $this->fetch_orders_by_status ('done', $symbol, $since, $limit, $params);
    }

    public function fetch_open_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        return $this->fetch_orders_by_status ('active', $symbol, $since, $limit, $params);
    }

    public function fetch_order ($id, $symbol = null, $params = array ()) {
        $this->load_markets();
        $request = array (
            'orderId' => $id,
        );
        $market = null;
        if ($symbol !== null) {
            $market = $this->market ($symbol);
        }
        $response = $this->privateGetOrdersOrderId (array_merge ($request, $params));
        $responseData = $response['data'];
        return $this->parse_order($responseData, $market);
    }

    public function parse_order ($order, $market = null) {
        //
        // fetchOpenOrders, fetchClosedOrders
        //
        //     {
        //         "id" => "5c35c02703aa673ceec2a168",   //orderid
        //         "$symbol" => "BTC-USDT",   //symbol
        //         "opType" => "DEAL",      // operation $type,deal is pending $order,cancel is cancel $order
        //         "$type" => "limit",       // $order $type,e.g. limit,markrt,stop_limit.
        //         "$side" => "buy",         // transaction direction,include buy and sell
        //         "$price" => "10",         // $order $price
        //         "size" => "2",           // $order quantity
        //         "funds" => "0",          // $order funds
        //         "dealFunds" => "0.166",  // deal funds
        //         "dealSize" => "2",       // deal quantity
        //         "$fee" => "0",            // $fee
        //         "$feeCurrency" => "USDT", // charge $fee currency
        //         "stp" => "",             // self trade prevention,include CN,CO,DC,CB
        //         "stop" => "",            // stop $type
        //         "stopTriggered" => false,  // stop $order is triggered
        //         "stopPrice" => "0",      // stop $price
        //         "timeInForce" => "GTC",  // time InForce,include GTC,GTT,IOC,FOK
        //         "postOnly" => false,     // postOnly
        //         "hidden" => false,       // hidden $order
        //         "iceberg" => false,      // iceberg $order
        //         "visibleSize" => "0",    // display quantity for iceberg $order
        //         "cancelAfter" => 0,      // cancel orders time，requires timeInForce to be GTT
        //         "channel" => "IOS",      // $order source
        //         "clientOid" => "",       // user-entered $order unique mark
        //         "remark" => "",          // remark
        //         "tags" => "",            // tag $order source
        //         "isActive" => false,     // $status before unfilled or uncancelled
        //         "cancelExist" => false,   // $order cancellation transaction record
        //         "createdAt" => 1547026471000  // time
        //     }
        //
        $symbol = null;
        $marketId = $this->safe_string($order, 'symbol');
        if ($marketId !== null) {
            if (is_array($this->markets_by_id) && array_key_exists($marketId, $this->markets_by_id)) {
                $market = $this->markets_by_id[$marketId];
                $symbol = $market['symbol'];
            } else {
                list($baseId, $quoteId) = explode('-', $marketId);
                $base = $this->safe_currency_code($baseId);
                $quote = $this->safe_currency_code($quoteId);
                $symbol = $base . '/' . $quote;
            }
            $market = $this->safe_value($this->markets_by_id, $marketId);
        }
        if ($symbol === null) {
            if ($market !== null) {
                $symbol = $market['symbol'];
            }
        }
        $orderId = $this->safe_string($order, 'id');
        $type = $this->safe_string($order, 'type');
        $timestamp = $this->safe_integer($order, 'createdAt');
        $datetime = $this->iso8601 ($timestamp);
        $price = $this->safe_float($order, 'price');
        $side = $this->safe_string($order, 'side');
        $feeCurrencyId = $this->safe_string($order, 'feeCurrency');
        $feeCurrency = $this->safe_currency_code($feeCurrencyId);
        $feeCost = $this->safe_float($order, 'fee');
        $amount = $this->safe_float($order, 'size');
        $filled = $this->safe_float($order, 'dealSize');
        $cost = $this->safe_float($order, 'dealFunds');
        $remaining = $amount - $filled;
        // bool
        $status = $order['isActive'] ? 'open' : 'closed';
        $fee = array (
            'currency' => $feeCurrency,
            'cost' => $feeCost,
        );
        if ($type === 'market') {
            if ($price === 0.0) {
                if (($cost !== null) && ($filled !== null)) {
                    if (($cost > 0) && ($filled > 0)) {
                        $price = $cost / $filled;
                    }
                }
            }
        }
        return array (
            'id' => $orderId,
            'symbol' => $symbol,
            'type' => $type,
            'side' => $side,
            'amount' => $amount,
            'price' => $price,
            'cost' => $cost,
            'filled' => $filled,
            'remaining' => $remaining,
            'timestamp' => $timestamp,
            'datetime' => $datetime,
            'fee' => $fee,
            'status' => $status,
            'info' => $order,
        );
    }

    public function fetch_my_trades ($symbol = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $request = array();
        $market = null;
        if ($symbol !== null) {
            $market = $this->market ($symbol);
            $request['symbol'] = $market['id'];
        }
        if ($limit !== null) {
            $request['pageSize'] = $limit;
        }
        $method = $this->options['fetchMyTradesMethod'];
        $parseResponseData = false;
        if ($method === 'private_get_fills') {
            // does not return $trades earlier than 2019-02-18T00:00:00Z
            if ($since !== null) {
                // only returns $trades up to one week after the $since param
                $request['startAt'] = $since;
            }
        } else if ($method === 'private_get_limit_fills') {
            // does not return $trades earlier than 2019-02-18T00:00:00Z
            // takes no $params
            // only returns first 1000 $trades (not only "in the last 24 hours" as stated in the docs)
            $parseResponseData = true;
        } else if ($method === 'private_get_hist_orders') {
            // despite that this endpoint is called `HistOrders`
            // it returns historical $trades instead of orders
            // returns $trades earlier than 2019-02-18T00:00:00Z only
            if ($since !== null) {
                $request['startAt'] = intval ($since / 1000);
            }
        } else {
            throw new ExchangeError($this->id . ' invalid fetchClosedOrder method');
        }
        $response = $this->$method (array_merge ($request, $params));
        //
        //     {
        //         "currentPage" => 1,
        //         "pageSize" => 50,
        //         "totalNum" => 1,
        //         "totalPage" => 1,
        //         "items" => array (
        //             array (
        //                 "$symbol":"BTC-USDT",       // $symbol
        //                 "tradeId":"5c35c02709e4f67d5266954e",        // trade id
        //                 "orderId":"5c35c02703aa673ceec2a168",        // order id
        //                 "counterOrderId":"5c1ab46003aa676e487fa8e3", // counter order id
        //                 "side":"buy",              // transaction direction,include buy and sell
        //                 "liquidity":"taker",       // include taker and maker
        //                 "forceTaker":true,         // forced to become taker
        //                 "price":"0.083",           // order price
        //                 "size":"0.8424304",        // order quantity
        //                 "funds":"0.0699217232",    // order funds
        //                 "fee":"0",                 // fee
        //                 "feeRate":"0",             // fee rate
        //                 "feeCurrency":"USDT",      // charge fee currency
        //                 "stop":"",                 // stop type
        //                 "type":"$limit",            // order type, e.g. $limit, $market, stop_limit.
        //                 "createdAt":1547026472000  // time
        //             ),
        //             //------------------------------------------------------
        //             // v1 (historical) trade $response structure
        //             {
        //                 "$symbol" => "SNOV-ETH",
        //                 "dealPrice" => "0.0000246",
        //                 "dealValue" => "0.018942",
        //                 "amount" => "770",
        //                 "fee" => "0.00001137",
        //                 "side" => "sell",
        //                 "createdAt" => 1540080199
        //                 "id":"5c4d389e4c8c60413f78e2e5",
        //             }
        //         )
        //     }
        //
        $data = $this->safe_value($response, 'data', array());
        $trades = null;
        if ($parseResponseData) {
            $trades = $data;
        } else {
            $trades = $this->safe_value($data, 'items', array());
        }
        return $this->parse_trades($trades, $market, $since, $limit);
    }

    public function fetch_trades ($symbol, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array (
            'symbol' => $market['id'],
        );
        if ($since !== null) {
            $request['startAt'] = (int) floor($since / 1000);
        }
        if ($limit !== null) {
            $request['pageSize'] = $limit;
        }
        $response = $this->publicGetMarketHistories (array_merge ($request, $params));
        //
        //     {
        //         "code" => "200000",
        //         "data" => array (
        //             {
        //                 "sequence" => "1548764654235",
        //                 "side" => "sell",
        //                 "size":"0.6841354",
        //                 "price":"0.03202",
        //                 "time":1548848575203567174
        //             }
        //         )
        //     }
        //
        $trades = $this->safe_value($response, 'data', array());
        return $this->parse_trades($trades, $market, $since, $limit);
    }

    public function parse_trade ($trade, $market = null) {
        //
        // fetchTrades (public)
        //
        //     {
        //         "sequence" => "1548764654235",
        //         "$side" => "sell",
        //         "size":"0.6841354",
        //         "$price":"0.03202",
        //         "time":1548848575203567174
        //     }
        //
        // fetchMyTrades (private) v2
        //
        //     {
        //         "$symbol":"BTC-USDT",
        //         "tradeId":"5c35c02709e4f67d5266954e",
        //         "$orderId":"5c35c02703aa673ceec2a168",
        //         "counterOrderId":"5c1ab46003aa676e487fa8e3",
        //         "$side":"buy",
        //         "liquidity":"taker",
        //         "forceTaker":true,
        //         "$price":"0.083",
        //         "size":"0.8424304",
        //         "funds":"0.0699217232",
        //         "$fee":"0",
        //         "feeRate":"0",
        //         "$feeCurrency":"USDT",
        //         "stop":"",
        //         "$type":"limit",
        //         "createdAt":1547026472000
        //     }
        //
        // fetchMyTrades v2 alternative format since 2019-05-21 https://github.com/ccxt/ccxt/pull/5162
        //
        //     {
        //         $symbol => "OPEN-BTC",
        //         forceTaker =>  false,
        //         $orderId => "5ce36420054b4663b1fff2c9",
        //         $fee => "0",
        //         $feeCurrency => "",
        //         $type => "",
        //         feeRate => "0",
        //         createdAt => 1558417615000,
        //         size => "12.8206",
        //         stop => "",
        //         $price => "0",
        //         funds => "0",
        //         tradeId => "5ce390cf6e0db23b861c6e80"
        //     }
        //
        // fetchMyTrades (private) v1 (historical)
        //
        //     {
        //         "$symbol" => "SNOV-ETH",
        //         "dealPrice" => "0.0000246",
        //         "dealValue" => "0.018942",
        //         "$amount" => "770",
        //         "$fee" => "0.00001137",
        //         "$side" => "sell",
        //         "createdAt" => 1540080199
        //         "$id":"5c4d389e4c8c60413f78e2e5",
        //     }
        //
        $symbol = null;
        $marketId = $this->safe_string($trade, 'symbol');
        if ($marketId !== null) {
            if (is_array($this->markets_by_id) && array_key_exists($marketId, $this->markets_by_id)) {
                $market = $this->markets_by_id[$marketId];
                $symbol = $market['symbol'];
            } else {
                list($baseId, $quoteId) = explode('-', $marketId);
                $base = $this->safe_currency_code($baseId);
                $quote = $this->safe_currency_code($quoteId);
                $symbol = $base . '/' . $quote;
            }
        }
        if ($symbol === null) {
            if ($market !== null) {
                $symbol = $market['symbol'];
            }
        }
        $id = $this->safe_string_2($trade, 'tradeId', 'id');
        if ($id !== null) {
            $id = (string) $id;
        }
        $orderId = $this->safe_string($trade, 'orderId');
        $takerOrMaker = $this->safe_string($trade, 'liquidity');
        $amount = $this->safe_float_2($trade, 'size', 'amount');
        $timestamp = $this->safe_integer($trade, 'time');
        if ($timestamp !== null) {
            $timestamp = intval ($timestamp / 1000000);
        } else {
            $timestamp = $this->safe_integer($trade, 'createdAt');
            // if it's a historical v1 $trade, the exchange returns $timestamp in seconds
            if ((is_array($trade) && array_key_exists('dealValue', $trade)) && ($timestamp !== null)) {
                $timestamp = $timestamp * 1000;
            }
        }
        $price = $this->safe_float_2($trade, 'price', 'dealPrice');
        $side = $this->safe_string($trade, 'side');
        $fee = null;
        $feeCost = $this->safe_float($trade, 'fee');
        if ($feeCost !== null) {
            $feeCurrencyId = $this->safe_string($trade, 'feeCurrency');
            $feeCurrency = $this->safe_currency_code($feeCurrencyId);
            if ($feeCurrency === null) {
                if ($market !== null) {
                    $feeCurrency = ($side === 'sell') ? $market['quote'] : $market['base'];
                }
            }
            $fee = array (
                'cost' => $feeCost,
                'currency' => $feeCurrency,
                'rate' => $this->safe_float($trade, 'feeRate'),
            );
        }
        $type = $this->safe_string($trade, 'type');
        $cost = $this->safe_float_2($trade, 'funds', 'dealValue');
        if ($cost === null) {
            if ($amount !== null) {
                if ($price !== null) {
                    $cost = $amount * $price;
                }
            }
        }
        return array (
            'info' => $trade,
            'id' => $id,
            'order' => $orderId,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'symbol' => $symbol,
            'type' => $type,
            'takerOrMaker' => $takerOrMaker,
            'side' => $side,
            'price' => $price,
            'amount' => $amount,
            'cost' => $cost,
            'fee' => $fee,
        );
    }

    public function withdraw ($code, $amount, $address, $tag = null, $params = array ()) {
        $this->load_markets();
        $this->check_address($address);
        $currency = $this->currencyId ($code);
        $request = array (
            'currency' => $currency,
            'address' => $address,
            'amount' => $amount,
        );
        if ($tag !== null) {
            $request['memo'] = $tag;
        }
        $response = $this->privatePostWithdrawals (array_merge ($request, $params));
        //
        // https://github.com/ccxt/ccxt/issues/5558
        //
        //     {
        //         "$code" =>  200000,
        //         "$data" => {
        //             "withdrawalId" =>  "abcdefghijklmnopqrstuvwxyz"
        //         }
        //     }
        //
        $data = $this->safe_value($response, 'data', array());
        return array (
            'id' => $this->safe_string($data, 'withdrawalId'),
            'info' => $response,
        );
    }

    public function parse_transaction_status ($status) {
        $statuses = array (
            'SUCCESS' => 'ok',
            'PROCESSING' => 'ok',
            'FAILURE' => 'failed',
        );
        return $this->safe_string($statuses, $status);
    }

    public function parse_transaction ($transaction, $currency = null) {
        //
        // fetchDeposits
        //
        //     {
        //         "$address" => "0x5f047b29041bcfdbf0e4478cdfa753a336ba6989",
        //         "memo" => "5c247c8a03aa677cea2a251d",
        //         "$amount" => 1,
        //         "$fee" => 0.0001,
        //         "$currency" => "KCS",
        //         "isInner" => false,
        //         "walletTxId" => "5bbb57386d99522d9f954c5a@test004",
        //         "$status" => "SUCCESS",
        //         "createdAt" => 1544178843000,
        //         "updatedAt" => 1544178891000
        //     }
        //
        // fetchWithdrawals
        //
        //     {
        //         "$id" => "5c2dc64e03aa675aa263f1ac",
        //         "$address" => "0x5bedb060b8eb8d823e2414d82acce78d38be7fe9",
        //         "memo" => "",
        //         "$currency" => "ETH",
        //         "$amount" => 1.0000000,
        //         "$fee" => 0.0100000,
        //         "walletTxId" => "3e2414d82acce78d38be7fe9",
        //         "isInner" => false,
        //         "$status" => "FAILURE",
        //         "createdAt" => 1546503758000,
        //         "updatedAt" => 1546504603000
        //     }
        //
        $currencyId = $this->safe_string($transaction, 'currency');
        $code = $this->safe_currency_code($currencyId, $currency);
        $address = $this->safe_string($transaction, 'address');
        $amount = $this->safe_float($transaction, 'amount');
        $txid = $this->safe_string($transaction, 'walletTxId');
        if ($txid !== null) {
            $txidParts = explode('@', $txid);
            $numTxidParts = is_array ($txidParts) ? count ($txidParts) : 0;
            if ($numTxidParts > 1) {
                if ($address === null) {
                    if (strlen ($txidParts[1]) > 1) {
                        $address = $txidParts[1];
                    }
                }
            }
            $txid = $txidParts[0];
        }
        $type = ($txid === null) ? 'withdrawal' : 'deposit';
        $rawStatus = $this->safe_string($transaction, 'status');
        $status = $this->parse_transaction_status ($rawStatus);
        $fee = null;
        $feeCost = $this->safe_float($transaction, 'fee');
        if ($feeCost !== null) {
            $rate = null;
            if ($amount !== null) {
                $rate = $feeCost / $amount;
            }
            $fee = array (
                'cost' => $feeCost,
                'rate' => $rate,
                'currency' => $code,
            );
        }
        $tag = $this->safe_string($transaction, 'memo');
        $timestamp = $this->safe_integer_2($transaction, 'createdAt', 'createAt');
        $id = $this->safe_string($transaction, 'id');
        $updated = $this->safe_integer($transaction, 'updatedAt');
        $isV1 = !(is_array($transaction) && array_key_exists('createdAt', $transaction));
        // if it's a v1 structure
        if ($isV1) {
            $type = (is_array($transaction) && array_key_exists('address', $transaction)) ? 'withdrawal' : 'deposit';
            if ($timestamp !== null) {
                $timestamp = $timestamp * 1000;
            }
            if ($updated !== null) {
                $updated = $updated * 1000;
            }
        }
        return array (
            'id' => $id,
            'address' => $address,
            'tag' => $tag,
            'currency' => $code,
            'amount' => $amount,
            'txid' => $txid,
            'type' => $type,
            'status' => $status,
            'fee' => $fee,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'updated' => $updated,
            'info' => $transaction,
        );
    }

    public function fetch_deposits ($code = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $request = array();
        $currency = null;
        if ($code !== null) {
            $currency = $this->currency ($code);
            $request['currency'] = $currency['id'];
        }
        if ($limit !== null) {
            $request['pageSize'] = $limit;
        }
        $method = 'privateGetDeposits';
        if ($since !== null) {
            // if $since is earlier than 2019-02-18T00:00:00Z
            if ($since < 1550448000000) {
                $request['startAt'] = intval ($since / 1000);
                $method = 'privateGetHistDeposits';
            } else {
                $request['startAt'] = $since;
            }
        }
        $response = $this->$method (array_merge ($request, $params));
        //
        //     {
        //         $code => '200000',
        //         data => {
        //             "currentPage" => 1,
        //             "pageSize" => 5,
        //             "totalNum" => 2,
        //             "totalPage" => 1,
        //             "items" => array (
        //                 //--------------------------------------------------
        //                 // version 2 deposit $response structure
        //                 array (
        //                     "address" => "0x5f047b29041bcfdbf0e4478cdfa753a336ba6989",
        //                     "memo" => "5c247c8a03aa677cea2a251d",
        //                     "amount" => 1,
        //                     "fee" => 0.0001,
        //                     "$currency" => "KCS",
        //                     "isInner" => false,
        //                     "walletTxId" => "5bbb57386d99522d9f954c5a@test004",
        //                     "status" => "SUCCESS",
        //                     "createdAt" => 1544178843000,
        //                     "updatedAt" => 1544178891000
        //                 ),
        //                 //--------------------------------------------------
        //                 // version 1 (historical) deposit $response structure
        //                 {
        //                     "$currency" => "BTC",
        //                     "createAt" => 1528536998,
        //                     "amount" => "0.03266638",
        //                     "walletTxId" => "55c643bc2c68d6f17266383ac1be9e454038864b929ae7cee0bc408cc5c869e8@12ffGWmMMD1zA1WbFm7Ho3JZ1w6NYXjpFk@234",
        //                     "isInner" => false,
        //                     "status" => "SUCCESS",
        //                 }
        //             )
        //         }
        //     }
        //
        $responseData = $response['data']['items'];
        return $this->parse_transactions($responseData, $currency, $since, $limit, array( 'type' => 'deposit' ));
    }

    public function fetch_withdrawals ($code = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $request = array();
        $currency = null;
        if ($code !== null) {
            $currency = $this->currency ($code);
            $request['currency'] = $currency['id'];
        }
        if ($limit !== null) {
            $request['pageSize'] = $limit;
        }
        $method = 'privateGetWithdrawals';
        if ($since !== null) {
            // if $since is earlier than 2019-02-18T00:00:00Z
            if ($since < 1550448000000) {
                $request['startAt'] = intval ($since / 1000);
                $method = 'privateGetHistWithdrawals';
            } else {
                $request['startAt'] = $since;
            }
        }
        $response = $this->$method (array_merge ($request, $params));
        //
        //     {
        //         $code => '200000',
        //         data => {
        //             "currentPage" => 1,
        //             "pageSize" => 5,
        //             "totalNum" => 2,
        //             "totalPage" => 1,
        //             "items" => array (
        //                 //--------------------------------------------------
        //                 // version 2 withdrawal $response structure
        //                 array (
        //                     "id" => "5c2dc64e03aa675aa263f1ac",
        //                     "address" => "0x5bedb060b8eb8d823e2414d82acce78d38be7fe9",
        //                     "memo" => "",
        //                     "$currency" => "ETH",
        //                     "amount" => 1.0000000,
        //                     "fee" => 0.0100000,
        //                     "walletTxId" => "3e2414d82acce78d38be7fe9",
        //                     "isInner" => false,
        //                     "status" => "FAILURE",
        //                     "createdAt" => 1546503758000,
        //                     "updatedAt" => 1546504603000
        //                 ),
        //                 //--------------------------------------------------
        //                 // version 1 (historical) withdrawal $response structure
        //                 {
        //                     "$currency" => "BTC",
        //                     "createAt" => 1526723468,
        //                     "amount" => "0.534",
        //                     "address" => "33xW37ZSW4tQvg443Pc7NLCAs167Yc2XUV",
        //                     "walletTxId" => "aeacea864c020acf58e51606169240e96774838dcd4f7ce48acf38e3651323f4",
        //                     "isInner" => false,
        //                     "status" => "SUCCESS"
        //                 }
        //             )
        //         }
        //     }
        //
        $responseData = $response['data']['items'];
        return $this->parse_transactions($responseData, $currency, $since, $limit, array( 'type' => 'withdrawal' ));
    }

    public function fetch_balance ($params = array ()) {
        $this->load_markets();
        $type = null;
        $request = array();
        if (is_array($params) && array_key_exists('type', $params)) {
            $type = $params['type'];
            if ($type !== null) {
                $request['type'] = $type;
            }
            $params = $this->omit ($params, 'type');
        } else {
            $options = $this->safe_value($this->options, 'fetchBalance', array());
            $type = $this->safe_string($options, 'type', 'trade');
        }
        $response = $this->privateGetAccounts (array_merge ($request, $params));
        //
        //     {
        //         "$code":"200000",
        //         "$data":array (
        //             array("$balance":"0.00009788","available":"0.00009788","holds":"0","currency":"BTC","id":"5c6a4fd399a1d81c4f9cc4d0","$type":"trade"),
        //             array("$balance":"3.41060034","available":"3.41060034","holds":"0","currency":"SOUL","id":"5c6a4d5d99a1d8182d37046d","$type":"trade"),
        //             array("$balance":"0.01562641","available":"0.01562641","holds":"0","currency":"NEO","id":"5c6a4f1199a1d8165a99edb1","$type":"trade"),
        //         )
        //     }
        //
        $data = $this->safe_value($response, 'data', array());
        $result = array( 'info' => $response );
        for ($i = 0; $i < count ($data); $i++) {
            $balance = $data[$i];
            $balanceType = $this->safe_string($balance, 'type');
            if ($balanceType === $type) {
                $currencyId = $this->safe_string($balance, 'currency');
                $code = $this->safe_currency_code($currencyId);
                $account = $this->account ();
                $account['total'] = $this->safe_float($balance, 'balance');
                $account['free'] = $this->safe_float($balance, 'available');
                $account['used'] = $this->safe_float($balance, 'holds');
                $result[$code] = $account;
            }
        }
        return $this->parse_balance($result);
    }

    public function fetch_ledger ($code = null, $since = null, $limit = null, $params = array ()) {
        if ($code === null) {
            throw new ArgumentsRequired($this->id . ' fetchLedger requires a $code param');
        }
        $this->load_markets();
        $this->load_accounts();
        $currency = $this->currency ($code);
        $accountId = $this->safe_string($params, 'accountId');
        if ($accountId === null) {
            for ($i = 0; $i < count ($this->accounts); $i++) {
                $account = $this->accounts[$i];
                if ($account['currency'] === $code && $account['type'] === 'main') {
                    $accountId = $account['id'];
                    break;
                }
            }
        }
        if ($accountId === null) {
            throw new ExchangeError($this->id . ' ' . $code . 'main $account is not loaded in loadAccounts');
        }
        $request = array (
            'accountId' => $accountId,
        );
        if ($since !== null) {
            $request['startAt'] = (int) floor($since / 1000);
        }
        $response = $this->privateGetAccountsAccountIdLedgers (array_merge ($request, $params));
        //
        //     {
        //         $code => '200000',
        //         data => {
        //             totalNum => 1,
        //             totalPage => 1,
        //             pageSize => 50,
        //             currentPage => 1,
        //             $items => array (
        //                 {
        //                     createdAt => 1561897880000,
        //                     amount => '0.0111123',
        //                     bizType => 'Exchange',
        //                     balance => '0.13224427',
        //                     fee => '0.0000111',
        //                     context => 'array("symbol":"KCS-ETH","orderId":"5d18ab98c788c6426188296f","tradeId":"5d18ab9818996813f539a806")',
        //                     $currency => 'ETH',
        //                     direction => 'out'
        //                 }
        //             )
        //         }
        //     }
        //
        $items = $response['data']['items'];
        return $this->parse_ledger($items, $currency, $since, $limit);
    }

    public function parse_ledger_entry ($item, $currency = null) {
        //
        // trade
        //
        //     {
        //         createdAt => 1561897880000,
        //         $amount => '0.0111123',
        //         bizType => 'Exchange',
        //         balance => '0.13224427',
        //         $fee => '0.0000111',
        //         $context => 'array("symbol":"KCS-ETH","orderId":"5d18ab98c788c6426188296f","tradeId":"5d18ab9818996813f539a806")',
        //         $currency => 'ETH',
        //         $direction => 'out'
        //     }
        //
        // withdrawal
        //
        //     {
        //         createdAt => 1561900264000,
        //         $amount => '0.14333217',
        //         bizType => 'Withdrawal',
        //         balance => '0',
        //         $fee => '0.01',
        //         $context => 'array("orderId":"5d18b4e687111437cf1c48b9","txId":"0x1d136ee065c5c4c5caa293faa90d43e213c953d7cdd575c89ed0b54eb87228b8")',
        //         $currency => 'ETH',
        //         $direction => 'out'
        //     }
        //
        $currencyId = $this->safe_string($item, 'currency');
        $code = $this->safe_currency_code($currencyId, $currency);
        $fee = array (
            'cost' => $this->safe_float($item, 'fee'),
            'code' => $code,
        );
        $amount = $this->safe_float($item, 'amount');
        $after = $this->safe_float($item, 'balance');
        $direction = $this->safe_string($item, 'direction');
        $before = null;
        if ($after !== null && $amount !== null) {
            $difference = ($direction === 'out') ? $amount : -$amount;
            $before = $this->sum ($after, $difference);
        }
        $timestamp = $this->safe_integer($item, 'createdAt');
        $type = $this->parse_ledger_entry_type ($this->safe_string($item, 'bizType'));
        $contextString = $this->safe_string($item, 'context');
        $id = null;
        $referenceId = null;
        if ($this->is_json_encoded_object($contextString)) {
            $context = $this->parse_json($contextString);
            $id = $this->safe_string($context, 'orderId');
            if ($type === 'trade') {
                $referenceId = $this->safe_string($context, 'tradeId');
            } else if ($type === 'transaction') {
                $referenceId = $this->safe_string($context, 'txId');
            }
        }
        return array (
            'id' => $id,
            'currency' => $code,
            'account' => null,
            'referenceAccount' => null,
            'referenceId' => $referenceId,
            'status' => null,
            'amount' => $amount,
            'before' => $before,
            'after' => $after,
            'fee' => $fee,
            'direction' => $direction,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'type' => $type,
            'info' => $item,
        );
    }

    public function parse_ledger_entry_type ($type) {
        $types = array (
            'Exchange' => 'trade',
            'Withdrawal' => 'transaction',
            'Deposit' => 'transaction',
            'Transfer' => 'transfer',
        );
        return $this->safe_string($types, $type, $type);
    }

    public function sign ($path, $api = 'public', $method = 'GET', $params = array (), $headers = null, $body = null) {
        //
        // the v2 URL is https://openapi-v2.kucoin.com/api/v1/endpoint
        //                                †                 ↑
        //
        $version = $this->safe_string($params, 'version', $this->options['version']);
        $params = $this->omit ($params, 'version');
        $endpoint = '/api/' . $version . '/' . $this->implode_params($path, $params);
        $query = $this->omit ($params, $this->extract_params($path));
        $endpart = '';
        $headers = ($headers !== null) ? $headers : array();
        if ($query) {
            if ($method !== 'GET') {
                $body = $this->json ($query);
                $endpart = $body;
                $headers['Content-Type'] = 'application/json';
            } else {
                $endpoint .= '?' . $this->urlencode ($query);
            }
        }
        $url = $this->urls['api'][$api] . $endpoint;
        if ($api === 'private') {
            $this->check_required_credentials();
            $timestamp = (string) $this->nonce ();
            $headers = array_merge (array (
                'KC-API-KEY' => $this->apiKey,
                'KC-API-TIMESTAMP' => $timestamp,
                'KC-API-PASSPHRASE' => $this->password,
            ), $headers);
            $payload = $timestamp . $method . $endpoint . $endpart;
            $signature = $this->hmac ($this->encode ($payload), $this->encode ($this->secret), 'sha256', 'base64');
            $headers['KC-API-SIGN'] = $this->decode ($signature);
        }
        return array( 'url' => $url, 'method' => $method, 'body' => $body, 'headers' => $headers );
    }

    public function handle_errors ($code, $reason, $url, $method, $headers, $body, $response, $requestHeaders, $requestBody) {
        if (!$response) {
            return;
        }
        //
        // bad
        //     array( "$code" => "400100", "msg" => "validation.createOrder.clientOidIsRequired" )
        // good
        //     array( $code => '200000', data => array( ... ))
        //
        $errorCode = $this->safe_string($response, 'code');
        $message = $this->safe_string($response, 'msg');
        $ExceptionClass = $this->safe_value_2($this->exceptions, $message, $errorCode);
        if ($ExceptionClass !== null) {
            throw new $ExceptionClass($this->id . ' ' . $message);
        }
    }
}
