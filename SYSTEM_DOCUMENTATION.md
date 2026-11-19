# Safar Fly – Complete System Documentation
**Tour & Expense Management System**  
*Plan Together. Travel Together. Manage Smarter.*  
*A beginner-friendly guide written in simple language*

---

## Contents
1. What Is Safar Fly?
2. The Most Important Idea: Everything Is Flexible
3. User Roles & Permissions
4. Public Website & Mobile Authentication Pages
5. Tour Manager Dashboard & 5-Tab Mobile Navigation
6. Tour Management & 20 Curated Categories
7. Tour Details Page & Control Center
8. Member Management & Traveler Directory
9. Expense Management & Receipt Invoicing
10. Precision Expense Auto-Splitting Engine
11. Expense-wise & Advance Payment Tracking
12. Payment Management: Submission & Verification
13. Dynamic Category-wise Backpack Checklist Engine
14. Tour Finish, Settlement & Fair Refund Engine
15. Reports, Analytics & Data Export (PDF / Excel)
16. SafarFly PRO Subscription System
17. Super Admin Security Terminal & Web Portal
18. Notifications, Profile & In-App Legal Compliance
19. Database Architecture & Complete API Schema
20. The Whole Journey in One Page

---

## 1. What Is Safar Fly?
Safar Fly is a cross-platform mobile application (built with Flutter for Android and iOS) and web administration portal (powered by Laravel 11 and MySQL) designed to organize group travel and transparently manage shared trip finances. Think of it as a smart group ledger that keeps every person's money records in one unified place, completely eliminating the confusion of *“Who paid? Who owes? How much is left in the fund?”* in messy group chats.

**Key Words Used in This Guide:**
- **Tour:** A travel event with its own dates, waypoints, participant roster, expenses, and isolated balance sheet.
- **Tour Manager:** The leader who creates the tour, manages logistics, and verifies payments. *Always a participant too.*
- **Tour Member:** A participant who joins the expedition, takes an equal share of costs, and submits payments.
- **Expense:** A financial cost paid for the group (e.g. resort booking, vehicle hire, boat rent, group dinner).
- **Share:** The exact portion of an expense allocated to one participant (`Amount ÷ Count`).
- **Remaining Due:** The net outstanding balance a participant still owes (`Total Share − Total Paid`).
- **Refund:** Excess money returned to a participant after trip completion when total collections exceed actual expenses.
- **Super Admin:** The platform owner with read-only monitoring oversight via a dedicated, rate-limited security terminal.

---

## 2. The Most Important Idea: Everything Is Flexible
Safar Fly is **never locked to one place, one style of travel, or a static list of bills**. The Tour Manager has complete freedom over every parameter:
- **Any Destination:** Single or multiple consecutive waypoints (e.g. *Dhaka → Sylhet → Ratargul → Jaflong*).
- **Dynamic Metadata:** Title, start location, dates, duration, budget benchmark, itinerary notes.
- **Infinite Custom Categories:** Default presets (Bus Fare, Hotel, Restaurant, Food, Boat Rent, Guide Fee) plus on-the-fly custom categories (Bonfire, BBQ, Jeep, Drone).

---

## 3. User Roles & Permissions
Safar Fly enforces a strict three-tier role-based access control (RBAC) architecture:
1. **Tour Manager (Admin):** Full control over tour planning, participant rosters, expenses, payment verification, and settlements.
   > **Core Architectural Principle:** When a Tour Manager creates a tour, the database automatically enrolls them as **Member #1**. The manager pays their equal share, can have dues, and receives refunds exactly like every other traveler.
2. **Tour Member:** Transparent access to personal shares, payment submission forms, payment history, and refund statuses.
3. **Super Admin:** Strictly read-only monitoring console for centralized audit oversight across all platform tours.

---

## 4. Public Website & Mobile Authentication Pages
- **Splash Screen:** Instant 0ms offline rendering from local SQLite cache; zero-delay brand visibility.
- **Login Screen:** Email/password authentication, server config switcher, discrete link to Super Admin Terminal.
- **Register Screen:** User onboarding with mandatory consent checkbox linking to native Privacy Policy & Terms viewers.
- **Email OTP Verification:** 6-digit numeric verification code dispatched to Gmail with 10-minute expiry.
- **Account Deletion:** In-app and API account deletion fully compliant with Apple Guideline 5.1.1(v) and Google Play rules.

---

## 5. Tour Manager Dashboard & 5-Tab Mobile Navigation
Organized into an ergonomic 5-tab bottom navigation bar (`SafarBottomNavBar`):
- **Tab 1 (Home):** Active Expedition hero card, Quick Actions, KPI summary cards, PRO banner.
- **Tab 2 (Tours):** Directory of all tours with real-time search, status filter chips, and floating creation button.
- **Tab 3 (Payments):** Master payment ledger with Verified/Pending/Rejected filters and verification queue.
- **Tab 4 (Travelers):** Universal traveler directory across all expeditions with Settled/Due chips and direct dialer.
- **Tab 5 (Profile):** User dossier, PRO badge, Legal Center (Privacy & Terms), Change Password, Logout, and Account Deletion.

