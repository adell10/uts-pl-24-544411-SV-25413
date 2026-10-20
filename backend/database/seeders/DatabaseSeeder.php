<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Akun demo + data contoh kafe.
     * Login: hailo@cafehopper.test / password123 (bisa diganti lewat SEED_USER_PASSWORD di .env)
     * Aman dijalankan berulang: data kafe hanya dibuat kalau user belum punya kafe.
     */
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'hailo@cafehopper.test'],
            ['name' => 'Hailo', 'password' => env('SEED_USER_PASSWORD', 'password123')],
        );

        if ($user->cafes()->exists()) {
            return;
        }

        $d = fn (int $daysAgo) => Carbon::today()->subDays($daysAgo)->toDateString();

        $cafes = [
            ['name' => 'Ruang Seduh', 'area' => 'Seturan', 'status' => 'visited', 'price_level' => 2, 'rating' => 5,
                'work_friendly' => true, 'facilities' => ['wifi', 'colokan', 'musala'],
                'favorite_menu' => 'Es kopi susu gula aren',
                'notes' => 'Colokan banyak, wifi kencang. Lantai 2 paling tenang buat nugas.',
                'last_visited_at' => $d(3), 'visit_count' => 5, 'color' => 'pink'],
            ['name' => 'Teras Pagi', 'area' => 'Jakal KM 5', 'status' => 'visited', 'price_level' => 1, 'rating' => 4,
                'work_friendly' => true, 'facilities' => ['wifi', 'colokan'],
                'favorite_menu' => 'Kopi tubruk', 'notes' => 'Buka dari jam 7 pagi.',
                'last_visited_at' => $d(8), 'visit_count' => 3, 'color' => 'yellow'],
            ['name' => 'Kedai Hujan', 'area' => 'Prawirotaman', 'status' => 'visited', 'price_level' => 3, 'rating' => 3,
                'work_friendly' => false, 'facilities' => ['wifi', 'parkir'],
                'favorite_menu' => 'Matcha latte', 'notes' => 'Estetik tapi ramai.',
                'last_visited_at' => $d(15), 'visit_count' => 1, 'color' => 'purple'],
            ['name' => 'Sudut Kopi', 'area' => 'Gejayan', 'status' => 'visited', 'price_level' => 1, 'rating' => 4,
                'work_friendly' => true, 'facilities' => ['wifi', 'colokan', 'parkir'],
                'favorite_menu' => 'Americano', 'notes' => null,
                'last_visited_at' => $d(20), 'visit_count' => 2, 'color' => 'green'],
            ['name' => 'Lantai Dua', 'area' => 'Kotabaru', 'status' => 'wishlist', 'price_level' => 2, 'rating' => null,
                'work_friendly' => false, 'facilities' => ['wifi'],
                'favorite_menu' => null, 'notes' => 'Direkomendasikan teman sekelas.',
                'last_visited_at' => null, 'visit_count' => 0, 'color' => 'blue'],
            ['name' => 'Teduh Coffee', 'area' => 'Kaliurang', 'status' => 'wishlist', 'price_level' => 2, 'rating' => null,
                'work_friendly' => true, 'facilities' => ['wifi', 'colokan'],
                'favorite_menu' => null, 'notes' => null,
                'last_visited_at' => null, 'visit_count' => 0, 'color' => 'pink'],
        ];

        foreach ($cafes as $cafe) {
            $user->cafes()->create($cafe);
        }
    }
}