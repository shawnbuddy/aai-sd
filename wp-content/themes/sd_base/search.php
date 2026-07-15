<?php
/**
 * Search results template.
 */

use Timber\Timber;

$context = Timber::context();
$context['posts'] = Timber::get_posts();
$context['search_query'] = get_search_query();
$context['template'] = 'search';

Timber::render(['src/4-pages/base.twig'], $context);
