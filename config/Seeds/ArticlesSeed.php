<?php
declare(strict_types=1);

use Faker\Factory;
use Migrations\AbstractSeed;

/**
 * Articles seed.
 */
class ArticlesSeed extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeds is available here:
     * https://book.cakephp.org/phinx/0/en/seeding.html
     *
     * @return void
     */
    public function run(): void
    {
        $data = [];
        foreach (range(1, 100) as $i) {
            $faker = Factory::create();
            $data[] = [
                'title' => $faker->sentence(),
                'content' => $faker->paragraphs(5, true),
                'photo_url' => "https://picsum.photos/seed/{$i}/600"
            ];
        }

        $table = $this->table('articles');
        $table->insert($data)->save();
    }
}
