<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CgaRequest extends FormRequest
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
            'compte' => 'required',
            'montant' => 'required',
            'vendeur' => 'required|exists:users,username'
        ];
    }

    public function messages() {
      return [
          'required'  =>  'Veuillez renseigner le champ :attribute',
          'exists'  =>  ':attribute existe deja en base de donnees'
      ];
    }
}
