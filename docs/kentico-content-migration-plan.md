# Kentico-to-WordPress Content Migration Plan

## Purpose

This document is the handoff specification for implementing and running the AAI Kentico-to-WordPress content migration. It is written so another AI or developer can continue without repeating the source audit.

This is a **migration implementation plan**, not an instruction to run a production import immediately. The structured data is sufficient for importer development and dry runs, but the primary Kentico media-library binaries are currently missing.

## Non-negotiable repository rules

Read `AGENTS.md` before making changes.

- Work only in the custom theme at `wp-content/themes/sd_base/` and approved root configuration files.
- Do not edit WordPress core or vendor-managed plugins.
- Keep the implementation in PHP. The importer should run through WP-CLI.
- Do not run Sass, Node, frontend build, or watch commands. Richard handles frontend compilation.
- Do not commit source exports, user data, uploads, generated logs, `vendor/`, `node_modules/`, or `dist/`.
- Do not expose credentials, password hashes, PII, uploaded form files, or secret-like values from the source exports.
- Treat `/Users/shawnhopkins/Downloads/Data` as restricted, read-only source data.
- Preserve existing public APIs and the existing `plan` content type.

## Current implementation state

The following PHP model files already exist:

- `wp-content/themes/sd_base/inc/kentico-content-model.php`
  - Registers 14 normalized custom post types.
  - Registers 6 taxonomies.
  - Registers local ACF field groups.
  - Seeds controlled award/program taxonomy terms.
- `wp-content/themes/sd_base/inc/kentico-migration-map.php`
  - Defines source-class-to-destination mappings.
  - Covers all 57 source classes listed in the supplied CSV.
  - Covers every concrete `aai.*` field listed in the CSV.
  - Includes preliminary core post/page, Events Calendar, and form mappings.
- `wp-content/themes/sd_base/inc/kentico-blocks.php`
  - Adds Content Section, Accordion Group, Document Links, and Related Content ACF blocks.
- `wp-content/themes/sd_base/functions.php`
  - Loads these files and exposes a filterable ACF block registry.

No content importer has been implemented yet. The mapping file is a specification/helper, not an executable migration.

## Source package

Source root:

`/Users/shawnhopkins/Downloads/Data`

### Package inventory

The package contains:

- 116 XML metadata/data exports outside the binary payload tree.
- 1,541 current document-tree records across 24 classes.
- 8,641 custom-table rows, including 327 rows from a test table that should be excluded unless explicitly requested.
- 1,415 document URL aliases.
- 31 document attachment metadata rows and all 31 matching attachment binaries.
- 4,792 media-library metadata rows across 20 media libraries.
- Six Kentico form definitions.
- Approximately 1,508 historical BizForm submission rows.
- Seven actual Kentico user records plus user settings/site-assignment rows.
- Current document data, aliases, workflow/version metadata, page-template definitions, web-part definitions, and limited site/module assets.

### Document record counts

| Source class | Rows | Destination |
|---|---:|---|
| `CMS.MenuItem` | 400 | WordPress `page` |
| `CMS.BookingEvent` | 309 | The Events Calendar `tribe_events` |
| `aai.MemberNews` | 193 | `member_news` |
| `aai.HistoryProfiles` | 191 | `history_profile` |
| `CMS.News` | 122 | WordPress `post` |
| `aai.History_Article` | 62 | `history_article` |
| `aai.Obituaries` | 61 | `obituary` |
| `aai.DistinguishedLecturers` | 38 | `dist_lecturer` |
| `CMS.Event` | 38 | The Events Calendar `tribe_events` |
| `aai.In_Memoriam` | 29 | `in_memoriam` |
| `aai.PresidentMessage` | 24 | `presidents_message` |
| `aai.committees` | 22 | `committee` |
| `aai.travelaward` | 15 | `award_program` |
| `aai.PastMeetings` | 14 | `past_meeting` |
| `aai.career_award` | 11 | `award_program` |
| `aai.CIIFP` | 3 | `award_program` |
| `aai.PPFP` | 2 | `award_program` |
| `aai.HistoryNews` | 1 | `history_article` |
| `aai.PA_Recog_Award` | 1 | `award_program` |
| `aai.PSA` | 1 | `award_program` |
| `aai.SummerTeacherProgram` | 1 | `award_program` |
| `aai.Travel_for_Techniques` | 1 | `award_program` |
| `CMS.Folder` | 1 | Structural only; do not create public content unless needed for hierarchy |
| `CMS.Root` | 1 | Structural only; do not import as content |

