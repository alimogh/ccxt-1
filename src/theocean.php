<?php

namespace ccxt;

use Exception as Exception; // a common import

class theocean extends Exchange {

    public function describe () {
        $this->check_required_dependencies();
        return array_replace_recursive (parent::describe (), array (
            'id' => 'theocean',
            'name' => 'The Ocean',
            'countries' => array ( 'US' ),
            'rateLimit' => 3000,
            'version' => 'v1',
            'certified' => true,
            'requiresWeb3' => true,
            'timeframes' => array (
                '5m' => '300',
                '15m' => '900',
                '1h' => '3600',
                '6h' => '21600',
                '1d' => '86400',
            ),
            'has' => array (
                'CORS' => false, // ?
                'fetchTickers' => true,
                'fetchOHLCV' => false,
                'fetchOrder' => true,
                'fetchOrders' => true,
                'fetchOpenOrders' => true,
                'fetchClosedOrders' => true,
            ),
            'urls' => array (
                'logo' => 'https://user-images.githubusercontent.com/1294454/43103756-d56613ce-8ed7-11e8-924e-68f9d4bcacab.jpg',
                'api' => 'https://api.staging.theocean.trade',
                'www' => 'https://theocean.trade',
                'doc' => 'https://docs.theocean.trade',
                'fees' => 'https://theocean.trade/fees',
            ),
            'api' => array (
                'public' => array (
                    'get' => array (
                        'fee_components',
                        'token_pairs',
                        'ticker',
                        'tickers',
                        'candlesticks',
                        'candlesticks/intervals',
                        'trade_history',
                        'order_book',
                        'order/{orderHash}',
                        'version',
                    ),
                ),
                'private' => array (
                    'get' => array (
                        'balance',
                        'available_balance',
                        'order_history',
                        'order/unsigned',
                        'order/unsigned/market',
                    ),
                    'post' => array (
                        'order',
                    ),
                    'delete' => array (
                        'order/{orderHash}',
                        'order',
                    ),
                ),
            ),
            'exceptions' => array (
                'exact' => array (
                    'Order not found' => '\\ccxt\\OrderNotFound', // array ("message":"Order not found","errors":...)
                ),
                'broad' => array (
                    "Price can't exceed 8 digits in precision." => '\\ccxt\\InvalidOrder', // array ("message":"Price can't exceed 8 digits in precision.","type":"paramPrice")
                    'Order cannot be canceled' => '\\ccxt\\InvalidOrder', // array ("message":"Order cannot be canceled","type":"General error")
                    'Greater than available wallet balance.' => '\\ccxt\\InsufficientFunds',
                    'Fillable amount under minimum' => '\\ccxt\\InvalidOrder', // array ("message":"Fillable amount under minimum WETH trade size.","type":"paramQuoteTokenAmount")
                    'Fillable amount over maximum' => '\\ccxt\\InvalidOrder', // array ("message":"Fillable amount over maximum TUSD trade size.","type":"paramQuoteTokenAmount")
                    "Schema validation failed for 'params'" => '\\ccxt\\BadRequest', // // array ("message":"Schema validation failed for 'params'")
                    'Service Temporarily Unavailable' => '\\ccxt\\ExchangeNotAvailable',
                ),
            ),
            'options' => array (
                'decimals' => array (),
                'fetchOrderMethod' => 'fetch_order_from_history',
            ),
        ));
    }

    public function fetch_markets ($params = array ()) {
        $markets = $this->publicGetTokenPairs ();
        //
        //     array (
        //       "$baseToken" => array (
        //         "$symbol" => "ZRX",
        //         "address" => "0x6ff6c0ff1d68b964901f986d4c9fa3ac68346570",
        //         "name" => "0x Protocol Token",
        //         "decimals" => "18",
        //         "minAmount" => "10000000000000000000",
        //         "maxAmount" => "10000000000000000000000",
        //         "$precision" => "-8"
        //       ),
        //       "$quoteToken" => {
        //         "$symbol" => "ETH",
        //         "address" => "0xd0a1e359811322d97991e03f863a0c30c2cf029c",
        //         "name" => "Ether Token",
        //         "decimals" => "18",
        //         "minAmount" => "20000000000000000",
        //         "maxAmount" => "20000000000000000000",
        //         "$precision" => "-8"
        //       }
        //     )
        //
        $result = array ();
        for ($i = 0; $i < count ($markets); $i++) {
            $market = $markets[$i];
            $baseToken = $market['baseToken'];
            $quoteToken = $market['quoteToken'];
            $baseId = $baseToken['address'];
            $quoteId = $quoteToken['address'];
            $base = $baseToken['symbol'];
            $quote = $quoteToken['symbol'];
            $base = $this->common_currency_code($base);
            $quote = $this->common_currency_code($quote);
            $symbol = $base . '/' . $quote;
            $id = $baseId . '/' . $quoteId;
            $baseDecimals = $this->safe_integer($baseToken, 'decimals');
            $quoteDecimals = $this->safe_integer($quoteToken, 'decimals');
            $this->options['decimals'][$base] = $baseDecimals;
            $this->options['decimals'][$quote] = $quoteDecimals;
            $precision = array (
                'amount' => -intval ($baseToken['precision']),
                'price' => -intval ($quoteToken['precision']),
            );
            $amountLimits = array (
                'min' => $this->fromWei ($this->safe_string($baseToken, 'minAmount'), 'ether', $baseDecimals),
                'max' => $this->fromWei ($this->safe_string($baseToken, 'maxAmount'), 'ether', $baseDecimals),
            );
            $priceLimits = array (
                'min' => null,
                'max' => null,
            );
            $costLimits = array (
                'min' => $this->fromWei ($this->safe_string($quoteToken, 'minAmount'), 'ether', $quoteDecimals),
                'max' => $this->fromWei ($this->safe_string($quoteToken, 'maxAmount'), 'ether', $quoteDecimals),
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
                'active' => $active,
                'precision' => $precision,
                'limits' => $limits,
                'info' => $market,
            );
        }
        return $result;
    }

