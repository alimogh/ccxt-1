<?php

namespace ccxt;

use Exception as Exception; // a common import

class coinbase extends Exchange {

    public function describe () {
        return array_replace_recursive (parent::describe (), array (
            'id' => 'coinbase',
            'name' => 'coinbase',
            'countries' => 'US',
            'rateLimit' => 400, // 10k calls per hour
            'version' => 'v2',
            'userAgent' => $this->userAgents['chrome'],
            'headers' => array (
                'CB-VERSION' => '2018-05-30',
            ),
            'has' => array (
                'CORS' => true,
                'cancelOrder' => false,
                'createDepositAddress' => false,
                'createOrder' => false,
                'deposit' => false,
                'fetchBalance' => true,
                'fetchClosedOrders' => false,
                'fetchCurrencies' => true,
                'fetchDepositAddress' => false,
                'fetchMarkets' => false,
                'fetchMyTrades' => false,
                'fetchOHLCV' => false,
                'fetchOpenOrders' => false,
                'fetchOrder' => false,
                'fetchOrderBook' => false,
                'fetchOrders' => false,
                'fetchTicker' => true,
                'fetchTickers' => false,
                'fetchBidsAsks' => false,
                'fetchTrades' => false,
                'withdraw' => false,
                'fetchTransactions' => false,
                'fetchDeposits' => false,
                'fetchWithdrawals' => false,
            ),
            'urls' => array (
                'logo' => 'https://user-images.githubusercontent.com/1294454/40811661-b6eceae2-653a-11e8-829e-10bfadb078cf.jpg',
                'api' => 'https://api.coinbase.com',
                'www' => 'https://www.coinbase.com',
                'doc' => 'https://developers.coinbase.com/api/v2',
                'fees' => 'https://support.coinbase.com/customer/portal/articles/2109597-buy-sell-bank-transfer-fees',
            ),
            'requiredCredentials' => array (
                'apiKey' => true,
                'secret' => true,
            ),
            'api' => array (
                'public' => array (
                    'get' => array (
                        'currencies',
                        'time',
                        'exchange-rates',
                        'users/{user_id}',
                        'prices/{symbol}/buy',
                        'prices/{symbol}/sell',
                        'prices/{symbol}/spot',
                    ),
                ),
                'private' => array (
                    'get' => array (
                        'accounts',
                        'accounts/{account_id}',
                        'accounts/{account_id}/addresses',
                        'accounts/{account_id}/addresses/{address_id}',
                        'accounts/{account_id}/addresses/{address_id}/transactions',
                        'accounts/{account_id}/transactions',
                        'accounts/{account_id}/transactions/{transaction_id}',
                        'accounts/{account_id}/buys',
                        'accounts/{account_id}/buys/{buy_id}',
                        'accounts/{account_id}/sells',
                        'accounts/{account_id}/sells/{sell_id}',
                        'accounts/{account_id}/deposits',
                        'accounts/{account_id}/deposits/{deposit_id}',
                        'accounts/{account_id}/withdrawals',
                        'accounts/{account_id}/withdrawals/{withdrawal_id}',
                        'payment-methods',
                        'payment-methods/{payment_method_id}',
                        'user',
                        'user/auth',
                    ),
                    'post' => array (
                        'accounts',
                        'accounts/{account_id}/primary',
                        'accounts/{account_id}/addresses',
                        'accounts/{account_id}/transactions',
                        'accounts/{account_id}/transactions/{transaction_id}/complete',
                        'accounts/{account_id}/transactions/{transaction_id}/resend',
                        'accounts/{account_id}/buys',
                        'accounts/{account_id}/buys/{buy_id}/commit',
                        'accounts/{account_id}/sells',
                        'accounts/{account_id}/sells/{sell_id}/commit',
                        'accounts/{account_id}/deposists',
                        'accounts/{account_id}/deposists/{deposit_id}/commit',
                        'accounts/{account_id}/withdrawals',
                        'accounts/{account_id}/withdrawals/{withdrawal_id}/commit',
                    ),
                    'put' => array (
                        'accounts/{account_id}',
                        'user',
                    ),
                    'delete' => array (
                        'accounts/{id}',
                        'accounts/{account_id}/transactions/{transaction_id}',
                    ),
                ),
            ),
            'exceptions' => array (
                'two_factor_required' => '\\ccxt\\AuthenticationError', // 402 When sending money over 2fa limit
                'param_required' => '\\ccxt\\ExchangeError', // 400 Missing parameter
                'validation_error' => '\\ccxt\\ExchangeError', // 400 Unable to validate POST/PUT
                'invalid_request' => '\\ccxt\\ExchangeError', // 400 Invalid request
                'personal_details_required' => '\\ccxt\\AuthenticationError', // 400 User’s personal detail required to complete this request
                'identity_verification_required' => '\\ccxt\\AuthenticationError', // 400 Identity verification is required to complete this request
                'jumio_verification_required' => '\\ccxt\\AuthenticationError', // 400 Document verification is required to complete this request
                'jumio_face_match_verification_required' => '\\ccxt\\AuthenticationError', // 400 Document verification including face match is required to complete this request
                'unverified_email' => '\\ccxt\\AuthenticationError', // 400 User has not verified their email
                'authentication_error' => '\\ccxt\\AuthenticationError', // 401 Invalid auth (generic)
                'invalid_token' => '\\ccxt\\AuthenticationError', // 401 Invalid Oauth token
                'revoked_token' => '\\ccxt\\AuthenticationError', // 401 Revoked Oauth token
                'expired_token' => '\\ccxt\\AuthenticationError', // 401 Expired Oauth token
                'invalid_scope' => '\\ccxt\\AuthenticationError', // 403 User hasn’t authenticated necessary scope
                'not_found' => '\\ccxt\\ExchangeError', // 404 Resource not found
                'rate_limit_exceeded' => '\\ccxt\\DDoSProtection', // 429 Rate limit exceeded
                'internal_server_error' => '\\ccxt\\ExchangeError', // 500 Internal server error
            ),
            'markets' => array (
                'BTC/USD' => array ( 'id' => 'btc-usd', 'symbol' => 'BTC/USD', 'base' => 'BTC', 'quote' => 'USD' ),
                'LTC/USD' => array ( 'id' => 'ltc-usd', 'symbol' => 'LTC/USD', 'base' => 'LTC', 'quote' => 'USD' ),
                'ETH/USD' => array ( 'id' => 'eth-usd', 'symbol' => 'ETH/USD', 'base' => 'ETH', 'quote' => 'USD' ),
                'BCH/USD' => array ( 'id' => 'bch-usd', 'symbol' => 'BCH/USD', 'base' => 'BCH', 'quote' => 'USD' ),
            ),
        ));
    }

