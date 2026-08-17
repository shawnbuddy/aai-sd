<?php

/**
 * @file
 * Kentico content model migrated to native WordPress content types and ACF.
 *
 * The model deliberately normalizes Kentico's inconsistent field names while
 * preserving source provenance in a shared migration field group.
 */

// phpcs:disable

if (!defined('ABSPATH')) {
  exit;
}

/**
 * Return the canonical Kentico content model.
 */
function sd_kentico_content_model(): array {
  static $model = null;

  if (is_array($model)) {
    return $model;
  }

  $model = [
    'history_article' => [
      'singular' => 'History Article',
      'plural' => 'History Articles',
      'rewrite' => 'history/articles',
      'icon' => 'dashicons-media-document',
      'supports' => ['title', 'editor', 'excerpt', 'thumbnail', 'revisions'],
      'taxonomies' => ['history_series'],
      'fields' => [
        ['article_author', 'Article Author', 'text'],
        ['history_series', 'Series', 'taxonomy', ['taxonomy' => 'history_series']],
        ['display_title', 'Display Title', 'wysiwyg', ['instructions' => 'Optional formatted title from Kentico.']],
        ['series_description_2', 'Secondary Series Description', 'text', ['instructions' => 'Kentico: Series_Desc2.']],
        ['page_title_image', 'Page Title Image', 'image'],
        ['sort_order', 'Sort Order', 'number'],
      ],
    ],
    'history_profile' => [
      'singular' => 'History Profile',
      'plural' => 'History Profiles',
      'rewrite' => 'history/profiles',
      'icon' => 'dashicons-id-alt',
      'supports' => ['title', 'editor', 'thumbnail', 'revisions'],
      'taxonomies' => ['profile_type'],
      'fields' => [
        ['prefix', 'Prefix', 'text'],
        ['suffix', 'Suffix', 'text'],
        ['profile_type', 'Profile Type', 'taxonomy', ['taxonomy' => 'profile_type']],
        ['service_history', 'Service History', 'wysiwyg'],
        ['awards_honors', 'Awards & Honors', 'wysiwyg'],
        ['nobel_prize_type', 'Nobel Prize Type', 'wysiwyg'],
        ['nobel_prize_science', 'Nobel Prize Science', 'wysiwyg'],
        ['nobel_subheading', 'Nobel Subheading', 'wysiwyg'],
        ['lasker_type', 'Lasker Type', 'wysiwyg'],
        ['lasker_subheading', 'Lasker Subheading', 'wysiwyg'],
        ['lasker_cmr_subheading', 'Lasker CMR Subheading', 'wysiwyg'],
        ['oral_history_full_interview', 'Oral History Full Interview', 'wysiwyg', ['instructions' => 'Kentico: OHFullInterview.']],
        ['oral_history_transcript', 'Oral History Transcript', 'wysiwyg', ['instructions' => 'Kentico: OHTranscript.']],
        ['oral_history_clips', 'Oral History Clips', 'wysiwyg', ['instructions' => 'Kentico: OHClips.']],
        ['oral_history_subheading', 'Oral History Subheading', 'wysiwyg', ['instructions' => 'Kentico: OHP_Subheading.']],
        ['presidents_address', "President's Address", 'wysiwyg'],
        ['presidents_message', "President's Message", 'wysiwyg'],
        ['in_office', 'In Office', 'text'],
        ['in_office_eic', 'In Office — EIC', 'text'],
        ['in_office_st', 'In Office — Secretary/Treasurer', 'text'],
        ['in_office_ih_eic', 'In Office — ImmunoHorizons EIC', 'text'],
        ['institutional_bio_links', 'Institutional Biography Links', 'wysiwyg', ['instructions' => 'Kentico: Inst_Bio_Links.']],
        ['page_title_image', 'Page Title Image', 'image'],
        ['officer_number', 'Officer Number', 'number'],
        ['eic_order', 'EIC Order', 'number'],
        ['eic_ih_order', 'ImmunoHorizons EIC Order', 'number'],
        ['np_order', 'Nobel Prize Order', 'number'],
        ['lba_order', 'Lasker Basic Medical Research Order', 'number'],
        ['lca_order', 'Lasker Clinical Medical Research Order', 'number'],
        ['lpsa_order', 'Lasker Public Service Award Order', 'number'],
        ['lsaa_order', 'Lasker Special Achievement Award Order', 'number'],
        ['awardee_order', 'Awardee Order', 'number'],
        ['st_order', 'Secretary/Treasurer Order', 'number'],
        ['ohp_order', 'Oral History Order', 'number'],
        ['ohp_eic_order', 'Oral History EIC Order', 'number'],
        ['ohp_a_order', 'Oral History Additional Order', 'number'],
      ],
    ],
    'member_news' => [
      'singular' => 'Member in the News',
      'plural' => 'Members in the News',
      'rewrite' => false,
      'icon' => 'dashicons-megaphone',
      'public' => false,
      'supports' => ['title', 'editor', 'revisions'],
      'fields' => [
        ['order_number', 'Order Number', 'number'],
      ],
    ],
    'obituary' => [
      'singular' => 'Obituary',
      'plural' => 'Obituaries',
      'rewrite' => 'obituaries',
      'icon' => 'dashicons-book-alt',
      'supports' => ['title', 'editor', 'revisions'],
      'fields' => [
        ['year', 'Year', 'number'],
        ['alpha_sort', 'Alphabetical Sort Value', 'text'],
        ['order_number', 'Order Number', 'number'],
      ],
    ],
    'in_memoriam' => [
      'singular' => 'In Memoriam',
      'plural' => 'In Memoriam',
      'rewrite' => 'in-memoriam',
      'icon' => 'dashicons-heart',
      'supports' => ['title', 'editor', 'excerpt', 'thumbnail', 'revisions'],
      'fields' => [
        ['order_number', 'Order Number', 'number'],
        ['page_title_image', 'Page Title Image', 'image'],
      ],
    ],
    'dist_lecturer' => [
      'singular' => 'Distinguished Lecturer',
      'plural' => 'Distinguished Lecturers',
      'rewrite' => 'distinguished-lecturers',
      'icon' => 'dashicons-welcome-learn-more',
      'supports' => ['title', 'editor', 'revisions'],
      'instructions' => 'The WordPress key is shortened from distinguished_lecturer because post type keys cannot exceed 20 characters.',
      'fields' => [
        ['year', 'Year', 'number'],
        ['location', 'Location', 'text'],
      ],
    ],
    'presidents_message' => [
      'singular' => "President's Message",
      'plural' => "Presidents' Messages",
      'rewrite' => 'presidents-messages',
      'icon' => 'dashicons-format-quote',
      'supports' => ['title', 'editor', 'excerpt', 'thumbnail', 'revisions'],
      'fields' => [
        ['order_number', 'Order Number', 'number'],
        ['page_title_image', 'Page Title Image', 'image'],
      ],
    ],
    'committee' => [
      'singular' => 'Committee',
      'plural' => 'Committees',
      'rewrite' => 'committees',
      'icon' => 'dashicons-groups',
      'supports' => ['title', 'editor', 'revisions'],
      'taxonomies' => ['committee_type'],
      'fields' => [
        ['committee_type', 'Committee Type', 'taxonomy', ['taxonomy' => 'committee_type']],
        ['committee_role', 'Committee Role', 'wysiwyg'],
        ['committee_mission', 'Committee Mission', 'wysiwyg'],
        ['committee_members', 'Committee Members', 'wysiwyg'],
        ['members_tab', 'Members Tab', 'wysiwyg'],
        ['activities_and_symposia', 'Committee Activities & Symposia', 'wysiwyg', ['instructions' => 'Kentico: CommitteeActivitiesandSymposia.']],
        ['session_tab', 'Session Tab', 'wysiwyg'],
        ['committee_resources', 'Committee Resources', 'wysiwyg'],
        ['subcommittees', 'Subcommittees', 'wysiwyg'],
        ['application_from_date', 'Application Opens', 'date_time_picker'],
        ['application_to_date', 'Application Closes', 'date_time_picker'],
        ['page_title_image', 'Page Title Image', 'image'],
      ],
    ],
    'past_meeting' => [
      'singular' => 'Past Meeting',
      'plural' => 'Past Meetings',
      'rewrite' => 'meetings/past',
      'icon' => 'dashicons-calendar-alt',
      'supports' => ['title', 'editor', 'thumbnail', 'revisions'],
      'fields' => [
        ['date_location', 'Date & Location', 'wysiwyg'],
        ['buttons', 'Legacy Buttons', 'wysiwyg', ['instructions' => 'Preserves Kentico button markup. Convert to links during migration where practical.']],
        ['order_number', 'Order Number', 'number'],
      ],
    ],
    'award_program' => [
      'singular' => 'Award or Program',
      'plural' => 'Awards & Programs',
      'rewrite' => 'awards-programs',
      'icon' => 'dashicons-awards',
      'supports' => ['title', 'editor', 'excerpt', 'revisions'],
      'taxonomies' => ['award_program_type'],
      'fields' => [
        ['award_program_type', 'Award / Program Type', 'taxonomy', ['taxonomy' => 'award_program_type']],
        ['introduction', 'Introduction', 'wysiwyg'],
        ['description', 'Additional Description', 'wysiwyg', ['instructions' => 'Kentico: PPFP.Description.']],
        ['eligibility', 'Eligibility', 'wysiwyg'],
        ['award_details', 'Award Details', 'wysiwyg', ['instructions' => 'Kentico: TA_Award or CA_Award.']],
        ['application_instructions', 'Application Instructions', 'wysiwyg'],
        ['nomination', 'Nomination', 'wysiwyg'],
        ['deadline', 'Deadline', 'wysiwyg'],
        ['current_recipients', 'Current Recipients / Participants', 'wysiwyg'],
        ['past_recipients', 'Past Recipients / Participants', 'wysiwyg'],
        ['requirements', 'Requirements', 'wysiwyg'],
        ['fellowship_support', 'Fellowship Support', 'wysiwyg'],
        ['travel_support', 'Travel Support', 'wysiwyg'],
        ['terms_conditions', 'Terms & Conditions', 'wysiwyg'],
        ['process', 'Process', 'wysiwyg'],
        ['program_details', 'Program', 'wysiwyg'],
        ['goals', 'Goals', 'wysiwyg'],
        ['structure', 'Structure', 'wysiwyg'],
        ['award_cycles', 'Award Cycles', 'wysiwyg'],
        ['application_from_date', 'Application Opens', 'date_time_picker'],
        ['application_to_date', 'Application Closes', 'date_time_picker'],
        ['page_title_image', 'Page Title Image', 'image'],
      ],
    ],
    'distinguished_fellow' => [
      'singular' => 'Distinguished Fellow',
      'plural' => 'Distinguished Fellows',
      'rewrite' => false,
      'icon' => 'dashicons-businessperson',
      'public' => false,
      'supports' => ['title', 'thumbnail', 'revisions'],
      'fields' => [
        ['alpha_sort', 'Alphabetical Sort Value', 'text'],
        ['year', 'Year', 'number'],
        ['organization', 'Organization', 'text'],
        ['join_date', 'Join Date', 'text', ['instructions' => 'Kept as text because the Kentico source is not a consistently typed date.']],
        ['aai_url', 'AAI URL', 'url'],
        ['past_president', 'Past President', 'true_false'],
        ['past_secretary_treasurer', 'Past Secretary/Treasurer', 'true_false', ['instructions' => 'Kentico: PastSecTreas.']],
        ['past_eic', 'Past Editor-in-Chief', 'true_false'],
        ['past_eic_ih', 'Past ImmunoHorizons Editor-in-Chief', 'true_false'],
      ],
    ],
    'cifp_recipient' => [
      'singular' => 'CIFP Recipient',
      'plural' => 'CIFP Recipients',
      'rewrite' => false,
      'icon' => 'dashicons-welcome-learn-more',
      'public' => false,
      'supports' => ['title', 'editor', 'thumbnail', 'revisions'],
      'fields' => [
        ['pi_job_title', 'PI Job Title', 'text'],
        ['organization_name', 'Organization Name', 'text', ['instructions' => 'Kentico: Org_Name.']],
        ['trainee_name', 'Trainee Name', 'text'],
        ['trainee_title', 'Trainee Title', 'text'],
        ['year', 'Year', 'number'],
        ['sort_order', 'Sort Order', 'number'],
      ],
    ],
    'timeline_event' => [
      'singular' => 'Timeline Event',
      'plural' => 'Timeline Events',
      'rewrite' => false,
      'icon' => 'dashicons-backup',
      'public' => false,
      'supports' => ['title', 'editor', 'thumbnail', 'revisions'],
      'taxonomies' => ['timeline_category'],
      'fields' => [
        ['start', 'Start', 'text', ['instructions' => 'Kept as text to preserve Kentico timeline date formats.']],
        ['year', 'Year', 'text'],
        ['media', 'Media URL', 'url'],
        ['media_type', 'Media Type', 'select', ['choices' => ['image' => 'Image', 'video' => 'Video', 'audio' => 'Audio', 'other' => 'Other'], 'allow_null' => 1]],
        ['media_type_raw', 'Original Media Type', 'text', ['instructions' => 'Preserves Kentico values not represented by Media Type.']],
        ['credit', 'Credit', 'textarea'],
        ['caption', 'Caption', 'textarea'],
        ['timeline_category', 'Category', 'taxonomy', ['taxonomy' => 'timeline_category']],
      ],
    ],
    'award_recipient' => [
      'singular' => 'Award Recipient',
      'plural' => 'Award Recipients',
      'rewrite' => false,
      'icon' => 'dashicons-awards',
      'public' => false,
      'supports' => ['title', 'thumbnail', 'revisions'],
      'taxonomies' => ['award_type'],
      'fields' => [
        ['award_type', 'Award Type', 'taxonomy', ['taxonomy' => 'award_type']],
        ['year', 'Year', 'number'],
        ['position', 'Position', 'text'],
        ['institution', 'Institution', 'wysiwyg'],
        ['cycle', 'Cycle', 'text'],
        ['term', 'Term', 'text'],
        ['remarks', 'Remarks', 'wysiwyg'],
        ['description', 'Description', 'wysiwyg'],
        ['secondary_position', 'Secondary Position', 'text', ['instructions' => 'Kentico: CIFP_Position or TFT_Position.']],
        ['secondary_year', 'Secondary Year', 'number', ['instructions' => 'Kentico: CIFP_Year or T_Year.']],
        ['sort_order', 'Sort Order', 'number'],
      ],
    ],
  ];

  return $model;
}

