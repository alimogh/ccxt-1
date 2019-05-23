<?php

namespace ccxt;

use Exception as Exception; // a common import

class anxpro extends Exchange {

    public function describe () {
        return array_replace_recursive (parent::describe (), array (
            'id' => 'anxpro',
            'name' => 'ANXPro',
            'countries' => array ( 'JP', 'SG', 'HK', 'NZ' ),
            'rateLimit' => 1500,
            'has' => array (
                'CORS' => false,
                'fetchCurrencies' => true,
                'fetchOHLCV' => false,
                'fetchTrades' => false,
                'fetchOpenOrders' => true,
                'fetchDepositAddress' => true,
                'createDepositAddress' => false,
                'withdraw' => true,
            ),
            'urls' => array (
                'logo' => 'https://user-images.githubusercontent.com/1294454/27765983-fd8595da-5ec9-11e7-82e3-adb3ab8c2612.jpg',
                'api' => array (
                    'public' => 'https://anxpro.com/api/2',
                    'private' => 'https://anxpro.com/api/2',
                    'v3public' => 'https://anxpro.com/api/3',
                ),
                'www' => 'https://anxpro.com',
                'doc' => array (
                    'https://anxv2.docs.apiary.io',
                    'https://anxv3.docs.apiary.io',
                    'https://anxpro.com/pages/api',
                ),
            ),
            'api' => array (
                'v3public' => array (
                    'get' => array (
                        'currencyStatic',
                    ),
                ),
                'public' => array (
                    'get' => array (
                        '{currency_pair}/money/ticker',
                        '{currency_pair}/money/depth/full',
                        '{currency_pair}/money/trade/fetch', // disabled by ANXPro
                    ),
                ),
                'private' => array (
                    'post' => array (
                        '{currency_pair}/money/order/add',
                        '{currency_pair}/money/order/cancel',
                        '{currency_pair}/money/order/quote',
                        '{currency_pair}/money/order/result',
                        '{currency_pair}/money/orders',
                        'money/{currency}/address',
                        'money/{currency}/send_simple',
                        'money/info',
                        'money/trade/list',
                        'money/wallet/history',
                    ),
                ),
            ),
            'httpExceptions' => array (
                '403' => '\\ccxt\\AuthenticationError',
            ),
            'exceptions' => array (
                'exact' => array (
                    // v2
                    'Insufficient Funds' => '\\ccxt\\InsufficientFunds',
                    'Trade value too small' => '\\ccxt\\InvalidOrder',
                    'The currency pair is not supported' => '\\ccxt\\BadRequest',
                    'Order amount is too low' => '\\ccxt\\InvalidOrder',
                    'Order amount is too high' => '\\ccxt\\InvalidOrder',
                    'order rate is too low' => '\\ccxt\\InvalidOrder',
                    'order rate is too high' => '\\ccxt\\InvalidOrder',
                    'Too many open orders' => '\\ccxt\\InvalidOrder',
                    'Unexpected error' => '\\ccxt\\ExchangeError',
                    'Order Engine is offline' => '\\ccxt\\ExchangeNotAvailable',
                    'No executed order with that identifer found' => '\\ccxt\\OrderNotFound',
                    'Unknown server error, please contact support.' => '\\ccxt\\ExchangeError',
                ),
            ),
            'fees' => array (
                'trading' => array (
                    'tierBased' => false,
                    'percentage' => true,
                    'maker' => 0.1 / 100,
                    'taker' => 0.2 / 100,
                ),
            ),
        ));
    }

