<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Visit;
use Illuminate\Support\Facades\Http;

class ProcessGeoLocation extends Command
{
    /**
     * Numele comenzii (o vom folosi în Kernel)
     */
    protected $signature = 'visits:process-geo';

    /**
     * Descrierea comenzii
     */
    protected $description = 'Actualizează locația (țara/orașul) pentru vizitele care nu le au setate.';

    public function handle()
    {
        $this->info('--> Încep procesarea locațiilor...');

        // 1. Selectăm IP-urile unice care nu au țară și nu sunt locale
        // Folosim DISTINCT pentru a nu interoga API-ul de 100 de ori pentru același IP
        $ipsToProcess = Visit::select('ip')
            ->whereNull('country')
            ->whereNotNull('ip')
            ->where('ip', '!=', '127.0.0.1')
            ->where('ip', '!=', '::1')
            ->distinct()
            ->limit(40) // Limităm la 40 pe tură ca să nu depășim limita API-ului gratuit (45 req/min)
            ->pluck('ip');

        if ($ipsToProcess->isEmpty()) {
            $this->info('Nu există vizite noi de procesat.');
            return;
        }

        $this->info('IP-uri găsite: ' . $ipsToProcess->count());

        foreach ($ipsToProcess as $ip) {
            try {
                // 2. Interogăm API-ul
                $response = Http::timeout(3)->get("http://ip-api.com/json/{$ip}");
                
                if ($response->successful()) {
                    $geo = $response->json();

                    if (isset($geo['status']) && $geo['status'] === 'success') {
                        
                        // 3. Actualizăm TOATE vizitele cu acest IP care nu au țara setată
                        Visit::where('ip', $ip)
                            ->whereNull('country')
                            ->update([
                                'country' => $geo['country'] ?? 'Unknown',
                                'city'    => $geo['city'] ?? 'Unknown',
                            ]);
                        
                        $this->info("Actualizat {$ip}: {$geo['country']}");
                    }
                }
                
                // 4. Pauză obligatorie pentru a nu fi blocați de API (rate limiting)
                sleep(2); 

            } catch (\Exception $e) {
                $this->error("Eroare la IP {$ip}: " . $e->getMessage());
            }
        }

        $this->info('--> Procesare finalizată.');
    }
}