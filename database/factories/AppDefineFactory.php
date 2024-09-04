<?php

declare(strict_types=1);

namespace Hypocenter\LaravelSignature\Database\Factories;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;
use Hypocenter\LaravelSignature\Define\Models\AppDefine;

/**
 * @extends Factory<AppDefine>
 */
class AppDefineFactory extends Factory
{
    protected $model = AppDefine::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => Str::random(64),
            'name' => fake()->company,
            'secret' => Str::random(40),
        ];
    }
}
