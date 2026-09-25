# 🌱 AgriConnect

### Digital Agribusiness Marketplace & Farmer Management Platform

AgriConnect is a full-stack agribusiness digital platform built to connect farmers, producers, customers and agricultural businesses through a scalable web ecosystem.

The platform combines **e-commerce, farmer management, payments, order fulfillment, agricultural content, location services, notifications, reviews and analytics** into a single system.

The project is designed as a portfolio implementation of a production-style agribusiness platform using the same core technologies and capabilities required for modern agricultural e-commerce platforms.

---

## 📌 Project Overview

Agribusiness platforms often need much more than a traditional online store.

AgriConnect provides a unified platform for:

* Farmers and producers
* Customers and buyers
* Agricultural products
* Product inventory
* Online shopping
* M-Pesa payments
* Orders and fulfillment
* Farmer profiles
* County and location management
* Reviews and ratings
* Agricultural articles and content
* Careers and online forms
* Notifications
* WhatsApp communication
* Reporting and analytics
* REST API integrations

The architecture is designed to support future integrations with external services and third-party systems.

---

# 🎯 Objectives

The primary goals of the project are to demonstrate practical experience in:

* Full-stack web development
* React frontend development
* Laravel backend development
* REST API architecture
* MySQL database design
* E-commerce workflows
* M-Pesa payment integration
* Authentication and authorization
* Multi-user systems
* Farmer/producer management
* Order management
* Search and filtering
* CMS functionality
* Notifications
* Analytics and reporting
* Security
* SEO
* Performance optimization
* Cloud/server deployment
* Scalable application architecture

These areas closely mirror the capabilities requested in the Viazi Kings agribusiness platform specification.

---

# 🏗️ Core Modules

## Customer Module

Customers can:

* Register and authenticate
* Manage their profile
* Browse products
* Search products
* Filter products
* View product details
* Add products to cart
* Checkout
* Pay using M-Pesa
* Track orders
* View order history
* Submit reviews and ratings
* Contact sellers/businesses

---

## 👨🏾‍🌾 Farmer / Producer Module

Farmers can:

* Register as producers
* Create and manage profiles
* Define production locations
* Add agricultural products
* Manage product availability
* Manage pricing
* View customer orders
* Track fulfillment
* Receive notifications
* View sales analytics

The farmer workflow reflects the requirement for farmer registration, profiles, products, locations and agricultural information.

---

# 🛒 E-Commerce Module

The marketplace provides:

* Product listings
* Product categories
* Product images
* Product pricing
* Inventory management
* Shopping cart
* Checkout
* Order creation
* Order processing
* Order status tracking
* Delivery/fulfillment management
* Customer reviews

The JD specifically identifies product listings, categories, inventory, cart, checkout and order management as core platform functionality.

---

# 💳 M-Pesa Integration

AgriConnect includes an M-Pesa payment workflow supporting:

```text
Customer
   ↓
Checkout
   ↓
Laravel API
   ↓
M-Pesa STK Push
   ↓
Customer Phone
   ↓
Payment Confirmation
   ↓
Transaction
   ↓
Order Status Update
```

### Payment capabilities

* STK Push
* Payment request tracking
* Callback handling
* Transaction records
* Payment status
* Failed payment handling
* Successful payment confirmation
* Order/payment reconciliation

M-Pesa STK Push, payment confirmation and transaction management are explicitly included in the platform requirements.

---

# 📍 County & Location Services

The platform supports location-based agricultural data.

Example hierarchy:

```text
Country
└── Kenya
    ├── County
    │   ├── Sub-County
    │   │   ├── Ward
    │   │   └── Farmers
    │   └── Products
    └── Services
```

Location information can be associated with:

* Farmers
* Products
* Orders
* Delivery destinations
* Agricultural services

This follows the JD requirement for county, sub-county and geographical location support.

---

# 🔎 Search & Filtering

Search functionality covers:

* Products
* Categories
* Farmers
* Locations
* Agricultural content

