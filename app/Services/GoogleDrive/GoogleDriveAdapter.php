<?php

namespace App\Services\GoogleDrive;

use League\Flysystem\UnableToReadFile;
use Masbug\Flysystem\GoogleDriveAdapter as BaseGoogleDriveAdapter;

class GoogleDriveAdapter extends BaseGoogleDriveAdapter
{
    /**
     * {@inheritdoc}
     */
    public function listContents(string $directory, bool $recursive): iterable
    {
        try {
            $iterator = parent::listContents($directory, $recursive);

            foreach ($iterator as $item) {
                yield $item;
            }
        } catch (UnableToReadFile) {
            return;
        }
    }
}
