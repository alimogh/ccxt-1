<?php

namespace ccxt;

use Exception as Exception; // a common import

class mandala extends Exchange {

    public function describe () {
        return array_replace_recursive (parent::describe (), array (
            'id' => 'mandala',
            'name' => 'Mandala',
            'countries' => array ( 'MT' ),
            'version' => 'v1.1',
            'rateLimit' => 1500,
            'certified' => false,
            // new metainfo interface
            'has' => array (
                'cancelAllOrders' => true,
                'CORS' => true,
                'createDepositAddress' => true,
                'createMarketOrder' => true,
                'fetchCurrencies' => true,
                'fetchDepositAddress' => true,
                'fetchDepositAddresses' => true,
                'fetchDeposits' => true,
                'fetchMyTrades' => true,
                'fetchOHLCV' => true,
                'fetchOpenOrders' => true,
                'fetchOrder' => true,
                'fetchOrders' => true,
                'fetchOrderStatus' => true,
                'fetchTickers' => true,
                'fetchWithdrawals' => true,
                'withdraw' => true,
            ),
            'timeframes' => array (
                '1m' => '1',
                '5m' => '5',
                '1h' => '60',
                '1d' => '1440',
            ),
            'comment' => 'Modulus Exchange API ',
            'hostname' => 'mandalaex.com',
            'urls' => array (
                'logo' => 'https://user-images.githubusercontent.com/1294454/54686665-df629400-4b2a-11e9-84d3-d88856367dd7.jpg',
                'api' => 'https://zapi.{hostname}',
                'www' => 'https://mandalaex.com',
                'doc' => array (
                    'https://apidocs.mandalaex.com',
                ),
                'fees' => array (
                    'https://mandalaex.com/trading-rules/',
                ),
                'referral' => 'https://trade.mandalaex.com/?ref=564377',
            ),
            'api' => array (
                'settings' => array (
                    'get' => array (
                        'getCoinInfo', // FIX ME, this endpoint is documented, but broken => https://zapi.mandalaex.com/api/getCoinInfo
                        'GetSettings',
                        'CurrencySettings',
                        'Get_Withdrawal_Limits',
                    ),
                ),
                'token' => array (
                    'post' => array (
                        'token',
                    ),
                ),
                'public' => array (
                    'post' => array (
                        'AuthenticateUser',
                        'ForgotPassword',
                        'SignUp',
                        'check_Duplicate_Mobile',
                        'check_Duplicate_Email',
                    ),
                ),
                'api' => array (
                    'get' => array (
                        'GAuth_Check_Status',
                        'GAuth_Enable_Request',
                        'GetProfile',
                        'Loginhistory',
                        'ListAllAddresses',
                        'Get_User_Withdrawal_Limits',
                        'GetPendingOrders', // ?side=aLL&pair=ALL&timestamp=1541240408&recvWindow=3600',
                        'TradeHistory', // ?side=ALL&pair=ALL&timestamp=1550920234&recvWindow=10000&count=100&page=1',
                        'GOKYC_Get_Kyc_Form',
                        'language_list',
                        'language', // ?code=en&namespace=translation',
                        'get_page_n_content',
                        'GetExchangeTokenDiscountEnrollmentStatus',
                        'GetDiscountTiers',
                        'My_Affiliate',
                        'Affiliate_Summary',
                        'Affiliate_Commission',
                        'List_Fiat_Manual_Deposit_Requests',
                        'List_Fiat_BanksList/YCN/',
                        'Get_Fiat_PGs', // ?Currency=TRY',
                        'get_insta_pairs',
                        'hmac', // ?side=BUY&market=BTC&trade=ETH&type=STOPLIMIT&volume=0.025&rate=0.032&timeInForce=GTC&stop=2&',
                    ),
                    'post' => array (
                        'GAuth_Set_Enable',
                        'GAuth_Disable_Request',
                        'VerifyAccount',
                        'SignUp_Resend_Email',
                        'AuthenticateUser_Resend_EmailOTP/{tempAuthToken}',
                        'Validate_BearerToken',
                        'RequestChangePasswordOT',
                        'ChangePassword',
                        'ResetPassword',
                        'GenerateAddress',
                        'GetBalance',
                        'GetDeposits',
                        'GetWithdrawals',
                        'RequestWithdraw',
                        'RequestWithdrawConfirmation',
                        'RequestTransfer_AeraPass',
                        'PlaceOrder',
                        'PlaceOrder_Priced',
                        'CancelOrder',
                        'KYC_GetSumAndSub_AccessToken',
                        'KYC_SaveSumAndSubstanceApplicationId',
                        'GOKYC_Submit_KYC_Form',
                        'SetExchangeTokenDiscountEnrollment',
                        'Dis_Enroll_ExchangeTokenDiscount',
                        'Webhook_BitGoDeposit',
                        'Add_Fiat_Manual_Deposit_Request',
                        'Add_Fiat_Manual_Withdrawal_Request',
                        'Add_Fiat_PG_Deposit_Request',
                        'ListApiKey',
                        'GenerateApiKey',
                        'DeleteApiKey',
                        'request_insta_trade',
                        'confirm_insta_trade',
                        'simplex_get_quote',
                        'simplex_payment',
                        'hmac',
                        'import_translations',
                    ),
                ),
                'market' => array (
                    'get' => array (
                        'get-market-summary',
                        'get-market-summary/{marketId}',
                        'get-trade-history/{marketId}',
                        'get-bid_ask-price/{marketId}',
                        'get-open-orders/{marketId}/{side}/{depth}',
                        'get-currency-price/{marketId}',
                        'get-currency-usd-rate/{currencyId}',
                        'depth', // ?symbol=BTC_ETH&limit=10
                        'get-chart-data', // ?baseCurrency=BTC&quoteCurrency=ETH&interval=60&limit=200&timestamp=1541228704517
                    ),
                ),
                'order' => array (
                    'get' => array (
                        'my-order-history/{key}/{side}',
                        'my-order-history/{key}/{side}/{orderId}',
                        'my-order-status/{key}/{side}/{orderId}',
                        'my-trade-history', // ?side=BUY&pair=BTC_ETH&orderID=13165837&apiKey=d14b1eb4-fe1f-4bfc-896d-97285975989e
                        'hmac', // ?side=BUY&market=BTC&trade=ETH&type=STOPLIMIT&volume=0.025&rate=0.032&timeInForce=GTC&stop=2&'
                    ),
                    'post' => array (
                        'PlaceOrder',
                        'cancel-my-order',
                        'cancel-all-my-orders',
                        'get-balance',
                    ),
                ),
            ),
            'fees' => array (
                'trading' => array (
                    'tierBased' => false,
                    'percentage' => true,
                    'maker' => 0.005,
                    'taker' => 0.005,
                ),
            ),
            'exceptions' => array (
                'exact' => array (
                    'Failure_General' => '\\ccxt\\ExchangeError', // array("Status":"Error","Message":"Failure_General","Data":"Cannot roll back TransBuyOrder. No transaction or savepoint of that name was found.")
                    'Exception_Insufficient_Funds' => '\\ccxt\\InsufficientFunds', // array("Status":"Error","Message":"Exception_Insufficient_Funds","Data":"Insufficient Funds.")
                    'Exception_TimeStamp' => '\\ccxt\\BadRequest', // array("status":"BadRequest","message":"Exception_TimeStamp","data":"Invalid timestamp.")
                    'Exception_HMAC_Validation' => '\\ccxt\\AuthenticationError', // array("status":"Error","message":"Exception_HMAC_Validation","data":"HMAC validation failed.")
                    'Exception_General' => '\\ccxt\\BadRequest', // array("status":"BadRequest","message":"Exception_General","data":"Our servers are experiencing some glitch, please try again later.")
                    'Must provide the orderID param.' => '\\ccxt\\BadRequest', // array("Status":"BadRequest","Message":"Must provide the orderID param.","Data":null)
                    'Invalid Market_Currency pair!' => '\\ccxt\\ExchangeError', // array("status":"Error","errorMessage":"Invalid Market_Currency pair!","data":null)
                    'Invalid volume parameter.' => '\\ccxt\\InvalidOrder', // array("Status":"BadRequest","Message":"Invalid volume parameter.","Data":null)
                    'Invalid rate parameter.' => '\\ccxt\\InvalidOrder', // array("Status":"BadRequest","Message":"Invalid rate parameter.","Data":null)
                    "Invalid parameter 'side', must be 'BUY' or 'SELL'." => '\\ccxt\\InvalidOrder', // array("Status":"BadRequest","Message":"Invalid parameter 'side', must be 'BUY' or 'SELL'.","Data":null)
                    'Invalid Type' => '\\ccxt\\BadRequest', // on fetchOrders with a wrong type array("status":"Error","errorMessage":"Invalid Type","data":null)
                    'Exception_Invalid_CurrencyName' => '\\ccxt\\BadRequest', // array("status":"BadRequest","message":"Exception_Invalid_CurrencyName","data":"Invalid Currency name")
                    'Exception_BadRequest' => '\\ccxt\\BadRequest', // array("status":"BadRequest","message":"Exception_BadRequest","data":"Invalid Payload")
                ),
                'broad' => array (
                ),
            ),
            'options' => array (
                'symbolSeparator' => '_',
                'api' => array (
                    'settings' => 'api',
                    'public' => 'api',
                ),
                'fetchCurrencies' => array (
                    'expires' => 5000,
                ),
                // https://documenter.getpostman.com/view/5614390/RWguuvfd#a74ee943-3b7a-415e-9315-a7bf204db09d
                // HMAC can be obtained using a Secret key. Thispre shared secret key ensures that the message is encrypted by a legitimate source. You can get a secret key issued for your sandbox enviroment by writing an email to support@modulus.io
                // Secret-Key : 03c06dd7-4982-441a-910d-5fd2cbb3f1c6
                'secret' => '03c06dd7-4982-441a-910d-5fd2cbb3f1c6',
            ),
        ));
    }