    public function fetch_currencies ($params = array ()) {
        $response = $this->v3publicGetCurrencyStatic ($params);
        $result = array ();
        $currencies = $response['currencyStatic']['currencies'];
        //       "$currencies" => array (
        //         "HKD" => array (
        //           "decimals" => 2,
        //           "minOrderSize" => 1.00000000,
        //           "maxOrderSize" => 10000000000.00000000,
        //           "displayDenominator" => 1,
        //           "summaryDecimals" => 0,
        //           "displayUnit" => "HKD",
        //           "symbol" => "$",
        //           "$type" => "FIAT",
        //           "$engineSettings" => array (
        //             "$depositsEnabled" => false,
        //             "$withdrawalsEnabled" => true,
        //             "$displayEnabled" => true,
        //             "mobileAccessEnabled" => true
        //           ),
        //           "minOrderValue" => 1.00000000,
        //           "maxOrderValue" => 10000000000.00000000,
        //           "maxMarketOrderValue" => 36000.00000000,
        //           "maxMarketOrderSize" => 36000.00000000,
        //           "assetDivisibility" => 0
        //         ),
        //         "ETH" => array (
        //           "decimals" => 8,
        //           "minOrderSize" => 0.00010000,
        //           "maxOrderSize" => 1000000000.00000000,
        //           "$type" => "CRYPTO",
        //           "confirmationThresholds" => array (
        //             array ( "confosRequired" => 30, "threshold" => 0.50000000 ),
        //             array ( "confosRequired" => 45, "threshold" => 10.00000000 ),
        //             array ( "confosRequired" => 70 )
        //           ),
        //           "networkFee" => 0.00500000,
        //           "$engineSettings" => array (
        //             "$depositsEnabled" => true,
        //             "$withdrawalsEnabled" => true,
        //             "$displayEnabled" => true,
        //             "mobileAccessEnabled" => true
        //           ),
        //           "minOrderValue" => 0.00010000,
        //           "maxOrderValue" => 10000000000.00000000,
        //           "maxMarketOrderValue" => 10000000000.00000000,
        //           "maxMarketOrderSize" => 1000000000.00000000,
        //           "digitalCurrencyType" => "ETHEREUM",
        //           "assetDivisibility" => 0,
        //           "assetIcon" => "/images/currencies/crypto/ETH.svg"
        //         ),
        //       ),
        $ids = is_array ($currencies) ? array_keys ($currencies) : array ();
        for ($i = 0; $i < count ($ids); $i++) {
            $id = $ids[$i];
            $currency = $currencies[$id];
            $code = $this->common_currency_code($id);
            $engineSettings = $this->safe_value($currency, 'engineSettings');
            $depositsEnabled = $this->safe_value($engineSettings, 'depositsEnabled');
            $withdrawalsEnabled = $this->safe_value($engineSettings, 'withdrawalsEnabled');
            $displayEnabled = $this->safe_value($engineSettings, 'displayEnabled');
            $active = $depositsEnabled && $withdrawalsEnabled && $displayEnabled;
            $precision = $this->safe_integer($currency, 'decimals');
            $fee = $this->safe_float($currency, 'networkFee');
            $type = $this->safe_string($currency, 'type');
            if ($type !== 'null') {
                $type = strtolower ($type);
            }
            $result[$code] = array (
                'id' => $id,
                'code' => $code,
                'info' => $currency,
                'name' => $code,
                'type' => $type,
                'active' => $active,
                'precision' => $precision,
                'fee' => $fee,
                'limits' => array (
                    'amount' => array (
                        'min' => $this->safe_float($currency, 'minOrderSize'),
                        'max' => $this->safe_float($currency, 'maxOrderSize'),
                    ),
                    'price' => array (
                        'min' => null,
                        'max' => null,
                    ),
                    'cost' => array (
                        'min' => $this->safe_float($currency, 'minOrderValue'),
                        'max' => $this->safe_float($currency, 'maxOrderValue'),
                    ),
                    'withdraw' => array (
                        'min' => null,
                        'max' => null,
                    ),
                ),
            );
        }
        return $result;
    }

