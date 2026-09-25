# 💳 M-Pesa Payment Flow (STK Push)

Detailed sequence for the M-Pesa payment workflow referenced in the [README — M-Pesa Integration](../../README.md#-m-pesa-integration), including callback handling and reconciliation.

## 1. Happy path — STK Push, callback, reconciliation

```mermaid
sequenceDiagram
    actor Customer
    participant FE as React Frontend
    participant API as Laravel API
    participant SVC as Payment Service
    participant DB as MySQL
    participant DARAJA as M-Pesa Daraja API
    participant PHONE as Customer Phone (M-Pesa app)

    Customer->>FE: Confirm checkout, enter M-Pesa phone number
    FE->>API: POST /api/v1/payments {order_id, phone}
    API->>DB: payments row (status=PENDING)
    API->>DB: orders.status = PAYMENT_PENDING
    API->>SVC: initiateStkPush(order, phone)
    SVC->>DARAJA: OAuth token request
    DARAJA-->>SVC: access_token
    SVC->>DARAJA: STK Push request (amount, phone, account_ref, callback_url)
    DARAJA-->>SVC: CheckoutRequestID (ack)
    SVC->>DB: payment_transactions.checkout_request_id = CheckoutRequestID
    SVC-->>API: Push accepted
    API-->>FE: "Enter M-Pesa PIN on your phone"

    DARAJA->>PHONE: STK prompt (amount, till/paybill)
    Customer->>PHONE: Enter M-Pesa PIN, approve

    Note over DARAJA,API: Asynchronous callback — independent of the original request/response
    DARAJA->>API: POST MPESA_CALLBACK_URL {ResultCode, MpesaReceiptNumber, ...}
    API->>API: Verify callback authenticity / expected CheckoutRequestID
    API->>DB: payment_transactions.raw_callback_payload = payload,\nmpesa_receipt_number = receipt

    alt ResultCode == 0 (success)
        API->>DB: payments.status = SUCCESS
        API->>DB: orders.status = PAID
        API->>DB: order_status_history (PAID)
        API->>Customer: Notify payment success (SMS/Email/WhatsApp)
    else ResultCode != 0 (failure / cancelled)
        API->>DB: payments.status = FAILED
        API->>DB: orders.status = PAYMENT_FAILED
        API->>Customer: Notify payment failed, offer retry
    end

    FE->>API: GET /api/v1/orders/{id} (poll status)
    API-->>FE: Current order + payment status
    FE-->>Customer: Show confirmation or retry option
```

## 2. Timeout / no-callback handling

```mermaid
sequenceDiagram
    participant API as Laravel API
    participant Job as Scheduled Job (Laravel Queue)
    participant DB as MySQL
    participant DARAJA as M-Pesa Daraja API

    Note over API,DB: STK Push sent, no callback received within timeout window
    Job->>DB: Find payments.status = PENDING older than N minutes
    Job->>DARAJA: STK Push Query (CheckoutRequestID)
    DARAJA-->>Job: Result (success / failed / still pending)

    alt Confirmed success (missed webhook)
        Job->>DB: payments.status = SUCCESS, orders.status = PAID
    else Confirmed failure or still unresolved after max retries
        Job->>DB: payments.status = FAILED, orders.status = PAYMENT_FAILED
    end
```

## Key safeguards

| Concern | Mitigation |
|---|---|
| **Callback authenticity** | Validate the callback originates from a trusted M-Pesa IP/URL and matches a known `CheckoutRequestID` before mutating order state |
| **Idempotency** | A callback for an already-`SUCCESS` payment is a no-op — never double-credit an order |
| **Reconciliation** | Raw callback payload is persisted (`payment_transactions.raw_callback_payload`) for audit and manual reconciliation |
| **Lost/late callbacks** | Scheduled job (STK Push Query API) resolves payments stuck in `PENDING` past a timeout |
| **Order/payment consistency** | `orders.status` is only advanced to `PAID` inside the same DB transaction as the `payments` update |

Related: [Order Flow](order-flow.md) · [ERD](../database/erd.md) · [Authentication Flow](authentication-flow.md)