    public function sign_in ($params = array ()) {
        if (!$this->login || !$this->password) {
            throw new AuthenticationError($this->id . ' signIn() requires $this->login (email) and $this->password credentials');
        }
        $authenticateRequest = array (
            'email' => $this->login,
            'password' => $this->password,
        );
        $authenticateResponse = $this->publicPostAuthenticateUser ($authenticateRequest);
        //
        //     {
        //         status => 'Success',
        //         message => 'Success!',
        //         $data => {
        //             $tempAuthToken => 'e1b0603a-5996-4bac-9ec4-f097a02d9696',
        //             tokenExpiry => '2019-03-19T21:16:15.999201Z',
        //             twoFAMehtod => 'GAuth'
        //         }
        //     }
        //
        $data = $this->safe_value($authenticateResponse, 'data', array());
        $tempAuthToken = $this->safe_string($data, 'tempAuthToken');
        $otp = null;
        if ($this->twofa !== null) {
            $otp = $this->oath ();
        }
        $otp = $this->safe_string($params, 'password', $otp);
        if ($otp === null) {
            throw new AuthenticationError($this->id . ' signIn() requires $this->twofa credential or a one-time 2FA "password" parameter');
        }
        $tokenRequest = array (
            'grant_type' => 'password',
            'username' => $tempAuthToken,
            'password' => $otp,
        );
        $tokenResponse = $this->tokenPostToken (array_merge ($tokenRequest, $params));
        //
        //     {
        //         "access_token" => "WWRNCO--bFjX3zKAixROAjy3dbU0csNoI91PXpT1oScTrik50mVrSIbr22HrsJV5ATXgN867vy66pxY7IzMQGzYtz-7KTxUnL6uPbQpiveBgPEGD5drpvh5KwhcCOzFelJ1-OxZa6g6trx82x2YqQI7Lny0VkAIEv-EBQT8B4C_UVYhoMVCzYumeQgcxtyXc9hoRolVUwwQ965--LrAYIybBby85LzRRIfh7Yg_CVSx6zehAcHFUeKh2tE4NwN9lYweeDEPb6z2kHn0UJb18nxYcC3-NjgiyublBiY1AI_U",
        //         "token_type" => "bearer",
        //         "expires_in" => 86399
        //     }
        //
        $expiresIn = $this->safe_integer($tokenResponse, 'expires_in');
        $this->options['expires'] = $this->sum ($this->milliseconds (), $expiresIn * 1000);
        $this->options['accessToken'] = $this->safe_string($tokenResponse, 'accessToken');
        $this->options['tokenType'] = $this->safe_string($tokenResponse, 'token_type');
        // $accessToken = $this->safe_value($tokenResponse, 'access_token');
        // $this->headers['Authorization'] = 'Bearer ' . $accessToken;
        return $tokenResponse;
    }

    public function fetch_currencies_from_cache ($params = array ()) {
        // this method is $now redundant
        // currencies are $now fetched before markets
        $options = $this->safe_value($this->options, 'fetchCurrencies', array());
        $timestamp = $this->safe_integer($options, 'timestamp');
        $expires = $this->safe_integer($options, 'expires', 1000);
        $now = $this->milliseconds ();
        if (($timestamp === null) || (($now - $timestamp) > $expires)) {
            $response = $this->settingsGetCurrencySettings ($params);
            $this->options['fetchCurrencies'] = array_merge ($options, array (
                'response' => $response,
                'timestamp' => $now,
            ));
        }
        return $this->safe_value($this->options['fetchCurrencies'], 'response');
    }

