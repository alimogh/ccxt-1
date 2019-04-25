<?php

namespace ccxt;

use Exception as Exception; // a common import

class bitfinex2 extends bitfinex {

    public function describe () {
        return array_replace_recursive (parent::describe (), array (
            'id' => 'bitfinex2',
            'name' => 'Bitfinex',
            'countries' => array ( 'VG' ),
            'version' => 'v2',
            'certified' => false,
            // new metainfo interface
            'has' => array (
                'CORS' => true,
                'createLimitOrder' => false,
                'createMarketOrder' => false,
                'createOrder' => false,
                'deposit' => false,
                'editOrder' => false,
                'fetchDepositAddress' => false,
                'fetchClosedOrders' => false,
                'fetchFundingFees' => false,
                'fetchMyTrades' => false, // has to be false https://github.com/ccxt/ccxt/issues/4971
                'fetchOHLCV' => true,
                'fetchOpenOrders' => false,
                'fetchOrder' => true,
                'fetchTickers' => true,
                'fetchTradingFee' => false,
                'fetchTradingFees' => false,
                'withdraw' => true,
            ),
            'timeframes' => array (
                '1m' => '1m',
                '5m' => '5m',
                '15m' => '15m',
                '30m' => '30m',
                '1h' => '1h',
                '3h' => '3h',
                '6h' => '6h',
                '12h' => '12h',
                '1d' => '1D',
                '1w' => '7D',
                '2w' => '14D',
                '1M' => '1M',
            ),
            'rateLimit' => 1500,
            'urls' => array (
                'logo' => 'https://user-images.githubusercontent.com/1294454/27766244-e328a50c-5ed2-11e7-947b-041416579bb3.jpg',
                'api' => 'https://api.bitfinex.com',
                'www' => 'https://www.bitfinex.com',
                'doc' => array (
                    'https://docs.bitfinex.com/v2/docs/',
                    'https://github.com/bitfinexcom/bitfinex-api-node',
                ),
                'fees' => 'https://www.bitfinex.com/fees',
            ),
            'api' => array (
                'v1' => array (
                    'get' => array (
                        'symbols',
                        'symbols_details',
                    ),
                ),
                'public' => array (
                    'get' => array (
                        'platform/status',
                        'tickers',
                        'ticker/{symbol}',
                        'trades/{symbol}/hist',
                        'book/{symbol}/{precision}',
                        'book/{symbol}/P0',
                        'book/{symbol}/P1',
                        'book/{symbol}/P2',
                        'book/{symbol}/P3',
                        'book/{symbol}/R0',
                        'stats1/{key}:{size}:{symbol}:{side}/{section}',
                        'stats1/{key}:{size}:{symbol}/{section}',
                        'stats1/{key}:{size}:{symbol}:long/last',
                        'stats1/{key}:{size}:{symbol}:long/hist',
                        'stats1/{key}:{size}:{symbol}:short/last',
                        'stats1/{key}:{size}:{symbol}:short/hist',
                        'candles/trade:{timeframe}:{symbol}/{section}',
                        'candles/trade:{timeframe}:{symbol}/last',
                        'candles/trade:{timeframe}:{symbol}/hist',
                    ),
                    'post' => array (
                        'calc/trade/avg',
                        'calc/fx',
                    ),
                ),
                'private' => array (
                    'post' => array (
                        'auth/r/wallets',
                        'auth/r/orders/{symbol}',
                        'auth/r/orders/{symbol}/new',
                        'auth/r/orders/{symbol}/hist',
                        'auth/r/order/{symbol}:{id}/trades',
                        'auth/r/trades/hist',
                        'auth/r/trades/{symbol}/hist',
                        'auth/r/positions',
                        'auth/r/positions/hist',
                        'auth/r/funding/offers/{symbol}',
                        'auth/r/funding/offers/{symbol}/hist',
                        'auth/r/funding/loans/{symbol}',
                        'auth/r/funding/loans/{symbol}/hist',
                        'auth/r/funding/credits/{symbol}',
                        'auth/r/funding/credits/{symbol}/hist',
                        'auth/r/funding/trades/{symbol}/hist',
                        'auth/r/info/margin/{key}',
                        'auth/r/info/funding/{key}',
                        'auth/r/ledgers/hist',
                        'auth/r/movements/hist',
                        'auth/r/movements/{currency}/hist',
                        'auth/r/stats/perf:{timeframe}/hist',
                        'auth/r/alerts',
                        'auth/w/alert/set',
                        'auth/w/alert/{type}:{symbol}:{price}/del',
                        'auth/calc/order/avail',
                        'auth/r/ledgers/{symbol}/hist',
                        'auth/r/settings',
                        'auth/w/settings/set',
                        'auth/w/settings/del',
                        'auth/r/info/user',
                    ),
                ),
            ),
            'fees' => array (
                'trading' => array (
                    'maker' => 0.1 / 100,
                    'taker' => 0.2 / 100,
                ),
                'funding' => array (
                    'withdraw' => array (
                        'BTC' => 0.0004,
                        'BCH' => 0.0001,
                        'ETH' => 0.00135,
                        'EOS' => 0.0,
                        'LTC' => 0.001,
                        'OMG' => 0.15097,
                        'IOT' => 0.0,
                        'NEO' => 0.0,
                        'ETC' => 0.01,
                        'XRP' => 0.02,
                        'ETP' => 0.01,
                        'ZEC' => 0.001,
                        'BTG' => 0.0,
                        'DASH' => 0.01,
                        'XMR' => 0.0001,
                        'QTM' => 0.01,
                        'EDO' => 0.23687,
                        'DAT' => 9.8858,
                        'AVT' => 1.1251,
                        'SAN' => 0.35977,
                        'USDT' => 5.0,
                        'SPK' => 16.971,
                        'BAT' => 1.1209,
                        'GNT' => 2.8789,
                        'SNT' => 9.0848,
                        'QASH' => 1.726,
                        'YYW' => 7.9464,
                    ),
                ),
            ),
            'options' => array (
                'orderTypes' => array (
                    'MARKET' => null,
                    'EXCHANGE MARKET' => 'market',
                    'LIMIT' => null,
                    'EXCHANGE LIMIT' => 'limit',
                    'STOP' => null,
                    'EXCHANGE STOP' => 'stopOrLoss',
                    'TRAILING STOP' => null,
                    'EXCHANGE TRAILING STOP' => null,
                    'FOK' => null,
                    'EXCHANGE FOK' => 'limit FOK',
                    'STOP LIMIT' => null,
                    'EXCHANGE STOP LIMIT' => 'limit stop',
                    'IOC' => null,
                    'EXCHANGE IOC' => 'limit ioc',
                ),
                'fiat' => array (
                    'USD' => 'USD',
                    'EUR' => 'EUR',
                    'JPY' => 'JPY',
                    'GBP' => 'GBP',
                ),
            ),
        ));
    }

