<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SettingRequest extends FormRequest
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
        $rules = [];

        foreach (g_enum('MasterData') as $option => $data) {
			if ($option != MAINTENANCE_MODE && $option != PRNG_PRIME_VALUE) {
	            $rules[$option] = 'required';
			}
        }

        return $rules;
    }

    public function messages()
    {
        $messages = [];

        foreach (g_enum('MasterData') as $option => $data) {
            $messages[$option . '.required'] = trans('auth.required');
        }

        return $messages;
    }
}