    public function fetch_currencies ($params = array ()) {
        $response = $this->fetch_currencies_from_cache ($params);
        $this->options['currencies'] = array (
            'timestamp' => $this->milliseconds (),
            'response' => $response,
        );
        //
        //     {
        //         status => 'Success',
        //         message => 'Success!',
        //         $data => array (
        //             array (
        //                 shortName => 'BAT',
        //                 fullName => 'Basic Attention Token',
        //                 buyServiceCharge => 0.5,
        //                 sellServiceCharge => 0.5,
        //                 withdrawalServiceCharge => 0.25,
        //                 withdrawalServiceChargeInBTC => 0,
        //                 confirmationCount => 29,
        //                 contractAddress => null,
        //                 minWithdrawalLimit => 100,
        //                 maxWithdrawalLimit => 2000000,
        //                 decimalPrecision => 18,
        //                 tradeEnabled => true,
        //                 depositEnabled => true,
        //                 withdrawalEnabled => true,
        //                 secondaryWalletType => '',
        //                 addressSeparator => '',
        //                 walletType => 'BitGo',
        //                 withdrawalServiceChargeType => 'Percentage',
        //             ),
        //             array (
        //                 shortName => 'BCH',
        //                 fullName => 'BitcoinCash',
        //                 buyServiceCharge => 0.5,
        //                 sellServiceCharge => 0.5,
        //                 withdrawalServiceCharge => 0.25,
        //                 withdrawalServiceChargeInBTC => 0.001,
        //                 confirmationCount => 3,
        //                 contractAddress => null,
        //                 minWithdrawalLimit => 0.1,
        //                 maxWithdrawalLimit => 300,
        //                 decimalPrecision => 8,
        //                 tradeEnabled => true,
        //                 depositEnabled => true,
        //                 withdrawalEnabled => true,
        //                 secondaryWalletType => '',
        //                 addressSeparator => '',
        //                 walletType => 'BitGo',
        //                 withdrawalServiceChargeType => 'Percentage',
        //             ),
        //         )
        //     }
        //
        $data = $this->safe_value($response, 'data', array());
        $result = array();
        for ($i = 0; $i < count ($data); $i++) {
            $currency = $data[$i];
            $id = $this->safe_string($currency, 'shortName');
            $code = $this->common_currency_code($id);
            $name = $this->safe_string($currency, 'fullName');
            $precision = $this->safe_integer($currency, 'decimalPrecision');
            $active = true;
            $canWithdraw = $this->safe_value($currency, 'withdrawalEnabled');
            $canDeposit = $this->safe_value($currency, 'depositEnabled');
            if (!$canWithdraw || !$canDeposit) {
                $active = false;
            }
            $result[$code] = array (
                'id' => $id,
                'code' => $code,
                'name' => $name,
                'active' => $active,
                'precision' => $precision,
                'fee' => $this->safe_float($currency, 'withdrawalServiceCharge') / 100,
                'limits' => array (
                    'amount' => array (
                        'min' => pow(10, -$precision),
                        'max' => pow(10, $precision),
                    ),
                    'price' => array (
                        'min' => pow(10, -$precision),
                        'max' => pow(10, $precision),
                    ),
                    'cost' => array (
                        'min' => null,
                        'max' => null,
                    ),
                    'withdraw' => array (
                        'min' => $this->safe_float($currency, 'minWithdrawalLimit'),
                        'max' => $this->safe_float($currency, 'maxWithdrawalLimit'),
                    ),
                ),
                'info' => $currency,
            );
        }
        return $result;
    }

    public function fetch_markets ($params = array ()) {
        $currenciesResponse = $this->fetch_currencies_from_cache ($params);
        $currencies = $this->safe_value($currenciesResponse, 'data', array());
        $currenciesById = $this->index_by($currencies, 'shortName');
        $response = $this->marketGetGetMarketSummary ();
        //
        //     array (
        //         status => 'Success',
        //         errorMessage => null,
        //         $data => array (
        //             BTC_BAT:
        //                 Last => 0.00003431,
        //                 LowestAsk => 0,
        //                 HeighestBid => 0,
        //                 PercentChange => 0,
        //                 BaseVolume => 0,
        //                 QuoteVolume => 0,
        //                 High_24hr => 0,
        //                 Low_24hr => 0,
        //             ),
        //             ETH_ZRX => array (
        //                 Last => 0.00213827,
        //                 LowestAsk => 0,
        //                 HeighestBid => 0,
        //                 PercentChange => 0,
        //                 BaseVolume => 0,
        //                 QuoteVolume => 0,
        //                 High_24hr => 0,
        //                 Low_24hr => 0,
        //             ),
        //         ),
        //     }
        //
        $result = array();
        $data = $this->safe_value($response, 'data', array());
        $ids = is_array($data) ? array_keys($data) : array();
        for ($i = 0; $i < count ($ids); $i++) {
            $id = $ids[$i];
            $market = $data[$id];
            list($quoteId, $baseId) = explode('_', $id);  // they have base/quote reversed with some endpoints
            $base = $this->common_currency_code($baseId);
            $quote = $this->common_currency_code($quoteId);
            $symbol = $base . '/' . $quote;
            $baseCurrency = $this->safe_value($currenciesById, $baseId, array());
            $quoteCurrency = $this->safe_value($currenciesById, $quoteId, array());
            $precision = array (
                'amount' => $this->safe_integer($baseCurrency, 'decimalPrecision', 8),
                'price' => $this->safe_integer($quoteCurrency, 'decimalPrecision', 8),
            );
            $baseTradeEnabled = $this->safe_value($baseCurrency, 'tradeEnabled', true);
            $quoteTradeEnabled = $this->safe_value($quoteCurrency, 'tradeEnabled', true);
            $active = $baseTradeEnabled && $quoteTradeEnabled;
            $result[] = array (
                'id' => $id,
                'symbol' => $symbol,
                'base' => $base,
                'quote' => $quote,
                'baseId' => $baseId,
                'quoteId' => $quoteId,
                'active' => $active,
                'info' => $market,
                'precision' => $precision,
                'limits' => array (
                    'amount' => array (
                        'min' => pow(10, -$precision['amount']),
                        'max' => null,
                    ),
                    'price' => array (
                        'min' => pow(10, -$precision['price']),
                        'max' => null,
                    ),
                ),
            );
        }
        return $result;
    }

    public function fetch_balance ($params = array ()) {
        $this->load_markets();
        $request = array (
            'currency' => 'ALL',
        );
        $response = $this->orderPostGetBalance (array_merge ($request, $params));
        //
        //     {
        //         Status => 'Success',
        //         Message => null,
        //         Data => array (
        //             array( currency => 'BCH', $balance => 0, balanceInTrade => 0 ),
        //             array( currency => 'BTC', $balance => 0, balanceInTrade => 0 ),
        //             ...,
        //         ),
        //     }
        //
        $data = $this->safe_value($response, 'Data');
        $result = array( 'info' => $response );
        for ($i = 0; $i < count ($data); $i++) {
            $balance = $data[$i];
            $code = $this->common_currency_code($this->safe_string($balance, 'currency'));
            $account = $this->account ();
            $free = $this->safe_float($balance, 'balance', 0);
            $used = $this->safe_float($balance, 'balanceInTrade', 0);
            $total = $this->sum ($free, $used);
            $account['free'] = $free;
            $account['used'] = $used;
            $account['total'] = $total;
            $result[$code] = $account;
        }
        return $this->parse_balance($result);
    }

    public function fetch_order_book ($symbol, $limit = null, $params = array ()) {
        $this->load_markets();
        if ($limit === null) {
            $limit = 10;
        }
        $request = array (
            'symbol' => $this->market_id($symbol),
            'limit' => $limit,
        );
        $response = $this->marketGetDepth (array_merge ($request, $params));
        // https://documenter.getpostman.com/view/6273708/RznBP1Hh#19469d73-45b5-4dd1-8464-c043efb62e00
        //
        //     {
        //         status => 'Success',
        //         errorMessage => '',
        //         $data => array (
        //             lastUpdate => 1552825727108,
        //             bids => [
        //                 [ "0.02880201", "0.05939008", array()],
        //                 [ "0.02880200", "0.30969842", array()],
        //             ],
        //             'asks' => [
        //                 [ "0.02877161", "0.00001779", array()],
        //                 [ "0.02881321", "0.47325696", array()],
        //             ],
        //         ),
        //     }
        //
        $data = $this->safe_value($response, 'data', array());
        $timestamp = $this->safe_integer($data, 'lastUpdate');
        return $this->parse_order_book($data, $timestamp);
    }