/**
 * Taxonomy definitions shared by the migrated content types.
 */
function sd_kentico_taxonomy_model(): array {
  return [
    'history_series' => [
      'singular' => 'History Series',
      'plural' => 'History Series',
      'post_types' => ['history_article'],
      'rewrite' => 'history/series',
    ],
    'profile_type' => [
      'singular' => 'Profile Type',
      'plural' => 'Profile Types',
      'post_types' => ['history_profile'],
      'rewrite' => 'history/profile-type',
    ],
    'committee_type' => [
      'singular' => 'Committee Type',
      'plural' => 'Committee Types',
      'post_types' => ['committee'],
      'rewrite' => 'committee-type',
    ],
    'award_program_type' => [
      'singular' => 'Award / Program Type',
      'plural' => 'Award / Program Types',
      'post_types' => ['award_program'],
      'rewrite' => 'award-program-type',
    ],
    'timeline_category' => [
      'singular' => 'Timeline Category',
      'plural' => 'Timeline Categories',
      'post_types' => ['timeline_event'],
      'rewrite' => false,
      'public' => false,
    ],
    'award_type' => [
      'singular' => 'Award Type',
      'plural' => 'Award Types',
      'post_types' => ['award_recipient'],
      'rewrite' => false,
      'public' => false,
    ],
  ];
}