Filtering examples:

```text
Search:
"Potato Seeds"

Filters:
Category
County
Price
Availability
Farmer
Rating
```

The requested platform specifically includes advanced search and filtering across products, farmers, locations and content.

---

# 📦 Order & Fulfillment Management

Order workflow:

```text
PENDING
   ↓
PAYMENT_PENDING
   ↓
PAID
   ↓
PROCESSING
   ↓
READY_FOR_FULFILLMENT
   ↓
DISPATCHED
   ↓
DELIVERED
```

Possible exception states:

```text
PAYMENT_FAILED
CANCELLED
REFUNDED
```

Order management includes:

* Order creation
* Payment association
* Order status
* Fulfillment
* Delivery tracking
* Customer order history
* Administrative management

---

# 📰 CMS & Agricultural Content

The CMS manages dynamic platform content such as:

* News
* Agricultural articles
* Educational content
* Pages
* Banners
* Announcements
* Agricultural information

Example:

```text
/admin/cms/articles
/admin/cms/pages
/admin/cms/banners
/admin/cms/categories
```

This corresponds to the requirement for dynamic news, articles, pages, banners and agricultural information.

---

# ⭐ Reviews & Ratings

Customers can review:

* Products
* Farmers
* Agricultural services

Example:

```text
Product
 ├── Average Rating
 ├── Review Count
 └── Customer Reviews
```

Reviews include:

* Rating
* Comment
* Customer
* Product
* Created date
* Moderation state

---

# 📣 Notifications

The notification architecture supports multiple channels:

```text
                ┌── SMS
                │
Laravel Events ─┼── Email
                │
                ├── WhatsApp
                │
                └── In-App
```

Possible events:

* Registration
* Order created
* Payment received
* Payment failed
* Order dispatched
* Order delivered
* New enquiry
* New farmer registration

The JD specifically lists SMS, email, WhatsApp and in-system notifications.

---

# 💬 WhatsApp Integration

WhatsApp can be used for:

* Customer enquiries
* Order communication
* Product enquiries
* Service requests
* Automated notifications

The architecture keeps messaging providers behind integration interfaces so external providers can be replaced without changing the core business logic.

---

# 📊 Analytics & Reporting

Administrative dashboards provide visibility into:

### Sales

* Revenue
* Orders
* Average order value
* Sales by period

### Customers

* Registered users
* Active users
* Repeat customers

### Products

* Best-selling products
* Inventory
* Categories
* Product performance

### Farmers

* Producer count
* Farmer sales
* Product distribution
* Geographic distribution

### Platform

* Order volume
* Transaction success rate
* User activity
* Platform performance

The JD explicitly calls for dashboards covering sales, users, products, orders and platform performance.

---

# 💼 Careers & Forms

The platform supports:

### Careers

* Job listings
* Job details
* Online applications
* Application management

### Custom Forms

* Contact forms
* Customer enquiries
* Partnership requests
* Farmer registration forms
* Service requests

This reflects the requested careers and custom-form capabilities.

---

# 🔐 Security

Security is treated as a core application concern.

The backend includes:

* Authentication
* Authorization
* Role-based access control
* Input validation
* Request validation
* API protection
* Secure password handling
* Token-based authentication
* Rate limiting
* CORS configuration
* Sensitive-data protection
* Audit logging
* Secure payment callback validation

Example roles:

```text
SUPER_ADMIN
ADMIN
FARMER
CUSTOMER
CONTENT_MANAGER
SUPPORT_AGENT
```

The source requirements explicitly call for secure authentication, data protection, access control, backups and system hardening.

---

# 🚀 Performance & SEO

The frontend and backend are designed around:

* Responsive UI
* Fast API responses
* Database indexing
* Query optimization
* Pagination
* Lazy loading
* Image optimization
* HTTP caching
* API caching
* Code splitting
* SEO-friendly pages
* Semantic HTML
* Metadata optimization
* Core Web Vitals

