<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SafarFly Email Verification</title>
</head>
<body style="margin: 0; padding: 0; background-color: #F8F9FD; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #F8F9FD; padding: 40px 15px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" style="max-width: 520px; background: #ffffff; border-radius: 20px; box-shadow: 0 4px 20px rgba(11, 31, 58, 0.08); overflow: hidden; border: 1px solid #E5EAF2;">
                    <!-- Nautical Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #0B1F3A 0%, #00685B 100%); padding: 32px 30px; text-align: center;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 26px; font-weight: 800; letter-spacing: -0.5px;">SafarFly</h1>
                            <p style="margin: 6px 0 0; color: #83F6E0; font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px;">Expedition & Tour Management</p>
                        </td>
                    </tr>
                    <!-- Content Body -->
                    <tr>
                        <td style="padding: 36px 32px;">
                            <h2 style="margin: 0 0 12px; color: #0B1C30; font-size: 20px; font-weight: 700;">Account Email Verification</h2>
                            <p style="margin: 0 0 20px; color: #4D5F7D; font-size: 14.5px; line-height: 1.6;">
                                Hello <strong>{{ $fullName }}</strong>,<br>
                                Thank you for joining SafarFly. To verify your email address and activate your account, please enter the 6-digit verification code below:
                            </p>

                            <!-- OTP Box -->
                            <div style="background: #F0F4FA; border: 2px dashed #00685B; border-radius: 14px; padding: 22px; text-align: center; margin: 26px 0;">
                                <div style="font-size: 11px; font-weight: 700; color: #00685B; text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 6px;">Your 6-Digit Verification Code</div>
                                <div style="font-size: 34px; font-weight: 800; color: #0B1F3A; letter-spacing: 8px; font-family: monospace;">{{ $otp }}</div>
                                <div style="font-size: 11.5px; color: #6D7A76; margin-top: 8px;">Valid for 10 minutes</div>
                            </div>

                            <p style="margin: 0 0 10px; color: #4D5F7D; font-size: 13px; line-height: 1.5;">
                                If you did not attempt to register on SafarFly, please safely ignore this email. No account will be created without this code.
                            </p>
                        </td>
                    </tr>
                    <!-- Footer -->
                    <tr>
                        <td style="background: #F0F4FA; padding: 20px 30px; text-align: center; border-top: 1px solid #E2E8F0;">
                            <p style="margin: 0; color: #6D7A76; font-size: 12px;">&copy; {{ date('Y') }} SafarFly. All rights reserved.</p>
                            <p style="margin: 4px 0 0; color: #94A3B8; font-size: 11px;">Automated security notification • Do not reply</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
