<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\KartuKeluarga;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\KartuKeluarga>
 */
class KartuKeluargaFactory extends Factory
{
    protected $model = KartuKeluarga::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nomor_kk' => $this->faker->unique()->numerify('################'), // 16 digit
            'alamat'   => $this->faker->address,
            'rt_id'    => 1, // atau $this->faker->numberBetween(1, 10) kalau ada banyak RT
        ];
    }
}