All document records use culture `en-US`. No multilingual migration is currently required.

### Custom-table counts

| Export/table | Rows | Destination/action |
|---|---:|---|
| Awardees | 167 | `award_recipient` |
| Chambers Memorial Award | 14 | `award_recipient` |
| CIFP Expanded | 282 | `cifp_recipient` |
| CIIF | 691 | `award_recipient` |
| Distinguished Fellows | 109 | `distinguished_fellow` |
| Distinguished Service Award | 47 | `award_recipient` |
| Early Career Faculty Travel Grant | 1,528 | `award_recipient` |
| European Congress Travel Grant | 97 | `award_recipient` |
| Excellence in Mentoring Award | 24 | `award_recipient` |
| Herzenberg Award | 6 | `award_recipient` |
| High School Teachers Program | 162 | `award_recipient` |
| Human Immunology Research | 16 | `award_recipient` |
| Investigator Award | 28 | `award_recipient` |
| Laboratory Travel Grant | 541 | `award_recipient` |
| LB Poster Award | 24 | `award_recipient` |
| Lefrançois Memorial Award | 6 | `award_recipient` |
| Lifetime Achievement Award | 27 | `award_recipient` |
| Lustgarten Memorial Award | 7 | `award_recipient` |
| Meritorious Career Award | 22 | `award_recipient` |
| Minority Scientist Travel Award | 88 | `award_recipient` |
| Public Affairs Recognition Award | 3 | `award_recipient` |
| Pfizer-Showell Travel Award | 28 | `award_recipient` |
| Public Service Award | 21 | `award_recipient` |
| TFT | 66 | `award_recipient` |
| Timeline JSON | 414 | `timeline_event` |
| Trainee Abstract Award | 2,911 | `award_recipient` |
| Trainee Achievement Award | 124 | `award_recipient` |
| Trainee Poster Award | 694 | `award_recipient` |
| Undergraduate Faculty Travel Grant | 167 | `award_recipient` |
| Test table | 327 | Exclude by default |

Total rows excluding the test table: **8,314**.

## Readiness decision

### Ready now

The package is sufficient to develop and dry-run migration of:

- Pages, posts, and all modeled custom post types.
- Custom-table records.
- Taxonomy terms derived from source fields and fixed source-class mappings.
- Document hierarchy and ordering.
- Basic WordPress menu candidates.
- Current publication state and dates.
- Author attribution through source user IDs.
- Events as content records.
- Document attachments.
- URL alias/redirect manifests.
- Manual reconstruction of forms.

### Production blocker: missing media-library binaries

The package contains metadata for **4,792 unique media-library files** with a declared total size of approximately **2.44 GiB**, but none of those 4,792 binaries is present under the exported payload tree.

The content exports contain:

- 5,074 embedded Kentico media-path occurrences.
- Approximately 3,214 unique raw media references.

The missing media set includes approximately:

- 3,284 JPG files.
- 784 PDFs.
- 573 PNG files.
- 115 GIF files.
- Office files, ebooks, presentations, video, calendar files, and archives.

Before production import, obtain a filesystem copy of all Kentico media-library directories, preserving paths relative to the Kentico site media root. A complete site backup containing the media tree is acceptable.

Do not falsely treat the files under `Files/cms_webpart`, `Files/cms_formusercontrol`, `Files/cms_cssstylesheet`, or similar module folders as content media. Those are mostly Kentico application assets.

### Optional supplemental exports

Request these only if the associated historical behavior must be retained:

1. Document-category association rows, commonly represented by a Kentico `CMS_DocumentCategory` table. Category definitions are present, but document-category assignments were not found.
2. Relationship-instance rows. A relationship-name definition export exists, but no relationship data export was found. No current document is marked as a linked document, so this may not be needed.
3. Booking/attendee/registration records for the 309 booking events. Event content is present; historical registrations are not.
4. A current-site crawl or SEO export if generated metadata must be preserved. The package contains one populated page-title override and no explicit meta descriptions or meta keywords.