    public function parse_ticker ($ticker, $market = null) {
        //
        // fetchTicker, fetchTickers
        //     {
        //         Pair => 'ETH_MDX', // FIXME missing in fetchTickers
        //         Last => 0.000055,
        //         LowestAsk => 0.000049,
        //         HeighestBid => 0.00003,
        //         PercentChange => 12.47,
        //         BaseVolume => 34.60345,
        //         QuoteVolume => 629153.63636364,
        //         IsFrozen => false, // FIXME missing in fetchTickers
        //         High_24hr => 0,
        //         Low_24hr => 0
        //     }
        //
        $symbol = null;
        $marketId = $this->safe_string($ticker, 'Pair');
        if ($marketId !== null) {
            if (is_array($this->markets_by_id) && array_key_exists($marketId, $this->markets_by_id)) {
                $market = $this->markets_by_id[$marketId];
                $symbol = $market['symbol'];
            } else {
                $symbol = $this->parse_symbol ($marketId);
            }
        }
        if ($symbol === null) {
            if ($market !== null) {
                $symbol = $market['symbol'];
            }
        }
        $last = $this->safe_float($ticker, 'Last');
        return array (
            'symbol' => $symbol,
            'timestamp' => null, // FIXME, no timestamp in tickers
            'datetime' => null,
            'high' => $this->safe_float($ticker, 'High_24hr'),
            'low' => $this->safe_float($ticker, 'Low_24hr'),
            'bid' => $this->safe_float($ticker, 'HeighestBid'),
            'bidVolume' => null,
            'ask' => $this->safe_float($ticker, 'LowestAsk'),
            'askVolume' => null,
            'vwap' => null,
            'open' => null,
            'close' => $last,
            'last' => $last,
            'previousClose' => null,
            'change' => null,
            'percentage' => $this->safe_float($ticker, 'PercentChange'),
            'average' => null,
            'baseVolume' => $this->safe_float($ticker, 'QuoteVolume'),
            'quoteVolume' => $this->safe_float($ticker, 'BaseVolume'),
            'info' => $ticker,
        );
    }

    public function fetch_tickers ($symbols = null, $params = array ()) {
        $this->load_markets();
        $response = $this->marketGetGetMarketSummary ($params);
        //
        //     {
        //         status => 'Success',
        //         errorMessage => null,
        //         $data => array (
        //             BTC_BAT => array (
        //                 Last => 0.00003431,
        //                 LowestAsk => 0,
        //                 HeighestBid => 0,
        //                 PercentChange => 0,
        //                 BaseVolume => 0,
        //                 QuoteVolume => 0,
        //                 High_24hr => 0,
        //                 Low_24hr => 0,
        //             ),
        //             ETH_ZRX => array (
        //                 Last => 0.00213827,
        //                 LowestAsk => 0,
        //                 HeighestBid => 0,
        //                 PercentChange => 0,
        //                 BaseVolume => 0,
        //                 QuoteVolume => 0,
        //                 High_24hr => 0,
        //                 Low_24hr => 0,
        //             ),
        //         ),
        //     }
        //
        $data = $this->safe_value($response, 'data', array());
        $ids = is_array($data) ? array_keys($data) : array();
        $result = array();
        for ($i = 0; $i < count ($ids); $i++) {
            $id = $ids[$i];
            $ticker = $data[$id];
            $market = null;
            $symbol = $id;
            if (is_array($this->markets_by_id) && array_key_exists($id, $this->markets_by_id)) {
                $market = $this->markets_by_id[$id];
                $symbol = $market['symbol'];
            } else {
                $symbol = $this->parse_symbol ($id);
            }
            $result[$symbol] = $this->parse_ticker($ticker, $market);
        }
        return $result;
    }

    public function fetch_ticker ($symbol, $params = array ()) {
        $this->load_markets();
        $request = array (
            'marketId' => $this->market_id($symbol),
        );
        $response = $this->marketGetGetMarketSummaryMarketId (array_merge ($request, $params));
        //
        //     {
        //         status => 'Success',
        //         errorMessage => null,
        //         $data => {
        //             Pair => 'ETH_MDX',
        //             Last => 0.000055,
        //             LowestAsk => 0.000049,
        //             HeighestBid => 0.00003,
        //             PercentChange => 12.47,
        //             BaseVolume => 34.60345,
        //             QuoteVolume => 629153.63636364,
        //             IsFrozen => false,
        //             High_24hr => 0,
        //             Low_24hr => 0
        //         }
        //     }
        //
        $data = $this->safe_value($response, 'data');
        return $this->parse_ticker($data);
    }

    public function parse_trade ($trade, $market = null) {
        //
        // fetchTrades (public)
        //
        //     {
        //         TradeID =>  619255,
        //         Rate =>  0.000055,
        //         Volume =>  79163.63636364,
        //         Total =>  4.354,
        //         Date => "2019-03-16T23:14:48.613",
        //         Type => "Buy"
        //     }
        //
        // fetchMyTrades (private)
        //
        //     {
        //         $orderId => 20000040,
        //         $market => 'ETH',
        //         $trade => 'MDX',
        //         volume => 1,
        //         rate => 2,
        //         $amount => 2,
        //         serviceCharge => 0.003,
        //         $side => 'SELL',
        //         date => '2019-03-20T01:47:09.14'
        //     }
        //
        $timestamp = $this->parse8601 ($this->safe_string_2($trade, 'Date', 'date'));
        $side = $this->safe_string_2($trade, 'Type', 'side');
        if ($side !== null) {
            $side = strtolower($side);
        }
        $id = $this->safe_string($trade, 'TradeID');
        $symbol = null;
        $baseId = $this->safe_string($trade, 'trade');
        $quoteId = $this->safe_string($trade, 'market');
        $base = $this->common_currency_code($baseId);
        $quote = $this->common_currency_code($quoteId);
        if ($base !== null && $quote !== null) {
            $symbol = $base . '/' . $quote;
        } else {
            if ($market !== null) {
                $symbol = $market['symbol'];
            }
        }
        $cost = $this->safe_float_2($trade, 'Total', 'amount');
        $price = $this->safe_float_2($trade, 'Rate', 'rate');
        $amount = $this->safe_float_2($trade, 'Volume', 'volume');
        $orderId = $this->safe_string($trade, 'orderId');
        $feeCost = $this->safe_value($trade, 'serviceCharge');
        $fee = null;
        if ($feeCost !== null) {
            $fee = array (
                'cost' => $feeCost,
                'currency' => $quote,
            );
        }
        return array (
            'info' => $trade,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'symbol' => $symbol,
            'id' => $id,
            'order' => $orderId,
            'type' => null,
            'takerOrMaker' => null,
            'side' => $side,
            'price' => $price,
            'amount' => $amount,
            'cost' => $cost,
            'fee' => $fee,
        );
    }

