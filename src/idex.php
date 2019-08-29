<?php

namespace ccxt;

use Exception as Exception; // a common import

class idex extends Exchange {

    public function describe () {
        return array_replace_recursive (parent::describe (), array (
            'id' => 'idex',
            'name' => 'IDEX',
            'countries' => array ( 'US' ),
            'rateLimit' => 1500,
            'certified' => true,
            'requiresWeb3' => true,
            'has' => array (
                'fetchOrderBook' => true,
                'fetchTicker' => true,
                'fetchTickers' => true,
                'fetchMarkets' => true,
                'fetchBalance' => true,
                'createOrder' => true,
                'cancelOrder' => true,
                'fetchTransactions' => true,
                'fetchTrades' => false,
                'fetchMyTrades' => true,
                'withdraw' => true,
                'fetchOHLCV' => false,
            ),
            'timeframes' => array (
                '1m' => 'M1',
                '3m' => 'M3',
                '5m' => 'M5',
                '15m' => 'M15',
                '30m' => 'M30', // default
                '1h' => 'H1',
                '4h' => 'H4',
                '1d' => 'D1',
                '1w' => 'D7',
                '1M' => '1M',
            ),
            'urls' => array (
                'test' => 'https://api.idex.market',
                'logo' => 'https://user-images.githubusercontent.com/1294454/63693236-3415e380-c81c-11e9-8600-ba1634f1407d.jpg',
                'api' => 'https://api.idex.market',
                'www' => 'https://idex.market',
                'doc' => array (
                    'https://github.com/AuroraDAO/idex-api-docs',
                ),
            ),
            'api' => array (
                'public' => array (
                    'post' => array (
                        'returnTicker',
                        'returnCurrenciesWithPairs', // undocumented
                        'returnCurrencies',
                        'return24Volume',
                        'returnBalances',
                        'returnCompleteBalances', // shows amount in orders as well as total
                        'returnDepositsWithdrawals',
                        'returnOpenOrders',
                        'returnOrderBook',
                        'returnOrderStatus',
                        'returnOrderTrades',
                        'returnTradeHistory',
                        'returnTradeHistoryMeta', // not documented
                        'returnContractAddress',
                        'returnNextNonce',
                    ),
                ),
                'private' => array (
                    'post' => array (
                        'order',
                        'cancel',
                        'trade',
                        'withdraw',
                    ),
                ),
            ),
            'options' => array (
                'contractAddress' => null,  // 0x2a0c0DBEcC7E4D658f48E01e3fA353F44050c208
                'orderNonce' => null,
            ),
            'exceptions' => array (
                'Invalid order signature. Please try again.' => '\\ccxt\\AuthenticationError',
                'You have insufficient funds to match this order. If you believe this is a mistake please refresh and try again.' => '\\ccxt\\InsufficientFunds',
                'Order no longer available.' => '\\ccxt\\InvalidOrder',
            ),
            'requiredCredentials' => array (
                'walletAddress' => true,
                'privateKey' => true,
                'apiKey' => false,
                'secret' => false,
            ),
        ));
    }

    public function fetch_markets ($params = array ()) {
        // idex does not have an endpoint for $markets
        // instead we generate the $markets from the endpoint for $currencies
        $request = array (
            'includeDelisted' => true,
        );
        $markets = $this->publicPostReturnCurrenciesWithPairs (array_merge ($request, $params));
        $currenciesById = array();
        $currencies = $markets['tokens'];
        for ($i = 0; $i < count ($currencies); $i++) {
            $currency = $currencies[$i];
            $currenciesById[$currency['symbol']] = $currency;
        }
        $result = array();
        $limits = array (
            'amount' => array (
                'min' => null,
                'max' => null,
            ),
            'price' => array (
                'min' => null,
                'max' => null,
            ),
            'cost' => array (
                'min' => null,
                'max' => null,
            ),
        );
        $quotes = $markets['pairs'];
        $keys = is_array($quotes) ? array_keys($quotes) : array();
        for ($i = 0; $i < count ($keys); $i++) {
            $quoteId = $keys[$i];
            $bases = $quotes[$quoteId];
            $quote = $this->safe_currency_code($quoteId);
            $quoteCurrency = $currenciesById[$quoteId];
            for ($j = 0; $j < count ($bases); $j++) {
                $baseId = $bases[$j];
                $id = $quoteId . '_' . $baseId;
                $base = $this->safe_currency_code($baseId);
                $symbol = $base . '/' . $quote;
                $baseCurrency = $currenciesById[$baseId];
                $baseAddress = $baseCurrency['address'];
                $quoteAddress = $quoteCurrency['address'];
                $precision = array (
                    'price' => $this->safe_integer($quoteCurrency, 'decimals'),
                    'amount' => $this->safe_integer($baseCurrency, 'decimals'),
                );
                $result[] = array (
                    'symbol' => $symbol,
                    'precision' => $precision,
                    'base' => $base,
                    'quote' => $quote,
                    'baseId' => $baseAddress,
                    'quoteId' => $quoteAddress,
                    'limits' => $limits,
                    'id' => $id,
                    'info' => $baseCurrency,
                    'tierBased' => false,
                );
            }
        }
        return $result;
    }

