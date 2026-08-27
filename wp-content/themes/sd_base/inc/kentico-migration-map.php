<?php

/**
 * @file
 * Deterministic Kentico source-to-WordPress migration map.
 */

// phpcs:disable

if (!defined('ABSPATH')) {
  exit;
}

/**
 * Return explicit source rules for document types that map one-to-one or merge.
 *
 * Destination values use post_title, post_content, post_excerpt, post_date,
 * featured_image, ACF field names, and taxonomy:<taxonomy> consistently.
 */
function sd_kentico_source_map(): array {
  $map = [
    'aai.History_Article' => [
      'strategy' => 'wordpress',
      'post_type' => 'history_article',
      'fields' => [
        'Article_Title' => 'post_title',
        'Article_Text' => 'post_content',
        'Article_Author' => 'article_author',
        'Pub_Date' => 'post_date',
        'Key_Image' => 'featured_image',
        'Article_Summary' => 'post_excerpt',
        'Sort_Order' => 'sort_order',
        'Series_Desc' => 'taxonomy:history_series',
        'Display_Title' => 'display_title',
        'Series_Desc2' => 'series_description_2',
        'PageTitleImage' => 'page_title_image',
      ],
    ],
    'aai.HistoryProfiles' => [
      'strategy' => 'wordpress',
      'post_type' => 'history_profile',
      'taxonomy_value_maps' => [
        'profile_type' => [
          'Past_Presidents' => 'Past Presidents',
          'Nobel_Laureates' => 'Nobel Laureates',
          'Secretary_Treasurer' => 'Secretary/Treasurer',
        ],
      ],
      'fields' => [
        'Name' => 'post_title',
        'Prefix' => 'prefix',
        'Suffix' => 'suffix',
        'BriefBio' => 'post_content',
        'Photo' => 'featured_image',
        'ProfileType' => 'taxonomy:profile_type',
        'ServiceHistory' => 'service_history',
        'AwardsHonors' => 'awards_honors',
        'NobelPrizeType' => 'nobel_prize_type',
        'NobelPrizeScience' => 'nobel_prize_science',
        'Nobel_Subheading' => 'nobel_subheading',
        'LaskerType' => 'lasker_type',
        'Lasker_Subheading' => 'lasker_subheading',
        'Lasker_CMR_Subheading' => 'lasker_cmr_subheading',
        'OHFullInterview' => 'oral_history_full_interview',
        'OHTranscript' => 'oral_history_transcript',
        'OHClips' => 'oral_history_clips',
        'OHP_Subheading' => 'oral_history_subheading',
        'PresidentsAddress' => 'presidents_address',
        'PresidentsMessage' => 'presidents_message',
        'In_Office' => 'in_office',
        'In_Office_EIC' => 'in_office_eic',
        'In_Office_ST' => 'in_office_st',
        'In_Office_IH_EIC' => 'in_office_ih_eic',
        'Inst_Bio_Links' => 'institutional_bio_links',
        'PageTitleImage' => 'page_title_image',
        'Officer_Number' => 'officer_number',
        'EIC_Order' => 'eic_order',
        'EIC_IH_ORDER' => 'eic_ih_order',
        'NP_Order' => 'np_order',
        'LBA_Order' => 'lba_order',
        'LCA_Order' => 'lca_order',
        'LPSA_Order' => 'lpsa_order',
        'LSAA_Order' => 'lsaa_order',
        'Awardee_Order' => 'awardee_order',
        'ST_Order' => 'st_order',
        'OHP_Order' => 'ohp_order',
        'OHP_EIC_Order' => 'ohp_eic_order',
        'OHP_A_Order' => 'ohp_a_order',
      ],
    ],
    'aai.MemberNews' => [
      'strategy' => 'wordpress',
      'post_type' => 'member_news',
      'title_fallback' => 'kentico_document_name',
      'fields' => ['News' => 'post_content', 'Order_Number' => 'order_number'],
    ],
    'aai.Obituaries' => [
      'strategy' => 'wordpress',
      'post_type' => 'obituary',
      'title_fallback' => 'kentico_document_name',
      'fields' => [
        'Obituary' => 'post_content',
        'D_Date' => 'post_date',
        'Year' => 'year',
        'Alpha_Sort' => 'alpha_sort',
        'Order_Number' => 'order_number',
      ],
    ],
    'aai.In_Memoriam' => [
      'strategy' => 'wordpress',
      'post_type' => 'in_memoriam',
      'fields' => [
        'Name' => 'post_title',
        'Summary' => 'post_excerpt',
        'Body' => 'post_content',
        'Photo' => 'featured_image',
        'Order_Number' => 'order_number',
        'PageTitleImage' => 'page_title_image',
      ],
    ],
    'aai.DistinguishedLecturers' => [
      'strategy' => 'wordpress',
      'post_type' => 'dist_lecturer',
      'legacy_post_type' => 'distinguished_lecturer',
      'title_fallback' => 'kentico_document_name',
      'fields' => ['Year' => 'year', 'LecturerDetails' => 'post_content', 'Location' => 'location'],
    ],
    'aai.PresidentMessage' => [
      'strategy' => 'wordpress',
      'post_type' => 'presidents_message',
      'fields' => [
        'Name' => 'post_title',
        'Body' => 'post_content',
        'Photo' => 'featured_image',
        'Summary' => 'post_excerpt',
        'Order_Number' => 'order_number',
        'PageTitleImage' => 'page_title_image',
      ],
    ],
    'aai.committees' => [
      'strategy' => 'wordpress',
      'post_type' => 'committee',
      'taxonomy_value_maps' => [
        'committee_type' => [
          '01' => 'Awards Committee',
          '02' => 'Clinical Immunology Committee',
          '03' => 'Committee on Public Affairs',
          '04' => 'Committee on the Status of Women',
          '05' => 'Education Committee',
          '06' => 'Finance Committee',
          '07' => 'Membership Committee',
          '08' => 'Minority Affairs Committee',
          '09' => 'Nominating Committee',
          '10' => 'Program Committee',
          '11' => 'Publications Committee',
          '12' => 'Veterinary Immunology Committee',
          '13' => 'Ad-Hoc Committees',
        ],
      ],
      'fields' => [
        'CommitteeName' => 'post_title',
        'CommitteeType' => 'taxonomy:committee_type',
        'CommitteeRole' => 'committee_role',
        'CommitteeMission' => 'committee_mission',
        'CommitteeMembers' => 'committee_members',
        'MembersTab' => 'members_tab',
        'CommitteeActivitiesandSymposia' => 'activities_and_symposia',
        'SessionTab' => 'session_tab',
        'CommitteeResources' => 'committee_resources',
        'SubCommittees' => 'subcommittees',
        'ApplicationFromDate' => 'application_from_date',
        'ApplicationToDate' => 'application_to_date',
        'PageTitleImage' => 'page_title_image',
      ],
    ],
    'aai.PastMeetings' => [
      'strategy' => 'wordpress',
      'post_type' => 'past_meeting',
      'transforms' => ['Title' => 'strip_tags'],
      'fields' => [
        'Title' => 'post_title',
        'Description' => 'post_content',
        'Image' => 'featured_image',
        'DateLocation' => 'date_location',
        'Buttons' => 'buttons',
        'Order_Number' => 'order_number',
      ],
    ],
    'aai.HistoryNews' => [
      'strategy' => 'wordpress',
      'post_type' => 'history_article',
      'title_fallback' => 'kentico_document_name',
      'fields' => ['Body' => 'post_content', 'Photo' => 'featured_image'],
    ],
    'aai.Distinguished_Fellows' => [
      'strategy' => 'wordpress',
      'post_type' => 'distinguished_fellow',
      'fields' => [
        'Name' => 'post_title',
        'AlphaSort' => 'alpha_sort',
        'Year' => 'year',
        'Organization' => 'organization',
        'JoinDate' => 'join_date',
        'Image' => 'featured_image',
        'AAI_URL' => 'aai_url',
        'PastPresident' => 'past_president',
        'PastSecTreas' => 'past_secretary_treasurer',
        'PastEIC' => 'past_eic',
        'PastEIC_IH' => 'past_eic_ih',
      ],
    ],
    'aai.CIFP_Expanded' => [
      'strategy' => 'wordpress',
      'post_type' => 'cifp_recipient',
      'fields' => [
        'PI_Full_Name' => 'post_title',
        'PI_Job_Title' => 'pi_job_title',
        'Org_Name' => 'organization_name',
        'Trainee_Name' => 'trainee_name',
        'Trainee_Title' => 'trainee_title',
        'Project_Description' => 'post_content',
        'CIFP_Photo' => 'featured_image',
        'Year' => 'year',
        'Sort_Order' => 'sort_order',
      ],
    ],
    'aai.timeline_json' => [
      'strategy' => 'wordpress',
      'post_type' => 'timeline_event',
      'fields' => [
        'title' => 'post_title',
        'description' => 'post_content',
        'start' => 'start',
        'year' => 'year',
        'thumbnail' => 'featured_image',
        'media' => 'media',
        'mediatype' => 'media_type',
        'credit' => 'credit',
        'caption' => 'caption',
        'category' => 'taxonomy:timeline_category',
      ],
      'fallback_fields' => ['mediatype' => 'media_type_raw'],
    ],
    'CMS.News' => [
      'strategy' => 'wordpress',
      'post_type' => 'post',
      'transforms' => ['NewsTitle' => 'strip_tags'],
      'post_content_fallbacks' => ['NewsSummary'],
      'fields' => [
        'NewsTitle' => 'post_title',
        'NewsText' => 'post_content',
        'NewsSummary' => 'post_excerpt',
        'NewsReleaseDate' => 'post_date',
        'PageTitleImage' => 'featured_image',
        'Featured_Image' => 'featured_image',
      ],
    ],
    'CMS.MenuItem' => [
      'strategy' => 'wordpress',
      'post_type' => 'page',
      'transforms' => ['DocumentContent' => 'document_content'],
      'fields' => [
        'MenuItemName' => 'post_title',
        'DocumentContent' => 'post_content',
        'PageTitleImage' => 'featured_image',
      ],
      'note' => 'Create WordPress menu assignments separately after page import.',
    ],
    'CMS.BookingEvent' => [
      'strategy' => 'events_calendar',
      'post_type' => 'tribe_events',
      'fields' => [
        'EventFullName' => 'post_title',
        'EventDetails' => 'post_content',
        'EventSummary' => 'post_excerpt',
        'EventDate' => '_EventStartDate',
        'EventEndDate' => '_EventEndDate',
        'EventAllDay' => '_EventAllDay',
        'EventLocation' => 'tribe_venue',
        'PageTitleImage' => 'featured_image',
        'Image' => 'featured_image',
      ],
    ],
    'CMS.Event' => [
      'strategy' => 'wordpress',
      'post_type' => 'post',
      'post_content_fallbacks' => ['DocumentContent'],
      'transforms' => ['DocumentContent' => 'document_content'],
      'fields' => [
        'EventName' => 'post_title',
        'EventDetails' => 'post_content',
        'EventSummary' => 'post_excerpt',
        'EventDate_TextField' => 'kentico_event_date_text',
        'EventLocation' => 'kentico_event_location',
        'NewsOrder' => 'kentico_news_order',
        'PageTitleImage' => 'featured_image',
      ],
    ],
  ];

  $award_programs = [
    'aai.travelaward' => [
      'term' => 'travel-award',
      'title' => 'Travel Award',
      'title_source' => 'TA_AwardType',
      'fields' => [
        'TA_AwardType' => 'taxonomy:award_program_type',
        'TA_Description' => 'post_content',
        'TA_Eligibility' => 'eligibility',
        'TA_Award' => 'award_details',
        'TA_App_Inst' => 'application_instructions',
        'TA_Deadline' => 'deadline',
        'TA_CurrentRecipient' => 'current_recipients',
        'TA_PastRecipients' => 'past_recipients',
      ],
    ],
    'aai.career_award' => [
      'term' => 'career-award',
      'title' => 'Career Award',
      'title_source' => 'CA_AwardType',
      'fields' => [
        'CA_AwardType' => 'taxonomy:award_program_type',
        'CA_Description' => 'post_content',
        'CA_Eligibility' => 'eligibility',
        'CA_Award' => 'award_details',
        'CA_App_Inst' => 'application_instructions',
        'CA_Nomination' => 'nomination',
        'CADeadline' => 'deadline',
        'CA_CurrentRecipient' => 'current_recipients',
        'CA_PastRecipients' => 'past_recipients',
      ],
    ],
    'aai.CIIFP' => [
      'term' => 'careers-in-immunology-fellowship',
      'title' => 'Careers in Immunology Fellowship',
      'fields' => [
        'Description' => 'post_content',
        'Deadline' => 'deadline',
        'Requirements' => 'requirements',
        'Eligibility' => 'eligibility',
        'FellowshipSupport' => 'fellowship_support',
        'TermsConditions' => 'terms_conditions',
        'Process' => 'process',
        'Instructions' => 'application_instructions',
        'CurrentRecipients' => 'current_recipients',
        'Past_Recipients' => 'past_recipients',
      ],
    ],
    'aai.PPFP' => [
      'term' => 'public-fellows',
      'fields' => [
        'Title' => 'post_title',
        'Introduction' => 'introduction',
        'Program' => 'post_content',
        'Description' => 'description',
        'CurrentFellows' => 'current_recipients',
        'PastFellows' => 'past_recipients',
        'Instructions' => 'application_instructions',
      ],
    ],
    'aai.PSA' => [
      'term' => 'public-service-award',
      'fields' => [
        'Title' => 'post_title',
        'Introduction' => 'post_content',
        'Current_Recipient' => 'current_recipients',
        'Past_Recipient' => 'past_recipients',
      ],
    ],
    'aai.PA_Recog_Award' => [
      'term' => 'public-affairs-recognition-award',
      'fields' => ['Title' => 'post_title', 'Introduction' => 'post_content', 'Current_Recipient' => 'current_recipients'],
    ],
    'aai.Travel_for_Techniques' => [
      'term' => 'travel-for-techniques',
      'title' => 'Travel for Techniques',
      'fields' => [
        'Description' => 'post_content',
        'Eligibility' => 'eligibility',
        'TravelSupport' => 'travel_support',
        'TermsConditions' => 'terms_conditions',
        'AwardCycles' => 'award_cycles',
        'Process' => 'process',
        'Deadline' => 'deadline',
        'App_Instructions' => 'application_instructions',
        'CurrentRecipients' => 'current_recipients',
        'Past_Recipients' => 'past_recipients',
      ],
    ],
    'aai.SummerTeacherProgram' => [
      'term' => 'summer-research-program-for-teachers',
      'title' => 'Summer Research Program for Teachers',
      'fields' => [
        'Description' => 'post_content',
        'Goals' => 'goals',
        'Structure' => 'structure',
        'Deadline' => 'deadline',
        'Instructions' => 'application_instructions',
        'CurrentParticipants' => 'current_recipients',
        'Past_Participants' => 'past_recipients',
      ],
    ],
  ];

  foreach ($award_programs as $source_class => $definition) {
    $definition['fields'] += [
      'ApplicationFromDate' => 'application_from_date',
      'ApplicationToDate' => 'application_to_date',
      'PageTitleImage' => 'page_title_image',
    ];
    $map[$source_class] = [
      'strategy' => 'wordpress',
      'post_type' => 'award_program',
      'fixed_title' => $definition['title'] ?? null,
      'title_source' => $definition['title_source'] ?? null,
      'fixed_terms' => ['award_program_type' => $definition['term']],
      'fields' => $definition['fields'],
    ];
  }

  $award_recipient_sources = [
    'aai.Lefrancois_Memorial_Award' => 'lefrancois-memorial-award',
    'aai.Human_Immunology_Research' => 'steinman-human-immunology-research-award',
    'aai.Investigator_Award' => 'investigator-award',
    'aai.TFT' => 'travel-for-techniques',
    'aai.Lifetime_Achievement_Award' => 'lifetime-achievement-award',
    'aai.Laboratory_Travel_Grant' => 'laboratory-travel-grant',
    'aai.European_Congress_of_Immunology_Travel_Grant' => 'european-congress-immunology-travel-grant',
    'aai.Chambers_Memorial_Award' => 'chambers-memorial-award',
    'aai.Herzenberg_Award' => 'herzenberg-award',
    'aai.HST_Program' => 'high-school-teachers-program',
    'aai.Excellence_in_Mentoring_Award' => 'excellence-in-mentoring-award',
    'aai.awardees' => 'awardees',
    'aai.Trainee_Achievement_Award' => 'trainee-achievement-award',
    'aai.Early_Career_Faculty_Travel_Grant' => 'early-career-faculty-travel-grant',
    'aai.Undergraduate_Faculty_Travel_Grant' => 'undergraduate-faculty-travel-grant',
    'aai.CIIF' => 'careers-in-immunology-fellowship',
    'aai.Distinguished_Service_Award' => 'distinguished-service-award',
    'aai.Trainee_Poster_Award' => 'trainee-poster-award',
    'aai.Meritorious_Career_Award' => 'meritorious-career-award',
    'aai.Minority_Scientist_Travel_Award' => 'minority-scientist-travel-award',
    'aai.Trainee_Abstract_Award' => 'trainee-abstract-award',
    'aai.Pfizer_Showell_Travel_Award' => 'pfizer-showell-travel-award',
    'aai.LB_Poster_Award' => 'lb-poster-award',
    'aai.Lustgarte_Memorial_Award' => 'lustgarten-memorial-award',
    'aai.Public_Service_Award_Table' => 'public-service-award',
    'aai.PA_Recognition_Award_Table' => 'public-affairs-recognition-award',
  ];

  foreach ($award_recipient_sources as $source_class => $term) {
    $map[$source_class] = [
      'strategy' => 'wordpress',
      'post_type' => 'award_recipient',
      'fixed_terms' => ['award_type' => $term],
      'transforms' => ['Recipient' => 'strip_tags'],
      'fields' => [
        'Recipient' => 'post_title',
        'Year' => 'year',
        'Position' => 'position',
        'Institution' => 'institution',
        'Cycle' => 'cycle',
        'Term' => 'term',
        'Remarks' => 'remarks',
        'Description' => 'description',
        'CIFP_Position' => 'secondary_position',
        'TFT_Position' => 'secondary_position',
        'CIFP_Year' => 'secondary_year',
        'T_Year' => 'secondary_year',
        'SortOrder' => 'sort_order',
        'Sort_Order' => 'sort_order',
        'AwardeeImage' => 'featured_image',
      ],
    ];
  }

  return $map;
}

