<?php
/**
 * Front page template.
 */

use Timber\Timber;

$context = Timber::context();
$context['post'] = Timber::get_post();
$context['template'] = 'front-page';

Timber::render(['src/4-pages/base.twig'], $context);
