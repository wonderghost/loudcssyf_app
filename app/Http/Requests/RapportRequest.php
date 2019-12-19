<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RapportRequest extends FormRequest
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

            'quantite_materiel'  =>  'required|min:1',
            'montant_ttc' =>  'required|numeric',
            'vendeurs'   =>  'required|exists:users,username',
            // 'date'  =>  'required|before_or_equal:'.(date("d/m/Y",strtotime("now")))
        ];
    }

    public function messages() {
      return [
        'required'  =>  'Veuillez remplir le champ `:attribute`',
        'numeric'  =>  '`:attribute` doit etre une valeur numeric'
      ];
    }
}
