<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Member;
use App\Models\User;
use App\Models\Office;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Member>
 */
class MemberFactory extends Factory
{
    protected $model = Member::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'office_id' => Office::factory(),
            'nama_lengkap' => $this->faker->name(),
            'no_hp' => $this->faker->unique()->e164PhoneNumber(),
            'jenis_kelamin' => $this->faker->randomElement(['L', 'P']),
            'asal_sekolah' => $this->faker->company() . ' School',
            'jurusan' => $this->faker->jobTitle(),
            'tanggal_mulai_magang' => $this->faker->dateTimeBetween('-2 months', '-1 month'),
            'tanggal_selesai_magang' => $this->faker->dateTimeBetween('+1 month', '+2 months'),
            'status_aktif' => true,
            'status' => 'approved',
            'created_by' => User::first() ?? User::factory(),
        ];
    }
}
