<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Translation>
 */
class TranslationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $locales = ['en', 'fr', 'es', 'de', 'it'];
        $tags = ['mobile', 'desktop', 'web', 'api', 'admin'];
        
        return [
            'key' => $this->faker->unique()->word() . '_' . $this->faker->randomNumber(4),
            'locale' => $this->faker->randomElement($locales),
            'tag' => $this->faker->randomElement($tags),
            'value' => $this->faker->sentence(),
        ];
    }
}
