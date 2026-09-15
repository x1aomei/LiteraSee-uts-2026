<?php
// database/factories/BookFactory.php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class BookFactory extends Factory
{
    public function definition(): array
    {
        $title = ucwords(fake()->words(rand(2, 5), true));
        $price = fake()->numberBetween(50000, 500000);

        return [
            'category_id' => Category::inRandomOrder()->first()?->id ?? Category::factory(),
            'title' => $title,
            'slug' => Str::slug($title) . '-' . fake()->unique()->numberBetween(1000, 9999),
            'author' => fake()->name(),
            'publisher' => fake()->randomElement([
                'Gramedia', 'Erlangga', 'Mizan', 'Bentang Pustaka', 'Kompas',
            ]),
            'isbn' => fake()->isbn13(),
            'year' => fake()->numberBetween(2000, 2025),
            'pages' => fake()->numberBetween(80, 800),
            'language' => 'Indonesia',
            'description' => fake()->paragraphs(rand(2, 4), true),
            'price' => $price,
            'discount_price' => fake()->boolean(30) ? (int)($price * 0.7) : null,
            'stock' => fake()->numberBetween(0, 100),
            'weight' => fake()->numberBetween(150, 1500),
            'is_active' => fake()->boolean(90),
            'is_featured' => fake()->boolean(15),
        ];
    }

    public function featured(): static
    {
        return $this->state(fn () => ['is_featured' => true, 'is_active' => true]);
    }
}