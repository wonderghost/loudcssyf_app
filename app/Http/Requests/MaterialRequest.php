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
            'libelle'=>'required',
            'prix_initial'=>'required|numeric|min:0',
            'prix_unitaire'=>'required|numeric|min:0',
            'quantite'=>'required|min:0',
            'marge' => 'required|numeric|min:0'
        ];
    }
}
