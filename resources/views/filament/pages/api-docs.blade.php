<x-filament-panels::page>
    <style>
        .fi-section-header-icon,
        .fi-section-header-icon svg,
        .fi-section-header svg,
        .fi-section > header svg {
            width: 1.5rem !important;
            height: 1.5rem !important;
            max-width: 1.5rem !important;
            max-height: 1.5rem !important;
            flex-shrink: 0 !important;
            display: inline-block !important;
        }
    </style>

    <div style="display: flex; flex-direction: column; gap: 24px;">

        {{-- Section 1: Security Architecture Overview --}}
        <x-filament::section>
            <x-slot name="heading">
                <div style="display: flex; items-center; gap: 8px;">
                    <svg style="width: 20px; height: 20px; color: #10b981; flex-shrink: 0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    <span>Gated Admin API Reference & Cryptographic Authentication</span>
                </div>
            </x-slot>
            <x-slot name="description">
                All client requests against the Smart Pharmacy API are protected by HMAC-SHA256 signatures, strict timestamp replay tolerance, and a strict 1-device hardware lock.
            </x-slot>
            <x-slot name="afterHeader">
                <x-filament::badge color="success">
                    HMAC-SHA256 Enforced
                </x-filament::badge>
            </x-slot>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; margin-top: 8px;">
                <div style="background: rgba(16, 185, 129, 0.06); border: 1px solid rgba(16, 185, 129, 0.2); border-radius: 8px; padding: 14px 16px;">
                    <div style="font-weight: 600; font-size: 13px; color: #10b981; margin-bottom: 4px;">
                        Hardware Binding on Signup
                    </div>
                    <div style="font-size: 12px; opacity: 0.85;">
                        Workstations register with <code>device_uuid</code> via <code>/api/v1/pharmacy/register</code>. Device is bound immediately.
                    </div>
                </div>

                <div style="background: rgba(59, 130, 246, 0.06); border: 1px solid rgba(59, 130, 246, 0.2); border-radius: 8px; padding: 14px 16px;">
                    <div style="font-weight: 600; font-size: 13px; color: #3b82f6; margin-bottom: 4px;">
                        Strict Hardware Lock on Signin
                    </div>
                    <div style="font-size: 12px; opacity: 0.85;">
                        1 Pharmacy = 1 Device UUID. Mismatches strictly rejected with HTTP 403 <code>DEVICE_MISMATCH</code>.
                    </div>
                </div>

                <div style="background: rgba(168, 85, 247, 0.06); border: 1px solid rgba(168, 85, 247, 0.2); border-radius: 8px; padding: 14px 16px;">
                    <div style="font-weight: 600; font-size: 13px; color: #a855f7; margin-bottom: 4px;">
                        Adaptive Rate Limiting & Anti-Replay
                    </div>
                    <div style="font-size: 12px; opacity: 0.85;">
                        Cutoff &plusmn;300s timestamp window via <code>X-Timestamp</code>. Ceiling: 60 req/min with HMAC auth.
                    </div>
                </div>
            </div>
        </x-filament::section>

        {{-- Section 2: Local Device App Registration & Hardware Binding --}}
        <x-filament::section>
            <x-slot name="heading">
                <div style="display: flex; items-center; gap: 8px;">
                    <svg style="width: 20px; height: 20px; color: #10b981; flex-shrink: 0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                    </svg>
                    <span>Local Device App Registration & Hardware Binding</span>
                </div>
            </x-slot>
            <x-slot name="description">
                Endpoint: <code>POST /api/v1/pharmacy/register</code> (alias: <code>POST /api/v1/register</code>). Registers a new pharmacy directly from a desktop/mobile client and permanently binds the hardware UUID.
            </x-slot>

            <div x-data="{ regTab: 'js' }" style="display: flex; flex-direction: column; gap: 12px;">
                <div style="display: flex; gap: 8px; border-bottom: 1px solid rgba(128,128,128,0.2); padding-bottom: 8px; flex-wrap: wrap;">
                    <button
                        type="button"
                        @click="regTab = 'js'"
                        :style="regTab === 'js' ? 'background: #10b981; color: #ffffff;' : 'background: rgba(128,128,128,0.1); color: inherit;'"
                        style="padding: 6px 14px; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; border: none; transition: all 0.15s ease;"
                    >
                        JavaScript / Node.js
                    </button>
                    <button
                        type="button"
                        @click="regTab = 'dart'"
                        :style="regTab === 'dart' ? 'background: #10b981; color: #ffffff;' : 'background: rgba(128,128,128,0.1); color: inherit;'"
                        style="padding: 6px 14px; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; border: none; transition: all 0.15s ease;"
                    >
                        Dart / Flutter
                    </button>
                    <button
                        type="button"
                        @click="regTab = 'csharp'"
                        :style="regTab === 'csharp' ? 'background: #10b981; color: #ffffff;' : 'background: rgba(128,128,128,0.1); color: inherit;'"
                        style="padding: 6px 14px; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; border: none; transition: all 0.15s ease;"
                    >
                        C# (.NET / WPF)
                    </button>
                    <button
                        type="button"
                        @click="regTab = 'python'"
                        :style="regTab === 'python' ? 'background: #10b981; color: #ffffff;' : 'background: rgba(128,128,128,0.1); color: inherit;'"
                        style="padding: 6px 14px; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; border: none; transition: all 0.15s ease;"
                    >
                        Python (Requests)
                    </button>
                </div>

                <div x-show="regTab === 'js'">
                    <pre style="background: #090d16; color: #e2e8f0; padding: 16px; border-radius: 8px; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 12px; line-height: 1.5; overflow-x: auto; margin: 0; border: 1px solid rgba(255,255,255,0.08);"><code>const axios = require('axios');

