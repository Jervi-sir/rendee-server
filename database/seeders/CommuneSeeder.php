<?php

namespace Database\Seeders;

use App\Models\Commune;
use App\Models\Wilaya;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class CommuneSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $jsonPath = database_path('seeders/communes.json');

        if (! File::exists($jsonPath)) {
            $this->command?->warn("File not found: {$jsonPath}");

            return;
        }

        $rawJson = File::get($jsonPath);
        $data = json_decode($rawJson, true);

        if (! is_array($data)) {
            $this->command?->error('Invalid JSON in communes.json');

            return;
        }

        // Cache wilayas mapped by ID to quickly lookup wilaya_id -> wilaya_code
        $wilayasById = Wilaya::all()->keyBy('id');

        $rows = [];
        $now = now();

        foreach ($data as $code => $item) {
            $nom = $item['nom'] ?? null;
            $wilayaId = $item['wilaya_id'] ?? null;
            $postalCode = $item['code_postal'] ?? null;

            $wilaya = $wilayaId ? $wilayasById->get($wilayaId) : null;
            $wilayaCode = $wilaya?->code;

            $rows[] = [
                'wilaya_id' => $wilaya?->id ?? $wilayaId,
                'wilaya_code' => $wilayaCode,
                'code' => (string) $code,
                'postal_code' => $postalCode ? (string) $postalCode : null,
                'en' => $nom,
                'fr' => $nom,
                'ar' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // Upsert in chunks of 500
        foreach (array_chunk($rows, 500) as $chunk) {
            Commune::upsert(
                $chunk,
                ['code'],
                ['wilaya_id', 'wilaya_code', 'postal_code', 'en', 'fr', 'updated_at']
            );
        }

        $count = count($rows);
        $this->command?->info("Seeded {$count} communes successfully.");
    }
}