    public function parse_ticker ($ticker, $market = null) {
        //
        //     {
        //         $last => '0.0016550916',
        //         high => 'N/A',
        //         low => 'N/A',
        //         lowestAsk => '0.0016743368',
        //         highestBid => '0.001163726270773897',
        //         percentChange => '0',
        //         $baseVolume => '0',
        //         $quoteVolume => '0'
        //     }
        //
        $symbol = null;
        if ($market) {
            $symbol = $market['symbol'];
        }
        $baseVolume = $this->safe_float($ticker, 'baseVolume');
        $quoteVolume = $this->safe_float($ticker, 'quoteVolume');
        $last = $this->safe_float($ticker, 'last');
        $percentage = $this->safe_float($ticker, 'percentChange');
        return array (
            'symbol' => $symbol,
            'timestamp' => null,
            'datetime' => null,
            'high' => $this->safe_float($ticker, 'high'),
            'low' => $this->safe_float($ticker, 'low'),
            'bid' => $this->safe_float($ticker, 'highestBid'),
            'bidVolume' => null,
            'ask' => $this->safe_float($ticker, 'lowestAsk'),
            'askVolume' => null,
            'vwap' => null,
            'open' => null,
            'close' => $last,
            'last' => $last,
            'previousClose' => null,
            'change' => null,
            'percentage' => $percentage,
            'average' => null,
            'baseVolume' => $baseVolume,
            'quoteVolume' => $quoteVolume,
            'info' => $ticker,
        );
    }

    public function fetch_tickers ($symbols = null, $params = array ()) {
        $this->load_markets();
        $response = $this->publicPostReturnTicker ($params);
        //  { ETH_BOUNCY:
        //    array ( last => '0.000000004000088005',
        //      high => 'N/A',
        //      low => 'N/A',
        //      lowestAsk => '0.00000000599885995',
        //      highestBid => '0.000000001400500103',
        //      percentChange => '0',
        //      baseVolume => '0',
        //      quoteVolume => '0' ),
        //   ETH_NBAI:
        //    array ( last => '0.0000032',
        //      high => 'N/A',
        //      low => 'N/A',
        //      lowestAsk => '0.000004000199999502',
        //      highestBid => '0.0000016002',
        //      percentChange => '0',
        //      baseVolume => '0',
        //      quoteVolume => '0' ), }
        $ids = is_array($response) ? array_keys($response) : array();
        $result = array();
        for ($i = 0; $i < count ($ids); $i++) {
            $id = $ids[$i];
            $symbol = null;
            $market = null;
            if (is_array($this->markets_by_id) && array_key_exists($id, $this->markets_by_id)) {
                $market = $this->markets_by_id[$id];
                $symbol = $market['symbol'];
            } else {
                list($quoteId, $baseId) = explode('_', $id);
                $base = $this->safe_currency_code($baseId);
                $quote = $this->safe_currency_code($quoteId);
                $symbol = $base . '/' . $quote;
                $market = array( 'symbol' => $symbol );
            }
            $ticker = $response[$id];
            $result[$symbol] = $this->parse_ticker($ticker, $market);
        }
        return $result;
    }

    public function fetch_ticker ($symbol, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array (
            'market' => $market['id'],
        );
        $response = $this->publicPostReturnTicker (array_merge ($request, $params));
        // { last => '0.0016550916',
        //   high => 'N/A',
        //   low => 'N/A',
        //   lowestAsk => '0.0016743368',
        //   highestBid => '0.001163726270773897',
        //   percentChange => '0',
        //   baseVolume => '0',
        //   quoteVolume => '0' }
        return $this->parse_ticker($response, $market);
    }

