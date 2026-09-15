<?php
// database/seeders/DatabaseSeeder.php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin LiteraSee',
            'email' => 'admin@literasee.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        User::create([
            'name' => 'Customer Demo',
            'email' => 'customer@literasee.com',
            'password' => Hash::make('password'),
            'role' => 'customer',
            'email_verified_at' => now(),
        ]);

        $categories = [
            ['name' => 'Fiksi', 'description' => 'Novel, cerpen, puisi'],
            ['name' => 'Non-Fiksi', 'description' => 'Biografi, sejarah, sains'],
            ['name' => 'Komik & Manga', 'description' => 'Komik, manga, graphic novel'],
            ['name' => 'Akademik', 'description' => 'Buku pelajaran & kuliah'],
            ['name' => 'Anak-Anak', 'description' => 'Buku cerita & edukasi anak'],
            ['name' => 'Agama & Spiritual', 'description' => 'Buku agama & pengembangan diri'],
        ];

        foreach ($categories as $cat) {
            Category::create($cat + ['is_active' => true]);
        }

        Book::factory(50)->create();
        Book::factory(10)->featured()->create();

        $this->command->info('🎉 Seeded! admin@literasee.com / password');
    }
}