    public function is_fiat ($code) {
        return (is_array ($this->options['fiat']) && array_key_exists ($code, $this->options['fiat']));
    }

    public function get_currency_id ($code) {
        return 'f' . $code;
    }

    public function fetch_markets ($params = array ()) {
        $markets = $this->v1GetSymbolsDetails ();
        $result = array ();
        for ($p = 0; $p < count ($markets); $p++) {
            $market = $markets[$p];
            $id = strtoupper ($market['pair']);
            $baseId = mb_substr ($id, 0, 3);
            $quoteId = mb_substr ($id, 3, 6);
            $base = $this->common_currency_code($baseId);
            $quote = $this->common_currency_code($quoteId);
            $symbol = $base . '/' . $quote;
            $id = 't' . $id;
            $baseId = $this->get_currency_id ($baseId);
            $quoteId = $this->get_currency_id ($quoteId);
            $precision = array (
                'price' => $market['price_precision'],
                'amount' => $market['price_precision'],
            );
            $limits = array (
                'amount' => array (
                    'min' => $this->safe_float($market, 'minimum_order_size'),
                    'max' => $this->safe_float($market, 'maximum_order_size'),
                ),
                'price' => array (
                    'min' => pow (10, -$precision['price']),
                    'max' => pow (10, $precision['price']),
                ),
            );
            $limits['cost'] = array (
                'min' => $limits['amount']['min'] * $limits['price']['min'],
                'max' => null,
            );
            $result[] = array (
                'id' => $id,
                'symbol' => $symbol,
                'base' => $base,
                'quote' => $quote,
                'baseId' => $baseId,
                'quoteId' => $quoteId,
                'active' => true,
                'precision' => $precision,
                'limits' => $limits,
                'info' => $market,
            );
        }
        return $result;
    }

    public function fetch_balance ($params = array ()) {
        // this api call does not return the 'used' amount - use the v1 version instead (which also returns zero balances)
        $this->load_markets();
        $response = $this->privatePostAuthRWallets ();
        $balanceType = $this->safe_string($params, 'type', 'exchange');
        $result = array ( 'info' => $response );
        for ($b = 0; $b < count ($response); $b++) {
            $balance = $response[$b];
            $accountType = $balance[0];
            $currency = $balance[1];
            $total = $balance[2];
            $available = $balance[4];
            if ($accountType === $balanceType) {
                $code = $currency;
                if (is_array ($this->currencies_by_id) && array_key_exists ($currency, $this->currencies_by_id)) {
                    $code = $this->currencies_by_id[$currency]['code'];
                } else if ($currency[0] === 't') {
                    $currency = mb_substr ($currency, 1);
                    $code = strtoupper ($currency);
                    $code = $this->common_currency_code($code);
                } else {
                    $code = $this->common_currency_code($code);
                }
                $account = $this->account ();
                $account['total'] = $total;
                if (!$available) {
                    if ($available === 0) {
                        $account['free'] = 0;
                        $account['used'] = $total;
                    } else {
                        $account['free'] = $total;
                    }
                } else {
                    $account['free'] = $available;
                    $account['used'] = $account['total'] - $account['free'];
                }
                $result[$code] = $account;
            }
        }
        return $this->parse_balance($result);
    }

    public function fetch_order_book ($symbol, $limit = null, $params = array ()) {
        $this->load_markets();
        $orderbook = $this->publicGetBookSymbolPrecision (array_merge (array (
            'symbol' => $this->market_id($symbol),
            'precision' => 'R0',
        ), $params));
        $timestamp = $this->milliseconds ();
        $result = array (
            'bids' => array (),
            'asks' => array (),
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'nonce' => null,
        );
        for ($i = 0; $i < count ($orderbook); $i++) {
            $order = $orderbook[$i];
            $price = $order[1];
            $amount = $order[2];
            $side = ($amount > 0) ? 'bids' : 'asks';
            $amount = abs ($amount);
            $result[$side][] = array ( $price, $amount );
        }
        $result['bids'] = $this->sort_by($result['bids'], 0, true);
        $result['asks'] = $this->sort_by($result['asks'], 0);
        return $result;
    }

    public function parse_ticker ($ticker, $market = null) {
        $timestamp = $this->milliseconds ();
        $symbol = null;
        if ($market)
            $symbol = $market['symbol'];
        $length = is_array ($ticker) ? count ($ticker) : 0;
        $last = $ticker[$length - 4];
        return array (
            'symbol' => $symbol,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'high' => $ticker[$length - 2],
            'low' => $ticker[$length - 1],
            'bid' => $ticker[$length - 10],
            'bidVolume' => null,
            'ask' => $ticker[$length - 8],
            'askVolume' => null,
            'vwap' => null,
            'open' => null,
            'close' => $last,
            'last' => $last,
            'previousClose' => null,
            'change' => $ticker[$length - 6],
            'percentage' => $ticker[$length - 5] * 100,
            'average' => null,
            'baseVolume' => $ticker[$length - 3],
            'quoteVolume' => null,
            'info' => $ticker,
        );
    }

    public function fetch_tickers ($symbols = null, $params = array ()) {
        $this->load_markets();
        $request = array ();
        if ($symbols !== null) {
            $ids = $this->market_ids($symbols);
            $request['symbols'] = implode (',', $ids);
        } else {
            $request['symbols'] = 'ALL';
        }
        $tickers = $this->publicGetTickers (array_merge ($request, $params));
        $result = array ();
        for ($i = 0; $i < count ($tickers); $i++) {
            $ticker = $tickers[$i];
            $id = $ticker[0];
            if (is_array ($this->markets_by_id) && array_key_exists ($id, $this->markets_by_id)) {
                $market = $this->markets_by_id[$id];
                $symbol = $market['symbol'];
                $result[$symbol] = $this->parse_ticker($ticker, $market);
            }
        }
        return $result;
    }