    public function fetch_order_book ($symbol, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $id = $market['quote'] . '_' . $market['base'];
        $request = array (
            'market' => $id,
            'count' => 100, // the default will only return one trade
        );
        if ($limit !== null) {
            $request['count'] = $limit;
        }
        $response = $this->publicPostReturnOrderBook (array_merge ($request, $params));
        //
        //     {
        //         "asks" => array (
        //             {
        //                 "price" => "0.001675282799999",
        //                 "amount" => "206.163978911921061732",
        //                 "total" => "0.345382967850497906",
        //                 "orderHash" => "0xfdf12c124a6a7fa4a8e1866b324da888c8e1b3ad209f5050d3a23df3397a5cb7",
        //                 "$params" => {
        //                     "tokenBuy" => "0x0000000000000000000000000000000000000000",
        //                     "buySymbol" => "ETH",
        //                     "buyPrecision" => 18,
        //                     "amountBuy" => "345382967850497906",
        //                     "tokenSell" => "0xb98d4c97425d9908e66e53a6fdf673acca0be986",
        //                     "sellSymbol" => "ABT",
        //                     "sellPrecision" => 18,
        //                     "amountSell" => "206163978911921061732",
        //                     "expires" => 10000,
        //                     "nonce" => 13489307413,
        //                     "user" => "0x9e8ef79316a4a79bbf55a5f9c16b3e068fff65c6"
        //                 }
        //             }
        //         ),
        //         "bids" => array (
        //             {
        //                 "price" => "0.001161865193232242",
        //                 "amount" => "854.393661648355",
        //                 "total" => "0.992690256787469029",
        //                 "orderHash" => "0x2f2baaf982085e4096f9e23e376214885fa74b2939497968e92222716fc2c86d",
        //                 "$params" => {
        //                     "tokenBuy" => "0xb98d4c97425d9908e66e53a6fdf673acca0be986",
        //                     "buySymbol" => "ABT",
        //                     "buyPrecision" => 18,
        //                     "amountBuy" => "854393661648355000000",
        //                     "tokenSell" => "0x0000000000000000000000000000000000000000",
        //                     "sellSymbol" => "ETH",
        //                     "sellPrecision" => 18,
        //                     "amountSell" => "992690256787469029",
        //                     "expires" => 10000,
        //                     "nonce" => 18155189676,
        //                     "user" => "0xb631284dd7b74a846af5b37766ceb1f85d53eca4"
        //                 }
        //             }
        //         )
        //     }
        //
        return $this->parse_order_book($response, null, 'bids', 'asks', 'price', 'amount');
    }

    public function parse_bid_ask ($bidAsk, $priceKey = 0, $amountKey = 1) {
        $price = $this->safe_float($bidAsk, $priceKey);
        $amount = $this->safe_float($bidAsk, $amountKey);
        $info = $bidAsk;
        return [$price, $amount, $info];
    }

    public function fetch_balance ($params = array ()) {
        $request = array (
            'address' => $this->walletAddress,
        );
        $response = $this->publicPostReturnCompleteBalances (array_merge ($request, $params));
        //
        //     {
        //         ETH => array( available => '0.0167', onOrders => '0.1533' )
        //     }
        //
        $result = array (
            'info' => $response,
        );
        $keys = is_array($response) ? array_keys($response) : array();
        for ($i = 0; $i < count ($keys); $i++) {
            $currency = $keys[$i];
            $balance = $response[$currency];
            $code = $this->safe_currency_code($currency);
            $result[$code] = array (
                'free' => $this->safe_float($balance, 'available'),
                'used' => $this->safe_float($balance, 'onOrders'),
            );
        }
        return $this->parse_balance($result);
    }