Performance work is aligned with the requirement for SEO, speed, scalability and Core Web Vitals optimization.

---

# 🧱 Technology Stack

## Frontend

```text
React
JavaScript / TypeScript
HTML5
CSS3
Responsive Web Design
REST API consumption
```

## Backend

```text
PHP
Laravel
REST APIs
API Authentication
Business Logic
Payment Integrations
Third-Party Integrations
```

## Database

```text
MySQL
Relational Database Design
Indexes
Foreign Keys
Transactions
```

## Integrations

```text
M-Pesa
WhatsApp
SMS
Email
Third-Party REST APIs
```

## Infrastructure

```text
Cloud / Server
Linux Server
Web Server
Application Deployment
Database Deployment
HTTPS
Backups
Monitoring
```

## Engineering Concerns

```text
Security
SEO
Performance Optimization
Scalability
API Design
Data Protection
Access Control
```

The proposed stack intentionally follows the technologies and engineering areas named in the JD: Laravel, React, MySQL, REST, APIs, M-Pesa, e-commerce, cloud/server, deployment, security, SEO and performance optimization.

---

# 🏛️ Architecture

The project uses a frontend/backend separation:

```text
┌───────────────────────────────────────────────┐
│                 React Frontend                │
│                                               │
│ Customer UI │ Farmer UI │ Admin Dashboard    │
└──────────────────────┬────────────────────────┘
                       │
                       │ REST API
                       ▼
┌───────────────────────────────────────────────┐
│                 Laravel API                   │
│                                               │
│ Auth │ Products │ Farmers │ Orders │ CMS     │
│ Payments │ Notifications │ Analytics │ Forms │
└──────────────────────┬────────────────────────┘
                       │
                       ▼
┌───────────────────────────────────────────────┐
│                     MySQL                     │
│                                               │
│ Users │ Farmers │ Products │ Orders │ Payments│
│ Content │ Reviews │ Notifications │ Analytics │
└───────────────────────────────────────────────┘
                       │
            ┌──────────┼──────────┐
            ▼          ▼          ▼
          M-Pesa     WhatsApp   Email/SMS
```

---

# 📁 Repository Structure

```text
agriconnect/
│
├── backend/
│   │
│   ├── app/
│   │   ├── Console/
│   │   ├── Exceptions/
│   │   ├── Http/
│   │   │   ├── Controllers/
│   │   │   ├── Middleware/
│   │   │   ├── Requests/
│   │   │   └── Resources/
│   │   ├── Models/
│   │   ├── Services/
│   │   ├── Repositories/
│   │   ├── Policies/
│   │   ├── Events/
│   │   ├── Listeners/
│   │   ├── Jobs/
│   │   └── Notifications/
│   │
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   │   ├── factories/
│   │   ├── migrations/
│   │   └── seeders/
│   │
│   ├── routes/
│   │   ├── api.php
│   │   ├── web.php
│   │   └── console.php
│   │
│   ├── storage/
│   ├── tests/
│   │   ├── Feature/
│   │   └── Unit/
│   │
│   ├── artisan
│   ├── composer.json
│   └── .env.example
│
├── frontend/
│   │
│   └── agric-onnect-web/
│       ├── public/
│       └── src/
│           ├── assets/
│           ├── components/
│           ├── layouts/
│           ├── pages/
│           │   ├── auth/
│           │   ├── customer/
│           │   ├── farmer/
│           │   ├── admin/
│           │   ├── products/
│           │   ├── orders/
│           │   ├── content/
│           │   └── careers/
│           │
│           ├── services/
│           ├── hooks/
│           ├── context/
│           ├── routes/
│           ├── utils/
│           ├── types/
│           └── App.*
│
├── docs/
│   ├── architecture/
│   ├── api/
│   ├── database/
│   ├── security/
│   ├── deployment/
│   └── diagrams/
│
├── tests/
│   └── api/
│
├── deployment/
│   ├── nginx/
│   ├── scripts/
│   └── server/
│
├── .gitignore
├── README.md
└── LICENSE
```

