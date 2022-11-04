<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use SoapClient;
use App\Child;
use App\СhildСategories;
use Illuminate\Support\Facades\Auth;
use App\fias_house;
use App\fias_address_object;
use Illuminate\Http\Request;
use App\fix_parent;
use App\Traits\CopyFileTrait;
use App\Bid;
use App\Helpdesk_question;
use App\Helpdesk_message;
use App\Http\Requests\AddChildRequest;
use App\Models\Citizenship;
use Illuminate\Auth\Events\Validated;

class ChildController extends Controller
{
    use CopyFileTrait;

  public function create()
  {
      $readOnly = false;
      $parent = Auth::user()->parent_id;
      //$childs = Child::where('parent_id', $parent )->with('bids')->get();
      $privileges = СhildСategories::all();
      $citizenship = Citizenship::CITIZENSHIPS;
      return view('child.add',[
          'privileges'=>$privileges,
          'readOnly'=>$readOnly,
          'citizenship'=>$citizenship,
      ]);
  }

  public function ModalDataAddChild(AddChildRequest $request)
	{
        //dd($request);

		$child = Child::where('Birth_certificate_number', '!=', null)->whereBirth_certificate_numberAndBirth_certificate_seria_bAndBirth_certificate_seria_a($request->birth_certificate_number, $request->birth_certificate_seria_b, $request->birth_certificate_seria_a);
		if (isset($child->first()->children_id) == false) {

			$child = Child::create([
				'f_name' => $request['f_name'],
				'l_name' => $request['l_name'],
				'm_name' => $request['m_name'],
				'born_at' => $request['born_at'],
				'flat' => $request['flat'],
				'gender' => $request['gender'],
				'citizenship' =>$request['citizenship'],
				'birth_certificate_created_at' => $request['birth_certificate_created_at'],
				'birth_certificate_number' => $request['birth_certificate_number'],
				'doc_type' => $request['doc_type'],
				'doc_seria' => $request['doc_seria'],
				'doc_number' => $request['doc_number'],
				'born_place' => $request['born_place'],
				'snils' => $request['snils'],
				'doc_created_at' => $request['doc_created_at'],
				'birth_certificate_seria_a' => $request['birth_certificate_seria_a'],
				'birth_certificate_seria_b' => $request['birth_certificate_seria_b'],
				'child_category_id' => $request['child_category_id'],
				'address_equal' => $request['address_equal'],
				'fias_fact_house_id' => $request['fias_fact_house_id'],
                'street_fact'=>$request['street_fact'],//фактический адрес
                'street_reg'=>$request['street_reg'],
                'fias_house_id'=>$request['fias_house_id'],
                'flat'=>$request['flat'],
				'fact_flat' => $request['fact_flat'],
				'user_id' => Auth::id(),
				'parent_id' => Auth::user()->parent_id
			]);
			Child::find($child->children_id)->update([
				'registration_doc_scan' => $this->copyFile($request, $child->children_id, 'registration_doc_scan'),
				'parent_passport_scan' => $this->copyFile($request, $child->children_id, 'parent_passport_scan'),
				'birth_certificate_scan' => $this->copyFile($request, $child->children_id, 'birth_certificate_scan'),
				'child_categories_scan' => $this->copyFile($request, $child->children_id, 'child_categories_scan'),
                'older_child_scan' => $this->copyFile($request, $child->children_id, 'older_child_scan')
			]);

			echo 'Ребёнок успешно добавлен!';
		} elseif (isset($child->whereF_nameAndL_name($request->f_name, $request->l_name)->first()->children_id)) {
			$child = $child->first();
			if($child->parent_id != Auth::user()->parent_id){// от повторного перезакрепления
				foreach (Bid::whereChild_id($child->children_id)->pluck('id') as $bid) {
					Bid::find($bid)->history_bids()->create([
						'comment' => 'Заявление перезакрепленно за новым кабинетом законного представителя',
						'user_id' => Auth::user()->id
					]);
				}
				fix_parent::create([
					'parent_id' => $child->parent_id,
					'reparent_id' => Auth::user()->parent_id,
					'child_id' => $child->children_id
				]);
				Child::find($child->children_id)->update(['parent_id' => Auth::user()->parent_id]);
				Bid::where('child_id', $child->children_id)->update([
					'parent_id' => Auth::user()->parent_id
				]);
				echo 'Ребёнок уже был в системе, сейчас перезакреплён за вашим кабинетом';
			}
			else{
				echo 'Ребёнок уже закреплён за вашим кабинетом';
			}
		} else {
			$Helpdesk_question = Helpdesk_question::updateOrCreate(
				[
					'subject' => 'Система: Перезакрепление ребёнка за новым кабинетом',
					'user_id' => Auth::id(),
				],
				[
					'status_id' => 1
				]
			);

			Helpdesk_message::updateOrCreate(
				[
					'question_id' => $Helpdesk_question->id
				],
				[
					'body' => "Здравствуйте! Закрепите за мной ребёнка " . $request->l_name . " " . $request->f_name . " " . $request->m_name . " " . $request->birth_certificate_seria_a . " " . $request->birth_certificate_seria_b . " " . $request->birth_certificate_number . ".",
					'user_id' => Auth::id(),
				]
			);
			echo 'От вас создано письмо в разделе Тех. помощь на перезакрепление ребёнка за вашим кабинетом';
		}
	}

