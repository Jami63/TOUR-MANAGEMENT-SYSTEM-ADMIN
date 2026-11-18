# Issue #3 Pre-Launch Hard Test Report: Broken Object-Level Authorization (IDOR) Fix

**Date:** November 18, 2026  
**Issue Reference:** P0-3 (IDOR / Broken Object-Level Authorization across Tour Read & Write APIs)  
**Status:** **FULLY RESOLVED & HARDENED (100% PASS)**

---

## 1. Summary of Changes

To eliminate broken object-level authorization vulnerabilities, the following security guards were implemented:
1. **Model Helpers (`Tour.php`)**: Added `canAccess(User $user)` (verifies creator, participant member, or super admin) and `canManage(User $user)` (verifies creator or super admin).
2. **Expense Management (`ExpenseController.php`)**:
   - `index`: Locked with `$tour->canAccess()`. Non-members receive `403 Forbidden`.
   - `store`: Locked with `$tour->canManage()`. Unrelated managers and participants receive `403 Forbidden`.
3. **Tour Details & Settlements (`TourController.php`)**:
   - `show`: Locked with `$tour->canAccess()`.
   - `settlementPreview`: Locked with `$tour->canAccess()`.
4. **Payments Inspection (`PaymentController.php`)**:
   - `getTourPayments`: Locked with `$tour->canAccess()`.
   - `show`: Restricted strictly to payment owner, tour manager, or super admin. Removed flaw allowing arbitrary managers access.
   - `recordManualPayment`, `recordTourPayment`, `updateTourPayment`, `deleteTourPayment`: Strictly guarded by `$tour->canManage()`.
5. **Participant Roster (`TourMemberController.php`)**:
   - `index`: Locked with `$tour->canAccess()`.
   - `store` & `destroy`: Replaced flawed logic (`role !== 'manager'`) with strict `$tour->canManage()`. Prevents Manager B from tampering with Manager A's roster.
6. **Backpack Checklist (`TourBackpackController.php`)**:
   - All 5 endpoints (`index`, `store`, `toggle`, `destroy`, `resetCategory`) guarded by `$tour->canAccess()`. Non-participants receive `403 Forbidden`.

---

## 2. Hard Test Results (Input, Expected vs Actual Output)

