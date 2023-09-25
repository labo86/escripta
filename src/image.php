<?php
declare(strict_types=1);

/**
 * https://github.com/edwrodrig/image
 * https://wiki.inkscape.org/wiki/index.php/Using_the_Command_Line
 *
 * Otros comandos para evaluar su integración
 *
 * #redimensionar la imagen tal que se contenga en el ancho y alto especificado y considere pixeles transparentes
 * convert -strip -interlace Plane -quality 85% $INPUT $OUTPUT
 *
 * convert $OUTPUT \
 * -set option:distort:viewport "%[fx:max(w,h)]x%[fx:max(w,h)]+%[fx:(w-max(w,h))/2]+%[fx:(h-max(w,h))/2]" \
 * -virtual-pixel transparent -filter point -distort SRT 0 +repage \
 * $OUTPUT
 *
 * inkscape --version
 * Inkscape 1.3 (1:1.3+202307231459+0e150ed6c4)
 *
 * convert --version
 * Version: ImageMagick 6.9.11-60 Q16 x86_64 2021-01-25 https://imagemagick.org
 * Copyright: (C) 1999-2021 ImageMagick Studio LLC
 * License: https://imagemagick.org/script/license.php
 * Features: Cipher DPC Modules OpenMP(4.5)
 * Delegates (built-in): bzlib djvu fftw fontconfig freetype heic jbig jng jp2 jpeg lcms lqr ltdl lzma openexr pangocairo png tiff webp wmf x xml zlib
 */


/**
 * @throws Exception
 */
function imageColorize(string $inputPath, string $outputPath, string $color) : void {
    if (!file_exists($inputPath)) {
        throw new Exception("File {$inputPath} does not exist");
    }

    $escapedInputPath = escapeshellarg($inputPath);
    $escapedOutputPath = escapeshellarg($outputPath);

    $command = <<<EOF
convert \
    {$escapedInputPath} \
    -matte \
    -fill "{$color}" \
    -colorize 100% \
    {$escapedOutputPath}
EOF;

        passthru($command);

}

/**
 * redimensionar la imagen tal que se contenga en el ancho y alto especificado y considere pixeles transparentes
 * @throws Exception
 */
function imageCoverArea(string $inputPath, string $outputPath, int $width, int $height) : void {
    if (!file_exists($inputPath)) {
        throw new Exception("File {$inputPath} does not exist");
    }

    $escapedInputPath = escapeshellarg($inputPath);
    $escapedOutputPath = escapeshellarg($outputPath);

    $command = <<<EOF
convert \
    {$escapedInputPath} \
    -background none \
    -resize {$width}x{$height}^ \
    -gravity center \
    -extent {$width}x{$height} \
    {$escapedOutputPath}
EOF;

        passthru($command);
}

/**
 * funciona con Inkscape 1.2.2 (1:1.2.2+202212051552+b0a8486541)
 * instalado desde el ppa oficial de inkscape para ubuntu 22.04 - 2023-02-23
 * @throws Exception
 */
function imageSvgToImage(string $inputPath, string $outputPath, int $dpi = 2000) : void
{
    if (!file_exists($inputPath)) {
        throw new Exception("File {$inputPath} does not exist");
    }

    $escapedInputPath = escapeshellarg($inputPath);
    $escapedOutputPath = escapeshellarg($outputPath);

    $command = <<<EOF
inkscape \
--export-filename={$escapedOutputPath} \
--export-type=png \
--export-dpi={$dpi} \
 {$escapedInputPath}
EOF;

    echo $command;

    passthru($command);
}

/**
 * scale to cover in the width and height specified
 * @param string $inputPath
 * @param string $outputPath
 * @param int $width
 * @param int $height
 * @return void
 * @throws Exception
 */
function imageResize(string $inputPath, string $outputPath, int $width, int $height) : void {
    if (!file_exists($inputPath)) {
        throw new Exception("File {$inputPath} does not exist");
    }

    $escapedInputPath = escapeshellarg($inputPath);
    $escapedOutputPath = escapeshellarg($outputPath);

    $command = <<<EOF
convert \
    {$escapedInputPath} \
    -resize {$width}x{$height} \
    {$escapedOutputPath}
EOF;

    passthru($command);
}

/**
 * @throws Exception
 */
function imageContainArea(string $inputPath, string $outputPath, int $width, int $height) : void {
    if (!file_exists($inputPath)) {
        throw new Exception("File {$inputPath} does not exist");
    }

    $escapedInputPath = escapeshellarg($inputPath);
    $escapedOutputPath = escapeshellarg($outputPath);

    $command = <<<EOF
convert \
    {$escapedInputPath} \
    -background none \
    -resize {$width}x{$height} \
    -gravity center \
    -extent {$width}x{$height} \
    {$escapedOutputPath}
EOF;

    passthru($command);
}






