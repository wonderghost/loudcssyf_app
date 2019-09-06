<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RecrutementRequest extends FormRequest
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
            'serial_number' => 'required',
            'debut' => 'required|date',
            'fin' => 'required|date',
            'nom' => 'required|string|min:2',
            'prenom' => 'required|string|min:4',
            'phone' => 'required',
            'adresse' => 'required|string',
            'formule' => 'required',
            'duree' =>  'required|string',

        ];
    }
}
