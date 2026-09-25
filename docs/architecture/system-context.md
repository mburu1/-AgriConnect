# 🌍 System Context Diagram

C4-style **System Context** view: who and what interacts with AgriConnect, at the highest level of abstraction (no internal components shown).

```mermaid
flowchart TB
    Customer(["👤 Customer<br/>Browses, buys, pays, reviews"])
    Farmer(["👨🏾‍🌾 Farmer / Producer<br/>Manages products, fulfillment"])
    Admin(["🛡️ Admin / Content Manager / Support<br/>Manages platform"])

    System["🌱 AgriConnect Platform<br/>(React + Laravel + MySQL)<br/>Marketplace, farmer management,<br/>orders, payments, content, analytics"]

    MPesa[("💳 M-Pesa Daraja API<br/>STK Push, payment callbacks")]
    WhatsApp[("💬 WhatsApp Business API<br/>Customer / order messaging")]
    SMS[("📱 SMS Gateway<br/>Transactional alerts")]
    Email[("✉️ Email / SMTP Provider<br/>Transactional email")]

    Customer -- "Browses products, checks out,\npays, tracks orders" --> System
    Farmer -- "Manages profile, products,\nviews orders & sales" --> System
    Admin -- "Manages users, content,\nreviews analytics" --> System

    System -- "STK Push request /\npayment callback" --> MPesa
    System -- "Sends / receives\nmessages" --> WhatsApp
    System -- "Sends order & account\nalerts" --> SMS
    System -- "Sends transactional\nemails" --> Email

    MPesa -. "Payment confirmation\n(callback)" .-> System
```

## Actors

| Actor | Role | Interacts via |
|---|---|---|
| **Customer** | Browses, buys, pays via M-Pesa, tracks orders, leaves reviews | React web app |
| **Farmer / Producer** | Registers, manages profile/locations, lists products, views orders & sales analytics | React web app (farmer portal) |
| **Admin / Content Manager / Support Agent** | Manages users, farmers, products, orders, CMS content, careers, forms, and views analytics | React admin dashboard |

## External systems

| System | Purpose | Direction |
|---|---|---|
| **M-Pesa Daraja API** | STK Push payment initiation and asynchronous payment confirmation callbacks | Outbound request / inbound callback |
| **WhatsApp Business API** | Enquiries, order communication, automated notifications | Outbound (and inbound webhook for replies) |
| **SMS Gateway** | Order/registration/payment alerts | Outbound |
| **Email / SMTP** | Transactional email (confirmations, receipts, password reset) | Outbound |

Related: [Architecture Overview](architecture-overview.md) · [Authentication Flow](../diagrams/authentication-flow.md)
