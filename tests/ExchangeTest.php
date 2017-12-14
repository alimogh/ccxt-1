<?php

use ccxt\Exchange;
use VCR\VCR;
use PHPUnit\Framework\TestCase;

class ExchangeTest extends TestCase {

    private static $skip = [
        'testFetchTicker' => [
            'bleutrade',
            'btcexchange',
            'bter',
            'ccex',
            'coingi',
            'dsx',
            'gateio',
            'jubi',
            'southxchange',
            'qryptos',
            'quoine',
            'xbtce',
            'yunbi',
        ],
        'testLoadMarkets' => [
            'bter',     // array issue @63
            'ccex',     // not accessible
            'flowbtc',  // bad offset in response
            'gdax',     // UserAgent is required
            'yunbi',    // not accessible
            'bitso',    // not accessible
            'kraken',   // timeout
        ],
        'testFetchTrades' => [
            'allcoin',      // not accessible
            'bitcoincoid',  // not accessible
            'bitstamp1',    // array to string @142
            'btcexchange',  // bad offset in response
            'btctradeua',   // array issue @206
            'btcx',         // bad offset in response
            'coincheck',    // supports BTC/JPY only
            'coingi',       // not accessible
            'huobi',        // not accessible
            'huobicny',     // bad offset in response
            'jubi',         // not accessible
            'kraken',       // bad offset in response
            'okcoincny',    // not accessible
            // empty response:
            'btcchina',
            'livecoin',
            'paymium',
            'xbtce',
        ],
        'testFetchOrderBook' => [
            'allcoin',      // not accessible
            'anxpro',       // not accessible
            'bitcoincoid',  // not accessible
            'bitstamp1',    // array to string @74
            'bittrex',      // null in Exchange @959
            'btcexchange',  // bad offset in response
            'btcx',         // bad offset in response
            'coincheck',    // supports BTC/JPY only
            'coingi',       // not accessible
            'huobi',        // not accessible
            'huobicny',     // bad offset in response
            'jubi',         // not accessible
            'kraken',       // string instead of array @336
            'okcoincny',    // not accessible
            'virwox',       // not implemented
        ],
    ];

    private static $config = [];

    public static function tearDownAfterClass() {
        parent::tearDownAfterClass();
        VCR::turnOff();
    }

    public function setUp() {
        parent::setUp();

        $keys_global = __DIR__ . '/keys.dist.json';
        $keys_local = __DIR__ . '/keys.json';
        $keys_file = file_exists($keys_local) ? $keys_local : $keys_global;
        self::$config = json_decode(file_get_contents ($keys_file), true);
    }

    /**
     * @dataProvider getExchangeClasses
     */
    public function testDescribe(string $name) {
        $exchange = self::exchangeFactory($name);
        $this->assertArrayHasKey('name', $exchange->describe());
    }

    /**
     * @dataProvider getExchangeClasses
     */
    public function testFetchTicker(string $name) {
        $exchange = self::exchangeFactory($name);
        if (in_array($exchange->id, self::$skip[__FUNCTION__])) {
            return $this->markTestSkipped("{$exchange->id}: fetch ticker skipped");
        }

        if ($exchange->hasFetchTickers) {
            VCR::insertCassette(__FUNCTION__ . '@' . $exchange->id . '.json');
            $tickers = $exchange->fetch_tickers();
            VCR::eject();
            $this->assertNotEmpty($tickers);

            $ticker = current($tickers);
            $this->assertArrayHasKey('symbol', $ticker);
            $this->assertArrayHasKey('baseVolume', $ticker);
            $this->assertArrayHasKey('info', $ticker);
        } else {
            $this->assertFalse($exchange->hasFetchTickers);
        }
    }

    /**
     * @dataProvider getExchangeClasses
     */
    public function testLoadMarkets(string $name) {
        $exchange = self::exchangeFactory($name);
        if (in_array($exchange->id, self::$skip[__FUNCTION__])) {
            return $this->markTestSkipped("{$exchange->id}: load markets skipped");
        }

        VCR::insertCassette(__FUNCTION__ . '@' . $exchange->id . '.json');
        $markets = $exchange->load_markets();
        VCR::eject();
        $this->assertNotEmpty($markets);
    }

    /**
     * @dataProvider getExchangeClasses
     */
    public function testFetchTrades(string $name) {
        $exchange = self::exchangeFactory($name);
        if (in_array($exchange->id, array_merge(self::$skip[__FUNCTION__], self::$skip['testLoadMarkets']))) {
            return $this->markTestSkipped("{$exchange->id}: fetch trades skipped");
        }

        if ($exchange->hasFetchTrades) {
            VCR::insertCassette('testLoadMarkets@' . $exchange->id . '.json');
            $markets = $exchange->load_markets();
            VCR::eject();
            $market = current($markets);

            VCR::insertCassette(__FUNCTION__ . '@' . $exchange->id . '.json');
            $trades = $exchange->fetch_trades($market);
            VCR::eject();
            $this->assertNotEmpty($trades);
        } else {
            $this->assertFalse($exchange->hasFetchTrades);
        }
    }

    /**
     * @dataProvider getExchangeClasses
     */
    public function testFetchOrderBook(string $name) {
        $exchange = self::exchangeFactory($name);
        if (in_array($exchange->id, array_merge(self::$skip[__FUNCTION__], self::$skip['testLoadMarkets']))) {
            return $this->markTestSkipped("{$exchange->id}: fetch fetch order book skipped");
        }

        if ($exchange->hasFetchOrderBook) {
            VCR::insertCassette('testLoadMarkets@' . $exchange->id . '.json');
            $markets = $exchange->load_markets();
            VCR::eject();
            $market = current($markets);

            VCR::insertCassette(__FUNCTION__ . '@' . $exchange->id . '.json');
            $order_book = $exchange->fetch_order_book($market);
            VCR::eject();
            $this->assertNotEmpty($order_book);
        } else {
            $this->assertFalse($exchange->hasFetchOrderBook);
        }
    }

    private static function exchangeFactory(string $class): Exchange {
        $exchange = new $class;
        $exchange->timeout = 15000;

        if ($class === 'ccxt\\gdax') {
            $exchange->urls['api'] = 'https://api-public.sandbox.gdax.com';
        }

        if (array_key_exists($exchange->id, self::$config)) {
            $params = self::$config[$exchange->id];

            foreach($params as $key => $value) {
                $exchange->{$key} = $value;
            }
        }

        return $exchange;
    }

    public static function getExchangeClasses(): array {
        $classes = [];
        foreach (Exchange::$exchanges as $name) {
            $classes[] = ["ccxt\\{$name}"];
        }
        return $classes;
    }
}