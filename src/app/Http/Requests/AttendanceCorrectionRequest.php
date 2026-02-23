<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Carbon\Carbon;

class AttendanceCorrectionRequest extends FormRequest
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
            'clock_in'  => ['nullable', 'date_format:H:i'],
            'clock_out' => ['nullable', 'date_format:H:i'],

            'break_start'   => ['array'],
            'break_start.*' => ['nullable', 'date_format:H:i'],

            'break_end'   => ['array'],     //配列になっているか
            'break_end.*' => ['nullable', 'date_format:H:i'],

            'remark' => ['required', 'string'],
        ];
    }

    public function messages()
    {
        return [
            'clock_in.date_format'  => '出勤時刻は HH:MM 形式で入力してください',
            'clock_out.date_format' => '退勤時刻は HH:MM 形式で入力してください',
            'break_start.*.date_format' => '休憩開始時刻は HH:MM 形式で入力してください',
            'break_end.*.date_format'   => '休憩終了時刻は HH:MM 形式で入力してください',
            'remark.required' => '備考を記入してください',
        ];
    }

    /* 出勤・退勤・休憩の前後関係チェック */
    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {

            $clockIn  = $this->clock_in ? Carbon::createFromFormat('H:i', $this->clock_in) : null;
            $clockOut = $this->clock_out ? Carbon::createFromFormat('H:i', $this->clock_out) : null;

            /* 出勤・退勤の前後関係 */
            if ($clockIn && $clockOut && $clockIn->gte($clockOut)) {
                $validator->errors()->add(
                    'clock_time',
                    '出勤時間もしくは退勤時間が不適切な値です'
                );
            }

            $breakStarts = $this->break_start ?? [];
            $breakEnds   = $this->break_end ?? [];

            $max = max(count($breakStarts), count($breakEnds));

            for ($i = 0; $i < $max; $i++) {
                $start = $breakStarts[$i] ?? null;
                $end   = $breakEnds[$i] ?? null;

                // ペア設定
                if ($start && !$end) {
                    $validator->errors()->add(
                        "break_end.$i",
                        '休憩終了時刻を入力してください'
                    );
                }

                if (!$start && $end) {
                    $validator->errors()->add(
                        "break_start.$i",
                        '休憩開始時刻を入力してください'
                    );
                }

                // 両方ない場合はスキップ
                if (!$start && !$end) {
                    continue;
                }

                // startがある時だけCarbon化
                if ($start) {
                    $startTime = Carbon::createFromFormat('H:i', $start);

                    if (
                        ($clockIn && $startTime->lt($clockIn)) ||
                        ($clockOut && $startTime->gt($clockOut))
                    ) {
                        $validator->errors()->add(
                            "break_start.$i",
                            '休憩時間が不適切な値です'
                        );
                    }
                }

                // endがある時だけCarbon化
                if ($end && $clockOut) {
                    $endTime = Carbon::createFromFormat('H:i', $end);

                    if ($endTime->gt($clockOut)) {
                        $validator->errors()->add(
                            "break_end.$i",
                            '休憩時間もしくは退勤時間が不適切な値です'
                        );
                    }
                }
            }
        });
    }
}
