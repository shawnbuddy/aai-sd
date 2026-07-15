<?php
/**
 * Plan content type and ACF fields.
 */

if (!defined('ABSPATH')) {
  exit;
}

/**
 * Register the Plan post type for plan-year content management.
 */
function sd_register_plan_post_type(): void {
  $labels = [
    'name'               => __('Plans', 'sd_base'),
    'singular_name'      => __('Plan', 'sd_base'),
    'menu_name'          => __('Plans', 'sd_base'),
    'name_admin_bar'     => __('Plan', 'sd_base'),
    'add_new'            => __('Add New', 'sd_base'),
    'add_new_item'       => __('Add New Plan', 'sd_base'),
    'new_item'           => __('New Plan', 'sd_base'),
    'edit_item'          => __('Edit Plan', 'sd_base'),
    'view_item'          => __('View Plan', 'sd_base'),
    'all_items'          => __('All Plans', 'sd_base'),
    'search_items'       => __('Search Plans', 'sd_base'),
    'not_found'          => __('No plans found.', 'sd_base'),
    'not_found_in_trash' => __('No plans found in Trash.', 'sd_base'),
  ];

  register_post_type('plan', [
    'labels'             => $labels,
    'public'             => true,
    'show_ui'            => true,
    'show_in_menu'       => true,
    'show_in_rest'       => true,
    'has_archive'        => true,
    'rewrite'            => ['slug' => 'plans'],
    'menu_icon'          => 'dashicons-clipboard',
    'supports'           => ['title', 'excerpt', 'thumbnail', 'revisions', 'page-attributes'],
    'publicly_queryable' => true,
    'taxonomies'         => ['plan_year', 'plan_type'],
  ]);
}
add_action('init', 'sd_register_plan_post_type');

/**
 * Disable the block editor for Plan posts.
 */
function sd_disable_plan_block_editor(bool $use_block_editor, string $post_type): bool {
  if ($post_type === 'plan') {
    return false;
  }

  return $use_block_editor;
}
add_filter('use_block_editor_for_post_type', 'sd_disable_plan_block_editor', 10, 2);

/**
 * Register taxonomy used for plan year grouping.
 */
function sd_register_plan_year_taxonomy(): void {
  register_taxonomy('plan_year', ['plan'], [
    'labels' => [
      'name' => __('Plan Years', 'sd_base'),
      'singular_name' => __('Plan Year', 'sd_base'),
      'search_items' => __('Search Plan Years', 'sd_base'),
      'all_items' => __('All Plan Years', 'sd_base'),
      'edit_item' => __('Edit Plan Year', 'sd_base'),
      'update_item' => __('Update Plan Year', 'sd_base'),
      'add_new_item' => __('Add New Plan Year', 'sd_base'),
      'new_item_name' => __('New Plan Year Name', 'sd_base'),
      'menu_name' => __('Plan Years', 'sd_base'),
    ],
    'public' => false,
    'show_ui' => true,
    'show_admin_column' => true,
    'show_in_rest' => true,
    'hierarchical' => false,
    'rewrite' => false,
    'meta_box_cb' => false,
  ]);
}
add_action('init', 'sd_register_plan_year_taxonomy');

/**
 * Register taxonomy used for plan type grouping.
 */
function sd_register_plan_type_taxonomy(): void {
  register_taxonomy('plan_type', ['plan'], [
    'labels' => [
      'name' => __('Plan Types', 'sd_base'),
      'singular_name' => __('Plan Type', 'sd_base'),
      'search_items' => __('Search Plan Types', 'sd_base'),
      'all_items' => __('All Plan Types', 'sd_base'),
      'edit_item' => __('Edit Plan Type', 'sd_base'),
      'update_item' => __('Update Plan Type', 'sd_base'),
      'add_new_item' => __('Add New Plan Type', 'sd_base'),
      'new_item_name' => __('New Plan Type Name', 'sd_base'),
      'menu_name' => __('Plan Types', 'sd_base'),
    ],
    'public' => false,
    'show_ui' => true,
    'show_admin_column' => true,
    'show_in_rest' => true,
    'hierarchical' => false,
    'rewrite' => false,
    'meta_box_cb' => false,
  ]);
}
add_action('init', 'sd_register_plan_type_taxonomy');

/**
 * Ensure required default terms exist for plan taxonomies.
 */
