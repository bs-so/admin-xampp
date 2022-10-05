<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StaffRequest extends FormRequest
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
        $id = $this->request->get('id');

        if (isset($id) && $id != 0) {
            return [
                'login_id'      => 'required|max:64|unique:olc_staff,login_id,' . $id,
                'name'          => 'required|max:64',
                'password'      => 'confirmed',
                'role'          => 'required',
            ];
        }
        return [
            'login_id'      => 'required|max:64|unique:olc_staff',
            'name'          => 'required|max:64',
            'password'      => 'required|min:6|max:255|confirmed',
            'role'          => 'required',
        ];
    }
}