    public function parse_ohlcv ($ohlcv, $market = null, $timeframe = '5m', $since = null, $limit = null) {
        $baseDecimals = $this->safe_integer($this->options['decimals'], $market['base'], 18);
        return array (
            $this->safe_integer($ohlcv, 'startTime') * 1000,
            $this->safe_float($ohlcv, 'open'),
            $this->safe_float($ohlcv, 'high'),
            $this->safe_float($ohlcv, 'low'),
            $this->safe_float($ohlcv, 'close'),
            $this->fromWei ($this->safe_string($ohlcv, 'baseVolume'), 'ether', $baseDecimals),
        );
    }

    public function fetch_ohlcv ($symbol, $timeframe = '5m', $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array (
            'baseTokenAddress' => $market['baseId'],
            'quoteTokenAddress' => $market['quoteId'],
            'interval' => $this->timeframes[$timeframe],
        );
        if ($since === null) {
            throw new ExchangeError ($this->id . ' fetchOHLCV requires a $since argument');
        }
        $since = intval ($since);
        $request['startTime'] = $since;
        $response = $this->publicGetCandlesticks (array_merge ($request, $params));
        //
        //   array (
        //     array (
        //         "high" => "100.52",
        //         "low" => "97.23",
        //         "open" => "98.45",
        //         "close" => "99.23",
        //         "baseVolume" => "2400000000000000000000",
        //         "quoteVolume" => "1200000000000000000000",
        //         "startTime" => "1512929323784"
        //     ),
        //     {
        //         "high" => "100.52",
        //         "low" => "97.23",
        //         "open" => "98.45",
        //         "close" => "99.23",
        //         "volume" => "2400000000000000000000",
        //         "startTime" => "1512929198980"
        //     }
        //   )
        //
        return $this->parse_ohlcvs($response, $market, $timeframe, $since, $limit);
    }

    public function fetch_balance_by_code ($code, $params = array ()) {
        if (!$this->walletAddress || (mb_strpos ($this->walletAddress, '0x') !== 0)) {
            throw new InvalidAddress ($this->id . ' fetchBalanceByCode() requires the .walletAddress to be a "0x"-prefixed hexstring like "0xbF2d65B3b2907214EEA3562f21B80f6Ed7220377"');
        }
        $this->load_markets();
        $currency = $this->currency ($code);
        $request = array (
            'walletAddress' => strtolower ($this->walletAddress),
            'tokenAddress' => $currency['id'],
        );
        $response = $this->privateGetBalance (array_merge ($request, $params));
        //
        //     array ("available":"0","committed":"0","$total":"0")
        //
        $decimals = $this->safe_integer($this->options['decimals'], $code, 18);
        $free = $this->fromWei ($this->safe_string($response, 'available'), 'ether', $decimals);
        $used = $this->fromWei ($this->safe_string($response, 'committed'), 'ether', $decimals);
        $total = $this->fromWei ($this->safe_string($response, 'total'), 'ether', $decimals);
        return array (
            'free' => $free,
            'used' => $used,
            'total' => $total,
        );
    }