    public function fetch_trades ($symbol, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $request = array (
            'marketId' => $market['id'],
        );
        // this endpoint returns last 50 trades
        $response = $this->marketGetGetTradeHistoryMarketId (array_merge ($request, $params));
        //
        //     {
        //         status =>   "Success",
        //         errorMessage =>    null,
        //         $data => array (
        //             array (
        //                 TradeID =>  619255,
        //                 Rate =>  0.000055,
        //                 Volume =>  79163.63636364,
        //                 Total =>  4.354,
        //                 Date => "2019-03-16T23:14:48.613",
        //                 Type => "Buy"
        //             ),
        //             {
        //                 TradeID =>  619206,
        //                 Rate =>  0.000073,
        //                 Volume =>  7635.50136986,
        //                 Total =>  0.5573916,
        //                 Date => "2019-02-13T16:49:54.02",
        //                 Type => "Sell"
        //             }
        //         )
        //     }
        //
        $data = $this->safe_value($response, 'data');
        return $this->parse_trades($data, $market, $since, $limit);
    }

    public function parse_ohlcv ($ohlcv, $market = null, $timeframe = '1m', $since = null, $limit = null) {
        //
        //     {
        //         time => 1552830600000,
        //         open => 0.000055,
        //         close => 0.000055,
        //         high => 0.000055,
        //         low => 0.000055,
        //         volume => 0,
        //     }
        //
        return array (
            $this->safe_integer($ohlcv, 'time'),
            $this->safe_float($ohlcv, 'open'),
            $this->safe_float($ohlcv, 'high'),
            $this->safe_float($ohlcv, 'low'),
            $this->safe_float($ohlcv, 'close'),
            $this->safe_float($ohlcv, 'volume'),
        );
    }

    public function fetch_ohlcv ($symbol, $timeframe = '1m', $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        if ($limit === null) {
            $limit = 100; // default is 100
        }
        $offset = $this->parse_timeframe($timeframe) * $this->sum ($limit, 1) * 1000;
        if ($since === null) {
            $since = $this->milliseconds () - $offset;
        }
        $request = array (
            'interval' => $this->timeframes[$timeframe],
            'baseCurrency' => $market['baseId'], // they have base/quote reversed with some endpoints
            'quoteCurrency' => $market['quoteId'],
            'limit' => $limit,
            'timestamp' => $this->sum ($since, $offset),
        );
        $response = $this->marketGetGetChartData (array_merge ($request, $params));
        //
        //     {
        //         status => 'Success',
        //         errorMessage => null,
        //         $data => array (
        //             array (
        //                 time => 1552830600000,
        //                 open => 0.000055,
        //                 close => 0.000055,
        //                 high => 0.000055,
        //                 low => 0.000055,
        //                 volume => 0,
        //             ),
        //             array (
        //                 time => 1552830540000,
        //                 open => 0.000055,
        //                 close => 0.000055,
        //                 high => 0.000055,
        //                 low => 0.000055,
        //                 volume => 0,
        //             ),
        //         ),
        //     }
        //
        $data = $this->safe_value($response, 'data');
        return $this->parse_ohlcvs($data, $market, $timeframe, $since, $limit);
    }

    public function create_order ($symbol, $type, $side, $amount, $price = null, $params = array ()) {
        $this->load_markets();
        $market = $this->market ($symbol);
        $orderPrice = $price;
        if ($type === 'market') {
            $orderPrice = 0;
        }
        $request = array (
            'market' => $market['quoteId'],
            'trade' => $market['baseId'],
            'type' => strtoupper($type), // MARKET, LIMIT, STOPLIMIT
            'side' => strtoupper($side), // BUY, SELL
            // Here GTC should be default for LIMIT, MARKET & STOP LIMIT Orders.
            // IOC,FOK, DO must be passed only with a LIMIT $order->
            // GTC (Good till cancelled), IOC (Immediate or cancel), FOK (Fill or Kill), Do (Day only)
            'timeInForce' => 'GTC',
            'rate' => $this->price_to_precision($symbol, $orderPrice),
            'volume' => $this->amount_to_precision($symbol, $amount),
            // the stop-$price at which a stop-limit $order
            // triggers and becomes a limit $order
            'stop' => 0, // stop is always zero for limit and $market orders
            // 'clientOrderId' => $this->uuid (),
        );
        $response = $this->orderPostPlaceOrder (array_merge ($request, $params));
        //
        //     {
        //         Status => 'Success',
        //         Message => 'Success!',
        //         Data => array (
        //             orderId => 20000031,
        //         ),
        //     }
        //
        $data = $this->safe_value($response, 'Data', array());
        $order = $this->parse_order($data, $market);
        return array_merge ($order, array (
            'symbol' => $symbol,
            'type' => $type,
            'side' => $side,
            'price' => $price,
            'amount' => $amount,
            'status' => 'open',
        ));
    }

    public function cancel_order ($id, $symbol = null, $params = array ()) {
        $this->load_markets();
        $side = $this->safe_string($params, 'side');
        if ($side === null) {
            throw new ArgumentsRequired($this->id . ' cancelOrder() requires an order `$side` extra parameter');
        }
        $params = $this->omit ($params, 'side');
        $id = (string) $id;
        $request = array (
            'orderId' => $id,
            'side' => strtoupper($side),
        );
        $response = $this->orderPostCancelMyOrder (array_merge ($request, $params));
        //
        //     {
        //         Status => 'Success',
        //         Message => 'Success_General',
        //         Data => 'Success!'
        //     }
        //
        return $this->parse_order($response, array (
            'id' => $id,
            'symbol' => $symbol,
            'side' => strtolower($side),
            'status' => 'canceled',
        ));
    }

    public function cancel_all_orders ($symbols = null, $params = array ()) {
        $side = $this->safe_string($params, 'side');
        if ($side === null) {
            throw new ArgumentsRequired($this->id . ' cancelAllOrders() requires an order `$side` extra parameter');
        }
        $params = $this->omit ($params, 'side');
        if ($symbols === null) {
            throw new ArgumentsRequired($this->id . ' cancelAllOrders() requires a `$symbols` argument (a list containing one $symbol)');
        } else {
            $numSymbols = is_array ($symbols) ? count ($symbols) : 0;
            if ($numSymbols !== 1) {
                throw new ArgumentsRequired($this->id . ' cancelAllOrders() requires a `$symbols` argument (a list containing one $symbol)');
            }
        }
        $symbol = $symbols[0];
        $request = array (
            'side' => strtoupper($side),
            'pair' => $this->market_id($symbol),
        );
        return $this->orderPostCancelAllMyOrders (array_merge ($request, $params));
    }

    public function parse_symbol ($id) {
        list($quote, $base) = explode($this->options['symbolSeparator'], $id);
        $base = $this->common_currency_code($base);
        $quote = $this->common_currency_code($quote);
        return $base . '/' . $quote;
    }

