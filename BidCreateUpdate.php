<?php

namespace App\Http\Requests;

use App\Traits\ChildValidationTrait;
use App\Traits\ParentValidationTrait;
use Illuminate\Foundation\Http\FormRequest;

class BidCreateUpdate extends AbstractRequest
{
    use ChildValidationTrait;
    use ParentValidationTrait;
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }
    protected function prepareForValidation(){
        $this->prepareChildData();
        $this->prepareParentData();

    // dd(
    //     "Child",
    //     "Тип докуента " .  $this->citizenship,
    //     "Серия А " . $this->citizenshipBirthCertificateSeria_A, //серия а  свид ребенка
    //     "Серия Б " . $this->citizenshipBirthCertificateSeria_B, //серия б свид ребенка
    //     "Серия номер " . $this->citizenshipBirthCertificateNumber,
    //     "Серия дата выдачи " . $this->citizenshipBirthCertificateCreatedAt,
    //     "Паспорт серия " . $this->citizenshipDocSeria,//серия  паспорта ребенка
    //     "Паспорт дата выдачи " .  $this->citizenshipDocCreatedAt , //дата выдачи  паспорта ребенка
    //     "Паспорт номер " . $this->citizenshipDocNumber, //нмоер  паспорта ребенка
    //     "Parent",
    //     "Тип докуента " . $this->parent_citizenship , //  паспорта родителя
    //     "Паспорт серия " . $this->parentcitizenshipDocSeria ,//серия  паспорта родителя
    //     "Паспорт номер " .  $this->parentcitizenshipDocNumber,//нмоер  паспорта родителя
    //     "Паспорт дата выдачи " . $this->parentcitizenshipDocCreatedAt, //дата выдачи  паспорта родителя
    // );

    }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return  array_merge($this->childRules(), $this->parentRules(), [
            'second_parent_f_name' => "",
            'second_parent_l_name' => "",
            'second_parent_m_name' => "",
            'second_parent_category' => "",
            'second_parent_doc_type' => "",
            'second_parent_doc_created_at' => "",
            'second_parent_phone' => "",
            'second_parent_citizenship' => "",
            'house_two_parent' => "",
            'second_parent_flat' => "",
            'second_parent_additional_contact' => "",
            'second_parent_doc_seria' => "",
            'second_parent_doc_number' => "",
        ]);
    }
}
