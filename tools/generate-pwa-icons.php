<?php
/**
 * Generates PNG app icons from the same visual language as assets/favicon.svg.
 * Run once: php tools/generate-pwa-icons.php
 * Requires PHP GD extension.
 */
declare(strict_types=1);

if (!extension_loaded('gd')) {
    fwrite(STDERR, "GD extension is required. Enable extension=gd in php.ini.\n");
    exit(1);
}

$outDir = dirname(__DIR__) . '/assets/icons';
if (!is_dir($outDir) && !mkdir($outDir, 0755, true)) {
    fwrite(STDERR, "Cannot create {$outDir}\n");
    exit(1);
}

$sizes = [32, 72, 96, 128, 144, 152, 180, 192, 384, 512];

foreach ($sizes as $size) {
    $im = imagecreatetruecolor($size, $size);
    imagesavealpha($im, true);
    $transparent = imagecolorallocatealpha($im, 0, 0, 0, 127);
    imagefill($im, 0, 0, $transparent);

    $r = (int) max(4, round($size * 0.22));
    $teal = imagecolorallocate($im, 13, 148, 136);
    $tealDark = imagecolorallocate($im, 15, 118, 110);

    imagefilledrectangle($im, $r, 0, $size - 1 - $r, $size - 1, $teal);
    imagefilledrectangle($im, 0, $r, $size - 1, $size - 1 - $r, $teal);
    imagefilledellipse($im, $r, $r, $r * 2, $r * 2, $teal);
    imagefilledellipse($im, $size - $r, $r, $r * 2, $r * 2, $teal);
    imagefilledellipse($im, $r, $size - $r, $r * 2, $r * 2, $teal);
    imagefilledellipse($im, $size - $r, $size - $r, $r * 2, $r * 2, $teal);

    $cx = (int) ($size / 2);
    $cy = (int) round($size * 0.38);
    $pr = (int) max(3, round($size * 0.13));
    $white = imagecolorallocate($im, 255, 255, 255);
    imagefilledellipse($im, $cx, $cy, $pr * 2 + 2, $pr * 2 + 2, $white);

    $tipY = (int) round($cy + $pr * 1.35);
    $wing = (int) max(2, round($pr * 1.1));
    $poly = [
        $cx, min($size - 2, $tipY + $wing),
        $cx - $wing, (int) round($cy + $pr * 0.15),
        $cx + $wing, (int) round($cy + $pr * 0.15),
    ];
    imagefilledpolygon($im, $poly, 3, $white);

    $path = $outDir . '/icon-' . $size . '.png';
    imagepng($im, $path, 6);
    imagedestroy($im);
    echo "Wrote {$path}\n";
}

echo "Done.\n";
