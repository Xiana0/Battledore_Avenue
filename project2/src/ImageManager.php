<?php

namespace PHPMaker2026\Project1;

use Intervention\Image\ImageManager as InterventionImageManager;
use Intervention\Image\Interfaces\ImageInterface;
use Intervention\Image\Encoders\AutoEncoder;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Symfony\Component\Mime\MimeTypes;

class ImageManager
{
    private readonly InterventionImageManager $manager;

    /**
     * Constructor for ImageManager service.
     *
     * @param string|DriverInterface $driver Image processing driver (GdDriver::class or ImagickDriver::class). Defaults to GdDriver::class.
     * @param int $quality Image output quality (0-100). Defaults to 100 (best quality).
     * @param bool $keepAspectRatio Whether to preserve aspect ratio during resizing. Defaults to true.
     * @param bool $resizeUp Whether to allow upscaling smaller images. Defaults to false.
     */
    public function __construct(
        string|DriverInterface $driver = GdDriver::class,
        private readonly int $quality = 100,
        private readonly bool $keepAspectRatio = true,
        private readonly bool $resizeUp = false,
    ) {
        $this->manager = new InterventionImageManager($driver);
    }

    /**
     * Checks whether a given image file or binary string is supported.
     *
     * @param string $file Binary image data or file path.
     * @return bool True if supported; false otherwise.
     */
    public function isSupported(string $file): bool
    {
        try {
            $this->manager->read($file);
            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Reads an image from binary data or file path.
     *
     * @param mixed $file Binary image data or file path.
     * @return ImageInterface
     */
    public function read(mixed $file): ImageInterface
    {
        return $this->manager->read($file);
    }

    /**
     * Resizes an image and returns an ImageInterface object.
     *
     * @param mixed          $file            Binary image data or file path.
     * @param ?int           $width           Target width.
     * @param ?int           $height          Target height.
     * @param ?callable      $callback        Optional callback to further manipulate the image.
     *                                        Signature: fn(ImageInterface): ImageInterface
     * @param ?bool          $keepAspectRatio Optional Keep aspect ratio.
     * @param ?bool          $resizeUp        Optional Resize up.
     * @return ImageInterface The resized and optionally encoded image.
     */
    public function resizeToImage(mixed $file, ?int $width = null, ?int $height = null, ?callable $callback = null, ?bool $keepAspectRatio = null, ?bool $resizeUp = null): ImageInterface
    {
        $image = $this->read($file);
        $width = $width > 0 ? $width : null;
        $height = $height > 0 ? $height : null;
        $keepAspectRatio ??= $this->keepAspectRatio;
        $resizeUp ??= $this->resizeUp;
        if ($keepAspectRatio) {
            $image = !$resizeUp
                ? $image->scaleDown($width, $height)
                : $image->scale($width, $height);
        } else {
            $image = !$resizeUp
                ? $image->resizeDown($width, $height)
                : $image->resize($width, $height);
        }
        if ($callback !== null) {
            $image = $callback($image);
        }
        return $image;
    }

    /**
     * Resizes an image and returns the result as binary data.
     *
     * @param mixed       $file   Binary image data or file path.
     * @param ?int        $width  Target width.
     * @param ?int        $height Target height.
     * @param ?callable   $callback Optional callback to further manipulate the image.
     *                                 Signature: fn(ImageInterface): ImageInterface
     * @param ?bool       $keepAspectRatio Optional Keep aspect ratio.
     * @param ?bool       $resizeUp        Optional Resize up.
     * @return string Resized image as binary string.
     */
    public function resize(mixed $file, ?int $width = null, ?int $height = null, ?callable $callback = null, ?bool $keepAspectRatio = null, ?bool $resizeUp = null): string
    {
        $image = $this->resizeToImage($file, $width, $height, $callback, $keepAspectRatio, $resizeUp);
        return (string)$image->encode(new AutoEncoder(quality: $this->quality));
    }
}
