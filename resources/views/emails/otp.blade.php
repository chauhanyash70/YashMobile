<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Login - {{ config('app.name', 'Yash Mobile') }}</title>
    <!-- Modern Sans-Serif Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* Responsive styles */
        @media screen and (max-width: 600px) {
            .email-card {
                width: 92% !important;
                margin: 20px auto !important;
                border-radius: 12px !important;
            }
            .otp-box {
                width: 44px !important;
                height: 50px !important;
                line-height: 50px !important;
                font-size: 26px !important;
                border-radius: 8px !important;
            }
            .content-padding {
                padding: 24px 16px !important;
            }
        }
    </style>
</head>
<body style="font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background-color: #f8fafc; color: #334155; margin: 0; padding: 0; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale;">

    <!-- Standard Outer Full-Width Container -->
    <table border="0" cellpadding="0" cellspacing="0" width="100%" bgcolor="#f8fafc" style="table-layout: fixed; width: 100%; min-width: 100%; background-color: #f8fafc; margin: 0; padding: 40px 0;">
        <tr>
            <td align="center" style="background-color: #f8fafc;">
                
                <!-- Main Email Card -->
                <table border="0" cellpadding="0" cellspacing="0" width="100%" class="email-card" style="max-width: 520px; background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.03), 0 8px 20px -6px rgba(0, 0, 0, 0.02); text-align: left; border-collapse: separate;">
                    
                    <!-- Decorative Premium Top Border -->
                    <tr>
                        <td height="5" style="background-color: #f87d1f; line-height: 5px; font-size: 0; mso-line-height-rule: exactly;">&nbsp;</td>
                    </tr>
                    
                    <!-- Card Body Padding Wrapper -->
                    <tr>
                        <td class="content-padding" style="padding: 36px 32px;">
                            
                            <!-- Header / Logo -->
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom: 28px;">
                                <tr>
                                    <td align="center">
                                        <img src="{{ asset('assets/logo/yash-mobile-logo.svg') }}" alt="Yash Mobile" height="40" width="70" style="display: block; border: 0; outline: none; text-decoration: none;">
                                        <div style="width: 40px; height: 3px; background-color: #f87d1f; border-radius: 2px; margin-top: 12px;"></div>
                                    </td>
                                </tr>
                            </table>
                            
                            <!-- Welcome Greeting -->
                            <h2 style="font-family: 'Outfit', 'Inter', sans-serif; font-size: 20px; font-weight: 600; color: #0f172a; margin-top: 0; margin-bottom: 12px; text-align: center;">
                                Secure Verification Code
                            </h2>
                            
                            <p style="font-size: 15px; line-height: 1.6; color: #475569; margin: 0 0 20px 0; text-align: center;">
                                Hello @if($user)<strong>{{ $user->name }}</strong>@else<strong>Valued Customer</strong>@endif,
                            </p>
                            
                            <p style="font-size: 14px; line-height: 1.6; color: #64748b; margin: 0 0 24px 0; text-align: center;">
                                You requested a one-time passcode (OTP) to securely sign in to your {{ config('app.name', 'Yash Mobile') }} account. Please use the following 4-digit code to complete your login:
                            </p>
                            
                            <!-- 4-Digit Display Blocks -->
                            @php
                                $digits = str_split($otp);
                            @endphp
                            <table align="center" border="0" cellpadding="0" cellspacing="0" style="margin: 24px auto; text-align: center; border-collapse: collapse;">
                                <tr>
                                    @foreach ($digits as $digit)
                                        <td style="padding: 0 6px;">
                                            <div class="otp-box" style="width: 52px; height: 60px; line-height: 60px; text-align: center; background-color: #f8fafc; border: 2px solid #e2e8f0; border-radius: 10px; font-family: 'Outfit', 'Inter', sans-serif; font-size: 32px; font-weight: 800; color: #f87d1f; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);">
                                                {{ $digit }}
                                            </div>
                                        </td>
                                    @endforeach
                                </tr>
                            </table>
                            
                            <!-- Styled Amber Warning Security Banner -->
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #fffbeb; border: 1px solid #fef3c7; border-radius: 10px; margin: 28px 0; border-collapse: separate;">
                                <tr>
                                    <td style="padding: 12px 16px;">
                                        <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                            <tr>
                                                <td width="20" valign="top" style="font-size: 16px; line-height: 1;">⚠️</td>
                                                <td style="font-size: 13px; line-height: 1.5; color: #b45309; padding-left: 8px; font-family: 'Inter', sans-serif;">
                                                    <strong>Security Notice:</strong> This code is valid for <strong>10 minutes</strong>. Please do not share this passcode with anyone.
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                            
                            <p style="font-size: 13px; line-height: 1.5; color: #94a3b8; margin: 0; text-align: center;">
                                If you did not make this request, you can safely ignore this email. Your password is still secure.
                            </p>
                            
                            <!-- Card Footer -->
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-top: 36px; padding-top: 24px; border-top: 1px solid #f1f5f9;">
                                <tr>
                                    <td align="center" style="font-family: 'Inter', sans-serif;">
                                        <p style="font-size: 12px; line-height: 1.6; color: #94a3b8; margin: 0 0 6px 0;">
                                            &copy; {{ date('Y') }} <strong>{{ config('app.name', 'Yash Mobile') }}</strong>. All rights reserved.
                                        </p>
                                        <p style="font-size: 11px; line-height: 1.4; color: #cbd5e1; margin: 0;">
                                            This is an automated security system notification. Please do not reply directly.
                                        </p>
                                    </td>
                                </tr>
                            </table>
                            
                        </td>
                    </tr>
                </table>
                
            </td>
        </tr>
    </table>
</body>
</html>