    public function fetch_ticker ($symbol, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $ticker = $this->publicGetTickerSymbol (array_merge (array (
            'symbol' => $market['id'],
        ), $params));
        return $this->parse_ticker($ticker, $market);
    }

    public function parse_trade ($trade, $market = null) {
        //
        // fetchTrades (public)
        //
        //     array (
        //         ID,
        //         MTS, // $timestamp
        //         AMOUNT,
        //         PRICE
        //     )
        //
        // fetchMyTrades (private)
        //
        //     array (
        //         ID,
        //         PAIR,
        //         MTS_CREATE,
        //         ORDER_ID,
        //         EXEC_AMOUNT,
        //         EXEC_PRICE,
        //         ORDER_TYPE,
        //         ORDER_PRICE,
        //         MAKER,
        //         FEE,
        //         FEE_CURRENCY,
        //         ...
        //     )
        //
        $tradeLength = is_array ($trade) ? count ($trade) : 0;
        $isPrivate = ($tradeLength > 5);
        $id = (string) $trade[0];
        $amountIndex = $isPrivate ? 4 : 2;
        $amount = $trade[$amountIndex];
        $cost = null;
        $priceIndex = $isPrivate ? 5 : 3;
        $price = $trade[$priceIndex];
        $side = null;
        $orderId = null;
        $takerOrMaker = null;
        $type = null;
        $fee = null;
        $symbol = null;
        $timestampIndex = $isPrivate ? 2 : 1;
        $timestamp = $trade[$timestampIndex];
        if ($isPrivate) {
            $marketId = $trade[1];
            if ($marketId !== null) {
                if (is_array ($this->markets_by_id) && array_key_exists ($marketId, $this->markets_by_id)) {
                    $market = $this->markets_by_id[$marketId];
                    $symbol = $market['symbol'];
                } else {
                    $symbol = $marketId;
                }
            }
            $orderId = $trade[3];
            $takerOrMaker = ($trade[8] === 1) ? 'maker' : 'taker';
            $feeCost = $trade[9];
            $feeCurrency = $this->common_currency_code($trade[10]);
            if ($feeCost !== null) {
                $fee = array (
                    'cost' => abs ($feeCost),
                    'currency' => $feeCurrency,
                );
            }
            $orderType = $trade[6];
            $type = $this->safe_string($this->options['orderTypes'], $orderType);
        }
        if ($symbol === null) {
            if ($market !== null) {
                $symbol = $market['symbol'];
            }
        }
        if ($amount !== null) {
            $side = ($amount < 0) ? 'sell' : 'buy';
            $amount = abs ($amount);
            if ($cost === null) {
                if ($price !== null) {
                    $cost = $amount * $price;
                }
            }
        }
        return array (
            'id' => $id,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'symbol' => $symbol,
            'order' => $orderId,
            'side' => $side,
            'type' => $type,
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
        $sort = '-1';
        $request = array (
            'symbol' => $market['id'],
        );
        if ($since !== null) {
            $request['start'] = $since;
            $sort = '1';
        }
        if ($limit !== null) {
            $request['limit'] = $limit; // default 120, max 5000
        }
        $request['sort'] = $sort;
        $response = $this->publicGetTradesSymbolHist (array_merge ($request, $params));
        //
        //     array (
        //         array (
        //             ID,
        //             MTS, // timestamp
        //             AMOUNT,
        //             PRICE
        //         )
        //     )
        //
        $trades = $this->sort_by($response, 1);
        return $this->parse_trades($trades, $market, null, $limit);
    }

    public function fetch_ohlcv ($symbol, $timeframe = '1m', $since = null, $limit = 100, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        if ($limit === null) {
            $limit = 100; // default 100, max 5000
        }
        if ($since === null) {
            $since = $this->milliseconds () - $this->parse_timeframe($timeframe) * $limit * 1000;
        }
        $request = array (
            'symbol' => $market['id'],
            'timeframe' => $this->timeframes[$timeframe],
            'sort' => 1,
            'start' => $since,
            'limit' => $limit,
        );
        $response = $this->publicGetCandlesTradeTimeframeSymbolHist (array_merge ($request, $params));
        return $this->parse_ohlcvs($response, $market, $timeframe, $since, $limit);
    }

    public function create_order ($symbol, $type, $side, $amount, $price = null, $params = array ()) {
        throw new NotSupported ($this->id . ' createOrder not implemented yet');
    }

    public function cancel_order ($id, $symbol = null, $params = array ()) {
        throw new NotSupported ($this->id . ' cancelOrder not implemented yet');
    }

    public function fetch_order ($id, $symbol = null, $params = array ()) {
        throw new NotSupported ($this->id . ' fetchOrder not implemented yet');
    }

    public function fetch_deposit_address ($currency, $params = array ()) {
        throw new NotSupported ($this->id . ' fetchDepositAddress() not implemented yet.');
    }

    public function withdraw ($code, $amount, $address, $tag = null, $params = array ()) {
        throw new NotSupported ($this->id . ' withdraw not implemented yet');
    }

    public function fetch_my_trades ($symbol = null, $since = null, $limit = null, $params = array ()) {
        // $this->has['fetchMyTrades'] is set to false
        // https://github.com/ccxt/ccxt/issues/4971
        $this->load_markets();
        $market = null;
        $request = array (
            'end' => $this->milliseconds (),
        );
        if ($since !== null) {
            $request['start'] = $since;
        }
        if ($limit !== null) {
            $request['limit'] = $limit; // default 25, max 1000
        }
        $method = 'privatePostAuthRTradesHist';
        if ($symbol !== null) {
            $market = $this->market ($symbol);
            $request['symbol'] = $market['id'];
            $method = 'privatePostAuthRTradesSymbolHist';
        }
        $response = $this->$method (array_merge ($request, $params));
        //
        //     array (
        //         array (
        //             ID,
        //             PAIR,
        //             MTS_CREATE,
        //             ORDER_ID,
        //             EXEC_AMOUNT,
        //             EXEC_PRICE,
        //             ORDER_TYPE,
        //             ORDER_PRICE,
        //             MAKER,
        //             FEE,
        //             FEE_CURRENCY,
        //             ...
        //         ),
        //         ...
        //     )
        //
        return $this->parse_trades($response, $market, $since, $limit);
    }

    public function nonce () {
        return $this->milliseconds ();
    }

    public function sign ($path, $api = 'public', $method = 'GET', $params = array (), $headers = null, $body = null) {
        $request = '/' . $this->implode_params($path, $params);
        $query = $this->omit ($params, $this->extract_params($path));
        if ($api === 'v1')
            $request = $api . $request;
        else
            $request = $this->version . $request;
        $url = $this->urls['api'] . '/' . $request;
        if ($api === 'public') {
            if ($query) {
                $url .= '?' . $this->urlencode ($query);
            }
        }
        if ($api === 'private') {
            $this->check_required_credentials();
            $nonce = (string) $this->nonce ();
            $body = $this->json ($query);
            $auth = '/api/' . $request . $nonce . $body;
            $signature = $this->hmac ($this->encode ($auth), $this->encode ($this->secret), 'sha384');
            $headers = array (
                'bfx-nonce' => $nonce,
                'bfx-apikey' => $this->apiKey,
                'bfx-signature' => $signature,
                'Content-Type' => 'application/json',
            );
        }
        return array ( 'url' => $url, 'method' => $method, 'body' => $body, 'headers' => $headers );
    }

    public function request ($path, $api = 'public', $method = 'GET', $params = array (), $headers = null, $body = null) {
        $response = $this->fetch2 ($path, $api, $method, $params, $headers, $body);
        if ($response) {
            if (is_array ($response) && array_key_exists ('message', $response)) {
                if (mb_strpos ($response['message'], 'not enough exchange balance') !== false)
                    throw new InsufficientFunds ($this->id . ' ' . $this->json ($response));
                throw new ExchangeError ($this->id . ' ' . $this->json ($response));
            }
            return $response;
        } else if ($response === '') {
            throw new ExchangeError ($this->id . ' returned empty response');
        }
        return $response;
    }
}