    public function create_order ($symbol, $type, $side, $amount, $price = null, $params = array ()) {
        $this->check_required_dependencies();
        $this->load_markets();
        $market = $this->market ($symbol);
        if ($type === 'limit') {
            $expires = 100000;
            $contractAddress = $this->get_contract_address ();
            $tokenBuy = null;
            $tokenSell = null;
            $amountBuy = null;
            $amountSell = null;
            $quoteAmount = floatval ($price) * floatval ($amount);
            if ($side === 'buy') {
                $tokenBuy = $market['baseId'];
                $tokenSell = $market['quoteId'];
                $amountBuy = $this->toWei ($amount, 'ether', $market['precision']['amount']);
                $amountSell = $this->toWei ($quoteAmount, 'ether', 18);
            } else {
                $tokenBuy = $market['quoteId'];
                $tokenSell = $market['baseId'];
                $amountBuy = $this->toWei ($quoteAmount, 'ether', 18);
                $amountSell = $this->toWei ($amount, 'ether', $market['precision']['amount']);
            }
            $nonce = $this->get_nonce ();
            $orderToHash = array (
                'contractAddress' => $contractAddress,
                'tokenBuy' => $tokenBuy,
                'amountBuy' => $amountBuy,
                'tokenSell' => $tokenSell,
                'amountSell' => $amountSell,
                'expires' => $expires,
                'nonce' => $nonce,
                'address' => $this->walletAddress,
            );
            $orderHash = $this->get_idex_create_order_hash ($orderToHash);
            $signature = $this->signMessage ($orderHash, $this->privateKey);
            $request = array (
                'tokenBuy' => $tokenBuy,
                'amountBuy' => $amountBuy,
                'tokenSell' => $tokenSell,
                'amountSell' => $amountSell,
                'address' => $this->walletAddress,
                'nonce' => $nonce,
                'expires' => $expires,
            );
            $response = $this->privatePostOrder (array_merge ($request, $signature)); // array_merge ($request, $params) will cause invalid $signature
            // { orderNumber => 1562323021,
            //   $orderHash:
            //    '0x31c42154a8421425a18d076df400d9ec1ef64d5251285384a71ba3c0ab31beb4',
            //   timestamp => 1564041428,
            //   $price => '0.00073',
            //   $amount => '210',
            //   total => '0.1533',
            //   $type => 'buy',
            //   $params:
            //    { $tokenBuy => '0x763fa6806e1acf68130d2d0f0df754c93cc546b2',
            //      buyPrecision => 18,
            //      $amountBuy => '210000000000000000000',
            //      $tokenSell => '0x0000000000000000000000000000000000000000',
            //      sellPrecision => 18,
            //      $amountSell => '153300000000000000',
            //      $expires => 100000,
            //      $nonce => 1,
            //      user => '0x0ab991497116f7f5532a4c2f4f7b1784488628e1' } }
            return $this->parse_order($response, $market);
        } else if ($type === 'market') {
            if (!(is_array($params) && array_key_exists('orderHash', $params))) {
                throw new ArgumentsRequired($this->id . ' $market order requires an order structure such as that in fetchOrderBook()[\'bids\'][0][2], fetchOrder()[\'info\'], or fetchOpenOrders()[0][\'info\']');
            }
            // { $price => '0.000132247803328924',
            //   $amount => '19980',
            //   total => '2.6423111105119',
            //   $orderHash:
            //    '0x5fb3452b3d13fc013585b51c91c43a0fbe4298c211243763c49437848c274749',
            //   $params:
            //    { $tokenBuy => '0x0000000000000000000000000000000000000000',
            //      buySymbol => 'ETH',
            //      buyPrecision => 18,
            //      $amountBuy => '2642311110511900000',
            //      $tokenSell => '0xb705268213d593b8fd88d3fdeff93aff5cbdcfae',
            //      sellSymbol => 'IDEX',
            //      sellPrecision => 18,
            //      $amountSell => '19980000000000000000000',
            //      $expires => 10000,
            //      $nonce => 1564656561510,
            //      user => '0xc3f8304270e49b8e8197bfcfd8567b83d9e4479b' } }
            $orderToSign = array (
                'orderHash' => $params['orderHash'],
                'amount' => $params['params']['amountBuy'],
                'address' => $params['params']['user'],
                'nonce' => $params['params']['nonce'],
            );
            $orderHash = $this->get_idex_market_order_hash ($orderToSign);
            $signature = $this->signMessage ($orderHash, $this->privateKey);
            $signedOrder = array_merge ($orderToSign, $signature);
            $signedOrder['address'] = $this->walletAddress;
            $signedOrder['nonce'] = $this->get_nonce ();
            //   array ( {
            //     "$amount" => "0.07",
            //     "date" => "2017-10-13 16:25:36",
            //     "total" => "0.49",
            //     "$market" => "ETH_DVIP",
            //     "$type" => "buy",
            //     "$price" => "7",
            //     "$orderHash" => "0xcfe4018c59e50e0e1964c979e6213ce5eb8c751cbc98a44251eb48a0985adc52",
            //     "uuid" => "250d51a0-b033-11e7-9984-a9ab79bb8f35"
            //   } )
            $response = $this->privatePostTrade ($signedOrder);
            return $this->parse_orders($response, $market);
        }
    }

    public function get_nonce () {
        if ($this->options['orderNonce'] === null) {
            $response = $this->publicPostReturnNextNonce (array (
                'address' => $this->walletAddress,
            ));
            return $this->safe_integer($response, 'nonce');
        } else {
            $result = $this->options['orderNonce'];
            $this->options['orderNonce'] = $this->sum ($this->options['orderNonce'], 1);
            return $result;
        }
    }

    public function get_contract_address () {
        if ($this->options['contractAddress'] !== null) {
            return $this->options['contractAddress'];
        }
        $response = $this->publicPostReturnContractAddress ();
        $this->options['contractAddress'] = $this->safe_string($response, 'address');
        return $this->options['contractAddress'];
    }

    public function cancel_order ($orderId, $symbol = null, $params = array ()) {
        $nonce = $this->get_nonce ();
        $orderToHash = array (
            'orderHash' => $orderId,
            'nonce' => $nonce,
        );
        $orderHash = $this->get_idex_cancel_order_hash ($orderToHash);
        $signature = $this->signMessage ($orderHash, $this->privateKey);
        $request = array (
            'orderHash' => $orderId,
            'address' => $this->walletAddress,
            'nonce' => $nonce,
        );
        $response = $this->privatePostCancel (array_merge ($request, $signature));
        // array( success => 1 )
        if (is_array($response) && array_key_exists('success', $response)) {
            return array (
                'info' => $response,
            );
        } else {
            throw new ExchangeError($this->id . ' cancel order failed ' . $this->json ($response));
        }
    }

