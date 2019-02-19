<?php

namespace ccxt;

use Exception as Exception; // a common import

class bitmex extends Exchange {

    public function describe () {
        return array_replace_recursive (parent::describe (), array (
            'id' => 'bitmex',
            'name' => 'BitMEX',
            'countries' => array ( 'SC' ), // Seychelles
            'version' => 'v1',
            'userAgent' => null,
            'rateLimit' => 2000,
            'has' => array (
                'CORS' => false,
                'fetchOHLCV' => true,
                'withdraw' => true,
                'editOrder' => true,
                'fetchOrder' => true,
                'fetchOrders' => true,
                'fetchOpenOrders' => true,
                'fetchClosedOrders' => true,
            ),
            'timeframes' => array (
                '1m' => '1m',
                '5m' => '5m',
                '1h' => '1h',
                '1d' => '1d',
            ),
            'urls' => array (
                'test' => 'https://testnet.bitmex.com',
                'logo' => 'https://user-images.githubusercontent.com/1294454/27766319-f653c6e6-5ed4-11e7-933d-f0bc3699ae8f.jpg',
                'api' => 'https://www.bitmex.com',
                'www' => 'https://www.bitmex.com',
                'doc' => array (
                    'https://www.bitmex.com/app/apiOverview',
                    'https://github.com/BitMEX/api-connectors/tree/master/official-http',
                ),
                'fees' => 'https://www.bitmex.com/app/fees',
                'referral' => 'https://www.bitmex.com/register/rm3C16',
            ),
            'api' => array (
                'public' => array (
                    'get' => array (
                        'announcement',
                        'announcement/urgent',
                        'funding',
                        'instrument',
                        'instrument/active',
                        'instrument/activeAndIndices',
                        'instrument/activeIntervals',
                        'instrument/compositeIndex',
                        'instrument/indices',
                        'insurance',
                        'leaderboard',
                        'liquidation',
                        'orderBook',
                        'orderBook/L2',
                        'quote',
                        'quote/bucketed',
                        'schema',
                        'schema/websocketHelp',
                        'settlement',
                        'stats',
                        'stats/history',
                        'trade',
                        'trade/bucketed',
                    ),
                ),
                'private' => array (
                    'get' => array (
                        'apiKey',
                        'chat',
                        'chat/channels',
                        'chat/connected',
                        'execution',
                        'execution/tradeHistory',
                        'notification',
                        'order',
                        'position',
                        'user',
                        'user/affiliateStatus',
                        'user/checkReferralCode',
                        'user/commission',
                        'user/depositAddress',
                        'user/margin',
                        'user/minWithdrawalFee',
                        'user/wallet',
                        'user/walletHistory',
                        'user/walletSummary',
                    ),
                    'post' => array (
                        'apiKey',
                        'apiKey/disable',
                        'apiKey/enable',
                        'chat',
                        'order',
                        'order/bulk',
                        'order/cancelAllAfter',
                        'order/closePosition',
                        'position/isolate',
                        'position/leverage',
                        'position/riskLimit',
                        'position/transferMargin',
                        'user/cancelWithdrawal',
                        'user/confirmEmail',
                        'user/confirmEnableTFA',
                        'user/confirmWithdrawal',
                        'user/disableTFA',
                        'user/logout',
                        'user/logoutAll',
                        'user/preferences',
                        'user/requestEnableTFA',
                        'user/requestWithdrawal',
                    ),
                    'put' => array (
                        'order',
                        'order/bulk',
                        'user',
                    ),
                    'delete' => array (
                        'apiKey',
                        'order',
                        'order/all',
                    ),
                ),
            ),
            'exceptions' => array (
                'exact' => array (
                    'Invalid API Key.' => '\\ccxt\\AuthenticationError',
                    'Access Denied' => '\\ccxt\\PermissionDenied',
                    'Duplicate clOrdID' => '\\ccxt\\InvalidOrder',
                    'Signature not valid' => '\\ccxt\\AuthenticationError',
                ),
                'broad' => array (
                    'overloaded' => '\\ccxt\\ExchangeNotAvailable',
                    'Account has insufficient Available Balance' => '\\ccxt\\InsufficientFunds',
                ),
            ),
            'options' => array (
                'api-expires' => null,
            ),
        ));
    }