async function registerDeviceApp(deviceHardwareUuid) {
  const payload = {
    pharmacy_name: 'Evergreen Care Pharmacy',
    trade_license_number: 'TL-2026-DHK-8891',
    pharmacy_address: 'House 12, Road 4, Dhanmondi, Dhaka',
    pharmacy_phone: '+8801711223344',
    opening_hours: '8:00AM-1:00PM, 2:00PM-5:00PM, 6:00PM-11:30PM',
    map_location_url: 'https://maps.google.com/?q=23.7461,90.3742',
    owner_name: 'Dr. Masud Rahman',
    owner_phone: '+8801811556677',
    owner_email: 'masud@evergreen.com',
    owner_address: 'Dhanmondi R/A, Dhaka',
    password: 'SecurePassword123!',
    password_confirmation: 'SecurePassword123!',
    // Device hardware parameters bound to this account
    device_uuid: deviceHardwareUuid,
    device_name: 'Counter-PC-01',
    platform: 'desktop',
    app_version: '1.2.0'
  };

  const response = await axios.post('{{ url('/api/v1/pharmacy/register') }}', payload);
  
  // Save credentials securely in local encrypted store:
  const { credentials, bound_device } = response.data.data;
  console.log('Hardware bound:', bound_device.device_uuid);
  console.log('License Key:', credentials.license_key);
  console.log('HMAC Secret:', credentials.client_secret);
  return response.data;
}</code></pre>
                </div>

                <div x-show="regTab === 'dart'" style="display: none;">
                    <pre style="background: #090d16; color: #e2e8f0; padding: 16px; border-radius: 8px; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 12px; line-height: 1.5; overflow-x: auto; margin: 0; border: 1px solid rgba(255,255,255,0.08);"><code>import 'dart:convert';
import 'package:http/http.dart' as http;