    public function fetch_transactions ($code = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $currency = $this->currency ($code);
        $request = array (
            'address' => $this->walletAddress,
        );
        if ($since !== null) {
            $request['start'] = intval ((int) floor($since / 1000));
        }
        $response = $this->publicPostReturnDepositsWithdrawals (array_merge ($request, $params));
        // { $deposits:
        //    array ( array ( $currency => 'ETH',
        //        amount => '0.05',
        //        timestamp => 1563953513,
        //        transactionHash:
        //         '0xd6eefd81c7efc9beeb35b924d6db3c93a78bf7eac082ba87e107ad4e94bccdcf',
        //        depositNumber => 1586430 ),
        //      { $currency => 'ETH',
        //        amount => '0.12',
        //        timestamp => 1564040359,
        //        transactionHash:
        //         '0x2ecbb3ab72b6f79fc7a9058c39dce28f913152748c1507d13ab1759e965da3ca',
        //        depositNumber => 1587341 } ),
        //   $withdrawals:
        //    array ( { $currency => 'ETH',
        //        amount => '0.149',
        //        timestamp => 1564060001,
        //        transactionHash:
        //         '0xab555fc301779dd92fd41ccd143b1d72776ae7b5acfc59ca44a1d376f68fda15',
        //        withdrawalNumber => 1444070,
        //        status => 'COMPLETE' } ) }
        $deposits = $this->parseTransactions ($response['deposits'], $currency, $since, $limit);
        $withdrawals = $this->parseTransactions ($response['withdrawals'], $currency, $since, $limit);
        return $this->array_concat($deposits, $withdrawals);
    }

    public function parse_transaction ($item, $currency = null) {
        // { $currency => 'ETH',
        //   $amount => '0.05',
        //   $timestamp => 1563953513,
        //   transactionHash:
        //    '0xd6eefd81c7efc9beeb35b924d6db3c93a78bf7eac082ba87e107ad4e94bccdcf',
        //   depositNumber => 1586430 }
        $amount = $this->safe_float($item, 'amount');
        $timestamp = $this->safe_integer($item, 'timestamp') * 1000;
        $txhash = $this->safe_string($item, 'transactionHash');
        $id = null;
        $type = null;
        $status = null;
        $addressFrom = null;
        $addressTo = null;
        if (is_array($item) && array_key_exists('depositNumber', $item)) {
            $id = $this->safe_string($item, 'depositNumber');
            $type = 'deposit';
            $addressFrom = $this->walletAddress;
            $addressTo = $this->options['contractAddress'];
        } else if (is_array($item) && array_key_exists('withdrawalNumber', $item)) {
            $id = $this->safe_string($item, 'withdrawalNumber');
            $type = 'withdrawal';
            $status = $this->parse_transaction_status ($this->safe_string($item, 'status'));
            $addressFrom = $this->options['contractAddress'];
            $addressTo = $this->walletAddress;
        }
        $code = $this->safe_currency_code($this->safe_string($item, 'currency'));
        return array (
            'info' => $item,
            'id' => $id,
            'txid' => $txhash,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'currency' => $code,
            'amount' => $amount,
            'status' => $status,
            'type' => $type,
            'updated' => null,
            'comment' => null,
            'addressFrom' => $addressFrom,
            'tagFrom' => null,
            'addressTo' => $addressTo,
            'tagTo' => null,
            'fee' => array (
                'currency' => $code,
                'cost' => null,
                'rate' => null,
            ),
        );
    }

    public function parse_transaction_status ($status) {
        $statuses = array (
            'COMPLETE' => 'ok',
        );
        return $this->safe_string($statuses, $status);
    }

