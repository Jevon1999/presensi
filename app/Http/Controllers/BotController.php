<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class BotController extends Controller
{
    private $apiUrl;

    public function __construct()
    {
        $this->apiUrl = env('API_URL', 'https://api.globalintermedia.online/api');
    }

    private function token()
    {
        return session('auth_token');
    }

    private function api()
    {
        return Http::withToken($this->token())->timeout(15);
    }

    /**
     * WAHA HTTP client — uses the stored WAHA API key from bot_configs
     */
    private function waha(string $apiKey = null)
    {
        $wahaUrl = env('WAHA_API_URL', 'https://waha.globalintermedia.online');

        $client = Http::baseUrl($wahaUrl)->timeout(15);

        if ($apiKey) {
            $client = $client->withHeaders(['X-Api-Key' => $apiKey]);
        }

        return $client;
    }

    /**
     * Fetch WAHA config (session name + API key) from the backend API.
     * Returns [sessionName, apiKey] or a redirect response on failure.
     */
    private function getWahaConfig()
    {
        $configResp = $this->api()->get("{$this->apiUrl}/bot-configs");

        if ($configResp->status() === 401) {
            session()->forget(['auth_token', 'user']);
            return ['redirect' => redirect()->route('login')->with('error', 'Sesi Anda telah berakhir.')];
        }

        if (!$configResp->successful()) {
            Log::error('Failed to fetch bot-configs', ['status' => $configResp->status(), 'body' => $configResp->body()]);
            return ['error' => 'Gagal memuat konfigurasi bot. (HTTP ' . $configResp->status() . ')'];
        }

        $config = $configResp->json('data', []);
        $sessionName = $config['waha_session_name'] ?? 'default';
        $apiKey = $config['waha_api_key'] ?? '';

        if (empty($apiKey)) {
            Log::warning('WAHA API key is empty in bot-configs', ['config_keys' => array_keys($config)]);
            return ['error' => 'WAHA API Key belum dikonfigurasi. Hubungi administrator untuk mengatur API Key di konfigurasi bot.'];
        }

        return ['sessionName' => $sessionName, 'apiKey' => $apiKey];
    }

    /**
     * Bot Config page — fetches bot_configs from API
     */
    public function config(Request $request)
    {
        try {
            $response = $this->api()->get("{$this->apiUrl}/bot-configs");

            if ($response->status() === 401) {
                session()->forget(['auth_token', 'user']);
                return redirect()->route('login')->with('error', 'Sesi Anda telah berakhir.');
            }

            $config = $response->json('data', []);

            return Inertia::render('Bot/Config', [
                'config' => $config,
            ]);
        } catch (\Exception $e) {
            Log::error('Bot config error: ' . $e->getMessage());
            return Inertia::render('Bot/Config', [
                'config' => [],
                'error' => 'Gagal memuat konfigurasi bot.',
            ]);
        }
    }

    /**
     * Update bot_configs via API
     */
    public function updateConfig(Request $request)
    {
        try {
            $response = $this->api()->put("{$this->apiUrl}/bot-configs", $request->all());

            if ($response->status() === 401) {
                session()->forget(['auth_token', 'user']);
                return redirect()->route('login')->with('error', 'Sesi Anda telah berakhir.');
            }
            if ($response->status() === 422) {
                return back()->withErrors($response->json('errors', []))->with('error', $response->json('message', 'Data tidak valid.'));
            }
            if (!$response->successful()) {
                return back()->with('error', 'Gagal menyimpan konfigurasi. (' . $response->status() . ')');
            }

            return back()->with('success', 'Konfigurasi bot berhasil disimpan.');
        } catch (\Exception $e) {
            Log::error('Bot config update error: ' . $e->getMessage());
            return back()->with('error', 'Tidak dapat terhubung ke server.');
        }
    }

    /**
     * Check WAHA session status (proxy to WAHA)
     */
    public function status(Request $request)
    {
        try {
            // First get waha config from API for session name + api key
            $configResp = $this->api()->get("{$this->apiUrl}/bot-configs");
            $config = $configResp->json('data', []);

            $sessionName = $config['waha_session_name'] ?? 'default';
            $apiKey = $config['waha_api_key'] ?? '';

            $wahaResp = $this->waha($apiKey)->get("/api/sessions/{$sessionName}");

            if (!$wahaResp->successful()) {
                return response()->json([
                    'status' => 'UNKNOWN',
                    'error' => 'Gagal cek status WAHA. HTTP ' . $wahaResp->status(),
                ]);
            }

            $data = $wahaResp->json();

            return response()->json([
                'status' => $data['status'] ?? 'UNKNOWN',
                'name' => $data['name'] ?? $sessionName,
                'me' => $data['me'] ?? null,
                'engine' => $data['engine'] ?? null,
            ]);
        } catch (\Exception $e) {
            Log::error('WAHA status error: ' . $e->getMessage());
            return response()->json([
                'status' => 'ERROR',
                'error' => 'Tidak dapat terhubung ke WAHA server.',
            ]);
        }
    }

    /**
     * Start WAHA session
     */
    public function startSession(Request $request)
    {
        try {
            $configResp = $this->api()->get("{$this->apiUrl}/bot-configs");
            $config = $configResp->json('data', []);

            $sessionName = $config['waha_session_name'] ?? 'default';
            $apiKey = $config['waha_api_key'] ?? '';

            $wahaResp = $this->waha($apiKey)->post("/api/sessions/{$sessionName}/start", []);

            return response()->json($wahaResp->json());
        } catch (\Exception $e) {
            Log::error('WAHA start session error: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal memulai sesi WAHA.'], 500);
        }
    }

    /**
     * Stop WAHA session
     */
    public function stopSession(Request $request)
    {
        try {
            $configResp = $this->api()->get("{$this->apiUrl}/bot-configs");
            $config = $configResp->json('data', []);

            $sessionName = $config['waha_session_name'] ?? 'default';
            $apiKey = $config['waha_api_key'] ?? '';

            $wahaResp = $this->waha($apiKey)->post("/api/sessions/{$sessionName}/stop", []);

            return response()->json($wahaResp->json());
        } catch (\Exception $e) {
            Log::error('WAHA stop session error: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal menghentikan sesi WAHA.'], 500);
        }
    }

    /**
     * Get QR code for session
     */
    public function qrCode(Request $request)
    {
        try {
            $configResp = $this->api()->get("{$this->apiUrl}/bot-configs");
            $config = $configResp->json('data', []);

            $sessionName = $config['waha_session_name'] ?? 'default';
            $apiKey = $config['waha_api_key'] ?? '';

            $wahaResp = $this->waha($apiKey)->get("/api/{$sessionName}/auth/qr", [
                'format' => 'raw',
            ]);

            if (!$wahaResp->successful()) {
                return response()->json(['error' => 'QR code tidak tersedia.'], $wahaResp->status());
            }

            return response($wahaResp->body())
                ->header('Content-Type', $wahaResp->header('Content-Type', 'image/png'));
        } catch (\Exception $e) {
            Log::error('WAHA QR error: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal mendapatkan QR code.'], 500);
        }
    }

    /**
     * Lookup a member by phone number (for admin send-message validation)
     */
    public function lookupMember(Request $request)
    {
        $queryStr = strtolower(trim($request->query('phone', '')));
        
        if (strlen($queryStr) < 3) {
            return response()->json(['found' => false, 'members' => []]);
        }

        try {
            $response = $this->api()->get("{$this->apiUrl}/members", [
                'per_page' => 500,
            ]);

            if (!$response->successful()) {
                return response()->json(['found' => false, 'members' => [], 'error' => 'Gagal mengambil data member.']);
            }

            $members = $response->json('data', []);
            $matches = [];

            $queryPhone = preg_replace('/[^0-9]/', '', $queryStr);
            if (str_starts_with($queryPhone, '0')) {
                $queryPhone = '62' . substr($queryPhone, 1);
            }

            foreach ($members as $member) {
                if (($member['status'] ?? '') !== 'approved' || !($member['status_aktif'] ?? false)) {
                    continue;
                }

                $memberPhone = preg_replace('/[^0-9]/', '', $member['no_hp'] ?? '');
                if (str_starts_with($memberPhone, '0')) {
                    $memberPhone = '62' . substr($memberPhone, 1);
                }

                $nama = strtolower($member['nama_lengkap'] ?? '');
                
                // Match by name or phone
                if (str_contains($nama, $queryStr) || (!empty($queryPhone) && str_contains($memberPhone, $queryPhone))) {
                    $matches[] = [
                        'id' => $member['id'],
                        'nama_lengkap' => $member['nama_lengkap'],
                        'no_hp' => $member['no_hp'],
                        'status_aktif' => $member['status_aktif'] ?? false,
                    ];
                    
                    if (count($matches) >= 5) {
                        break;
                    }
                }
            }

            return response()->json([
                'found' => count($matches) > 0,
                'members' => $matches,
            ]);
        } catch (\Exception $e) {
            Log::error('lookupMember error: ' . $e->getMessage());
            return response()->json(['found' => false, 'members' => [], 'error' => 'Terjadi kesalahan.']);
        }
    }

    /**
     * Send single text message via WAHA — only to registered members
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'message' => 'required|string',
        ]);

        try {
            // Validate: phone must belong to an approved member
            $phone = preg_replace('/[^0-9]/', '', $request->phone);
            if (str_starts_with($phone, '0')) {
                $phone = '62' . substr($phone, 1);
            }

            $membersResp = $this->api()->get("{$this->apiUrl}/members", ['per_page' => 500]);

            if ($membersResp->status() === 401) {
                session()->forget(['auth_token', 'user']);
                return redirect()->route('login')->with('error', 'Sesi Anda telah berakhir.');
            }

            $members = $membersResp->json('data', []);
            $isMember = false;
            foreach ($members as $member) {
                $memberPhone = preg_replace('/[^0-9]/', '', $member['no_hp'] ?? '');
                if (str_starts_with($memberPhone, '0')) {
                    $memberPhone = '62' . substr($memberPhone, 1);
                }
                if ($memberPhone === $phone && ($member['status'] ?? '') === 'approved' && ($member['status_aktif'] ?? false)) {
                    $isMember = true;
                    break;
                }
            }

            if (!$isMember) {
                return back()->withErrors(['phone' => 'Nomor ini bukan member aktif. Pesan hanya dapat dikirim ke member yang sudah disetujui dan masih aktif.']);
            }

            // Fetch WAHA config with proper validation
            $wahaConfig = $this->getWahaConfig();
            if (isset($wahaConfig['redirect'])) {
                return $wahaConfig['redirect'];
            }
            if (isset($wahaConfig['error'])) {
                return back()->with('error', $wahaConfig['error']);
            }

            $sessionName = $wahaConfig['sessionName'];
            $apiKey = $wahaConfig['apiKey'];
            $chatId = $phone . '@c.us';

            $wahaResp = $this->waha($apiKey)->post('/api/sendText', [
                'session' => $sessionName,
                'chatId' => $chatId,
                'text' => $request->message,
            ]);

            if (!$wahaResp->successful()) {
                $errorDetail = $wahaResp->json('message', '');
                Log::error('WAHA sendText failed', [
                    'status' => $wahaResp->status(),
                    'body' => $wahaResp->body(),
                    'chatId' => $chatId,
                    'session' => $sessionName,
                ]);

                if ($wahaResp->status() === 401) {
                    return back()->with('error', 'WAHA API Key tidak valid. Periksa konfigurasi API Key pada bot settings.');
                }

                return back()->with('error', 'Gagal mengirim pesan. (' . $wahaResp->status() . ($errorDetail ? ": $errorDetail" : '') . ')');
            }

            return back()->with('success', 'Pesan berhasil dikirim ke ' . $request->phone);
        } catch (\Exception $e) {
            Log::error('WAHA sendMessage error: ' . $e->getMessage());
            return back()->with('error', 'Tidak dapat terhubung ke WAHA server.');
        }
    }

    /**
     * Broadcast text message to all active members
     */
    public function broadcastMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        try {
            // Fetch bot config + members in parallel
            $responses = Http::pool(fn($pool) => [
                $pool->as('config')->withToken($this->token())->timeout(15)->get("{$this->apiUrl}/bot-configs"),
                $pool->as('members')->withToken($this->token())->timeout(15)->get("{$this->apiUrl}/members", ['per_page' => 500]),
            ]);

            if ($responses['config']->status() === 401 || $responses['members']->status() === 401) {
                session()->forget(['auth_token', 'user']);
                return redirect()->route('login')->with('error', 'Sesi Anda telah berakhir.');
            }

            if (!$responses['config']->successful()) {
                Log::error('Broadcast: failed to fetch bot-configs', ['status' => $responses['config']->status()]);
                return back()->with('error', 'Gagal memuat konfigurasi bot. (HTTP ' . $responses['config']->status() . ')');
            }

            $config = $responses['config']->json('data', []);
            $members = $responses['members']->json('data', []);

            $sessionName = $config['waha_session_name'] ?? 'default';
            $apiKey = $config['waha_api_key'] ?? '';

            if (empty($apiKey)) {
                Log::warning('Broadcast: WAHA API key is empty in bot-configs', ['config_keys' => array_keys($config)]);
                return back()->with('error', 'WAHA API Key belum dikonfigurasi. Hubungi administrator untuk mengatur API Key di konfigurasi bot.');
            }

            $sent = 0;
            $failed = 0;
            $firstError = null;
            $wahaUrl = env('WAHA_API_URL', 'https://waha.globalintermedia.online');

            foreach ($members as $member) {
                if (empty($member['no_hp']) || ($member['status_aktif'] ?? false) == false || ($member['status'] ?? '') !== 'approved') {
                    continue;
                }

                $phone = preg_replace('/[^0-9]/', '', $member['no_hp']);
                if (str_starts_with($phone, '0')) {
                    $phone = '62' . substr($phone, 1);
                }
                $chatId = $phone . '@c.us';

                try {
                    $resp = Http::baseUrl($wahaUrl)
                        ->timeout(10)
                        ->withHeaders(['X-Api-Key' => $apiKey])
                        ->post('/api/sendText', [
                            'session' => $sessionName,
                            'chatId' => $chatId,
                            'text' => $request->message,
                        ]);

                    if ($resp->successful()) {
                        $sent++;
                    } else {
                        $failed++;
                        if (!$firstError) {
                            $firstError = 'HTTP ' . $resp->status() . ': ' . $resp->json('message', $resp->body());
                        }
                        // If first message fails with 401, API key is wrong — abort early
                        if ($resp->status() === 401 && $sent === 0) {
                            Log::error('Broadcast: WAHA API key rejected (401)', ['session' => $sessionName]);
                            return back()->with('error', 'WAHA API Key tidak valid. Periksa konfigurasi API Key pada bot settings.');
                        }
                    }
                } catch (\Exception $e) {
                    $failed++;
                }

                // Small delay to avoid rate-limiting
                usleep(300000); // 300ms
            }

            if ($sent === 0 && $failed === 0) {
                return back()->with('error', 'Tidak ada member aktif yang memiliki nomor HP untuk dikirim broadcast.');
            }

            $msg = "Broadcast selesai. Terkirim: {$sent}, Gagal: {$failed}";
            if ($failed > 0 && $firstError) {
                $msg .= " (Error pertama: {$firstError})";
            }

            return back()->with($sent > 0 ? 'success' : 'error', $msg);
        } catch (\Exception $e) {
            Log::error('WAHA broadcast error: ' . $e->getMessage());
            return back()->with('error', 'Gagal melakukan broadcast.');
        }
    }

    /**
     * Get screenshot of WAHA session (connection proof)
     */
    public function screenshot(Request $request)
    {
        try {
            $configResp = $this->api()->get("{$this->apiUrl}/bot-configs");
            $config = $configResp->json('data', []);

            $sessionName = $config['waha_session_name'] ?? 'default';
            $apiKey = $config['waha_api_key'] ?? '';

            $wahaResp = $this->waha($apiKey)->get("/api/screenshot", [
                'session' => $sessionName,
            ]);

            if (!$wahaResp->successful()) {
                return response()->json(['error' => 'Gagal mengambil screenshot.'], $wahaResp->status());
            }

            return response($wahaResp->body())
                ->header('Content-Type', $wahaResp->header('Content-Type', 'image/png'));
        } catch (\Exception $e) {
            return response()->json(['error' => 'Gagal mengambil screenshot.'], 500);
        }
    }
}
