<?php

namespace Drmmr763\AsyncApi\Tests\Fixtures;

use AsyncApi\Attributes\Message;
use AsyncApi\Attributes\Schema;

#[Message(
    name: 'ReadonlyMessage',
    title: 'Readonly Message',
    summary: 'Test message on readonly class',
    payload: new Schema(
        type: 'object',
        properties: [
            'id' => new Schema(type: 'integer'),
            'value' => new Schema(type: 'string'),
        ]
    )
)]
readonly class TestReadonlyClass
{
    public function __construct(
        public int $id,
        public string $value
    ) {
    }
}