    public function fetch_markets ($params = array ()) {
        $response = $this->publicGetInstrumentActiveAndIndices ($params);
        $result = array ();
        for ($i = 0; $i < count ($response); $i++) {
            $market = $response[$i];
            $active = ($market['state'] !== 'Unlisted');
            $id = $market['symbol'];
            $baseId = $market['underlying'];
            $quoteId = $market['quoteCurrency'];
            $basequote = $baseId . $quoteId;
            $base = $this->common_currency_code($baseId);
            $quote = $this->common_currency_code($quoteId);
            $swap = ($id === $basequote);
            // 'positionCurrency' may be empty ("", as Bitmex currently returns for ETHUSD)
            // so let's take the $quote currency first and then adjust if needed
            $positionId = $this->safe_string_2($market, 'positionCurrency', 'quoteCurrency');
            $type = null;
            $future = false;
            $prediction = false;
            $position = $this->common_currency_code($positionId);
            $symbol = $id;
            if ($swap) {
                $type = 'swap';
                $symbol = $base . '/' . $quote;
            } else if (mb_strpos ($id, 'B_') !== false) {
                $prediction = true;
                $type = 'prediction';
            } else {
                $future = true;
                $type = 'future';
            }
            $precision = array (
                'amount' => null,
                'price' => null,
            );
            $lotSize = $this->safe_float($market, 'lotSize');
            $tickSize = $this->safe_float($market, 'tickSize');
            if ($lotSize !== null) {
                $precision['amount'] = $this->precision_from_string($this->truncate_to_string ($lotSize, 16));
            }
            if ($tickSize !== null) {
                $precision['price'] = $this->precision_from_string($this->truncate_to_string ($tickSize, 16));
            }
            $limits = array (
                'amount' => array (
                    'min' => null,
                    'max' => null,
                ),
                'price' => array (
                    'min' => $tickSize,
                    'max' => $this->safe_float($market, 'maxPrice'),
                ),
                'cost' => array (
                    'min' => null,
                    'max' => null,
                ),
            );
            $limitField = ($position === $quote) ? 'cost' : 'amount';
            $limits[$limitField] = array (
                'min' => $lotSize,
                'max' => $this->safe_float($market, 'maxOrderQty'),
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
                'taker' => $market['takerFee'],
                'maker' => $market['makerFee'],
                'type' => $type,
                'spot' => false,
                'swap' => $swap,
                'future' => $future,
                'prediction' => $prediction,
                'info' => $market,
            );
        }
        return $result;
    }

    public function fetch_balance ($params = array ()) {
        $this->load_markets();
        $request = array ( 'currency' => 'all' );
        $response = $this->privateGetUserMargin (array_merge ($request, $params));
        $result = array ( 'info' => $response );
        for ($b = 0; $b < count ($response); $b++) {
            $balance = $response[$b];
            $currencyId = $this->safe_string($balance, 'currency');
            $currencyId = strtoupper ($currencyId);
            $code = $this->common_currency_code($currencyId);
            $account = array (
                'free' => $balance['availableMargin'],
                'used' => 0.0,
                'total' => $balance['marginBalance'],
            );
            if ($code === 'BTC') {
                $account['free'] = $account['free'] * 0.00000001;
                $account['total'] = $account['total'] * 0.00000001;
            }
            $account['used'] = $account['total'] - $account['free'];
            $result[$code] = $account;
        }
        return $this->parse_balance($result);
    }

