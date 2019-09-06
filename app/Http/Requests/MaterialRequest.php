<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MaterialRequest extends FormRequest
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
            'depot'=>'required',
            'libelle'=>'required',
            'prix_initial'=>'required|numeric|min:1',
            'prix_unitaire'=>'required|numeric|min:1',
            'quantite'=>'required',
            'marge' => 'required|numeric|min:1'
        ];
    }
}