    public function fetch_markets ($params = array ()) {
        $response = $this->v3publicGetCurrencyStatic ($params);
        //
        //   {
        //     "$currencyStatic" => array (
        //       "$currencies" => array (
        //         "HKD" => array (
        //           "decimals" => 2,
        //           "minOrderSize" => 1.00000000,
        //           "maxOrderSize" => 10000000000.00000000,
        //           "displayDenominator" => 1,
        //           "summaryDecimals" => 0,
        //           "displayUnit" => "HKD",
        //           "$symbol" => "$",
        //           "type" => "FIAT",
        //           "$engineSettings" => array (
        //             "depositsEnabled" => false,
        //             "withdrawalsEnabled" => true,
        //             "$displayEnabled" => true,
        //             "mobileAccessEnabled" => true
        //           ),
        //           "minOrderValue" => 1.00000000,
        //           "maxOrderValue" => 10000000000.00000000,
        //           "maxMarketOrderValue" => 36000.00000000,
        //           "maxMarketOrderSize" => 36000.00000000,
        //           "assetDivisibility" => 0
        //         ),
        //         "ETH" => array (
        //           "decimals" => 8,
        //           "minOrderSize" => 0.00010000,
        //           "maxOrderSize" => 1000000000.00000000,
        //           "type" => "CRYPTO",
        //           "confirmationThresholds" => array (
        //             array ( "confosRequired" => 30, "threshold" => 0.50000000 ),
        //             array ( "confosRequired" => 45, "threshold" => 10.00000000 ),
        //             array ( "confosRequired" => 70 )
        //           ),
        //           "networkFee" => 0.00500000,
        //           "$engineSettings" => array (
        //             "depositsEnabled" => true,
        //             "withdrawalsEnabled" => true,
        //             "$displayEnabled" => true,
        //             "mobileAccessEnabled" => true
        //           ),
        //           "minOrderValue" => 0.00010000,
        //           "maxOrderValue" => 10000000000.00000000,
        //           "maxMarketOrderValue" => 10000000000.00000000,
        //           "maxMarketOrderSize" => 1000000000.00000000,
        //           "digitalCurrencyType" => "ETHEREUM",
        //           "assetDivisibility" => 0,
        //           "assetIcon" => "/images/currencies/crypto/ETH.svg"
        //         ),
        //       ),
        //       "$currencyPairs" => array (
        //         "ETHUSD" => array (
        //           "priceDecimals" => 5,
        //           "$engineSettings" => array (
        //             "$tradingEnabled" => true,
        //             "$displayEnabled" => true,
        //             "cancelOnly" => true,
        //             "verifyRequired" => false,
        //             "restrictedBuy" => false,
        //             "restrictedSell" => false
        //           ),
        //           "minOrderRate" => 10.00000000,
        //           "maxOrderRate" => 10000.00000000,
        //           "displayPriceDecimals" => 5,
        //           "tradedCcy" => "ETH",
        //           "settlementCcy" => "USD",
        //           "preferredMarket" => "ANX",
        //           "chartEnabled" => true,
        //           "simpleTradeEnabled" => false
        //         ),
        //       ),
        //     ),
        //     "timestamp" => "1549840691039",
        //     "resultCode" => "OK"
        //   }
        //
        $currencyStatic = $this->safe_value($response, 'currencyStatic', array ());
        $currencies = $this->safe_value($currencyStatic, 'currencies', array ());
        $currencyPairs = $this->safe_value($currencyStatic, 'currencyPairs', array ());
        $result = array ();
        $ids = is_array ($currencyPairs) ? array_keys ($currencyPairs) : array ();
        for ($i = 0; $i < count ($ids); $i++) {
            $id = $ids[$i];
            $market = $currencyPairs[$id];
            //
            //     "ETHUSD" => array (
            //       "priceDecimals" => 5,
            //       "$engineSettings" => array (
            //         "$tradingEnabled" => true,
            //         "$displayEnabled" => true,
            //         "cancelOnly" => true,
            //         "verifyRequired" => false,
            //         "restrictedBuy" => false,
            //         "restrictedSell" => false
            //       ),
            //       "minOrderRate" => 10.00000000,
            //       "maxOrderRate" => 10000.00000000,
            //       "displayPriceDecimals" => 5,
            //       "tradedCcy" => "ETH",
            //       "settlementCcy" => "USD",
            //       "preferredMarket" => "ANX",
            //       "chartEnabled" => true,
            //       "simpleTradeEnabled" => false
            //     ),
            //
            $baseId = $this->safe_string($market, 'tradedCcy');
            $quoteId = $this->safe_string($market, 'settlementCcy');
            $base = $this->common_currency_code($baseId);
            $quote = $this->common_currency_code($quoteId);
            $symbol = $base . '/' . $quote;
            $baseCurrency = $this->safe_value($currencies, $baseId, array ());
            $quoteCurrency = $this->safe_value($currencies, $quoteId, array ());
            $precision = array (
                'price' => $this->safe_integer($market, 'priceDecimals'),
                'amount' => $this->safe_integer($baseCurrency, 'decimals'),
            );
            $engineSettings = $this->safe_value($market, 'engineSettings');
            $displayEnabled = $this->safe_value($engineSettings, 'displayEnabled');
            $tradingEnabled = $this->safe_value($engineSettings, 'tradingEnabled');
            $active = $displayEnabled && $tradingEnabled;
            $result[] = array (
                'id' => $id,
                'symbol' => $symbol,
                'base' => $base,
                'quote' => $quote,
                'baseId' => $baseId,
                'quoteId' => $quoteId,
                'precision' => $precision,
                'active' => $active,
                'limits' => array (
                    'price' => array (
                        'min' => $this->safe_float($market, 'minOrderRate'),
                        'max' => $this->safe_float($market, 'maxOrderRate'),
                    ),
                    'amount' => array (
                        'min' => $this->safe_float($baseCurrency, 'minOrderSize'),
                        'max' => $this->safe_float($baseCurrency, 'maxOrderSize'),
                    ),
                    'cost' => array (
                        'min' => $this->safe_float($quoteCurrency, 'minOrderValue'),
                        'max' => $this->safe_float($quoteCurrency, 'maxOrderValue'),
                    ),
                ),
                'info' => $market,
            );
        }
        return $result;
    }