## Known source-data caveats

### One malformed XML export

The copyright BizForm submission export contains an invalid XML character and is not well-formed. The other 115 metadata/data XML exports pass an XML well-formedness check.

Forms are being rebuilt manually, so this is not a blocker. If historical copyright submissions must be retained, parse that file with a recovery-capable parser or sanitize invalid XML code points in a copy. Never alter the source file in place.

### Portal Engine pages

Of the 400 `CMS.MenuItem` records:

- 368 contain `DocumentContent`.
- 104 contain `DocumentWebParts`.
- 184 reference a page template.

Direct content can be normalized into WordPress block/editor content. Portal Engine web-part configuration cannot be copied directly into Gutenberg. Extract meaningful text, links, media references, and component intent, then map to the available ACF blocks or flag the page for manual reconstruction.

Do not import Kentico rendering configuration, macros, server controls, scripts, or web-part XML as visible page content.

### Actual standard class field names

The preliminary map for `CMS.News`, `CMS.Event`, and `CMS.BookingEvent` uses generic field labels and must be corrected before import.

`CMS.News` source fields include:

- `NewsTitle` -> `post_title`
- `NewsSummary` -> `post_excerpt`
- `NewsText` -> `post_content`
- `NewsReleaseDate` -> `post_date`
- Relevant image fields, if populated, -> featured image/media import

`CMS.BookingEvent` source fields include:

- `EventName` / `EventFullName` -> event title, with a documented precedence rule
- `EventDetails` -> `post_content`
- `EventSummary` -> excerpt
- `EventDate` -> event start
- `EventEndDate` -> event end
- `EventAllDay` -> all-day flag
- `EventLocation` -> venue/location
- `Image` -> featured image
- `EventAllowRegistrationOverCapacity` -> preserve as migration metadata only unless replacement registration functionality needs it

`CMS.Event` source fields include:

- `EventName` -> event title
- `EventDetails` -> `post_content`
- `EventSummary` -> excerpt
- `EventDate_TextField` -> source date text requiring normalization
- `EventLocation` -> venue/location
- `PageTitleImage` -> page-title/featured media according to final design

The importer must report event rows whose source dates cannot be parsed; it must not silently substitute the current date.

### Security and privacy

The package includes:

- User PII and password hashes.
- Historical form submissions.
- Uploaded press documents.
- Potential secret-like values embedded in legacy content/configuration.

Requirements:

- Never commit the package or generated extracts.
- Never print raw sensitive values in logs.
- Do not migrate Kentico password hashes.
- Create or map only approved WordPress author accounts.
- Sanitize scripts, server-side code, macros, and secret-like configuration fragments from content.

## Importer architecture

Implement an idempotent WP-CLI migration system in PHP.

Recommended structure:

```text
wp-content/themes/sd_base/inc/migration/
  class-kentico-command.php
  class-kentico-xml-reader.php
  class-kentico-transformer.php
  class-kentico-media-importer.php
  class-kentico-content-importer.php
  class-kentico-event-importer.php
  class-kentico-redirect-exporter.php
  class-kentico-report.php
```

Load the command only when WP-CLI is active:

```php
if (defined('WP_CLI') && WP_CLI) {
  // Load and register migration command classes.
}
```

Do not load or parse source files on normal web requests.

### Suggested commands

Use a single command namespace with focused subcommands:

```text
wp sd-kentico preflight --source=/absolute/path/to/Data
wp sd-kentico import-media --source=/absolute/path/to/Data --dry-run
wp sd-kentico import-content --source=/absolute/path/to/Data --dry-run
wp sd-kentico import-custom-tables --source=/absolute/path/to/Data --dry-run
wp sd-kentico import-events --source=/absolute/path/to/Data --dry-run
wp sd-kentico export-redirects --source=/absolute/path/to/Data
wp sd-kentico verify --source=/absolute/path/to/Data
```

Useful options:

- `--dry-run`
- `--class=<Kentico class>`
- `--limit=<n>`
- `--offset=<n>`
- `--batch-size=<n>`
- `--update-existing`
- `--skip-media`
- `--report=<absolute path outside repository>`

