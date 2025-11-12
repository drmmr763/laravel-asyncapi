<?php

namespace Drmmr763\AsyncApi\Tests\Fixtures;

use AsyncApi\Attributes\AsyncApi;
use AsyncApi\Attributes\Channel;
use AsyncApi\Attributes\Channels;
use AsyncApi\Attributes\Components;
use AsyncApi\Attributes\Info;
use AsyncApi\Attributes\Message;
use AsyncApi\Attributes\Messages;
use AsyncApi\Attributes\Operation;
use AsyncApi\Attributes\Operations;
use AsyncApi\Attributes\Reference;
use AsyncApi\Attributes\Schema;
use AsyncApi\Attributes\Server;
use AsyncApi\Attributes\Servers;

/**
 * Test fixture for AsyncAPI specification
 */
#[AsyncApi(
    asyncapi: '3.0.0',
    id: 'urn:com:example:test',
    info: new Info(
        title: 'Test API',
        version: '1.0.0',
        description: 'Test AsyncAPI specification'
    ),
    servers: new Servers(
        servers: [
            'test' => new Server(
                host: 'localhost:9092',
                protocol: 'kafka',
                description: 'Test Kafka server'
            )
        ]
    ),
    channels: new Channels(
        channels: [
            'test/events' => new Channel(
                address: 'test.events',
                messages: new Messages(
                    messages: [
                        'testEvent' => new Message(
                            name: 'TestEvent',
                            title: 'Test Event',
                            summary: 'A test event',
                            payload: new Schema(
                                type: 'object',
                                properties: [
                                    'id' => new Schema(
                                        type: 'integer',
                                        description: 'Event ID'
                                    ),
                                    'message' => new Schema(
                                        type: 'string',
                                        description: 'Event message'
                                    ),
                                    'timestamp' => new Schema(
                                        type: 'string',
                                        format: 'date-time',
                                        description: 'Event timestamp'
                                    )
                                ],
                                required: ['id', 'message']
                            )
                        )
                    ]
                ),
                description: 'Test events channel'
            )
        ]
    ),
    operations: new Operations(
        operations: [
            'sendTestEvent' => new Operation(
                action: 'send',
                channel: new Reference(ref: '#/channels/test~1events'),
                title: 'Send Test Event',
                messages: [new Reference(ref: '#/channels/test~1events/messages/testEvent')]
            ),
            'receiveTestEvent' => new Operation(
                action: 'receive',
                channel: new Reference(ref: '#/channels/test~1events'),
                title: 'Receive Test Event',
                messages: [new Reference(ref: '#/channels/test~1events/messages/testEvent')]
            )
        ]
    ),
    components: new Components(
        schemas: [
            'TestSchema' => new Schema(
                type: 'object',
                properties: [
                    'name' => new Schema(type: 'string'),
                    'value' => new Schema(type: 'integer')
                ],
                required: ['name']
            )
        ]
    )
)]
class TestAsyncApiSpec
{
}