Future&lt;Map&lt;String, dynamic&gt;&gt; registerDeviceApp({
  required String deviceUuid,
  required String pharmacyName,
  required String tradeLicense,
  required String phone,
  required String address,
  String? openingHours,
  required String ownerName,
  required String ownerPhone,
  required String password,
}) async {
  final payload = {
    'pharmacy_name': pharmacyName,
    'trade_license_number': tradeLicense,
    'pharmacy_address': address,
    'pharmacy_phone': phone,
    'opening_hours': openingHours ?? '8:00AM-1:00PM, 2:00PM-5:00PM, 6:00PM-11:30PM',
    'owner_name': ownerName,
    'owner_phone': ownerPhone,
    'password': password,
    'password_confirmation': password,
    'device_uuid': deviceUuid,
    'device_name': 'Handheld-Tablet-01',
    'platform': 'desktop',
    'app_version': '1.2.0',
  };

  final res = await http.post(
    Uri.parse('{{ url('/api/v1/pharmacy/register') }}'),
    headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
    body: jsonEncode(payload),
  );

  return jsonDecode(res.body);
}</code></pre>
                </div>

                <div x-show="regTab === 'csharp'" style="display: none;">
                    <pre style="background: #090d16; color: #e2e8f0; padding: 16px; border-radius: 8px; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 12px; line-height: 1.5; overflow-x: auto; margin: 0; border: 1px solid rgba(255,255,255,0.08);"><code>using System.Net.Http;
using System.Text;
using System.Text.Json;
using System.Threading.Tasks;

public class SmartPharmacyRegistration {
    public static async Task&lt;string&gt; RegisterDeviceAsync(string deviceUuid) {
        var payloadObj = new {
            pharmacy_name = "Apex Care Pharmacy",
            trade_license_number = "TL-2026-DHK-9921",
            pharmacy_address = "Sector 3, Uttara, Dhaka",
            pharmacy_phone = "+8801700112233",
            opening_hours = "8:00AM-1:00PM, 2:00PM-5:00PM, 6:00PM-11:30PM",
            owner_name = "Mr. Rafiqul Islam",
            owner_phone = "+8801800112233",
            password = "SecretPassword123!",
            password_confirmation = "SecretPassword123!",
            device_uuid = deviceUuid,
            device_name = "Front-Counter-PC-Win11",
            platform = "desktop",
            app_version = "1.2.0"
        };

        using var client = new HttpClient();
        var content = new StringContent(JsonSerializer.Serialize(payloadObj), Encoding.UTF8, "application/json");
        var response = await client.PostAsync("{{ url('/api/v1/pharmacy/register') }}", content);
        return await response.Content.ReadAsStringAsync();
    }
}</code></pre>
                </div>

                <div x-show="regTab === 'python'" style="display: none;">
                    <pre style="background: #090d16; color: #e2e8f0; padding: 16px; border-radius: 8px; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 12px; line-height: 1.5; overflow-x: auto; margin: 0; border: 1px solid rgba(255,255,255,0.08);"><code>import requests

