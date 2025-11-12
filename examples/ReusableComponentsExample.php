<?php

namespace App\AsyncApi;

use AsyncApi\Attributes\AsyncApi;
use AsyncApi\Attributes\Info;
use AsyncApi\Attributes\Channel;
use AsyncApi\Attributes\Channels;
use AsyncApi\Attributes\Message;
use AsyncApi\Attributes\Operation;
use AsyncApi\Attributes\Operations;
use AsyncApi\Attributes\Components;
use AsyncApi\Attributes\Schema;
use AsyncApi\Attributes\Reference;
use AsyncApi\Attributes\Server;
use AsyncApi\Attributes\Servers;

/**
 * Reusable Schema Components
 * 
 * Define schemas once and reference them throughout your API specification.
 * This promotes consistency and reduces duplication.
 */
class CommonSchemas
{
    /**
     * Standard timestamp schema
     */
    public const TIMESTAMP = '#/components/schemas/Timestamp';

    /**
     * Standard error response schema
     */
    public const ERROR_RESPONSE = '#/components/schemas/ErrorResponse';

    /**
     * User schema
     */
    public const USER = '#/components/schemas/User';

    /**
     * Order schema
     */
    public const ORDER = '#/components/schemas/Order';

    /**
     * Product schema
     */
    public const PRODUCT = '#/components/schemas/Product';

    /**
     * Address schema
     */
    public const ADDRESS = '#/components/schemas/Address';

    /**
     * Pagination metadata schema
     */
    public const PAGINATION = '#/components/schemas/Pagination';
}

/**
 * E-commerce AsyncAPI Specification with Reusable Components
 * 
 * This example demonstrates:
 * 1. Defining reusable schemas in the components section
 * 2. Referencing those schemas from multiple messages
 * 3. Composing complex schemas from simpler ones using refs
 * 4. Best practices for organizing AsyncAPI specifications
 */