    public function fetch_balance ($params = array ()) {
        $response = $this->privatePostMoneyInfo ();
        $balance = $response['data'];
        $currencies = is_array ($balance['Wallets']) ? array_keys ($balance['Wallets']) : array ();
        $result = array ( 'info' => $balance );
        for ($c = 0; $c < count ($currencies); $c++) {
            $currency = $currencies[$c];
            $account = $this->account ();
            if (is_array ($balance['Wallets']) && array_key_exists ($currency, $balance['Wallets'])) {
                $wallet = $balance['Wallets'][$currency];
                $account['free'] = floatval ($wallet['Available_Balance']['value']);
                $account['total'] = floatval ($wallet['Balance']['value']);
                $account['used'] = $account['total'] - $account['free'];
            }
            $result[$currency] = $account;
        }
        return $this->parse_balance($result);
    }

    public function fetch_order_book ($symbol, $limit = null, $params = array ()) {
        $response = $this->publicGetCurrencyPairMoneyDepthFull (array_merge (array (
            'currency_pair' => $this->market_id($symbol),
        ), $params));
        $orderbook = $response['data'];
        $t = intval ($orderbook['dataUpdateTime']);
        $timestamp = intval ($t / 1000);
        return $this->parse_order_book($orderbook, $timestamp, 'bids', 'asks', 'price', 'amount');
    }

    public function fetch_ticker ($symbol, $params = array ()) {
        $response = $this->publicGetCurrencyPairMoneyTicker (array_merge (array (
            'currency_pair' => $this->market_id($symbol),
        ), $params));
        $ticker = $response['data'];
        $t = intval ($ticker['dataUpdateTime']);
        $timestamp = intval ($t / 1000);
        $bid = $this->safe_float($ticker['buy'], 'value');
        $ask = $this->safe_float($ticker['sell'], 'value');
        $baseVolume = floatval ($ticker['vol']['value']);
        $last = floatval ($ticker['last']['value']);
        return array (
            'symbol' => $symbol,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'high' => floatval ($ticker['high']['value']),
            'low' => floatval ($ticker['low']['value']),
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
            'average' => floatval ($ticker['avg']['value']),
            'baseVolume' => $baseVolume,
            'quoteVolume' => null,
            'info' => $ticker,
        );
    }

    public function fetch_trades ($symbol, $since = null, $limit = null, $params = array ()) {
        throw new ExchangeError ($this->id . ' switched off the trades endpoint, see their docs at https://docs.anxv2.apiary.io');
    }

