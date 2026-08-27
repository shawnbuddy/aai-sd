<?php

/**
 * @file
 * Routes singular content to its Timber template.
 */

use Timber\Timber;

$context = Timber::context();
$context['post'] = Timber::get_post();
$post_type = get_post_type();
$kentico_post_types = array_keys(sd_kentico_content_model());
$kentico_post_types[] = 'tribe_events';
$context['template'] = in_array($post_type, $kentico_post_types, TRUE) ? 'single-' . $post_type : 'single';

Timber::render(['src/4-pages/base.twig'], $context);