    public function fetch_balance ($params = array ()) {
        if (!$this->walletAddress || (mb_strpos ($this->walletAddress, '0x') !== 0)) {
            throw new InvalidAddress ($this->id . ' fetchBalance() requires the .walletAddress to be a "0x"-prefixed hexstring like "0xbF2d65B3b2907214EEA3562f21B80f6Ed7220377"');
        }
        $codes = $this->safe_value($this->options, 'fetchBalanceCurrencies');
        if ($codes === null)
            $codes = $this->safe_value($params, 'codes');
        if (($codes === null) || (!gettype ($codes) === 'array' && count (array_filter (array_keys ($codes), 'is_string')) == 0)) {
            throw new ExchangeError ($this->id . ' fetchBalance() requires a `$codes` parameter (an array of currency $codes)');
        }
        $this->load_markets();
        $result = array ();
        for ($i = 0; $i < count ($codes); $i++) {
            $code = $codes[$i];
            $result[$code] = $this->fetch_balance_by_code ($code);
        }
        return $this->parse_balance($result);
    }

    public function parse_bid_ask ($bidask, $priceKey = 0, $amountKey = 1, $market = null) {
        if ($market === null) {
            throw new ArgumentsRequired ($this->id . ' parseBidAsk requires a $market argument');
        }
        $price = floatval ($bidask[$priceKey]);
        $amountDecimals = $this->safe_integer($this->options['decimals'], $market['base'], 18);
        $amount = $this->fromWei ($bidask[$amountKey], 'ether', $amountDecimals);
        return array ( $price, $amount );
    }

    public function parse_order_book ($orderbook, $timestamp = null, $bidsKey = 'bids', $asksKey = 'asks', $priceKey = 0, $amountKey = 1, $market = null) {
        $result = array (
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'nonce' => null,
        );
        $sides = array ( $bidsKey, $asksKey );
        for ($i = 0; $i < count ($sides); $i++) {
            $side = $sides[$i];
            $orders = array ();
            $bidasks = $this->safe_value($orderbook, $side);
            for ($k = 0; $k < count ($bidasks); $k++) {
                $orders[] = $this->parse_bid_ask($bidasks[$k], $priceKey, $amountKey, $market);
            }
            $result[$side] = $orders;
        }
        $result[$bidsKey] = $this->sort_by($result[$bidsKey], 0, true);
        $result[$asksKey] = $this->sort_by($result[$asksKey], 0);
        return $result;
    }

    public function fetch_order_book ($symbol, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array (
            'baseTokenAddress' => $market['baseId'],
            'quoteTokenAddress' => $market['quoteId'],
        );
        if ($limit !== null) {
            $request['depth'] = $limit;
        }
        $response = $this->publicGetOrderBook (array_merge ($request, $params));
        //
        //     {
        //       "bids" => array (
        //         { orderHash => '0xe2b7f80198edb561cc66cd85cb8e5f420073cf1e5143193d8add8774bd8236c4',
        //           price => '30',
        //           availableAmount => '500000000000000000',
        //           creationTimestamp => '1547193525',
        //           expirationTimestampInSec => '1549789124'
        //         }
        //       ),
        //       "asks" => array (
        //         { orderHash => '0xe2b7f80198edb561cc66cd85cb8e5f420073cf1e5143193d8add8774bd8236c4',
        //           price => '30',
        //           availableAmount => '500000000000000000',
        //           creationTimestamp => '1547193525',
        //           expirationTimestampInSec => '1549789124'
        //         }
        //       )
        //     }
        //
        return $this->parse_order_book($response, null, 'bids', 'asks', 'price', 'availableAmount', $market);
    }

