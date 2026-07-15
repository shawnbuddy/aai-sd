<?php
/**
 * Main fallback template.
 */

use Timber\Timber;

$context = Timber::context();
$context['posts'] = Timber::get_posts();
$context['template'] = 'index';

Timber::render(['src/4-pages/base.twig'], $context);
