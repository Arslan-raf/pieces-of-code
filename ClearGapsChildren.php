<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Child;
class ClearGapsChildren extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ClearGapsChildren';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear Gaps in children table';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $childrenGaps = DB::table('children')
            // ->select('*')
            ->where('f_name', 'like', ' %')
            ->orWhere('f_name', 'like', '% ')
            ->orWhere('l_name', 'like', ' %')
            ->orWhere('l_name', 'like', '% ')
            ->orWhere('birth_certificate_number', 'like', '% ')
            ->orWhere('birth_certificate_number', 'like', ' %')
            ->get();
            /* dd($childrenGaps->toSql(), $childrenGaps->get()->count(), $childrenGaps->first(), [
                'f_name' => trim($childrenGaps->first()->f_name),
                'l_name' => trim($childrenGaps->first()->l_name),
                'birth_certificate_number' => trim($childrenGaps->first()->birth_certificate_number),
            ]); */
            // ->limit(1)->get();
            //dd($childrenGaps);

            foreach ($childrenGaps as $childrenGap) {
                $q = Child::where('children_id', '=', $childrenGap->children_id)
                    ->withTrashed()
                    ->update([
                        'f_name' => trim($childrenGap->f_name),
                        'l_name' => trim($childrenGap->l_name),
                        'birth_certificate_number' => trim($childrenGap->birth_certificate_number),
                    ]);
               // dump('Обдновляем '.$childrenGap->children_id);
            }

    }
}
