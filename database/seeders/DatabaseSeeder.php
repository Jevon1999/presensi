<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Office;
use App\Models\Member;
use App\Models\Attendance;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Buat 1 user admin utama
        $admin = User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'role' => 'admin',
        ]);

        // Buat 5 kantor
        $offices = Office::factory(5)->create();

        // Buat 20 anggota magang, sebar ke kantor-kantor yang ada
        $members = Member::factory(20)->recycle($offices)->create([
            'created_by' => $admin->id,
        ]);

        // Buat data absensi untuk setiap anggota selama 30 hari ke belakang
        foreach ($members as $member) {
            for ($i = 0; $i < 30; $i++) {
                $date = now()->subDays($i);

                // 80% kemungkinan hadir
                if (rand(1, 100) <= 80) {
                    Attendance::factory()->create([
                        'member_id' => $member->id,
                        'tanggal' => $date,
                    ]);
                } else {
                    // 20% kemungkinan izin, sakit, atau alpha
                    Attendance::factory()->create([
                        'member_id' => $member->id,
                        'tanggal' => $date,
                        'status' => $this->faker->randomElement(['izin', 'sakit', 'alpha']),
                        'check_in_time' => null,
                        'check_out_time' => null,
                        'is_late' => false,
                    ]);
                }
            }
        }
    }
}