    public function parse_order ($order, $market = null) {
        //
        // fetchOrders
        //
        //     {
        //         orderId => 20000038,
        //         $market => 'BTC',
        //         trade => 'ETH',
        //         volume => 1,
        //         pendingVolume => 1,
        //         orderStatus => false,
        //         rate => 1,
        //         $amount => 1,
        //         serviceCharge => 0,
        //         placementDate => '2019-03-19T18:28:43.553',
        //         $completionDate => null
        //     }
        //
        // fetchOpenOrders
        //
        //     {
        //         orderId => 20000038,
        //         $market => 'BTC',
        //         trade => 'ETH',
        //         volume => 1,
        //         rate => 1,
        //         side => 'SELL',
        //         date => '2019-03-19T18:28:43.553',
        //     }
        //
        // fetchOrderStatus
        //
        //     {
        //         "PendingVolume" => 0.7368974,
        //         "Volume" => 0.7368974,
        //         "Price" => 0.22921771,
        //         "Status" => true
        //     }
        //
        $id = $this->safe_string($order, 'orderId');
        $baseId = $this->safe_string($order, 'trade');
        $quoteId = $this->safe_string($order, 'market');
        $base = $this->common_currency_code($baseId);
        $quote = $this->common_currency_code($quoteId);
        $symbol = null;
        if ($base !== null && $quote !== null) {
            $symbol = $base . '/' . $quote;
        }
        $completionDate = $this->parse8601 ($this->safe_string($order, 'completionDate'));
        $timestamp = $this->parse8601 ($this->safe_string_2($order, 'placementDate', 'date'));
        $price = $this->safe_float_2($order, 'rate', 'Price');
        $amount = $this->safe_float_2($order, 'volume', 'Volume');
        $cost = $this->safe_float($order, 'amount');
        $remaining = $this->safe_float_2($order, 'pendingVolume', 'PendingVolume');
        $filled = null;
        if ($amount !== null && $remaining !== null) {
            $filled = max ($amount - $remaining, 0);
        }
        if (!$cost) {
            if ($price && $filled) {
                $cost = $price * $filled;
            }
        }
        if (!$price) {
            if ($cost && $filled) {
                $price = $cost / $filled;
            }
        }
        $status = $this->safe_value_2($order, 'orderStatus', 'Status');
        $status = $status ? 'closed' : 'open';
        $lastTradeTimestamp = null;
        if ($filled > 0) {
            $lastTradeTimestamp = $completionDate;
        }
        if (($filled !== null) && ($amount !== null)) {
            if (($filled < $amount) && ($status === 'closed')) {
                $status = 'canceled';
            }
        }
        $feeCost = $this->safe_value($order, 'serviceCharge');
        $fee = null;
        if ($feeCost !== null) {
            $fee = array (
                'cost' => $feeCost,
                'currency' => $quote,
            );
        }
        return array (
            'info' => $order,
            'id' => $id,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'lastTradeTimestamp' => $lastTradeTimestamp,
            'symbol' => $symbol,
            'type' => 'limit',
            'side' => null,
            'price' => $price,
            'cost' => $cost,
            'average' => null,
            'amount' => $amount,
            'filled' => $filled,
            'remaining' => $remaining,
            'status' => $status,
            'fee' => $fee,
        );
    }

