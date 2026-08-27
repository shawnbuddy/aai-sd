<?php

/**
 * @file
 * Idempotent Kentico content and media import helpers.
 */

// phpcs:disable

if (!defined('ABSPATH')) {
  exit;
}

final class SD_Kentico_Media_Importer {
  private const MAX_FILE_SIZE = 52428800;

  private bool $dry_run;

  public function __construct(bool $dry_run = false) {
    $this->dry_run = $dry_run;
  }

  /**
   * Import an AAI media-library image or reuse its existing attachment.
   *
   * @return int|WP_Error Attachment ID, zero during a dry run, or an error.
   */
  public function import_image(string $source, int $parent_post_id = 0, string $title = '') {
    $remote_url = $this->remote_url($source);
    if (is_wp_error($remote_url)) {
      return $remote_url;
    }

    $identity = 'kentico-media:' . strtolower((string) wp_parse_url($remote_url, PHP_URL_PATH));
    $existing_id = $this->find_attachment($identity);
    if ($existing_id) {
      if ($parent_post_id && !(int) wp_get_post_parent_id($existing_id)) {
        wp_update_post(['ID' => $existing_id, 'post_parent' => $parent_post_id]);
      }
      return $existing_id;
    }

    if ($this->dry_run) {
      return 0;
    }

    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';

    $tmp_file = download_url($remote_url, 60);
    if (is_wp_error($tmp_file)) {
      return $tmp_file;
    }

    if (filesize($tmp_file) > self::MAX_FILE_SIZE) {
      @unlink($tmp_file);
      return new WP_Error('sd_kentico_media_too_large', 'Media file exceeds the 50 MB import limit.');
    }

    $path = (string) wp_parse_url($remote_url, PHP_URL_PATH);
    $filename = sanitize_file_name(rawurldecode(basename($path)));
    $file = ['name' => $filename, 'tmp_name' => $tmp_file];
    $attachment_id = media_handle_sideload($file, $parent_post_id, null, [
      'post_title' => $title ?: pathinfo($filename, PATHINFO_FILENAME),
    ]);

    if (is_wp_error($attachment_id)) {
      @unlink($tmp_file);
      return $attachment_id;
    }

    if (!wp_attachment_is_image($attachment_id)) {
      wp_delete_attachment($attachment_id, true);
      return new WP_Error('sd_kentico_media_not_image', 'The downloaded file is not a supported image.');
    }

    update_post_meta($attachment_id, '_sd_kentico_media_identity', $identity);
    update_post_meta($attachment_id, '_sd_kentico_source_url', esc_url_raw($remote_url));
    update_post_meta($attachment_id, '_sd_kentico_source_value', $source);

    return $attachment_id;
  }

  /**
   * Convert a Kentico media value into an allow-listed live-site URL.
   *
   * @return string|WP_Error
   */
  public function remote_url(string $source) {
    $source = html_entity_decode(trim($source), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    if ($source === '') {
      return new WP_Error('sd_kentico_media_empty', 'The media source is empty.');
    }

    $source = preg_replace('/^~/', '', $source);
    $parts = wp_parse_url($source);
    if ($parts === false) {
      return new WP_Error('sd_kentico_media_url', 'The media source is not a valid URL.');
    }

    $host = strtolower($parts['host'] ?? '');
    if ($host !== '' && !in_array($host, ['aai.org', 'www.aai.org'], true)) {
      return new WP_Error('sd_kentico_media_host', 'Only aai.org media may be downloaded.');
    }

    $path = rawurldecode($parts['path'] ?? '');
    if (strpos($path, "\0") !== false || preg_match('#(?:^|/)\.\.(?:/|$)#', $path)) {
      return new WP_Error('sd_kentico_media_path', 'The media path is unsafe.');
    }

    if (!preg_match('#^/AAISite/media/(.+)$#i', $path, $matches)) {
      return new WP_Error('sd_kentico_media_path', 'The source is not an AAI media-library path.');
    }

    $segments = array_map('rawurlencode', explode('/', $matches[1]));
    return 'https://www.aai.org/AAISite/media/' . implode('/', $segments);
  }

  private function find_attachment(string $identity): int {
    $attachments = get_posts([
      'post_type' => 'attachment',
      'post_status' => 'inherit',
      'fields' => 'ids',
      'posts_per_page' => 1,
      'meta_key' => '_sd_kentico_media_identity',
      'meta_value' => $identity,
      'no_found_rows' => true,
      'suppress_filters' => true,
    ]);

    return $attachments ? (int) $attachments[0] : 0;
  }
}

final class SD_Kentico_Content_Importer {
  private string $data_root;
  private bool $dry_run;
  private bool $skip_media;
  private string $post_status;
  private SD_Kentico_Media_Importer $media_importer;

