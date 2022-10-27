<?php

namespace Tests\Unit\App\Commands;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class RecycleTempFolderTest extends TestCase
{
    /** @test **/
    public function it_empties_the_temp_folder_and_recreates_it(): void
    {
        // Arrange
        File::makeDirectory(storage_path('app/temp'));
        File::put(storage_path('app/temp/test.txt'), 'test');

        // Act
        $this->assertFileExists(storage_path('app/temp/test.txt'));
        $this->artisan('recycle:temp-folder');

        // Assert
        $this->assertFileDoesNotExist(storage_path('app/temp/test.txt'));
        $this->assertDirectoryExists(storage_path('app/temp'));
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        File::deleteDirectory(storage_path('app/temp'));
    }
}