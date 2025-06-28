<?php

namespace Database\Factories;

use App\Models\TranslationUnit;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\TranslationUnitVersion;

class TranslationUnitVersionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model
     *
     * @var string
     */
    protected $model = TranslationUnitVersion::class;

    /**
     * Define the model's default state
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'translation_unit_id' => function () {
                return TranslationUnit::factory()->create()->id;
            },
            'translated_text' => $this->faker->sentence(),
            'edited_by' => $this->faker->numberBetween(1, 5),
            'version_number' => 1,
            'created_at' => now(),
        ];
    }
}
