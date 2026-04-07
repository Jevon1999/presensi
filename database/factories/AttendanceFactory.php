<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Attendance;
use App\Models\Member;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Attendance>
 */
class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $checkIn = $this->faker->dateTimeBetween('08:00:00', '10:00:00');
        $isLate = $checkIn->format('H:i:s') > '08:30:00';

        return [
            'member_id' => Member::factory(),
            'tanggal' => $this->faker->date(),
            'check_in_time' => $checkIn,
            'check_out_time' => $this->faker->dateTimeBetween('16:00:00', '17:00:00'),
            'status' => 'hadir',
            'is_late' => $isLate,
            'late_reason' => $isLate ? $this->faker->sentence() : null,
        ];
    }
}