    //* редактирование */
    //озвращение вьющки
    public function ViewModalDataEditChild()
	{
		if (Auth::user()->role_id == 1) {
			$child = Child::withTrashed()->find($_POST['id']);
		} else {
			$child = Child::where('children_id', $_POST['id'])->where('parent_id', Auth::user()->parent_id)->first();
		}
		$house_reg = fias_house::where('id', $child->fias_house_id ?? '')->select('id', 'full_number as title', 'fias_address_object_id')->first();
		$address_reg = fias_address_object::where('id', $house_reg->fias_address_object_id ?? '')->select('id', 'full_address as title')->first();
		$house_fact = fias_house::where('id', $child->fias_fact_house_id ?? '')->select('id', 'full_number as title', 'fias_address_object_id')->first();
		$address_fact = fias_address_object::where('id', $house_fact->fias_address_object_id ?? '')->select('id', 'full_address as title')->first();
        if($child->haveAcceptedBid()){
            $forEdit = false;
            $readOnly = true;
        }
        else{
            $readOnly = false;
            $forEdit = true;
        }

        if($child->haveAcceptedBid() && Auth::user()->role_id == 1){
            $forEdit = true;
            $readOnly = false;
        }
        $father = true;
        $citizenship = Citizenship::CITIZENSHIPS;
        return view('child.edit', [
            	'child' => $child,
            	'privileges' => СhildСategories::get(),
            	'house_reg' => $house_reg,
            	'address_reg' => $address_reg,
            	'house_fact' => $house_fact,
            	'address_fact' => $address_fact,
                'forEdit'=>$forEdit,
                'readOnly' => $readOnly,
                'father'=> $father,
                'citizenship'=>$citizenship,
            ]);
	}

    // метод редактирования POST
    public function ModalDataEditChild(AddChildRequest $request)
	{
//         $data = $request->validated();
//         dd($data);
		if (!Child::find($request->id)->haveAcceptedBid() || Auth::user()->role_id != 2) {

            $child = Child::find($request->id);

			$child->fill($request->all());

            $child->fill([
                'registration_doc_scan' => $this->copyFile($request, $child->children_id, 'registration_doc_scan'),
                'parent_passport_scan' => $this->copyFile($request, $child->children_id, 'parent_passport_scan'),
                'birth_certificate_scan' => $this->copyFile($request, $child->children_id, 'birth_certificate_scan'),
                'child_categories_scan' => $this->copyFile($request, $child->children_id, 'child_categories_scan'),
                'older_child_scan' => $this->copyFile($request, $child->children_id, 'older_child_scan')
            ]);


            $child->createLogs();
            $child->save();

		}
	}

}
