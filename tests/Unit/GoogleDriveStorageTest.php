<?php

namespace Tests\Unit;

use App\Services\GoogleDrive\GoogleDriveAdapter;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GoogleDriveStorageTest extends TestCase
{
    #[Test]
    public function it_registers_and_resolves_the_google_drive_disk(): void
    {
        $disk = Storage::disk('google');

        $this->assertInstanceOf(FilesystemAdapter::class, $disk);
        $this->assertEquals('google', config('filesystems.disks.google.driver'));
    }

    #[Test]
    public function it_sets_root_to_configured_folder_id(): void
    {
        config(['filesystems.disks.google.folderId' => 'test-folder-id-123']);
        Storage::forgetDisk('google');

        $disk = Storage::disk('google');
        $adapter = $disk->getAdapter();

        $ref = new \ReflectionProperty($adapter, 'root');
        $this->assertEquals('test-folder-id-123', $ref->getValue($adapter));
        $this->assertInstanceOf(GoogleDriveAdapter::class, $adapter);
    }
}