    public function fetch_open_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        if ($this->walletAddress === null) {
            throw new ArgumentsRequired($this->id . ' fetchOpenOrders requires a walletAddress');
        }
        $this->load_markets();
        $request = array (
            'address' => $this->walletAddress,
        );
        $market = null;
        if ($symbol !== null) {
            $market = $this->market ($symbol);
            $request['market'] = $market['id'];
        }
        $response = $this->publicPostReturnOpenOrders (array_merge ($request, $params));
        // array ( { timestamp => 1564041428,
        //     orderHash:
        //      '0x31c42154a8421425a18d076df400d9ec1ef64d5251285384a71ba3c0ab31beb4',
        //     orderNumber => 1562323021,
        //     $market => 'ETH_LIT',
        //     type => 'buy',
        //     $params:
        //      array ( tokenBuy => '0x763fa6806e1acf68130d2d0f0df754c93cc546b2',
        //        buySymbol => 'LIT',
        //        buyPrecision => 18,
        //        amountBuy => '210000000000000000000',
        //        tokenSell => '0x0000000000000000000000000000000000000000',
        //        sellSymbol => 'ETH',
        //        sellPrecision => 18,
        //        amountSell => '153300000000000000',
        //        expires => 100000,
        //        nonce => 1,
        //        user => '0x0ab991497116f7f5532a4c2f4f7b1784488628e1' ),
        //     price => '0.00073',
        //     amount => '210',
        //     status => 'open',
        //     total => '0.1533' } )
        return $this->parse_orders($response, $market, $since, $limit);
    }

    public function fetch_order ($id, $symbol = null, $params = array ()) {
        $this->load_markets();
        $market = null;
        if ($symbol !== null) {
            $market = $this->market ($symbol);
        }
        $request = array (
            'orderHash' => $id,
        );
        $response = $this->publicPostReturnOrderStatus (array_merge ($request, $params));
        // { filled => '0',
        //   initialAmount => '210',
        //   timestamp => 1564041428,
        //   orderHash:
        //    '0x31c42154a8421425a18d076df400d9ec1ef64d5251285384a71ba3c0ab31beb4',
        //   orderNumber => 1562323021,
        //   $market => 'ETH_LIT',
        //   type => 'buy',
        //   $params:
        //    array ( tokenBuy => '0x763fa6806e1acf68130d2d0f0df754c93cc546b2',
        //      buySymbol => 'LIT',
        //      buyPrecision => 18,
        //      amountBuy => '210000000000000000000',
        //      tokenSell => '0x0000000000000000000000000000000000000000',
        //      sellSymbol => 'ETH',
        //      sellPrecision => 18,
        //      amountSell => '153300000000000000',
        //      expires => 100000,
        //      nonce => 1,
        //      user => '0x0ab991497116f7f5532a4c2f4f7b1784488628e1' ),
        //   price => '0.00073',
        //   amount => '210',
        //   status => 'open',
        //   total => '0.1533' }
        return $this->parse_order($response, $market);
    }

    public function parse_order ($order, $market = null) {
        // { $filled => '0',
        //   initialAmount => '210',
        //   $timestamp => 1564041428,
        //   orderHash:
        //    '0x31c42154a8421425a18d076df400d9ec1ef64d5251285384a71ba3c0ab31beb4',
        //   orderNumber => 1562323021,
        //   $market => 'ETH_LIT',
        //   type => 'buy',
        //   $params:
        //    array ( tokenBuy => '0x763fa6806e1acf68130d2d0f0df754c93cc546b2',
        //      buySymbol => 'LIT',
        //      buyPrecision => 18,
        //      amountBuy => '210000000000000000000',
        //      tokenSell => '0x0000000000000000000000000000000000000000',
        //      sellSymbol => 'ETH',
        //      sellPrecision => 18,
        //      amountSell => '153300000000000000',
        //      expires => 100000,
        //      nonce => 1,
        //      user => '0x0ab991497116f7f5532a4c2f4f7b1784488628e1' ),
        //   $price => '0.00073',
        //   $amount => '210',
        //   $status => 'open',
        //   total => '0.1533' }
        $timestamp = $this->safe_integer($order, 'timestamp') * 1000;
        $side = $this->safe_string($order, 'type');
        $symbol = null;
        $amount = null;
        $remaining = null;
        if (is_array($order) && array_key_exists('initialAmount', $order)) {
            $amount = $this->safe_float($order, 'initialAmount');
            $remaining = $this->safe_float($order, 'amount');
        } else {
            $amount = $this->safe_float($order, 'amount');
        }
        $filled = $this->safe_float($order, 'filled');
        $cost = $this->safe_float($order, 'total');
        $price = $this->safe_float($order, 'price');
        if (is_array($order) && array_key_exists('market', $order)) {
            $marketId = $order['market'];
            $symbol = $this->markets_by_id[$marketId]['symbol'];
        } else if (($side !== null) && (is_array($order) && array_key_exists('params', $order))) {
            $params = $order['params'];
            $buy = $this->safe_currency_code($this->safe_string($params, 'tokenBuy'));
            $sell = $this->safe_currency_code($this->safe_string($params, 'tokenSell'));
            if ($buy !== null && $sell !== null) {
                $symbol = $side === 'buy' ? $buy . '/' . $sell : $sell . '/' . $buy;
            }
        }
        if ($symbol === null && $market !== null) {
            $symbol = $market['symbol'];
        }
        $id = $this->safe_string($order, 'orderHash');
        $status = $this->parse_order_status($this->safe_string($order, 'status'));
        return array (
            'info' => $order,
            'id' => $id,
            'symbol' => $symbol,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'side' => $side,
            'amount' => $amount,
            'price' => $price,
            'type' => 'limit',
            'filled' => $filled,
            'remaining' => $remaining,
            'cost' => $cost,
            'status' => $status,
        );
    }

    public function parse_order_status ($status) {
        $statuses = array (
            'open' => 'open',
        );
        return $this->safe_string($statuses, $status, $status);
    }

    public function fetch_my_trades ($symbol = null, $since = null, $limit = null, $params = array ()) {
        if ($this->walletAddress === null) {
            throw new ArgumentsRequired($this->id . ' fetchOpenOrders requires a walletAddress');
        }
        $this->load_markets();
        $request = array (
            'address' => $this->walletAddress,
        );
        $market = null;
        if ($symbol !== null) {
            $market = $this->market ($symbol);
            $request['market'] = $market['id'];
        }
        if ($limit !== null) {
            $request['start'] = intval ((int) floor($limit));
        }
        $response = $this->publicPostReturnTradeHistory (array_merge ($request, $params));
        // { ETH_IDEX:
        //    array ( { type => 'buy',
        //        date => '2019-07-25 11:24:41',
        //        amount => '347.833140025692348611',
        //        total => '0.050998794333719943',
        //        uuid => 'cbdff960-aece-11e9-b566-c5d69c3be671',
        //        tid => 4320867,
        //        timestamp => 1564053881,
        //        price => '0.000146618560640751',
        //        taker => '0x0ab991497116f7f5532a4c2f4f7b1784488628e1',
        //        maker => '0x1a961bc2e0d619d101f5f92a6be752132d7606e6',
        //        orderHash:
        //         '0xbec6485613a15be619c04c1425e8e821ebae42b88fa95ac4dfe8ba2beb363ee4',
        //        transactionHash:
        //         '0xf094e07b329ac8046e8f34db358415863c41daa36765c05516f4cf4f5b403ad1',
        //        tokenBuy => '0x0000000000000000000000000000000000000000',
        //        buyerFee => '0.695666280051384697',
        //        gasFee => '28.986780264563232993',
        //        sellerFee => '0.00005099879433372',
        //        tokenSell => '0xb705268213d593b8fd88d3fdeff93aff5cbdcfae',
        //        usdValue => '11.336926687304238214' } ) }
        //
        // if a $symbol is specified in the $request:
        //
        //    array ( { type => 'buy',
        //        date => '2019-07-25 11:24:41',
        //        amount => '347.833140025692348611',
        //        total => '0.050998794333719943',
        //        uuid => 'cbdff960-aece-11e9-b566-c5d69c3be671',
        //        tid => 4320867,
        //        timestamp => 1564053881,
        //        price => '0.000146618560640751',
        //        taker => '0x0ab991497116f7f5532a4c2f4f7b1784488628e1',
        //        maker => '0x1a961bc2e0d619d101f5f92a6be752132d7606e6',
        //        orderHash:
        //         '0xbec6485613a15be619c04c1425e8e821ebae42b88fa95ac4dfe8ba2beb363ee4',
        //        transactionHash:
        //         '0xf094e07b329ac8046e8f34db358415863c41daa36765c05516f4cf4f5b403ad1',
        //        tokenBuy => '0x0000000000000000000000000000000000000000',
        //        buyerFee => '0.695666280051384697',
        //        gasFee => '28.986780264563232993',
        //        sellerFee => '0.00005099879433372',
        //        tokenSell => '0xb705268213d593b8fd88d3fdeff93aff5cbdcfae',
        //        usdValue => '11.336926687304238214' } )
        if (gettype ($response) === 'array' && count (array_filter (array_keys ($response), 'is_string')) == 0) {
            return $this->parse_trades($response, $market, $since, $limit);
        } else {
            $result = array();
            $marketIds = is_array($response) ? array_keys($response) : array();
            for ($i = 0; $i < count ($marketIds); $i++) {
                $marketId = $marketIds[$i];
                $trades = $response[$marketId];
                $parsed = $this->parse_trades($trades, $market, $since, $limit);
                $result = $this->array_concat($result, $parsed);
            }
            return $result;
        }
    }

    public function parse_trade ($trade, $market = null) {
        // { type => 'buy',
        //   date => '2019-07-25 11:24:41',
        //   $amount => '347.833140025692348611',
        //   total => '0.050998794333719943',
        //   uuid => 'cbdff960-aece-11e9-b566-c5d69c3be671',
        //   tid => 4320867,
        //   $timestamp => 1564053881,
        //   $price => '0.000146618560640751',
        //   taker => '0x0ab991497116f7f5532a4c2f4f7b1784488628e1',
        //   $maker => '0x1a961bc2e0d619d101f5f92a6be752132d7606e6',
        //   orderHash:
        //    '0xbec6485613a15be619c04c1425e8e821ebae42b88fa95ac4dfe8ba2beb363ee4',
        //   transactionHash:
        //    '0xf094e07b329ac8046e8f34db358415863c41daa36765c05516f4cf4f5b403ad1',
        //   tokenBuy => '0x0000000000000000000000000000000000000000',
        //   buyerFee => '0.695666280051384697',
        //   $gasFee => '28.986780264563232993',
        //   sellerFee => '0.00005099879433372',
        //   tokenSell => '0xb705268213d593b8fd88d3fdeff93aff5cbdcfae',
        //   usdValue => '11.336926687304238214' }
        $side = $this->safe_string($trade, 'type');
        $feeCurrency = null;
        $symbol = null;
        $maker = $this->safe_string($trade, 'maker');
        $takerOrMaker = null;
        if ($maker !== null) {
            if (strtolower($maker) === strtolower($this->walletAddress)) {
                $takerOrMaker = 'maker';
            } else {
                $takerOrMaker = 'taker';
            }
        }
        $buy = $this->safe_currency_code($this->safe_string($trade, 'tokenBuy'));
        $sell = $this->safe_currency_code($this->safe_string($trade, 'tokenSell'));
        // get ready to be mind-boggled
        $feeSide = null;
        if ($buy !== null && $sell !== null) {
            if ($side === 'buy') {
                $feeSide = 'buyerFee';
                if ($takerOrMaker === 'maker') {
                    $symbol = $buy . '/' . $sell;
                    $feeCurrency = $buy;
                } else {
                    $symbol = $sell . '/' . $buy;
                    $feeCurrency = $sell;
                }
            } else {
                $feeSide = 'sellerFee';
                if ($takerOrMaker === 'maker') {
                    $symbol = $sell . '/' . $buy;
                    $feeCurrency = $buy;
                } else {
                    $symbol = $buy . '/' . $sell;
                    $feeCurrency = $sell;
                }
            }
        }
        if ($symbol === null && $market !== null) {
            $symbol = $market['symbol'];
        }
        $timestamp = $this->safe_integer($trade, 'timestamp') * 1000;
        $id = $this->safe_string($trade, 'tid');
        $amount = $this->safe_float($trade, 'amount');
        $price = $this->safe_float($trade, 'price');
        $cost = $this->safe_float($trade, 'total');
        $feeCost = $this->safe_float($trade, $feeSide);
        if ($feeCost < 0) {
            $gasFee = $this->safe_float($trade, 'gasFee');
            $feeCost = $this->sum ($gasFee, $feeCost);
        }
        $fee = array (
            'currency' => $feeCurrency,
            'cost' => $feeCost,
        );
        if ($feeCost !== null && $amount !== null) {
            $feeCurrencyAmount = $feeCurrency === 'ETH' ? $cost : $amount;
            $fee['rate'] = $feeCost / $feeCurrencyAmount;
        }
        $orderId = $this->safe_string($trade, 'orderHash');
        return array (
            'info' => $trade,
            'id' => $id,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'fee' => $fee,
            'price' => $price,
            'amount' => $amount,
            'cost' => $cost,
            'takerOrMaker' => $takerOrMaker,
            'side' => $side,
            'order' => $orderId,
            'symbol' => $symbol,
            'type' => 'limit',
        );
    }

    public function withdraw ($code, $amount, $address, $tag = null, $params = array ()) {
        $this->check_required_dependencies();
        $this->check_address($address);
        $this->load_markets();
        $currency = $this->currency ($code);
        $tokenAddress = $currency['id'];
        $nonce = $this->get_nonce ();
        $amount = $this->toWei ($amount, 'ether', $currency['precision']);
        $requestToHash = array (
            'contractAddress' => $this->get_contract_address (),
            'token' => $tokenAddress,
            'amount' => $amount,
            'address' => $address,
            'nonce' => $nonce,
        );
        $hash = $this->get_idex_withdraw_hash ($requestToHash);
        $signature = $this->signMessage ($hash, $this->privateKey);
        $request = array (
            'address' => $address,
            'amount' => $amount,
            'token' => $tokenAddress,
            'nonce' => $nonce,
        );
        $response = $this->privatePostWithdraw (array_merge ($request, $signature));
        // array( $amount => '0' )
        return array (
            'info' => $response,
            'id' => null,
        );
    }

    public function sign ($path, $api = 'public', $method = 'GET', $params = array (), $headers = null, $body = null) {
        $body = $this->json ($params);  // all methods are POST
        $url = $this->urls['api'] . '/' . $path;
        $headers = array (
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        );
        if ($api === 'private') {
            $this->check_required_credentials();
            $headers['API-Key'] = $this->apiKey;
        }
        return array( 'url' => $url, 'method' => $method, 'body' => $body, 'headers' => $headers );
    }

    public function get_idex_create_order_hash ($order) {
        return $this->soliditySha3 ([
            $order['contractAddress'], // address
            $order['tokenBuy'], // address
            $order['amountBuy'], // uint256
            $order['tokenSell'], // address
            $order['amountSell'], // uint256
            $order['expires'], // uint256
            $order['nonce'], // uint256
            $order['address'], // address
        ]);
    }

    public function get_idex_cancel_order_hash ($order) {
        return $this->soliditySha3 ([
            $order['orderHash'], // address
            $order['nonce'], // uint256
        ]);
    }

    public function get_idex_market_order_hash ($order) {
        return $this->soliditySha3 ([
            $order['orderHash'], // address
            $order['amount'], // uint256
            $order['address'], // address
            $order['nonce'], // uint256
        ]);
    }

    public function get_idex_withdraw_hash ($request) {
        return $this->soliditySha3 ([
            $request['contractAddress'], // address
            $request['token'], // uint256
            $request['amount'], // uint256
            $request['address'],  // address
            $request['nonce'], // uint256
        ]);
    }

    public function handle_errors ($code, $reason, $url, $method, $headers, $body, $response, $requestHeaders, $requestBody) {
        if ($response === null) {
            return;
        }
        if (is_array($response) && array_key_exists('error', $response)) {
            if (is_array($this->exceptions) && array_key_exists($response['error'], $this->exceptions)) {
                throw new $this->exceptions[$response['error']]($this->id . ' ' . $response['error']);
            }
            throw new ExchangeError($this->id . ' ' . $body);
        }
    }
}
