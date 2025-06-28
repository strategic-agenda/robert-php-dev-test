<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\TranslationUnit;

class TranslationUnitFactory extends Factory
{
    /**
     * The name of the factory's corresponding model
     *
     * @var string
     */
    protected $model = TranslationUnit::class;

    /**
     * Define the model's default state
     *
     * @return array
     */
    public function definition()
    {
        return [
            'document_id' => $this->faker->numberBetween(1, 10),
            'segment_index' => $this->faker->numberBetween(1, 100),
            'source_text' => $this->faker->sentence(),
            'source_locale' => $this->faker->randomElement(['en', 'fr', 'de', 'es']),
            'target_locale' => $this->faker->randomElement(['en', 'fr', 'de', 'es']),
        ];
    }
}
