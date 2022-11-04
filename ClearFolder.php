<?php

namespace App\Console\Commands;

use App\Child;
use Illuminate\Console\Command;
use RecursiveIteratorIterator;
use SplFileInfo;
use Throwable;

class ClearFolder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clear:folder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    protected $dirNames = Child::DIR_NAMES;

    protected $currentFolder = null;

    protected $image;
    protected $image_type;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        foreach ($this->dirNames as $dirName) {
            if (!file_exists(__DIR__ . '/../../../public/uploads/child/' . $dirName)) {
                continue;
            }

            $dir = new \DirectoryIterator(__DIR__ . '/../../../public/uploads/child/' . $dirName);
            $this->currentFolder = $dirName;

            foreach ($dir as $childDir) {
                if ($childDir->isDot()) {
                    continue;
                }

                if (!$childDir->isDir()) {
                    dump('Найдена не директория - ' . $childDir->getFilename());
                    continue;
                }
                $this->currentChild = $childDir->getFilename();

                $childDirIterator = new \RecursiveDirectoryIterator(
                    $dir->getPath() . '/' . $this->currentChild,
                    \FilesystemIterator::SKIP_DOTS
                );

                $child = Child::withTrashed()->find($this->currentChild);

                if (!$child) {
                    $this->clearFolder($childDirIterator);
                    continue;
                }

                if ($child->trashed()) {
                    $child->$dirName = null;
                    $child->save();
                    $this->clearFolder($childDirIterator);
                    continue;
                }

                $currentChildFileName = $child->$dirName;
                $filesCount = iterator_count($childDirIterator);
                $childDirIterator->rewind();

                $counter = 0;
                $actualFileExists = false;
                foreach ($childDirIterator as $childFile) {
                    $counter++;

                    $this->resize($childFile);
                    if ($currentChildFileName == $childFile->getFilename()) {
                        $actualFileExists = true;
                        continue;
                    }

                    if ($filesCount == 1) {
                        $child->$dirName = $childFile->getFilename();
                        dump('Заменён файл ' . $this->currentFolder . ' (id = ' . $this->currentChild . ')');
                    }

                    if ($filesCount > 1) {
                        if ($counter == $filesCount && !$actualFileExists) {
                            $child->$dirName = $childFile->getFilename();
                            dump('Заменён файл ' . $this->currentFolder . ' (id = ' . $this->currentChild . ')');
                            continue;
                        }
                        unlink($childFile);
                        dump('Удалён файл (id = ' . $this->currentChild . ')');
                    }
                }

                if (iterator_count($childDirIterator) == 0) {
                    rmdir($childDirIterator->getPath());
                    $child->$dirName = null;
                    dump('Удалена папка ребёнка ' . $this->currentChild . ' (из папки ' . $this->currentFolder . ')');
                }

                $child->save();
            }
        }
    }

    protected function clearFolder(\RecursiveDirectoryIterator $childDirIterator)
    {
        $rii = new \RecursiveIteratorIterator(
            $childDirIterator,
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($rii as $file) {
            if ($rii->isDot()) {
                continue;
            }

            if ($file->isDir()) {
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }
        rmdir($childDirIterator->getPath());
        dump("Удалена папка удалённого ребёнка $this->currentChild (из папки " . $this->currentFolder . ') ');
    }

    function resize(SplFileInfo $file)
    {
        if ($file->getSize() / 1024 <= 1024) {
            dump('Фото ' . $file->getRealPath() . ' пропущено');
            return;
        }

        if (!\exif_imagetype($file->getRealPath())) {
            dump('Фото ' . $file->getRealPath() . ' пропущено (не фото)');
            return;
        }

        dump('Ресайз фото ' . $file->getRealPath() . ' (старый размер: ' . round($file->getSize() / 1024, 2) . 'кб)');
        try {
            $image_info = \getimagesize($file->getRealPath());

            $width = ceil($image_info[0] / 3);
            $height = ceil($image_info[1] / 3);
            $this->image_type = $image_info[2];

            if ($this->image_type == IMAGETYPE_JPEG) {
                $this->image = \imagecreatefromjpeg($file->getRealPath());
            } elseif ($this->image_type == IMAGETYPE_GIF) {
                $this->image = \imagecreatefromgif($file->getRealPath());
            } elseif ($this->image_type == IMAGETYPE_PNG) {
                $this->image = \imagecreatefrompng($file->getRealPath());
            } elseif ($this->image_type == IMAGETYPE_BMP) {
                $this->image = \imagecreatefrombmp($file->getRealPath());
            } elseif ($this->image_type == IMAGETYPE_TIFF_II || $this->image_type == IMAGETYPE_TIFF_MM) {
                dump('Фото ' . $file->getRealPath() . ' пропущено (TIFF формат)');
                return;
            }
        } catch (Throwable $e) {
            dump($e->getMessage());
            return;
        }

        $new_image = \imagecreatetruecolor($width, $height);

        if ($this->image_type == IMAGETYPE_PNG) {
            $this->transparentPNGBackground($new_image);
        }

        \imagecopyresampled($new_image, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());
        $this->image = $new_image;
        $this->save($file->getRealPath());

        $newSplInfo = new SplFileInfo($file->getRealPath());
        dump('Ресайз фото ' . $newSplInfo->getRealPath() . ' завершён (новый размер: ' . round($newSplInfo->getSize() / 1024, 2) . 'кб)');
    }

    function transparentPNGBackground($png)
    {
        imagesavealpha($png, true);

        $trans_colour = imagecolorallocatealpha($png, 0, 0, 0, 127);
        imagefill($png, 0, 0, $trans_colour);
    }

    function save($filename, $compression=75) {
        if( $this->image_type == IMAGETYPE_JPEG ) {
           imagejpeg($this->image,$filename,$compression);
        } elseif( $this->image_type == IMAGETYPE_GIF ) {
           imagegif($this->image,$filename);
        } elseif( $this->image_type == IMAGETYPE_PNG ) {
           imagepng($this->image,$filename);
        }
     }

    function getWidth()
    {
        return imagesx($this->image);
    }

    function getHeight()
    {
        return imagesy($this->image);
    }
}
