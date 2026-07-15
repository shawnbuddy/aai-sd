<?php
/**
 * SD Base theme bootstrap.
 *
 * Keeps Timber/Twig rendering and generic ACF block registration
 * while removing project-specific business logic.
 */

use Timber\Timber;

// Prevent canonical redirects from breaking static assets.
add_filter('redirect_canonical', function ($redirect_url, $requested_url) {
  if (strpos($requested_url, '/wp-content/') !== false) {
    return false;
  }
  return $redirect_url;
}, 10, 2);

/**
 * Locate and load Composer autoloader.
 */
function sd_theme_load_autoloader(): void {
  $autoload_paths = [
    __DIR__ . '/../../../../vendor/autoload.php', // project root /vendor
    __DIR__ . '/../../../vendor/autoload.php',    // web /vendor fallback
    __DIR__ . '/../../vendor/autoload.php',       // wp-content /vendor fallback
  ];

  foreach ($autoload_paths as $autoload_path) {
    if (file_exists($autoload_path)) {
      require_once $autoload_path;
      return;
    }
  }
}
sd_theme_load_autoloader();

/**
 * Load modular theme include files.
 */
function sd_theme_load_includes(): void {
  $include_files = [
    __DIR__ . '/inc/plan-content-type.php',
  ];

  foreach ($include_files as $include_file) {
    if (file_exists($include_file)) {
      require_once $include_file;
    }
  }
}
sd_theme_load_includes();

/**
 * Configure Timber when available.
 */
function sd_theme_bootstrap_timber(): void {
  if (!class_exists('\Timber\Timber')) {
    return;
  }

  Timber::init();
  Timber::$dirname = [
    'src/1-elements',
    'src/2-components',
    'src/3-sections',
    'src/4-pages',
  ];
}
sd_theme_bootstrap_timber();

/**
 * Build absolute URL for compiled theme assets.
 */
function sd_asset_url(string $asset): string {
  return trailingslashit(get_template_directory_uri()) . 'dist/' . ltrim($asset, '/');
}

/**
 * Shared context values available to Twig templates.
 */
add_filter('timber/context', function ($context) {
  $protocol = is_ssl() ? 'https://' : 'http://';
  $host = $_SERVER['HTTP_HOST'] ?? '';
  $request_uri = $_SERVER['REQUEST_URI'] ?? '';

  $context['current_url'] = esc_url($protocol . $host . $request_uri);
  $context['document_title'] = wp_get_document_title();
  $context['title'] = is_singular() ? get_the_title() : (get_the_archive_title() ?: wp_get_document_title());
  $context['post'] = class_exists('\Timber\Timber') ? Timber::get_post() : null;

  return $context;
});

/**
 * Register theme supports and menus.
 */
function sd_theme_setup(): void {
  add_theme_support('post-thumbnails');
  add_theme_support('title-tag');
  add_theme_support('menus');
  add_theme_support('align-wide');
  add_theme_support('custom-logo');

  register_nav_menus([
    'main-menu' => __('Main Menu', 'sd_base'),
    'footer-menu' => __('Footer Menu', 'sd_base'),
  ]);
}
add_action('after_setup_theme', 'sd_theme_setup');

/**
 * Enqueue global frontend assets.
 */
function sd_enqueue_global_assets(): void {
  $css_assets = [
    'global' => 'css/global.css',
    'header' => 'css/header.css',
    'footer' => 'css/footer.css',
    'page-content' => 'css/page-content.css',
  ];

  foreach ($css_assets as $handle => $file) {
    $absolute_path = get_template_directory() . '/dist/' . $file;
    if (file_exists($absolute_path)) {
      wp_enqueue_style('sd-' . $handle, sd_asset_url($file), [], null);
    }
  }

  $header_js = get_template_directory() . '/dist/js/header.min.js';
  if (file_exists($header_js)) {
    wp_enqueue_script('sd-header', sd_asset_url('js/header.min.js'), [], null, true);
  }
}
add_action('wp_enqueue_scripts', 'sd_enqueue_global_assets');

/**
 * Editor styles for closer frontend parity.
 */
function sd_enqueue_editor_assets(): void {
  $editor_files = [
    'css/global.css',
    'css/page-content.css',
  ];

  foreach ($editor_files as $file) {
    $absolute_path = get_template_directory() . '/dist/' . $file;
    if (file_exists($absolute_path)) {
      wp_enqueue_style('sd-editor-' . sanitize_title($file), sd_asset_url($file), [], null);
    }
  }
}
add_action('enqueue_block_editor_assets', 'sd_enqueue_editor_assets');

/**
 * Load per-template CSS/JS if compiled assets exist.
 */
function load_assets(string $template_name): void {
  $template_name = sanitize_key($template_name);
  $css_relative = "css/{$template_name}.css";
  $js_min_relative = "js/{$template_name}.min.js";
  $js_relative = "js/{$template_name}.js";

  if (file_exists(get_template_directory() . '/dist/' . $css_relative)) {
    wp_enqueue_style('sd-template-' . $template_name, sd_asset_url($css_relative), [], null);
  }

  if (file_exists(get_template_directory() . '/dist/' . $js_min_relative)) {
    wp_enqueue_script('sd-template-' . $template_name, sd_asset_url($js_min_relative), [], null, true);
  } elseif (file_exists(get_template_directory() . '/dist/' . $js_relative)) {
    wp_enqueue_script('sd-template-' . $template_name, sd_asset_url($js_relative), [], null, true);
  }
}

// ---------- ACF BLOCKS: DATA-DRIVEN REGISTRATION ----------
if (!defined('ABSPATH')) {
  exit;
}

$GLOBALS['sd_acf_block_templates'] = [];

