<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms of Service – SafarFly</title>
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
                <h1 class="font-display text-3xl sm:text-4xl font-extrabold text-navy-900 mt-3">Terms of Service</h1>
                <p class="text-sm text-gray-500 mt-2">Effective Date: November 14, 2026</p>
            </div>

            <div class="space-y-8 text-sm leading-relaxed text-gray-700">
                <section>
                    <h2 class="font-display text-lg font-bold text-navy-900 mb-2">1. Agreement to Terms</h2>
                    <p>
                        By creating an account, downloading, or using the <strong>SafarFly</strong> mobile application or web portal, you agree to be bound by these Terms of Service. If you do not agree to these terms, please do not use SafarFly.
                    </p>
                </section>

                <section>
                    <h2 class="font-display text-lg font-bold text-navy-900 mb-2">2. Nature of the Service (Non-Banking Platform)</h2>
                    <p class="mb-2">
                        <strong>SafarFly is an informational bookkeeping, group expense splitting, and expedition management software.</strong>
                    </p>
                    <p>
                        SafarFly is <strong>NOT</strong> a bank, escrow agent, money transmitter, or licensed Mobile Financial Service (MFS). All money transfers, cash handoffs, and digital payment transactions (bKash, Nagad, Bank transfers) are carried out directly between tour managers and tour members through external channels. SafarFly records manual reference numbers (Transaction IDs) solely for collaborative accounting and verification between tour organizers and participants.
                    </p>
                </section>

                <section>
                    <h2 class="font-display text-lg font-bold text-navy-900 mb-2">3. User Responsibilities &amp; Tour Management</h2>
                    <ul class="list-disc pl-5 space-y-1.5 text-gray-600">
                        <li><strong>Accuracy:</strong> Tour Managers are responsible for entering truthful, accurate expense records and verifying legitimate payments from participants.</li>
                        <li><strong>Security:</strong> You are responsible for safeguarding your login credentials. You must immediately notify SafarFly of any unauthorized account access.</li>
                        <li><strong>Settlement &amp; Refunds:</strong> Final tour settlement balances and emergency refund allocations are calculated algorithmically based on user-entered expenses and verified payments. SafarFly is not liable for private financial disagreements between group members.</li>
                    </ul>
                </section>

                <section>
                    <h2 class="font-display text-lg font-bold text-navy-900 mb-2">4. Prohibited Activities</h2>
                    <p class="mb-2">When using SafarFly, you agree not to:</p>
                    <ul class="list-disc pl-5 space-y-1.5 text-gray-600">
                        <li>Upload fraudulent, deceptive, or abusive payment receipts or fabricated Transaction IDs.</li>
                        <li>Attempt to bypass security boundaries, perform unauthorized privilege escalation, or inject malicious executable code.</li>
                        <li>Solicit financial credentials (such as PINs or passwords) from other users.</li>
                    </ul>
                </section>

                <section>
                    <h2 class="font-display text-lg font-bold text-navy-900 mb-2">5. Account Termination &amp; Deletion</h2>
                    <p>
                        You may delete your account at any time via the mobile app settings. We reserve the right to suspend or terminate accounts that violate these Terms or engage in fraudulent activities.
                    </p>
                </section>

                <section>
                    <h2 class="font-display text-lg font-bold text-navy-900 mb-2">6. Limitation of Liability</h2>
                    <p>
                        To the maximum extent permitted by law, SafarFly and its developers shall not be liable for any indirect, incidental, or consequential damages resulting from tour cancellations, disputes over manual cash payments, or third-party service interruptions.
                    </p>
                </section>

                <section class="border-t border-gray-100 pt-6">
                    <h2 class="font-display text-lg font-bold text-navy-900 mb-2">7. Contact Information</h2>
                    <p>
                        For inquiries regarding these Terms, contact <a href="mailto:support@safarfly.com" class="text-teal-700 hover:underline font-medium">support@safarfly.com</a>.
                    </p>
                </section>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-100 py-6 px-6 text-center text-xs text-gray-400">
        <p>&copy; {{ date('Y') }} SafarFly. All rights reserved. <a href="{{ route('legal.privacy') }}" class="text-teal-700 hover:underline ml-2">Privacy Policy</a></p>
    </footer>
</body>
</html>