  public function __construct(array $options = []) {
    $data_root = realpath($options['data_root'] ?? '/mnt/kentico-data');
    if (!$data_root || !is_dir($data_root)) {
      throw new InvalidArgumentException('Kentico data directory is not readable.');
    }

    $this->data_root = rtrim($data_root, '/');
    $this->dry_run = !empty($options['dry_run']);
    $this->skip_media = !empty($options['skip_media']);
    $this->post_status = $options['post_status'] ?? 'draft';
    $this->media_importer = new SD_Kentico_Media_Importer($this->dry_run);
  }

  public function import_class(string $source_class, int $limit = 0, int $offset = 0): array {
    $map = sd_kentico_source_map();
    if (!isset($map[$source_class])) {
      throw new InvalidArgumentException('Unknown source class: ' . $source_class);
    }

    $rule = $map[$source_class];
    if (!in_array($rule['strategy'] ?? '', ['wordpress', 'events_calendar'], true)) {
      throw new InvalidArgumentException('Unsupported import strategy for ' . $source_class . '.');
    }

    [$source_file, $record_name] = $this->source_location($source_class);
    $stats = ['read' => 0, 'created' => 0, 'updated' => 0, 'skipped' => 0, 'failed' => 0, 'media_failed' => 0];
    $position = 0;

    foreach ($this->records($source_file, $record_name) as $record) {
      if ($position++ < $offset) {
        continue;
      }
      if ($limit > 0 && $stats['read'] >= $limit) {
        break;
      }

      $stats['read']++;
      $result = $this->import_record($source_class, $rule, $record);
      if (is_wp_error($result)) {
        $stats['failed']++;
        WP_CLI::warning($result->get_error_message());
        continue;
      }

      $stats[$result['action']]++;
      $stats['media_failed'] += $result['media_failed'];
      if ($result['media_failed']) {
        WP_CLI::warning(sprintf('%s imported with %d media failure(s).', $result['identity'], $result['media_failed']));
      }
    }

    return $stats;
  }

  /**
   * @return array{action:string,identity:string,media_failed:int}|WP_Error
   */
  private function import_record(string $source_class, array $rule, array $record) {
    $identity = $this->record_identity($source_class, $record);
    if (is_wp_error($identity)) {
      return $identity;
    }

    $existing_id = $this->find_post($identity, $rule['post_type']);
    $post_data = [
      'ID' => $existing_id,
      'post_type' => $rule['post_type'],
      'post_status' => $this->post_status,
    ];
    $field_values = [];
    $taxonomy_values = [];
    $image_values = [];
    $event_values = [];

    foreach ($rule['fields'] as $source_field => $destination) {
      if (!array_key_exists($source_field, $record) || $record[$source_field] === '') {
        continue;
      }

      $value = $this->transform_value($record[$source_field], $rule['transforms'][$source_field] ?? '');
      if (in_array($destination, ['post_title', 'post_content', 'post_excerpt'], true)) {
        $post_data[$destination] = $value;
      } elseif ($destination === 'post_date') {
        $date = $this->mysql_date($value);
        if ($date) {
          $post_data['post_date'] = $date;
        }
      } elseif ($destination === 'featured_image' || $this->is_image_field($rule['post_type'], $destination)) {
        $image_values[$destination] = $record[$source_field];
      } elseif (strpos($destination, 'taxonomy:') === 0) {
        $taxonomy = substr($destination, 9);
        $taxonomy_values[$taxonomy] = array_merge(
          $taxonomy_values[$taxonomy] ?? [],
          $this->normalize_terms($value, $rule['taxonomy_value_maps'][$taxonomy] ?? [])
        );
      } elseif (strpos($destination, '_Event') === 0 || $destination === 'tribe_venue') {
        $event_values[$destination] = $value;
      } else {
        $field_values[$destination] = $this->normalize_field_value($rule['post_type'], $destination, $value);
      }
    }

    foreach ($rule['fixed_terms'] ?? [] as $taxonomy => $term) {
      $taxonomy_values[$taxonomy][] = $term;
    }

    if (empty($post_data['post_title'])) {
      $post_data['post_title'] = $rule['fixed_title'] ?? $this->fallback_title($record, $identity);
    }
    if (!empty($record['NodeAlias'])) {
      $post_data['post_name'] = sanitize_title(wp_strip_all_tags($record['NodeAlias']));
    }
    if (isset($record['NodeOrder']) && is_numeric($record['NodeOrder'])) {
      $post_data['menu_order'] = (int) $record['NodeOrder'];
    }

    if ($this->dry_run) {
      return ['action' => $existing_id ? 'updated' : 'created', 'identity' => $identity, 'media_failed' => 0];
    }

    if (($rule['strategy'] ?? '') === 'events_calendar') {
      $post_id = $this->save_event($existing_id, $post_data, $event_values);
    } else {
      $post_id = wp_insert_post(wp_slash($post_data), true);
    }
    if (is_wp_error($post_id)) {
      return $post_id;
    }

    update_post_meta($post_id, '_sd_kentico_identity', $identity);
    update_post_meta($post_id, '_sd_kentico_source_class', $source_class);
    $this->store_source_metadata($post_id, $record);

    foreach ($field_values as $field_name => $value) {
      if (function_exists('update_field')) {
        update_field($field_name, $value, $post_id);
      } else {
        update_post_meta($post_id, $field_name, $value);
      }
    }

    foreach ($taxonomy_values as $taxonomy => $terms) {
      $term_result = wp_set_object_terms($post_id, array_values(array_unique($terms)), $taxonomy, false);
      if (is_wp_error($term_result)) {
        return $term_result;
      }
    }

    $media_failed = $this->skip_media ? 0 : $this->attach_images($post_id, $post_data['post_title'], $image_values);

    return [
      'action' => $existing_id ? 'updated' : 'created',
      'identity' => $identity,
      'media_failed' => $media_failed,
    ];
  }