    public function parse_ticker ($ticker, $market = null) {
        //
        //     {
        //         "bid" => "0.00050915",
        //         "ask" => "0.00054134",
        //         "$last" => "0.00052718",
        //         "volume" => "3000000000000000000",
        //         "$timestamp" => "1512929327792"
        //     }
        //
        $timestamp = intval ($this->safe_float($ticker, 'timestamp') / 1000);
        $symbol = null;
        $base = null;
        if ($market !== null) {
            $symbol = $market['symbol'];
            $base = $market['base'];
        }
        $baseDecimals = $this->safe_integer($this->options['decimals'], $base, 18);
        $baseVolume = $this->fromWei ($this->safe_string($ticker, 'volume'), 'ether', $baseDecimals);
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
            'percentage' => $this->safe_float($ticker, 'priceChange'),
            'average' => null,
            'baseVolume' => $baseVolume,
            'quoteVolume' => null,
            'info' => $ticker,
        );
    }

    public function fetch_tickers ($symbols = null, $params = array ()) {
        $this->load_markets();
        $tickers = $this->publicGetTickers ($params);
        //
        //     [{
        //     "baseTokenAddress" => "0xa8e9fa8f91e5ae138c74648c9c304f1c75003a8d",
        //     "quoteTokenAddress" => "0xc00fd9820cd2898cc4c054b7bf142de637ad129a",
        //     "$ticker" => array (
        //         "bid" => "0.00050915",
        //         "ask" => "0.00054134",
        //         "last" => "0.00052718",
        //         "volume" => "3000000000000000000",
        //         "timestamp" => "1512929327792"
        //     }
        //     )]
        //
        $result = array ();
        for ($i = 0; $i < count ($tickers); $i++) {
            $ticker = $tickers[$i];
            $baseId = $this->safe_string($ticker, 'baseTokenAddress');
            $quoteId = $this->safe_string($ticker, 'quoteTokenAddress');
            $marketId = $baseId . '/' . $quoteId;
            $market = null;
            $symbol = $marketId;
            if (is_array ($this->markets_by_id) && array_key_exists ($marketId, $this->markets_by_id)) {
                $market = $this->markets_by_id[$marketId];
                $symbol = $market['symbol'];
            }
            $result[$symbol] = $this->parse_ticker($ticker['ticker'], $market);
        }
        return $result;
    }

    public function fetch_ticker ($symbol, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array (
            'baseTokenAddress' => $market['baseId'],
            'quoteTokenAddress' => $market['quoteId'],
        );
        $response = $this->publicGetTicker (array_merge ($request, $params));
        return $this->parse_ticker($response, $market);
    }

    public function parse_trade ($trade, $market = null) {
        //
        // fetchTrades
        //
        //     {
        //         "$id" => "37212",
        //         "transactionHash" => "0x5e6e75e1aa681b51b034296f62ac19be7460411a2ad94042dd8ba637e13eac0c",
        //         "$amount" => "300000000000000000",
        //         "$price" => "0.00052718",
        // ------- they also have a "confirmed" status here ↓ -----------------
        //         "status" => "filled", // filled | settled | failed
        //         "lastUpdated" => "1520265048996"
        //     }
        //
        // parseOrder trades (timeline "actions", "fills")
        //
        //     {      action => "confirmed",
        //            $amount => "1000000000000000000",
        //          intentID => "MARKET_INTENT:90jjw2s7gj90jjw2s7gkjjw2s7gl",
        //            txHash => "0x043488fdc3f995bf9e632a32424e41ed126de90f8cb340a1ff006c2a74ca8336",
        //       blockNumber => "8094822",
        //         $timestamp => "1532261686"                                                          }
        //
        $timestamp = $this->safe_integer($trade, 'lastUpdated');
        $price = $this->safe_float($trade, 'price');
        $id = $this->safe_string($trade, 'id');
        $side = $this->safe_string($trade, 'side');
        $symbol = null;
        $base = null;
        if ($market !== null) {
            $symbol = $market['symbol'];
            $base = $market['base'];
        }
        $baseDecimals = $this->safe_integer($this->options['decimals'], $base, 18);
        $amount = $this->fromWei ($this->safe_string($trade, 'amount'), 'ether', $baseDecimals);
        $cost = null;
        if ($amount !== null && $price !== null) {
            $cost = $amount * $price;
        }
        $takerOrMaker = 'taker';
        return array (
            'id' => $id,
            'order' => $id,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'symbol' => $symbol,
            'type' => null,
            'side' => $side,
            'takerOrMaker' => $takerOrMaker,
            'price' => $price,
            'amount' => $amount,
            'cost' => $cost,
            'fee' => null,
            'info' => $trade,
        );
    }

    public function fetch_trades ($symbol, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array (
            'baseTokenAddress' => $market['baseId'],
            'quoteTokenAddress' => $market['quoteId'],
        );
        $response = $this->publicGetTradeHistory (array_merge ($request, $params));
        //
        //     array (
        //       {
        //         "id" => "37212",
        //         "transactionHash" => "0x5e6e75e1aa681b51b034296f62ac19be7460411a2ad94042dd8ba637e13eac0c",
        //         "amount" => "300000000000000000",
        //         "price" => "0.00052718",
        //         "status" => "filled", // filled | settled | failed
        //         "lastUpdated" => "1520265048996"
        //       }
        //     )
        //
        return $this->parse_trades($response, $market, $since, $limit);
    }

    public function create_order ($symbol, $type, $side, $amount, $price = null, $params = array ()) {
        $errorMessage = $this->id . ' createOrder() requires `exchange.walletAddress` and `exchange.privateKey`. The .walletAddress should be a "0x"-prefixed hexstring like "0xbF2d65B3b2907214EEA3562f21B80f6Ed7220377". The .privateKey for that wallet should be a "0x"-prefixed hexstring like "0xe4f40d465efa94c98aec1a51f574329344c772c1bce33be07fa20a56795fdd09".';
        if (!$this->walletAddress || (mb_strpos ($this->walletAddress, '0x') !== 0)) {
            throw new InvalidAddress ($errorMessage);
        }
        if (!$this->privateKey || (mb_strpos ($this->privateKey, '0x') !== 0)) {
            throw new InvalidAddress ($errorMessage);
        }
        $orderParams = $this->fetch_order_params_to_sign ($symbol, $type, $side, $amount, $price, $params);
        $unsignedOrder = $orderParams['unsignedZeroExOrder'];
        if ($unsignedOrder === null) {
            throw new OrderNotFillable ($this->id . ' ' . $type . ' $order to ' . $side . ' ' . $symbol . ' is not fillable at the moment');
        }
        $signedOrder = $this->signZeroExOrderV2 ($unsignedOrder, $this->privateKey);
        $id = $this->safe_string($signedOrder, 'orderHash');
        $this->post_signed_order ($signedOrder, $orderParams, $params);
        $order = $this->fetch_order($id);
        $order['type'] = $type;
        return $order;
    }

    public function fetch_order_params_to_sign ($symbol, $type, $side, $amount, $price = null, $params = array ()) {
        if ($side !== 'buy' && $side !== 'sell') {
            throw new ExchangeError ($side . ' is not valid $side param. Use \'buy\' or \'sell\'');
        }
        if ($type !== 'market' && $type !== 'limit') {
            throw new ExchangeError ($type . ' is not valid $type param. Use \'market\' or \'limit\'');
        }
        if ($type === 'limit' && $price === null) {
            throw new ExchangeError ('Price is not provided for limit order');
        }
        $this->load_markets();
        $market = $this->market ($symbol);
        $baseDecimals = $this->safe_integer($this->options['decimals'], $market['base'], 18);
        $request = array (
            'walletAddress' => strtolower ($this->walletAddress), // Your Wallet Address
            'baseTokenAddress' => $market['baseId'], // Base token address
            'quoteTokenAddress' => $market['quoteId'], // Quote token address
            'side' => $side, // "buy" or "sell"
            'amount' => $this->toWei ($this->amount_to_precision($symbol, $amount), 'ether', $baseDecimals), // Base token $amount in wei
        );
        $method = null;
        if ($type === 'limit') {
            $method = 'privateGetOrderUnsigned';
            $request['price'] = $this->price_to_precision($symbol, $price);
        } else if ($type === 'market') {
            $method = 'privateGetOrderUnsignedMarket';
        } else {
            throw new ExchangeError ('Unsupported order $type => ' . $type);
        }
        $response = $this->$method (array_merge ($request, $params));
        return $response;
    }

    public function post_signed_order ($signedOrder, $requestParams, $params = array ()) {
        $request = $requestParams;
        $request['signedZeroExOrder'] = $signedOrder;
        $request = $this->omit ($request, 'unsignedZeroExOrder');
        $response = $this->privatePostOrder (array_merge ($request, $params));
        return $response;
    }

    public function cancel_order ($id, $symbol = null, $params = array ()) {
        $this->load_markets();
        $request = array (
            'orderHash' => $id,
        );
        $response = $this->privateDeleteOrderOrderHash (array_merge ($request, $params));
        //
        //     {
        //       "canceledOrder" => {
        //         "orderHash" => "0x3d6b287c1dc79262d2391ae2ca9d050fdbbab2c8b3180e4a46f9f321a7f1d7a9",
        //         "amount" => "100000000000"
        //       }
        //     }
        //
        $market = null;
        if ($symbol !== null) {
            $market = $this->market ($symbol);
        }
        return array_merge ($this->parse_order($response['canceledOrder'], $market), array (
            'status' => 'canceled',
        ));
    }

    public function cancel_all_orders ($params = array ()) {
        $response = $this->privateDeleteOrder ($params);
        //
        //     [{
        //       "canceledOrder" => array (
        //         "orderHash" => "0x3d6b287c1dc79262d2391ae2ca9d050fdbbab2c8b3180e4a46f9f321a7f1d7a9",
        //         "amount" => "100000000000"
        //       }
        //     )]
        //
        return $response;
    }

    public function parse_order ($order, $market = null) {
        $zeroExOrder = $this->safe_value($order, 'zeroExOrder');
        $id = $this->safe_string($order, 'orderHash');
        if (($id === null) && ($zeroExOrder !== null)) {
            $id = $this->safe_string($zeroExOrder, 'orderHash');
        }
        $side = $this->safe_string($order, 'side');
        $type = $this->safe_string($order, 'type'); // injected from outside
        $timestamp = $this->safe_integer($order, 'creationTimestamp');
        $symbol = null;
        $baseId = $this->safe_string($order, 'baseTokenAddress');
        $quoteId = $this->safe_string($order, 'quoteTokenAddress');
        $marketId = null;
        if ($baseId !== null && $quoteId !== null) {
            $marketId = $baseId . '/' . $quoteId;
        }
        $market = $this->safe_value($this->markets_by_id, $marketId, $market);
        $base = null;
        if ($market !== null) {
            $symbol = $market['symbol'];
            $base = $market['base'];
        }
        $baseDecimals = $this->safe_integer($this->options['decimals'], $base, 18);
        $price = $this->safe_float($order, 'price');
        $filledAmount = $this->fromWei ($this->safe_string($order, 'filledAmount'), 'ether', $baseDecimals);
        $settledAmount = $this->fromWei ($this->safe_string($order, 'settledAmount'), 'ether', $baseDecimals);
        $confirmedAmount = $this->fromWei ($this->safe_string($order, 'confirmedAmount'), 'ether', $baseDecimals);
        $failedAmount = $this->fromWei ($this->safe_string($order, 'failedAmount'), 'ether', $baseDecimals);
        $deadAmount = $this->fromWei ($this->safe_string($order, 'deadAmount'), 'ether', $baseDecimals);
        $prunedAmount = $this->fromWei ($this->safe_string($order, 'prunedAmount'), 'ether', $baseDecimals);
        $amount = $this->fromWei ($this->safe_string($order, 'initialAmount'), 'ether', $baseDecimals);
        $filled = $this->sum ($filledAmount, $settledAmount, $confirmedAmount);
        $remaining = null;
        $lastTradeTimestamp = null;
        $timeline = $this->safe_value($order, 'timeline');
        $trades = null;
        $status = null;
        if ($timeline !== null) {
            $numEvents = is_array ($timeline) ? count ($timeline) : 0;
            if ($numEvents > 0) {
                $timelineEventsGroupedByAction = $this->group_by($timeline, 'action');
                if (is_array ($timelineEventsGroupedByAction) && array_key_exists ('error', $timelineEventsGroupedByAction)) {
                    $status = 'failed';
                }
                if (is_array ($timelineEventsGroupedByAction) && array_key_exists ('filled', $timelineEventsGroupedByAction)) {
                    $fillEvents = $this->safe_value($timelineEventsGroupedByAction, 'filled');
                    $numFillEvents = is_array ($fillEvents) ? count ($fillEvents) : 0;
                    $lastTradeTimestamp = $this->safe_integer($fillEvents[$numFillEvents - 1], 'timestamp');
                    $lastTradeTimestamp = ($lastTradeTimestamp !== null) ? $lastTradeTimestamp * 1000 : $lastTradeTimestamp;
                    $trades = array ();
                    for ($i = 0; $i < $numFillEvents; $i++) {
                        $trade = $this->parse_trade(array_merge ($fillEvents[$i], array (
                            'price' => $price,
                        )), $market);
                        $trades[] = array_merge ($trade, array (
                            'order' => $id,
                            'type' => $type,
                            'side' => $side,
                        ));
                    }
                }
            }
        }
        $cost = null;
        if ($filled !== null) {
            if ($remaining === null) {
                if ($amount !== null) {
                    $remaining = $amount - $filled;
                }
            }
            if ($price !== null) {
                $cost = $filled * $price;
            }
        }
        $fee = null;
        $feeCost = $this->safe_string($order, 'feeAmount');
        if ($feeCost !== null) {
            $feeOption = $this->safe_string($order, 'feeOption');
            $feeCurrency = null;
            if ($feeOption === 'feeInNative') {
                if ($market !== null) {
                    $feeCurrency = $market['base'];
                }
            } else if ($feeOption === 'feeInZRX') {
                $feeCurrency = 'ZRX';
            } else {
                throw new NotSupported ($this->id . ' encountered an unsupported $order $fee option => ' . $feeOption);
            }
            $feeDecimals = $this->safe_integer($this->options['decimals'], $feeCurrency, 18);
            $fee = array (
                'cost' => $this->fromWei ($feeCost, 'ether', $feeDecimals),
                'currency' => $feeCurrency,
            );
        }
        $amountPrecision = $market ? $market['precision']['amount'] : 8;
        if ($remaining !== null) {
            if ($status === null) {
                $status = 'open';
                $rest = $remaining - $failedAmount - $deadAmount - $prunedAmount;
                if ($rest < pow (10, -$amountPrecision)) {
                    $status = ($filled < $amount) ? 'canceled' : 'closed';
                }
            }
        }
        $result = array (
            'info' => $order,
            'id' => $id,
            'symbol' => $symbol,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'lastTradeTimestamp' => $lastTradeTimestamp,
            'type' => $type,
            'side' => $side,
            'price' => $price,
            'cost' => $cost,
            'amount' => $amount,
            'remaining' => $remaining,
            'filled' => $filled,
            'status' => $status,
            'fee' => $fee,
            'trades' => $trades,
        );
        return $result;
    }

    public function fetch_open_order ($id, $symbol = null, $params = array ()) {
        $method = $this->options['fetchOrderMethod'];
        return $this->$method ($id, $symbol, array_merge (array (
            'openAmount' => 1,
        ), $params));
    }

    public function fetch_closed_order ($id, $symbol = null, $params = array ()) {
        $method = $this->options['fetchOrderMethod'];
        return $this->$method ($id, $symbol, array_merge ($params));
    }

    public function fetch_order_from_history ($id, $symbol = null, $params = array ()) {
        $orders = $this->fetch_orders($symbol, null, null, array_merge (array (
            'orderHash' => $id,
        ), $params));
        $ordersById = $this->index_by($orders, 'id');
        if (is_array ($ordersById) && array_key_exists ($id, $ordersById))
            return $ordersById[$id];
        throw new OrderNotFound ($this->id . ' could not find order ' . $id . ' in order history');
    }

    public function fetch_order_by_id ($id, $symbol = null, $params = array ()) {
        $this->load_markets();
        $request = array (
            'orderHash' => $id,
        );
        $response = $this->publicGetOrderOrderHash (array_merge ($request, $params));
        //  {
        //   baseTokenAddress => '0xb18845c260f680d5b9d84649638813e342e4f8c9',
        //   quoteTokenAddress => '0x6ff6c0ff1d68b964901f986d4c9fa3ac68346570',
        //   side => 'sell',
        //   price => '30',
        //   feeTokenAddress => '0x6ff6c0ff1d68b964901f986d4c9fa3ac68346570',
        //   amount => '500000000000000000',
        //   created => '1547194003',
        //   expires => '1549786003',
        //   zeroExOrder => array (
        //     salt => '71810414258284992779348693906799008280152689028521273772736250669496045815907',
        //     maker => '0xfa1a3371bcbfcf3deaa8a6f67784bfbe5b886d7f',
        //     taker => '0x77b18613579d49f252bd237ef113884eb37a7090',
        //     makerFee => '0',
        //     takerFee => '0',
        //     orderHash => '0x368540323af55868dd9ce6ac248e6a91d9b7595252ca061c4ada7612b09af1cf',
        //     feeRecipient => '0x88a64b5e882e5ad851bea5e7a3c8ba7c523fecbe',
        //     makerTokenAmount => '500000000000000000',
        //     takerTokenAmount => '14845250714350000000',
        //     makerTokenAddress => '0xb18845c260f680d5b9d84649638813e342e4f8c9',
        //     takerTokenAddress => '0x6ff6c0ff1d68b964901f986d4c9fa3ac68346570',
        //     exchangeContractAddress => '0x35dd2932454449b14cee11a94d3674a936d5d7b2',
        //     expirationUnixTimestampSec => '1549789602'
        //   ),
        //   feeAmount => '154749285650000000',
        //   feeOption => 'feeInNative',
        //   cancelAfter => '1549786003'
        //  }
        return $this->parse_order($response);
    }

    public function fetch_order ($id, $symbol = null, $params = array ()) {
        $request = array (
            'orderHash' => $id,
        );
        $orders = $this->fetch_orders($symbol, null, null, array_merge ($request, $params));
        $numOrders = is_array ($orders) ? count ($orders) : 0;
        if ($numOrders !== 1) {
            throw new OrderNotFound ($this->id . ' order ' . $id . ' not found');
        }
        return $orders[0];
    }

    public function fetch_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $request = array ();
        $market = null;
        if ($symbol !== null) {
            $market = $this->market ($symbol);
            $request['baseTokenAddress'] = $market['baseId'];
            $request['quoteTokenAddress'] = $market['quoteId'];
        }
        if ($limit !== null) {
            $request['limit'] = $limit;
        }
        $response = $this->privateGetOrderHistory (array_merge ($request, $params));
        //
        //     array (
        //       {
        //         "orderHash" => "0x94629386298dee69ae63cd3e414336ae153b3f02cffb9ffc53ad71e166615618",
        //         "baseTokenAddress" => "0x323b5d4c32345ced77393b3530b1eed0f346429d",
        //         "quoteTokenAddress" => "0xef7fff64389b814a946f3e92105513705ca6b990",
        //         "side" => "buy",
        //         "openAmount" => "10000000000000000000",
        //         "filledAmount" => "0",
        //         "reservedAmount" => "0",
        //         "settledAmount" => "0",
        //         "confirmedAmount" => "0",
        //         "deadAmount" => "0",
        //         "price" => "0.00050915",
        //         "timeline" => array (
        //           {
        //             "action" => "placed",
        //             "amount" => "10000000000000000000",
        //             "timestamp" => "1512929327792"
        //           }
        //         )
        //       }
        //     )
        //
        return $this->parse_orders($response, null, $since, $limit);
    }

    public function fetch_open_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        return $this->fetch_orders($symbol, $since, $limit, array_merge (array (
            'openAmount' => 1, // returns open orders with remaining openAmount >= 1
        ), $params));
    }

    public function fetch_closed_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        return $this->fetch_orders($symbol, $since, $limit, array_merge (array (
            'openAmount' => 0, // returns closed orders with remaining openAmount === 0
        ), $params));
    }

    public function sign ($path, $api = 'public', $method = 'GET', $params = array (), $headers = null, $body = null) {
        $url = $this->urls['api'] . '/' . $this->version . '/' . $this->implode_params($path, $params);
        $query = $this->omit ($params, $this->extract_params($path));
        if ($api === 'private') {
            $this->check_required_credentials();
            $timestamp = (string) $this->seconds ();
            $prehash = $this->apiKey . $timestamp . $method;
            if ($method === 'POST') {
                $body = $this->json ($query);
                $prehash .= $body;
            } else {
                if ($query) {
                    $url .= '?' . $this->urlencode ($query);
                }
                $prehash .= $this->json (array ());
            }
            $signature = $this->hmac ($this->encode ($prehash), $this->encode ($this->secret), 'sha256', 'base64');
            $headers = array (
                'TOX-ACCESS-KEY' => $this->apiKey,
                'TOX-ACCESS-SIGN' => $signature,
                'TOX-ACCESS-TIMESTAMP' => $timestamp,
                'Content-Type' => 'application/json',
            );
        } else if ($api === 'public') {
            if ($query) {
                $url .= '?' . $this->urlencode ($query);
            }
        }
        return array ( 'url' => $url, 'method' => $method, 'body' => $body, 'headers' => $headers );
    }

    public function handle_errors ($httpCode, $reason, $url, $method, $headers, $body, $response) {
        if (gettype ($body) !== 'string')
            return; // fallback to default error handler
        if (strlen ($body) < 2)
            return; // fallback to default error handler
        // code 401 and plain $body 'Authentication failed' (with single quotes)
        // this error is sent if you do not submit a proper Content-Type
        if ($body === "'Authentication failed'") {
            throw new AuthenticationError ($this->id . ' ' . $body);
        }
        if (($body[0] === '{') || ($body[0] === '[')) {
            $message = $this->safe_string($response, 'message');
            if ($message !== null) {
                //
                // array ("$message":"Schema validation failed for 'query'","errors":[{"name":"required","argument":"startTime","$message":"requires property \"startTime\"","instance":array ("baseTokenAddress":"0x6ff6c0ff1d68b964901f986d4c9fa3ac68346570","quoteTokenAddress":"0xd0a1e359811322d97991e03f863a0c30c2cf029c","interval":"300"),"property":"instance")]}
                // array ("$message":"Logic validation failed for 'query'","errors":[{"$message":"startTime should be between 0 and current date","type":"startTime")]}
                // array ("$message":"Order not found","errors":array ())
                // array ("$message":"Orderbook exhausted for intent MARKET_INTENT:8yjjzd8b0e8yjjzd8b0fjjzd8b0g")
                // array ("$message":"Intent validation failed.","errors":[{"$message":"Greater than available wallet balance.","type":"walletBaseTokenAmount")]}
                // array ("$message":"Schema validation failed for 'body'","errors":[{"name":"anyOf","argument":["[subschema 0]","[subschema 1]","[subschema 2]"],"$message":"is not any of [subschema 0],[subschema 1],[subschema 2]","instance":array ("signedTargetOrder":array ("error":array ("$message":"Unsigned target order validation failed.","errors":[array ("$message":"Greater than available wallet balance.","type":"walletBaseTokenAmount")]),"maker":"0x1709c02cd7327d391a39a7671af8a91a1ef8a47b","orderHash":"0xda007ea8b5eca71ac96fe4072f7c1209bb151d898a9cc89bbeaa594f0491ee49","ecSignature":array ("v":27,"r":"0xb23ce6c4a7b5d51d77e2d00f6d1d472a3b2e72d5b2be1510cfeb122f9366b79e","s":"0x07d274e6d7a00b65fc3026c2f9019215b1e47a5ac4d1f05e03f90550d27109be"))),"property":"instance")]}
                // array ("$message":"Schema validation failed for 'params'","errors":[{"name":"pattern","argument":"^0x[0-9a-fA-F]{64}$","$message":"does not match pattern \"^0x[0-9a-fA-F]{64}$\"","instance":"1","property":"instance.orderHash")]}
                //
                $feedback = $this->id . ' ' . $this->json ($response);
                $exact = $this->exceptions['exact'];
                if (is_array ($exact) && array_key_exists ($message, $exact))
                    throw new $exact[$message] ($feedback);
                $broad = $this->exceptions['broad'];
                $broadKey = $this->findBroadlyMatchedKey ($broad, $body);
                if ($broadKey !== null)
                    throw new $broad[$broadKey] ($feedback);
                throw new ExchangeError ($feedback); // unknown $message
            }
        }
    }
}