### Parsing requirements

- Use `XMLReader` or another streaming parser for scalability and predictable memory use.
- Do not parse the 83 MiB version-history export unless historical WordPress revisions are explicitly required. Current-state migration does not need it.
- Treat source files as immutable.
- Decode XML entities safely.
- Preserve rich text only after sanitization and URL rewriting.
- Record parse failures with source class and non-sensitive source identity.
- Never log complete source rows.

### Idempotency

Every imported item needs a stable source identity.

Recommended identity components:

- Source class.
- Source primary ID when available.
- Source GUID when available.

Store:

- `kentico_source_class`
- `kentico_source_id`
- `kentico_source_url`
- A private indexed identity meta value such as `_sd_kentico_identity = <class>:<id-or-guid>`

Before insertion, query by `_sd_kentico_identity`:

- Default behavior: skip existing item and report it.
- With `--update-existing`: update mapped fields but preserve manually edited fields according to a documented update policy.
- Never create duplicates on rerun.

Maintain a source-to-WordPress ID map in memory per batch and optionally in a migration-only option or report file. Do not depend only on titles or slugs.

### Reporting

Each command must produce counts for:

- Read.
- Valid.
- Inserted.
- Updated.
- Skipped.
- Failed.
- Missing media.
- Unparseable dates.
- Unmapped fields.
- Unresolved internal links.

Write detailed machine-readable reports outside the repository. Reports must contain source IDs and error categories, not sensitive body content.

## Transform rules

### WordPress core fields

Use native fields when the mapping specifies:

- `post_title`
- `post_content`
- `post_excerpt`
- `post_date`
- Featured image
- Parent and menu order

Do not duplicate those values into ACF unless the content model explicitly requires a separate formatted value.

### Titles

- Strip HTML from rich-text source titles.
- Decode entities and normalize whitespace.
- For classes without a dedicated title field, use `DocumentName` as the deterministic fallback.
- Never derive a title from an arbitrary truncated body unless both the map and source audit show no safer value.
- Respect fixed titles in `sd_kentico_source_map()` for merged award-program classes.

### Rich text

For every rich-text value:

1. Remove scripts, server controls, macros, inline event handlers, and unsafe elements.
2. Convert Kentico `~/` and site-root links into resolvable source paths.
3. Rewrite internal document links using the source Node/GUID/alias map.
4. Rewrite media paths only after a media identity map exists.
5. Preserve semantic headings, paragraphs, lists, tables, links, emphasis, and accessible image alternatives.
6. Flag content containing untranslatable web parts or macros.

Use WordPress sanitization appropriate to trusted editorial HTML. Do not use unescaped raw source values in SQL, shell commands, or rendered admin notices.

### Dates

- Parse dates in a fixed timezone agreed with the site owner.
- Preserve source publication dates.
- Preserve event start/end and all-day state.
- Leave optional invalid dates unset and report them.
- Do not silently coerce invalid dates to epoch, today, or import time.

### Taxonomies

Use `wp_set_object_terms()` for canonical taxonomy assignment, including:

- `history_series`
- `profile_type`
- `committee_type`
- `award_program_type`
- `timeline_category`
- `award_type`

Normalize source text carefully, but preserve display names. For merged source classes, use the fixed term slugs in `sd_kentico_source_map()`.

ACF taxonomy fields configured with `save_terms` and `load_terms` should be synchronized with the actual WordPress term assignments.

### ACF fields

- Prefer `update_field()` with stable ACF field keys when available.
- Keep ACF values consistent with configured return formats.
- Image fields currently return attachment IDs.
- Do not store a source URL in an image field as a substitute for an attachment ID.
- Unknown timeline media types go into `media_type_raw`; map known values to `media_type`.

### Authors

- Build an explicit source user ID to WordPress user ID map.
- Map system/service/unknown accounts to an approved migration author.
- Do not recreate every Kentico account automatically.
- Do not migrate source password hashes.

### Publication state

Derive status using available Kentico fields such as:

- `DocumentCanBePublished`
- `DocumentIsArchived`
- `DocumentPublishFrom`
- `DocumentPublishTo`
- Current workflow/publication information

Define and document the status policy before the production run. A safe default is:

- Import currently published, publishable records as `publish`.
- Import archived, expired, or uncertain records as `draft` for review.
- Preserve original dates in migration metadata when status is uncertain.

## Media strategy

### Document attachments

The 31 document attachments are immediately importable.

- Match metadata and payload by attachment GUID.
- Exported payload filenames end in `.export`; remove only that export suffix when determining the real filename.
- Verify extension, MIME type, and size before sideloading.
- Use WordPress attachment APIs, not direct database inserts.

### Media libraries

Do not run a production media import until the missing 2.44 GiB media tree is available.

When obtained:

1. Read all 4,792 media metadata rows.
2. Resolve each metadata `FilePath` against the supplied media filesystem root.
3. Validate size and extension.
4. Import through WordPress media APIs.
5. Store source media GUID and original path as private attachment metadata.
6. Build maps by GUID and normalized source path.
7. Reuse an existing attachment when the same source GUID/path is encountered.
8. Rewrite rich-text references and assign featured images/page-title images.
9. Report missing, size-mismatched, unsupported, and duplicate files.

Do not use filename-only matching; filenames can collide across media libraries and folders.

A remote-fetch fallback from the live site may be implemented only after approval. If used, cache by source path/GUID, validate MIME type and size, rate-limit requests, and report HTTP failures.

## Import phases

### Phase 0: preflight and freeze

1. Confirm the Data folder remains unchanged.
2. Generate a non-sensitive manifest of source files, sizes, and checksums.
3. Validate XML well-formedness and record the known BizForm exception.
4. Verify expected document/custom-table counts.
5. Verify ACF Pro, Timber, The Events Calendar, and WP-CLI are available.
6. Confirm the site timezone.
7. Confirm the fallback migration author.
8. Confirm whether archived/expired content should be imported as drafts or omitted.
9. Confirm whether historical form submissions and event registrations are in scope.

**Acceptance gate:** preflight reports no unexplained missing source classes and no write operations occur during `--dry-run`.

### Phase 1: correct and validate mappings

1. Update `CMS.News`, `CMS.Event`, and `CMS.BookingEvent` mappings to actual source fields.
2. Add explicit source primary-ID/GUID definitions for every class/table.
3. Add field transforms for dates, booleans, rich-text titles, media, and fixed taxonomy terms.
4. Add a validation routine that compares all populated source fields against known mapped/ignored fields.
5. Require every non-system source field to be mapped or explicitly ignored with a reason.

**Acceptance gate:** zero unexplained populated fields for every in-scope source class.

### Phase 2: importer framework

1. Implement streaming readers.
2. Implement idempotent identity handling.
3. Implement dry-run behavior.
4. Implement structured reporting.
5. Implement batching and resumability.
6. Add unit-testable transformer methods that do not require database writes.

**Acceptance gate:** repeated dry runs produce identical counts and no database changes.

### Phase 3: authors and taxonomies

1. Create the approved author map.
2. Seed/resolve controlled terms.
3. Extract dynamic terms from source content.
4. Validate term normalization and collisions.

**Acceptance gate:** all taxonomy values resolve to a destination term or appear in an exception report.

### Phase 4: media

1. Import the 31 document attachments.
2. Once supplied, import all media-library binaries.
3. Build source-path/GUID maps.
4. Verify image dimensions, PDFs, and non-image downloads.

**Acceptance gate:** all expected media either imports successfully or is listed in an approved missing-media exception report.

### Phase 5: pages, posts, and document CPTs

1. Import structural nodes needed for hierarchy mapping without exposing `CMS.Root` or `CMS.Folder` publicly.
2. Import pages in parent-before-child order.
3. Import posts and document-based CPTs.
4. Apply terms, authors, dates, statuses, featured images, and ACF fields.
5. Convert direct content.
6. Flag Portal Engine pages for block/manual reconstruction.

**Acceptance gate:** destination counts match approved source counts, with every discrepancy explained.

### Phase 6: custom tables

1. Import all mapped custom tables.
2. Exclude the test table by default.
3. Apply fixed `award_type` terms based on source class.
4. Batch high-volume tables such as Trainee Abstract Award and Early Career Faculty Travel Grant.