  private function attach_images(int $post_id, string $title, array $image_values): int {
    $failures = 0;
    delete_post_meta($post_id, '_sd_kentico_media_error');
    foreach ($image_values as $destination => $source) {
      $attachment_id = $this->media_importer->import_image($source, $post_id, $title);
      if (is_wp_error($attachment_id)) {
        $failures++;
        add_post_meta($post_id, '_sd_kentico_media_error', wp_json_encode([
          'destination' => $destination,
          'source' => $source,
          'error' => $attachment_id->get_error_message(),
        ]));
        WP_CLI::warning(sprintf('Media failed for post %d (%s): %s', $post_id, $source, $attachment_id->get_error_message()));
        continue;
      }

      if ($destination === 'featured_image') {
        set_post_thumbnail($post_id, $attachment_id);
      } elseif (function_exists('update_field')) {
        update_field($destination, $attachment_id, $post_id);
      } else {
        update_post_meta($post_id, $destination, $attachment_id);
      }
    }

    return $failures;
  }

  /**
   * @return int|WP_Error
   */
  private function save_event(int $existing_id, array $post_data, array $event_values) {
    if (!function_exists('tribe_create_event') || !function_exists('tribe_update_event')) {
      return new WP_Error('sd_kentico_events_plugin', 'The Events Calendar must be active.');
    }

    $start_date = $this->event_date($event_values['_EventStartDate'] ?? '');
    if (!$start_date) {
      return new WP_Error('sd_kentico_event_date', 'Event has no valid start date: ' . $post_data['post_title']);
    }
    $end_date = $this->event_date($event_values['_EventEndDate'] ?? '') ?: $start_date;
    $all_day = filter_var($event_values['_EventAllDay'] ?? false, FILTER_VALIDATE_BOOLEAN);

    $event_args = $post_data;
    unset($event_args['ID'], $event_args['post_type']);
    $event_args['EventStartDate'] = $start_date->format('n/j/Y');
    $event_args['EventEndDate'] = $end_date->format('n/j/Y');
    $event_args['EventAllDay'] = $all_day;
    if (!$all_day) {
      $event_args['EventStartTime'] = $start_date->format('H:i:s');
      $event_args['EventEndTime'] = $end_date->format('H:i:s');
    }

    $venue_id = $this->resolve_venue($event_values['tribe_venue'] ?? '');
    if (is_wp_error($venue_id)) {
      return $venue_id;
    }
    if ($venue_id) {
      $event_args['EventVenueID'] = $venue_id;
    }

    $post_id = $existing_id
      ? tribe_update_event($existing_id, $event_args)
      : tribe_create_event($event_args);

    if (!$post_id) {
      return new WP_Error('sd_kentico_event_save', 'The Events Calendar could not save ' . $post_data['post_title']);
    }

    $updater_class = '\TEC\Events\Custom_Tables\V1\Updates\Events';
    if (class_exists($updater_class) && !tribe($updater_class)->update($post_id)) {
      return new WP_Error('sd_kentico_event_index', 'The Events Calendar could not index ' . $post_data['post_title']);
    }

    return (int) $post_id;
  }