/**
 * Build complete post type labels.
 */
function sd_kentico_post_type_labels(string $singular, string $plural): array {
  return [
    'name' => __($plural, 'sd_base'),
    'singular_name' => __($singular, 'sd_base'),
    'menu_name' => __($plural, 'sd_base'),
    'name_admin_bar' => __($singular, 'sd_base'),
    'add_new' => __('Add New', 'sd_base'),
    'add_new_item' => sprintf(__('Add New %s', 'sd_base'), $singular),
    'new_item' => sprintf(__('New %s', 'sd_base'), $singular),
    'edit_item' => sprintf(__('Edit %s', 'sd_base'), $singular),
    'view_item' => sprintf(__('View %s', 'sd_base'), $singular),
    'all_items' => sprintf(__('All %s', 'sd_base'), $plural),
    'search_items' => sprintf(__('Search %s', 'sd_base'), $plural),
    'not_found' => sprintf(__('No %s found.', 'sd_base'), strtolower($plural)),
    'not_found_in_trash' => sprintf(__('No %s found in Trash.', 'sd_base'), strtolower($plural)),
  ];
}

/**
 * Register migrated Kentico post types.
 */
function sd_register_kentico_post_types(): void {
  foreach (sd_kentico_content_model() as $post_type => $definition) {
    $is_public = $definition['public'] ?? true;
    $rewrite = $definition['rewrite'] ?? false;

    register_post_type($post_type, [
      'labels' => sd_kentico_post_type_labels($definition['singular'], $definition['plural']),
      'description' => $definition['instructions'] ?? '',
      'public' => $is_public,
      'show_ui' => true,
      'show_in_menu' => true,
      'show_in_rest' => true,
      'publicly_queryable' => $is_public,
      'exclude_from_search' => !$is_public,
      'has_archive' => $is_public,
      'rewrite' => $is_public && $rewrite ? ['slug' => $rewrite, 'with_front' => false] : false,
      'query_var' => $is_public,
      'menu_icon' => $definition['icon'],
      'supports' => $definition['supports'],
      'taxonomies' => $definition['taxonomies'] ?? [],
      'map_meta_cap' => true,
      'delete_with_user' => false,
    ]);
  }
}
add_action('init', 'sd_register_kentico_post_types', 5);

