<?php
/**
 * Default archive template.
 */

use Timber\Timber;

$context = Timber::context();
$context['posts'] = Timber::get_posts();
$context['template'] = 'archive';

Timber::render(['src/4-pages/base.twig'], $context);