function sd_seed_plan_taxonomy_terms(): void {
  $terms_by_taxonomy = [
    'plan_year' => ['2026', '2027'],
    'plan_type' => ['MLTC', 'MAP'],
  ];

  foreach ($terms_by_taxonomy as $taxonomy => $terms) {
    if (!taxonomy_exists($taxonomy)) {
      continue;
    }

    foreach ($terms as $term_name) {
      if (!term_exists($term_name, $taxonomy)) {
        wp_insert_term($term_name, $taxonomy);
      }
    }
  }
}
add_action('init', 'sd_seed_plan_taxonomy_terms', 30);

/**
 * Reusable field builder for Plan ACF definitions.
 */
function sd_plan_field(string $key, string $label, string $name, string $type, array $additional = []): array {
  return array_merge([
    'key'               => $key,
    'label'             => $label,
    'name'              => $name,
    'type'              => $type,
    'required'          => 0,
    'wrapper'           => ['width' => '', 'class' => '', 'id' => ''],
    'instructions'      => '',
    'conditional_logic' => 0,
  ], $additional);
}

/**
 * Canonical list of plan benefits for comparison.
 */
function sd_plan_benefit_choices(): array {
  return [
    '$0 PCP copay' => '$0 PCP copay',
    'Specialist' => 'Specialist',
    'Over the counter (OTC)' => 'Over the counter (OTC)',
    'Transportation/Community Rides' => 'Transportation/Community Rides',
    'Dental' => 'Dental',
    'Vision' => 'Vision',
    'Hearing' => 'Hearing',
    'Acupuncture' => 'Acupuncture',
    'Flex Card' => 'Flex Card',
    'Worldwide travel assistance' => 'Worldwide travel assistance',
    'Gym membership' => 'Gym membership',
    'Wellness Incentive Program' => 'Wellness Incentive Program',
    'BrainHQ memory fitness' => 'BrainHQ memory fitness',
    'Member-to-Member Community access' => 'Member-to-Member Community access',
  ];
}

/**
 * Shared sub-fields for image/video media entries.
 */
function sd_plan_media_sub_fields(string $prefix): array {
  $media_type_key = 'field_' . $prefix . '_media_type';

  return [
    sd_plan_field($media_type_key, 'Media Type', 'media_type', 'select', [
      'required' => 1,
      'choices' => [
        'image' => 'Image',
        'video' => 'Video',
      ],
      'default_value' => 'image',
      'return_format' => 'value',
      'ui' => 1,
      'wrapper' => ['width' => '25', 'class' => '', 'id' => ''],
    ]),
    sd_plan_field('field_' . $prefix . '_image', 'Image', 'image', 'image', [
      'return_format' => 'array',
      'preview_size' => 'medium',
      'library' => 'all',
      'conditional_logic' => [[[
        'field' => $media_type_key,
        'operator' => '==',
        'value' => 'image',
      ]]],
      'wrapper' => ['width' => '75', 'class' => '', 'id' => ''],
    ]),
    sd_plan_field('field_' . $prefix . '_video_url', 'Video URL', 'video_url', 'url', [
      'conditional_logic' => [[[
        'field' => $media_type_key,
        'operator' => '==',
        'value' => 'video',
      ]]],
      'placeholder' => 'https://',
      'wrapper' => ['width' => '75', 'class' => '', 'id' => ''],
    ]),
    sd_plan_field('field_' . $prefix . '_title', 'Title', 'title', 'text'),
    sd_plan_field('field_' . $prefix . '_description', 'Description', 'description', 'textarea', [
      'rows' => 3,
    ]),
    sd_plan_field('field_' . $prefix . '_image_link', 'Link (Image Only)', 'image_link', 'url', [
      'conditional_logic' => [[[
        'field' => $media_type_key,
        'operator' => '==',
        'value' => 'image',
      ]]],
      'placeholder' => 'https://',
    ]),
  ];
}

/**
 * Register all Plan field groups.
 */