/**
 * Helper to define ACF fields with sane defaults.
 */
function sd_acf_field(string $key, string $label, string $name, string $type, array $additional = []): array {
  return array_merge([
    'key'      => $key,
    'label'    => $label,
    'name'     => $name,
    'type'     => $type,
    'required' => 0,
    'wrapper'  => ['width' => '', 'class' => '', 'id' => ''],
  ], $additional);
}

/**
 * Generic Twig render callback for all registered ACF blocks.
 */
function sd_render_block_generic(array $block, string $content = '', bool $is_preview = false, $post_id = 0): void {
  $block_name = str_replace('acf/', '', $block['name'] ?? '');
  $block_data = is_array($block) ? (get_fields($block['id'] ?? '') ?: []) : [];

  $block_data += [
    '_block'      => $block,
    '_is_preview' => $is_preview,
    '_post_id'    => $post_id,
  ];

  $explicit_template = $GLOBALS['sd_acf_block_templates'][$block_name] ?? null;
  $dirs = apply_filters('sd/acf_block_template_dirs', [
    'src/2-components',
    'src/3-sections',
    'src/1-elements',
    'src/4-pages',
  ]);

  $candidates = [];
  if (is_string($explicit_template) && $explicit_template !== '') {
    $candidates[] = ltrim($explicit_template, '/');
  } else {
    foreach ($dirs as $dir) {
      $candidates[] = "{$dir}/{$block_name}/{$block_name}.twig";
    }
  }

  if (class_exists('\Timber\Timber')) {
    $root = get_template_directory();
    foreach ($candidates as $relative_path) {
      $absolute_path = $root . '/' . $relative_path;
      if (file_exists($absolute_path)) {
        echo Timber::compile($absolute_path, $block_data);
        return;
      }
    }

    echo '<p>Template not found for block "' . esc_html($block_name) . '".</p>';
    return;
  }

  echo '<div class="' . esc_attr($block_name) . '">';
  echo '<p>Timber is unavailable. Block "' . esc_html($block_name) . '" cannot render.</p>';
  echo '</div>';
}

/**
 * Register a small generic set of ACF blocks and fields.
 */
function sd_register_acf_blocks(): void {
  if (!function_exists('acf_register_block_type') || !function_exists('acf_add_local_field_group')) {
    return;
  }

  $defaults = [
    'category'      => 'design',
    'keywords'      => [],
    'post_types'    => ['post', 'page'],
    'mode'          => 'preview',
    'supports'      => ['align' => true, 'mode' => false, 'jsx' => true],
    'icon'          => 'block-default',
    'enqueue_style' => sd_asset_url('css/global.css'),
  ];

  $blocks = [
    [
      'name'        => 'teaser-block',
      'title'       => 'Teaser Block',
      'description' => 'Teaser with text, image, and CTA.',
      'icon'        => 'admin-post',
      'keywords'    => ['teaser', 'content', 'cta'],
      'fields'      => [
        sd_acf_field('field_teaser_title', 'Title', 'title', 'text'),
        sd_acf_field('field_teaser_description', 'Description', 'description', 'textarea', ['rows' => 4]),
        sd_acf_field('field_teaser_image', 'Image', 'image', 'image', ['return_format' => 'array']),
        sd_acf_field('field_teaser_cta', 'CTA', 'cta', 'link', ['return_format' => 'array']),
      ],
      'template'    => 'src/2-components/teaser-block/teaser-block.twig',
    ],
    [
      'name'        => 'hero-block',
      'title'       => 'Hero Block',
      'description' => 'Hero section with eyebrow, headline, and CTA.',
      'icon'        => 'align-wide',
      'keywords'    => ['hero', 'banner', 'header'],
      'fields'      => [
        sd_acf_field('field_hero_eyebrow', 'Eyebrow', 'eyebrow', 'text'),
        sd_acf_field('field_hero_headline', 'Headline', 'headline', 'text'),
        sd_acf_field('field_hero_background_image', 'Background Image', 'background_image', 'image', ['return_format' => 'array']),
        sd_acf_field('field_hero_primary_cta', 'Primary CTA', 'primary_cta', 'link', ['return_format' => 'array']),
      ],
      'template'    => 'src/3-sections/hero-block/hero-block.twig',
    ],
  ];

  foreach ($blocks as $block) {
    $block = array_merge($defaults, $block);
    $name = $block['name'];
    $group_key = 'group_' . $name;

    if (!empty($block['template'])) {
      $GLOBALS['sd_acf_block_templates'][$name] = ltrim($block['template'], '/');
    }

    acf_register_block_type([
      'name'            => $name,
      'title'           => $block['title'],
      'description'     => $block['description'],
      'category'        => $block['category'],
      'icon'            => $block['icon'],
      'keywords'        => $block['keywords'],
      'post_types'      => $block['post_types'],
      'mode'            => $block['mode'],
      'supports'        => $block['supports'],
      'render_callback' => 'sd_render_block_generic',
      'enqueue_style'   => $block['enqueue_style'],
    ]);

    acf_add_local_field_group([
      'key'                   => $group_key,
      'title'                 => ucfirst(str_replace('-', ' ', $name)) . ' Fields',
      'fields'                => $block['fields'],
      'location'              => [[['param' => 'block', 'operator' => '==', 'value' => 'acf/' . $name]]],
      'menu_order'            => 0,
      'position'              => 'normal',
      'style'                 => 'default',
      'label_placement'       => 'top',
      'instruction_placement' => 'label',
      'hide_on_screen'        => '',
      'active'                => true,
      'description'           => '',
    ]);
  }
}
add_action('acf/init', 'sd_register_acf_blocks');