  /**
   * @return int|WP_Error
   */
  private function resolve_venue(string $location) {
    $location = trim(wp_strip_all_tags($location));
    if ($location === '') {
      return 0;
    }

    $identity = 'kentico-venue:' . md5(strtolower($location));
    $venues = get_posts([
      'post_type' => 'tribe_venue',
      'post_status' => 'any',
      'fields' => 'ids',
      'posts_per_page' => 1,
      'meta_key' => '_sd_kentico_venue_identity',
      'meta_value' => $identity,
      'no_found_rows' => true,
      'suppress_filters' => true,
    ]);
    if ($venues) {
      return (int) $venues[0];
    }

    if ($this->dry_run) {
      return 0;
    }

    $venue_id = function_exists('tribe_create_venue')
      ? tribe_create_venue(['Venue' => $location, 'post_status' => 'publish'])
      : wp_insert_post(['post_type' => 'tribe_venue', 'post_status' => 'publish', 'post_title' => $location], true);
    if (!$venue_id || is_wp_error($venue_id)) {
      return is_wp_error($venue_id) ? $venue_id : new WP_Error('sd_kentico_venue', 'Unable to create venue: ' . $location);
    }

    update_post_meta($venue_id, '_sd_kentico_venue_identity', $identity);
    return (int) $venue_id;
  }

  private function source_location(string $source_class): array {
    $record_name = strtolower(str_replace('.', '_', $source_class));
    $custom_table = $this->data_root . '/Customtables/customtable_' . $record_name . '.xml.export';
    if (is_readable($custom_table)) {
      return [$custom_table, $record_name];
    }

    $document_file = $this->data_root . '/Documents/cms_document.xml.export';
    if (is_readable($document_file)) {
      return [$document_file, strtolower($source_class)];
    }

    throw new RuntimeException('No readable XML export found for ' . $source_class);
  }

  private function records(string $source_file, string $record_name): Generator {
    $reader = new XMLReader();
    if (!$reader->open($source_file, null, LIBXML_NONET | LIBXML_COMPACT)) {
      throw new RuntimeException('Unable to open ' . $source_file);
    }

    try {
      while ($reader->read()) {
        if ($reader->nodeType !== XMLReader::ELEMENT || $reader->depth !== 2 || strtolower($reader->name) !== $record_name) {
          continue;
        }

        $xml = simplexml_load_string($reader->readOuterXML(), SimpleXMLElement::class, LIBXML_NONET | LIBXML_NOCDATA);
        if ($xml === false) {
          continue;
        }

        $record = [];
        foreach ($xml->children() as $name => $value) {
          $record[$name] = (string) $value;
        }
        yield $record;
      }
    } finally {
      $reader->close();
    }
  }

  /**
   * @return string|WP_Error
   */
  private function record_identity(string $source_class, array $record) {
    foreach (['ItemGUID', 'DocumentGUID', 'NodeGUID'] as $field) {
      if (!empty($record[$field])) {
        return strtolower($source_class . ':' . $field . ':' . $record[$field]);
      }
    }

    foreach (['ItemID', 'DocumentID', 'NodeID'] as $field) {
      if (!empty($record[$field])) {
        return strtolower($source_class . ':' . $field . ':' . $record[$field]);
      }
    }

    return new WP_Error('sd_kentico_identity', 'Record has no stable Kentico identity in ' . $source_class . '.');
  }

  private function find_post(string $identity, string $post_type): int {
    global $wpdb;

    return (int) $wpdb->get_var($wpdb->prepare(
      "SELECT posts.ID
       FROM {$wpdb->posts} AS posts
       INNER JOIN {$wpdb->postmeta} AS identity_meta ON identity_meta.post_id = posts.ID
       WHERE posts.post_type = %s
         AND identity_meta.meta_key = '_sd_kentico_identity'
         AND identity_meta.meta_value = %s
       LIMIT 1",
      $post_type,
      $identity
    ));
  }

  private function transform_value(string $value, string $transform): string {
    if ($transform === 'strip_tags') {
      return wp_strip_all_tags($value);
    }

    return strpos($value, '<') !== false ? wp_kses_post($value) : trim($value);
  }

