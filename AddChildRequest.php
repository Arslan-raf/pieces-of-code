<?php

namespace App\Http\Requests;

use App\Traits\ChildValidationTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

use function GuzzleHttp\Promise\all;

class AddChildRequest extends AbstractRequest
{
use ChildValidationTrait;
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
    }
    public function rules(){
        return $this->childRules();
    }
}
