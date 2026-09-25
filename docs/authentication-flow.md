# 🔑 Authentication & Authorization Flow

Token-based authentication and role-based authorization, matching the [README — Authentication & Authorization](../../README.md#-authentication--authorization) endpoints.

## 1. Registration & login sequence

```mermaid
sequenceDiagram
    actor User
    participant FE as React Frontend
    participant API as Laravel API
    participant DB as MySQL

    User->>FE: Fill registration form (name, email, phone, password, role)
    FE->>API: POST /api/v1/auth/register
    API->>API: Validate request (FormRequest)
    API->>DB: Hash password, insert users row (role_id)
    DB-->>API: user created
    API-->>FE: 201 Created (user profile)
    API->>User: Send welcome notification (Email/SMS)

    User->>FE: Login (email/phone + password)
    FE->>API: POST /api/v1/auth/login
    API->>DB: Lookup user by email/phone
    DB-->>API: user row (hashed password, role)
    API->>API: Verify password hash

    alt Credentials valid
        API->>API: Issue signed access token (JWT / Sanctum)
        API-->>FE: 200 OK {token, user, role}
        FE->>FE: Store token (memory/httpOnly cookie)
        FE-->>User: Redirect to role-based dashboard
    else Credentials invalid
        API-->>FE: 401 Unauthorized
        FE-->>User: Show error
    end

    User->>FE: Access protected page
    FE->>API: GET /api/v1/auth/me (Authorization: Bearer token)
    API->>API: Verify token signature & expiry (auth middleware)
    API->>DB: Load current user + role/permissions
    API-->>FE: 200 OK {user, role, permissions}

    User->>FE: Logout
    FE->>API: POST /api/v1/auth/logout
    API->>API: Revoke/blacklist token
    API-->>FE: 200 OK
    FE->>FE: Clear stored token
```

## 2. Request authorization (role-based access control)

```mermaid
sequenceDiagram
    actor User
    participant FE as React Frontend
    participant MW as Auth Middleware
    participant POL as Policy / Gate
    participant CTRL as Controller
    participant DB as MySQL

    User->>FE: Trigger action (e.g. update product)
    FE->>MW: PUT /api/v1/products/{id} (Bearer token)
    MW->>MW: Validate token signature & expiry

    alt Token invalid/expired
        MW-->>FE: 401 Unauthorized
    else Token valid
        MW->>POL: Authorize(user, action, resource)
        POL->>DB: Load resource ownership (e.g. product.farmer_id == user.farmer.id)
        DB-->>POL: Resource data

        alt Not authorized
            POL-->>FE: 403 Forbidden
        else Authorized
            POL->>CTRL: Proceed
            CTRL->>DB: Execute business logic
            DB-->>CTRL: Result
            CTRL-->>FE: 200 OK
        end
    end
```

## 3. Role → permission matrix

```mermaid
flowchart LR
    subgraph Roles
        SA[SUPER_ADMIN]
        AD[ADMIN]
        CM[CONTENT_MANAGER]
        SUP[SUPPORT_AGENT]
        FM[FARMER]
        CU[CUSTOMER]
    end

    SA --> Users[Manage Users]
    SA --> Farmers[Manage Farmers]
    SA --> Products[Manage Products]
    SA --> Orders[Manage Orders]
    SA --> CMS[Manage CMS]
    SA --> Analytics[View Analytics]

    AD --> Users
    AD --> Farmers
    AD --> Products
    AD --> Orders
    AD --> CMS
    AD --> Analytics

    CM --> CMS

    SUP --> Enquiries[Handle Enquiries / Forms]
    SUP --> OrdersView[View Orders]

    FM --> ProfileF[Manage Own Profile]
    FM --> ProductsF[Manage Own Products]
    FM --> OrdersF[View Own Orders]

    CU --> Browse[Browse Products]
    CU --> Checkout[Checkout & Pay]
    CU --> Track[Track Orders]
    CU --> Review[Review Products]
```

## Endpoints

```text
POST /api/v1/auth/register
POST /api/v1/auth/login
POST /api/v1/auth/logout
GET  /api/v1/auth/me
```

## Security notes

- Passwords are hashed (bcrypt/Argon2) — never stored or logged in plaintext.
- Access tokens are short-lived; refresh/re-authentication is required on expiry.
- Every mutating endpoint passes through both **authentication** (who is this?) and **authorization** (are they allowed to do this, on this specific resource?) — see the Policy/Gate step above.
- Sensitive actions (role changes, payment status overrides) are written to `audit_logs`.

Related: [System Context](../architecture/system-context.md) · [ERD](../database/erd.md) · [Order Flow](order-flow.md)
