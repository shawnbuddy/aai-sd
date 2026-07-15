<?php
/**
 * Default single template.
 */

use Timber\Timber;

$context = Timber::context();
$context['post'] = Timber::get_post();
$context['template'] = 'single';

Timber::render(['src/4-pages/base.twig'], $context);
