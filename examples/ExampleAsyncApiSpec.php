<?php

namespace App\AsyncApi;

use AsyncApi\Attributes\AsyncApi;
use AsyncApi\Attributes\Channel;
use AsyncApi\Attributes\Channels;
use AsyncApi\Attributes\Components;
use AsyncApi\Attributes\Contact;
use AsyncApi\Attributes\Info;
use AsyncApi\Attributes\License;
use AsyncApi\Attributes\Message;
use AsyncApi\Attributes\Operation;
use AsyncApi\Attributes\Operations;
use AsyncApi\Attributes\Reference;
use AsyncApi\Attributes\Schema;
use AsyncApi\Attributes\Server;
use AsyncApi\Attributes\Servers;

/**
 * Reusable schema components that can be referenced from other classes
 */
class UserSchemas
{
    #[Schema(
        type: 'object',
        properties: [
            'id' => new Schema(
                type: 'integer',
                description: 'The user ID'
            ),
            'name' => new Schema(
                type: 'string',
                description: 'The user full name'
            ),
            'email' => new Schema(
                type: 'string',
                format: 'email',
                description: 'The user email address'
            ),
            'created_at' => new Schema(
                type: 'string',
                format: 'date-time',
                description: 'When the user was created'
            ),
        ],
        required: ['id', 'name', 'email']
    )]
    public const USER_SCHEMA = '#/components/schemas/User';

    #[Schema(
        type: 'object',
        properties: [
            'user_id' => new Schema(
                type: 'integer',
                description: 'The user ID'
            ),
            'message' => new Schema(
                type: 'string',
                description: 'The notification message'
            ),
            'type' => new Schema(
                type: 'string',
                enum: ['email', 'sms', 'push'],
                description: 'The notification type'
            ),
            'timestamp' => new Schema(
                type: 'string',
                format: 'date-time',
                description: 'When the notification was created'
            ),
        ],
        required: ['user_id', 'message', 'type', 'timestamp']
    )]
    public const NOTIFICATION_SCHEMA = '#/components/schemas/Notification';
}

/**
 * Example AsyncAPI 3.0.0 specification using PHP attributes
 *
 * This example demonstrates:
 * - Laravel broadcast event integration
 * - Using refs from another class
 * - AsyncAPI specification for a user notification system using Kafka
 */