---

# 🗄️ Core Database Model

Initial MySQL entities:

```text
users
roles
permissions

farmers
farmer_locations

counties
sub_counties
wards

products
product_categories
product_images
inventories

carts
cart_items

orders
order_items
order_status_history

payments
payment_transactions

reviews
ratings

articles
pages
banners

notifications

whatsapp_messages

job_posts
job_applications

contact_forms
enquiries

audit_logs
```

Relationship example:

```text
User
 ├── Farmer
 │    ├── FarmerLocation
 │    └── Products
 │
 └── Orders
      ├── OrderItems
      └── Payments

Product
 ├── Category
 ├── Inventory
 └── Reviews
```

---

# 🔌 REST API

Example API structure:

```text
/api/v1/auth
/api/v1/users
/api/v1/farmers
/api/v1/counties
/api/v1/products
/api/v1/categories
/api/v1/cart
/api/v1/orders
/api/v1/payments
/api/v1/reviews
/api/v1/articles
/api/v1/pages
/api/v1/banners
/api/v1/notifications
/api/v1/careers
/api/v1/forms
/api/v1/analytics
```

Example:

```http
GET    /api/v1/products
GET    /api/v1/products/{id}
POST   /api/v1/products
PUT    /api/v1/products/{id}
DELETE /api/v1/products/{id}
```

---

# 🔄 Example Order Flow

```text
Customer
   │
   ▼
Browse Products
   │
   ▼
Add To Cart
   │
   ▼
Checkout
   │
   ▼
Create Order
   │
   ▼
Initiate M-Pesa STK Push
   │
   ▼
Payment Confirmation
   │
   ▼
Update Payment
   │
   ▼
Confirm Order
   │
   ▼
Farmer / Fulfillment
   │
   ▼
Dispatch
   │
   ▼
Delivery
```

---

# 🧪 Testing Strategy

Testing covers:

### Backend

* Unit tests
* Feature tests
* API tests
* Authentication tests
* Authorization tests
* Payment tests
* Order workflow tests

### Frontend

* Component tests
* Form validation tests
* API integration tests
* User-flow tests

### Critical scenarios

```text
Registration
Authentication
Product creation
Product search
Cart
Checkout
M-Pesa payment
Order creation
Order tracking
Review submission
Admin authorization
```

---

# 🔑 Authentication & Authorization

Authentication endpoints:

```text
POST /api/v1/auth/register
POST /api/v1/auth/login
POST /api/v1/auth/logout
GET  /api/v1/auth/me
```

Authorization is role based:

```text
Admin
  ├── Manage users
  ├── Manage farmers
  ├── Manage products
  ├── Manage orders
  ├── Manage CMS
  └── View analytics

Farmer
  ├── Manage profile
  ├── Manage products
  └── View orders

Customer
  ├── Browse products
  ├── Checkout
  ├── Pay
  ├── Track orders
  └── Review products
```

---

# 📈 Scalability Strategy

The platform is designed so additional services can be introduced as usage grows.

Potential future services:

```text
Authentication Service
Product Service
Order Service
Payment Service
Notification Service
Content Service
Analytics Service
Search Service
```

Third-party integrations are isolated behind integration services/interfaces to support future external systems.

This aligns with the requirement that the platform architecture support future third-party integrations and significant growth in users, transactions, products and geographical coverage.

---

# ☁️ Deployment

Target deployment architecture:

```text
Internet
   │
   ▼
HTTPS / Domain
   │
   ▼
Web Server
   │
   ├── React Frontend
   │
   └── Laravel API
          │
          ▼
        MySQL
```

Deployment concerns:

* Production environment configuration
* HTTPS
* Database migrations
* Database backups
* Application logs
* Error handling
* Security hardening
* Performance monitoring
* Server configuration
* Environment variables

---

# 🔐 Environment Configuration

Sensitive values must not be committed to Git.

Example:

```env
APP_ENV=production
APP_KEY=
APP_URL=

DB_CONNECTION=mysql
DB_HOST=
DB_PORT=3306
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=

MPESA_CONSUMER_KEY=
MPESA_CONSUMER_SECRET=
MPESA_SHORTCODE=
MPESA_PASSKEY=
MPESA_CALLBACK_URL=

MAIL_MAILER=
MAIL_HOST=
MAIL_USERNAME=
MAIL_PASSWORD=

WHATSAPP_API_URL=
WHATSAPP_API_TOKEN=
```

`.env` remains outside source control.

---

# 📚 Documentation

```text
docs/
├── architecture/
│   ├── architecture-overview.md
│   └── system-context.md
│
├── api/
│   └── api-reference.md
│
├── database/
│   ├── erd.md
│   └── schema.md
│
├── security/
│   └── security-model.md
│
├── deployment/
│   └── deployment-guide.md
│
└── diagrams/
    ├── order-flow.md
    ├── payment-flow.md
    └── authentication-flow.md
```

---

# 🛠️ Development Setup

## Backend

```bash
cd backend

composer install

cp .env.example .env

php artisan key:generate

php artisan migrate

php artisan db:seed

php artisan serve
```

---

## Frontend

```bash
cd frontend/agric-onnect-web

npm install

npm run dev
```

---

# 📊 Portfolio Demonstration

The project is intended to demonstrate production-oriented full-stack engineering rather than simply CRUD functionality.

### Backend Engineering

* Laravel architecture
* REST API development
* Business logic separation
* Authentication
* Authorization
* Validation
* Database transactions
* External API integrations
* Payment integration
* Notification workflows

### Frontend Engineering

* React
* Component architecture
* Responsive interfaces
* State management
* API integration
* Forms
* Authentication flows
* Dashboards
* Search and filtering
* E-commerce UI

### Database Engineering

* MySQL
* Relational modeling
* Foreign keys
* Indexes
* Transactions
* Data integrity

### Production Engineering

* Security
* SEO
* Performance
* Deployment
* Backups
* Logging
* Scalability

---

# 🗺️ Development Roadmap

## Phase 1 — Foundation

* Repository setup
* Laravel project
* React project
* MySQL schema
* Authentication
* User roles

## Phase 2 — Marketplace

* Products
* Categories
* Inventory
* Cart
* Checkout

## Phase 3 — Payments

* M-Pesa integration
* STK Push
* Callback processing
* Payment transaction records

## Phase 4 — Farmer Platform

* Farmer registration
* Farmer profiles
* Locations
* Farmer products
* Farmer dashboard

## Phase 5 — Orders

* Order management
* Fulfillment
* Delivery tracking
* Notifications

## Phase 6 — Content

* CMS
* Articles
* Pages
* Banners
* Careers
* Forms

## Phase 7 — Analytics

* Admin dashboard
* Sales analytics
* Product analytics
* User analytics
* Farmer analytics

## Phase 8 — Production

* Security hardening
* SEO
* Performance optimization
* Deployment
* Monitoring
* Backups

---

# 🎯 Why This Project Matters

AgriConnect intentionally demonstrates the combination of technologies and business capabilities expected from a serious agribusiness digital platform:

```text
React
   +
Laravel
   +
MySQL
   +
REST APIs
   +
M-Pesa
   +
E-Commerce
   +
Farmer Management
   +
Analytics
   +
Security
   +
SEO
   +
Performance
   +
Deployment
```

Rather than presenting a generic CRUD application, the project demonstrates a **real-world multi-user agribusiness ecosystem** with transactional workflows and external integrations.

---

# 👨🏾‍💻 Author

**Mwangi Wa Mburu**

Full-Stack Software Developer

Core interests:

```text
React
Laravel / PHP
C# / .NET
REST APIs
SQL / NoSQL
System Architecture
Cloud & Deployment
API Integrations
```

---

# 📄 License

This project is intended for educational, portfolio and demonstration purposes.