def register_device(device_uuid: str):
    payload = {
        "pharmacy_name": "Apex Care Pharmacy",
        "trade_license_number": "TL-2026-DHK-9921",
        "pharmacy_address": "Sector 3, Uttara, Dhaka",
        "pharmacy_phone": "+8801700112233",
        "opening_hours": "8:00AM-1:00PM, 2:00PM-5:00PM, 6:00PM-11:30PM",
        "owner_name": "Mr. Rafiqul Islam",
        "owner_phone": "+8801800112233",
        "password": "SecretPassword123!",
        "password_confirmation": "SecretPassword123!",
        "device_uuid": device_uuid,
        "device_name": "Terminal-Linux-01",
        "platform": "desktop",
        "app_version": "1.2.0"
    }

    response = requests.post("{{ url('/api/v1/pharmacy/register') }}", json=payload)
    return response.json()</code></pre>
                </div>
            </div>
        </x-filament::section>

        {{-- Section 2.5: Verify Owner Credentials from Unbound Machine (New Machine Diagnostic) --}}
        <x-filament::section>
            <x-slot name="heading">
                <div style="display: flex; items-center; gap: 8px;">
                    <svg style="width: 20px; height: 20px; color: #f59e0b; flex-shrink: 0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                    </svg>
                    <span>Check Owner Credentials (New / Unbound Machine)</span>
                </div>
            </x-slot>
            <x-slot name="description">
                Endpoint: <code>POST /api/v1/pharmacy/verify-credentials</code> (alias: <code>POST /api/v1/owner/verify-credentials</code>).
                Enables a new workstation to verify owner credentials, inspect pharmacy & license status, and evaluate device lock without exposing cryptographic secrets or requiring prior HMAC signatures.
            </x-slot>

            <div x-data="{ checkTab: 'js' }" style="display: flex; flex-direction: column; gap: 12px;">
                <div style="display: flex; gap: 8px; border-bottom: 1px solid rgba(128,128,128,0.2); padding-bottom: 8px; flex-wrap: wrap;">
                    <button
                        type="button"
                        @click="checkTab = 'js'"
                        :style="checkTab === 'js' ? 'background: #f59e0b; color: #ffffff;' : 'background: rgba(128,128,128,0.1); color: inherit;'"
                        style="padding: 6px 14px; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; border: none; transition: all 0.15s ease;"
                    >
                        JavaScript / Node.js
                    </button>
                    <button
                        type="button"
                        @click="checkTab = 'dart'"
                        :style="checkTab === 'dart' ? 'background: #f59e0b; color: #ffffff;' : 'background: rgba(128,128,128,0.1); color: inherit;'"
                        style="padding: 6px 14px; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; border: none; transition: all 0.15s ease;"
                    >
                        Dart / Flutter
                    </button>
                    <button
                        type="button"
                        @click="checkTab = 'csharp'"
                        :style="checkTab === 'csharp' ? 'background: #f59e0b; color: #ffffff;' : 'background: rgba(128,128,128,0.1); color: inherit;'"
                        style="padding: 6px 14px; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; border: none; transition: all 0.15s ease;"
                    >
                        C# (.NET / WPF)
                    </button>
                    <button
                        type="button"
                        @click="checkTab = 'python'"
                        :style="checkTab === 'python' ? 'background: #f59e0b; color: #ffffff;' : 'background: rgba(128,128,128,0.1); color: inherit;'"
                        style="padding: 6px 14px; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; border: none; transition: all 0.15s ease;"
                    >
                        Python (Requests)
                    </button>
                </div>

                <div x-show="checkTab === 'js'">
                    <pre style="background: #090d16; color: #e2e8f0; padding: 16px; border-radius: 8px; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 12px; line-height: 1.5; overflow-x: auto; margin: 0; border: 1px solid rgba(255,255,255,0.08);"><code>const axios = require('axios');

async function checkOwnerOnNewMachine(identifier, password, newDeviceUuid) {
  const payload = {
    identifier: identifier, // trade_license, owner_email, owner_phone, or license_key
    password: password,
    device_uuid: newDeviceUuid // optional: to check lock state for this machine
  };

  const response = await axios.post('{{ url('/api/v1/pharmacy/verify-credentials') }}', payload);
  const { pharmacy, owner, license_summary, hardware_lock } = response.data.data;

  console.log('Pharmacy:', pharmacy.pharmacy_name);
  console.log('Owner:', owner.name, owner.email);
  console.log('Is Locked to Another Device:', hardware_lock.is_locked_to_device);
  console.log('Lock Notice:', hardware_lock.notice);
  
  // Note: secrets (client_secret, hashes) are strictly excluded from response.
  return response.data;
}</code></pre>
                </div>

                <div x-show="checkTab === 'dart'" style="display: none;">
                    <pre style="background: #090d16; color: #e2e8f0; padding: 16px; border-radius: 8px; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 12px; line-height: 1.5; overflow-x: auto; margin: 0; border: 1px solid rgba(255,255,255,0.08);"><code>import 'dart:convert';
