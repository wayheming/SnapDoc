<?php
// app/database/factories/DocumentFormatFactory.php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class DocumentFormatFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'       => fake()->words(2, true),
            'country'    => fake()->countryCode(),
            'width_mm'   => 35,
            'height_mm'  => 45,
            'dpi'        => 300,
            'is_active'  => true,
            'sort_order' => fake()->numberBetween(0, 100),
        ];
    }
}