**Acceptance gate:** 8,314 expected non-test rows are inserted, updated, skipped idempotently, or explicitly rejected with a reason.

### Phase 7: events

1. Normalize both event source classes into The Events Calendar.
2. Preserve start/end/all-day/location/details/summary.
3. Resolve event images.
4. Report invalid or text-only dates.
5. Do not imply that Kentico booking functionality has been migrated.

**Acceptance gate:** 347 event source records are accounted for and date validation passes.

### Phase 8: menus, internal links, and redirects

1. Reconstruct page hierarchy from `NodeParentID` and `NodeOrder`.
2. Use `DocumentMenuItemHideInNavigation` to generate menu candidates.
3. Do not automatically publish a menu without review; Kentico navigation may have been generated by web-part queries.
4. Build canonical source URL maps from `NodeAliasPath`.
5. Build redirect candidates from all 1,415 alias rows.
6. Rewrite internal links after all destination IDs/permalinks are known.
7. Detect redirect loops and collisions.

**Acceptance gate:** all aliases map to an imported canonical destination or an explicit exception; no redirect loops exist.

### Phase 9: forms

Rebuild the six forms manually in the selected WordPress form plugin:

- Advanced Course Interest
- Intro Course Interest
- Copyright Request
- Discount Request
- Job Ad Submission
- Press Inquiry

Historical submissions are not required for the form rebuild. If they are later requested, treat them as a separate privacy-reviewed migration project.

### Phase 10: QA and launch preparation

Run all verification gates below before content freeze/cutover.

## Verification matrix

### Count reconciliation

Compare source and destination counts by:

- Kentico class.
- Custom-table source.
- Destination post type.
- Destination taxonomy term.
- Published/draft status.

Every mismatch must have a documented reason.

### Content QA

Spot-check at minimum:

- 20 pages, including Portal Engine pages.
- 20 news posts.
- 20 history profiles across different profile types.
- 10 history articles.
- 10 committees.
- All award-program landing pages.
- 20 award recipients across at least 10 award types.
- 20 events, including all-day and multi-day examples.
- 20 timeline events with different media types.

Check:

- Titles and dates.
- Rich-text structure.
- Lists and tables.
- Internal links.
- Images and alternative text.
- PDFs/downloads.
- Taxonomy assignments.
- Featured images.
- Ordering fields.
- Unsafe or leaked scripts/configuration.

### Automated QA

- Broken internal-link scan.
- Missing-media scan.
- Redirect loop/collision scan.
- Duplicate `_sd_kentico_identity` scan.
- Orphan parent scan.
- Invalid date scan.
- Unmapped populated field scan.
- Unexpected HTML/script/macro scan.
- Destination count report.

### Idempotency QA

Run the same import twice in a disposable environment:

- First run creates expected records.
- Second run creates zero duplicates.
- `--update-existing` updates only fields allowed by the update policy.
- Reports remain deterministic.

## Definition of done

The migration is complete only when:

- The missing media-library binaries have been supplied or an approved exception/fallback strategy exists.
- All in-scope source classes and fields are mapped or explicitly ignored.
- All expected records are reconciled.
- Media and internal links resolve.
- Events have valid dates.
- Menus and redirects are reviewed.
- Forms are rebuilt and tested.
- Portal Engine pages are reconstructed or approved as exceptions.
- No sensitive source data appears in Git, logs, rendered pages, or public uploads unintentionally.
- A second migration run creates no duplicates.
- Stakeholders approve the QA sample and discrepancy report.

## Immediate next actions for the next AI/model

1. **Do not run a production import.**
2. Read `AGENTS.md`, this document, and the three existing Kentico PHP files.
3. Inspect current Git changes before editing; do not overwrite unrelated work.
4. Correct the generic standard news/event mappings using the actual fields listed above.
5. Implement the WP-CLI preflight command first.
6. Make preflight read-only and add `--dry-run` to every future importing command.
7. Add tests for mapping completeness, identity generation, date parsing, rich-text sanitization, and URL normalization.
8. Develop against a disposable/local database.
9. Request the complete Kentico media-library filesystem dump before production migration.
10. Stop and report rather than inventing data when a source field, media file, date, relationship, or destination is ambiguous.