    public function fetch_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $side = $this->safe_string($params, 'side');
        if ($side === null) {
            throw new ArgumentsRequired($this->id . ' fetchOrders() requires an order `$side` extra parameter');
        }
        $params = $this->omit ($params, 'side');
        $request = array (
            'key' => $this->apiKey,
            'side' => strtoupper($side),
            // 'orderId' => id,
        );
        $response = $this->orderGetMyOrderHistoryKeySide (array_merge ($request, $params));
        //
        //     {
        //         status => 'Success',
        //         errorMessage => null,
        //         $data => array (
        //             array (
        //                 orderId => 20000038,
        //                 $market => 'BTC',
        //                 trade => 'ETH',
        //                 volume => 1,
        //                 pendingVolume => 1,
        //                 orderStatus => false,
        //                 rate => 1,
        //                 amount => 1,
        //                 serviceCharge => 0,
        //                 placementDate => '2019-03-19T18:28:43.553',
        //                 completionDate => null
        //             ),
        //             {
        //                 orderId => 20000037,
        //                 $market => 'BTC',
        //                 trade => 'ETH',
        //                 volume => 1,
        //                 pendingVolume => 1,
        //                 orderStatus => true,
        //                 rate => 1,
        //                 amount => 1,
        //                 serviceCharge => 0,
        //                 placementDate => '2019-03-19T18:27:51.087',
        //                 completionDate => '2019-03-19T18:28:16.07'
        //             }
        //         )
        //     }
        //
        $data = $this->safe_value($response, 'data', array());
        $market = ($symbol !== null) ? $this->market ($symbol) : null;
        return $this->parse_orders($data, $market, $since, $limit, array (
            'side' => strtolower($side),
        ));
    }

    public function fetch_open_orders ($symbol = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $side = $this->safe_string($params, 'side', 'ALL');
        $params = $this->omit ($params, 'side');
        $market = null;
        $pair = 'ALL';
        if ($symbol !== null) {
            $market = $this->market ($symbol);
            $pair = $market['baseId'] . '-' . $market['quoteId'];
        }
        $request = array (
            'side' => strtoupper($side),
            'pair' => $pair,
        );
        $response = $this->apiGetGetPendingOrders (array_merge ($request, $params));
        //
        //     {
        //         status => 'Success',
        //         message => 'Success!',
        //         $data => array (
        //             array (
        //                 orderId => 20000038,
        //                 $market => 'BTC',
        //                 trade => 'ETH',
        //                 volume => 1,
        //                 rate => 1,
        //                 $side => 'SELL',
        //                 date => '2019-03-19T18:28:43.553',
        //             ),
        //             {
        //                 orderId => 20000039,
        //                 $market => 'BTC',
        //                 trade => 'ETH',
        //                 volume => 1,
        //                 rate => 2,
        //                 $side => 'SELL',
        //                 date => '2019-03-19T18:48:12.033',
        //             }
        //         )
        //     }
        //
        $data = $this->safe_value($response, 'data');
        return $this->parse_orders($data, $market, $since, $limit);
    }

    public function fetch_order ($id, $symbol = null, $params = array ()) {
        $this->load_markets();
        $side = $this->safe_string($params, 'side');
        if ($side === null) {
            throw new ArgumentsRequired($this->id . ' fetchOrder() requires an order `$side` extra parameter');
        }
        $params = $this->omit ($params, 'side');
        $id = (string) $id;
        $request = array (
            'key' => $this->apiKey,
            'side' => strtoupper($side),
            'orderId' => $id,
        );
        $response = $this->orderGetMyOrderStatusKeySideOrderId (array_merge ($request, $params));
        //
        //     {
        //         "status" => "Success",
        //         "errorMessage" => null,
        //         "$data" => {
        //             "PendingVolume" => 0.7368974,
        //             "Volume" => 0.7368974,
        //             "Price" => 0.22921771,
        //             "Status" => true
        //         }
        //     }
        //
        $data = $this->safe_value($response, 'data');
        return array_merge ($this->parse_order($data), array (
            'id' => $id,
            'side' => strtolower($side),
        ));
    }

    public function fetch_my_trades ($symbol = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $side = $this->safe_string($params, 'side', 'ALL');
        $params = $this->omit ($params, 'side');
        $market = null;
        $pair = 'ALL';
        if ($symbol !== null) {
            $market = $this->market ($symbol);
            $pair = $market['id'];
        }
        $request = array (
            'side' => strtoupper($side),
            'pair' => $pair,
            'orderID' => -1,
            'apiKey' => $this->apiKey,
        );
        $response = $this->orderGetMyTradeHistory (array_merge ($request, $params));
        //
        //     {
        //         Status => 'Success',
        //         Message => null,
        //         Data => array (
        //             array (
        //                 orderId => 20000040,
        //                 $market => 'ETH',
        //                 trade => 'MDX',
        //                 volume => 1,
        //                 rate => 2,
        //                 amount => 2,
        //                 serviceCharge => 0.003,
        //                 $side => 'SELL',
        //                 date => '2019-03-20T01:47:09.14'
        //             ),
        //             array (
        //                 orderId => 20000041,
        //                 $market => 'ETH',
        //                 trade => 'MDX',
        //                 volume => 0.5,
        //                 rate => 3,
        //                 amount => 1.5,
        //                 serviceCharge => 0.00225,
        //                 $side => 'SELL',
        //                 date => '2019-03-20T01:49:20.42'
        //             ),
        //             {
        //                 orderId => 20000041,
        //                 $market => 'ETH',
        //                 trade => 'MDX',
        //                 volume => 0.25,
        //                 rate => 3,
        //                 amount => 0.75,
        //                 serviceCharge => 0.001125,
        //                 $side => 'SELL',
        //                 date => '2019-03-20T01:51:01.307'
        //             }
        //         )
        //     }
        //
        $data = $this->safe_value($response, 'Data');
        return $this->parse_trades($data, $market, $since, $limit);
    }

    public function fetch_deposits ($code = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $currency = null;
        $requestCurrency = 'ALL';
        if ($code !== null) {
            $currency = $this->currency ($code);
            $requestCurrency = $currency['id'];
        }
        $request = array (
            'currency' => $requestCurrency,
        );
        $response = $this->apiPostGetDeposits (array_merge ($request, $params));
        //
        //     {
        //         "status" => "Success",
        //         "message" => null,
        //         "$data" => {
        //             "$deposits" => array (
        //                 {
        //                     ?
        //                 }
        //             )
        //         }
        //     }
        //
        $data = $this->safe_value($response, 'data', array());
        $deposits = $this->safe_value($data, 'deposits', array());
        return $this->parseTransactions ($deposits, $currency, $since, $limit);
    }

    public function fetch_withdrawals ($code = null, $since = null, $limit = null, $params = array ()) {
        $this->load_markets();
        $currency = null;
        $requestCurrency = 'ALL';
        if ($code !== null) {
            $currency = $this->currency ($code);
            $requestCurrency = $currency['id'];
        }
        $request = array (
            'currency' => $requestCurrency,
        );
        $response = $this->apiPostGetWithdrawals (array_merge ($request, $params));
        //
        //     {
        //         "status" => "Success",
        //         "message" => null,
        //         "$data" => {
        //             "$withdrawals" => array (
        //                 {
        //                     "withdrawalType" => "ETH",
        //                     "withdrawalAddress" => "0xE28CE3A999d6035d042D1a87FAab389Cb0B78Db6",
        //                     "withdrawalAmount" => 0.071,
        //                     "txnHash" => null,
        //                     "withdrawalReqDate" => "2018-11-12T09:38:28.43",
        //                     "withdrawalConfirmDate" => null,
        //                     "withdrawalStatus" => "Pending"
        //                 }
        //             )
        //         }
        //     }
        //
        $data = $this->safe_value($response, 'data', array());
        $withdrawals = $this->safe_value($data, 'withdrawals', array());
        return $this->parseTransactions ($withdrawals, $currency, $since, $limit);
    }

    public function parse_transaction_status ($status) {
        $statuses = array (
            'Pending' => 'pending',
        );
        return $this->safe_string($statuses, $status, $status);
    }

    public function parse_transaction ($transaction, $currency = null) {
        //
        // fetchDeposits
        //
        //     {
        //         ?
        //     }
        //
        // fetchWithdrawals
        //
        //     {
        //         "withdrawalType" => "ETH",
        //         "withdrawalAddress" => "0xE28CE3A999d6035d042D1a87FAab389Cb0B78Db6",
        //         "withdrawalAmount" => 0.071,
        //         "txnHash" => null,
        //         "withdrawalReqDate" => "2018-11-12T09:38:28.43",
        //         "withdrawalConfirmDate" => null,
        //         "withdrawalStatus" => "Pending"
        //     }
        //
        $id = null;
        $amount = $this->safe_float($transaction, 'withdrawalAmount');
        $address = $this->safe_string($transaction, 'withdrawalAddress');
        $tag = null;
        $txid = $this->safe_string($transaction, 'txnHash');
        $updated = $this->parse8601 ($this->safe_value($transaction, 'withdrawalConfirmDate'));
        $timestamp = $this->parse8601 ($this->safe_string($transaction, 'withdrawalReqDate', $updated));
        $type = (is_array($transaction) && array_key_exists('withdrawalReqDate', $transaction)) ? 'withdrawal' : 'deposit';
        $code = null;
        $currencyId = $this->safe_string($transaction, 'withdrawalType');
        $currency = $this->safe_value($this->currencies_by_id, $currencyId);
        if ($currency !== null) {
            $code = $currency['code'];
        } else {
            $code = $this->common_currency_code($currencyId);
        }
        $status = $this->parse_transaction_status ($this->safe_string($transaction, 'withdrawalStatus'));
        $feeCost = null;
        if ($type === 'deposit') {
            $status = 'ok';
            $feeCost = 0;
        }
        $fee = null;
        if ($feeCost !== null) {
            $fee = array (
                'cost' => $feeCost,
                'currency' => $code,
            );
        }
        return array (
            'info' => $transaction,
            'id' => $id,
            'currency' => $code,
            'amount' => $amount,
            'address' => $address,
            'tag' => $tag,
            'status' => $status,
            'type' => $type,
            'updated' => $updated,
            'txid' => $txid,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'fee' => $fee,
        );
    }

    public function parse_deposit_addresses ($addresses) {
        $result = array();
        $ids = is_array($addresses) ? array_keys($addresses) : array();
        for ($i = 0; $i < count ($ids); $i++) {
            $id = $ids[$i];
            $address = $addresses[$id];
            $currencyId = strtoupper($id);
            $currency = $this->safe_value($this->currencies_by_id, $currencyId);
            $result[] = $this->parse_deposit_address ($address, $currency);
        }
        return $result;
    }

    public function fetch_deposit_addresses ($codes = null, $params = array ()) {
        $this->load_markets();
        $response = $this->apiGetListAllAddresses ($params);
        //
        //     {
        //         "status" => "Success",
        //         "message" => null,
        //         "$data" => {
        //             "btc" => "3PLKhwm59C21U3KN3YZVQmrQhoE3q1p1i8",
        //             "eth" => "0x8143c11ed6b100e5a96419994846c890598647cf",
        //             "xrp" => "rKHZQttBiDysDT4PtYL7RmLbGm6p5HBHfV:3931222419"
        //         }
        //     }
        //
        $data = $this->safe_value($response, 'data');
        return $this->parse_deposit_addresses ($data);
    }

    public function parse_deposit_address ($depositAddress, $currency = null) {
        //
        //     "btc" => "3PLKhwm59C21U3KN3YZVQmrQhoE3q1p1i8",
        //     "eth" => "0x8143c11ed6b100e5a96419994846c890598647cf",
        //     "xrp" => "rKHZQttBiDysDT4PtYL7RmLbGm6p5HBHfV:3931222419"
        //
        $parts = explode(':', $depositAddress);
        $address = $parts[0];
        $this->check_address($address);
        $tag = null;
        $numParts = is_array ($parts) ? count ($parts) : 0;
        if ($numParts > 1) {
            $tag = $parts[1];
        }
        $code = null;
        if ($currency !== null) {
            $code = $currency['code'];
        }
        $this->check_address($address);
        return array (
            'currency' => $code,
            'address' => $address,
            'tag' => $tag,
            'info' => $depositAddress,
        );
    }

    public function fetch_deposit_address ($code, $params = array ()) {
        $this->load_markets();
        $currency = $this->currency ($code);
        $request = array (
            'currency' => $currency['id'],
        );
        $response = $this->apiPostGenerateAddress (array_merge ($request, $params));
        //
        //     {
        //         status => 'Success',
        //         message => '',
        //         $data => {
        //             $address => '0x13a1ac355bf1be5b157486f619169cf7f9ffed4e'
        //         }
        //     }
        //
        $data = $this->safe_value($response, 'data', array());
        $address = $this->safe_string($data, 'address');
        return $this->parse_deposit_address ($address, $currency);
    }

    public function create_deposit_address ($code, $params = array ()) {
        $this->load_markets();
        $currency = $this->currency ($code);
        $request = array (
            'currency' => $currency['id'],
        );
        $response = $this->apiPostGenerateAddress (array_merge ($request, $params));
        //
        //     {
        //         status => 'Success',
        //         message => '',
        //         $data => {
        //             $address => '0x13a1ac355bf1be5b157486f619169cf7f9ffed4e'
        //         }
        //     }
        //
        $data = $this->safe_value($response, 'data', array());
        $address = $this->safe_string($data, 'address');
        return $this->parse_deposit_address ($address, $currency);
    }

    public function withdraw ($code, $amount, $address, $tag = null, $params = array ()) {
        $this->check_address($address);
        $this->load_markets();
        $currency = $this->currency ($code);
        $withdrawalRequest = array (
            'currency' => $currency['id'],
            'amount' => floatval ($amount),
            'address' => $address,
            // 'addressTag' => null,
        );
        if ($tag !== null) {
            $withdrawalRequest['addressTag'] = $tag;
        }
        $withdrawalResponse = $this->apiPostRequestWithdraw (array_merge ($withdrawalRequest, $params));
        //
        //     {
        //         "status" => "Success",
        //         "message" => null,
        //         "$data" => {
        //             "withdrawalId" => "E26AA92F-F526-4F6C-85FD-B1EA9B1B118D"
        //         }
        //     }
        //
        $data = $this->safe_value($withdrawalResponse, 'data', array());
        $id = $this->safe_string($data, 'withdrawalId');
        $otp = null;
        if ($this->twofa !== null) {
            $otp = $this->oath ();
        }
        $otp = $this->safe_string($params, 'emailToken', $otp);
        if ($otp === null) {
            throw new AuthenticationError($this->id . ' signIn() requires $this->twofa credential or a one-time 2FA "emailToken" parameter');
        }
        $confirmationRequest = array (
            'EmailToken' => $otp,
        );
        $confirmationResponse = $this->apiPostRequestWithdrawConfirmation (array_merge ($confirmationRequest, $params));
        $timestamp = $this->milliseconds ();
        return array (
            'info' => array ( $withdrawalResponse, $confirmationResponse ),
            'id' => $id,
            'timestamp' => $timestamp,
            'datetime' => $this->iso8601 ($timestamp),
            'currency' => $code,
            'amount' => $amount,
            'address' => $address,
            'tag' => $tag,
            'addressFrom' => null,
            'tagFrom' => null,
            'addressTo' => $address,
            'tagTo' => $tag,
            'type' => 'withdrawal',
            'updated' => null,
            'txid' => null,
            'status' => 'pending',
            'fee' => null,
        );
    }

    public function sign ($path, $api = 'api', $method = 'GET', $params = array (), $headers = null, $body = null) {
        $url = $this->implode_params($this->urls['api'], array (
            'hostname' => $this->hostname,
        ));
        if ($api !== 'token') {
            $url .= '/' . $this->safe_string($this->options['api'], $api, $api);
        }
        $url .= '/' . $this->implode_params($path, $params);
        $query = $this->omit ($params, $this->extract_params($path));
        // $isPublic = $this->safe_value($this->options['api'], $api, true);
        if ($api === 'market' || $api === 'settings' || $api === 'public') {
            if ($method === 'POST') {
                $body = $this->json ($query);
                $headers = array (
                    'Content-Type' => 'application/json',
                );
            } else {
                if ($query) {
                    $url .= '?' . $this->urlencode ($query);
                }
            }
        } else if ($api === 'token') {
            $body = $this->urlencode ($query);
            $headers = array (
                'Content-Type' => 'application/x-www-form-urlencoded',
            );
        } else {
            $this->check_required_credentials();
            $query = $this->keysort (array_merge (array (
                'timestamp' => $this->seconds (),
            ), $query));
            $auth = $this->urlencode ($query);
            $secret = ($api === 'api') ? $this->options['secret'] : $this->secret;
            $signature = $this->hmac ($this->encode ($auth), $this->encode ($secret), 'sha512');
            $headers = array (
                'HMAC' => strtoupper($signature),
            );
            if ($api === 'api') {
                $token = $this->safe_string($this->options, 'accessToken');
                if ($token === null) {
                    throw new AuthenticationError($this->id . ' ' . $path . ' endpoint requires an `accessToken` option or a prior call to signIn() method');
                }
                $expires = $this->safe_integer($this->options, 'expires');
                if ($expires !== null) {
                    if ($this->milliseconds () >= $expires) {
                        throw new AuthenticationError($this->id . ' accessToken expired, supply a new `accessToken` or call signIn() method');
                    }
                }
                $tokenType = $this->safe_string($this->options, 'tokenType', 'bearer');
                $headers['Authorization'] = $tokenType . ' ' . $token;
            }
            if ($method === 'POST') {
                $body = $this->json ($query);
                $headers['Content-Type'] = 'application/json';
                $headers['publicKey'] = $this->apiKey;
            } else if ($method === 'GET') {
                if ($query) {
                    $url .= '?' . $this->urlencode ($query);
                }
            }
        }
        return array( 'url' => $url, 'method' => $method, 'body' => $body, 'headers' => $headers );
    }

    public function handle_errors ($httpCode, $reason, $url, $method, $headers, $body, $response) {
        if (!$response) {
            return; // fallback to default error handler
        }
        //
        //     array("Status":"Error","Message":"Exception_Insufficient_Funds","Data":"Insufficient Funds.")
        //     array("$status":"Error","errorMessage":"Invalid Market_Currency pair!","data":null)
        //     array("$status":"BadRequest","$message":"Exception_BadRequest","data":"Invalid Payload")
        //
        //
        $status = $this->safe_string_2($response, 'status', 'Status');
        if (($status !== null) && ($status !== 'Success')) {
            $message = $this->safe_string_2($response, 'errorMessage', 'Message');
            $message = $this->safe_string($response, 'message', $message);
            $feedback = $this->id . ' ' . $this->json ($response);
            $exact = $this->exceptions['exact'];
            if (is_array($exact) && array_key_exists($message, $exact)) {
                throw new $exact[$message]($feedback);
            }
            $broad = $this->exceptions['broad'];
            $broadKey = $this->findBroadlyMatchedKey ($broad, $message);
            if ($broadKey !== null) {
                throw new $broad[$broadKey]($feedback);
            }
            throw new ExchangeError($feedback); // unknown $message
        }
    }
}