    public function fetch_order_book ($symbol, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array (
            'symbol' => $market['id'],
        );
        if ($limit !== null)
            $request['depth'] = $limit;
        $orderbook = $this->publicGetOrderBookL2 (array_merge ($request, $params));
        $result = array (
            'bids' => array (),
            'asks' => array (),
            'timestamp' => null,
            'datetime' => null,
            'nonce' => null,
        );
        for ($o = 0; $o < count ($orderbook); $o++) {
            $order = $orderbook[$o];
            $side = ($order['side'] === 'Sell') ? 'asks' : 'bids';
            $amount = $order['size'];
            $price = $order['price'];
            $result[$side][] = array ( $price, $amount );
        }
        $result['bids'] = $this->sort_by($result['bids'], 0, true);
        $result['asks'] = $this->sort_by($result['asks'], 0);
        return $result;
    }

    public function fetch_order ($id, $symbol = null, $params = array ()) {
        $filter = array ( 'filter' => array ( 'orderID' => $id ));
        $result = $this->fetch_orders($symbol, null, null, array_replace_recursive ($filter, $params));
        $numResults = is_array ($result) ? count ($result) : 0;
        if ($numResults === 1)
            return $result[0];
        throw new OrderNotFound ($this->id . ' => The order ' . $id . ' not found.');
    }

