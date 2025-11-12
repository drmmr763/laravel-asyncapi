<?php

namespace Drmmr763\AsyncApi\Tests\Fixtures;

use AsyncApi\Attributes\Message;
use AsyncApi\Attributes\Schema;

#[Message(
    name: 'FinalMessage',
    title: 'Final Message',
    summary: 'Test message on final class',
    payload: new Schema(
        type: 'object',
        properties: [
            'id' => new Schema(type: 'integer'),
            'status' => new Schema(type: 'string'),
        ]
    )
)]
final class TestFinalClass
{
    public function execute(): void
    {
        // Implementation
    }
}
