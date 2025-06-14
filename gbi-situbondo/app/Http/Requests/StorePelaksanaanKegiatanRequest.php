<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class StorePelaksanaanKegiatanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'id_kegiatan' => 'required|exists:kegiatan,id_kegiatan',
            'tanggal_kegiatan' => 'required|date|after_or_equal:today',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
            'lokasi' => 'nullable|string|max:255',
            'is_recurring' => 'boolean',
            'recurring_type' => 'required_if:is_recurring,true|nullable|in:weekly,monthly',
            'recurring_end_date' => 'required_if:is_recurring,true|nullable|date|after:tanggal_kegiatan',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'id_kegiatan.required' => 'Kegiatan harus dipilih.',
            'id_kegiatan.exists' => 'Kegiatan yang dipilih tidak valid.',
            'tanggal_kegiatan.required' => 'Tanggal kegiatan harus diisi.',
            'tanggal_kegiatan.date' => 'Format tanggal tidak valid.',
            'tanggal_kegiatan.after_or_equal' => 'Tanggal kegiatan tidak boleh kurang dari hari ini.',
            'jam_mulai.required' => 'Jam mulai harus diisi.',
            'jam_mulai.date_format' => 'Format jam mulai tidak valid.',
            'jam_selesai.required' => 'Jam selesai harus diisi.',
            'jam_selesai.date_format' => 'Format jam selesai tidak valid.',
            'jam_selesai.after' => 'Jam selesai harus lebih besar dari jam mulai.',
            'lokasi.max' => 'Lokasi maksimal 255 karakter.',
            'recurring_type.required_if' => 'Tipe pengulangan harus dipilih jika jadwal berulang diaktifkan.',
            'recurring_type.in' => 'Tipe pengulangan harus mingguan atau bulanan.',
            'recurring_end_date.required_if' => 'Tanggal berakhir harus diisi jika jadwal berulang diaktifkan.',
            'recurring_end_date.date' => 'Format tanggal berakhir tidak valid.',
            'recurring_end_date.after' => 'Tanggal berakhir harus lebih besar dari tanggal kegiatan.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Additional validation for recurring schedules - only if is_recurring is true
            if ($this->boolean('is_recurring') && $this->filled(['tanggal_kegiatan', 'recurring_end_date', 'recurring_type'])) {
                $startDate = Carbon::parse($this->tanggal_kegiatan);
                $endDate = Carbon::parse($this->recurring_end_date);
                
                // Check if there's enough time between start and end date
                if ($this->recurring_type === 'weekly' && $startDate->diffInWeeks($endDate) < 1) {
                    $validator->errors()->add('recurring_end_date', 'Untuk jadwal mingguan, tanggal berakhir minimal 1 minggu setelah tanggal mulai.');
                }
                
                if ($this->recurring_type === 'monthly' && $startDate->diffInMonths($endDate) < 1) {
                    $validator->errors()->add('recurring_end_date', 'Untuk jadwal bulanan, tanggal berakhir minimal 1 bulan setelah tanggal mulai.');
                }
                
                // Check if end date is not too far in the future (e.g., max 2 years)
                if ($startDate->diffInYears($endDate) > 2) {
                    $validator->errors()->add('recurring_end_date', 'Jadwal berulang maksimal 2 tahun ke depan.');
                }
            }
        });
    }
}