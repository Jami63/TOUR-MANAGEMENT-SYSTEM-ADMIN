# 7 Major Issues Explained in Simple Words

Here are the 7 major issues found before launching the SafarFly app, explained in plain, simple everyday words:

---

### 1. The Home Wi-Fi Trap (Status: FIXED ✅)
- **Simple Meaning:** The app was looking for your home bedroom Wi-Fi router instead of the live cloud website. Public users downloading the app on mobile internet could not open it.

---

### 2. The Secret Password Peeking Bug (`APP_DEBUG=true`) (Status: FIXED ✅)
- **Simple Meaning:** If the server runs into any small error, it shows its private passwords and secret keys on screen instead of a friendly "Oops, try again" message. (We fixed this: errors now show a clean safe message and secrets never leak).

---

### 3. Strangers Touching Another Tour’s Money (Missing Lock) (Status: FIXED ✅)
- **Simple Meaning:** One tour leader could accidentally view or change another tour leader's private trip bills because the server was not double-checking who owned the trip. (We fixed this: every tour, expense, payment, member list, and backpack checklist is now guarded with strict locks; only the creator or members can view or manage their own tours).

---

### 4. Missing Official Android Play Store Stamp (Keystore Key) (Status: FIXED ✅)
- **Simple Meaning:** The Android app was signed with a practice beginner stamp. Google Play rejects beginner stamps. (We fixed this: we created SafarFly's official 2048-bit production release stamp valid until year 2054, configured `key.properties`, and stamped the release APK with SafarFly Ltd's official certificate).

---

### 5. Missing Privacy Policy Button Inside the App (Status: FIXED ✅)
- **Simple Meaning:** Both Apple and Google legally require a button inside the app that says "Privacy Policy". Without it, store reviewers will reject the app immediately. (We fixed this: we built a dedicated in-app Legal & Compliance screen for Privacy Policy & Terms of Service, added clickable links on the Login screen, added mandatory consent links on the Registration screen, and added menu items in Account & Profile settings).

---

### 6. App Allows Plain Unsecured Internet (`usesCleartextTraffic`) (Status: FIXED ✅)
- **Simple Meaning:** The app was previously allowing plain unencrypted HTTP traffic. For sensitive money data, Apple and Google require locked HTTPS encryption everywhere. (We fixed this: locked Android to `usesCleartextTraffic="false"`, enabled strict XML network security config for production domains, and verified iOS App Transport Security so all data travels inside bank-grade encrypted HTTPS).

---

### 7. Admins and Regular Users Share the Same Front Door (`/login`) (Status: FIXED ✅)
- **Simple Meaning:** Previously, the big boss admin and normal tour leaders both logged in at the exact same public door, making it easier for hackers to try guessing passwords. (We fixed this: we built a private, separate, rate-limited "Admin Security Terminal" at `/admin/login` exclusively for Super Administrators with strict role verification; any normal manager or stranger trying to enter is immediately blocked, logged out, and locked out).
