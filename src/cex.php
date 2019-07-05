<?php

namespace ccxt;

use Exception as Exception; // a common import

class cex extends Exchange {

    public function describe () {
        return array_replace_recursive (parent::describe (), array (
            'id' => 'cex',
            'name' => 'CEX.IO',
            'countries' => array ( 'GB', 'EU', 'CY', 'RU' ),
            'rateLimit' => 1500,
            'has' => array (
                'CORS' => true,
                'fetchTickers' => true,
                'fetchOHLCV' => true,
                'fetchOrder' => true,
                'fetchOpenOrders' => true,
                'fetchClosedOrders' => true,
                'fetchDepositAddress' => true,
                'fetchOrders' => true,
            ),
            'timeframes' => array (
                '1m' => '1m',
            ),
            'urls' => array (
                'logo' => 'https://user-images.githubusercontent.com/1294454/27766442-8ddc33b0-5ed8-11e7-8b98-f786aef0f3c9.jpg',
                'api' => 'https://cex.io/api',
                'www' => 'https://cex.io',
                'doc' => 'https://cex.io/cex-api',
                'fees' => array (
                    'https://cex.io/fee-schedule',
                    'https://cex.io/limits-commissions',
                ),
                'referral' => 'https://cex.io/r/0/up105393824/0/',
            ),
            'requiredCredentials' => array (
                'apiKey' => true,
                'secret' => true,
                'uid' => true,
            ),
            'api' => array (
                'public' => array (
                    'get' => array (
                        'currency_limits/',
                        'last_price/{pair}/',
                        'last_prices/{currencies}/',
                        'ohlcv/hd/{yyyymmdd}/{pair}',
                        'order_book/{pair}/',
                        'ticker/{pair}/',
                        'tickers/{currencies}/',
                        'trade_history/{pair}/',
                    ),
                    'post' => array (
                        'convert/{pair}',
                        'price_stats/{pair}',
                    ),
                ),
                'private' => array (
                    'post' => array (
                        'active_orders_status/',
                        'archived_orders/{pair}/',
                        'balance/',
                        'cancel_order/',
                        'cancel_orders/{pair}/',
                        'cancel_replace_order/{pair}/',
                        'close_position/{pair}/',
                        'get_address/',
                        'get_myfee/',
                        'get_order/',
                        'get_order_tx/',
                        'open_orders/{pair}/',
                        'open_orders/',
                        'open_position/{pair}/',
                        'open_positions/{pair}/',
                        'place_order/{pair}/',
                    ),
                ),
            ),
            'fees' => array (
                'trading' => array (
                    'maker' => 0.16 / 100,
                    'taker' => 0.25 / 100,
                ),
                'funding' => array (
                    'withdraw' => array (
                        // 'USD' => null,
                        // 'EUR' => null,
                        // 'RUB' => null,
                        // 'GBP' => null,
                        'BTC' => 0.001,
                        'ETH' => 0.01,
                        'BCH' => 0.001,
                        'DASH' => 0.01,
                        'BTG' => 0.001,
                        'ZEC' => 0.001,
                        'XRP' => 0.02,
                    ),
                    'deposit' => array (
                        // 'USD' => amount => amount * 0.035 . 0.25,
                        // 'EUR' => amount => amount * 0.035 . 0.24,
                        // 'RUB' => amount => amount * 0.05 . 15.57,
                        // 'GBP' => amount => amount * 0.035 . 0.2,
                        'BTC' => 0.0,
                        'ETH' => 0.0,
                        'BCH' => 0.0,
                        'DASH' => 0.0,
                        'BTG' => 0.0,
                        'ZEC' => 0.0,
                        'XRP' => 0.0,
                        'XLM' => 0.0,
                    ),
                ),
            ),
            'options' => array (
                'fetchOHLCVWarning' => true,
                'createMarketBuyOrderRequiresPrice' => true,
                'order' => array (
                    'status' => array (
                        'c' => 'canceled',
                        'd' => 'closed',
                        'cd' => 'closed',
                        'a' => 'open',
                    ),
                ),
            ),
        ));
    }

