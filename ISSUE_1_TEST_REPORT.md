# Issue #1 Strict Pre-Launch Verification Report: Hardcoded Local IP Fallback
**Date:** November 18, 2026  
**Auditor:** Senior QA & Release Architect  
**Platform:** SafarFly Mobile Application (Flutter) & Backend Admin  
**Issue ID:** P0-1 (Production Blocker #1)  
**Verification Result:** **100% VERIFIED FIXED & HARDENED (7 of 7 Tests Passed)**

---

## 1. What Was the Bug? (Recap)
- **Previous Code:** In `lib/core/constants.dart`, `defaultBaseUrl` hardcoded `http://192.168.0.108:8000/api`.
- **The Danger:** A public user installing the app from the Google Play Store on 4G/5G mobile internet would attempt to connect to the developer's home Wi-Fi subnet (`192.168.0.108`), causing instant connection failure and store negative reviews.

---

## 2. Strict Automated Verification Results

### **Test 1: Release Mode Resolution (Zero Local IP Guarantee)**
- **Scenario:** Public store download running on a real customer's phone on 4G/5G.
- **Input:** `AppConfig.resolveDefaultUrl(isRelease: true)`
- **Expected Output:**
  - Must return `https://api.safarfly.com/api`
  - Must use `https://` protocol scheme
  - Must contain ZERO occurrences of `192.168.` or port `8000`
- **Actual Output:**
  - Value: `https://api.safarfly.com/api`
  - Contains `192.168.`: `false`
  - Contains `8000`: `false`
- **Status:** **PASS ✅**

---

### **Test 2: Debug Mode Fallback Resolution (Local Testing Preservation)**
- **Scenario:** Developer debugging on local host PC or home Wi-Fi.
- **Input:** `AppConfig.resolveDefaultUrl(isRelease: false, isAndroidPlatform: true)`
- **Expected Output:**
  - Must resolve to `http://192.168.0.108:8000/api` so internal testing and development never break.
- **Actual Output:**
  - Value: `http://192.168.0.108:8000/api`
- **Status:** **PASS ✅**

---

### **Test 3: CI/CD Pipeline Build-Time Override**
- **Scenario:** Custom staging deployment using `--dart-define=API_BASE_URL=...`.
- **Input:** `AppConfig.resolveDefaultUrl(envOverride: 'https://staging-api.safarfly.com')`
- **Expected Output:**
  - Returns `https://staging-api.safarfly.com/api`
- **Actual Output:**
  - Value: `https://staging-api.safarfly.com/api`
- **Status:** **PASS ✅**

---

### **Test 4: Stale Local IP Storage Purge on Release Startup**
- **Scenario:** A device previously used for beta testing has old `192.168.0.108:8000` cached in `SharedPreferences`.
- **Input:**
  - Device storage has: `custom_api_base_url = 'http://192.168.0.108:8000/api'`
  - Call `AppConfig.init(isRelease: true)`
- **Expected Output:**
  - Stale local IP is automatically deleted from `SharedPreferences`
  - `prefs.getString('custom_api_base_url')` is `null`
  - Active `AppConfig.baseUrl` cleanly defaults to `https://api.safarfly.com/api`
- **Actual Output:**
  - `prefs.getString('custom_api_base_url')`: `null`
  - `AppConfig.isCustomConfigured`: `false`
  - `AppConfig.baseUrl`: `https://api.safarfly.com/api`
- **Status:** **PASS ✅**

---

### **Test 5: Valid Custom Cloud Domain Preservation in Release Mode**
- **Scenario:** Enterprise user manually enters a valid public corporate API domain into server settings.
- **Input:**
  - Device storage has: `custom_api_base_url = 'api.enterprise-tours.com'`
  - Call `AppConfig.init(isRelease: true)`
- **Expected Output:**
  - Public domain is preserved and normalized to `https://api.enterprise-tours.com/api`
- **Actual Output:**
  - `AppConfig.isCustomConfigured`: `true`
  - `AppConfig.baseUrl`: `https://api.enterprise-tours.com/api`
- **Status:** **PASS ✅**

---

### **Test 6: Local LAN Address Detection Accuracy (`isLocalAddress`)**
- **Scenario:** Validates boundary detection of private IP ranges and loopbacks.
- **Inputs & Actual Outputs:**
  - `http://192.168.0.108:8000/api` -> `true` (Detected as LAN)
  - `http://192.168.1.1:8000` -> `true` (Detected as LAN)
  - `http://10.0.2.2:8000` -> `true` (Detected as Emulator Loopback)
  - `http://localhost:8000` -> `true` (Detected as Loopback)
  - `http://127.0.0.1:8000` -> `true` (Detected as Loopback)
  - `https://api.safarfly.com/api` -> `false` (Recognized as Public Cloud)
  - `https://safarfly.com` -> `false` (Recognized as Public Cloud)
- **Status:** **PASS ✅**

---

### **Test 7: URL Normalizer Multi-Variation Handling**
- **Scenario:** Users type URLs with missing schemes, trailing slashes, or duplicate `/api`.
- **Inputs & Actual Outputs:**
  - Input: `'api.safarfly.com'` -> Output: `'https://api.safarfly.com/api'`
  - Input: `'https://api.safarfly.com/'` -> Output: `'https://api.safarfly.com/api'`
  - Input: `'https://api.safarfly.com/api'` -> Output: `'https://api.safarfly.com/api'`
  - Input: `'https://api.safarfly.com/api/'` -> Output: `'https://api.safarfly.com/api'`
  - Input: `'192.168.0.108:8000'` -> Output: `'http://192.168.0.108:8000/api'`
  - Input: `'http://192.168.0.108:8000/'` -> Output: `'http://192.168.0.108:8000/api'`
- **Status:** **PASS ✅**

---

## 3. Deliverable & Build Verification
- **Test Suite Results:** **36 of 36 Flutter tests passed** (100% green)
- **Static Analysis:** `flutter analyze` completed with **0 errors, 0 warnings**
- **Production Binary:** Compiled fresh at `SafarFly.apk` (23.1 MB, 24,239,781 bytes)
