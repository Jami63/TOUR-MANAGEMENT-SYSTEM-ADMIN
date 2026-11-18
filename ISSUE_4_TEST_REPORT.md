# Issue #4 Pre-Launch Hard Test Report: Official Android Release Keystore & Play Store Signing

**Date:** November 18, 2026  
**Issue Reference:** P1-1 (Missing Production Keystore & Practice Beginner Stamp)  
**Status:** **FULLY RESOLVED & HARDENED (100% PASS)**

---

## 1. Summary of Changes

To resolve Google Play Store rejection risks caused by debug signing:
1. **Production Keystore Generation**:
   - Generated an official 2048-bit RSA release keystore (`safarfly-release-key.jks`) using Java Keytool.
   - Set validity to **10,000 days** (valid until **April 5, 2054**, well beyond Google Play's 25-year requirement).
   - Certificate Distinguished Name (DName):  
     `CN=SafarFly Release Manager, OU=Mobile Engineering, O=SafarFly, L=Dhaka, ST=Dhaka, C=BD`
2. **Build Configuration Hardening (`android/app/build.gradle`)**:
   - Upgraded Gradle release signing configuration to dynamically load `key.properties`.
   - Added smart path fallback supporting both project-relative and root-relative keystore locations.
   - Enforced that release builds utilize `signingConfigs.release` when `key.properties` is present.
3. **Secret Security & Git Ignore Protection**:
   - Verified that `.gitignore` strictly protects `key.properties`, `android/key.properties`, `*.jks`, and `*.keystore`.
   - Updated `android/key.properties.example` with clear instructions and parameters for production release engineers.
   - Safely backed up production keystore to user home directory (`C:\Users\Windows 10\safarfly-release-key.jks`).
4. **Full Artifact Compilation & Binary Verification**:
   - Compiled full production release APK via `flutter build apk --release` (23.1 MB).
   - Inspected embedded APK certificate with `keytool -printcert -jarfile` confirming **zero debug certificate**.
   - Synchronized top-level `SafarFly.apk` with the officially signed production artifact.

---

## 2. Hard Test Results (Input, Expected vs Actual Output)

| # | Test Scenario | Test Input | Expected Output | Actual Output | Status |
| :---: | :--- | :--- | :--- | :--- | :---: |
| **1** | **Keystore Generation & Cryptographic Strength** | `keytool -genkeypair -alias safarfly -keyalg RSA -keysize 2048 -validity 10000` | 2048-bit RSA key pair generated with SHA384withRSA signature | 2048-bit RSA key pair generated and stored at `safarfly-release-key.jks` | **PASSED** ✅ |
| **2** | **Keystore Validity Period Verification** | `keytool -list -v -keystore safarfly-release-key.jks -alias safarfly` | Valid for ≥ 25 years (until 2051+) | `Valid from Nov 18, 2026 until Sun Apr 05 15:27:32 GMT+06:00 2054` (27.4 years) | **PASSED** ✅ |
| **3** | **Distinguished Name (DName) Ownership** | Inspect certificate Subject / Owner | `CN=SafarFly Release Manager, OU=Mobile Engineering, O=SafarFly, L=Dhaka, ST=Dhaka, C=BD` | `CN=SafarFly Release Manager, OU=Mobile Engineering, O=SafarFly, L=Dhaka, ST=Dhaka, C=BD` | **PASSED** ✅ |
| **4** | **Keystore Git Leakage Prevention** | `git status` after generating `key.properties` and `safarfly-release-key.jks` | Files must be ignored by `.gitignore`; working tree remains clean | `nothing to commit, working tree clean` (Keystore and credentials completely hidden from Git) | **PASSED** ✅ |
| **5** | **Gradle Signing Configuration Loading** | Read `android/key.properties` via `rootProject.file('key.properties')` in `build.gradle` | Properties loaded: `storePassword`, `keyPassword`, `keyAlias`, `storeFile` | `signingConfig` successfully instantiated with release credentials | **PASSED** ✅ |
| **6** | **Full Release APK Build** | `flutter build apk --release` | Exit code 0, generates `build/app/outputs/flutter-apk/app-release.apk` | Exit code 0, generated `app-release.apk` (23.1 MB in 104.1s) | **PASSED** ✅ |
| **7** | **APK Embedded Signer Inspection** | `keytool -printcert -jarfile app-release.apk` | Signer #1 Owner: `CN=SafarFly Release Manager, O=SafarFly` | Signer #1 Owner: `CN=SafarFly Release Manager, OU=Mobile Engineering, O=SafarFly, L=Dhaka, ST=Dhaka, C=BD` | **PASSED** ✅ |
| **8** | **Absence of Debug Signature Check** | Search APK signer certificate for `CN=Android Debug` or `O=Android` | `CN=Android Debug` must NOT exist in the signed APK | Zero debug strings found; 100% official SafarFly certificate | **PASSED** ✅ |
| **9** | **Certificate Fingerprint Integrity** | Compute SHA-256 fingerprint on signed APK | Matching SHA-256: `D3:7F:33:E9:8A:F4:75:3C:90:03:4B:88:EE:7D:2B:45:4A:2E:4B:57:83:69:54:3C:BE:A9:3B:91:EA:86:19:91` | Verified identical SHA-256 fingerprint on APK signer | **PASSED** ✅ |
| **10** | **Root APK Distribution Asset Synchronization** | `Copy-Item app-release.apk SafarFly.apk -Force` | Root `SafarFly.apk` updated to 24,239,899 bytes with production signature | Root `SafarFly.apk` synchronized (24,239,899 bytes, timestamp 3:34 PM) | **PASSED** ✅ |
| **11** | **Automated Flutter Regression Test Suite** | `flutter test` across 5 test suites | 36/36 tests passed (0 failures) | `00:11 +36: All tests passed!` | **PASSED** ✅ |
| **12** | **Dart/Flutter Static Code Analysis** | `flutter analyze` | 0 errors, 0 warnings | `No issues found! (ran in 40.8s)` | **PASSED** ✅ |

---

## 3. Cryptographic Certificate Specifications

- **Keystore Format:** JKS (Java Keystore) / PKCS12 compatible
- **Key Algorithm:** RSA 2048-bit
- **Signature Algorithm:** SHA384withRSA
- **Serial Number:** `16725f6bda327548`
- **Validity:** November 18, 2026 – April 5, 2054 (10,000 Days)
- **SHA-1 Fingerprint:** `44:AB:03:A3:A6:64:CF:A2:BC:70:A7:5C:9E:7B:9D:65:25:AC:FD:A1`
- **SHA-256 Fingerprint:** `D3:7F:33:E9:8A:F4:75:3C:90:03:4B:88:EE:7D:2B:45:4A:2E:4B:57:83:69:54:3C:BE:A9:3B:91:EA:86:19:91`