function sd_register_plan_acf_fields(): void {
  if (!function_exists('acf_add_local_field_group')) {
    return;
  }

  acf_add_local_field_group([
    'key' => 'group_plan_content',
    'title' => 'Plan Content',
    'fields' => [
      sd_plan_field('field_plan_admin_tab', 'Admin & Availability', '', 'tab', [
        'placement' => 'top',
      ]),
      sd_plan_field('field_plan_year_taxonomy', 'Plan Year', 'plan_year_taxonomy', 'taxonomy', [
        'required' => 1,
        'taxonomy' => 'plan_year',
        'field_type' => 'select',
        'allow_null' => 0,
        'add_term' => 0,
        'save_terms' => 1,
        'load_terms' => 1,
        'return_format' => 'id',
        'wrapper' => ['width' => '33', 'class' => '', 'id' => ''],
      ]),
      sd_plan_field('field_plan_language_code', 'Language Code', 'language_code', 'text', [
        'instructions' => 'Optional language marker for this plan version (for example: en, es, zh).',
        'placeholder' => 'en',
        'wrapper' => ['width' => '33', 'class' => '', 'id' => ''],
      ]),
      sd_plan_field('field_plan_type_taxonomy', 'Plan Type', 'plan_type_taxonomy', 'taxonomy', [
        'required' => 1,
        'taxonomy' => 'plan_type',
        'field_type' => 'select',
        'allow_null' => 0,
        'add_term' => 0,
        'save_terms' => 1,
        'load_terms' => 1,
        'return_format' => 'id',
        'instructions' => 'Used to drive plan-specific footer links.',
        'wrapper' => ['width' => '34', 'class' => '', 'id' => ''],
      ]),
      sd_plan_field('field_plan_visibility_start', 'Visibility Start', 'visibility_start', 'date_time_picker', [
        'instructions' => 'Optional start date/time used by frontend logic to show this plan.',
        'display_format' => 'F j, Y g:i a',
        'return_format' => 'Y-m-d H:i:s',
        'first_day' => 0,
        'wrapper' => ['width' => '50', 'class' => '', 'id' => ''],
      ]),
      sd_plan_field('field_plan_visibility_end', 'Visibility End', 'visibility_end', 'date_time_picker', [
        'instructions' => 'Optional end date/time used by frontend logic to hide this plan.',
        'display_format' => 'F j, Y g:i a',
        'return_format' => 'Y-m-d H:i:s',
        'first_day' => 0,
        'wrapper' => ['width' => '50', 'class' => '', 'id' => ''],
      ]),
      sd_plan_field('field_plan_editor_note', 'Publishing Note', '', 'message', [
        'message' => 'Use WordPress publish scheduling for publish dates. Use visibility start/end for frontend visibility windows, including unpublish behavior.',
        'new_lines' => 'wpautop',
        'esc_html' => 0,
      ]),

      sd_plan_field('field_plan_global_tab', 'Global Plan Information', '', 'tab', [
        'placement' => 'top',
      ]),
      sd_plan_field('field_plan_name_note', 'Plan Name', '', 'message', [
        'message' => 'Use the post title as the required Plan Name.',
        'new_lines' => 'wpautop',
        'esc_html' => 0,
      ]),
      sd_plan_field('field_plan_monthly_premium', 'Monthly Premium', 'monthly_premium', 'text', [
        'required' => 1,
        'instructions' => 'Dollar amount as display text.',
        'prepend' => '$',
        'wrapper' => ['width' => '50', 'class' => '', 'id' => ''],
      ]),
      sd_plan_field('field_plan_qualifying_statement', 'Qualifying Statement', 'qualifying_statement', 'textarea', [
        'rows' => 3,
      ]),
      sd_plan_field('field_plan_star_rating', 'Star Rating', 'star_rating', 'range', [
        'instructions' => 'Scale from 1 to 5.',
        'min' => 1,
        'max' => 5,
        'step' => 0.5,
      ]),
      sd_plan_field('field_plan_enroll_link', 'Enroll Link Destination', 'enroll_link_destination', 'url', [
        'required' => 1,
        'placeholder' => 'https://',
      ]),
      sd_plan_field('field_plan_contact_form_image', 'Contact Form Image', 'contact_form_image', 'image', [
        'required' => 1,
        'return_format' => 'array',
        'preview_size' => 'medium',
        'library' => 'all',
      ]),
      sd_plan_field('field_plan_alert_group', 'Plan Alert', 'plan_alert', 'group', [
        'layout' => 'block',
        'sub_fields' => [
          sd_plan_field('field_plan_alert_enabled', 'Enable Alert Banner', 'enabled', 'true_false', [
            'ui' => 1,
            'default_value' => 0,
            'wrapper' => ['width' => '30', 'class' => '', 'id' => ''],
          ]),
          sd_plan_field('field_plan_alert_text', 'Alert Text', 'text', 'textarea', [
            'rows' => 3,
            'wrapper' => ['width' => '70', 'class' => '', 'id' => ''],
          ]),
          sd_plan_field('field_plan_alert_link', 'Alert Link', 'link', 'link', [
            'return_format' => 'array',
          ]),
        ],
      ]),

      sd_plan_field('field_plan_comparison_tab', 'Benefits Comparison', '', 'tab', [
        'placement' => 'top',
      ]),
      sd_plan_field('field_plan_benefits_included', 'Included Benefits', 'benefits_included', 'checkbox', [
        'choices' => sd_plan_benefit_choices(),
        'layout' => 'vertical',
        'return_format' => 'value',
      ]),
      sd_plan_field('field_plan_benefit_footnotes', 'Benefit Footnotes', 'benefit_footnotes', 'repeater', [
        'layout' => 'row',
        'button_label' => 'Add Footnote',
        'sub_fields' => [
          sd_plan_field('field_plan_benefit_footnote_marker', 'Marker', 'marker', 'text', [
            'instructions' => 'Example: *, 1, 2',
            'wrapper' => ['width' => '20', 'class' => '', 'id' => ''],
          ]),
          sd_plan_field('field_plan_benefit_footnote_text', 'Footnote Text', 'text', 'textarea', [
            'rows' => 2,
            'wrapper' => ['width' => '80', 'class' => '', 'id' => ''],
          ]),
        ],
      ]),

      sd_plan_field('field_plan_links_tab', 'Quick Links & Care Network', '', 'tab', [
        'placement' => 'top',
      ]),
      sd_plan_field('field_plan_quick_links', 'Quick Links', 'quick_links', 'repeater', [
        'instructions' => 'Maximum of 6 quick links.',
        'max' => 6,
        'layout' => 'row',
        'button_label' => 'Add Quick Link',
        'sub_fields' => [
          sd_plan_field('field_plan_quick_link_icon', 'Icon', 'icon', 'image', [
            'return_format' => 'array',
            'preview_size' => 'thumbnail',
            'wrapper' => ['width' => '20', 'class' => '', 'id' => ''],
          ]),
          sd_plan_field('field_plan_quick_link_text', 'Link Text', 'link_text', 'text', [
            'required' => 1,
            'wrapper' => ['width' => '40', 'class' => '', 'id' => ''],
          ]),
          sd_plan_field('field_plan_quick_link_url', 'Link URL', 'link_url', 'url', [
            'required' => 1,
            'wrapper' => ['width' => '40', 'class' => '', 'id' => ''],
          ]),
        ],
      ]),
      sd_plan_field('field_plan_care_network_heading', 'Care Network Heading', 'care_network_heading', 'text'),
      sd_plan_field('field_plan_care_network_description', 'Care Network Description', 'care_network_description', 'textarea', [
        'rows' => 3,
      ]),
      sd_plan_field('field_plan_care_network_links', 'Care Network Links', 'care_network_links', 'repeater', [
        'layout' => 'row',
        'button_label' => 'Add Care Network Link',
        'sub_fields' => [
          sd_plan_field('field_plan_care_network_link_text', 'Link Text', 'link_text', 'text', [
            'required' => 1,
            'wrapper' => ['width' => '35', 'class' => '', 'id' => ''],
          ]),
          sd_plan_field('field_plan_care_network_link_url', 'Link URL', 'link_url', 'url', [
            'required' => 1,
            'wrapper' => ['width' => '35', 'class' => '', 'id' => ''],
          ]),
          sd_plan_field('field_plan_care_network_link_description', 'Optional Description', 'description', 'text', [
            'wrapper' => ['width' => '30', 'class' => '', 'id' => ''],
          ]),
        ],
      ]),

      sd_plan_field('field_plan_shop_tab', 'Shop Plan Information', '', 'tab', [
        'placement' => 'top',
      ]),
      sd_plan_field('field_plan_shop_heading', 'Heading', 'shop_heading', 'text'),
      sd_plan_field('field_plan_shop_description', 'Description', 'shop_description', 'wysiwyg', [
        'tabs' => 'all',
        'toolbar' => 'basic',
        'media_upload' => 0,
      ]),
      sd_plan_field('field_plan_shop_image', 'Image', 'shop_image', 'image', [
        'return_format' => 'array',
        'preview_size' => 'medium',
      ]),
      sd_plan_field('field_plan_overview_primary_media', 'Overview Primary Media', 'overview_primary_media', 'group', [
        'layout' => 'block',
        'sub_fields' => sd_plan_media_sub_fields('plan_overview_primary'),
      ]),
      sd_plan_field('field_plan_overview_secondary_media', 'Overview Secondary Media', 'overview_secondary_media', 'group', [
        'layout' => 'block',
        'sub_fields' => sd_plan_media_sub_fields('plan_overview_secondary'),
      ]),
      sd_plan_field('field_plan_whats_new_links', 'What\'s New Links', 'whats_new_links', 'repeater', [
        'max' => 6,
        'layout' => 'row',
        'button_label' => 'Add What\'s New Link',
        'sub_fields' => [
          sd_plan_field('field_plan_whats_new_link_text', 'Link Text', 'link_text', 'text', [
            'required' => 1,
            'wrapper' => ['width' => '50', 'class' => '', 'id' => ''],
          ]),
          sd_plan_field('field_plan_whats_new_link_url', 'Link URL', 'link_url', 'url', [
            'required' => 1,
            'wrapper' => ['width' => '50', 'class' => '', 'id' => ''],
          ]),
        ],
      ]),
      sd_plan_field('field_plan_related_content_title', 'Related Content Title', 'related_content_title', 'text'),
      sd_plan_field('field_plan_related_content_description', 'Related Content Description', 'related_content_description', 'textarea', [
        'rows' => 3,
      ]),
      sd_plan_field('field_plan_related_content_image', 'Related Content Image', 'related_content_image', 'image', [
        'return_format' => 'array',
        'preview_size' => 'medium',
      ]),
      sd_plan_field('field_plan_related_content_urls', 'Related Content URLs', 'related_content_urls', 'repeater', [
        'instructions' => 'Up to 3 internal links.',
        'max' => 3,
        'layout' => 'table',
        'button_label' => 'Add Related URL',
        'sub_fields' => [
          sd_plan_field('field_plan_related_content_url_link', 'URL', 'url', 'page_link', [
            'post_type' => [],
            'taxonomy' => [],
            'allow_null' => 0,
            'allow_archives' => 0,
            'multiple' => 0,
          ]),
        ],
      ]),
      sd_plan_field('field_plan_faqs', 'FAQs', 'faqs', 'repeater', [
        'instructions' => 'Add between 3 and 10 FAQ items.',
        'min' => 3,
        'max' => 10,
        'layout' => 'block',
        'button_label' => 'Add FAQ',
        'sub_fields' => [
          sd_plan_field('field_plan_faq_title', 'Accordion Title', 'accordion_title', 'text', [
            'required' => 1,
          ]),
          sd_plan_field('field_plan_faq_content', 'Accordion Content', 'accordion_content', 'wysiwyg', [
            'tabs' => 'all',
            'toolbar' => 'basic',
            'media_upload' => 0,
          ]),
          sd_plan_field('field_plan_faq_cta', 'Optional CTA Button', 'cta_button', 'link', [
            'return_format' => 'array',
          ]),
        ],
      ]),

      sd_plan_field('field_plan_coverage_tab', 'Benefits and Coverage', '', 'tab', [
        'placement' => 'top',
      ]),
      sd_plan_field('field_plan_coverage_note', 'Coverage Note', '', 'message', [
        'message' => 'Footnotes are expected in this section.',
        'new_lines' => 'wpautop',
        'esc_html' => 0,
      ]),
      sd_plan_field('field_plan_coverage_intro_copy', 'Intro Copy', 'coverage_intro_copy', 'wysiwyg', [
        'tabs' => 'all',
        'toolbar' => 'basic',
        'media_upload' => 0,
      ]),
      sd_plan_field('field_plan_coverage_media_links', 'Media Links (PDF)', 'coverage_media_links', 'repeater', [
        'instructions' => 'Up to 3 PDF files. Frontend can display each PDF title.',
        'max' => 3,
        'layout' => 'table',
        'button_label' => 'Add PDF',
        'sub_fields' => [
          sd_plan_field('field_plan_coverage_media_pdf', 'PDF', 'pdf', 'file', [
            'required' => 1,
            'return_format' => 'array',
            'library' => 'all',
            'mime_types' => 'pdf',
          ]),
        ],
      ]),
      sd_plan_field('field_plan_coverage_heading', 'Heading', 'coverage_heading', 'text'),
      sd_plan_field('field_plan_coverage_description', 'Description', 'coverage_description', 'textarea', [
        'rows' => 3,
      ]),
      sd_plan_field('field_plan_benefit_accordions', 'Benefit Accordions', 'benefit_accordions', 'repeater', [
        'layout' => 'block',
        'button_label' => 'Add Benefit Accordion',
        'sub_fields' => [
          sd_plan_field('field_plan_benefit_accordion_icon', 'Accordion Icon', 'accordion_icon', 'image', [
            'required' => 1,
            'return_format' => 'array',
            'preview_size' => 'thumbnail',
          ]),
          sd_plan_field('field_plan_benefit_accordion_title', 'Accordion Title', 'accordion_title', 'text', [
            'required' => 1,
          ]),
          sd_plan_field('field_plan_benefit_accordion_benefit_text', 'Benefit Text', 'benefit_text', 'wysiwyg', [
            'required' => 1,
            'tabs' => 'all',
            'toolbar' => 'basic',
            'media_upload' => 0,
          ]),
          sd_plan_field('field_plan_benefit_accordion_coverage_text', 'Coverage Text', 'coverage_text', 'textarea', [
            'rows' => 3,
          ]),
          sd_plan_field('field_plan_benefit_accordion_button_text', 'Button Text', 'button_text', 'text', [
            'wrapper' => ['width' => '50', 'class' => '', 'id' => ''],
          ]),
          sd_plan_field('field_plan_benefit_accordion_button_link', 'Button Link', 'button_link', 'url', [
            'wrapper' => ['width' => '50', 'class' => '', 'id' => ''],
          ]),
        ],
      ]),
      sd_plan_field('field_plan_extras_cards', 'Extras Section (CTA Cards)', 'extras_cards', 'repeater', [
        'layout' => 'block',
        'button_label' => 'Add CTA Card',
        'sub_fields' => [
          sd_plan_field('field_plan_extras_card_image', 'Image', 'image', 'image', [
            'required' => 1,
            'return_format' => 'array',
            'preview_size' => 'medium',
          ]),
          sd_plan_field('field_plan_extras_card_title', 'Title', 'title', 'text'),
          sd_plan_field('field_plan_extras_card_subtitle', 'Subtitle', 'subtitle', 'text'),
          sd_plan_field('field_plan_extras_card_description', 'Description', 'description', 'textarea', [
            'rows' => 3,
          ]),
          sd_plan_field('field_plan_extras_card_button_text', 'Button Text', 'button_text', 'text', [
            'wrapper' => ['width' => '50', 'class' => '', 'id' => ''],
          ]),
          sd_plan_field('field_plan_extras_card_button_link', 'Button Link', 'button_link', 'url', [
            'wrapper' => ['width' => '50', 'class' => '', 'id' => ''],
          ]),
        ],
      ]),

      sd_plan_field('field_plan_documents_tab', 'Member Documents', '', 'tab', [
        'placement' => 'top',
      ]),
      sd_plan_field('field_plan_member_documents', 'Member Documents', 'member_documents', 'repeater', [
        'instructions' => 'Plan-level documents that members can access after validation.',
        'layout' => 'block',
        'button_label' => 'Add Document',
        'sub_fields' => [
          sd_plan_field('field_plan_member_document_title', 'Document Title', 'title', 'text', [
            'required' => 1,
          ]),
          sd_plan_field('field_plan_member_document_description', 'Description', 'description', 'textarea', [
            'rows' => 2,
          ]),
          sd_plan_field('field_plan_member_document_file', 'Document File', 'file', 'file', [
            'required' => 1,
            'return_format' => 'array',
            'library' => 'all',
          ]),
        ],
      ]),
    ],
    'location' => [[[
      'param' => 'post_type',
      'operator' => '==',
      'value' => 'plan',
    ]]],
    'menu_order' => 0,
    'position' => 'normal',
    'style' => 'default',
    'label_placement' => 'top',
    'instruction_placement' => 'label',
    'hide_on_screen' => '',
    'active' => true,
    'description' => 'Per-plan, per-year plan benefits, links, and documents.',
    'show_in_rest' => 1,
  ]);
}
add_action('acf/init', 'sd_register_plan_acf_fields');
