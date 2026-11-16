<?php
$linkedContents = Initiatives::linkedContents($osiris, $Settings, $association);
$contentSections = [
    'publications' => ['common.publications', 'ph-books', 'activities'],
    'activities' => ['common.activities', 'ph-folders', 'activities'],
    'projects' => ['common.projects', 'ph-tree-structure', 'projects'],
    'proposals' => ['common.project_proposals', 'ph-file-text', 'proposals'],
    'events' => ['common.events', 'ph-calendar-dots', 'conferences'],
    'infrastructures' => ['common.infrastructures', 'ph-cube-transparent', 'infrastructures'],
    'deadlines' => ['common.deadlines', 'ph-flag-pennant', 'deadlines'],
];
?>