#[AsyncApi(
    asyncapi: '3.0.0',
    id: 'urn:com:example:ecommerce',
    defaultContentType: 'application/json',
    info: new Info(
        title: 'E-Commerce Event API',
        version: '2.0.0',
        description: 'AsyncAPI specification for e-commerce platform events demonstrating reusable components'
    ),
    servers: new Servers(
        servers: [
            'production' => new Server(
                host: 'events.example.com',
                protocol: 'kafka',
                description: 'Production event stream'
            )
        ]
    ),
    channels: new Channels(
        channels: [
            'orders/created' => new Channel(
                address: 'orders.created',
                messages: [
                    'orderCreated' => new Message(
                        name: 'OrderCreated',
                        title: 'Order Created',
                        summary: 'Event published when a new order is created',
                        payload: new Schema(
                            type: 'object',
                            properties: [
                                'event_id' => new Schema(
                                    type: 'string',
                                    format: 'uuid',
                                    description: 'Unique event identifier'
                                ),
                                'event_type' => new Schema(
                                    type: 'string',
                                    const: 'order.created'
                                ),
                                'timestamp' => new Reference(ref: CommonSchemas::TIMESTAMP),
                                'data' => new Schema(
                                    type: 'object',
                                    properties: [
                                        'order' => new Reference(ref: CommonSchemas::ORDER),
                                        'user' => new Reference(ref: CommonSchemas::USER)
                                    ],
                                    required: ['order', 'user']
                                )
                            ],
                            required: ['event_id', 'event_type', 'timestamp', 'data']
                        )
                    )
                ]
            ),
            'orders/updated' => new Channel(
                address: 'orders.updated',
                messages: [
                    'orderUpdated' => new Message(
                        name: 'OrderUpdated',
                        title: 'Order Updated',
                        summary: 'Event published when an order is updated',
                        payload: new Schema(
                            type: 'object',
                            properties: [
                                'event_id' => new Schema(
                                    type: 'string',
                                    format: 'uuid'
                                ),
                                'event_type' => new Schema(
                                    type: 'string',
                                    const: 'order.updated'
                                ),
                                'timestamp' => new Reference(ref: CommonSchemas::TIMESTAMP),
                                'data' => new Schema(
                                    type: 'object',
                                    properties: [
                                        'order' => new Reference(ref: CommonSchemas::ORDER),
                                        'previous_status' => new Schema(
                                            type: 'string',
                                            enum: ['pending', 'processing', 'shipped', 'delivered', 'cancelled']
                                        ),
                                        'new_status' => new Schema(
                                            type: 'string',
                                            enum: ['pending', 'processing', 'shipped', 'delivered', 'cancelled']
                                        )
                                    ],
                                    required: ['order', 'previous_status', 'new_status']
                                )
                            ],
                            required: ['event_id', 'event_type', 'timestamp', 'data']
                        )
                    )
                ]
            ),
            'products/inventory' => new Channel(
                address: 'products.inventory.updated',
                messages: [
                    'inventoryUpdated' => new Message(
                        name: 'InventoryUpdated',
                        title: 'Product Inventory Updated',
                        summary: 'Event published when product inventory changes',
                        payload: new Schema(
                            type: 'object',
                            properties: [
                                'event_id' => new Schema(type: 'string', format: 'uuid'),
                                'event_type' => new Schema(type: 'string', const: 'product.inventory.updated'),
                                'timestamp' => new Reference(ref: CommonSchemas::TIMESTAMP),
                                'data' => new Schema(
                                    type: 'object',
                                    properties: [
                                        'product' => new Reference(ref: CommonSchemas::PRODUCT),
                                        'previous_quantity' => new Schema(type: 'integer', minimum: 0),
                                        'new_quantity' => new Schema(type: 'integer', minimum: 0),
                                        'reason' => new Schema(
                                            type: 'string',
                                            enum: ['sale', 'restock', 'adjustment', 'return']
                                        )
                                    ],
                                    required: ['product', 'previous_quantity', 'new_quantity', 'reason']
                                )
                            ],
                            required: ['event_id', 'event_type', 'timestamp', 'data']
                        )
                    )
                ]
            )
        ]
    ),
    operations: new Operations(
        operations: [
            'publishOrderCreated' => new Operation(
                action: 'send',
                channel: 'orders/created',
                title: 'Publish Order Created Event',
                messages: ['orderCreated']
            ),
            'subscribeOrderCreated' => new Operation(
                action: 'receive',
                channel: 'orders/created',
                title: 'Subscribe to Order Created Events',
                messages: ['orderCreated']
            ),
            'publishOrderUpdated' => new Operation(
                action: 'send',
                channel: 'orders/updated',
                title: 'Publish Order Updated Event',
                messages: ['orderUpdated']
            ),
            'subscribeOrderUpdated' => new Operation(
                action: 'receive',
                channel: 'orders/updated',
                title: 'Subscribe to Order Updated Events',
                messages: ['orderUpdated']
            )
        ]
    ),
    components: new Components(
        schemas: [
            'Timestamp' => new Schema(
                type: 'string',
                format: 'date-time',
                description: 'ISO 8601 timestamp',
                example: '2024-01-15T10:30:00Z'
            ),
            'ErrorResponse' => new Schema(
                type: 'object',
                properties: [
                    'error' => new Schema(
                        type: 'object',
                        properties: [
                            'code' => new Schema(type: 'string', description: 'Error code'),
                            'message' => new Schema(type: 'string', description: 'Error message'),
                            'details' => new Schema(type: 'object', description: 'Additional error details')
                        ],
                        required: ['code', 'message']
                    )
                ],
                required: ['error']
            ),
            'User' => new Schema(
                type: 'object',
                properties: [
                    'id' => new Schema(type: 'integer', description: 'User ID'),
                    'email' => new Schema(type: 'string', format: 'email', description: 'User email'),
                    'name' => new Schema(type: 'string', description: 'User full name'),
                    'created_at' => new Reference(ref: CommonSchemas::TIMESTAMP)
                ],
                required: ['id', 'email', 'name']
            ),
            'Address' => new Schema(
                type: 'object',
                properties: [
                    'street' => new Schema(type: 'string'),
                    'city' => new Schema(type: 'string'),
                    'state' => new Schema(type: 'string'),
                    'postal_code' => new Schema(type: 'string'),
                    'country' => new Schema(type: 'string', minLength: 2, maxLength: 2, description: 'ISO 3166-1 alpha-2 country code')
                ],
                required: ['street', 'city', 'country']
            ),
            'Product' => new Schema(
                type: 'object',
                properties: [
                    'id' => new Schema(type: 'integer', description: 'Product ID'),
                    'sku' => new Schema(type: 'string', description: 'Stock keeping unit'),
                    'name' => new Schema(type: 'string', description: 'Product name'),
                    'price' => new Schema(type: 'number', format: 'decimal', minimum: 0, description: 'Product price'),
                    'currency' => new Schema(type: 'string', minLength: 3, maxLength: 3, description: 'ISO 4217 currency code'),
                    'inventory_count' => new Schema(type: 'integer', minimum: 0, description: 'Available inventory')
                ],
                required: ['id', 'sku', 'name', 'price', 'currency']
            ),
            'Order' => new Schema(
                type: 'object',
                properties: [
                    'id' => new Schema(type: 'integer', description: 'Order ID'),
                    'order_number' => new Schema(type: 'string', description: 'Human-readable order number'),
                    'status' => new Schema(
                        type: 'string',
                        enum: ['pending', 'processing', 'shipped', 'delivered', 'cancelled'],
                        description: 'Order status'
                    ),
                    'items' => new Schema(
                        type: 'array',
                        items: new Schema(
                            type: 'object',
                            properties: [
                                'product' => new Reference(ref: CommonSchemas::PRODUCT),
                                'quantity' => new Schema(type: 'integer', minimum: 1),
                                'unit_price' => new Schema(type: 'number', format: 'decimal', minimum: 0)
                            ],
                            required: ['product', 'quantity', 'unit_price']
                        ),
                        minItems: 1
                    ),
                    'shipping_address' => new Reference(ref: CommonSchemas::ADDRESS),
                    'billing_address' => new Reference(ref: CommonSchemas::ADDRESS),
                    'total_amount' => new Schema(type: 'number', format: 'decimal', minimum: 0),
                    'currency' => new Schema(type: 'string', minLength: 3, maxLength: 3),
                    'created_at' => new Reference(ref: CommonSchemas::TIMESTAMP),
                    'updated_at' => new Reference(ref: CommonSchemas::TIMESTAMP)
                ],
                required: ['id', 'order_number', 'status', 'items', 'total_amount', 'currency']
            ),
            'Pagination' => new Schema(
                type: 'object',
                properties: [
                    'page' => new Schema(type: 'integer', minimum: 1, description: 'Current page number'),
                    'per_page' => new Schema(type: 'integer', minimum: 1, maximum: 100, description: 'Items per page'),
                    'total' => new Schema(type: 'integer', minimum: 0, description: 'Total number of items'),
                    'total_pages' => new Schema(type: 'integer', minimum: 0, description: 'Total number of pages')
                ],
                required: ['page', 'per_page', 'total', 'total_pages']
            )
        ]
    )
)]
class ECommerceAsyncApiSpec
{
    /**
     * This specification demonstrates best practices for using reusable components:
     * 
     * 1. Define common schemas once in the components section
     * 2. Reference them using the Reference class with the schema path
     * 3. Use constants in a separate class for easy reference management
     * 4. Compose complex schemas from simpler ones (e.g., Order references Product and Address)
     * 5. Maintain consistency across all messages by reusing the same schemas
     */
}

