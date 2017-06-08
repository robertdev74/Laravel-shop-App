<?php

/*
 * This file is part of the Antvel App package.
 *
 * (c) Gustavo Ocanto <gustavoocanto@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use App\VirtualProduct;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Antvel\User\Models\Business;
use Antvel\Product\Models\Product;

class VirtualProductsSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();
        $businesses = Business::get();
        for ($i = 0; $i < 10; $i++) {
            $price = $faker->numberBetween(1, 99);
            $stock = $faker->numberBetween(20, 50);
            $product = Product::create([
                'category_id'  => $faker->numberBetween(1, 9),
                'created_by' => '3',
                'updated_by' => '3',
                'name'         => 'VIRTUAL '.$faker->unique()->catchPhrase,
                'description'  => $faker->text(500),
                'price'        => $price,
                'stock'        => $stock,
                'sale_counts'  => $faker->randomNumber(9),
                'view_counts'  => $faker->randomNumber(9),
                'brand'        => $faker->randomElement(['Apple', 'Gigabyte', 'Microsoft', 'Google. Inc', 'Samsung', 'Lg']),
                'features'     => json_encode([
                    'images' => [
                    '/img//pt-default/'.$faker->unique()->numberBetween(1, 330).'.jpg',
                    '/img//pt-default/'.$faker->unique()->numberBetween(1, 330).'.jpg',
                    '/img//pt-default/'.$faker->unique()->numberBetween(1, 330).'.jpg',
                    '/img//pt-default/'.$faker->unique()->numberBetween(1, 330).'.jpg',
                    '/img//pt-default/'.$faker->unique()->numberBetween(1, 330).'.jpg',
                    ],
                ]),
                'condition' => $faker->randomElement(['new', 'refurbished', 'used']),
                'tags'      => json_encode($faker->word.','.$faker->word.','.$faker->word),
                'type'      => 'software_key',
            ]);
            $faker->unique($reset = true);
            $virtual = VirtualProduct::create([
                'product_id' => $product->id,
                'key'        => 'undefined',
                'status'     => 'cancelled',
            ]);
            for ($j = 1; $j < $stock; $j++) {
                $virtual = VirtualProduct::create([
                    'product_id' => $product->id,
                    'key'        => $faker->uuid,
                    'status'     => 'open',
                ]);
            }
        }
    }
}
