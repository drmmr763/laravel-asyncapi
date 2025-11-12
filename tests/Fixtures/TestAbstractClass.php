<?php

namespace Drmmr763\AsyncApi\Tests\Fixtures;

use AsyncApi\Attributes\Message;
use AsyncApi\Attributes\Schema;

#[Message(
    name: 'AbstractMessage',
    title: 'Abstract Message',
    summary: 'Test message on abstract class',
    payload: new Schema(
        type: 'object',
        properties: [
            'id' => new Schema(type: 'integer'),
            'data' => new Schema(type: 'string'),
        ]
    )
)]
abstract class TestAbstractClass
{
    abstract public function process(): void;
}

