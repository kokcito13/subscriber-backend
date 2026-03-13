# Subscription Tracker

## Overview

Subscription Tracker is an iOS application designed to help users manage and track their recurring subscriptions in one place.

The app allows users to record, monitor, and analyze all their subscriptions such as streaming services, SaaS tools, mobile apps, and other recurring payments. The goal is to give users better visibility into their recurring expenses and prevent unwanted or forgotten charges.

The application is built using **SwiftUI** and follows modern iOS development practices. It is designed with a clean architecture that allows AI tools and developers to easily understand and extend the project.

---

# Goals of the Project

The primary goals of this application are:

- Help users **track all subscriptions in one place**
- Provide **clear visibility of monthly and yearly spending**
- Send reminders before subscription renewals
- Help users identify subscriptions they no longer use
- Reduce unnecessary recurring expenses

---

# Target Users

The application is designed for:

- Individuals who have multiple digital subscriptions
- Users who want better control over recurring expenses
- People who forget about active subscriptions
- Users who want a simple personal finance helper tool

---

# Core Features

### Subscription Management
Users can add and manage subscriptions including:

- Service name
- Subscription price
- Billing period (monthly / yearly)
- Next billing date
- Category (streaming, productivity, fitness, etc.)

### Subscription Overview
Users can quickly see:

- Total monthly spending
- Total yearly spending
- Upcoming renewals

### Renewal Tracking
The app helps users track when subscriptions will renew and how much they will be charged.

### Subscription History (future feature)
Users may view historical payments and trends over time.

---

# Technical Stack

The project is built using modern iOS technologies:

| Technology | Purpose |
|--------|--------|
| Swift | Main programming language |
| SwiftUI | User interface framework |
| Xcode | Development environment |
| StoreKit 2 | In-app subscriptions (future premium features) |

---

# Architecture

The project follows a **SwiftUI + MVVM architecture**.

Typical structure:
