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
            'mat-reference'   =>  'required|string',
            'numero_versement'  =>  'required|string',
            'recu' => 'required|image'
        ];
    }
}