    public function fetch_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array (
            'limit' => $limit,
            'pair' => $market['id'],
            'dateFrom' => $since,
        );
        $response = $this->privatePostArchivedOrdersPair (array_merge ($request, $params));
        $results = array();
        for ($i = 0; $i < count ($response); $i++) {
            // cancelled (unfilled):
            //    { id => '4005785516',
            //     $type => 'sell',
            //     $time => '2017-07-18T19:08:34.223Z',
            //     $lastTxTime => '2017-07-18T19:08:34.396Z',
            //     lastTx => '4005785522',
            //     pos => null,
            //     $status => 'c',
            //     symbol1 => 'ETH',
            //     symbol2 => 'GBP',
            //     $amount => '0.20000000',
            //     $price => '200.5625',
            //     remains => '0.20000000',
            //     'a:ETH:cds' => '0.20000000',
            //     tradingFeeMaker => '0',
            //     tradingFeeTaker => '0.16',
            //     tradingFeeUserVolumeAmount => '10155061217',
            //     orderId => '4005785516' }
            // --
            // cancelled (partially $filled buy):
            //    { id => '4084911657',
            //     $type => 'buy',
            //     $time => '2017-08-05T03:18:39.596Z',
            //     $lastTxTime => '2019-03-19T17:37:46.404Z',
            //     lastTx => '8459265833',
            //     pos => null,
            //     $status => 'cd',
            //     symbol1 => 'BTC',
            //     symbol2 => 'GBP',
            //     $amount => '0.05000000',
            //     $price => '2241.4692',
            //     tfacf => '1',
            //     remains => '0.03910535',
            //     'tfa:GBP' => '0.04',
            //     'tta:GBP' => '24.39',
            //     'a:BTC:cds' => '0.01089465',
            //     'a:GBP:cds' => '112.26',
            //     'f:GBP:cds' => '0.04',
            //     tradingFeeMaker => '0',
            //     tradingFeeTaker => '0.16',
            //     tradingFeeUserVolumeAmount => '13336396963',
            //     orderId => '4084911657' }
            // --
            // cancelled (partially $filled sell):
            //    { id => '4426728375',
            //     $type => 'sell',
            //     $time => '2017-09-22T00:24:20.126Z',
            //     $lastTxTime => '2017-09-22T00:24:30.476Z',
            //     lastTx => '4426729543',
            //     pos => null,
            //     $status => 'cd',
            //     symbol1 => 'BCH',
            //     symbol2 => 'BTC',
            //     $amount => '0.10000000',
            //     $price => '0.11757182',
            //     tfacf => '1',
            //     remains => '0.09935956',
            //     'tfa:BTC' => '0.00000014',
            //     'tta:BTC' => '0.00007537',
            //     'a:BCH:cds' => '0.10000000',
            //     'a:BTC:cds' => '0.00007537',
            //     'f:BTC:cds' => '0.00000014',
            //     tradingFeeMaker => '0',
            //     tradingFeeTaker => '0.18',
            //     tradingFeeUserVolumeAmount => '3466715450',
            //     orderId => '4426728375' }
            // --
            // $filled:
            //    { id => '5342275378',
            //     $type => 'sell',
            //     $time => '2018-01-04T00:28:12.992Z',
            //     $lastTxTime => '2018-01-04T00:28:12.992Z',
            //     lastTx => '5342275393',
            //     pos => null,
            //     $status => 'd',
            //     symbol1 => 'BCH',
            //     symbol2 => 'BTC',
            //     $amount => '0.10000000',
            //     kind => 'api',
            //     $price => '0.17',
            //     remains => '0.00000000',
            //     'tfa:BTC' => '0.00003902',
            //     'tta:BTC' => '0.01699999',
            //     'a:BCH:cds' => '0.10000000',
            //     'a:BTC:cds' => '0.01699999',
            //     'f:BTC:cds' => '0.00003902',
            //     tradingFeeMaker => '0.15',
            //     tradingFeeTaker => '0.23',
            //     tradingFeeUserVolumeAmount => '1525951128',
            //     orderId => '5342275378' }
            // --
            // $market order (buy):
            //    { "id" => "6281946200",
            //     "pos" => null,
            //     "$time" => "2018-05-23T11:55:43.467Z",
            //     "$type" => "buy",
            //     "$amount" => "0.00000000",
            //     "lastTx" => "6281946210",
            //     "$status" => "d",
            //     "amount2" => "20.00",
            //     "orderId" => "6281946200",
            //     "remains" => "0.00000000",
            //     "symbol1" => "ETH",
            //     "symbol2" => "EUR",
            //     "$tfa:EUR" => "0.05",
            //     "$tta:EUR" => "19.94",
            //     "a:ETH:cds" => "0.03764100",
            //     "a:EUR:cds" => "20.00",
            //     "f:EUR:cds" => "0.05",
            //     "$lastTxTime" => "2018-05-23T11:55:43.467Z",
            //     "tradingFeeTaker" => "0.25",
            //     "tradingFeeUserVolumeAmount" => "55998097" }
            // --
            // $market order (sell):
            //   { "id" => "6282200948",
            //     "pos" => null,
            //     "$time" => "2018-05-23T12:42:58.315Z",
            //     "$type" => "sell",
            //     "$amount" => "-0.05000000",
            //     "lastTx" => "6282200958",
            //     "$status" => "d",
            //     "orderId" => "6282200948",
            //     "remains" => "0.00000000",
            //     "symbol1" => "ETH",
            //     "symbol2" => "EUR",
            //     "$tfa:EUR" => "0.07",
            //     "$tta:EUR" => "26.49",
            //     "a:ETH:cds" => "0.05000000",
            //     "a:EUR:cds" => "26.49",
            //     "f:EUR:cds" => "0.07",
            //     "$lastTxTime" => "2018-05-23T12:42:58.315Z",
            //     "tradingFeeTaker" => "0.25",
            //     "tradingFeeUserVolumeAmount" => "56294576" }
            $item = $response[$i];
            $status = $this->parse_order_status($this->safe_string($item, 'status'));
            $baseId = $item['symbol1'];
            $quoteId = $item['symbol2'];
            $side = $item['type'];
            $baseAmount = $this->safe_float($item, 'a:' . $baseId . ':cds');
            $quoteAmount = $this->safe_float($item, 'a:' . $quoteId . ':cds');
            $fee = $this->safe_float($item, 'f:' . $quoteId . ':cds');
            $amount = $this->safe_float($item, 'amount');
            $price = $this->safe_float($item, 'price');
            $remaining = $this->safe_float($item, 'remains');
            $filled = $amount - $remaining;
            $orderAmount = null;
            $cost = null;
            $average = null;
            $type = null;
            if (!$price) {
                $type = 'market';
                $orderAmount = $baseAmount;
                $cost = $quoteAmount;
                $average = $orderAmount / $cost;
            } else {
                $ta = $this->safe_float($item, 'ta:' . $quoteId, 0);
                $tta = $this->safe_float($item, 'tta:' . $quoteId, 0);
                $fa = $this->safe_float($item, 'fa:' . $quoteId, 0);
                $tfa = $this->safe_float($item, 'tfa:' . $quoteId, 0);
                if ($side === 'sell') {
                    $cost = $ta . $tta . ($fa . $tfa);
                } else {
                    $cost = $ta . $tta - ($fa . $tfa);
                }
                $type = 'limit';
                $orderAmount = $amount;
                $average = $cost / $filled;
            }
            $time = $this->safe_string($item, 'time');
            $lastTxTime = $this->safe_string($item, 'lastTxTime');
            $timestamp = $this->parse8601 ($time);
            $results[] = array (
                'id' => $item['id'],
                'timestamp' => $timestamp,
                'datetime' => $this->iso8601 ($timestamp),
                'lastUpdated' => $this->parse8601 ($lastTxTime),
                'status' => $status,
                'symbol' => $this->find_symbol($baseId . '/' . $quoteId),
                'side' => $side,
                'price' => $price,
                'amount' => $orderAmount,
                'average' => $average,
                'type' => $type,
                'filled' => $filled,
                'cost' => $cost,
                'remaining' => $remaining,
                'fee' => array (
                    'cost' => $fee,
                    'currency' => $this->currencyId ($quoteId),
                ),
                'info' => $item,
            );
        }
        return $results;
    }

    public function parse_order_status ($status) {
        return $this->safe_string($this->options['order']['status'], $status, $status);
    }

    public function fetch_markets ($params = array ()) {
        $response = $this->publicGetCurrencyLimits ($params);
        $result = array();
        $markets = $this->safe_value($response['data'], 'pairs');
        for ($i = 0; $i < count ($markets); $i++) {
            $market = $markets[$i];
            $baseId = $this->safe_string($market, 'symbol1');
            $quoteId = $this->safe_string($market, 'symbol2');
            $id = $baseId . '/' . $quoteId;
            $base = $this->safe_currency_code($baseId);
            $quote = $this->safe_currency_code($quoteId);
            $symbol = $base . '/' . $quote;
            $result[] = array (
                'id' => $id,
                'info' => $market,
                'symbol' => $symbol,
                'base' => $base,
                'quote' => $quote,
                'baseId' => $baseId,
                'quoteId' => $quoteId,
                'precision' => array (
                    'price' => $this->precision_from_string($this->safe_string($market, 'minPrice')),
                    'amount' => $this->precision_from_string($this->safe_string($market, 'minLotSize')),
                ),
                'limits' => array (
                    'amount' => array (
                        'min' => $this->safe_float($market, 'minLotSize'),
                        'max' => $this->safe_float($market, 'maxLotSize'),
                    ),
                    'price' => array (
                        'min' => $this->safe_float($market, 'minPrice'),
                        'max' => $this->safe_float($market, 'maxPrice'),
                    ),
                    'cost' => array (
                        'min' => $this->safe_float($market, 'minLotSizeS2'),
                        'max' => null,
                    ),
                ),
            );
        }
        return $result;
    }

    public function fetch_balance ($params = array ()) {
        $this->load_markets();
        $response = $this->privatePostBalance ($params);
        $result = array( 'info' => $response );
        $ommited = array ( 'username', 'timestamp' );
        $balances = $this->omit ($response, $ommited);
        $currencyIds = is_array($balances) ? array_keys($balances) : array();
        for ($i = 0; $i < count ($currencyIds); $i++) {
            $currencyId = $currencyIds[$i];
            $balance = $this->safe_value($balances, $currencyId, array());
            $account = $this->account ();
            $account['free'] = $this->safe_float($balance, 'available');
            $account['used'] = $this->safe_float($balance, 'orders');
            $code = $this->safe_currency_code($currencyId);
            $result[$code] = $account;
        }
        return $this->parse_balance($result);
    }

    public function fetch_order_book ($symbol, $limit = null, $params = array ()) {
        $this->load_markets();
        $request = array (
            'pair' => $this->market_id($symbol),
        );
        if ($limit !== null) {
            $request['depth'] = $limit;
        }
        $response = $this->publicGetOrderBookPair (array_merge ($request, $params));
        $timestamp = $response['timestamp'] * 1000;
        return $this->parse_order_book($response, $timestamp);
    }

    public function parse_ohlcv ($ohlcv, $market = null, $timeframe = '1m', $since = null, $limit = null) {
        return [
            $ohlcv[0] * 1000,
            $ohlcv[1],
            $ohlcv[2],
            $ohlcv[3],
            $ohlcv[4],
            $ohlcv[5],
        ];
    }

    public function fetch_ohlcv ($symbol, $timeframe = '1m', $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        if ($since === null) {
            $since = $this->milliseconds () - 86400000; // yesterday
        } else {
            if ($this->options['fetchOHLCVWarning']) {
                throw new ExchangeError($this->id . " fetchOHLCV warning => CEX can return historical candles for a certain date only, this might produce an empty or null reply. Set exchange.options['fetchOHLCVWarning'] = false or add (array( 'options' => array ( 'fetchOHLCVWarning' => false ))) to constructor $params to suppress this warning message.");
            }
        }
        $ymd = $this->ymd ($since);
        $ymd = explode('-', $ymd);
        $ymd = implode('', $ymd);
        $request = array (
            'pair' => $market['id'],
            'yyyymmdd' => $ymd,
        );
        try {
            $response = $this->publicGetOhlcvHdYyyymmddPair (array_merge ($request, $params));
            $key = 'data' . $this->timeframes[$timeframe];
            $ohlcvs = json_decode($response[$key], $as_associative_array = true);
            return $this->parse_ohlcvs($ohlcvs, $market, $timeframe, $since, $limit);
        } catch (Exception $e) {
            if ($e instanceof NullResponse) {
                return array();
            }
        }
    }

    public function parse_ticker ($ticker, $market = null) {
        $timestamp = null;
        if (is_array($ticker) && array_key_exists('timestamp', $ticker)) {
            $timestamp = intval ($ticker['timestamp']) * 1000;
        }
        $volume = $this->safe_float($ticker, 'volume');
        $high = $this->safe_float($ticker, 'high');
        $low = $this->safe_float($ticker, 'low');
        $bid = $this->safe_float($ticker, 'bid');
        $ask = $this->safe_float($ticker, 'ask');
        $last = $this->safe_float($ticker, 'last');
        $symbol = null;
        if ($market) {
            $symbol = $market['symbol'];
        }
        return array (
            'symbol' => $symbol,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'high' => $high,
            'low' => $low,
            'bid' => $bid,
            'bidVolume' => null,
            'ask' => $ask,
            'askVolume' => null,
            'vwap' => null,
            'open' => null,
            'close' => $last,
            'last' => $last,
            'previousClose' => null,
            'change' => null,
            'percentage' => null,
            'average' => null,
            'baseVolume' => $volume,
            'quoteVolume' => null,
            'info' => $ticker,
        );
    }

    public function fetch_tickers ($symbols = null, $params = array ()) {
        $this->load_markets();
        $currencies = is_array($this->currencies) ? array_keys($this->currencies) : array();
        $request = array (
            'currencies' => implode('/', $currencies),
        );
        $response = $this->publicGetTickersCurrencies (array_merge ($request, $params));
        $tickers = $response['data'];
        $result = array();
        for ($t = 0; $t < count ($tickers); $t++) {
            $ticker = $tickers[$t];
            $symbol = str_replace(':', '/', $ticker['pair']);
            $market = $this->markets[$symbol];
            $result[$symbol] = $this->parse_ticker($ticker, $market);
        }
        return $result;
    }

    public function fetch_ticker ($symbol, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array (
            'pair' => $market['id'],
        );
        $ticker = $this->publicGetTickerPair (array_merge ($request, $params));
        return $this->parse_ticker($ticker, $market);
    }

    public function parse_trade ($trade, $market = null) {
        $timestamp = $this->safe_integer($trade, 'date');
        if ($timestamp !== null) {
            $timestamp *= 1000;
        }
        $id = $this->safe_string($trade, 'tid');
        $type = null;
        $side = $this->safe_string($trade, 'type');
        $price = $this->safe_float($trade, 'price');
        $amount = $this->safe_float($trade, 'amount');
        $cost = null;
        if ($amount !== null) {
            if ($price !== null) {
                $cost = $amount * $price;
            }
        }
        $symbol = null;
        if ($market !== null) {
            $symbol = $market['symbol'];
        }
        return array (
            'info' => $trade,
            'id' => $id,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'symbol' => $symbol,
            'type' => $type,
            'side' => $side,
            'order' => null,
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
            'pair' => $market['id'],
        );
        $response = $this->publicGetTradeHistoryPair (array_merge ($request, $params));
        return $this->parse_trades($response, $market, $since, $limit);
    }

    public function create_order ($symbol, $type, $side, $amount, $price = null, $params = array ()) {
        if ($type === 'market') {
            // for market buy it requires the $amount of quote currency to spend
            if ($side === 'buy') {
                if ($this->options['createMarketBuyOrderRequiresPrice']) {
                    if ($price === null) {
                        throw new InvalidOrder($this->id . " createOrder() requires the $price argument with market buy orders to calculate total order cost ($amount to spend), where cost = $amount * $price-> Supply a $price argument to createOrder() call if you want the cost to be calculated for you from $price and $amount, or, alternatively, add .options['createMarketBuyOrderRequiresPrice'] = false to supply the cost in the $amount argument (the exchange-specific behaviour)");
                    } else {
                        $amount = $amount * $price;
                    }
                }
            }
        }
        $this->load_markets();
        $request = array (
            'pair' => $this->market_id($symbol),
            'type' => $side,
            'amount' => $amount,
        );
        if ($type === 'limit') {
            $request['price'] = $price;
        } else {
            $request['order_type'] = $type;
        }
        $response = $this->privatePostPlaceOrderPair (array_merge ($request, $params));
        return array (
            'info' => $response,
            'id' => $response['id'],
        );
    }

    public function cancel_order ($id, $symbol = null, $params = array ()) {
        $this->load_markets();
        $request = array (
            'id' => $id,
        );
        return $this->privatePostCancelOrder (array_merge ($request, $params));
    }

    public function parse_order ($order, $market = null) {
        // Depending on the call, 'time' can be a unix int, unix string or ISO string
        // Yes, really
        $timestamp = $this->safe_value($order, 'time');
        if (gettype ($timestamp) === 'string' && mb_strpos($timestamp, 'T') !== false) {
            // ISO8601 string
            $timestamp = $this->parse8601 ($timestamp);
        } else {
            // either integer or string integer
            $timestamp = intval ($timestamp);
        }
        $symbol = null;
        if ($market === null) {
            $baseId = $this->safe_string($order, 'symbol1');
            $quoteId = $this->safe_string($order, 'symbol2');
            $base = $this->safe_currency_code($baseId);
            $quote = $this->safe_currency_code($quoteId);
            $symbol = $base . '/' . $quote;
            if (is_array($this->markets) && array_key_exists($symbol, $this->markets)) {
                $market = $this->market ($symbol);
            }
        }
        $status = $this->parse_order_status($this->safe_string($order, 'status'));
        $price = $this->safe_float($order, 'price');
        $amount = $this->safe_float($order, 'amount');
        // sell orders can have a negative $amount
        // https://github.com/ccxt/ccxt/issues/5338
        if ($amount !== null) {
            $amount = abs ($amount);
        }
        $remaining = $this->safe_float_2($order, 'pending', 'remains');
        $filled = $amount - $remaining;
        $fee = null;
        $cost = null;
        if ($market !== null) {
            $symbol = $market['symbol'];
            $cost = $this->safe_float($order, 'ta:' . $market['quote']);
            if ($cost === null) {
                $cost = $this->safe_float($order, 'tta:' . $market['quote']);
            }
            $baseFee = 'fa:' . $market['base'];
            $baseTakerFee = 'tfa:' . $market['base'];
            $quoteFee = 'fa:' . $market['quote'];
            $quoteTakerFee = 'tfa:' . $market['quote'];
            $feeRate = $this->safe_float($order, 'tradingFeeMaker');
            if (!$feeRate) {
                $feeRate = $this->safe_float($order, 'tradingFeeTaker', $feeRate);
            }
            if ($feeRate) {
                $feeRate /= 100.0; // convert to mathematically-correct percentage coefficients => 1.0 = 100%
            }
            if ((is_array($order) && array_key_exists($baseFee, $order)) || (is_array($order) && array_key_exists($baseTakerFee, $order))) {
                $baseFeeCost = $this->safe_float_2($order, $baseFee, $baseTakerFee);
                $fee = array (
                    'currency' => $market['base'],
                    'rate' => $feeRate,
                    'cost' => $baseFeeCost,
                );
            } else if ((is_array($order) && array_key_exists($quoteFee, $order)) || (is_array($order) && array_key_exists($quoteTakerFee, $order))) {
                $quoteFeeCost = $this->safe_float_2($order, $quoteFee, $quoteTakerFee);
                $fee = array (
                    'currency' => $market['quote'],
                    'rate' => $feeRate,
                    'cost' => $quoteFeeCost,
                );
            }
        }
        if (!$cost) {
            $cost = $price * $filled;
        }
        $side = $order['type'];
        $trades = null;
        $orderId = $order['id'];
        if (is_array($order) && array_key_exists('vtx', $order)) {
            $trades = array();
            for ($i = 0; $i < count ($order['vtx']); $i++) {
                $item = $order['vtx'][$i];
                $tradeSide = $this->safe_string($item, 'type');
                if ($item['type'] === 'cancel') {
                    // looks like this might represent the cancelled part of an $order
                    //   { id => '4426729543',
                    //     type => 'cancel',
                    //     time => '2017-09-22T00:24:30.476Z',
                    //     user => 'up106404164',
                    //     c => 'user:up106404164:a:BCH',
                    //     d => 'order:4426728375:a:BCH',
                    //     a => '0.09935956',
                    //     $amount => '0.09935956',
                    //     balance => '0.42580261',
                    //     $symbol => 'BCH',
                    //     $order => '4426728375',
                    //     buy => null,
                    //     sell => null,
                    //     pair => null,
                    //     pos => null,
                    //     cs => '0.42580261',
                    //     ds => 0 }
                    continue;
                }
                if (!$item['price']) {
                    // this represents the $order
                    //   {
                    //     "a" => "0.47000000",
                    //     "c" => "user:up106404164:a:EUR",
                    //     "d" => "$order:6065499239:a:EUR",
                    //     "cs" => "1432.93",
                    //     "ds" => "476.72",
                    //     "id" => "6065499249",
                    //     "buy" => null,
                    //     "pos" => null,
                    //     "pair" => null,
                    //     "sell" => null,
                    //     "time" => "2018-04-22T13:07:22.152Z",
                    //     "type" => "buy",
                    //     "user" => "up106404164",
                    //     "$order" => "6065499239",
                    //     "$amount" => "-715.97000000",
                    //     "$symbol" => "EUR",
                    //     "balance" => "1432.93000000" }
                    continue;
                }
                // if ($item['type'] === 'costsNothing')
                //     var_dump ($item);
                // todo => deal with these
                if ($item['type'] === 'costsNothing') {
                    continue;
                }
                // --
                // if ($side !== $tradeSide)
                //     throw Error (json_encode ($order, null, 2));
                // if ($orderId !== $item['order'])
                //     throw Error (json_encode ($order, null, 2));
                // --
                // partial buy trade
                //   {
                //     "a" => "0.01589885",
                //     "c" => "user:up106404164:a:BTC",
                //     "d" => "$order:6065499239:a:BTC",
                //     "cs" => "0.36300000",
                //     "ds" => 0,
                //     "id" => "6067991213",
                //     "buy" => "6065499239",
                //     "pos" => null,
                //     "pair" => null,
                //     "sell" => "6067991206",
                //     "time" => "2018-04-22T23:09:11.773Z",
                //     "type" => "buy",
                //     "user" => "up106404164",
                //     "$order" => "6065499239",
                //     "$price" => 7146.5,
                //     "$amount" => "0.01589885",
                //     "$symbol" => "BTC",
                //     "balance" => "0.36300000",
                //     "symbol2" => "EUR",
                //     "fee_amount" => "0.19" }
                // --
                // trade with zero $amount, but non-zero $fee
                //   {
                //     "a" => "0.00000000",
                //     "c" => "user:up106404164:a:EUR",
                //     "d" => "$order:5840654423:a:EUR",
                //     "cs" => 559744,
                //     "ds" => 0,
                //     "id" => "5840654429",
                //     "buy" => "5807238573",
                //     "pos" => null,
                //     "pair" => null,
                //     "sell" => "5840654423",
                //     "time" => "2018-03-15T03:20:14.010Z",
                //     "type" => "sell",
                //     "user" => "up106404164",
                //     "$order" => "5840654423",
                //     "$price" => 730,
                //     "$amount" => "0.00000000",
                //     "$symbol" => "EUR",
                //     "balance" => "5597.44000000",
                //     "symbol2" => "BCH",
                //     "fee_amount" => "0.01" }
                $tradeTime = $this->safe_string($item, 'time');
                $tradeTimestamp = $this->parse8601 ($tradeTime);
                $tradeAmount = $this->safe_float($item, 'amount');
                $tradePrice = $this->safe_float($item, 'price');
                $absTradeAmount = $tradeAmount < 0 ? -$tradeAmount : $tradeAmount;
                $tradeCost = null;
                if ($tradeSide === 'sell') {
                    $tradeCost = $absTradeAmount;
                    $absTradeAmount = $tradeCost / $tradePrice;
                } else {
                    $tradeCost = $absTradeAmount * $tradePrice;
                }
                $trades[] = array (
                    'id' => $this->safe_string($item, 'id'),
                    'timestamp' => $tradeTimestamp,
                    'datetime' => $this->iso8601 ($tradeTimestamp),
                    'order' => $orderId,
                    'symbol' => $symbol,
                    'price' => $tradePrice,
                    'amount' => $absTradeAmount,
                    'cost' => $tradeCost,
                    'side' => $tradeSide,
                    'fee' => array (
                        'cost' => $this->safe_float($item, 'fee_amount'),
                        'currency' => $market['quote'],
                    ),
                    'info' => $item,
                );
            }
        }
        return array (
            'id' => $orderId,
            'datetime' => $this->iso8601 ($timestamp),
            'timestamp' => $timestamp,
            'lastTradeTimestamp' => null,
            'status' => $status,
            'symbol' => $symbol,
            'type' => null,
            'side' => $side,
            'price' => $price,
            'cost' => $cost,
            'amount' => $amount,
            'filled' => $filled,
            'remaining' => $remaining,
            'trades' => $trades,
            'fee' => $fee,
            'info' => $order,
        );
    }

    public function fetch_open_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $request = array();
        $method = 'privatePostOpenOrders';
        $market = null;
        if ($symbol !== null) {
            $market = $this->market ($symbol);
            $request['pair'] = $market['id'];
            $method .= 'Pair';
        }
        $orders = $this->$method (array_merge ($request, $params));
        for ($i = 0; $i < count ($orders); $i++) {
            $orders[$i] = array_merge ($orders[$i], array( 'status' => 'open' ));
        }
        return $this->parse_orders($orders, $market, $since, $limit);
    }

    public function fetch_closed_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $method = 'privatePostArchivedOrdersPair';
        if ($symbol === null) {
            throw new ArgumentsRequired($this->id . ' fetchClosedOrders requires a $symbol argument');
        }
        $market = $this->market ($symbol);
        $request = array( 'pair' => $market['id'] );
        $response = $this->$method (array_merge ($request, $params));
        return $this->parse_orders($response, $market, $since, $limit);
    }

    public function fetch_order ($id, $symbol = null, $params = array ()) {
        $this->load_markets();
        $request = array (
            'id' => (string) $id,
        );
        $response = $this->privatePostGetOrderTx (array_merge ($request, $params));
        return $this->parse_order($response['data']);
    }

    public function nonce () {
        return $this->milliseconds ();
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
            $nonce = (string) $this->nonce ();
            $auth = $nonce . $this->uid . $this->apiKey;
            $signature = $this->hmac ($this->encode ($auth), $this->encode ($this->secret));
            $body = $this->json (array_merge (array (
                'key' => $this->apiKey,
                'signature' => strtoupper($signature),
                'nonce' => $nonce,
            ), $query));
            $headers = array (
                'Content-Type' => 'application/json',
            );
        }
        return array( 'url' => $url, 'method' => $method, 'body' => $body, 'headers' => $headers );
    }

    public function request ($path, $api = 'public', $method = 'GET', $params = array (), $headers = null, $body = null) {
        $response = $this->fetch2 ($path, $api, $method, $params, $headers, $body);
        if (gettype ($response) === 'array' && count (array_filter (array_keys ($response), 'is_string')) == 0) {
            return $response; // public endpoints may return array()-arrays
        }
        if (!$response) {
            throw new NullResponse($this->id . ' returned ' . $this->json ($response));
        } else if ($response === true || $response === 'true') {
            return $response;
        } else if (is_array($response) && array_key_exists('e', $response)) {
            if (is_array($response) && array_key_exists('ok', $response)) {
                if ($response['ok'] === 'ok') {
                    return $response;
                }
            }
            throw new ExchangeError($this->id . ' ' . $this->json ($response));
        } else if (is_array($response) && array_key_exists('error', $response)) {
            if ($response['error']) {
                throw new ExchangeError($this->id . ' ' . $this->json ($response));
            }
        }
        return $response;
    }

    public function fetch_deposit_address ($code, $params = array ()) {
        if ($code === 'XRP' || $code === 'XLM') {
            // https://github.com/ccxt/ccxt/pull/2327#issuecomment-375204856
            throw new NotSupported($this->id . ' fetchDepositAddress does not support XRP and XLM addresses yet (awaiting docs from CEX.io)');
        }
        $this->load_markets();
        $currency = $this->currency ($code);
        $request = array (
            'currency' => $currency['id'],
        );
        $response = $this->privatePostGetAddress (array_merge ($request, $params));
        $address = $this->safe_string($response, 'data');
        $this->check_address($address);
        return array (
            'currency' => $code,
            'address' => $address,
            'tag' => null,
            'info' => $response,
        );
    }
}
