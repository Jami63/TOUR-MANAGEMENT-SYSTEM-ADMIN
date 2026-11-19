# Safar Fly – Complete System Documentation
**Tour & Expense Management System**  
*Plan Together. Travel Together. Manage Smarter.*  
*A beginner-friendly guide written in simple language*

---

## Contents
1. What Is Safar Fly?
2. The Most Important Idea: Everything Is Flexible
3. User Roles
4. Public Website Pages
5. Tour Manager Dashboard
6. Tour Management
7. Tour Details Page
8. Member Management
9. Expense Management
10. Expense Sharing System
11. Expense-wise Payment Tracking
12. Payment Management
13. Refund Management
14. Reports and Analytics
15. Notifications
16. Profile and Settings
17. Tour Member Dashboard
18. My Tour Details
19. Member Pages: Expenses, Payments and Refunds
20. The Whole Journey in One Page
21. Production Security, Mobile Ecosystem & Architecture

---

## 1. What Is Safar Fly?
Safar Fly is a website and mobile application that helps a group of people organize a trip together and handle all the money side of it. Think of it as a smart notebook that keeps everyone's trip money in one clear place, so nobody has to ask *“Who paid? Who still owes? How much is left?”* in a messy group chat.

**With Safar Fly you can:**
- Create a tour (trip) and invite the people who are going.
- Add every cost of the trip, such as bus fare, hotel or food.
- Let the system split each cost between everyone automatically.
- Collect money in small or large parts and keep a record of every payment.
- See exactly who has paid and who still owes money.
- Share the money that is left over fairly after the trip ends (refunds).

### Key Words Used in This Guide
| Word | Simple Meaning |
| :--- | :--- |
| **Tour** | One trip with its own people, costs and payments. |
| **Tour Manager (Admin)** | The person who creates and runs the tour. He or she is also one of the travellers. |
| **Tour Member** | A person who joins a tour and pays his or her share. |
| **Participant** | Anyone going on the tour, including the Tour Manager. |
| **Expense** | Something the tour must pay for, for example a hotel booking. |
| **Share** | The part of an expense that one person has to pay. |
| **Due** | The money a person still has to pay. |
| **Refund** | Money given back to people when the tour is over and money is left. |

---

## 2. The Most Important Idea: Everything Is Flexible
Safar Fly is **not** made for one place, one kind of trip or one fixed list of costs. The Tour Manager decides everything. Nothing is locked in advance.

- **2.1 Any Destination:** Dhaka to Cox's Bazar, Sylhet, Sajek, Bandarban, or multiple destinations (Sylhet → Ratargul → Jaflong).
- **2.2 Any Tour Details:** Name, starting location, destinations, dates, budget, description. Starts as an empty form.
- **2.3 Any Expense Category:** Custom categories (Bus Fare, Hotel, Food, Bonfire, Guide, Medicine).

---

## 3. User Roles
- **Role 1: Tour Manager (Admin):** Full control over tour creation, participants, expenses, payment verification, and refund calculations.
  > **Very Important Rule:** The Tour Manager is **also a participant** in his or her own tour, taking an equal share of every expense.
- **Role 2: Tour Member:** View tour details, personal expense share, payment history, remaining due, and submit payments.

---

## 4. Public Website Pages
| Page | What It Is For | What It Does |
| :--- | :--- | :--- |
| **Home Page** | First page visitors see | Shows tagline, introduction, key benefits, Login/Register |
| **About Page** | Explains the mission | Tells why Safar Fly was created and problems it solves |
| **Features Page** | Capability overview | Custom tours, auto-splitting, payment tracking, refunds |
| **Contact Page** | Support & inquiries | Simple message form and support channels |
| **Registration Page** | Account sign-up | Name, email, phone, password with mandatory consent |
| **Login Page** | System entry | Directs to Tour Manager or Tour Member dashboards |

---