    public function fetch_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = null;
        $request = array ();
        if ($symbol !== null) {
            $market = $this->market ($symbol);
            $request['symbol'] = $market['id'];
        }
        if ($since !== null)
            $request['startTime'] = $this->iso8601 ($since);
        if ($limit !== null)
            $request['count'] = $limit;
        $request = array_replace_recursive ($request, $params);
        // why the hassle? urlencode in python is kinda broken for nested dicts.
        // E.g. self.urlencode(array ("filter" => array ("open" => True))) will return "filter=array ('open':+True)"
        // Bitmex doesn't like that. Hence resorting to this hack.
        if (is_array ($request) && array_key_exists ('filter', $request))
            $request['filter'] = $this->json ($request['filter']);
        $response = $this->privateGetOrder ($request);
        return $this->parse_orders($response, $market, $since, $limit);
    }

    public function fetch_open_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        $filter_params = array ( 'filter' => array ( 'open' => true ));
        return $this->fetch_orders($symbol, $since, $limit, array_replace_recursive ($filter_params, $params));
    }

    public function fetch_closed_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        // Bitmex barfs if you set 'open' => false in the filter...
        $orders = $this->fetch_orders($symbol, $since, $limit, $params);
        return $this->filter_by($orders, 'status', 'closed');
    }

    public function fetch_ticker ($symbol, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        if (!$market['active']) {
            throw new ExchangeError ($this->id . ' => $symbol ' . $symbol . ' is delisted');
        }
        $request = array (
            'symbol' => $market['id'],
        );
        $response = $this->publicGetInstrumentActiveAndIndices (array_merge ($request, $params));
        return $this->parse_ticker($response[0]);
    }

    public function parse_ticker ($ticker, $market = null) {
        //
        //     {                         $symbol => "ETHH19",
        //                           rootSymbol => "ETH",
        //                                state => "Open",
        //                                  typ => "FFCCSX",
        //                              listing => "2018-12-17T04:00:00.000Z",
        //                                front => "2019-02-22T12:00:00.000Z",
        //                               expiry => "2019-03-29T12:00:00.000Z",
        //                               settle => "2019-03-29T12:00:00.000Z",
        //                       relistInterval =>  null,
        //                           inverseLeg => "",
        //                              sellLeg => "",
        //                               buyLeg => "",
        //                     optionStrikePcnt =>  null,
        //                    optionStrikeRound =>  null,
        //                    optionStrikePrice =>  null,
        //                     optionMultiplier =>  null,
        //                     positionCurrency => "ETH",
        //                           underlying => "ETH",
        //                        quoteCurrency => "XBT",
        //                     underlyingSymbol => "ETHXBT=",
        //                            reference => "BMEX",
        //                      referenceSymbol => ".BETHXBT30M",
        //                         calcInterval =>  null,
        //                      publishInterval =>  null,
        //                          publishTime =>  null,
        //                          maxOrderQty =>  100000000,
        //                             maxPrice =>  10,
        //                              lotSize =>  1,
        //                             tickSize =>  0.00001,
        //                           multiplier =>  100000000,
        //                        settlCurrency => "XBt",
        //       underlyingToPositionMultiplier =>  1,
        //         underlyingToSettleMultiplier =>  null,
        //              quoteToSettleMultiplier =>  100000000,
        //                             isQuanto =>  false,
        //                            isInverse =>  false,
        //                           initMargin =>  0.02,
        //                          maintMargin =>  0.01,
        //                            riskLimit =>  5000000000,
        //                             riskStep =>  5000000000,
        //                                limit =>  null,
        //                               capped =>  false,
        //                                taxed =>  true,
        //                           deleverage =>  true,
        //                             makerFee =>  -0.0005,
        //                             takerFee =>  0.0025,
        //                        settlementFee =>  0,
        //                         insuranceFee =>  0,
        //                    fundingBaseSymbol => "",
        //                   fundingQuoteSymbol => "",
        //                 fundingPremiumSymbol => "",
        //                     fundingTimestamp =>  null,
        //                      fundingInterval =>  null,
        //                          fundingRate =>  null,
        //                indicativeFundingRate =>  null,
        //                   rebalanceTimestamp =>  null,
        //                    rebalanceInterval =>  null,
        //                     openingTimestamp => "2019-02-13T08:00:00.000Z",
        //                     closingTimestamp => "2019-02-13T09:00:00.000Z",
        //                      sessionInterval => "2000-01-01T01:00:00.000Z",
        //                       prevClosePrice =>  0.03347,
        //                       limitDownPrice =>  null,
        //                         limitUpPrice =>  null,
        //               bankruptLimitDownPrice =>  null,
        //                 bankruptLimitUpPrice =>  null,
        //                      prevTotalVolume =>  1386531,
        //                          totalVolume =>  1387062,
        //                               volume =>  531,
        //                            volume24h =>  17118,
        //                    prevTotalTurnover =>  4741294246000,
        //                        totalTurnover =>  4743103466000,
        //                             turnover =>  1809220000,
        //                          turnover24h =>  57919845000,
        //                      homeNotional24h =>  17118,
        //                   foreignNotional24h =>  579.19845,
        //                         prevPrice24h =>  0.03349,
        //                                 vwap =>  0.03383564,
        //                            highPrice =>  0.03458,
        //                             lowPrice =>  0.03329,
        //                            lastPrice =>  0.03406,
        //                   lastPriceProtected =>  0.03406,
        //                    lastTickDirection => "ZeroMinusTick",
        //                       lastChangePcnt =>  0.017,
        //                             bidPrice =>  0.03406,
        //                             midPrice =>  0.034065,
        //                             askPrice =>  0.03407,
        //                       impactBidPrice =>  0.03406,
        //                       impactMidPrice =>  0.034065,
        //                       impactAskPrice =>  0.03407,
        //                         hasLiquidity =>  true,
        //                         openInterest =>  83679,
        //                            openValue =>  285010674000,
        //                           fairMethod => "ImpactMidPrice",
        //                        fairBasisRate =>  0,
        //                            fairBasis =>  0,
        //                            fairPrice =>  0.03406,
        //                           markMethod => "FairPrice",
        //                            markPrice =>  0.03406,
        //                    indicativeTaxRate =>  0,
        //                indicativeSettlePrice =>  0.03406,
        //                optionUnderlyingPrice =>  null,
        //                         settledPrice =>  null,
        //                            $timestamp => "2019-02-13T08:40:30.000Z",
        //     }
        //
        $symbol = null;
        $marketId = $this->safe_string($ticker, 'symbol');
        $market = $this->safe_value($this->markets_by_id, $marketId, $market);
        if ($market !== null) {
            $symbol = $market['symbol'];
        }
        $timestamp = $this->parse8601 ($this->safe_string($ticker, 'timestamp'));
        $open = $this->safe_float($ticker, 'prevPrice24h');
        $last = $this->safe_float($ticker, 'lastPrice');
        $change = null;
        $percentage = null;
        if ($last !== null && $open !== null) {
            $change = $last - $open;
            if ($open > 0) {
                $percentage = $change / $open * 100;
            }
        }
        return array (
            'symbol' => $symbol,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'high' => $this->safe_float($ticker, 'highPrice'),
            'low' => $this->safe_float($ticker, 'lowPrice'),
            'bid' => $this->safe_float($ticker, 'bidPrice'),
            'bidVolume' => null,
            'ask' => $this->safe_float($ticker, 'askPrice'),
            'askVolume' => null,
            'vwap' => $this->safe_float($ticker, 'vwap'),
            'open' => $open,
            'close' => $last,
            'last' => $last,
            'previousClose' => null,
            'change' => $change,
            'percentage' => $percentage,
            'average' => $this->sum ($open, $last) / 2,
            'baseVolume' => $this->safe_float($ticker, 'homeNotional24h'),
            'quoteVolume' => $this->safe_float($ticker, 'foreignNotional24h'),
            'info' => $ticker,
        );
    }

    public function parse_ohlcv ($ohlcv, $market = null, $timeframe = '1m', $since = null, $limit = null) {
        $timestamp = $this->parse8601 ($ohlcv['timestamp']);
        return array (
            $timestamp,
            $this->safe_float($ohlcv, 'open'),
            $this->safe_float($ohlcv, 'high'),
            $this->safe_float($ohlcv, 'low'),
            $this->safe_float($ohlcv, 'close'),
            $this->safe_float($ohlcv, 'volume'),
        );
    }

    public function fetch_ohlcv ($symbol, $timeframe = '1m', $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        // send JSON key/value pairs, such as array ("key" => "value")
        // $filter by individual fields and do advanced queries on timestamps
        // $filter = array ( 'key' => 'value' );
        // send a bare series (e.g. XBU) to nearest expiring contract in that series
        // you can also send a $timeframe, e.g. XBU:monthly
        // timeframes => daily, weekly, monthly, quarterly, and biquarterly
        $market = $this->market ($symbol);
        $request = array (
            'symbol' => $market['id'],
            'binSize' => $this->timeframes[$timeframe],
            'partial' => true,     // true == include yet-incomplete current bins
            // 'filter' => $filter, // $filter by individual fields and do advanced queries
            // 'columns' => array (),    // will return all columns if omitted
            // 'start' => 0,       // starting point for results (wtf?)
            // 'reverse' => false, // true == newest first
            // 'endTime' => '',    // ending date $filter for results
        );
        if ($limit !== null)
            $request['count'] = $limit; // default 100, max 500
        // if $since is not set, they will return candles starting from 2017-01-01
        if ($since !== null) {
            $ymdhms = $this->ymdhms ($since);
            $request['startTime'] = $ymdhms; // starting date $filter for results
        }
        $response = $this->publicGetTradeBucketed (array_merge ($request, $params));
        return $this->parse_ohlcvs($response, $market, $timeframe, $since, $limit);
    }

    public function parse_trade ($trade, $market = null) {
        $timestamp = $this->parse8601 ($trade['timestamp']);
        $symbol = null;
        if ($market === null) {
            if (is_array ($trade) && array_key_exists ('symbol', $trade))
                $market = $this->markets_by_id[$trade['symbol']];
        }
        if ($market)
            $symbol = $market['symbol'];
        return array (
            'id' => $trade['trdMatchID'],
            'info' => $trade,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'symbol' => $symbol,
            'order' => null,
            'type' => null,
            'side' => strtolower ($trade['side']),
            'price' => $trade['price'],
            'amount' => $trade['size'],
        );
    }

    public function parse_order_status ($status) {
        $statuses = array (
            'New' => 'open',
            'PartiallyFilled' => 'open',
            'Filled' => 'closed',
            'DoneForDay' => 'open',
            'Canceled' => 'canceled',
            'PendingCancel' => 'open',
            'PendingNew' => 'open',
            'Rejected' => 'rejected',
            'Expired' => 'expired',
            'Stopped' => 'open',
            'Untriggered' => 'open',
            'Triggered' => 'open',
        );
        return $this->safe_string($statuses, $status, $status);
    }

    public function parse_order ($order, $market = null) {
        $status = $this->parse_order_status($this->safe_string($order, 'ordStatus'));
        $symbol = null;
        if ($market !== null) {
            $symbol = $market['symbol'];
        } else {
            $id = $order['symbol'];
            if (is_array ($this->markets_by_id) && array_key_exists ($id, $this->markets_by_id)) {
                $market = $this->markets_by_id[$id];
                $symbol = $market['symbol'];
            }
        }
        $timestamp = $this->parse8601 ($this->safe_string($order, 'timestamp'));
        $lastTradeTimestamp = $this->parse8601 ($this->safe_string($order, 'transactTime'));
        $price = $this->safe_float($order, 'price');
        $amount = $this->safe_float($order, 'orderQty');
        $filled = $this->safe_float($order, 'cumQty', 0.0);
        $remaining = null;
        if ($amount !== null) {
            if ($filled !== null) {
                $remaining = max ($amount - $filled, 0.0);
            }
        }
        $cost = null;
        if ($price !== null)
            if ($filled !== null)
                $cost = $price * $filled;
        $result = array (
            'info' => $order,
            'id' => (string) $order['orderID'],
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'lastTradeTimestamp' => $lastTradeTimestamp,
            'symbol' => $symbol,
            'type' => strtolower ($order['ordType']),
            'side' => strtolower ($order['side']),
            'price' => $price,
            'amount' => $amount,
            'cost' => $cost,
            'filled' => $filled,
            'remaining' => $remaining,
            'status' => $status,
            'fee' => null,
        );
        return $result;
    }

    public function fetch_trades ($symbol, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array (
            'symbol' => $market['id'],
        );
        if ($since !== null)
            $request['startTime'] = $this->iso8601 ($since);
        if ($limit !== null)
            $request['count'] = $limit;
        $response = $this->publicGetTrade (array_merge ($request, $params));
        return $this->parse_trades($response, $market);
    }

    public function create_order ($symbol, $type, $side, $amount, $price = null, $params = array ()) {
        $this->load_markets();
        $request = array (
            'symbol' => $this->market_id($symbol),
            'side' => $this->capitalize ($side),
            'orderQty' => $amount,
            'ordType' => $this->capitalize ($type),
        );
        if ($price !== null)
            $request['price'] = $price;
        $response = $this->privatePostOrder (array_merge ($request, $params));
        $order = $this->parse_order($response);
        $id = $order['id'];
        $this->orders[$id] = $order;
        return array_merge (array ( 'info' => $response ), $order);
    }

    public function edit_order ($id, $symbol, $type, $side, $amount = null, $price = null, $params = array ()) {
        $this->load_markets();
        $request = array (
            'orderID' => $id,
        );
        if ($amount !== null)
            $request['orderQty'] = $amount;
        if ($price !== null)
            $request['price'] = $price;
        $response = $this->privatePutOrder (array_merge ($request, $params));
        $order = $this->parse_order($response);
        $this->orders[$order['id']] = $order;
        return array_merge (array ( 'info' => $response ), $order);
    }

    public function cancel_order ($id, $symbol = null, $params = array ()) {
        $this->load_markets();
        $response = $this->privateDeleteOrder (array_merge (array ( 'orderID' => $id ), $params));
        $order = $response[0];
        $error = $this->safe_string($order, 'error');
        if ($error !== null)
            if (mb_strpos ($error, 'Unable to cancel $order due to existing state') !== false)
                throw new OrderNotFound ($this->id . ' cancelOrder() failed => ' . $error);
        $order = $this->parse_order($order);
        $this->orders[$order['id']] = $order;
        return array_merge (array ( 'info' => $response ), $order);
    }

    public function is_fiat ($currency) {
        if ($currency === 'EUR')
            return true;
        if ($currency === 'PLN')
            return true;
        return false;
    }

    public function withdraw ($code, $amount, $address, $tag = null, $params = array ()) {
        $this->check_address($address);
        $this->load_markets();
        // $currency = $this->currency ($code);
        if ($code !== 'BTC') {
            throw new ExchangeError ($this->id . ' supoprts BTC withdrawals only, other currencies coming soon...');
        }
        $request = array (
            'currency' => 'XBt', // temporarily
            'amount' => $amount,
            'address' => $address,
            // 'otpToken' => '123456', // requires if two-factor auth (OTP) is enabled
            // 'fee' => 0.001, // bitcoin network fee
        );
        $response = $this->privatePostUserRequestWithdrawal (array_merge ($request, $params));
        return array (
            'info' => $response,
            'id' => $response['transactID'],
        );
    }

    public function handle_errors ($code, $reason, $url, $method, $headers, $body, $response) {
        if ($code === 429)
            throw new DDoSProtection ($this->id . ' ' . $body);
        if ($code >= 400) {
            if ($body) {
                if ($body[0] === '{') {
                    $error = $this->safe_value($response, 'error', array ());
                    $message = $this->safe_string($error, 'message');
                    $feedback = $this->id . ' ' . $body;
                    $exact = $this->exceptions['exact'];
                    if (is_array ($exact) && array_key_exists ($message, $exact)) {
                        throw new $exact[$message] ($feedback);
                    }
                    $broad = $this->exceptions['broad'];
                    $broadKey = $this->findBroadlyMatchedKey ($broad, $message);
                    if ($broadKey !== null) {
                        throw new $broad[$broadKey] ($feedback);
                    }
                    if ($code === 400) {
                        throw new BadRequest ($feedback);
                    }
                    throw new ExchangeError ($feedback); // unknown $message
                }
            }
        }
    }

    public function nonce () {
        return $this->milliseconds ();
    }

    public function sign ($path, $api = 'public', $method = 'GET', $params = array (), $headers = null, $body = null) {
        $query = '/api/' . $this->version . '/' . $path;
        if ($method !== 'PUT')
            if ($params)
                $query .= '?' . $this->urlencode ($params);
        $url = $this->urls['api'] . $query;
        if ($api === 'private') {
            $this->check_required_credentials();
            $auth = $method . $query;
            $expires = $this->safe_integer($this->options, 'api-expires');
            $nonce = (string) $this->nonce ();
            $headers = array (
                'Content-Type' => 'application/json',
                'api-key' => $this->apiKey,
            );
            if ($expires !== null) {
                $expires = $this->sum ($this->seconds (), $expires);
                $expires = (string) $expires;
                $auth .= $expires;
                $headers['api-expires'] = $expires;
            } else {
                $auth .= $nonce;
                $headers['api-nonce'] = $nonce;
            }
            if ($method === 'POST' || $method === 'PUT') {
                if ($params) {
                    $body = $this->json ($params);
                    $auth .= $body;
                }
            }
            $headers['api-signature'] = $this->hmac ($this->encode ($auth), $this->encode ($this->secret));
        }
        return array ( 'url' => $url, 'method' => $method, 'body' => $body, 'headers' => $headers );
    }
}
