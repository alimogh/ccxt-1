<?php

namespace ccxt;

use Exception as Exception; // a common import

class theocean extends Exchange {

    public function describe () {
        return array_replace_recursive (parent::describe (), array (
            'id' => 'theocean',
            'name' => 'The Ocean',
            'countries' => array ( 'US' ),
            'rateLimit' => 3000,
            'version' => 'v0',
            'certified' => true,
            'parseJsonResponse' => false,
            // add GET https://api.staging.theocean.trade/api/v0/candlesticks/intervals to fetchMarkets
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
                'fetchOpenOrders' => true,
                'fetchClosedOrders' => true,
            ),
            'urls' => array (
                'logo' => 'https://user-images.githubusercontent.com/1294454/43103756-d56613ce-8ed7-11e8-924e-68f9d4bcacab.jpg',
                'api' => 'https://api.theocean.trade/api',
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
                    ),
                ),
                'private' => array (
                    'get' => array (
                        'available_balance',
                        'user_history',
                    ),
                    'post' => array (
                        'limit_order/reserve',
                        'limit_order/place',
                        'market_order/reserve',
                        'market_order/place',
                    ),
                    'delete' => array (
                        'order/{orderHash}',
                        'orders',
                    ),
                ),
            ),
            'exceptions' => array (
                "Schema validation failed for 'query'" => '\\ccxt\\ExchangeError', // array ( "message" => "Schema validation failed for 'query'", "errors" => ... )
                "Logic validation failed for 'query'" => '\\ccxt\\ExchangeError', // array ( "message" => "Logic validation failed for 'query'", "errors" => ... )
                "Schema validation failed for 'body'" => '\\ccxt\\ExchangeError', // array ( "message" => "Schema validation failed for 'body'", "errors" => ... )
                "Logic validation failed for 'body'" => '\\ccxt\\ExchangeError', // array ( "message" => "Logic validation failed for 'body'", "errors" => ... )
                'Order not found' => '\\ccxt\\OrderNotFound', // array ("message":"Order not found","errors":...)
                'Greater than available wallet balance.' => '\\ccxt\\InsufficientFunds', // array ("message":"Greater than available wallet balance.","type":"walletBaseTokenAmount")
            ),
            'options' => array (
                'fetchOrderMethod' => 'fetch_order_from_history',
            ),
        ));
    }

    public function calculate_fee ($symbol, $type, $side, $amount, $price, $takerOrMaker = 'taker', $params = array ()) {
        $market = $this->markets[$symbol];
        $key = 'quote';
        $rate = $market[$takerOrMaker];
        $cost = floatval ($this->cost_to_precision($symbol, $amount * $rate));
        if ($side === 'sell') {
            $cost *= $price;
        } else {
            $key = 'base';
        }
        return array (
            'type' => $takerOrMaker,
            'currency' => $market[$key],
            'rate' => $rate,
            'cost' => $cost,
        );
    }

    public function fetch_markets () {
        $markets = $this->publicGetTokenPairs ();
        //
        //     array (
        //       {
        //         "$baseToken" => array (
        //           "address" => "0xa8e9fa8f91e5ae138c74648c9c304f1c75003a8d",
        //           "$symbol" => "ZRX",
        //           "decimals" => "18",
        //           "minAmount" => "1000000000000000000",
        //           "maxAmount" => "100000000000000000000000",
        //           "$precision" => "18"
        //         ),
        //         "$quoteToken" => {
        //           "address" => "0xc00fd9820cd2898cc4c054b7bf142de637ad129a",
        //           "$symbol" => "WETH",
        //           "decimals" => "18",
        //           "minAmount" => "5000000000000000",
        //           "maxAmount" => "100000000000000000000",
        //           "$precision" => "18"
        //         }
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
            $precision = array (
                'amount' => $this->safe_integer($baseToken, 'precision'),
                'price' => $this->safe_integer($quoteToken, 'precision'),
            );
            $amountLimits = array (
                'min' => $this->fromWei ($this->safe_string($baseToken, 'minAmount')),
                'max' => $this->fromWei ($this->safe_string($baseToken, 'maxAmount')),
            );
            $priceLimits = array (
                'min' => null,
                'max' => null,
            );
            $costLimits = array (
                'min' => $this->fromWei ($this->safe_string($quoteToken, 'minAmount')),
                'max' => $this->fromWei ($this->safe_string($quoteToken, 'maxAmount')),
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
        return array (
            $this->safe_integer($ohlcv, 'startTime') * 1000,
            $this->safe_float($ohlcv, 'open'),
            $this->safe_float($ohlcv, 'high'),
            $this->safe_float($ohlcv, 'low'),
            $this->safe_float($ohlcv, 'close'),
            $this->fromWei ($this->safe_string($ohlcv, 'baseVolume')),
            // $this->safe_string($ohlcv, 'quoteVolume'),
        );
    }

    public function fetch_ohlcv ($symbol, $timeframe = '5m', $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array (
            'baseTokenAddress' => $market['baseId'],
            'quoteTokenAddress' => $market['quoteId'],
            'interval' => $this->timeframes[$timeframe],
            // 'endTime' => endTime, // (optional) Snapshot end time
        );
        if ($since === null) {
            throw new ExchangeError ($this->id . ' fetchOHLCV requires a $since argument');
        }
        $request['startTime'] = intval ($since / 1000);
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
        $response = $this->privateGetAvailableBalance (array_merge ($request, $params));
        //
        //     {
        //       "availableBalance" => "1001006594219628829207"
        //     }
        //
        $balance = $this->fromWei ($this->safe_string($response, 'availableBalance'));
        return array (
            'free' => $balance,
            'used' => 0,
            'total' => null,
        );
    }

    public function fetch_balance ($params = array ()) {
        if (!$this->walletAddress || (mb_strpos ($this->walletAddress, '0x') !== 0)) {
            throw new InvalidAddress ($this->id . ' fetchBalance() requires the .walletAddress to be a "0x"-prefixed hexstring like "0xbF2d65B3b2907214EEA3562f21B80f6Ed7220377"');
        }
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

    public function parse_bid_ask ($bidask, $priceKey = 0, $amountKey = 1) {
        $price = floatval ($bidask[$priceKey]);
        $amount = $this->fromWei ($bidask[$amountKey]);
        // return array ( $price, $amount, $bidask );
        return array ( $price, $amount );
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
        //         {
        //           "orderHash" => "0x94629386298dee69ae63cd3e414336ae153b3f02cffb9ffc53ad71e166615618",
        //           "price" => "0.00050915",
        //           "availableAmount" => "100000000000000000000",
        //           "creationTimestamp" => "1512929327792",
        //           "expirationTimestampInSec" => "1534449466"
        //         }
        //       ),
        //       "asks" => array (
        //         {
        //           "orderHash" => "0x94629386298dee69ae63cd3e414336ae153b3f02cffb9ffc53ad71e166615618",
        //           "price" => "0.00054134",
        //           "availableAmount" => "100000000000000000000",
        //           "creationTimestamp" => "1512929323784",
        //           "expirationTimestampInSec" => "1534449466"
        //         }
        //       )
        //     }
        //
        return $this->parse_order_book($response, null, 'bids', 'asks', 'price', 'availableAmount');
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
        if ($market !== null) {
            $symbol = $market['symbol'];
        }
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
            'baseVolume' => $this->fromWei ($this->safe_string($ticker, 'volume')),
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
        if ($timestamp === null) {
            $timestamp = $this->safe_integer($trade, 'timestamp');
        }
        if ($timestamp !== null) {
            // their timestamps are in seconds, mostly
            $timestamp = $timestamp * 1000;
        }
        $price = $this->safe_float($trade, 'price');
        $orderId = $this->safe_string($trade, 'order');
        $id = $this->safe_string_2($trade, 'transactionHash', 'txHash');
        $symbol = null;
        if ($market !== null) {
            $symbol = $market['symbol'];
        }
        $amount = $this->fromWei ($this->safe_string($trade, 'amount'));
        $cost = null;
        if ($amount !== null) {
            if ($price !== null) {
                $cost = $amount * $price;
            }
        }
        $takerOrMaker = 'taker';
        $fee = null;
        // $fee = $this->calculate_fee($symbol, type, side, $amount, $price, $takerOrMaker);
        return array (
            'id' => $id,
            'order' => $orderId,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'symbol' => $symbol,
            'type' => null,
            'side' => null,
            'takerOrMaker' => $takerOrMaker,
            'price' => $price,
            'amount' => $amount,
            'cost' => $cost,
            'fee' => $fee,
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

    public function price_to_precision ($symbol, $price) {
        return $this->decimal_to_precision($price, ROUND, $this->markets[$symbol]['precision']['price'], $this->precisionMode);
    }

    public function create_order ($symbol, $type, $side, $amount, $price = null, $params = array ()) {
        $errorMessage = $this->id . ' createOrder() requires `exchange.walletAddress` and `exchange.privateKey`. The .walletAddress should be a "0x"-prefixed hexstring like "0xbF2d65B3b2907214EEA3562f21B80f6Ed7220377". The .privateKey for that wallet should be a "0x"-prefixed hexstring like "0xe4f40d465efa94c98aec1a51f574329344c772c1bce33be07fa20a56795fdd09".';
        if (!$this->walletAddress || (mb_strpos ($this->walletAddress, '0x') !== 0)) {
            throw new InvalidAddress ($errorMessage);
        }
        if (!$this->privateKey || (mb_strpos ($this->privateKey, '0x') !== 0)) {
            throw new InvalidAddress ($errorMessage);
        }
        $this->load_markets();
        $makerOrTaker = $this->safe_string($params, 'makerOrTaker');
        $isMarket = ($type === 'market');
        $isMakerOrTakerUndefined = ($makerOrTaker === null);
        $isTaker = ($makerOrTaker === 'taker');
        $isMaker = ($makerOrTaker === 'maker');
        if ($isMarket && !$isMakerOrTakerUndefined && !$isTaker) {
            throw new InvalidOrder ($this->id . ' createOrder() ' . $type . ' order $type cannot be a ' . $makerOrTaker . '. The createOrder() $method of ' . $type . ' $type can be used with $taker orders only.');
        }
        $query = $this->omit ($params, 'makerOrTaker');
        $timestamp = $this->milliseconds ();
        $market = $this->market ($symbol);
        $reserveRequest = array (
            'walletAddress' => strtolower ($this->walletAddress), // Your Wallet Address
            'baseTokenAddress' => $market['baseId'], // Base token address
            'quoteTokenAddress' => $market['quoteId'], // Quote token address
            'side' => $side, // "buy" or "sell"
            'orderAmount' => $this->toWei ($this->amount_to_precision($symbol, $amount)), // Base token $amount in wei
            'feeOption' => 'feeInNative', // Fees can be paid in native currency ("feeInNative"), or ZRX ("feeInZRX")
        );
        if ($type === 'limit') {
            $reserveRequest['price'] = $this->price_to_precision($symbol, $price); // Price denominated in quote tokens (limit orders only)
        }
        $method = 'privatePost' . $this->capitalize ($type) . 'Order';
        $reserveMethod = $method . 'Reserve';
        $reserveResponse = $this->$reserveMethod (array_merge ($reserveRequest, $query));
        //
        // ---- $market orders -------------------------------------------------
        //
        // $reserveResponse =
        //     {       matchingOrderID =>   "MARKET_INTENT:90jjw2s7gj90jjw2s7gkjjw2s7gl",
        //       $unsignedMatchingOrder => {                      $maker => "",
        //                                                     $taker => "0x00ba938cc0df182c25108d7bf2ee3d37bce07513",
        //                                         makerTokenAddress => "0xd0a1e359811322d97991e03f863a0c30c2cf029c",
        //                                         takerTokenAddress => "0x6ff6c0ff1d68b964901f986d4c9fa3ac68346570",
        //                                          makerTokenAmount => "27100000000000000",
        //                                          takerTokenAmount => "874377028175459241",
        //                                                  makerFee => "0",
        //                                                  takerFee => "0",
        //                                expirationUnixTimestampSec => "1534809575",
        //                                              feeRecipient => "0x88a64b5e882e5ad851bea5e7a3c8ba7c523fecbe",
        //                                                      salt => "3610846705800197954038657082705100176266402776121341340841167002345284333867",
        //                                   exchangeContractAddress => "0x90fe2af704b34e0224bf2299c838e04d4dcf1364"                                    } }
        //
        // ---- limit orders --------------------------------------------------
        //
        // 1. if the order is completely fillable:
        //    . $unsignedMatchingOrder will be present
        //    - $unsignedTargetOrder will be missing
        // 2. if the order is partially fillable:
        //    . $unsignedMatchingOrder and
        //    . unsignedTarget order will be present
        // 3. if the order is not fillable at the moment:
        //    . $unsignedTargetOrder will be present
        //    - $unsignedMatchingOrder will be missing
        // In other words, $unsignedMatchingOrder is only present
        // if there is some fillable $amount in the order book.
        //
        // Note => ecSignature is empty at this point and missing in the actual
        // response, there's no need for it here at this point anyway.
        //
        // $reserveResponse =
        //     { $unsignedTargetOrder => {                      $maker => "",
        //                                                   $taker => "0x00ba938cc0df182c25108d7bf2ee3d37bce07513",
        //                                       makerTokenAddress => "0xd0a1e359811322d97991e03f863a0c30c2cf029c",
        //                                       takerTokenAddress => "0x6ff6c0ff1d68b964901f986d4c9fa3ac68346570",
        //                                        makerTokenAmount => "2700000000000000",
        //                                        takerTokenAmount => "937912044575392743",
        //                                                makerFee => "0",
        //                                                takerFee => "0",
        //                              expirationUnixTimestampSec => "1534813319",
        //                                            feeRecipient => "0x88a64b5e882e5ad851bea5e7a3c8ba7c523fecbe",
        //                                                    salt => "54933934472162523007303314622614098849759889305199720392701919179357703099693",
        //                                 exchangeContractAddress => "0x90fe2af704b34e0224bf2299c838e04d4dcf1364"                                     } }
        //
        // $reserveResponse =
        //     {
        //       "$unsignedTargetOrder" => {
        //         "exchangeContractAddress" => "0x516bdc037df84d70672b2d140835833d3623e451",
        //         "$maker" => "",
        //         "$taker" => "0x00ba938cc0df182c25108d7bf2ee3d37bce07513",
        //         "makerTokenAddress" => "0x7cc7fdd065cfa9c7f4f6a3c1bfc6dfcb1a3177aa",
        //         "takerTokenAddress" => "0x17f15936ef3a2da5593033f84487cbe9e268f02f",
        //         "feeRecipient" => "0x88a64b5e882e5ad851bea5e7a3c8ba7c523fecbe",
        //         "makerTokenAmount" => "10000000000000000000",
        //         "takerTokenAmount" => "10000000000000000000",
        //         "makerFee" => "0",
        //         "takerFee" => "0",
        //         "expirationUnixTimestampSec" => "525600",
        //         "salt" => "37800593840622773016017857006417214310534675667008850948421364357744823963318",
        //         "ecSignature" => array (
        //           "v" => 0,
        //           "r" => "",
        //           "s" => ""
        //         }
        //       ),
        //       "$unsignedMatchingOrder" => {
        //         "exchangeContractAddress" => "0x516bdc037df84d70672b2d140835833d3623e451",
        //         "$maker" => "",
        //         "$taker" => "0x00ba938cc0df182c25108d7bf2ee3d37bce07513",
        //         "makerTokenAddress" => "0x7cc7fdd065cfa9c7f4f6a3c1bfc6dfcb1a3177aa",
        //         "takerTokenAddress" => "0x17f15936ef3a2da5593033f84487cbe9e268f02f",
        //         "feeRecipient" => "0x88a64b5e882e5ad851bea5e7a3c8ba7c523fecbe",
        //         "makerTokenAmount" => "10000000000000000000",
        //         "takerTokenAmount" => "10000000000000000000",
        //         "makerFee" => "0",
        //         "takerFee" => "0",
        //         "expirationUnixTimestampSec" => "525600",
        //         "salt" => "37800593840622773016017857006417214310534675667008850948421364357744823963318",
        //         "ecSignature" => array (
        //           "v" => 0,
        //           "r" => "",
        //           "s" => ""
        //         }
        //       ),
        //       "matchingOrderID" => "MARKET_INTENT:8ajjh92s1r8ajjh92s1sjjh92s1t"
        //     }
        //
        // --------------------------------------------------------------------
        $unsignedMatchingOrder = $this->safe_value($reserveResponse, 'unsignedMatchingOrder');
        $unsignedTargetOrder = $this->safe_value($reserveResponse, 'unsignedTargetOrder');
        $isUnsignedMatchingOrderDefined = ($unsignedMatchingOrder !== null);
        $isUnsignedTargetOrderDefined = ($unsignedTargetOrder !== null);
        $makerAddress = array (
            'maker' => strtolower ($this->walletAddress),
        );
        $placeRequest = array ();
        $signedMatchingOrder = null;
        $signedTargetOrder = null;
        if ($isUnsignedMatchingOrderDefined && $isUnsignedTargetOrderDefined) {
            if ($isTaker) {
                $signedMatchingOrder = $this->signZeroExOrder (array_merge ($unsignedMatchingOrder, $makerAddress), $this->privateKey);
                $placeRequest['signedMatchingOrder'] = $signedMatchingOrder;
                $placeRequest['matchingOrderID'] = $reserveResponse['matchingOrderID'];
            } else if ($isMaker) {
                $signedTargetOrder = $this->signZeroExOrder (array_merge ($unsignedTargetOrder, $makerAddress), $this->privateKey);
                $placeRequest['signedTargetOrder'] = $signedTargetOrder;
            } else {
                $signedMatchingOrder = $this->signZeroExOrder (array_merge ($unsignedMatchingOrder, $makerAddress), $this->privateKey);
                $placeRequest['signedMatchingOrder'] = $signedMatchingOrder;
                $placeRequest['matchingOrderID'] = $reserveResponse['matchingOrderID'];
                $signedTargetOrder = $this->signZeroExOrder (array_merge ($unsignedTargetOrder, $makerAddress), $this->privateKey);
                $placeRequest['signedTargetOrder'] = $signedTargetOrder;
            }
        } else if ($isUnsignedMatchingOrderDefined) {
            if ($isMaker) {
                throw new OrderImmediatelyFillable ($this->id . ' createOrder() ' . $type . ' order to ' . $side . ' ' . $symbol . ' is not fillable as a $maker order');
            } else {
                $signedMatchingOrder = $this->signZeroExOrder (array_merge ($unsignedMatchingOrder, $makerAddress), $this->privateKey);
                $placeRequest['signedMatchingOrder'] = $signedMatchingOrder;
                $placeRequest['matchingOrderID'] = $reserveResponse['matchingOrderID'];
            }
        } else if ($isUnsignedTargetOrderDefined) {
            if ($isTaker || $isMarket) {
                throw new OrderNotFillable ($this->id . ' createOrder() ' . $type . ' order to ' . $side . ' ' . $symbol . ' is not fillable as a $taker order');
            } else {
                $signedTargetOrder = $this->signZeroExOrder (array_merge ($unsignedTargetOrder, $makerAddress), $this->privateKey);
                $placeRequest['signedTargetOrder'] = $signedTargetOrder;
            }
        } else {
            throw new OrderNotFillable ($this->id . ' ' . $type . ' order to ' . $side . ' ' . $symbol . ' is not fillable at the moment');
        }
        $placeMethod = $method . 'Place';
        $placeResponse = $this->$placeMethod (array_merge ($placeRequest, $query));
        //
        // ---- $market orders -------------------------------------------------
        //
        // $placeResponse =
        //     { $matchingOrder => array ( transactionHash => "0x043488fdc3f995bf9e632a32424e41ed126de90f8cb340a1ff006c2a74ca8336",
        //                                 $amount => "1000000000000000000",
        //                              orderHash => "0xe815dc92933b68e7fc2b7102b8407ba7afb384e4080ac8d28ed42482933c5cf5"  ),
        //            parentID =>   "MARKET_INTENT:90jjw2s7gj90jjw2s7gkjjw2s7gl"                                              }
        //
        // ---- limit orders -------------------------------------------------
        //
        // $placeResponse =
        //     { $targetOrder => array (    $amount => "1000000000000000000",
        //                      orderHash => "0x517aef1ce5027328c40204833b624f04a54c913e93cffcdd500fe9252c535251" ),
        //          parentID =>   "MARKET_INTENT:90jjw50gpk90jjw50gpljjw50gpm"                                       }
        //
        // $placeResponse =
        //     {
        //         "$targetOrder" => array (
        //             "orderHash" => "0x94629386298dee69ae63cd3e414336ae153b3f02cffb9ffc53ad71e166615618",
        //             "$amount" => "100000000000"
        //         ),
        //         "$matchingOrder" => {
        //             "orderHash" => "0x3d6b287c1dc79262d2391ae2ca9d050fdbbab2c8b3180e4a46f9f321a7f1d7a9",
        //             "transactionHash" => "0x5e6e75e1aa681b51b034296f62ac19be7460411a2ad94042dd8ba637e13eac0c",
        //             "$amount" => "100000000000"
        //         }
        //     }
        //
        $matchingOrder = $this->safe_value($placeResponse, 'matchingOrder');
        $targetOrder = $this->safe_value($placeResponse, 'targetOrder');
        $orderParams = array (
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'price' => $price,
            'side' => $side,
            'filled' => 0,
            'status' => 'open',
        );
        $taker = null;
        $maker = null;
        if ($matchingOrder !== null) {
            $matchingOrder = array_merge ($signedMatchingOrder, $matchingOrder);
            $taker = $this->parse_order($matchingOrder, $market);
            $taker = array_merge ($taker, array (
                'type' => 'market',
                'remaining' => $taker['amount'],
            ), $orderParams);
            if ($isTaker)
                return $taker;
        }
        if ($targetOrder !== null) {
            $targetOrder = array_merge ($signedTargetOrder, $targetOrder);
            $maker = $this->parse_order($targetOrder, $market);
            $maker = array_merge ($maker, array (
                'type' => 'limit',
                'remaining' => $maker['amount'],
            ), $orderParams);
            if ($isMaker)
                return $maker;
        }
        return array (
            'info' => array_merge ($reserveResponse, $placeRequest, $placeResponse),
            'maker' => $maker,
            'taker' => $taker,
        );
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
        $response = $this->privateDeleteOrders ($params);
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

    public function parse_order_status ($status) {
        $statuses = array (
            'placed' => 'open',
            'reserved' => 'open',
            'filled' => 'closed',
            'settled' => 'closed',
            'confirmed' => 'closed',
            'returned' => 'open',
            'canceled' => 'canceled',
            'pruned' => 'failed',
        );
        if (is_array ($statuses) && array_key_exists ($status, $statuses)) {
            return $statuses[$status];
        }
        return $status;
    }

    public function parse_order ($order, $market = null) {
        //
        // fetchOrder, fetchOrderBook
        //
        //     {
        //       "baseTokenAddress" => "0x7cc7fdd065cfa9c7f4f6a3c1bfc6dfcb1a3177aa",
        //       "quoteTokenAddress" => "0x17f15936ef3a2da5593033f84487cbe9e268f02f",
        //       "$side" => "buy",
        //       "$amount" => "10000000000000000000",
        //       "$price" => "1.000",
        //       "created" => "1512929327792",
        //       "expires" => "1512929897118",
        //       "$zeroExOrder" => {
        //         "exchangeContractAddress" => "0x516bdc037df84d70672b2d140835833d3623e451",
        //         "maker" => "0x006dc83e5b21854d4afc44c9b92a91e0349dda13",
        //         "taker" => "0x00ba938cc0df182c25108d7bf2ee3d37bce07513",
        //         "makerTokenAddress" => "0x7cc7fdd065cfa9c7f4f6a3c1bfc6dfcb1a3177aa",
        //         "takerTokenAddress" => "0x17f15936ef3a2da5593033f84487cbe9e268f02f",
        //         "feeRecipient" => "0x88a64b5e882e5ad851bea5e7a3c8ba7c523fecbe",
        //         "makerTokenAmount" => "10000000000000000000",
        //         "takerTokenAmount" => "10000000000000000000",
        //         "makerFee" => "0",
        //         "takerFee" => "0",
        //         "expirationUnixTimestampSec" => "525600",
        //         "salt" => "37800593840622773016017857006417214310534675667008850948421364357744823963318",
        //         "orderHash" => "0x94629386298dee69ae63cd3e414336ae153b3f02cffb9ffc53ad71e166615618",
        //         "ecSignature" => {
        //           "v" => 28,
        //           "r" => "0x5307b6a69e7cba8583e1de39efb93a9ae1afc11849e79d99f462e49c18c4d6e4",
        //           "s" => "0x5950e82364227ccca95c70b47375e8911a2039d3040ba0684329634ebdced160"
        //         }
        //       }
        //     }
        //
        // fetchOrders
        //
        //     {              orderHash =>   "0xe815dc92933b68e7fc2b7102b8407ba7afb384e4080ac8d28ed42482933c5cf5",
        //             baseTokenAddress =>   "0x6ff6c0ff1d68b964901f986d4c9fa3ac68346570",
        //            quoteTokenAddress =>   "0xd0a1e359811322d97991e03f863a0c30c2cf029c",
        //                         $side =>   "buy",
        //                        $price =>   "0.0271",
        //                   $openAmount =>   "0",
        //               $reservedAmount =>   "0",
        //                 $filledAmount =>   "0",
        //                $settledAmount =>   "0",
        //              $confirmedAmount =>   "1000000000000000000",
        //                 $failedAmount =>   "0",
        //                   $deadAmount =>   "0",
        //                 $prunedAmount =>   "0",
        //                    feeAmount =>   "125622971824540759",
        //                    $feeOption =>   "feeInNative",
        //                     parentID =>   "MARKET_INTENT:90jjw2s7gj90jjw2s7gkjjw2s7gl",
        //       siblingTargetOrderHash =>    null,
        //                     $timeline => array ( array (      action => "$filled",
        //                                        $amount => "1000000000000000000",
        //                                      intentID => "MARKET_INTENT:90jjw2s7gj90jjw2s7gkjjw2s7gl",
        //                                        txHash =>  null,
        //                                   blockNumber => "0",
        //                                     $timestamp => "1532217579"                                  ),
        //                                 array (      action => "settled",
        //                                        $amount => "1000000000000000000",
        //                                      intentID => "MARKET_INTENT:90jjw2s7gj90jjw2s7gkjjw2s7gl",
        //                                        txHash => "0x043488fdc3f995bf9e632a32424441ed126de90f8cb340a1ff006c2a74ca8336",
        //                                   blockNumber => "8094822",
        //                                     $timestamp => "1532261671"                                                          ),
        //                                 {      action => "confirmed",
        //                                        $amount => "1000000000000000000",
        //                                      intentID => "MARKET_INTENT:90jjw2s7gj90jjw2s7gkjjw2s7gl",
        //                                        txHash => "0x043488fdc3f995bf9e632a32424441ed126de90f8cb340a1ff006c2a74ca8336",
        //                                   blockNumber => "8094822",
        //                                     $timestamp => "1532261686"                                                          }  ) }
        //
        //
        //
        $zeroExOrder = $this->safe_value($order, 'zeroExOrder');
        $id = $this->safe_string($order, 'orderHash');
        if (($id === null) && ($zeroExOrder !== null)) {
            $id = $this->safe_string($zeroExOrder, 'orderHash');
        }
        $side = $this->safe_string($order, 'side');
        $type = 'limit';
        $timestamp = $this->safe_integer($order, 'created');
        $timestamp = ($timestamp !== null) ? $timestamp * 1000 : $timestamp;
        $symbol = null;
        if ($market === null) {
            $baseId = $this->safe_string($order, 'baseTokenAddress');
            $quoteId = $this->safe_string($order, 'quoteTokenAddress');
            $marketId = $baseId . '/' . $quoteId;
            if (is_array ($this->markets_by_id) && array_key_exists ($marketId, $this->markets_by_id)) {
                $market = $this->markets_by_id[$marketId];
            }
        }
        if ($market !== null) {
            $symbol = $market['symbol'];
        }
        $price = $this->safe_float($order, 'price');
        $openAmount = $this->fromWei ($this->safe_string($order, 'openAmount'));
        $reservedAmount = $this->fromWei ($this->safe_string($order, 'reservedAmount'));
        $filledAmount = $this->fromWei ($this->safe_string($order, 'filledAmount'));
        $settledAmount = $this->fromWei ($this->safe_string($order, 'settledAmount'));
        $confirmedAmount = $this->fromWei ($this->safe_string($order, 'confirmedAmount'));
        $failedAmount = $this->fromWei ($this->safe_string($order, 'failedAmount'));
        $deadAmount = $this->fromWei ($this->safe_string($order, 'deadAmount'));
        $prunedAmount = $this->fromWei ($this->safe_string($order, 'prunedAmount'));
        $amount = $this->fromWei ($this->safe_string($order, 'amount'));
        if ($amount === null) {
            $amount = $this->sum ($openAmount, $reservedAmount, $filledAmount, $settledAmount, $confirmedAmount, $failedAmount, $deadAmount, $prunedAmount);
        }
        $filled = $this->sum ($filledAmount, $settledAmount, $confirmedAmount);
        $remaining = null;
        $lastTradeTimestamp = null;
        $timeline = $this->safe_value($order, 'timeline');
        $trades = null;
        $status = 'open';
        if ($timeline !== null) {
            $numEvents = is_array ($timeline) ? count ($timeline) : 0;
            if ($numEvents > 0) {
                $status = $this->safe_string($timeline[$numEvents - 1], 'action');
                $status = $this->parse_order_status($status);
                $timelineEventsGroupedByAction = $this->group_by($timeline, 'action');
                if (is_array ($timelineEventsGroupedByAction) && array_key_exists ('placed', $timelineEventsGroupedByAction)) {
                    $placeEvents = $this->safe_value($timelineEventsGroupedByAction, 'placed');
                    if ($amount === null) {
                        $amount = $this->fromWei ($this->safe_string($placeEvents[0], 'amount'));
                    }
                    $timestamp = $this->safe_integer($placeEvents[0], 'timestamp');
                    $timestamp = ($timestamp !== null) ? $timestamp * 1000 : $timestamp;
                } else {
                    if (is_array ($timelineEventsGroupedByAction) && array_key_exists ('filled', $timelineEventsGroupedByAction)) {
                        $timestamp = $this->safe_integer($timelineEventsGroupedByAction['filled'][0], 'timestamp');
                        $timestamp = ($timestamp !== null) ? $timestamp * 1000 : $timestamp;
                    }
                    $type = 'market';
                }
                if (is_array ($timelineEventsGroupedByAction) && array_key_exists ('filled', $timelineEventsGroupedByAction)) {
                    $fillEvents = $this->safe_value($timelineEventsGroupedByAction, 'filled');
                    $numFillEvents = is_array ($fillEvents) ? count ($fillEvents) : 0;
                    if ($timestamp === null) {
                        $timestamp = $this->safe_integer($fillEvents[0], 'timestamp');
                        $timestamp = ($timestamp !== null) ? $timestamp * 1000 : $timestamp;
                    }
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
        $feeCost = $this->fromWei ($this->safe_string($order, 'feeAmount'));
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
            $fee = array (
                'feeCost' => $feeCost,
                'feeCurrency' => $feeCurrency,
            );
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
        //
        //     {
        //       "baseTokenAddress" => "0x7cc7fdd065cfa9c7f4f6a3c1bfc6dfcb1a3177aa",
        //       "quoteTokenAddress" => "0x17f15936ef3a2da5593033f84487cbe9e268f02f",
        //       "side" => "buy",
        //       "amount" => "10000000000000000000",
        //       "price" => "1.000",
        //       "created" => "1512929327792",
        //       "expires" => "1512929897118",
        //       "zeroExOrder" => {
        //         "exchangeContractAddress" => "0x516bdc037df84d70672b2d140835833d3623e451",
        //         "maker" => "0x006dc83e5b21854d4afc44c9b92a91e0349dda13",
        //         "taker" => "0x00ba938cc0df182c25108d7bf2ee3d37bce07513",
        //         "makerTokenAddress" => "0x7cc7fdd065cfa9c7f4f6a3c1bfc6dfcb1a3177aa",
        //         "takerTokenAddress" => "0x17f15936ef3a2da5593033f84487cbe9e268f02f",
        //         "feeRecipient" => "0x88a64b5e882e5ad851bea5e7a3c8ba7c523fecbe",
        //         "makerTokenAmount" => "10000000000000000000",
        //         "takerTokenAmount" => "10000000000000000000",
        //         "makerFee" => "0",
        //         "takerFee" => "0",
        //         "expirationUnixTimestampSec" => "525600",
        //         "salt" => "37800593840622773016017857006417214310534675667008850948421364357744823963318",
        //         "orderHash" => "0x94629386298dee69ae63cd3e414336ae153b3f02cffb9ffc53ad71e166615618",
        //         "ecSignature" => {
        //           "v" => 28,
        //           "r" => "0x5307b6a69e7cba8583e1de39efb93a9ae1afc11849e79d99f462e49c18c4d6e4",
        //           "s" => "0x5950e82364227ccca95c70b47375e8911a2039d3040ba0684329634ebdced160"
        //         }
        //       }
        //     }
        //
        return $this->parse_order($response);
    }

    public function fetch_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $request = array (
            // openAmount (optional) Return orders with an openAmount greater than or equal to this value
            // reservedAmount (optional) Return orders with a reservedAmount greater than or equal to this value
            // filledAmount (optional) Return orders with a filledAmount greater than or equal to this value
            // confirmedAmount (optional) Return orders with a confirmedAmount greater than or equal to this value
            // deadAmount (optional) Return orders with a deadAmount greater than or equal to this value
            // baseTokenAddress (optional) Return orders with a baseTokenAddress equal to this value
            // quoteTokenAddress (optional) Return orders with a quoteTokenAddress equal to this value
        );
        $market = null;
        if ($symbol !== null) {
            $market = $this->market ($symbol);
            $request['baseTokenAddress'] = $market['baseId'];
            $request['quoteTokenAddress'] = $market['quoteId'];
        }
        if ($limit !== null) {
            // $request['start'] = 0; // the number of orders to offset from the end
            $request['limit'] = $limit;
        }
        $response = $this->privateGetUserHistory (array_merge ($request, $params));
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

    public function handle_errors ($httpCode, $reason, $url, $method, $headers, $body) {
        if (gettype ($body) !== 'string')
            return; // fallback to default $error handler
        if (strlen ($body) < 2)
            return; // fallback to default $error handler
        // code 401 and plain $body 'Authentication failed' (with single quotes)
        // this $error is sent if you do not submit a proper Content-Type
        if ($body === "'Authentication failed'") {
            throw new AuthenticationError ($this->id . ' ' . $body);
        }
        if (($body[0] === '{') || ($body[0] === '[')) {
            $response = json_decode ($body, $as_associative_array = true);
            $message = $this->safe_string($response, 'message');
            if ($message !== null) {
                //
                // array ("$message":"Schema validation failed for 'query'","$errors":[{"name":"required","argument":"startTime","$message":"requires property \"startTime\"","instance":array ("baseTokenAddress":"0x6ff6c0ff1d68b964901f986d4c9fa3ac68346570","quoteTokenAddress":"0xd0a1e359811322d97991e03f863a0c30c2cf029c","interval":"300"),"property":"instance")]}
                // array ("$message":"Logic validation failed for 'query'","$errors":[{"$message":"startTime should be between 0 and current date","type":"startTime")]}
                // array ("$message":"Order not found","$errors":array ())
                // array ("$message":"Orderbook exhausted for intent MARKET_INTENT:8yjjzd8b0e8yjjzd8b0fjjzd8b0g")
                // array ("$message":"Intent validation failed.","$errors":[{"$message":"Greater than available wallet balance.","type":"walletBaseTokenAmount")]}
                //
                $feedback = $this->id . ' ' . $this->json ($response);
                $exceptions = $this->exceptions;
                $errors = $this->safe_value($response, 'errors');
                if (is_array ($exceptions) && array_key_exists ($message, $exceptions)) {
                    throw new $exceptions[$message] ($feedback);
                } else {
                    if (mb_strpos ($message, 'Orderbook exhausted for intent') !== false) {
                        throw new OrderNotFillable ($feedback);
                    } else if ($message === 'Intent validation failed.') {
                        if (gettype ($errors) === 'array' && count (array_filter (array_keys ($errors), 'is_string')) == 0) {
                            for ($i = 0; $i < count ($errors); $i++) {
                                $error = $errors[$i];
                                $errorMessage = $this->safe_string($error, 'message');
                                if (is_array ($exceptions) && array_key_exists ($errorMessage, $exceptions)) {
                                    throw new $exceptions[$errorMessage] ($feedback);
                                }
                            }
                        }
                    }
                    throw new ExchangeError ($feedback);
                }
            }
        }
    }

    public function request ($path, $api = 'public', $method = 'GET', $params = array (), $headers = null, $body = null) {
        $response = $this->fetch2 ($path, $api, $method, $params, $headers, $body);
        if (gettype ($response) !== 'string') {
            throw new ExchangeError ($this->id . ' returned a non-string $response => ' . (string) $response);
        }
        if (($response[0] === '{' || $response[0] === '[')) {
            return json_decode ($response, $as_associative_array = true);
        }
        return $response;
    }
}