#[AsyncApi(
    asyncapi: '3.0.0',
    id: 'urn:com:example:user-notifications',
    defaultContentType: 'application/json',
    info: new Info(
        title: 'User Notification Service',
        version: '1.0.0',
        description: 'Service for handling user notifications via Kafka',
        termsOfService: 'https://example.com/terms',
        contact: new Contact(
            name: 'API Support',
            url: 'https://example.com/support',
            email: 'support@example.com'
        ),
        license: new License(
            name: 'Apache 2.0',
            url: 'https://www.apache.org/licenses/LICENSE-2.0.html'
        )
    ),
    servers: new Servers(
        servers: [
            'development' => new Server(
                host: 'localhost:9092',
                protocol: 'kafka',
                description: 'Development Kafka broker'
            ),
            'production' => new Server(
                host: 'kafka.example.com:9092',
                protocol: 'kafka',
                description: 'Production Kafka cluster',
                security: [
                    ['saslScram' => []],
                ]
            ),
        ]
    ),
    channels: new Channels(
        channels: [
            'user/notifications' => new Channel(
                address: 'user.notifications',
                messages: [
                    'userNotification' => new Message(
                        name: 'UserNotification',
                        title: 'User Notification',
                        summary: 'Notification sent to a user',
                        contentType: 'application/json',
                        // Using a reference to the schema defined in UserSchemas class
                        payload: new Reference(ref: UserSchemas::NOTIFICATION_SCHEMA)
                    ),
                ],
                description: 'Kafka channel for user notifications'
            ),
            'user/registered' => new Channel(
                address: 'user.registered',
                messages: [
                    'userRegistered' => new Message(
                        name: 'UserRegistered',
                        title: 'User Registered Event',
                        summary: 'Event broadcast when a new user registers',
                        contentType: 'application/json',
                        payload: new Schema(
                            type: 'object',
                            properties: [
                                'event' => new Schema(
                                    type: 'string',
                                    description: 'Event name',
                                    example: 'user.registered'
                                ),
                                'data' => new Schema(
                                    type: 'object',
                                    properties: [
                                        'user' => new Reference(ref: UserSchemas::USER_SCHEMA),
                                        'ip_address' => new Schema(
                                            type: 'string',
                                            description: 'IP address of registration'
                                        ),
                                        'user_agent' => new Schema(
                                            type: 'string',
                                            description: 'User agent string'
                                        ),
                                    ],
                                    required: ['user']
                                ),
                                'socket' => new Schema(
                                    type: 'string',
                                    description: 'Laravel broadcast socket ID',
                                    nullable: true
                                ),
                            ],
                            required: ['event', 'data']
                        )
                    ),
                ],
                description: 'Laravel broadcast channel for user registration events'
            ),
            'user/updated' => new Channel(
                address: 'user.{userId}.updated',
                messages: [
                    'userUpdated' => new Message(
                        name: 'UserUpdated',
                        title: 'User Updated Event',
                        summary: 'Event broadcast when a user profile is updated',
                        contentType: 'application/json',
                        payload: new Schema(
                            type: 'object',
                            properties: [
                                'event' => new Schema(
                                    type: 'string',
                                    description: 'Event name',
                                    example: 'user.updated'
                                ),
                                'data' => new Schema(
                                    type: 'object',
                                    properties: [
                                        'user' => new Reference(ref: UserSchemas::USER_SCHEMA),
                                        'changes' => new Schema(
                                            type: 'object',
                                            description: 'Fields that were changed',
                                            additionalProperties: true
                                        ),
                                    ],
                                    required: ['user', 'changes']
                                ),
                            ],
                            required: ['event', 'data']
                        )
                    ),
                ],
                description: 'Laravel broadcast channel for user update events (private channel)'
            ),
        ]
    ),
    operations: new Operations(
        operations: [
            'sendNotification' => new Operation(
                action: 'send',
                channel: 'user/notifications',
                title: 'Send Notification',
                summary: 'Send a notification to a user',
                description: 'Publishes a notification message to the user notifications channel',
                messages: [
                    'userNotification',
                ]
            ),
            'receiveNotification' => new Operation(
                action: 'receive',
                channel: 'user/notifications',
                title: 'Receive Notification',
                summary: 'Receive user notifications',
                description: 'Subscribes to user notification messages',
                messages: [
                    'userNotification',
                ]
            ),
            'broadcastUserRegistered' => new Operation(
                action: 'send',
                channel: 'user/registered',
                title: 'Broadcast User Registered',
                summary: 'Broadcast when a new user registers',
                description: 'Laravel broadcast event sent when a new user completes registration',
                messages: [
                    'userRegistered',
                ]
            ),
            'subscribeUserRegistered' => new Operation(
                action: 'receive',
                channel: 'user/registered',
                title: 'Subscribe to User Registered',
                summary: 'Listen for user registration events',
                description: 'Subscribe to receive notifications when new users register',
                messages: [
                    'userRegistered',
                ]
            ),
            'broadcastUserUpdated' => new Operation(
                action: 'send',
                channel: 'user/updated',
                title: 'Broadcast User Updated',
                summary: 'Broadcast when a user profile is updated',
                description: 'Laravel broadcast event sent when a user updates their profile (private channel)',
                messages: [
                    'userUpdated',
                ]
            ),
            'subscribeUserUpdated' => new Operation(
                action: 'receive',
                channel: 'user/updated',
                title: 'Subscribe to User Updated',
                summary: 'Listen for user update events',
                description: 'Subscribe to receive notifications when a specific user updates their profile',
                messages: [
                    'userUpdated',
                ]
            ),
        ]
    ),
    components: new Components(
        schemas: [
            'User' => new Schema(
                type: 'object',
                properties: [
                    'id' => new Schema(
                        type: 'integer',
                        description: 'The user ID'
                    ),
                    'name' => new Schema(
                        type: 'string',
                        description: 'The user full name'
                    ),
                    'email' => new Schema(
                        type: 'string',
                        format: 'email',
                        description: 'The user email address'
                    ),
                    'created_at' => new Schema(
                        type: 'string',
                        format: 'date-time',
                        description: 'When the user was created'
                    ),
                    'updated_at' => new Schema(
                        type: 'string',
                        format: 'date-time',
                        description: 'When the user was last updated'
                    ),
                ],
                required: ['id', 'name', 'email']
            ),
            'Notification' => new Schema(
                type: 'object',
                properties: [
                    'user_id' => new Schema(
                        type: 'integer',
                        description: 'The user ID'
                    ),
                    'message' => new Schema(
                        type: 'string',
                        description: 'The notification message'
                    ),
                    'type' => new Schema(
                        type: 'string',
                        enum: ['email', 'sms', 'push'],
                        description: 'The notification type'
                    ),
                    'timestamp' => new Schema(
                        type: 'string',
                        format: 'date-time',
                        description: 'When the notification was created'
                    ),
                    'read' => new Schema(
                        type: 'boolean',
                        description: 'Whether the notification has been read',
                        default: false
                    ),
                ],
                required: ['user_id', 'message', 'type', 'timestamp']
            ),
        ],
        securitySchemes: [
            'saslScram' => [
                'type' => 'scramSha256',
                'description' => 'SASL/SCRAM authentication',
            ],
        ]
    )
)]
class UserNotificationSpec
{
    /**
     * This class serves as a container for the AsyncAPI specification.
     * The specification is defined entirely through the AsyncApi attribute above.
     *
     * You can add methods here to provide additional functionality or
     * documentation related to your API specification.
     */
}
