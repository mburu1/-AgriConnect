# 📦 Order Flow

Two complementary views: the **order status lifecycle** (state machine) and the **end-to-end sequence** from browsing to delivery.

## 1. Order status state machine

```mermaid
stateDiagram-v2
    [*] --> PENDING : Order created

    PENDING --> PAYMENT_PENDING : Checkout initiated
    PAYMENT_PENDING --> PAID : M-Pesa payment confirmed
    PAYMENT_PENDING --> PAYMENT_FAILED : Callback reports failure / timeout

    PAYMENT_FAILED --> PAYMENT_PENDING : Customer retries payment
    PAYMENT_FAILED --> CANCELLED : Customer/admin cancels

    PAID --> PROCESSING : Farmer/admin confirms order
    PROCESSING --> READY_FOR_FULFILLMENT : Items picked & packed
    READY_FOR_FULFILLMENT --> DISPATCHED : Handed to delivery/rider
    DISPATCHED --> DELIVERED : Delivery confirmed

    PENDING --> CANCELLED : Customer/admin cancels before payment
    PAID --> REFUNDED : Refund issued
    PROCESSING --> REFUNDED : Refund issued (pre-dispatch)

    DELIVERED --> [*]
    CANCELLED --> [*]
    REFUNDED --> [*]
```

## 2. End-to-end order sequence

```mermaid
sequenceDiagram
    actor Customer
    participant FE as React Frontend
    participant API as Laravel API
    participant DB as MySQL
    participant PAY as Payment Service
    participant MPESA as M-Pesa Daraja
    participant Farmer

    Customer->>FE: Browse products / apply filters
    FE->>API: GET /api/v1/products?filters
    API->>DB: Query products, categories, inventory
    DB-->>API: Product results
    API-->>FE: Product list
    FE-->>Customer: Render product grid

    Customer->>FE: Add to cart
    FE->>API: POST /api/v1/cart
    API->>DB: Upsert cart_items
    DB-->>API: OK
    API-->>FE: Updated cart

    Customer->>FE: Checkout
    FE->>API: POST /api/v1/orders
    API->>DB: Create order (status=PENDING), order_items
    DB-->>API: order_id
    API->>DB: order_status_history (PENDING)
    API-->>FE: order_id, order summary

    Customer->>FE: Confirm payment (enter M-Pesa phone)
    FE->>API: POST /api/v1/payments (order_id, phone)
    API->>DB: Set order.status = PAYMENT_PENDING
    API->>PAY: Initiate STK Push
    PAY->>MPESA: STK Push request
    MPESA-->>Customer: Prompt on phone (enter PIN)
    Customer->>MPESA: Approve payment
    MPESA-->>PAY: Payment callback (async)
    PAY->>API: Forward callback payload
    API->>DB: payment_transactions insert,\norder.status = PAID
    API->>DB: order_status_history (PAID)
    API-->>FE: Payment confirmed (poll / webhook)
    FE-->>Customer: Order confirmed

    API->>Farmer: Notify new order (SMS/Email/WhatsApp)
    Farmer->>API: Update status → PROCESSING
    API->>DB: order_status_history (PROCESSING)
    Farmer->>API: Update status → READY_FOR_FULFILLMENT
    API->>DB: order_status_history (READY_FOR_FULFILLMENT)
    Farmer->>API: Update status → DISPATCHED
    API->>DB: order_status_history (DISPATCHED)
    API->>Customer: Notify dispatched (SMS/Email/WhatsApp)

    Farmer->>API: Confirm delivered
    API->>DB: order.status = DELIVERED,\norder_status_history (DELIVERED)
    API->>Customer: Notify delivered
    Customer->>FE: Submit review
    FE->>API: POST /api/v1/reviews
    API->>DB: Insert review + rating
```

## Exception states

| State | Trigger |
|---|---|
| `PAYMENT_FAILED` | M-Pesa callback reports failure, or STK Push request times out |
| `CANCELLED` | Customer cancels before payment, or admin cancels manually |
| `REFUNDED` | Admin issues a refund on a paid order that must not proceed |

Related: [Payment Flow](payment-flow.md) · [ERD](../database/erd.md) · [Architecture Overview](../architecture/architecture-overview.md)