| # | Test Scenario | Test Input | Expected Output | Actual Output | Status |
| :---: | :--- | :--- | :--- | :--- | :---: |
| **1** | **Manager B reads Manager A's tour details** | `GET /api/tours/{tourA_id}`<br>Bearer token: `Manager B` (unrelated) | `HTTP 403 Forbidden`<br>`{"success":false,"message":"Unauthorized access to tour details"}` | `HTTP 403 Forbidden`<br>`{"success":false,"message":"Unauthorized access to tour details"}` | **PASSED** ✅ |
| **2** | **Outsider reads Tour A details** | `GET /api/tours/{tourA_id}`<br>Bearer token: `Outsider Dave` (no relation) | `HTTP 403 Forbidden`<br>`{"success":false,"message":"Unauthorized access to tour details"}` | `HTTP 403 Forbidden`<br>`{"success":false,"message":"Unauthorized access to tour details"}` | **PASSED** ✅ |
| **3** | **Tour Owner reads own tour details** | `GET /api/tours/{tourA_id}`<br>Bearer token: `Manager A` (creator) | `HTTP 200 OK`<br>`{"success":true,"tour":{...}}` | `HTTP 200 OK`<br>`{"success":true,"tour":{...}}` | **PASSED** ✅ |
| **4** | **Tour Member reads tour details** | `GET /api/tours/{tourA_id}`<br>Bearer token: `Participant Charlie` (joined member) | `HTTP 200 OK`<br>`{"success":true,"tour":{...}}` | `HTTP 200 OK`<br>`{"success":true,"tour":{...}}` | **PASSED** ✅ |
| **5** | **Manager B reads Tour A expenses** | `GET /api/tours/{tourA_id}/expenses`<br>Bearer token: `Manager B` | `HTTP 403 Forbidden`<br>`{"success":false,"message":"Unauthorized access to tour expenses"}` | `HTTP 403 Forbidden`<br>`{"success":false,"message":"Unauthorized access to tour expenses"}` | **PASSED** ✅ |
| **6** | **Outsider reads Tour A expenses** | `GET /api/tours/{tourA_id}/expenses`<br>Bearer token: `Outsider Dave` | `HTTP 403 Forbidden`<br>`{"success":false,"message":"Unauthorized access to tour expenses"}` | `HTTP 403 Forbidden`<br>`{"success":false,"message":"Unauthorized access to tour expenses"}` | **PASSED** ✅ |
| **7** | **Manager B adds expense to Manager A's tour** | `POST /api/tours/{tourA_id}/expenses`<br>Bearer token: `Manager B`<br>`{"expense_name":"Illicit Hotel","total_amount":5000}` | `HTTP 403 Forbidden`<br>`{"success":false,"message":"Only the Tour Manager can add expenses to this tour"}` | `HTTP 403 Forbidden`<br>`{"success":false,"message":"Only the Tour Manager can add expenses to this tour"}` | **PASSED** ✅ |
| **8** | **Participant adds expense to tour** | `POST /api/tours/{tourA_id}/expenses`<br>Bearer token: `Participant Charlie`<br>`{"expense_name":"Fuel","total_amount":1200}` | `HTTP 403 Forbidden`<br>`{"success":false,"message":"Only the Tour Manager can add expenses to this tour"}` | `HTTP 403 Forbidden`<br>`{"success":false,"message":"Only the Tour Manager can add expenses to this tour"}` | **PASSED** ✅ |
| **9** | **Tour Manager A adds expense to own tour** | `POST /api/tours/{tourA_id}/expenses`<br>Bearer token: `Manager A`<br>`{"expense_name":"Resort Booking","total_amount":10000}` | `HTTP 201 Created`<br>`{"success":true,"message":"Expense added and split successfully"}` | `HTTP 201 Created`<br>`{"success":true,"message":"Expense added and split successfully"}` | **PASSED** ✅ |
| **10** | **Outsider views settlement preview** | `GET /api/tours/{tourA_id}/settlement-preview`<br>Bearer token: `Outsider Dave` | `HTTP 403 Forbidden`<br>`{"success":false,"message":"Unauthorized access to tour settlement preview"}` | `HTTP 403 Forbidden`<br>`{"success":false,"message":"Unauthorized access to tour settlement preview"}` | **PASSED** ✅ |
| **11** | **Manager B views settlement preview** | `GET /api/tours/{tourA_id}/settlement-preview`<br>Bearer token: `Manager B` | `HTTP 403 Forbidden`<br>`{"success":false,"message":"Unauthorized access to tour settlement preview"}` | `HTTP 403 Forbidden`<br>`{"success":false,"message":"Unauthorized access to tour settlement preview"}` | **PASSED** ✅ |
| **12** | **Tour Manager A views settlement preview** | `GET /api/tours/{tourA_id}/settlement-preview`<br>Bearer token: `Manager A` | `HTTP 200 OK`<br>`{"success":true,"settlement":{...}}` | `HTTP 200 OK`<br>`{"success":true,"settlement":{...}}` | **PASSED** ✅ |
| **13** | **Outsider views tour payments** | `GET /api/tours/{tourA_id}/payments`<br>Bearer token: `Outsider Dave` | `HTTP 403 Forbidden`<br>`{"success":false,"message":"Unauthorized access to tour payments"}` | `HTTP 403 Forbidden`<br>`{"success":false,"message":"Unauthorized access to tour payments"}` | **PASSED** ✅ |
| **14** | **Manager B views tour payments** | `GET /api/tours/{tourA_id}/payments`<br>Bearer token: `Manager B` | `HTTP 403 Forbidden`<br>`{"success":false,"message":"Unauthorized access to tour payments"}` | `HTTP 403 Forbidden`<br>`{"success":false,"message":"Unauthorized access to tour payments"}` | **PASSED** ✅ |
| **15** | **Outsider views tour member roster** | `GET /api/tours/{tourA_id}/members`<br>Bearer token: `Outsider Dave` | `HTTP 403 Forbidden`<br>`{"success":false,"message":"Unauthorized access to tour members"}` | `HTTP 403 Forbidden`<br>`{"success":false,"message":"Unauthorized access to tour members"}` | **PASSED** ✅ |
| **16** | **Manager B views tour member roster** | `GET /api/tours/{tourA_id}/members`<br>Bearer token: `Manager B` | `HTTP 403 Forbidden`<br>`{"success":false,"message":"Unauthorized access to tour members"}` | `HTTP 403 Forbidden`<br>`{"success":false,"message":"Unauthorized access to tour members"}` | **PASSED** ✅ |
| **17** | **Manager B adds member to Manager A's tour** | `POST /api/tours/{tourA_id}/members`<br>Bearer token: `Manager B`<br>`{"name":"Fake Member","phone":"01999999999"}` | `HTTP 403 Forbidden`<br>`{"success":false,"message":"Only the Tour Manager can add participants"}` | `HTTP 403 Forbidden`<br>`{"success":false,"message":"Only the Tour Manager can add participants"}` | **PASSED** ✅ |
| **18** | **Manager B deletes member from Manager A's tour** | `DELETE /api/tours/{tourA_id}/members/{member_id}`<br>Bearer token: `Manager B` | `HTTP 403 Forbidden`<br>`{"success":false,"message":"Unauthorized"}` | `HTTP 403 Forbidden`<br>`{"success":false,"message":"Unauthorized"}` | **PASSED** ✅ |
| **19** | **Manager A adds member to own tour** | `POST /api/tours/{tourA_id}/members`<br>Bearer token: `Manager A`<br>`{"name":"Authorized Traveler","phone":"01888888888"}` | `HTTP 201 Created`<br>`{"success":true,"message":"Participant added successfully"}` | `HTTP 201 Created`<br>`{"success":true,"message":"Participant added successfully"}` | **PASSED** ✅ |
| **20** | **Outsider reads backpack checklist** | `GET /api/tours/{tourA_id}/backpack-items`<br>Bearer token: `Outsider Dave` | `HTTP 403 Forbidden`<br>`{"success":false,"message":"Unauthorized access to backpack checklist"}` | `HTTP 403 Forbidden`<br>`{"success":false,"message":"Unauthorized access to backpack checklist"}` | **PASSED** ✅ |
| **21** | **Outsider adds backpack item** | `POST /api/tours/{tourA_id}/backpack-items`<br>Bearer token: `Outsider Dave`<br>`{"category":"Hill","item_name":"Tent"}` | `HTTP 403 Forbidden`<br>`{"success":false,"message":"Unauthorized access to backpack checklist"}` | `HTTP 403 Forbidden`<br>`{"success":false,"message":"Unauthorized access to backpack checklist"}` | **PASSED** ✅ |
| **22** | **Outsider toggles backpack item** | `PATCH /api/tours/{tourA_id}/backpack-items/{id}/toggle`<br>Bearer token: `Outsider Dave` | `HTTP 403 Forbidden`<br>`{"success":false,"message":"Unauthorized access to backpack checklist"}` | `HTTP 403 Forbidden`<br>`{"success":false,"message":"Unauthorized access to backpack checklist"}` | **PASSED** ✅ |
| **23** | **Outsider deletes backpack item** | `DELETE /api/tours/{tourA_id}/backpack-items/{id}`<br>Bearer token: `Outsider Dave` | `HTTP 403 Forbidden`<br>`{"success":false,"message":"Unauthorized access to backpack checklist"}` | `HTTP 403 Forbidden`<br>`{"success":false,"message":"Unauthorized access to backpack checklist"}` | **PASSED** ✅ |
| **24** | **Tour Member C adds backpack item** | `POST /api/tours/{tourA_id}/backpack-items`<br>Bearer token: `Participant Charlie`<br>`{"category":"Hill & Forest Tours","item_name":"Flashlight"}` | `HTTP 201 Created`<br>`{"success":true,"item":{...}}` | `HTTP 201 Created`<br>`{"success":true,"item":{...}}` | **PASSED** ✅ |
| **25** | **Super Admin global oversight** | `GET /api/tours/{tourA_id}`<br>Bearer token: `Super Admin` | `HTTP 200 OK`<br>`{"success":true,"tour":{...}}` | `HTTP 200 OK`<br>`{"success":true,"tour":{...}}` | **PASSED** ✅ |

---

## 3. Automated Test Suite Metrics

- **Backend Automated Security Tests (`TourAuthorizationSecurityTest.php`):** 9/9 passing (68 assertions).
- **Total Backend Tests (`php artisan test`):** 45/45 passing (292 assertions, 0 errors, 0 failures).
- **Mobile Automated Tests (`flutter test`):** 36/36 passing.
- **Flutter Code Analysis (`flutter analyze`):** No issues found (0 errors, 0 warnings).
