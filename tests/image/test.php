<?php
declare(strict_types=1);

require_once(__DIR__ . '/../../src/image.php');

const VAR_DIR = __DIR__ . '/../../var';
const TEST_IMAGE = __DIR__ . '/../data/ssj.png';
const TEST_SVG_IMAGE = __DIR__ . '/../data/logo.svg';

imageContainArea(TEST_IMAGE, VAR_DIR . '/test-contain-area.png', 100, 100);
imageCoverArea(TEST_IMAGE, VAR_DIR . '/test-cover-area.png', 100, 100);
imageResize(TEST_IMAGE, VAR_DIR . '/test-resize.png', 100, 50);
imageColorize(TEST_IMAGE, VAR_DIR . '/test-colorize.png', 'red');
imageSvgToImage(TEST_SVG_IMAGE, VAR_DIR . '/test-svg-to-image.png', 3000);
