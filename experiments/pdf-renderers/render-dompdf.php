<?php

require '/opt/renderer/vendor/autoload.php';

use Composer\InstalledVersions;
use Dompdf\Dompdf;
use Dompdf\Options;

$fixtures = json_decode(file_get_contents('/work/fixtures.json'), true, flags: JSON_THROW_ON_ERROR);
$repeats = (int) (getenv('PDF_SPIKE_REPEATS') ?: 3);
if ($repeats < 1 || $repeats > 5) {
    throw new RuntimeException('Expected one to five measured repetitions.');
}
$results = [];
foreach ($fixtures as $fixture) {
    $times = [];
    for ($iteration = 0; $iteration < $repeats; $iteration++) {
        $started = hrtime(true);
        $cache = '/tmp/fonts-'.$fixture['id'].'-'.$iteration;
        mkdir($cache, 0700, true);
        $options = new Options([
            'isRemoteEnabled' => false,
            'isPhpEnabled' => false,
            'isJavascriptEnabled' => false,
            'isFontSubsettingEnabled' => true,
            'chroot' => ['/work', '/opt/pdf-fonts'],
            'fontDir' => $cache,
            'fontCache' => $cache,
            'tempDir' => '/tmp',
        ]);
        $renderer = new Dompdf($options);
        $renderer->setPaper(strtolower($fixture['paper']));
        $renderer->loadHtml(file_get_contents('/work/html/'.$fixture['id'].'.html'), 'UTF-8');
        $renderer->render();
        $renderer->getCanvas()->page_script(function ($pageNumber, $pageCount, $canvas, $fontMetrics): void {
            $font = $fontMetrics->getFont('DemoSans', 'normal');
            $text = "Dompdf (Demo) | Page {$pageNumber} of {$pageCount}";
            $size = 8;
            $width = $fontMetrics->getTextWidth($text, $font, $size);
            $canvas->text(($canvas->get_width() - $width) / 2, $canvas->get_height() - 32, $text, $font, $size, [0.33, 0.43, 0.51]);
        });
        $pdf = $renderer->output();
        $times[] = round((hrtime(true) - $started) / 1e6, 2);
        file_put_contents('/work/dompdf/'.$fixture['id'].'.pdf', $pdf);
        unset($renderer);
        gc_collect_cycles();
    }
    $results[] = ['id' => $fixture['id'], 'milliseconds' => $times];
    echo 'Dompdf: '.$fixture['id'].' '.json_encode($times)." ms\n";
}
$fonts = [];
foreach (glob('/opt/pdf-fonts/*.ttf') as $font) {
    $fonts[basename($font)] = hash_file('sha256', $font);
}
file_put_contents('/work/dompdf/metrics.json', json_encode([
    'engine' => 'dompdf', 'version' => InstalledVersions::getPrettyVersion('dompdf/dompdf'),
    'php' => PHP_VERSION, 'repeats' => $repeats, 'font_sha256' => $fonts,
    'timing_scope' => 'Fresh Dompdf instance and font cache per repetition; HTML load through PDF bytes, file write excluded.',
    'fixtures' => $results,
], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