  private function mysql_date(string $value): string {
    $timestamp = strtotime($value);
    return $timestamp === false ? '' : wp_date('Y-m-d H:i:s', $timestamp);
  }

  private function event_date(string $value): ?DateTimeImmutable {
    if (trim($value) === '') {
      return null;
    }

    try {
      return new DateTimeImmutable($value);
    } catch (Exception $error) {
      return null;
    }
  }

  private function fallback_title(array $record, string $identity): string {
    foreach (['DocumentName', 'NodeName', 'Name', 'Title', 'Recipient'] as $field) {
      if (!empty($record[$field])) {
        return wp_strip_all_tags($record[$field]);
      }
    }

    return $identity;
  }

  private function is_image_field(string $post_type, string $field_name): bool {
    $model = sd_kentico_content_model();
    foreach ($model[$post_type]['fields'] ?? [] as $field) {
      if ($field[0] === $field_name) {
        return $field[2] === 'image';
      }
    }

    return false;
  }

  private function normalize_field_value(string $post_type, string $field_name, string $value) {
    $model = sd_kentico_content_model();
    foreach ($model[$post_type]['fields'] ?? [] as $field) {
      if ($field[0] !== $field_name) {
        continue;
      }

      if ($field[2] === 'true_false') {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
      }

      if ($field[2] === 'date_time_picker') {
        return $this->mysql_date($value);
      }

      return $value;
    }

    return $value;
  }

  private function normalize_terms(string $value, array $value_map = []): array {
    $terms = preg_split('/\s*[;,|]\s*/', wp_strip_all_tags($value), -1, PREG_SPLIT_NO_EMPTY);
    $terms = array_map(function ($term) use ($value_map) {
      $term = trim($term);
      return $value_map[$term] ?? str_replace('_', ' ', $term);
    }, $terms);

    return array_values(array_filter($terms));
  }

  private function store_source_metadata(int $post_id, array $record): void {
    $metadata = [
      '_sd_kentico_node_guid' => 'NodeGUID',
      '_sd_kentico_document_guid' => 'DocumentGUID',
      '_sd_kentico_alias_path' => 'NodeAliasPath',
      '_sd_kentico_item_guid' => 'ItemGUID',
      '_sd_kentico_node_id' => 'NodeID',
      '_sd_kentico_parent_node_id' => 'NodeParentID',
      '_sd_kentico_document_id' => 'DocumentID',
    ];

    foreach ($metadata as $meta_key => $source_field) {
      if (!empty($record[$source_field])) {
        update_post_meta($post_id, $meta_key, $record[$source_field]);
      }
    }
  }

