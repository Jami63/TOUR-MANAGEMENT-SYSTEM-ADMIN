<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy – SafarFly</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Manrope:wght@600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        navy: { 900: '#0B1F3A', 800: '#12305A', 700: '#1B4577' },
                        teal: { 700: '#0B7F70', 600: '#0F9D8A', 50: '#E6F6F3' },
                        surface: '#F8F9FF'
                    },
                    fontFamily: {
                        display: ['Manrope', 'sans-serif'],
                        body: ['Inter', 'sans-serif']
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-surface text-navy-900 font-body antialiased min-h-screen flex flex-col justify-between">
    <!-- Top Navigation / Header -->
    <header class="bg-white border-b border-gray-100 py-4 px-6 sm:px-12">
        <div class="max-w-4xl mx-auto flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-teal-600 text-white flex items-center justify-center shadow-md shadow-teal-600/20">
                    <span class="material-symbols-outlined text-[22px]">flight_takeoff</span>
                </div>
                <span class="font-display font-extrabold text-xl tracking-tight text-navy-900">SafarFly</span>
            </div>
            <a href="{{ url('/') }}" class="text-xs font-bold text-teal-700 hover:text-teal-600 flex items-center gap-1">
                <span class="material-symbols-outlined text-[16px]">arrow_back</span> Back to App
            </a>
        </div>
    </header>

    <!-- Main Content Container -->
    <main class="max-w-4xl mx-auto px-6 py-12 flex-1">
        <div class="bg-white rounded-3xl p-8 sm:p-12 shadow-sm border border-gray-100">
            <div class="mb-8 pb-6 border-b border-gray-100">
                <span class="px-3 py-1 rounded-full bg-teal-50 text-teal-700 text-xs font-bold uppercase tracking-wider">Legal Document</span>
                <h1 class="font-display text-3xl sm:text-4xl font-extrabold text-navy-900 mt-3">Privacy Policy</h1>
                <p class="text-sm text-gray-500 mt-2">Effective Date: November 14, 2026 | Last Updated: November 14, 2026</p>
            </div>

            <div class="space-y-8 text-sm leading-relaxed text-gray-700">
                <section>
                    <h2 class="font-display text-lg font-bold text-navy-900 mb-2">1. Overview</h2>
                    <p>
                        Welcome to <strong>SafarFly</strong>. SafarFly is a group travel planning, expedition ledger, and expense management mobile and web application. We respect your privacy and are committed to protecting your personal information. This Privacy Policy explains how our services collect, use, and safeguard your data when using SafarFly on mobile (Android &amp; iOS) or through our web portal.
                    </p>
                </section>

                <section>
                    <h2 class="font-display text-lg font-bold text-navy-900 mb-2">2. Information We Collect</h2>
                    <p class="mb-3">To provide group expedition planning, dynamic expense splitting, and financial balance calculations, we collect:</p>
                    <ul class="list-disc pl-5 space-y-1.5 text-gray-600">
                        <li><strong>Account Details:</strong> Your full name, email address, phone number (optional), and encrypted password credentials.</li>
                        <li><strong>Tour Itineraries &amp; Collaboration:</strong> Tour names, dates, destination waypoints, and packing checklist notes.</li>
                        <li><strong>Manual Payment &amp; Expense Data:</strong> Expense amounts, category allocations, split shares, payment methods (Cash, bKash, Nagad), and member-provided Transaction IDs (TrxID).</li>
                        <li><strong>Images &amp; Receipts:</strong> User profile pictures and photographed expense receipt vouchers that you choose to upload.</li>
                    </ul>
                </section>

                <section class="bg-teal-50/60 p-5 rounded-2xl border border-teal-100">
                    <h2 class="font-display text-base font-bold text-teal-900 mb-2 flex items-center gap-2">
                        <span class="material-symbols-outlined text-[20px] text-teal-700">shield</span>
                        3. Strict Financial Protection &amp; Zero Credential Solicitation
                    </h2>
                    <p class="text-xs text-teal-950 leading-relaxed">
                        <strong>SafarFly operates on a strictly MANUAL payment model.</strong> We do <strong>NOT</strong> process digital bank transactions, and we <strong>NEVER</strong> ask for, solicit, or store mobile financial service (MFS) PINs, banking passwords, one-time passwords (OTPs), or credit/debit card numbers. All monetary exchanges occur externally, and SafarFly is used solely as a collaborative accounting ledger where tour managers verify payment references.
                    </p>
                </section>

                <section>
                    <h2 class="font-display text-lg font-bold text-navy-900 mb-2">4. How We Use Your Information</h2>
                    <ul class="list-disc pl-5 space-y-1.5 text-gray-600">
                        <li>To authenticate your identity and provide secure access to your tour workspaces.</li>
                        <li>To calculate dynamic expense splits, outstanding dues, and fair emergency refund settlements among group members.</li>
                        <li>To send email verification codes (OTP) during registration to prevent unauthorized account creation.</li>
                        <li>To preserve transparent expense records accessible only to members of that specific tour.</li>
                    </ul>
                </section>

                <section>
                    <h2 class="font-display text-lg font-bold text-navy-900 mb-2">5. Data Sharing &amp; Multi-Tenancy</h2>
                    <p>
                        We do <strong>not</strong> sell, rent, or trade your personal data to third parties or advertising networks. Your tour itineraries and financial splits are strictly isolated: only authenticated members who belong to your specific tour can view shared expense allocations.
                    </p>
                </section>

                <section>
                    <h2 class="font-display text-lg font-bold text-navy-900 mb-2">6. User Rights &amp; Permanent Account Deletion</h2>
                    <p class="mb-3">
                        In compliance with Apple App Store Guideline 5.1.1(v) and Google Play Data Safety policies, you have complete control over your account:
                    </p>
                    <ul class="list-disc pl-5 space-y-1.5 text-gray-600 mb-3">
                        <li><strong>In-App Deletion:</strong> You may initiate permanent account deletion at any time directly in the mobile app by navigating to <code>Profile Settings &rarr; Delete Account</code> and confirming with your password.</li>
                        <li><strong>Data Handling on Deletion:</strong> Your personal identifiable information (name, email, phone number, and uploaded profile image) is permanently wiped and anonymized. Past tour split balances remain mathematically intact as "Former Member" so that other group participants' financial accounting is not disrupted. All authentication tokens are immediately revoked.</li>
                        <li><strong>Web Deletion Requests:</strong> You can also request complete account removal by emailing <code>support@safarfly.com</code> from your registered email address.</li>
                    </ul>
                </section>

                <section>
                    <h2 class="font-display text-lg font-bold text-navy-900 mb-2">7. Security Measures</h2>
                    <p>
                        We protect your data using industry-standard technical safeguards, including Bcrypt (12 rounds) password hashing, Laravel Sanctum tokenized API access, strict file upload MIME verification, and encrypted database connections.
                    </p>
                </section>

                <section>
                    <h2 class="font-display text-lg font-bold text-navy-900 mb-2">8. Children's Privacy</h2>
                    <p>
                        SafarFly is not directed toward individuals under the age of 13. We do not knowingly collect personal information from children.
                    </p>
                </section>

                <section class="border-t border-gray-100 pt-6">
                    <h2 class="font-display text-lg font-bold text-navy-900 mb-2">9. Contact Us</h2>
                    <p>
                        If you have questions, feedback, or data privacy requests regarding this policy, please reach out to:
                    </p>
                    <div class="mt-3 p-4 bg-gray-50 rounded-2xl text-xs space-y-1 text-gray-700">
                        <p class="font-bold text-navy-900">SafarFly Support &amp; Compliance Team</p>
                        <p>Email: <a href="mailto:support@safarfly.com" class="text-teal-700 hover:underline">support@safarfly.com</a></p>
                        <p>Web: <a href="{{ url('/') }}" class="text-teal-700 hover:underline">{{ url('/') }}</a></p>
                    </div>
                </section>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-100 py-6 px-6 text-center text-xs text-gray-400">
        <p>&copy; {{ date('Y') }} SafarFly. All rights reserved. <a href="{{ route('legal.terms') }}" class="text-teal-700 hover:underline ml-2">Terms of Service</a></p>
    </footer>
</body>
</html>
