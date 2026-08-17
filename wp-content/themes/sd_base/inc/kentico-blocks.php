<?php

/**
 * @file
 * Reusable ACF blocks for migrated structured content.
 */

// phpcs:disable

if (!defined('ABSPATH')) {
  exit;
}

/**
 * Add Kentico migration blocks to the theme's data-driven block registry.
 */
function sd_register_kentico_blocks(array $blocks): array {
  $blocks[] = [
    'name' => 'content-section',
    'title' => 'Content Section',
    'description' => 'A titled rich-text section with an optional anchor and CTA.',
    'icon' => 'editor-alignleft',
    'keywords' => ['section', 'content', 'kentico'],
    'post_types' => ['page', 'post', 'award_program', 'committee', 'history_article'],
    'fields' => [
      sd_acf_field('field_content_section_anchor', 'Anchor ID', 'anchor_id', 'text', [
        'instructions' => 'Optional URL anchor without the # character.',
      ]),
      sd_acf_field('field_content_section_heading', 'Heading', 'heading', 'text'),
      sd_acf_field('field_content_section_content', 'Content', 'content', 'wysiwyg', [
        'tabs' => 'all',
        'toolbar' => 'full',
        'media_upload' => 1,
      ]),
      sd_acf_field('field_content_section_cta', 'CTA', 'cta', 'link', [
        'return_format' => 'array',
      ]),
    ],
    'template' => 'src/2-components/content-section/content-section.twig',
  ];

  $blocks[] = [
    'name' => 'accordion-group',
    'title' => 'Accordion Group',
    'description' => 'A heading and a collection of expandable rich-text items.',
    'icon' => 'list-view',
    'keywords' => ['accordion', 'faq', 'sections', 'kentico'],
    'post_types' => ['page', 'post', 'award_program', 'committee', 'history_profile'],
    'fields' => [
      sd_acf_field('field_accordion_group_heading', 'Heading', 'heading', 'text'),
      sd_acf_field('field_accordion_group_intro', 'Introduction', 'intro', 'wysiwyg', [
        'tabs' => 'all',
        'toolbar' => 'basic',
        'media_upload' => 0,
      ]),
      sd_acf_field('field_accordion_group_items', 'Items', 'items', 'repeater', [
        'min' => 1,
        'layout' => 'block',
        'button_label' => 'Add Accordion Item',
        'sub_fields' => [
          sd_acf_field('field_accordion_group_item_heading', 'Heading', 'heading', 'text', [
            'required' => 1,
          ]),
          sd_acf_field('field_accordion_group_item_content', 'Content', 'content', 'wysiwyg', [
            'tabs' => 'all',
            'toolbar' => 'full',
            'media_upload' => 1,
          ]),
          sd_acf_field('field_accordion_group_item_cta', 'CTA', 'cta', 'link', [
            'return_format' => 'array',
          ]),
        ],
      ]),
    ],
    'template' => 'src/2-components/accordion-group/accordion-group.twig',
  ];

  $blocks[] = [
    'name' => 'document-links',
    'title' => 'Document Links',
    'description' => 'A list of files or external document links with descriptions.',
    'icon' => 'media-document',
    'keywords' => ['documents', 'files', 'links', 'resources'],
    'post_types' => ['page', 'post', 'award_program', 'committee', 'past_meeting'],
    'fields' => [
      sd_acf_field('field_document_links_heading', 'Heading', 'heading', 'text'),
      sd_acf_field('field_document_links_items', 'Documents', 'documents', 'repeater', [
        'min' => 1,
        'layout' => 'row',
        'button_label' => 'Add Document',
        'sub_fields' => [
          sd_acf_field('field_document_links_item_title', 'Title', 'title', 'text', [
            'required' => 1,
          ]),
          sd_acf_field('field_document_links_item_description', 'Description', 'description', 'textarea', [
            'rows' => 2,
          ]),
          sd_acf_field('field_document_links_item_file', 'File', 'file', 'file', [
            'return_format' => 'array',
            'library' => 'all',
          ]),
          sd_acf_field('field_document_links_item_url', 'External URL', 'url', 'url', [
            'instructions' => 'Use only when no WordPress media file is selected.',
          ]),
        ],
      ]),
    ],
    'template' => 'src/2-components/document-links/document-links.twig',
  ];

  $blocks[] = [
    'name' => 'related-content',
    'title' => 'Related Content',
    'description' => 'A curated list of related WordPress content.',
    'icon' => 'admin-links',
    'keywords' => ['related', 'posts', 'links'],
    'post_types' => ['page', 'post', 'history_article', 'history_profile', 'award_program', 'committee'],
    'fields' => [
      sd_acf_field('field_related_content_heading', 'Heading', 'heading', 'text'),
      sd_acf_field('field_related_content_posts', 'Related Content', 'posts', 'relationship', [
        'post_type' => [],
        'taxonomy' => [],
        'filters' => ['search', 'post_type', 'taxonomy'],
        'return_format' => 'object',
        'min' => 1,
        'max' => 6,
      ]),
    ],
    'template' => 'src/2-components/related-content/related-content.twig',
  ];

  return $blocks;
}
add_filter('sd/acf_blocks', 'sd_register_kentico_blocks');
