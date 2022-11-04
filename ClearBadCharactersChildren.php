<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Child;
use Illuminate\Support\Str;
class ClearBadCharactersChildren extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ClearBadCharactersChildren';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        ->whereRaw(" `f_name` REGEXP '^[!@#$%^&*()<>?_=:;.,~]' ")
        ->orwhereRaw(" `f_name` REGEXP '[!@#$%^&*()<>?_=:;.,~]$' ")
        ->orwhereRaw(" `l_name` REGEXP '^[!@#$%^&*()<>?_=:;.,~]' ")
        ->orwhereRaw(" `l_name` REGEXP '[!@#$%^&*()<>?_=:;.,~]$' ")
        ->orwhereRaw(" `birth_certificate_number` REGEXP '^[!@#$%^&*()<>?_=:;.,~]' ")
        ->orwhereRaw(" `birth_certificate_number` REGEXP '[!@#$%^&*()<>?_=:;.,~]$' ")
        ->get();

        foreach ($childrenGaps as $childrenGap) {
            
            $badCharacters = [ '!','@','#','$','%','^','&','*','(',')','<','>','?','_','=',':',';','.',',','~'];
            // dump($childrenGap->f_name);
                    $q = Child::where('children_id', $childrenGap->children_id)
                    ->withTrashed()
                    ->update ([
                        'f_name' =>  trim($childrenGap->f_name, implode($badCharacters)),
                        'l_name' => trim($childrenGap->l_name, implode($badCharacters)),
                        'birth_certificate_number' => trim($childrenGap->birth_certificate_number,implode($badCharacters)),
                    ]);
        }

    }
}

