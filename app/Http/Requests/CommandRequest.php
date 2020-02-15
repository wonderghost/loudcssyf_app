<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CommandRequest extends FormRequest
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
            //
            'quantite'  =>  'required|numeric|min:1',
            'reference_material'   =>  'required|string',
            'prix_achat'  =>  'required|numeric'
        ];
    }

    public function messages() {
      return [
        'required'  =>  'Champ `:attribute` Obligatoire',
        'sting' =>  'Champ `:attribute` est une chaine de caratere',
        'min' =>  '`:attribute` doit etre superieur a 0'
      ];
    }
}
