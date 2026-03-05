<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use SamuelTerra22\TelegramNotifications\Telegram;

beforeEach(function () {
    Http::fake([
        'api.telegram.org/*' => Http::response([
            'ok' => true,
            'result' => ['message_id' => 1],
        ]),
    ]);

    $this->telegram = new Telegram(
        botsConfig: [
            'default' => ['token' => 'default-token'],
            'alerts' => ['token' => 'alerts-token'],
        ],
        defaultBot: 'default',
        baseUrl: 'https://api.telegram.org',
        timeout: 10,
    );
});

it('sends invoice with all params', function () {
    $prices = [['label' => 'Product', 'amount' => 1000]];

    $this->telegram->sendInvoice(
        chatId: '-1001234',
        title: 'Test Product',
        description: 'A test product',
        payload: 'test-payload',
        currency: 'USD',
        prices: $prices,
        providerToken: 'provider-token-123',
    );

    Http::assertSent(fn ($request) => str_contains($request->url(), '/sendInvoice')
        && $request['chat_id'] === '-1001234'
        && $request['title'] === 'Test Product'
        && $request['description'] === 'A test product'
        && $request['payload'] === 'test-payload'
        && $request['currency'] === 'USD'
        && $request['prices'] === $prices
        && $request['provider_token'] === 'provider-token-123'
    );
});

it('sends invoice without provider_token for Telegram Stars', function () {
    $prices = [['label' => 'Stars Item', 'amount' => 100]];

    $this->telegram->sendInvoice(
        chatId: '-1001234',
        title: 'Stars Product',
        description: 'Pay with stars',
        payload: 'stars-payload',
        currency: 'XTR',
        prices: $prices,
    );

    Http::assertSent(fn ($request) => str_contains($request->url(), '/sendInvoice')
        && $request['chat_id'] === '-1001234'
        && $request['currency'] === 'XTR'
        && ! array_key_exists('provider_token', $request->data())
    );
});

it('sends invoice with reply_markup option', function () {
    $prices = [['label' => 'Product', 'amount' => 1000]];
    $markup = ['inline_keyboard' => [[['text' => 'Pay', 'pay' => true]]]];

    $this->telegram->sendInvoice(
        chatId: '-1001234',
        title: 'Test Product',
        description: 'A test product',
        payload: 'test-payload',
        currency: 'USD',
        prices: $prices,
        options: ['reply_markup' => $markup],
    );

    Http::assertSent(fn ($request) => str_contains($request->url(), '/sendInvoice')
        && $request['reply_markup'] === json_encode($markup)
    );
});

it('creates invoice link', function () {
    $prices = [['label' => 'Product', 'amount' => 500]];

    $this->telegram->createInvoiceLink(
        title: 'Link Product',
        description: 'A link product',
        payload: 'link-payload',
        currency: 'USD',
        prices: $prices,
        providerToken: 'provider-token-456',
    );

    Http::assertSent(fn ($request) => str_contains($request->url(), '/createInvoiceLink')
        && $request['title'] === 'Link Product'
        && $request['description'] === 'A link product'
        && $request['payload'] === 'link-payload'
        && $request['currency'] === 'USD'
        && $request['prices'] === $prices
        && $request['provider_token'] === 'provider-token-456'
    );
});

it('creates invoice link without provider_token', function () {
    $prices = [['label' => 'Stars Item', 'amount' => 50]];

    $this->telegram->createInvoiceLink(
        title: 'Stars Link',
        description: 'Stars link product',
        payload: 'stars-link-payload',
        currency: 'XTR',
        prices: $prices,
    );

    Http::assertSent(fn ($request) => str_contains($request->url(), '/createInvoiceLink')
        && $request['currency'] === 'XTR'
        && ! array_key_exists('provider_token', $request->data())
    );
});

it('answers pre-checkout query with ok true', function () {
    $this->telegram->answerPreCheckoutQuery(
        preCheckoutQueryId: 'pchk-123',
        ok: true,
    );

    Http::assertSent(fn ($request) => str_contains($request->url(), '/answerPreCheckoutQuery')
        && $request['pre_checkout_query_id'] === 'pchk-123'
        && $request['ok'] === true
    );
});

it('answers pre-checkout query with ok false and error_message', function () {
    $this->telegram->answerPreCheckoutQuery(
        preCheckoutQueryId: 'pchk-456',
        ok: false,
        errorMessage: 'Out of stock',
    );

    Http::assertSent(fn ($request) => str_contains($request->url(), '/answerPreCheckoutQuery')
        && $request['pre_checkout_query_id'] === 'pchk-456'
        && $request['ok'] === false
        && $request['error_message'] === 'Out of stock'
    );
});

it('answers pre-checkout query keeps ok false not filtered out', function () {
    $this->telegram->answerPreCheckoutQuery(
        preCheckoutQueryId: 'pchk-789',
        ok: false,
    );

    Http::assertSent(fn ($request) => str_contains($request->url(), '/answerPreCheckoutQuery')
        && array_key_exists('ok', $request->data())
        && $request['ok'] === false
    );
});

it('answers shipping query with ok true and shipping_options', function () {
    $shippingOptions = [
        ['id' => 'standard', 'title' => 'Standard', 'prices' => [['label' => 'Shipping', 'amount' => 500]]],
    ];

    $this->telegram->answerShippingQuery(
        shippingQueryId: 'shq-123',
        ok: true,
        shippingOptions: $shippingOptions,
    );

    Http::assertSent(fn ($request) => str_contains($request->url(), '/answerShippingQuery')
        && $request['shipping_query_id'] === 'shq-123'
        && $request['ok'] === true
        && $request['shipping_options'] === $shippingOptions
    );
});

it('answers shipping query with ok false and error_message', function () {
    $this->telegram->answerShippingQuery(
        shippingQueryId: 'shq-456',
        ok: false,
        errorMessage: 'Cannot ship to this address',
    );

    Http::assertSent(fn ($request) => str_contains($request->url(), '/answerShippingQuery')
        && $request['shipping_query_id'] === 'shq-456'
        && $request['ok'] === false
        && $request['error_message'] === 'Cannot ship to this address'
    );
});

it('refunds star payment', function () {
    $this->telegram->refundStarPayment(
        userId: 123456,
        telegramPaymentChargeId: 'charge-abc',
    );

    Http::assertSent(fn ($request) => str_contains($request->url(), '/refundStarPayment')
        && $request['user_id'] === 123456
        && $request['telegram_payment_charge_id'] === 'charge-abc'
    );
});

it('gets star transactions without params', function () {
    $this->telegram->getStarTransactions();

    Http::assertSent(fn ($request) => str_contains($request->url(), '/getStarTransactions'));
});

it('gets star transactions with offset and limit', function () {
    $this->telegram->getStarTransactions(offset: 10, limit: 25);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/getStarTransactions')
        && $request['offset'] === 10
        && $request['limit'] === 25
    );
});
