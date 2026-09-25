# 🏛️ Architecture Overview

High-level layered architecture of AgriConnect: React frontend → Laravel REST API → MySQL, with third-party integrations isolated behind service interfaces.

```mermaid
flowchart TB
    subgraph FE["🖥️ React Frontend (agric-onnect-web)"]
        direction LR
        FE1[Customer UI]
        FE2[Farmer UI]
        FE3[Admin Dashboard]
    end

    subgraph API["⚙️ Laravel REST API"]
        direction LR
        A1[Auth]
        A2[Products]
        A3[Farmers]
        A4[Orders]
        A5[CMS]
        A6[Payments]
        A7[Notifications]
        A8[Analytics]
        A9[Forms / Careers]
    end

    subgraph SVC["🧩 Service / Integration Layer"]
        direction LR
        S1[Payment Service]
        S2[Messaging Service]
        S3[Notification Service]
    end

    subgraph DB["🗄️ MySQL"]
        direction LR
        D1[(Users / Roles)]
        D2[(Farmers / Locations)]
        D3[(Products / Inventory)]
        D4[(Orders / Payments)]
        D5[(Content / Reviews)]
        D6[(Notifications / Audit)]
    end

    subgraph EXT["🌐 External Providers"]
        direction LR
        E1[M-Pesa Daraja API]
        E2[WhatsApp Business API]
        E3[SMS Gateway]
        E4[Email / SMTP]
    end

    FE -- REST API (HTTPS / JSON) --> API
    API --> SVC
    API --> DB
    SVC --> EXT

    A6 --> S1
    A7 --> S2
    A7 --> S3
    S1 --> E1
    S2 --> E2
    S3 --> E3
    S3 --> E4
```

## Layer responsibilities

| Layer | Responsibility |
|---|---|
| **React Frontend** | Presentation, routing, state management, form validation, calling the REST API |
| **Laravel API** | Authentication/authorization, business logic, validation, request/response shaping |
| **Service / Integration Layer** | Encapsulates external providers (M-Pesa, WhatsApp, SMS, Email) behind stable interfaces so providers can be swapped without touching business logic |
| **MySQL** | Relational persistence, referential integrity, transactions |
| **External Providers** | M-Pesa Daraja (payments), WhatsApp Business API, SMS gateway, SMTP/Email |

## Design principles

- **Separation of concerns** — frontend and backend are independently deployable.
- **Interface-driven integrations** — every third-party dependency (payments, messaging) sits behind an interface/service contract (see `Services/` in the backend structure), enabling future migration to a microservice per domain (see [Scalability Strategy](../../README.md#-scalability-strategy)).
- **Stateless API** — token-based authentication (JWT/Sanctum) allows horizontal scaling of the API tier.
- **Single database today, service-ready tomorrow** — the schema is modular enough that Products, Orders, Payments, and Notifications could each be extracted into their own service and database in a future phase.

Related: [System Context](system-context.md) · [ERD](../database/erd.md) · [Order Flow](../diagrams/order-flow.md) · [Payment Flow](../diagrams/payment-flow.md) · [Authentication Flow](../diagrams/authentication-flow.md)
