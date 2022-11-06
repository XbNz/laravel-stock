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

        if (File::exists(storage_path('app/tmp'))) {
            File::deleteDirectory(storage_path('app/tmp'));
        }

        File::makeDirectory(storage_path('app/tmp'));
        File::put(storage_path('app/tmp/test.txt'), 'test');

        // Act
        $this->assertFileExists(storage_path('app/tmp/test.txt'));
        $this->artisan('recycle:temp-folder');

        // Assert
        $this->assertFileDoesNotExist(storage_path('app/tmp/test.txt'));
        $this->assertDirectoryExists(storage_path('app/tmp'));
    }

}
