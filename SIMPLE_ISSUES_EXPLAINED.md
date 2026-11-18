# 7 Major Issues Explained in Simple Words

Here are the 7 major issues found before launching the SafarFly app, explained in plain, simple everyday words:

---

### 1. The Home Wi-Fi Trap (Status: FIXED ✅)
- **Simple Meaning:** The app was looking for your home bedroom Wi-Fi router instead of the live cloud website. Public users downloading the app on mobile internet could not open it.

---

### 2. The Secret Password Peeking Bug (`APP_DEBUG=true`) (Status: FIXED ✅)
- **Simple Meaning:** If the server runs into any small error, it shows its private passwords and secret keys on screen instead of a friendly "Oops, try again" message. (We fixed this: errors now show a clean safe message and secrets never leak).

---

### 3. Strangers Touching Another Tour’s Money (Missing Lock)
- **Simple Meaning:** One tour leader could accidentally view or change another tour leader's private trip bills because the server was not double-checking who owned the trip.

---

### 4. Missing Official Android Play Store Stamp (Keystore Key)
- **Simple Meaning:** The Android app is signed with a practice beginner stamp. Google Play will reject the app until we give it an official release stamp (`key.properties`).

---

### 5. Missing Privacy Policy Button Inside the App
- **Simple Meaning:** Both Apple and Google legally require a button inside the app that says "Privacy Policy". Without it, store reviewers will reject the app immediately.

---

### 6. App Allows Plain Unsecured Internet (`usesCleartextTraffic`)
- **Simple Meaning:** The app currently allows plain unencrypted HTTP traffic. For sensitive money data, Apple and Google require locked HTTPS encryption everywhere.

---

### 7. Admins and Regular Users Share the Same Front Door (`/login`)
- **Simple Meaning:** The big boss admin and normal tour leaders both log in at the exact same public door, making it easier for hackers to try guessing passwords.