## 5. Tour Manager Dashboard
- **Summary Cards:** Total Tours, Upcoming Tours, Ongoing Tours, Completed Tours, Total Members, Total Budget, Total Expense, Total Collected, Total Due, Remaining Balance.
- **Activity Feeds:** Recent activities, upcoming tour schedules, pending payment alerts, expense logs.

---

## 6. Tour Management
- Actions: View All Tours, Create New Tour, Edit Tour, Archive/Delete Tour, Search, Filter.
- Tour Status: Upcoming, Ongoing, Completed, Cancelled.
- **Multi-tour Isolation:** Every tour keeps separate financial records.

---

## 7. Tour Details Page
Single dashboard displaying Tour Overview, Waypoints, Dates, Duration, Member Roster, Budget, Expense Breakdown, Payment Summary, Dues, and Event Timeline.

---

## 8. Member Management
- Actions: Add Member, Create Account, Edit, Remove, View Payment History, View Expense-wise Dues.
- Data Stored: Name, Email, Phone, Profile Photo, Tour Role, Total Share, Total Paid, Remaining Due, Status.

---

## 9. Expense Management
- Record expenses with name, custom category, amount, date, description, receipt upload, and creator tracking.
- Dynamic per-tour flexibility.

---

## 10. Expense Sharing System
- **Formula:** `Each Person's Share = Total Expense Amount ÷ Total Participants`
- **Remainder Precision:** Mathematical remainders down to 1 paisa are systematically allocated so zero money is lost.

---

## 11. Expense-wise Payment Tracking
- Every expense tracks individual participant payment status: **Paid**, **Partial**, or **Pending**.

---

## 12. Payment Management
- Member submission steps: Select tour → Select expense → Enter amount → Choose bKash/Nagad/Cash → Enter TrxID → Upload proof.
- Tour Manager verifies or rejects with feedback.

---

## 13. Refund Management
- Safe rule: **Collect outstanding member dues first before issuing refunds.**
- **Formula:** `Refund = (Verified Paid) − (Share of Final Expenses) − Adjustments`

---

## 14. Reports and Analytics
- Full suite of reports: Tour Budget, Total Expense, Category-wise, Member-wise Payment, Dues, Refunds, Final Balance.
- Export formats: **PDF** and **Excel**.

---

## 15. Notifications
- Real-time alerts for member invitations, expense additions, payment submissions, verifications, due reminders, and schedule changes.

---

## 16. Profile and Settings
- Profile management, password reset, avatar upload, preference toggles, and secure session logout.

---

## 17. Tour Member Dashboard & Details (Chapters 17, 18, 19)
- Member-centric view showing personal trip participation, individual share breakdown, payment submission modal, and refund tracking.

---

## 20. The Whole Journey in One Page
1. Visitor registers and logs in.
2. Tour Manager creates a tour.
3. Tour Manager adds participants (manager is participant #1).
4. Tour Manager creates categories and adds expenses.
5. System auto-splits expenses equally.
6. Members submit payments in parts.
7. Tour Manager verifies payments.
8. System updates dues and reports automatically.
9. Tour marked Completed after final costs are recorded.
10. System checks collected funds vs final expenses and calculates refunds.
11. Tour Manager disburses refunds via bKash/Nagad/Cash.
12. Final Balance Report closes tour accounts.

---

## 21. Production Security & New Architecture Features
1. **Dedicated Super Admin Terminal:** `/admin/login` with `throttle:5,1` rate limiting and role gating.
2. **Dynamic Backpack Checklist:** Category-tailored packing checklists across 20 expedition types.
3. **SafarFly PRO Subscription:** Premium tiers unlocking unlimited squad sizes and advanced financial analytics.
4. **Native Legal & Privacy Center:** In-app Privacy Policy & Terms compliant with Apple Guideline 5.1.1 and Google Play policies.
5. **Mobile Security:** Disallowed cleartext HTTP (`usesCleartextTraffic="false"`), official 2048-bit RSA release keystore valid to 2054, and zero debug credential exposure.
