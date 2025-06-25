<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class UpdatePelaksanaanKegiatanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Check if the schedule can be edited (hasn't started yet)
        $pelaksanaan = $this->route('pelaksanaan');
        
        if ($pelaksanaan) {
            $eventDate = Carbon::parse($pelaksanaan->tanggal_kegiatan);
            try {
                $eventStartTime = $eventDate->copy()->setTimeFromTimeString($pelaksanaan->jam_mulai);
            } catch (\Exception $e) {
                $eventStartTime = Carbon::createFromFormat('Y-m-d H:i', 
                    $eventDate->format('Y-m-d') . ' ' . substr($pelaksanaan->jam_mulai, 0, 5));
            }
            
            return Carbon::now()->lt($eventStartTime);
        }
        
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $pelaksanaan = $this->route('pelaksanaan');
        
        $rules = [
            'tanggal_kegiatan' => 'required|date|after_or_equal:today',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
            'lokasi' => 'nullable|string|max:255',
        ];

        // For recurring schedules or child schedules, kegiatan cannot be changed
        if ($pelaksanaan && ($pelaksanaan->is_recurring || $pelaksanaan->parent_id)) {
            $rules['id_kegiatan'] = 'required|in:' . $pelaksanaan->id_kegiatan;
        } else {
            $rules['id_kegiatan'] = 'required|exists:kegiatan,id_kegiatan';
        }

        // Add recurring validation only if it's a recurring event being edited
        if ($pelaksanaan && $pelaksanaan->is_recurring) {
            $rules['recurring_type'] = 'required|in:weekly,monthly';
            
            // Only validate end date if it can be edited (hasn't been reached yet)
            if ($pelaksanaan->recurring_end_date && Carbon::now()->lt($pelaksanaan->recurring_end_date)) {
                $rules['recurring_end_date'] = 'required|date|after:tanggal_kegiatan';
            }
        }

        return $rules;
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'id_kegiatan.required' => 'Kegiatan harus dipilih.',
            'id_kegiatan.exists' => 'Kegiatan yang dipilih tidak valid.',
            'id_kegiatan.in' => 'Kegiatan tidak dapat diubah untuk jadwal berulang.',
            'tanggal_kegiatan.required' => 'Tanggal kegiatan harus diisi.',
            'tanggal_kegiatan.date' => 'Format tanggal tidak valid.',
            'tanggal_kegiatan.after_or_equal' => 'Tanggal kegiatan tidak boleh kurang dari hari ini.',
            'jam_mulai.required' => 'Jam mulai harus diisi.',
            'jam_mulai.date_format' => 'Format jam mulai tidak valid.',
            'jam_selesai.required' => 'Jam selesai harus diisi.',
            'jam_selesai.date_format' => 'Format jam selesai tidak valid.',
            'jam_selesai.after' => 'Jam selesai harus lebih besar dari jam mulai.',
            'lokasi.max' => 'Lokasi maksimal 255 karakter.',
            'recurring_type.required' => 'Tipe pengulangan harus dipilih.',
            'recurring_type.in' => 'Tipe pengulangan harus mingguan atau bulanan.',
            'recurring_end_date.required' => 'Tanggal berakhir harus diisi.',
            'recurring_end_date.date' => 'Format tanggal berakhir tidak valid.',
            'recurring_end_date.after' => 'Tanggal berakhir harus lebih besar dari tanggal kegiatan.',
        ];
    }

    /**
     * Get custom authorization message.
     */
    protected function failedAuthorization()
    {
        abort(403, 'Jadwal kegiatan yang sudah berlangsung tidak dapat diedit.');
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $pelaksanaan = $this->route('pelaksanaan');
            
            // Additional validation for recurring schedules
            if ($pelaksanaan && $pelaksanaan->is_recurring && $this->filled(['tanggal_kegiatan', 'recurring_end_date', 'recurring_type'])) {
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
                
                // Check if the new end date makes sense
                $originalEndDate = $pelaksanaan->recurring_end_date;
                if ($originalEndDate && $endDate->lt($originalEndDate)) {
                    // Count how many completed events would be affected
                    $completedEvents = $pelaksanaan->children()
                        ->where('tanggal_kegiatan', '>', $endDate->format('Y-m-d'))
                        ->where('tanggal_kegiatan', '<=', Carbon::now()->format('Y-m-d'))
                        ->count();
                        
                    if ($completedEvents > 0) {
                        $validator->errors()->add('recurring_end_date', 
                            "Tanggal berakhir tidak dapat dipercepat karena ada {$completedEvents} jadwal yang sudah terlaksana setelah tanggal ini.");
                    }
                }
            }
        });
    }
}