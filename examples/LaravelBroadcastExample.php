<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use AsyncApi\Attributes\Message;
use AsyncApi\Attributes\Schema;
use AsyncApi\Attributes\Reference;

/**
 * Example Laravel Broadcast Event with AsyncAPI annotations
 * 
 * This demonstrates how to annotate a Laravel broadcast event
 * with AsyncAPI attributes to document your real-time API.
 */
#[Message(
    name: 'UserRegistered',
    title: 'User Registered Event',
    summary: 'Broadcast when a new user registers',
    description: 'This event is broadcast to the public channel when a new user completes registration',
    contentType: 'application/json',
    payload: new Schema(
        type: 'object',
        properties: [
            'event' => new Schema(
                type: 'string',
                description: 'The event name',
                example: 'user.registered'
            ),
            'data' => new Schema(
                type: 'object',
                properties: [
                    'user' => new Schema(
                        type: 'object',
                        properties: [
                            'id' => new Schema(type: 'integer', description: 'User ID'),
                            'name' => new Schema(type: 'string', description: 'User name'),
                            'email' => new Schema(type: 'string', format: 'email', description: 'User email'),
                            'created_at' => new Schema(type: 'string', format: 'date-time')
                        ],
                        required: ['id', 'name', 'email']
                    ),
                    'ip_address' => new Schema(
                        type: 'string',
                        description: 'IP address from which the user registered'
                    )
                ],
                required: ['user']
            ),
            'socket' => new Schema(
                type: 'string',
                description: 'Socket ID to exclude from broadcast',
                nullable: true
            )
        ],
        required: ['event', 'data']
    )
)]
class UserRegistered implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public User $user,
        public string $ipAddress
    ) {
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('user.registered'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'user.registered';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
                'created_at' => $this->user->created_at->toISOString(),
            ],
            'ip_address' => $this->ipAddress,
        ];
    }
}

/**
 * Example Private Channel Broadcast Event
 * 
 * This demonstrates a private channel broadcast with user-specific data
 */
#[Message(
    name: 'UserProfileUpdated',
    title: 'User Profile Updated Event',
    summary: 'Broadcast when a user updates their profile',
    description: 'This event is broadcast to a private user channel when the user updates their profile',
    contentType: 'application/json',
    payload: new Schema(
        type: 'object',
        properties: [
            'event' => new Schema(
                type: 'string',
                description: 'The event name',
                example: 'user.profile.updated'
            ),
            'data' => new Schema(
                type: 'object',
                properties: [
                    'user_id' => new Schema(
                        type: 'integer',
                        description: 'The ID of the user who updated their profile'
                    ),
                    'changes' => new Schema(
                        type: 'object',
                        description: 'The fields that were changed',
                        additionalProperties: new Schema(
                            type: 'object',
                            properties: [
                                'old' => new Schema(description: 'Previous value'),
                                'new' => new Schema(description: 'New value')
                            ]
                        )
                    ),
                    'updated_at' => new Schema(
                        type: 'string',
                        format: 'date-time',
                        description: 'When the update occurred'
                    )
                ],
                required: ['user_id', 'changes', 'updated_at']
            )
        ],
        required: ['event', 'data']
    )
)]
class UserProfileUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public User $user,
        public array $changes
    ) {
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->user->id),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'user.profile.updated';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'user_id' => $this->user->id,
            'changes' => $this->changes,
            'updated_at' => now()->toISOString(),
        ];
    }
}

/**
 * Example Presence Channel Broadcast Event
 * 
 * This demonstrates a presence channel for real-time collaboration
 */
#[Message(
    name: 'UserTyping',
    title: 'User Typing Event',
    summary: 'Broadcast when a user is typing in a chat',
    description: 'This event is broadcast to a presence channel to show typing indicators',
    contentType: 'application/json',
    payload: new Schema(
        type: 'object',
        properties: [
            'event' => new Schema(
                type: 'string',
                description: 'The event name',
                example: 'user.typing'
            ),
            'data' => new Schema(
                type: 'object',
                properties: [
                    'user_id' => new Schema(
                        type: 'integer',
                        description: 'The ID of the user who is typing'
                    ),
                    'user_name' => new Schema(
                        type: 'string',
                        description: 'The name of the user who is typing'
                    ),
                    'room_id' => new Schema(
                        type: 'string',
                        description: 'The chat room ID'
                    ),
                    'is_typing' => new Schema(
                        type: 'boolean',
                        description: 'Whether the user is currently typing'
                    )
                ],
                required: ['user_id', 'user_name', 'room_id', 'is_typing']
            )
        ],
        required: ['event', 'data']
    )
)]
class UserTyping implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public User $user,
        public string $roomId,
        public bool $isTyping = true
    ) {
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('chat.' . $this->roomId),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'user.typing';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'user_id' => $this->user->id,
            'user_name' => $this->user->name,
            'room_id' => $this->roomId,
            'is_typing' => $this->isTyping,
        ];
    }
}

