# ISSUE #6 HARD TEST & SECURITY VERIFICATION REPORT
**Audit Item:** Issue #6 (P1-3) - Unencrypted Cleartext HTTP Disallow & Network Security Hardening  
**Target:** Android Production Manifest, Android Network Security Configuration, iOS ATS, and Flutter Network Config  
**Date:** November 19, 2026  
**Auditor / Engineer:** Senior Security Auditor & Mobile Release Engineer  

---

## 1. Executive Summary & Plain Language Explanation

### What was the issue?
In the mobile application's main configuration (`AndroidManifest.xml`), the flag `android:usesCleartextTraffic="true"` was enabled. This allowed the app to send and receive unencrypted plain HTTP traffic across the open internet.

### Why is this dangerous?
1. **Man-In-The-Middle (MITM) Attacks:** Passwords, user sessions, traveler personal profiles, and financial tour expense records could be snooped on public Wi-Fi networks if transmitted over plain HTTP.
2. **Google Play Store Policy & Security Scanners:** Google Play Console flags applications that permit cleartext traffic globally, as modern Android versions (Android 9+ / API 28+) mandate encrypted communication by default.

### How was it fixed?
1. **Enforced Cleartext Traffic Disallow in Release:** Set `android:usesCleartextTraffic="false"` in `android/app/src/main/AndroidManifest.xml`.
2. **Created Network Security Config:** Added `android/app/src/main/res/xml/network_security_config.xml` defining `<base-config cleartextTrafficPermitted="false">` and strictly enforcing TLS/HTTPS on all production endpoints (`safarfly.com` and `api.safarfly.com`).
3. **Preserved Local Wi-Fi Development Only for Debug:** In `android/app/src/debug/AndroidManifest.xml`, enabled cleartext traffic strictly for debug builds so engineers can continue developing against local LAN servers without weakening the production release.
4. **Verified iOS App Transport Security (ATS):** Confirmed `ios/Runner/Info.plist` does not contain `NSAllowsArbitraryLoads`, guaranteeing HTTPS enforcement across Apple devices.

---

## 2. Hard Test Suite Execution Results

Below is the complete Hard Test verification table covering all security constraints and production requirements:

| Test Scenario | Test Input | Expected Output | Actual Output | Status |
| :--- | :--- | :--- | :--- | :--- |
| **1. Release API URL Protocol Verification** | Evaluate `AppConfig.resolveDefaultUrl(isRelease: true)` | Must strictly start with `https://` (`https://api.safarfly.com/api`) and never `http://` | `https://api.safarfly.com/api` (Strict HTTPS confirmed) | **PASSED ✅** |
| **2. Android Production Manifest Cleartext Flag** | Inspect `android/app/src/main/AndroidManifest.xml` | `android:usesCleartextTraffic="false"` present; `android:usesCleartextTraffic="true"` absent | `android:usesCleartextTraffic="false"` strictly enforced | **PASSED ✅** |
| **3. Android Network Security Config Reference** | Inspect `android/app/src/main/AndroidManifest.xml` `<application>` tag | Must link to `@xml/network_security_config` | `android:networkSecurityConfig="@xml/network_security_config"` present | **PASSED ✅** |
| **4. Android XML Network Security Policy** | Inspect `android/app/src/main/res/xml/network_security_config.xml` | `<base-config cleartextTrafficPermitted="false">` and subdomains `safarfly.com` / `api.safarfly.com` enforced | Base config cleartextTrafficPermitted="false" with domain pin active | **PASSED ✅** |
| **5. Android Debug LAN Fallback Isolation** | Inspect `android/app/src/debug/AndroidManifest.xml` | `usesCleartextTraffic="true"` confined exclusively to debug source set | Debug manifest has `usesCleartextTraffic="true"` (isolated to debug builds) | **PASSED ✅** |
| **6. iOS App Transport Security (ATS) Policy** | Inspect `ios/Runner/Info.plist` | No `NSAllowsArbitraryLoads = true` bypass | Zero unencrypted ATS exceptions present (100% compliant) | **PASSED ✅** |
| **7. Full Flutter Test Suite Regression** | Run `flutter test` across all 46 test cases | 46/46 tests passed (0 failures, 0 errors) | `00:19 +46: All tests passed!` | **PASSED ✅** |
| **8. Production Release APK Compilation** | Run `flutter build apk --release` with official keystore | Successful Gradle build producing signed production APK | Build successful; `SafarFly.apk` generated | **PASSED ✅** |

---

## 3. Security Guarantee for Store Submission

- **Google Play Protect:** Compliant with Android API 28+ Network Security Config guidelines.
- **Apple Store Review:** Compliant with iOS App Transport Security (ATS) specifications.
- **Financial & Token Data:** All API traffic, bearer tokens, passwords, and tour finances are strictly encrypted over TLS/HTTPS.