  public function rebuild_page_hierarchy(): int {
    $pages = get_posts([
      'post_type' => 'page',
      'post_status' => 'any',
      'posts_per_page' => -1,
      'meta_key' => '_sd_kentico_source_class',
      'meta_value' => 'CMS.MenuItem',
    ]);
    $node_map = [];
    foreach ($pages as $page) {
      $node_id = (int) get_post_meta($page->ID, '_sd_kentico_node_id', true);
      if ($node_id) {
        $node_map[$node_id] = $page->ID;
      }
    }

    $updated = 0;
    foreach ($pages as $page) {
      $parent_node_id = (int) get_post_meta($page->ID, '_sd_kentico_parent_node_id', true);
      $parent_id = (int) ($node_map[$parent_node_id] ?? 0);
      if ((int) $page->post_parent !== $parent_id) {
        wp_update_post(['ID' => $page->ID, 'post_parent' => $parent_id]);
        $updated++;
      }
    }

    return $updated;
  }
}

if (defined('WP_CLI') && WP_CLI) {
  /**
   * Test or import one Kentico media-library image.
   *
   * ## OPTIONS
   *
   * <source>
   * : Kentico ~/AAISite/media path or an aai.org media URL.
   *
   * [--post-id=<id>]
   * : Optional parent post ID.
   *
   * [--dry-run]
   * : Resolve the source without downloading it.
   */
  $sd_kentico_media_command = function (array $args, array $assoc_args): void {
    $dry_run = isset($assoc_args['dry-run']);
    $importer = new SD_Kentico_Media_Importer($dry_run);
    $source = $args[0];

    if ($dry_run) {
      $remote_url = $importer->remote_url($source);
      if (is_wp_error($remote_url)) {
        WP_CLI::error($remote_url->get_error_message());
      }
      WP_CLI::success('Would download ' . $remote_url);
      return;
    }

    $attachment_id = $importer->import_image($source, (int) ($assoc_args['post-id'] ?? 0));
    if (is_wp_error($attachment_id)) {
      WP_CLI::error($attachment_id->get_error_message());
    }

    WP_CLI::success(sprintf('Attachment %d: %s', $attachment_id, wp_get_attachment_url($attachment_id)));
  };

  WP_CLI::add_command('sd kentico media', $sd_kentico_media_command);

  /**
   * Import one mapped Kentico source class.
   *
   * ## OPTIONS
   *
   * <source-class>
   * : Exact source class from sd_kentico_source_map().
   *
   * [--data-dir=<path>]
   * : Mounted export root. Default: /mnt/kentico-data.
   *
   * [--limit=<number>]
   * : Maximum records to read. Zero imports all records.
   *
   * [--offset=<number>]
   * : Number of matching records to skip.
   *
   * [--post-status=<status>]
   * : Destination post status. Default: draft.
   *
   * [--skip-media]
   * : Do not download mapped image fields.
   *
   * [--dry-run]
   * : Stream and map records without database or filesystem writes.
   */
  $sd_kentico_import_command = function (array $args, array $assoc_args): void {
    try {
      $importer = new SD_Kentico_Content_Importer([
        'data_root' => $assoc_args['data-dir'] ?? '/mnt/kentico-data',
        'dry_run' => isset($assoc_args['dry-run']),
        'skip_media' => isset($assoc_args['skip-media']),
        'post_status' => $assoc_args['post-status'] ?? 'draft',
      ]);
      $stats = $importer->import_class(
        $args[0],
        max(0, (int) ($assoc_args['limit'] ?? 0)),
        max(0, (int) ($assoc_args['offset'] ?? 0))
      );
    } catch (Throwable $error) {
      WP_CLI::error($error->getMessage());
    }

    WP_CLI::log(sprintf(
      'Read: %d; created: %d; updated: %d; skipped: %d; failed: %d; media failures: %d',
      $stats['read'],
      $stats['created'],
      $stats['updated'],
      $stats['skipped'],
      $stats['failed'],
      $stats['media_failed']
    ));
    WP_CLI::success(isset($assoc_args['dry-run']) ? 'Dry run complete.' : 'Import complete.');
  };

  WP_CLI::add_command('sd kentico import', $sd_kentico_import_command);

  /**
   * Import every mapped Kentico content class.
   *
   * ## OPTIONS
   *
   * [--data-dir=<path>]
   * : Mounted export root. Default: /mnt/kentico-data.
   *
   * [--post-status=<status>]
   * : Destination post status. Default: draft.
   *
   * [--skip-media]
   * : Do not download mapped image fields.
   *
   * [--dry-run]
   * : Stream and map all records without writes.
   */
  $sd_kentico_import_all_command = function (array $args, array $assoc_args): void {
    try {
      $importer = new SD_Kentico_Content_Importer([
        'data_root' => $assoc_args['data-dir'] ?? '/mnt/kentico-data',
        'dry_run' => isset($assoc_args['dry-run']),
        'skip_media' => isset($assoc_args['skip-media']),
        'post_status' => $assoc_args['post-status'] ?? 'draft',
      ]);
      $totals = ['read' => 0, 'created' => 0, 'updated' => 0, 'skipped' => 0, 'failed' => 0, 'media_failed' => 0];
      foreach (sd_kentico_source_map() as $source_class => $rule) {
        WP_CLI::log('Importing ' . $source_class . '...');
        $stats = $importer->import_class($source_class);
        WP_CLI::log(sprintf(
          '  read=%d created=%d updated=%d failed=%d media_failed=%d',
          $stats['read'],
          $stats['created'],
          $stats['updated'],
          $stats['failed'],
          $stats['media_failed']
        ));
        foreach ($totals as $key => $value) {
          $totals[$key] += $stats[$key];
        }
      }

      $hierarchy_updates = isset($assoc_args['dry-run']) ? 0 : $importer->rebuild_page_hierarchy();
    } catch (Throwable $error) {
      WP_CLI::error($error->getMessage());
    }

    WP_CLI::log(sprintf(
      'Total read=%d created=%d updated=%d failed=%d media_failed=%d hierarchy_updates=%d',
      $totals['read'],
      $totals['created'],
      $totals['updated'],
      $totals['failed'],
      $totals['media_failed'],
      $hierarchy_updates
    ));
    WP_CLI::success(isset($assoc_args['dry-run']) ? 'Full dry run complete.' : 'Full import complete.');
  };

  WP_CLI::add_command('sd kentico import-all', $sd_kentico_import_all_command);
}