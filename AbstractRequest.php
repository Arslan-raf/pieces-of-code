<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

abstract class AbstractRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return false;
    }

    protected function toupper($str){
        $result='';
        $str = str_replace("-", " ", $str);
        $pieces = explode(" ", $str);
        foreach($pieces as $piece){
            $result .= (mb_strtoupper(mb_substr($piece, 0, 1)) . mb_strtolower( mb_substr($piece, 1, mb_strlen($piece))) ) . ' ';
        }
        $result = preg_replace('/( {2,})/i', ' ', $result);
        //$result = str_replace($chars, '', $result); // PHP код
         //var_dump($result);
        $result = trim($result);
        //dd($result);
        return $result;
    }

}