/**
 * Return the available form migration blueprints from the supplied CSV.
 *
 * Only fields present in the source specification are asserted. Forms marked
 * requires_source_schema need the original Kentico form export before a safe
 * Contact Form 7 definition can be generated.
 */
function sd_kentico_form_map(): array {
  return [
    'bizform_advcourseinterest' => [
      'title' => 'Advanced Course Interest Form',
      'plugin' => 'contact-form-7',
      'fields' => [
        'Name' => 'text',
        'Position' => 'text',
        'Organization' => 'text',
        'Email' => 'email',
        'Interest' => 'textarea',
      ],
      'requires_source_schema' => false,
    ],
    'bizform_introcourseinterest' => [
      'title' => 'Intro Course Interest Form',
      'plugin' => 'contact-form-7',
      'fields' => [
        'Name' => 'text',
        'Position' => 'text',
        'Organization' => 'text',
        'Email' => 'email',
        'Interest' => 'textarea',
      ],
      'requires_source_schema' => false,
    ],
    'bizform_copyright' => [
      'title' => 'Copyright Request Form',
      'plugin' => 'contact-form-7',
      'fields' => [],
      'requires_source_schema' => true,
    ],
    'bizform_discount' => [
      'title' => 'Discount Request Form',
      'plugin' => 'contact-form-7',
      'fields' => [],
      'requires_source_schema' => true,
    ],
    'bizform_jobad' => [
      'title' => 'Job Ad Submission Form',
      'plugin' => 'contact-form-7',
      'fields' => [],
      'requires_source_schema' => true,
    ],
    'bizform_press' => [
      'title' => 'Press Inquiry Form',
      'plugin' => 'contact-form-7',
      'fields' => [],
      'requires_source_schema' => true,
    ],
  ];
}

/**
 * Retrieve one source mapping without duplicating importer lookup logic.
 */
function sd_get_kentico_source_rule(string $source_class): ?array {
  $map = sd_kentico_source_map();
  return $map[$source_class] ?? null;
}
