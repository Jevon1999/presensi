<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MemberApplyController extends Controller
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
     * Show the member application form or status.
     */
    public function showApply()
    {
        try {
            // Get offices for dropdown
            $officesResponse = $this->api()->get("{$this->apiUrl}/offices");
            $offices = $officesResponse->successful() ? ($officesResponse->json('data') ?? []) : [];

            // Get current member status
            $statusResponse = $this->api()->get("{$this->apiUrl}/member/my-status");
            $memberData = $statusResponse->successful() ? $statusResponse->json('data') : null;

            return Inertia::render('Member/Apply', [
                'offices' => $offices,
                'existingMember' => $memberData,
            ]);
        } catch (\Exception $e) {
            Log::error('Member apply page error: ' . $e->getMessage());
            return Inertia::render('Member/Apply', [
                'offices' => [],
                'existingMember' => null,
                'error' => 'Gagal memuat data.',
            ]);
        }
    }

    /**
     * Submit a member application.
     */
    public function submitApply(Request $request)
    {
        try {
            // Validate locally first to catch obvious errors
            $validated = $request->validate([
                'no_hp'                  => 'required|string',
                'office_id'              => 'required',
                'jenis_kelamin'          => 'required|in:L,P',
                'asal_sekolah'           => 'required|string',
                'jurusan'                => 'required|string',
                'tanggal_mulai_magang'   => 'required|date',
                'tanggal_selesai_magang' => 'nullable|date',
            ]);

            Log::info('Member apply request', ['data' => $validated]);

            $response = $this->api()->post("{$this->apiUrl}/member/apply", $validated);

            Log::info('Member apply API response', [
                'status' => $response->status(),
                'body'   => $response->json(),
            ]);

            if ($response->status() === 401) {
                session()->forget(['auth_token', 'user', 'member']);
                return redirect()->route('login')->with('error', 'Sesi telah berakhir.');
            }

            if ($response->status() === 422) {
                $errors = $response->json('errors', []);
                $msg = $response->json('message', 'Data tidak valid.');
                return back()->withErrors($errors)->with('error', $msg)->withInput();
            }

            if (!$response->successful()) {
                return back()->with('error', $response->json('message') ?: 'Gagal mengajukan pendaftaran. (HTTP ' . $response->status() . ')')->withInput();
            }

            // Update member session data
            $member = $response->json('data');
            if ($member) {
                session(['member' => [
                    'id' => $member['id'],
                    'nama_lengkap' => $member['nama_lengkap'],
                    'status' => $member['status'],
                    'status_aktif' => $member['status_aktif'],
                    'office' => $member['office'] ?? null,
                ]]);
            }

            return redirect('/member/pending')->with('success', 'Pengajuan berhasil dikirim!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e; // Let Laravel handle validation exceptions
        } catch (\Exception $e) {
            Log::error('Member apply error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Terjadi kesalahan. Silakan coba lagi.')->withInput();
        }
    }

    /**
     * Show the pending status page.
     */
    public function showPending()
    {
        try {
            $statusResponse = $this->api()->get("{$this->apiUrl}/member/my-status");
            $memberData = $statusResponse->successful() ? $statusResponse->json('data') : null;

            // If approved, update session and redirect
            if ($memberData && $memberData['status'] === 'approved') {
                session(['member' => [
                    'id' => $memberData['id'],
                    'nama_lengkap' => $memberData['nama_lengkap'],
                    'status' => $memberData['status'],
                    'status_aktif' => $memberData['status_aktif'],
                    'office' => $memberData['office'] ?? null,
                ]]);
                return redirect('/member/dashboard');
            }

            // If rejected, redirect to apply page to re-apply
            if ($memberData && $memberData['status'] === 'rejected') {
                session(['member' => null]);
                return redirect('/member/apply')->with('error', 'Pengajuan Anda ditolak. Alasan: ' . ($memberData['rejection_reason'] ?: 'Tidak ada alasan.'));
            }

            return Inertia::render('Member/Pending', [
                'member' => $memberData,
            ]);
        } catch (\Exception $e) {
            Log::error('Member pending error: ' . $e->getMessage());
            return Inertia::render('Member/Pending', [
                'member' => null,
            ]);
        }
    }
}
