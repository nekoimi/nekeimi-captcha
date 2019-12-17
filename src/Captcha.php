<?php
/**
 * ----------------------------------------------------------------------
 *              nekoimi <i@sakuraio.com>
 *                                          ------
 *   Copyright (c) 2017-2019 https://nekoimi.com All rights reserved.
 * ----------------------------------------------------------------------
 */

namespace Nekoimi\Canvas;

use Illuminate\Cache\CacheManager;
use Illuminate\Config\Repository as Config;
use Illuminate\Contracts\Cache\Store;
use Illuminate\Filesystem\Filesystem;
use Intervention\Image\AbstractFont;
use Intervention\Image\Gd\Shapes\LineShape;
use Intervention\Image\Image;
use Intervention\Image\ImageManager;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpFoundation\Response;

class Captcha extends Canvas
{

    /**
     * @var Config
     */
    protected $options;

    /**
     * @var Store
     */
    protected $cache;

    /**
     * @var SplFileInfo[]
     */
    protected $backgroundImages = [];

    /**
     * @var string
     */
    protected $state = '';

    /**
     * Captcha constructor.
     * @param array $config
     * @param CacheManager $cache
     * @param Filesystem $files
     * @param ImageManager $imageManager
     */
    public function __construct(
        array $config,
        CacheManager $cache,
        Filesystem $files,
        ImageManager $imageManager
    ) {
        parent::__construct($config, $files, $imageManager);
        $this->cache = $cache->getStore();

        $this->config = new Config(
            $this->config->get($this->configKey(), [])
        );
        $this->loadBackgroundImages();
    }

    /**
     * Merge config.
     *
     * @param array $options
     */
    protected function mergeConfig(array $options)
    {
        $this->config->set('options', array_merge(
            $this->config->get('options', []),
            $options
        ));

        $this->options = new Config(
            $this->config->get('options', [])
        );
    }

    /**
     * Load background image and font.
     */
    protected function loadBackgroundImages()
    {
        $this->backgroundImages = $this->files->files(
            __DIR__ . '/../assets/backgrounds'
        );
    }

    /**
     * Generate
     */
    protected function generate()
    {
        $characters = str_split($this->config->get('characters'));
        $captchaValue = '';
        for ($i = 0; $i < (int)$this->options->get('length'); $i ++) {
            $captchaValue .= $characters[rand(0, count($characters) - 1)];
        }

        $this->cacheCaptchaValue($captchaValue);

        $this->text = $captchaValue;
    }

    /**
     * @param string $captchaValue
     */
    protected function cacheCaptchaValue(string $captchaValue)
    {
        $this->cache->put(
            $this->cacheKey($this->state),
            $captchaValue,
            $this->config->get('expire', 10)
        );
    }

    /**
     * @param Image $canvas
     * @return Image
     */
    protected function renderText(Image $canvas)
    {
        $confLength = $this->options->get('length', 4);
        $marginTop = $canvas->getHeight() / $confLength;

        $i = 0;
        foreach (str_split($this->text) as $char) {
            $marginLeft = ($i * $canvas->getWidth() / $confLength) + 10;

            $canvas->text($char, $marginLeft, $marginTop, function (AbstractFont $font) {
                $font->file($this->fonts[mt_rand(0, sizeof($this->fonts) - 1)]);
                $font->size($this->fontSize());
                $font->color($this->fontColor());
                $font->align('left');
                $font->valign('top');
                $font->angle($this->angle());
            });

            $i ++;
        }

        return $canvas;
    }

    /**
     * @return int
     */
    protected function fontSize()
    {
        return (int)$this->options->get('fontSize');
    }

    /**
     * @return string
     */
    protected function fontColor()
    {
        $confFontColor = $this->options->get('fontColors');
        if (is_string($confFontColor)) {
            return $confFontColor;
        }

        if (is_array($confFontColor)) {
            return $confFontColor[mt_rand(0, sizeof($confFontColor) - 1)];
        }

        return '#555555';
    }

    /**
     * Angle
     *
     * @return int
     */
    protected function angle()
    {
        $confFontAngle = (int)$this->options->get('angle');
        return rand((- 1 * $confFontAngle), $confFontAngle);
    }

    /**
     * Random image lines
     *
     * @param Image $canvas
     * @return Image
     */
    protected function lines(Image $canvas)
    {
        $confLines = (int)$this->options->get('lines');
        for ($i = 0; $i < $confLines; $i++) {
            $canvas->line(
                rand(0, $canvas->width()) + $i * rand(0, $canvas->height()),
                rand(0, $canvas->height()),
                rand(0, $canvas->width()),
                rand(0, $canvas->height()),
                function (LineShape $draw) {
                    $draw->color($this->fontColor());
                }
            );
        }
        return $canvas;
    }

    /**
     * Create new captcha.
     *
     * @param array $options
     * @return Response
     */
    public function create(array $options = [])
    {
        $this->mergeConfig($options);

        $width = $this->options->get('width', 100);
        $height = $this->options->get('height', 30);

        $this->generate();
        $canvas = $this->imageManager->canvas(
            $width,
            $height,
            $this->options->get('bgColor')
        );

        if ($this->options->get('bgImage')) {
            $bgImage = $this->imageManager->make(
                $this->backgroundImages[mt_rand(0, sizeof($this->backgroundImages) - 1)]
            )->resize($width, $height);

            $canvas->insert($bgImage);
        }

        if (($contrast = (int)$this->options->get('contrast')) !== 0) {
            $canvas->contrast($contrast);
        }

        $canvas = $this->renderText($canvas);

        $canvas = $this->lines($canvas);

        if (($sharpen = (int)$this->options->get('sharpen')) > 0) {
            $canvas->sharpen($sharpen);
        }

        if (($blur = (int)$this->options->get('blur')) > 0) {
            $canvas->blur($blur);
        }

        return $canvas->response('png', (int)$this->options->get('quality'));
    }

    /**
     * Check captcha value.
     *
     * @param string $state
     * @param string $value
     * @return bool
     */
    public function check(string $state, string $value): bool
    {
        if (empty($cacheValue = $this->cache->get($this->cacheKey($state)))) {
            return false;
        }

        $sensitive = (bool)$this->config->get('sensitive', false);
        if (!$sensitive) {
            $value = mb_strtolower($value);
            $cacheValue = mb_strtolower($cacheValue);
        }

        $this->cache->forget($this->cacheKey($state));

        return hash_equals($cacheValue, $value);
    }

    /**
     * @param string $state
     * @return Captcha
     */
    public function withState(string $state)
    {
        $this->state = $state;

        return $this;
    }
}
