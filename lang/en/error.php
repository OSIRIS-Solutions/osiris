<?php
return [
    'admin_no_permission' => 'You do not have permission to access the admin area.',
    'file_partially_uploaded' => 'The uploaded file was only partially uploaded.',
    'file_too_big_max_2MB' => 'The file is too big: max 2 MB is allowed.',
    'file_upload_generic' => 'Sorry, there was an error uploading your file.',
    'file_upload_exceeds_limit' => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
    'file_upload_missing_temp' => 'Missing a temporary folder.',
    'file_upload_stopped' => 'A PHP extension stopped the file upload.',
    'file_upload_too_large' => 'The file is too large: max {{max}} is allowed.',
    'file_upload_write_failed' => 'Failed to write file to disk.',
    'method_not_allowed_message' => 'The method "{{method}}" is not allowed for the requested URL.',
    'method_not_allowed' => 'Method not allowed',
    'no_file_uploaded' => 'No file was uploaded.',
    'no_values' => 'No values provided.',
    'organization_select_missing' => 'No organization selected',
    'page_not_found_message' => 'The page you are looking for does not exist or has been moved.',
    'page_not_found' => 'Page not found',
    'password_no_permission' => 'You do not have permission to reset passwords.',
    'something_went_wrong' => 'Something went wrong.',
    'user_not_found' => 'User not found.',
    'username_already_taken' => 'The username is already taken. Please try again.',
    'news_not_enabled' => 'News are not enabled.',
    'infrastructure_no_edit_permission' => 'You do not have permission to edit this infrastructure.',
    'why_do_i_have_to_confirm_my_authorships' => 'Why do I have to confirm my authorships?',
    'but_i_have_already_confirmed_this_activity_once_that_might_be_because_as_so' => '
                    <q><b>But I have already confirmed this activity once.</b></q><br>
                    That might be.
                    Because as soon as an activity is edited, even if it is only that a document was deposited or a spelling mistake in the title was corrected, the confirmation of all authors is reset.
                    This is to avoid that already confirmed activities are edited without your knowledge.
                    ',
    'why_do_i_have_to_review_online_ahead_of_print_articles' => 'Why do I have to review <q>Online ahead of print</q> Articles?',
    'online_ahead_of_print_means_that_the_publication_is_already_available_onlin' => '
                    [Online ahead of print] means that the publication is already available online, but the actual publication in an issue is still pending.
                    An example is the NAR database issue, where publications are already available online in September or October, although the
                    issue is not published until the following January.
                    ',
    'these_publications_cannot_be_included_in_the_reports_they_are_included_in_o' => '
                    <b>These publications cannot be included in the reports.</b>
                    They are included in OSIRIS in order not to lose sight of them and because they represent achievements already made.
                    But for this reason, it is regularly queried whether the publication has now been published.
                    Because only then can it be taken into account in the reporting.
                    ',
    'the_bibliographic_data_must_be_checked_again_the_check_mark_for_online_ahea' => '
                    <b>The bibliographic data must be checked again.</b> The check mark for <q>Online ahead of print</q> must be removed and the publication date is usually also adjusted.
                    Furthermore, it also happens that something changes in the bibliographic data itself. Therefore, please check carefully if all data is correct.
                    ',
    'why_do_i_have_to_review_these_activities' => 'Why do I have to review these activities?',
    'in_order_to_ensure_that_the_correct_status_and_completion_date_is_always_in' => '
                    In order to ensure that the correct status and completion date is always indicated for activities with a status, OSIRIS will issue a warning
                    if this work is still "in preparation" although the start date is in the past or "in progress" although the completion date is in the past.
                    <b>Please check if the work has already been completed.</b>
                    If so, please enter if the work has been successfully completed or not and provide the correct completion date.
                    If the work is still "in preparation", please extend the period by entering a new expected completion date.
                    If the work is still "in progress", please extend the period by entering a new expected completion date.
                    OSIRIS will then ask you again in due course if the thesis has been successfully completed.
                    ',
    'no_warnings' => 'No warnings',
    'please_review_the_following_activities_that_were_rejected_in_the_quality_wo' => 'Please review the following activities that were rejected in the quality workflow:',
    'what_does_it_mean' => 'What does it mean?',
    'reason_for_rejection' => 'Reason for rejection',
    'edit_activity' => 'Edit activity',
    'reply' => 'Reply',
    'please_review_the_following_authorships' => 'Please review the following authorships:',
    'approve_all' => 'Approve all',
    'please_confirm_possibly_again_that_you_are_the_author_and_all_details_are_c' => 'Please confirm (possibly again) that you are the author and all details are correct: ',
    'is_this_your_activity' => 'Is this your activity?',
    'no_this_is_not_me' => 'No, this is not me',
    'status_at_this_time_point' => 'Status at this time point',
    'please_review_the_following_online_ahead_of_print_articles' => 'Please review the following <q>Online ahead of print</q> articles:',
    'this_publication_is_marked_as_online_ahead_of_print_is_it_still_not_officia' => 'This publication is marked as <q>Online ahead of print</q>. Is it still not officially published?',
    'yes_still_online_ahead_of_print_ask_again_later' => 'Yes, still <q>Online ahead of print</q> (ask again later).',
    'no_longer_online_ahead_of_print_review' => 'No longer <q>Online ahead of print</q> (Review)',
    'please_review_the_status_of_the_following_activities' => 'Please review the status of the following activities:',
    'the_activity_has_ended_but_the_status_is_still_in_progress_please_confirm_i' => 'The activity has ended, but the status is still <b>in progress</b>. Please confirm if the work has been successfully completed or not or extend the time frame.',
    'the_activity_has_officially_started_but_the_status_is_still_in_preparation' => 'The activity has officially started, but the status is still <b>in preparation</b>. Please change the status or move the time frame.',
    'ended_at_extend_until' => 'Ended at / Extend until',
    'in_preparation' => 'In preparation',
    'in_progress' => 'In progress',
    'aborted' => 'Aborted',
    'do_you_still_work_on_the_following_activities' => 'Do you still work on the following activities?',
    'yes_still_running' => 'Yes, still running',
    'no_edit' => 'No (Edit)',
    'please_have_a_look_at_the_following_projects' => 'Please have a look at the following projects:',
    'the_project' => 'The project',
    'still_has_the_status_applied_is_this_correct' => 'still has the status <q>applied</q>. Is this correct? ',
    'yes_ask_again_later' => 'Yes, ask again later',
    'has_ended_you_can_either_prolong_it_or_end_it' => 'has ended. You can either prolong it or end it:',
    'please_have_a_look_at_the_following_infrastructures' => 'Please have a look at the following infrastructures:',
    'please_update_the_statistics_of' => 'Please update the statistics of ',
    'from' => 'from ',
    'update_now' => 'Update now',
    'please_review_the_following_nagoya_protocol_submissions' => 'Please review the following Nagoya Protocol submissions:',
    'the_nagoya_protocol_compliance_for_project' => 'The Nagoya Protocol compliance for project',
    'requires_your_input' => 'requires your input.',
    'provide_input_now' => 'Provide input now',
    'view_project' => 'View project',
    'sometimes_other_scientists_or_members_of_the_institute_add_scientific_activ' => '
                    Sometimes other scientists or members of the institute add scientific activities that you were also involved in.
                    The system tries to assign them automatically, which is why they show up here in this list.
                    However, a lot can go wrong with this. For reporting purposes, for example, it is not only important that the
                    bibliographic data is correct, the users must also be correctly assigned. Therefore it is important, <b>if this
                    is you at all</b> or maybe someone with a similar name, that your <b>name is spelled correctly</b> and
                    that you were also <b>affiliated with the {{affiliation}}</b>.
                    ',
    'i_confirm_that_i_am_the_author_of_all_of_the_following_publications_and_tha' => 'I confirm that I am the author of <b>all</b> of the following publications and that my affiliation has always been the {{affiliation}}.',
    'yes_and_i_was_affiliated_to_the_affiliation' => 'Yes, and I was affiliated to the{{affiliation}}',
    'yes_but_i_was_not_affiliated_to_the_affiliation' => 'Yes, but I was not affiliated to the {{affiliation}}',
];
