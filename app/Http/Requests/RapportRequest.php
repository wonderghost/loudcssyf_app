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

            'quantite_recrutement'  =>  'required|min:1',
            'ttc_recrutement'   =>  'required',
            'quantite_migration'    =>  'required',
            'ttc_reabonnement'  =>  'required',
            'vendeurs'   =>  'required|exists:users,username',
            'date'  =>  'required|unique:rapport_vente,date_rapport'

        ];
    }
}
