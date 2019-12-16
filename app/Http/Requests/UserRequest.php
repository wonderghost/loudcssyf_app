<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //requi
            'email'=>'required|email|unique:users',
            'phone'=>'required',
            'type'=>'required',
            'societe'=>'required'
        ];
    }

    public function messages() {
      return [
        'required'  =>  'Veuillez renseigner le champ :attribute',
        'email'  =>  'Le champ :attribute doit etre une adresse email valide',
        'unique'  =>  'Le champ :attribute doit etre unique'
      ];
    }
}
