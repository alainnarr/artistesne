<?php

namespace Database\Seeders;

use App\Database\Models\Activity;
use App\Database\Models\Discipline;
use Illuminate\Database\Seeder;

class ActivitiesSeeder extends Seeder
{
    public function run(): void
    {
        $disciplines = DisciplinesSeeder::getDisciplines();
        $count = 0;

        foreach ($disciplines as $arrDiscipline) {
            foreach ($arrDiscipline['activities'] as $activity) {
                $count++;
                $discipline = Discipline::where('code', $arrDiscipline['code'])->firstOrFail();
                $activity['discipline_id'] = $discipline->id;
                $code = $arrDiscipline['code'].'.'.$activity['code'];
                $activity['code'] = $code;
                Activity::firstOrCreate(['code' => $activity['code']], $activity);
            }
        }

        $this->command?->info('Activities seeded: '.$count.' activity(s).');
    }
}