import 'package:http/http.dart' as http;

Future&lt;Map&lt;String, dynamic&gt;&gt; verifyOwnerFromNewMachine({
  required String identifier,
  required String password,
  String? deviceUuid,
}) async {
  final res = await http.post(
    Uri.parse('{{ url('/api/v1/pharmacy/verify-credentials') }}'),
    headers: {'Content-Type': 'application/json', 'Accept': 'application/json'},
    body: jsonEncode({
      'identifier': identifier,
      'password': password,
      if (deviceUuid != null) 'device_uuid': deviceUuid,
    }),
  );

  return jsonDecode(res.body);
}</code></pre>
                </div>

                <div x-show="checkTab === 'csharp'" style="display: none;">
                    <pre style="background: #090d16; color: #e2e8f0; padding: 16px; border-radius: 8px; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 12px; line-height: 1.5; overflow-x: auto; margin: 0; border: 1px solid rgba(255,255,255,0.08);"><code>using System.Net.Http;
using System.Text;
using System.Text.Json;
using System.Threading.Tasks;

public class OwnerCredentialCheck {
    public static async Task&lt;string&gt; VerifyOwnerAsync(string identifier, string password, string deviceUuid) {
        using var client = new HttpClient();
        var payloadObj = new {
            identifier = identifier,
            password = password,
            device_uuid = deviceUuid
        };
        var content = new StringContent(JsonSerializer.Serialize(payloadObj), Encoding.UTF8, "application/json");
        var response = await client.PostAsync("{{ url('/api/v1/pharmacy/verify-credentials') }}", content);
        return await response.Content.ReadAsStringAsync();
    }
}</code></pre>
                </div>

                <div x-show="checkTab === 'python'" style="display: none;">
                    <pre style="background: #090d16; color: #e2e8f0; padding: 16px; border-radius: 8px; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 12px; line-height: 1.5; overflow-x: auto; margin: 0; border: 1px solid rgba(255,255,255,0.08);"><code>import requests

def check_owner_credentials(identifier: str, password: str, device_uuid: str = None):
    payload = {
        "identifier": identifier,
        "password": password,
    }
    if device_uuid:
        payload["device_uuid"] = device_uuid

    response = requests.post("{{ url('/api/v1/pharmacy/verify-credentials') }}", json=payload)
    return response.json()</code></pre>
                </div>
            </div>
        </x-filament::section>

        {{-- Section 3: Client Signin, HMAC & Validation Reference in 4 Languages --}}
        <x-filament::section>
            <x-slot name="heading">
                <div style="display: flex; items-center; gap: 8px;">
                    <svg style="width: 20px; height: 20px; color: #6366f1; flex-shrink: 0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                    </svg>
                    <span>Client-Side Signature Implementations</span>
                </div>
            </x-slot>
            <x-slot name="description">
                Signature formula: <code>hash_hmac('sha256', client_id + timestamp + payload_json, client_secret)</code>
            </x-slot>

            <div x-data="{ tab: 'js' }" style="display: flex; flex-direction: column; gap: 12px;">
                <div style="display: flex; gap: 8px; border-bottom: 1px solid rgba(128,128,128,0.2); padding-bottom: 8px; flex-wrap: wrap;">
                    <button
                        type="button"
                        @click="tab = 'js'"
                        :style="tab === 'js' ? 'background: #6366f1; color: #ffffff;' : 'background: rgba(128,128,128,0.1); color: inherit;'"
                        style="padding: 6px 14px; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; border: none; transition: all 0.15s ease;"
                    >
                        JavaScript / Node.js
                    </button>
                    <button
                        type="button"
                        @click="tab = 'dart'"
                        :style="tab === 'dart' ? 'background: #6366f1; color: #ffffff;' : 'background: rgba(128,128,128,0.1); color: inherit;'"
                        style="padding: 6px 14px; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; border: none; transition: all 0.15s ease;"
                    >
                        Dart / Flutter
                    </button>
                    <button
                        type="button"
                        @click="tab = 'csharp'"
                        :style="tab === 'csharp' ? 'background: #6366f1; color: #ffffff;' : 'background: rgba(128,128,128,0.1); color: inherit;'"
                        style="padding: 6px 14px; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; border: none; transition: all 0.15s ease;"
                    >
                        C# (.NET / WPF)
                    </button>
                    <button
                        type="button"
                        @click="tab = 'python'"
                        :style="tab === 'python' ? 'background: #6366f1; color: #ffffff;' : 'background: rgba(128,128,128,0.1); color: inherit;'"
                        style="padding: 6px 14px; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; border: none; transition: all 0.15s ease;"
                    >
                        Python (Requests)
                    </button>
                </div>

                <div x-show="tab === 'js'">
                    <pre style="background: #090d16; color: #e2e8f0; padding: 16px; border-radius: 8px; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 12px; line-height: 1.5; overflow-x: auto; margin: 0; border: 1px solid rgba(255,255,255,0.08);"><code>const crypto = require('crypto');
