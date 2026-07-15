<?php
/**
 * 404 template.
 */

use Timber\Timber;

$context = Timber::context();
$context['template'] = '404';

Timber::render(['src/4-pages/base.twig'], $context);