/**
 * Register migrated Kentico taxonomies.
 */
function sd_register_kentico_taxonomies(): void {
  foreach (sd_kentico_taxonomy_model() as $taxonomy => $definition) {
    $is_public = $definition['public'] ?? true;
    $rewrite = $definition['rewrite'] ?? false;
    $singular = $definition['singular'];
    $plural = $definition['plural'];

    register_taxonomy($taxonomy, $definition['post_types'], [
      'labels' => [
        'name' => __($plural, 'sd_base'),
        'singular_name' => __($singular, 'sd_base'),
        'search_items' => sprintf(__('Search %s', 'sd_base'), $plural),
        'all_items' => sprintf(__('All %s', 'sd_base'), $plural),
        'edit_item' => sprintf(__('Edit %s', 'sd_base'), $singular),
        'update_item' => sprintf(__('Update %s', 'sd_base'), $singular),
        'add_new_item' => sprintf(__('Add New %s', 'sd_base'), $singular),
        'new_item_name' => sprintf(__('New %s Name', 'sd_base'), $singular),
        'menu_name' => __($plural, 'sd_base'),
      ],
      'public' => $is_public,
      'publicly_queryable' => $is_public,
      'show_ui' => true,
      'show_admin_column' => true,
      'show_in_rest' => true,
      'hierarchical' => true,
      'rewrite' => $is_public && $rewrite ? ['slug' => $rewrite, 'with_front' => false] : false,
      'meta_box_cb' => false,
    ]);
  }
}
add_action('init', 'sd_register_kentico_taxonomies', 6);