const axios = require('axios');

async function validateLicense(clientId, clientSecret, licenseKey, deviceUuid) {
  const timestamp = Math.floor(Date.now() / 1000);
  const payload = {
    license_key: licenseKey,
    device_uuid: deviceUuid,
    device_name: 'Counter-PC-01',
    platform: 'desktop',
    app_version: '1.2.0'
  };

  const payloadString = JSON.stringify(payload);
  const dataToSign = `${clientId}${timestamp}${payloadString}`;
  const signature = crypto.createHmac('sha256', clientSecret).update(dataToSign).digest('hex');

  const response = await axios.post('{{ url('/api/v1/license/validate') }}', payload, {
    headers: {
      'Content-Type': 'application/json',
      'X-Client-Id': clientId,
      'X-Timestamp': timestamp.toString(),
      'X-Signature': signature
    }
  });

  return response.data;
}</code></pre>
                </div>

                <div x-show="tab === 'dart'" style="display: none;">
                    <pre style="background: #090d16; color: #e2e8f0; padding: 16px; border-radius: 8px; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 12px; line-height: 1.5; overflow-x: auto; margin: 0; border: 1px solid rgba(255,255,255,0.08);"><code>import 'dart:convert';
import 'package:crypto/crypto.dart';
import 'package:http/http.dart' as http;

Future&lt;Map&lt;String, dynamic&gt;&gt; validateLicense({
  required String clientId,
  required String clientSecret,
  required String licenseKey,
  required String deviceUuid,
}) async {
  final timestamp = (DateTime.now().millisecondsSinceEpoch / 1000).floor();
  final payload = {
    'license_key': licenseKey,
    'device_uuid': deviceUuid,
    'device_name': 'Handheld-Tablet-01',
    'platform': 'mobile',
    'app_version': '1.2.0',
  };

  final payloadJson = jsonEncode(payload);
  final dataToSign = '$clientId$timestamp$payloadJson';
  final hmac = Hmac(sha256, utf8.encode(clientSecret));
  final signature = hmac.convert(utf8.encode(dataToSign)).toString();

  final res = await http.post(
    Uri.parse('{{ url('/api/v1/license/validate') }}'),
    headers: {
      'Content-Type': 'application/json',
      'X-Client-Id': clientId,
      'X-Timestamp': timestamp.toString(),
      'X-Signature': signature,
    },
    body: payloadJson,
  );

  return jsonDecode(res.body);
}</code></pre>
                </div>

                <div x-show="tab === 'csharp'" style="display: none;">
                    <pre style="background: #090d16; color: #e2e8f0; padding: 16px; border-radius: 8px; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 12px; line-height: 1.5; overflow-x: auto; margin: 0; border: 1px solid rgba(255,255,255,0.08);"><code>using System;
