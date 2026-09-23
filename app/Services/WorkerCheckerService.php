<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Process;

class WorkerCheckerService
{

    public static function isRunning(): array
    {
        if(PHP_OS_FAMILY === 'Windows') {
            return self::checkWindowsWorker();
        }

        return self::checkLinuxSuperVisorWorker();
    }

    public static function checkWindowsWorker(): array
    {
        // 1. Cek langsung proses Windows menggunakan WMIC
        $process = Process::run('wmic process where "name=\'php.exe\'" get commandline');
        $output = $process->output();

        // 2. Jika WMIC tidak memberikan hasil/error (misal di Windows 11 baru), gunakan PowerShell
        if (empty(trim($output))) {
            $process = Process::run('powershell "Get-CimInstance Win32_Process -Filter \"name=\'php.exe\'\" | Select-Object -ExpandProperty CommandLine"');
            $output = $process->output();
        }
        // 3. Evaluasi dari eksekusi command Windows
        $isProcessFound = str_contains($output, 'queue:work') || str_contains($output, 'queue:listen');

        // 4. Fallback ke Cache Ping jika proses tidak terdeteksi via CLI tapi Job Ping masih jalan
        $lastPing = Cache::get('queue_worker_last_ping');
        $isPingActive = $lastPing && (now()->timestamp - $lastPing < 180);

        // Status akhir (True jika proses ditemukan ATAU ping masih segar)
        $isRunning = $isProcessFound || $isPingActive;

        // Tentukan pesan berdasarkan sumber kebenaran data
        if ($isProcessFound) {
            $message = 'Proses php artisan queue:work terdeteksi aktif di Windows'. ($isPingActive ? ' (Terakhir merespons: ' . now()->setTimestamp($lastPing)->diffForHumans() . ')' : '');
        } elseif ($isPingActive) {
            $lastSeen = now()->setTimestamp($lastPing)->diffForHumans();
            $message = "Worker aktif via Ping (Terakhir merespons: {$lastSeen})";
        } else {
            $message = 'Proses queue:work tidak ditemukan dan tidak ada respons ping';
        }

        return [
            'is_running' => $isRunning,
            'environment' => 'Local (Windows)',
            'message' => $message,
        ];
    }

    /**
     * Cek status Supervisor di Linux CWP 7 dengan fallback ke Cache Ping
     */
    private static function checkLinuxSuperVisorWorker(): array
    {
        // Coba cek langsung via supervisorctl jika diizinkan sudoers
        $process = Process::run('sudo /usr/bin/supervisorctl status');
        $output = $process->output();

        if ($process->successful() && !empty($output)) {
            $isRunning = str_contains($output, 'RUNNING');
            return [
                'is_running' => $isRunning,
                'environment' => 'Production (CWP 7 Supervisor)',
                'message' => $isRunning 
                    ? 'Service Supervisor CWP 7 status: RUNNING' 
                    : 'Service Supervisor CWP 7 status: STOPPED / FATAL',
            ];
        }

        // Fallback ke Cache Ping (jika supervisorctl diblokir/tanpa sudo)
        $lastPing = Cache::get('queue_worker_last_ping');
        $isRunning = $lastPing && (now()->timestamp - $lastPing < 180);

        return [
            'is_running' => $isRunning,
            'environment' => 'Production (CWP 7 Ping Fallback)',
            'message' => $isRunning 
                ? 'Worker aktif (Terakhir respons: ' . now()->setTimestamp($lastPing)->diffForHumans() . ')'
                : 'Worker mati (Tidak ada respons ping dalam 3 menit)',
        ];
    }

    /**
     * Eksekusi Aksi Worker (restart / start / stop)
     */
    public static function executeAction(string $action): array
    {
        // 1. LINGKUNGAN WINDOWS (Lokal)
        if (PHP_OS_FAMILY === 'Windows') {
            // TODO: Implementasi eksekusi aksi di Windows
            return [
                'success' => false,
                'message' => 'Aksi start/stop otomatis tidak didukung di Windows secara native (Gunakan NSSM / Terminal).'
            ];
        }

        // 2. LINGKUNGAN LINUX (Production CWP 7 Supervisor)
        $allowedActions = ['start', 'stop', 'restart'];
        if (!in_array($action, $allowedActions)) {
            return ['success' => false, 'message' => 'Aksi tidak valid!'];
        }

        // Jalankan perintah supervisorctl (Misal: sudo supervisorctl restart all atau nama worker spesifik)
        $process = Process::run("sudo /usr/bin/supervisorctl {$action} all");

        if ($process->successful()) {
            return [
                'success' => true,
                'message' => "Perintah Supervisor [{$action}] berhasil dieksekusi: " . trim($process->output())
            ];
        }

        return [
            'success' => false,
            'message' => "Gagal mengeksekusi Supervisor [{$action}]: " . trim($process->errorOutput())
        ];
    }
}