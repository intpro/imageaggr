<?php

return [

    'res100x100' => [
        'width' => 100,
        'height' => 100,
        'when_upload' => true
    ],

    'res400' => [
        'width' => 400, 'height' => null,
    ],

    'res400mod' => [
        'width' => 400, 'height' => null,
        'mods' => [
            'wmark' => ['image_key' => 'wmimage.crop.crop400x300', 'position' => 'center', 'x' => 0, 'y' => 0],
            'mask' => ['image_key' => 'maskimage.crop.crop400x300'] //Должно получиться одинаковой с wm позиционирование из-за умолчаний
        ]
    ],

    'res800' => [
        'width' => 800, 'height' => null
    ],

    'resMod' => [
        'mods' => [
            'wmark' => ['image_key' => 'wmimage.crop.crop400x300', 'position' => 'center', 'x' => 0, 'y' => 0],
            'mask' => ['image_key' => 'maskimage.crop.crop400x300'] //Должно получиться одинаковой с wm позиционирование из-за умолчаний
        ]
    ],

    'res1000' => [
        'width' => 1000
    ]

];
