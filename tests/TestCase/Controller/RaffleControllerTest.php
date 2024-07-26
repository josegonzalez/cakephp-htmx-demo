<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Controller\RaffleController;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * App\Controller\RaffleController Test Case
 *
 * @uses \App\Controller\RaffleController
 */
class RaffleControllerTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.Raffle',
    ];
}
