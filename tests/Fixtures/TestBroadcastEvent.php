<?php

namespace Drmmr763\AsyncApi\Tests\Fixtures;

use AsyncApi\Attributes\Message;
use AsyncApi\Attributes\Schema;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Test broadcast event fixture
 */
#[Message(
    name: 'TestBroadcastEvent',
    title: 'Test Broadcast Event',
    summary: 'A test broadcast event',
    payload: new Schema(
        type: 'object',
        properties: [
            'event' => new Schema(type: 'string'),
            'data' => new Schema(
                type: 'object',
                properties: [
                    'user_id' => new Schema(type: 'integer'),
                    'message' => new Schema(type: 'string')
                ]
            )
        ]
    )
)]
class TestBroadcastEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $userId,
        public string $message
    ) {
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('test.events'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'test.event';
    }

    public function broadcastWith(): array
    {
        return [
            'user_id' => $this->userId,
            'message' => $this->message,
        ];
    }
}

