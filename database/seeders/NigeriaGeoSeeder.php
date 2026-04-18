<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class NigeriaGeoSeeder extends Seeder
{
    public function run(): void
    {
        $jsonPath = database_path('data/nigeria_geo.json');

        if (!File::exists($jsonPath)) {
            $this->command->error("Nigeria geo data file not found at: {$jsonPath}");
            $this->command->info("Please place nigeria_geo.json in database/data/");
            return;
        }

        $data = json_decode(File::get($jsonPath), true);

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('towns')->truncate();
        DB::table('lgas')->truncate();
        DB::table('states')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        foreach ($data as $stateData) {
            $stateId = DB::table('states')->insertGetId([
                'name' => $stateData['state'],
            ]);

            foreach ($stateData['lgas'] as $lgaData) {
                $lgaName = is_array($lgaData) ? $lgaData['name'] : $lgaData;
                $lgaId = DB::table('lgas')->insertGetId([
                    'state_id' => $stateId,
                    'name' => $lgaName,
                ]);

                if (is_array($lgaData) && isset($lgaData['towns'])) {
                    foreach ($lgaData['towns'] as $town) {
                        DB::table('towns')->insert([
                            'lga_id' => $lgaId,
                            'name' => $town,
                        ]);
                    }
                }
            }
        }

        $stateCount = DB::table('states')->count();
        $lgaCount = DB::table('lgas')->count();
        $townCount = DB::table('towns')->count();
        $this->command->info("Seeded: {$stateCount} states, {$lgaCount} LGAs, {$townCount} towns");
    }
}
