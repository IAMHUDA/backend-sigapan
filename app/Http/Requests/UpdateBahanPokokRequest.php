<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBahanPokokRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // Sesuaikan ini jika Anda memiliki logika otorisasi di dalam FormRequest
        // atau biarkan true jika otorisasi ditangani di controller/policy.
        // Untuk contoh ini, kita asumsikan otorisasi ada di controller/policy.
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        // Aturan validasi untuk update
        // Perhatikan 'satuan' adalah 'nullable'
        return [
            'urutan' => 'nullable|integer',
            'nama' => 'nullable|string|max:50', // 'nama' tetap required
            'satuan' => 'nullable|string|max:10', // 'satuan' bisa null
            'up_stok' => 'nullable|integer|min:0',
            'stok_wajib' => 'nullable|integer|min:0',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ];
    }

    /**
     * Prepare the data for validation.
     * * Ini sangat penting untuk method spoofing dengan FormData.
     * Laravel terkadang menyimpan input yang di-spoof (PUT/PATCH) di request->json() atau request->request
     * bukan request->all() secara langsung jika Content-Type adalah multipart/form-data.
     * Menggunakan FormRequest dan method prepareForValidation() membantu menyatukan ini.
     */
    protected function prepareForValidation()
{
    $allInput = $this->all();
    $mergedInput = array_merge($allInput, $this->request->all());

    if (array_key_exists('up_stok', $mergedInput)) {
        $mergedInput['up_stok'] = is_numeric($mergedInput['up_stok']) ? (int) $mergedInput['up_stok'] : null;
    }

    if (array_key_exists('stok_wajib', $mergedInput)) {
        $mergedInput['stok_wajib'] = is_numeric($mergedInput['stok_wajib']) ? (int) $mergedInput['stok_wajib'] : null;
    }

    $this->replace($mergedInput);
}

}
