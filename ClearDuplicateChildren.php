<?php

namespace App\Console\Commands;

use App\Child;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ClearDuplicateChildren extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ClearChild'; //определяет вводимые данные

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear duplicate Children';

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
    public function handle() //вызывается при выполнении команд
    {
        $childrenDuplicates = DB::table('children')
            ->select('f_name', 'l_name', 'birth_certificate_number', DB::raw('count(*) as total')) //
            ->groupBy('f_name', 'l_name', 'birth_certificate_number')
            ->having('total', '>', 1)
            ->whereNull('deleted_at')
            // ->paginate(10);
            ->get();


        foreach ($childrenDuplicates as $childDupe) {


            $q = Child::select(
                'children_id',
                DB::raw('count(bids.id) as totalBids'),
                 //нужно будет получать дату создания заявлений,
                // 'users.last_sign_in_at' //и дату авторизации родителя
            )
                ->leftJoin('bids', 'bids.child_id', 'children.children_id')
                ->leftJoin('parents', 'parents.parent_id', 'children.parent_id')
                ->leftJoin('users', 'users.parent_id', 'parents.parent_id')
                ->where('children.f_name', '=', $childDupe->f_name)
                ->where('children.l_name', '=', $childDupe->l_name)
                ->orderBy('children.created_at')
                ->groupBy('children_id');

            if ($childDupe->birth_certificate_number != null) {
                $q->where('children.birth_certificate_number', '=', $childDupe->birth_certificate_number);
            } else {
                $q->whereNull('children.birth_certificate_number');
            }

            $children = $q->get();
            dd($children);
            // dump(
            //     sprintf(
            //         "Новый набор дубликатов: %s %s %s (%d)",
            //         $childDupe->f_name,
            //         $childDupe->l_name,
            //         $childDupe->birth_certificate_number,
            //         $childDupe->total
            //     )
            // );

            $i = 0;
            $keptChildren = [];
            foreach ($children as $child) {
                $i++;
                if ($child->totalBids != 0) {
                    $keptChildren[$child->children_id] = true;
                }
                /**
                 *
                 */
                if ($childDupe->total == $i && count($keptChildren) == 0) {
                    $keptChildren[$child->children_id] = true;
                }
                //удаляю при условии что у нас этого ребенка нет в keptChildren
                if (!isset($keptChildren[$child->children_id])) {
                    //$child->delete();
                    //dump(' удалил ' . $child->children_id);
                    $child->delete();
                } else {
                    //dump('не удалил ' . $child->children_id);
                }
            }
            // dd($children);
            //dump('---------------------------');
        }
    }
}
