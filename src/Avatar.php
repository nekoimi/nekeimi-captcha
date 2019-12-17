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
use Intervention\Image\AbstractFont;
use Intervention\Image\Image;
use Symfony\Component\Finder\SplFileInfo;

class Avatar extends Canvas
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var string
     */
    protected $savePath = null;

    /**
     * Load font.
     */
    protected function loadFonts()
    {
        $this->fonts = $this->files->files(
            __DIR__ . '/../assets/fonts/cn'
        );

        $this->fonts = array_map(function (SplFileInfo $file) {
            return $file->getPathname();
        }, array_values($this->fonts));
    }

    /**
     * @param array $options
     */
    protected function mergeConfig(array $options)
    {
        $this->config = new Config(array_merge(
            $this->config->get($this->configKey(), []),
            $options
        ));
    }

    /**
     * @param array $options
     * @return mixed
     */
    public function create(array $options = [])
    {
        $this->mergeConfig($options);

        $width = $this->config->get('width', 100);
        $height = $this->config->get('height', 100);

        $canvas = $this->imageManager->canvas(
            $width,
            $height,
            $this->config->get('bgColor')
        );

        $canvas = $this->renderText($canvas);

        if (!is_null($this->savePath)) {
            $canvas->save($this->savePath, 90, 'png');
            return true;
        }
        return $canvas->response('png', 90);
    }

    /**
     * @param Image $canvas
     * @return Image
     */
    protected function renderText(Image $canvas)
    {
        $marginTop = $canvas->getHeight() / 2;
        $marginLeft = $canvas->getWidth() / 2;

        $canvas->text($this->text, $marginLeft, $marginTop, function (AbstractFont $font) {
            $font->file($this->fonts[mt_rand(0, sizeof($this->fonts) - 1)]);
            $font->size($this->fontSize());
            $font->color($this->fontColor());
            $font->align('center');
            $font->valign('center');
        });

        return $canvas;
    }

    /**
     * fontSize
     */
    protected function fontSize()
    {
        return (int)$this->config->get('fontSize');
    }

    /**
     * fontColor
     */
    protected function fontColor()
    {
        return $this->config->get('fontColor');
    }

    /**
     * @param string $text
     * @return Avatar
     */
    public function withText(string $text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * @param string $savePath
     * @return Avatar
     */
    public function withSavePath(string $savePath)
    {
        $this->savePath = $savePath;

        return $this;
    }
}