/**
 * Build a stable, unique ACF key.
 */
function sd_kentico_acf_key(string $prefix, string $post_type, string $name): string {
  return $prefix . '_sd_' . sanitize_key($post_type) . '_' . sanitize_key($name);
}

/**
 * Convert a compact model field definition to an ACF local field.
 */
function sd_kentico_acf_field(string $post_type, array $definition): array {
  [$name, $label, $type] = $definition;
  $additional = $definition[3] ?? [];
  $defaults = [
    'key' => sd_kentico_acf_key('field', $post_type, $name),
    'label' => $label,
    'name' => $name,
    'type' => $type,
    'required' => 0,
    'instructions' => '',
    'conditional_logic' => 0,
    'wrapper' => ['width' => '', 'class' => '', 'id' => ''],
  ];

  if ($type === 'wysiwyg') {
    $defaults += ['tabs' => 'all', 'toolbar' => 'full', 'media_upload' => 1, 'delay' => 1];
  } elseif ($type === 'textarea') {
    $defaults += ['rows' => 4, 'new_lines' => 'wpautop'];
  } elseif ($type === 'number') {
    $defaults += ['step' => 1];
  } elseif ($type === 'true_false') {
    $defaults += ['ui' => 1, 'default_value' => 0];
  } elseif ($type === 'image') {
    $defaults += ['return_format' => 'id', 'preview_size' => 'medium', 'library' => 'all'];
  } elseif ($type === 'url') {
    $defaults += ['placeholder' => 'https://'];
  } elseif ($type === 'date_time_picker') {
    $defaults += [
      'display_format' => 'F j, Y g:i a',
      'return_format' => 'Y-m-d H:i:s',
      'first_day' => 0,
    ];
  } elseif ($type === 'select') {
    $defaults += ['choices' => [], 'allow_null' => 0, 'ui' => 1, 'return_format' => 'value'];
  } elseif ($type === 'taxonomy') {
    $defaults += [
      'field_type' => 'select',
      'allow_null' => 1,
      'add_term' => 1,
      'save_terms' => 1,
      'load_terms' => 1,
      'return_format' => 'id',
    ];
  }

  return array_replace($defaults, $additional);
}