using System.Net.Http;
using System.Security.Cryptography;
using System.Text;
using System.Text.Json;
using System.Threading.Tasks;

public class SmartPharmacyClient {
    public static async Task&lt;string&gt; ValidateLicenseAsync(string clientId, string clientSecret, string licenseKey, string deviceUuid) {
        long timestamp = DateTimeOffset.UtcNow.ToUnixTimeSeconds();
        var payloadObj = new {
            license_key = licenseKey,
            device_uuid = deviceUuid,
            device_name = "Counter-PC-Win11",
            platform = "desktop",
            app_version = "1.2.0"
        };
        string payloadJson = JsonSerializer.Serialize(payloadObj);
        string dataToSign = $"{clientId}{timestamp}{payloadJson}";

        using var hmac = new HMACSHA256(Encoding.UTF8.GetBytes(clientSecret));
        byte[] hash = hmac.ComputeHash(Encoding.UTF8.GetBytes(dataToSign));
        string signature = Convert.ToHexString(hash).ToLowerInvariant();

        using var client = new HttpClient();
        var request = new HttpRequestMessage(HttpMethod.Post, "{{ url('/api/v1/license/validate') }}") {
            Content = new StringContent(payloadJson, Encoding.UTF8, "application/json")
        };
        request.Headers.Add("X-Client-Id", clientId);
        request.Headers.Add("X-Timestamp", timestamp.ToString());
        request.Headers.Add("X-Signature", signature);

        var response = await client.SendAsync(request);
        return await response.Content.ReadAsStringAsync();
    }
}</code></pre>
                </div>

                <div x-show="tab === 'python'" style="display: none;">
                    <pre style="background: #090d16; color: #e2e8f0; padding: 16px; border-radius: 8px; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 12px; line-height: 1.5; overflow-x: auto; margin: 0; border: 1px solid rgba(255,255,255,0.08);"><code>import time
import json
import hmac
import hashlib
import requests

def validate_license(client_id: str, client_secret: str, license_key: str, device_uuid: str):
    timestamp = int(time.time())
    payload = {
        "license_key": license_key,
        "device_uuid": device_uuid,
        "device_name": "Terminal-Linux-01",
        "platform": "desktop",
        "app_version": "1.2.0"
    }
    payload_json = json.dumps(payload, separators=(',', ':'))
    data_to_sign = f"{client_id}{timestamp}{payload_json}"
    
    signature = hmac.new(
        client_secret.encode('utf-8'),
        data_to_sign.encode('utf-8'),
        hashlib.sha256
    ).hexdigest()

    headers = {
        "Content-Type": "application/json",
        "X-Client-Id": client_id,
        "X-Timestamp": str(timestamp),
        "X-Signature": signature
    }

    res = requests.post("{{ url('/api/v1/license/validate') }}", data=payload_json, headers=headers)
    return res.json()</code></pre>
                </div>
            </div>
        </x-filament::section>

        {{-- Section 4: Interactive OpenAPI 3.1 Documentation via Scalar --}}
        <x-filament::section>
            <x-slot name="heading">
                <div style="display: flex; items-center; gap: 8px;">
                    <svg style="width: 20px; height: 20px; color: #0284c7; flex-shrink: 0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span>Interactive OpenAPI 3.1 Reference & Testing Console</span>
                </div>
            </x-slot>
            <x-slot name="description">
                Explore schemas, test API requests, and review endpoint contracts in the embedded Scalar documentation engine.
            </x-slot>

            <div style="border-radius: 8px; overflow: hidden; border: 1px solid rgba(128,128,128,0.2); background: #0b0f19;">
                <iframe
                    src="{{ route('admin.api-docs.embed') }}"
                    title="Smart Pharmacy Scalar API Reference"
                    style="width: 100%; height: 820px; border: 0; display: block;"
                    loading="lazy"
                ></iframe>
            </div>
        </x-filament::section>

    </div>
</x-filament-panels::page>
