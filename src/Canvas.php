<?php
/**
 * ----------------------------------------------------------------------
 *              nekoimi <i@sakuraio.com>
 *                                          ------
 *   Copyright (c) 2017-2019 https://nekoimi.com All rights reserved.
 * ----------------------------------------------------------------------
 */

namespace Nekoimi\Canvas;

use Illuminate\Config\Repository as Config;
use Illuminate\Filesystem\Filesystem;
use Intervention\Image\Image;
use Intervention\Image\ImageManager;
use Symfony\Component\Finder\SplFileInfo;

abstract class Canvas
{
    const VERSION = '0.1';

    /**
     * @var Filesystem
     */
    protected $files;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var ImageManager
     */
    protected $imageManager;

    /**
     * @var array
     */
    protected $fonts = [];

    /**
     * @var string
     */
    protected $text = '';

    /**
     * Canvas constructor.
     * @param array $config
     * @param Filesystem $files
     * @param ImageManager $imageManager
     */
    public function __construct(
        array $config,
        Filesystem $files,
        ImageManager $imageManager
    ) {
        $this->files = $files;
        $this->imageManager = $imageManager;

        // merge default config
        $this->config = new Config($config);

        $this->loadFonts();
    }

    /**
     * @param array $options
     */
    abstract protected function mergeConfig(array $options);

    /**
     * @param array $options
     */
    abstract public function create(array $options = []);

    /**
     * @param Image $canvas
     */
    abstract protected function renderText(Image $canvas);

    /**
     * fontSize
     */
    abstract protected function fontSize();

    /**
     * fontColor
     */
    abstract protected function fontColor();

    /**
     * @return string
     */
    protected function configKey()
    {
        $carr = explode('.', str_replace('\\', '.', strtolower(static::class)));
        return $carr[sizeof($carr) - 1];
    }

    /**
     * @param string $bit
     * @return string
     */
    protected function cacheKey(string $bit)
    {
        return str_replace(
                '/',
                '.',
                str_replace('\\', '/', strtolower(__CLASS__))
            ) . ':' . $bit;
    }

    /**
     * Load fonts.
     */
    protected function loadFonts()
    {
        $this->fonts = $this->files->files(
            __DIR__ . '/../assets/fonts/en'
        );

        $this->fonts = array_map(function (SplFileInfo $file) {
            return $file->getPathname();
        }, array_values($this->fonts));
    }
}