    public function fetch_open_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array (
            'currency_pair' => $market['id'],
        );
        // ANXPro will return all $symbol pairs regardless of what is specified in $request
        $response = $this->privatePostCurrencyPairMoneyOrders (array_merge ($request, $params));
        //
        //     {
        //         "result" => "success",
        //         "data" => array (
        //             array (
        //                 "oid" => "e74305c7-c424-4fbc-a8a2-b41d8329deb0",
        //                 "currency" => "HKD",
        //                 "item" => "BTC",
        //                 "type" => "offer",
        //                 "amount" => array (
        //                     "currency" => "BTC",
        //                     "display" => "10.00000000 BTC",
        //                     "display_short" => "10.00 BTC",
        //                     "value" => "10.00000000",
        //                     "value_int" => "1000000000"
        //                 ),
        //                 "effective_amount" => array (
        //                     "currency" => "BTC",
        //                     "display" => "10.00000000 BTC",
        //                     "display_short" => "10.00 BTC",
        //                     "value" => "10.00000000",
        //                     "value_int" => "1000000000"
        //                 ),
        //                 "price" => array (
        //                     "currency" => "HKD",
        //                     "display" => "412.34567 HKD",
        //                     "display_short" => "412.35 HKD",
        //                     "value" => "412.34567",
        //                     "value_int" => "41234567"
        //                 ),
        //                 "status" => "open",
        //                 "date" => 1393411075000,
        //                 "priority" => 1393411075000000,
        //                 "actions" => array ()
        //             ),
        //            ...
        //         )
        //     }
        //
        return $this->parse_orders($this->safe_value($response, 'data', array ()), $symbol, $since, $limit);
    }

    public function parse_order ($order, $market = null) {
        //
        //     {
        //       "oid" => "e74305c7-c424-4fbc-a8a2-b41d8329deb0",
        //       "currency" => "HKD",
        //       "item" => "BTC",
        //       "type" => "offer",  <-- bid/offer
        //       "$amount" => array (
        //         "currency" => "BTC",
        //         "display" => "10.00000000 BTC",
        //         "display_short" => "10.00 BTC",
        //         "value" => "10.00000000",
        //         "value_int" => "1000000000"
        //       ),
        //       "effective_amount" => array (
        //         "currency" => "BTC",
        //         "display" => "10.00000000 BTC",
        //         "display_short" => "10.00 BTC",
        //         "value" => "10.00000000",
        //         "value_int" => "1000000000"
        //       ),
        //       "$price" => array (
        //         "currency" => "HKD",
        //         "display" => "412.34567 HKD",
        //         "display_short" => "412.35 HKD",
        //         "value" => "412.34567",
        //         "value_int" => "41234567"
        //       ),
        //       "$status" => "open",
        //       "date" => 1393411075000,
        //       "priority" => 1393411075000000,
        //       "actions" => array ()
        //     }
        //
        $id = $this->safe_string($order, 'oid');
        $status = $this->safe_string($order, 'status');
        $timestamp = $this->safe_integer($order, 'date');
        $baseId = $this->safe_string($order, 'item');
        $quoteId = $this->safe_string($order, 'currency');
        $marketId = $baseId . '/' . $quoteId;
        $market = $this->safe_value($this->markets_by_id, $marketId);
        $symbol = null;
        if ($market !== null) {
            $symbol = $market['symbol'];
        }
        $amount_info = $this->safe_value($order, 'amount', array ());
        $effective_info = $this->safe_value($order, 'effective_amount', array ());
        $price_info = $this->safe_value($order, 'price', array ());
        $remaining = $this->safe_float($effective_info, 'value');
        $amount = $this->safe_float($amount_info, 'volume');
        $price = $this->safe_float($price_info, 'value');
        $filled = null;
        $cost = null;
        if ($amount !== null) {
            if ($remaining !== null) {
                $filled = $amount - $remaining;
                $cost = $price * $filled;
            }
        }
        $orderType = 'limit';
        $side = $this->safe_string($order, 'type');
        if ($side === 'offer') {
            $side = 'sell';
        } else {
            $side = 'buy';
        }
        $fee = null;
        $trades = null; // todo parse $trades
        $lastTradeTimestamp = null;
        return array (
            'info' => $order,
            'id' => $id,
            'symbol' => $symbol,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'lastTradeTimestamp' => $lastTradeTimestamp,
            'type' => $orderType,
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
    }

    public function create_order ($symbol, $type, $side, $amount, $price = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $amountMultiplier = pow (10, $market['precision']['amount']);
        $request = array (
            'currency_pair' => $market['id'],
            'amount_int' => intval ($amount * $amountMultiplier), // 10^8
        );
        if ($type === 'limit') {
            $priceMultiplier = pow (10, $market['precision']['price']);
            $request['price_int'] = intval ($price * $priceMultiplier); // 10^5 or 10^8
        }
        $request['type'] = ($side === 'buy') ? 'bid' : 'ask';
        $response = $this->privatePostCurrencyPairMoneyOrderAdd (array_merge ($request, $params));
        return array (
            'info' => $response,
            'id' => $response['data'],
        );
    }

    public function cancel_order ($id, $symbol = null, $params = array ()) {
        return $this->privatePostCurrencyPairMoneyOrderCancel (array ( 'oid' => $id ));
    }

    public function get_amount_multiplier ($code) {
        $multipliers = array (
            'BTC' => 100000000,
            'LTC' => 100000000,
            'STR' => 100000000,
            'XRP' => 100000000,
            'DOGE' => 100000000,
        );
        $defaultValue = 100;
        return $this->safe_integer($multipliers, $code, $defaultValue);
    }

    public function withdraw ($code, $amount, $address, $tag = null, $params = array ()) {
        $this->check_address($address);
        $this->load_markets();
        $currency = $this->currency ($code);
        $multiplier = $this->get_amount_multiplier ($code);
        $request = array (
            'currency' => $currency,
            'amount_int' => intval ($amount * $multiplier),
            'address' => $address,
        );
        if ($tag !== null) {
            $request['destinationTag'] = $tag;
        }
        $response = $this->privatePostMoneyCurrencySendSimple (array_merge ($request, $params));
        return array (
            'info' => $response,
            'id' => $response['data']['transactionId'],
        );
    }

    public function fetch_deposit_address ($code, $params = array ()) {
        $this->load_markets();
        $currency = $this->currency ($code);
        $request = array (
            'currency' => $currency['id'],
        );
        $response = $this->privatePostMoneyCurrencyAddress (array_merge ($request, $params));
        $result = $response['data'];
        $address = $this->safe_string($result, 'addr');
        $this->check_address($address);
        return array (
            'currency' => $code,
            'address' => $address,
            'info' => $response,
        );
    }

    public function nonce () {
        return $this->milliseconds ();
    }

    public function sign ($path, $api = 'public', $method = 'GET', $params = array (), $headers = null, $body = null) {
        $request = $this->implode_params($path, $params);
        $query = $this->omit ($params, $this->extract_params($path));
        $url = $this->urls['api'][$api] . '/' . $request;
        if ($api === 'public' || $api === 'v3public') {
            if ($query)
                $url .= '?' . $this->urlencode ($query);
        } else {
            $this->check_required_credentials();
            $nonce = $this->nonce ();
            $body = $this->urlencode (array_merge (array ( 'nonce' => $nonce ), $query));
            $secret = base64_decode ($this->secret);
            // eslint-disable-next-line quotes
            $auth = $request . "\0" . $body;
            $signature = $this->hmac ($this->encode ($auth), $secret, 'sha512', 'base64');
            $headers = array (
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Rest-Key' => $this->apiKey,
                'Rest-Sign' => $this->decode ($signature),
            );
        }
        return array ( 'url' => $url, 'method' => $method, 'body' => $body, 'headers' => $headers );
    }

    public function handle_errors ($httpCode, $reason, $url, $method, $headers, $body, $response) {
        if ($response === null || $response === '') {
            return;
        }
        $result = $this->safe_string($response, 'result');
        $code = $this->safe_string($response, 'resultCode');
        if ((($result !== null) && ($result !== 'success')) || (($code !== null) && ($code !== 'OK'))) {
            $message = $this->safe_string($response, 'error');
            $feedback = $this->id . ' ' . $body;
            $exact = $this->exceptions['exact'];
            if (is_array ($exact) && array_key_exists ($code, $exact)) {
                throw new $exact[$code] ($feedback);
            } else if (is_array ($exact) && array_key_exists ($message, $exact)) {
                throw new $exact[$message] ($feedback);
            }
            $broad = $this->safe_value($this->exceptions, 'broad', array ());
            $broadKey = $this->findBroadlyMatchedKey ($broad, $message);
            if ($broadKey !== null) {
                throw new $broad[$broadKey] ($feedback);
            }
            throw new ExchangeError ($feedback); // unknown $message
        }
    }
}