---

## 6. Tour Management & 20 Curated Categories
Every expedition is tagged with one or multiple categories from Safar Fly's **20 Curated Tour Categories**:
1. Beach & Island Tours | 2. Mountain & Hiking Tours | 3. Adventure & Extreme Sports | 4. Cultural & Heritage | 5. Historical & Archaeological | 6. Wildlife & Safari | 7. Eco & Nature | 8. City & Sightseeing | 9. Religious & Pilgrimage | 10. Road Trips & Camping | 11. Luxury & Resort | 12. Cruise & Boat | 13. Food & Culinary | 14. Photography & Stargazing | 15. Wellness & Yoga | 16. Backpacking & Solo | 17. Festival & Event | 18. Winter & Snow Sports | 19. Desert Safari | 20. Family & Group Vacations.

---

## 7. Tour Details Page & Control Center
Cockpit displaying: Hero Header with waypoints, Financial Progress Bar (Budget vs Actuals), Quick Actions (+ Expense, + Member, Record Payment, Backpack), KPI Cards, Travelers Roster, Category Expense List with photo receipts, Payment Activity Feed, and Finish Tour trigger.

---

## 8. Member Management & Traveler Directory
- **Registered App Users:** Search by name/email/phone; instantly links `user_id` and surfaces tour on member's phone.
- **Offline Guest Travelers:** Created with `user_id = null` for relatives/friends without smartphones; receives equal shares and dues.

---

## 9. Expense Management & Receipt Invoicing
Records title, custom category, amount, date, payer, notes, and cryptographic photo receipt uploads. Receipts stored securely with full-screen zoom preview.

---

## 10. Precision Expense Auto-Splitting Engine
- **Formula:** `Base Share = floor((Total Amount ÷ Member Count) * 100) / 100`
- **1-Paisa Remainder Precision:** Fractional remainders are systematically assigned to Member #1 (Manager) so zero money is lost.

---

## 11. Expense-wise & Advance Payment Tracking
Accommodates direct advance deposits and itemized expense shares. Shows individual status: **Paid**, **Partial**, or **Pending**.

---

## 12. Payment Management: Submission & Verification
- **Submission:** Members select tour, expense, amount, channel (bKash, Nagad, Bank, Cash), TrxID, and screenshot proof.
- **Verification:** Tour Manager audits against wallet statement and marks **Verified** (credits balance) or **Rejected** (with reason).

---

## 13. Dynamic Category-wise Backpack Checklist Engine
Pre-seeds packing essentials based on the tour's chosen 20 categories. Supports interactive checkbox toggles, custom gear entry, progress tracking, and category resetting.

---

## 14. Tour Finish, Settlement & Fair Refund Engine
- **Golden Rule:** Finishing is blocked if `total_outstanding_due > 0`. All dues must be collected first.
- **Formula:** `Refund Pool = Collections − Expenses`; `Individual Refund = Paid − Final Share`.
- Supports Normal Finish and Emergency Finish (with mandatory emergency reason logging).

---

## 15. Reports, Analytics & Data Export
Automated budget variance, category pie charts, member ledgers, payment logs, and final balance sheets exportable to **PDF** and **Excel**.

---

## 16. SafarFly PRO Subscription System
In-app monetization offering Monthly (৳299), Semi-Annual (৳1,499), and Annual (৳2,499) tiers unlocking unlimited tours, unlimited travelers, and verified organizer badges.

---

## 17. Super Admin Security Terminal & Web Portal
Segregated administrative portal at `/admin/login` with `throttle:5,1` rate limiting, post-auth role checking, and read-only monitoring consoles.

---

## 18. Notifications, Profile & In-App Legal Compliance
8 automated system event triggers paired with native dual-tab Privacy Policy & Terms of Service fulfilling Apple and Google store guidelines.

---

## 19. Database Architecture & Complete API Schema
26 normalized MySQL tables with foreign key constraints, 35+ REST endpoints, Sanctum tokens, and 12-round Bcrypt password hashing.

---

## 20. The Whole Journey in One Page
1. Visitor registers and completes email OTP verification.
2. Tour Manager creates expedition with waypoints, dates, budget, and categories.
3. System creates tour and enrolls Manager as Member #1.
4. Manager adds travelers (registered or guests).
5. System seeds category backpack checklists and initializes ledgers.
6. Manager logs daily expenses with photo receipts.
7. System auto-splits costs equally with 1-paisa remainder allocation.
8. Members submit payments via bKash/Nagad/Cash with TrxIDs.
9. Manager verifies proofs and credits balances.
10. Manager collects remaining dues and triggers Finish Tour.
11. System calculates refund pool and individual refunds.
12. Manager disburses refunds, members confirm, and closed balance sheet completes the journey.
