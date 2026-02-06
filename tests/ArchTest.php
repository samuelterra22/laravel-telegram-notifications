<?php

declare(strict_types=1);

arch('it will not use debugging functions')
    ->expect(['dd', 'dump', 'ray'])
    ->each->not->toBeUsed();

arch('all classes have strict types')
    ->expect('SamuelTerra22\TelegramNotifications')
    ->toUseStrictTypes();

arch('contracts are interfaces')
    ->expect('SamuelTerra22\TelegramNotifications\Contracts')
    ->toBeInterfaces();

arch('enums are enums')
    ->expect('SamuelTerra22\TelegramNotifications\Enums')
    ->toBeEnums();

arch('exceptions extend base exception')
    ->expect('SamuelTerra22\TelegramNotifications\Exceptions')
    ->toExtend(Exception::class);
