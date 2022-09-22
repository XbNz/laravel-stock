<?php

declare(strict_types=1);

namespace Tests\Feature\Users\UserController;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DestroyTest extends TestCase
{
    use RefreshDatabase;

    /** @test **/
    public function a_users_related_stocks_are_cascade_deleted_when_a_user_is_destroyed(): void
    {
        // Arrange

        // Act

        // Assert
    }
}