<?php

namespace Interpro\ImageAggr\Contracts\Operation\Enum;

class MimeType
{
    const GIF = 'image/gif';
    const JPEG = 'image/jpeg';
    const PNG = 'image/jpeg';
    const SVG = 'image/svg+xml';
    const SVG_1 = 'image/svg';//TODO:Костыль, оказывается есть более 1го майма
}
