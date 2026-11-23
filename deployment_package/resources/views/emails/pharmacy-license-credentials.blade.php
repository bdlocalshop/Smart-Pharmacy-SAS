<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Pharmacy License Credentials</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f1f5f9; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #1e293b; -webkit-font-smoothing: antialiased;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f1f5f9; padding: 32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" style="max-width: 600px; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); border: 1px solid #e2e8f0;" cellspacing="0" cellpadding="0">
                    
                    <!-- Header -->
                    <tr>
                        <td style="background-color: #0f172a; padding: 32px; text-align: center;">
                            <div style="font-size: 24px; font-weight: 800; color: #ffffff; letter-spacing: -0.5px;">
                                Smart <span style="color: #10b981;">Pharmacy</span>
                            </div>
                            <div style="font-size: 13px; color: #94a3b8; margin-top: 4px; font-weight: 500;">
                                Cloud Licensing & POS Device Management
                            </div>
                        </td>
                    </tr>

                    <!-- Body Content -->
                    <tr>
                        <td style="padding: 36px 32px;">
                            <h1 style="font-size: 20px; font-weight: 700; color: #0f172a; margin: 0 0 16px 0;">
                                @if($isRegenerated)
                                    Your Licensing Credentials Have Been Regenerated
                                @else
                                    Congratulations! Your Pharmacy Has Been Approved
                                @endif
                            </h1>

                            <p style="font-size: 14px; line-height: 1.6; color: #475569; margin: 0 0 24px 0;">
                                Hello <strong>{{ $pharmacy->owner_name }}</strong>,<br>
                                @if($isRegenerated)
                                    A new set of cryptographic licensing credentials has been issued for <strong>{{ $pharmacy->pharmacy_name }}</strong>. Please update your point-of-sale workstations with these new credentials immediately.
                                @else
                                    We are pleased to inform you that your application for <strong>{{ $pharmacy->pharmacy_name }}</strong> has been verified and approved. Below are your official cryptographic license keys required to activate your POS and local client applications.
                                @endif
                            </p>

                            <!-- Credentials Box -->
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color: #f8fafc; border: 1px solid #cbd5e1; border-radius: 8px; margin-bottom: 28px;">
                                <tr>
                                    <td style="padding: 20px;">
                                        <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: 0.5px; margin-bottom: 4px;">
                                            Pharmacy Business Details
                                        </div>
                                        <div style="font-size: 15px; font-weight: 700; color: #0f172a; margin-bottom: 2px;">
                                            {{ $pharmacy->pharmacy_name }}
                                        </div>
                                        <div style="font-size: 12px; color: #64748b; margin-bottom: 16px;">
                                            Trade License: <strong>{{ $pharmacy->trade_license_number }}</strong>
                                        </div>

                                        <hr style="border: none; border-top: 1px solid #e2e8f0; margin: 12px 0;">

                                        <!-- License Key -->
                                        <div style="margin-bottom: 16px;">
                                            <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #0369a1; letter-spacing: 0.5px; margin-bottom: 4px;">
                                                License Key (Public Client Identifier)
                                            </div>
                                            <div style="background-color: #e0f2fe; border: 1px solid #bae6fd; border-radius: 6px; padding: 10px 14px; font-family: Consolas, Monaco, monospace; font-size: 15px; font-weight: 700; color: #0369a1; letter-spacing: 0.5px;">
                                                {{ $license->license_key }}
                                            </div>
                                        </div>

                                        <!-- HMAC Client Secret -->
                                        <div style="margin-bottom: 16px;">
                                            <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #b45309; letter-spacing: 0.5px; margin-bottom: 4px;">
                                                HMAC Client Secret (Private Cryptographic Key)
                                            </div>
                                            <div style="background-color: #fef3c7; border: 1px solid #fde68a; border-radius: 6px; padding: 10px 14px; font-family: Consolas, Monaco, monospace; font-size: 13px; font-weight: 700; color: #92400e; word-break: break-all;">
                                                {{ $plainSecret }}
                                            </div>
                                            <div style="font-size: 11px; color: #92400e; margin-top: 4px;">
                                                ⚠️ Treat this secret like a master password. Never share it publicly.
                                            </div>
                                        </div>

                                        <!-- Validity & Hardware Lock -->
                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                            <tr>
                                                <td width="50%" style="vertical-align: top; padding-right: 8px;">
                                                    <div style="font-size: 11px; font-weight: 600; color: #64748b;">Valid Until</div>
                                                    <div style="font-size: 13px; font-weight: 600; color: #0f172a;">
                                                        {{ $license->valid_until ? $license->valid_until->format('F d, Y') : 'Active' }}
                                                    </div>
                                                </td>
                                                <td width="50%" style="vertical-align: top; padding-left: 8px;">
                                                    <div style="font-size: 11px; font-weight: 600; color: #64748b;">Hardware Binding</div>
                                                    <div style="font-size: 13px; font-weight: 600; color: #0f172a;">
                                                        @if($license->bound_device_uuid)
                                                            Locked to: {{ $license->bound_device_name ?: 'Bound Device' }}
                                                        @else
                                                            Unbound (Ready for first machine)
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>

                                    </td>
                                </tr>
                            </table>

                            <!-- Activation Steps -->
                            <h2 style="font-size: 14px; font-weight: 700; color: #0f172a; margin: 0 0 12px 0;">
                                How to Activate Your Workstation:
                            </h2>

                            <ol style="font-size: 13px; line-height: 1.6; color: #475569; margin: 0 0 24px 0; padding-left: 20px;">
                                <li style="margin-bottom: 8px;">Launch your local Smart Pharmacy Desktop or Mobile POS application.</li>
                                <li style="margin-bottom: 8px;">When prompted, enter your <strong>License Key</strong> and <strong>HMAC Client Secret</strong>.</li>
                                <li style="margin-bottom: 8px;">Your workstation's physical hardware UUID will bind to your license upon first successful validation.</li>
                            </ol>

                            <div style="background-color: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 8px; padding: 14px 16px; margin-bottom: 24px;">
                                <div style="font-size: 13px; font-weight: 600; color: #065f46; margin-bottom: 2px;">
                                    🔒 1 Pharmacy = 1 Workstation Policy
                                </div>
                                <div style="font-size: 12px; color: #047857; line-height: 1.5;">
                                    Each license is locked strictly to a single physical computer. If you change or replace your computer, contact your system administrator to reset the device binding.
                                </div>
                            </div>

                            <p style="font-size: 13px; line-height: 1.5; color: #64748b; margin: 0;">
                                If you did not request this license or have questions, please reach out to our support team at <a href="mailto:support@smartpharmacy.com" style="color: #10b981; text-decoration: none; font-weight: 500;">support@smartpharmacy.com</a>.
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8fafc; padding: 24px 32px; text-align: center; border-top: 1px solid #e2e8f0;">
                            <p style="font-size: 12px; color: #94a3b8; margin: 0 0 4px 0;">
                                &copy; {{ date('Y') }} Smart Pharmacy Engine. All rights reserved.
                            </p>
                            <p style="font-size: 11px; color: #cbd5e1; margin: 0;">
                                This is an automated notification. Please do not reply directly to this email.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>