/**
 * Register ACF field groups for every migrated content type.
 */
function sd_register_kentico_acf_fields(): void {
  if (!function_exists('acf_add_local_field_group')) {
    return;
  }

  $model = sd_kentico_content_model();

  foreach ($model as $post_type => $definition) {
    if (empty($definition['fields'])) {
      continue;
    }

    $fields = [];
    foreach ($definition['fields'] as $field_definition) {
      $fields[] = sd_kentico_acf_field($post_type, $field_definition);
    }

    acf_add_local_field_group([
      'key' => sd_kentico_acf_key('group', $post_type, 'content'),
      'title' => $definition['singular'] . ' Details',
      'fields' => $fields,
      'location' => [[[
        'param' => 'post_type',
        'operator' => '==',
        'value' => $post_type,
      ]]],
      'menu_order' => 0,
      'position' => 'normal',
      'style' => 'default',
      'label_placement' => 'top',
      'instruction_placement' => 'label',
      'active' => true,
      'show_in_rest' => 1,
    ]);
  }

  $migration_locations = [];
  foreach (array_keys($model) as $post_type) {
    $migration_locations[] = [[
      'param' => 'post_type',
      'operator' => '==',
      'value' => $post_type,
    ]];
  }

  acf_add_local_field_group([
    'key' => 'group_sd_kentico_migration',
    'title' => 'Kentico Migration',
    'fields' => [
      sd_kentico_acf_field('kentico', ['kentico_source_class', 'Source Class', 'text', [
        'instructions' => 'Original Kentico class name, such as aai.HistoryProfiles.',
      ]]),
      sd_kentico_acf_field('kentico', ['kentico_source_id', 'Source ID / GUID', 'text']),
      sd_kentico_acf_field('kentico', ['kentico_source_url', 'Original URL', 'url']),
    ],
    'location' => $migration_locations,
    'menu_order' => 100,
    'position' => 'side',
    'style' => 'default',
    'label_placement' => 'top',
    'instruction_placement' => 'label',
    'active' => true,
    'show_in_rest' => 1,
  ]);
}
add_action('acf/init', 'sd_register_kentico_acf_fields');

/**
 * Seed only controlled vocabularies that are explicit in the source model.
 */