    public function fetch_time () {
        $response = $this->publicGetTime ();
        $data = $response['data'];
        return $this->parse8601 ($data['iso']);
    }

    public function fetch_currencies ($params = array ()) {
        $response = $this->publicGetCurrencies ($params);
        $currencies = $response['data'];
        $result = array ();
        for ($c = 0; $c < count ($currencies); $c++) {
            $currency = $currencies[$c];
            $id = $currency['id'];
            $name = $currency['name'];
            $code = $this->common_currency_code($id);
            $minimum = $this->safe_float($currency, 'min_size');
            $result[$code] = array (
                'id' => $id,
                'code' => $code,
                'info' => $currency, // the original payload
                'name' => $name,
                'active' => true,
                'status' => 'ok',
                'fee' => null,
                'precision' => null,
                'limits' => array (
                    'amount' => array (
                        'min' => $minimum,
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
                    'withdraw' => array (
                        'min' => null,
                        'max' => null,
                    ),
                ),
            );
        }
        return $result;
    }

    public function fetch_ticker ($symbol, $params = array ()) {
        $this->load_markets();
        $timestamp = $this->seconds ();
        $market = $this->market ($symbol);
        $request = array_merge (array (
            'symbol' => $market['id'],
        ), $params);
        $buy = $this->publicGetPricesSymbolBuy ($request);
        $sell = $this->publicGetPricesSymbolSell ($request);
        $spot = $this->publicGetPricesSymbolSpot ($request);
        $ask = $this->safe_float($buy['data'], 'amount');
        $bid = $this->safe_float($sell['data'], 'amount');
        $last = $this->safe_float($spot['data'], 'amount');
        return array (
            'symbol' => $symbol,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'bid' => $bid,
            'ask' => $ask,
            'last' => $last,
            'high' => null,
            'low' => null,
            'bidVolume' => null,
            'askVolume' => null,
            'vwap' => null,
            'open' => null,
            'close' => null,
            'previousClose' => null,
            'change' => null,
            'percentage' => null,
            'average' => null,
            'baseVolume' => null,
            'quoteVolume' => null,
            'info' => array (
                'buy' => $buy,
                'sell' => $sell,
                'spot' => $spot,
            ),
        );
    }

    public function fetch_balance ($params = array ()) {
        $response = $this->privateGetAccounts ();
        $balances = $response['data'];
        $result = array ( 'info' => $response );
        for ($b = 0; $b < count ($balances); $b++) {
            $balance = $balances[$b];
            $currency = $balance['balance']['currency'];
            $account = array (
                'free' => $this->safe_float($balance['balance'], 'amount'),
                'used' => null,
                'total' => $this->safe_float($balance['balance'], 'amount'),
            );
            $result[$currency] = $account;
        }
        return $this->parse_balance($result);
    }

    public function sign ($path, $api = 'public', $method = 'GET', $params = array (), $headers = null, $body = null) {
        $request = '/' . $this->implode_params($path, $params);
        $query = $this->omit ($params, $this->extract_params($path));
        if ($method === 'GET') {
            if ($query)
                $request .= '?' . $this->urlencode ($query);
        }
        $url = $this->urls['api'] . '/' . $this->version . $request;
        if ($api === 'private') {
            $this->check_required_credentials();
            $nonce = (string) $this->nonce ();
            $payload = '';
            if ($method !== 'GET') {
                if ($query) {
                    $body = $this->json ($query);
                    $payload = $body;
                }
            }
            $what = $nonce . $method . '/' . $this->version . $request . $payload;
            $signature = $this->hmac ($this->encode ($what), $this->encode ($this->secret));
            $headers = array (
                'CB-ACCESS-KEY' => $this->apiKey,
                'CB-ACCESS-SIGN' => $signature,
                'CB-ACCESS-TIMESTAMP' => $nonce,
                'Content-Type' => 'application/json',
            );
        }
        return array ( 'url' => $url, 'method' => $method, 'body' => $body, 'headers' => $headers );
    }

    public function handle_errors ($code, $reason, $url, $method, $headers, $body) {
        if (gettype ($body) != 'string')
            return; // fallback to default error handler
        if (strlen ($body) < 2)
            return; // fallback to default error handler
        if (($body[0] === '{') || ($body[0] === '[')) {
            $response = json_decode ($body, $as_associative_array = true);
            $feedback = $this->id . ' ' . $body;
            //
            //    array ("error" => "invalid_request", "error_description" => "The request is missing a required parameter, includes an unsupported parameter value, or is otherwise malformed.")
            //
            // or
            //
            //    {
            //      "$errors" => array (
            //        {
            //          "id" => "not_found",
            //          "message" => "Not found"
            //        }
            //      )
            //    }
            //
            $exceptions = $this->exceptions;
            $errorCode = $this->safe_string($response, 'error');
            if ($errorCode !== null) {
                if (is_array ($exceptions) && array_key_exists ($errorCode, $exceptions)) {
                    throw new $exceptions[$errorCode] ($feedback);
                } else {
                    throw new ExchangeError ($feedback);
                }
            }
            $errors = $this->safe_value($response, 'errors');
            if ($errors !== null) {
                if (gettype ($errors) === 'array' && count (array_filter (array_keys ($errors), 'is_string')) == 0) {
                    $numErrors = is_array ($errors) ? count ($errors) : 0;
                    if ($numErrors > 0) {
                        $errorCode = $this->safe_string($errors[0], 'id');
                        if ($errorCode !== null) {
                            if (is_array ($exceptions) && array_key_exists ($errorCode, $exceptions)) {
                                throw new $exceptions[$errorCode] ($feedback);
                            } else {
                                throw new ExchangeError ($feedback);
                            }
                        }
                    }
                }
            }
            $data = $this->safe_value($response, 'data');
            if ($data === null)
                throw new ExchangeError ($this->id . ' failed due to a malformed $response ' . $this->json ($response));
        }
    }
}
