<?php
/**
 * ----------------------------------------------------------------------
 *              nekoimi <i@sakuraio.com>
 *                                          ------
 *   Copyright (c) 2017-2019 https://nekoimi.com All rights reserved.
 * ----------------------------------------------------------------------
 */
return [

    'avatar' => [
        'width'      => 100,//图片宽度
        'height'     => 100,//字体大小和图片高度
        'bgColor'    => '#797979',
        'fontSize'   => 36, // 字体大小
        'fontColor' => '#ffffff',//字体颜色
    ],

    'captcha' => [
        'expire'     => 10,     //验证码有效时间，单位（分钟）
        'characters' => '2346789abcdefghjmnpqrtuxyzABCDEFGHJMNPQRTUXYZ',
        'sensitive'  => false,//验证码大小写是否敏感
        'options'    => [
            'length'     => 4,//验证码字数
            'width'      => 180,//图片宽度
            'height'     => 50,//字体大小和图片高度
            'angle'      => 10,//验证码中字体倾斜度
            'lines'      => 0,//生成横线条数
            'quality'    => 90,//品质
            'bgImage'    => false,//是否有背景图
            'bgColor'    => '#797979',
            'blur'       => 0,//模糊度
            'sharpen'    => 0,//锐化
            'contrast'   => 0,//反差
            'fontSize'   => 36, // 字体大小
            'fontColors' => '#ffffff',//字体颜色
        ]
    ]

];