function sd_seed_kentico_terms(): void {
  $seed_version = '1';
  if (get_option('sd_kentico_term_seed_version') === $seed_version) {
    return;
  }

  $terms = [
    'award_program_type' => [
      'travel-award' => 'Travel Award',
      'career-award' => 'Career Award',
      'careers-in-immunology-fellowship' => 'Careers in Immunology Fellowship',
      'public-fellows' => 'Public Fellows',
      'public-service-award' => 'Public Service Award',
      'public-affairs-recognition-award' => 'Public Affairs Recognition Award',
      'travel-for-techniques' => 'Travel for Techniques',
      'summer-research-program-for-teachers' => 'Summer Research Program for Teachers',
    ],
    'award_type' => [
      'lefrancois-memorial-award' => 'Lefrançois Memorial Award',
      'steinman-human-immunology-research-award' => 'Steinman Award for Human Immunology Research',
      'investigator-award' => 'Investigator Award',
      'travel-for-techniques' => 'Travel for Techniques',
      'lifetime-achievement-award' => 'Lifetime Achievement Award',
      'laboratory-travel-grant' => 'Laboratory Travel Grant',
      'european-congress-immunology-travel-grant' => 'European Congress of Immunology Travel Grant',
      'chambers-memorial-award' => 'Chambers Memorial Award',
      'herzenberg-award' => 'Herzenberg Award',
      'high-school-teachers-program' => 'High School Teachers Program',
      'excellence-in-mentoring-award' => 'Excellence in Mentoring Award',
      'awardees' => 'Awardees',
      'trainee-achievement-award' => 'Trainee Achievement Award',
      'early-career-faculty-travel-grant' => 'Early Career Faculty Travel Grant',
      'undergraduate-faculty-travel-grant' => 'Undergraduate Faculty Travel Grant',
      'careers-in-immunology-fellowship' => 'Careers in Immunology Fellowship',
      'distinguished-service-award' => 'Distinguished Service Award',
      'trainee-poster-award' => 'Trainee Poster Award',
      'meritorious-career-award' => 'Meritorious Career Award',
      'minority-scientist-travel-award' => 'Minority Scientist Travel Award',
      'trainee-abstract-award' => 'Trainee Abstract Award',
      'pfizer-showell-travel-award' => 'Pfizer-Showell Travel Award',
      'lb-poster-award' => 'LB Poster Award',
      'lustgarten-memorial-award' => 'Lustgarten Memorial Award',
      'public-service-award' => 'Public Service Award',
      'public-affairs-recognition-award' => 'Public Affairs Recognition Award',
    ],
  ];

  foreach ($terms as $taxonomy => $taxonomy_terms) {
    if (!taxonomy_exists($taxonomy)) {
      continue;
    }

    foreach ($taxonomy_terms as $slug => $name) {
      if (!term_exists($slug, $taxonomy)) {
        wp_insert_term($name, $taxonomy, ['slug' => $slug]);
      }
    }
  }

  update_option('sd_kentico_term_seed_version', $seed_version, false);
}
add_action('init', 'sd_seed_kentico_terms', 20);

/**
 * Refresh rewrite rules once when the PHP content model changes.
 */
function sd_maybe_flush_kentico_rewrite_rules(): void {
  $schema_version = '1';
  if (get_option('sd_kentico_content_model_version') === $schema_version) {
    return;
  }

  flush_rewrite_rules(false);
  update_option('sd_kentico_content_model_version', $schema_version, false);
}
add_action('init', 'sd_maybe_flush_kentico_rewrite_rules', 100);

/**
 * Improve title prompts for imported record-like content.
 */
function sd_kentico_title_placeholder(string $placeholder, WP_Post $post): string {
  $prompts = [
    'member_news' => 'Headline or short administrative label',
    'obituary' => 'Person name',
    'dist_lecturer' => 'Lecturer name',
    'award_program' => 'Award or program name',
    'timeline_event' => 'Timeline event title',
    'award_recipient' => 'Recipient name',
  ];

  return $prompts[$post->post_type] ?? $placeholder;
}
add_filter('enter_title_here', 'sd_kentico_title_placeholder', 10, 2);
