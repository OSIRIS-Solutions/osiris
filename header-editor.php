<!-- Editor scripts -->
<!-- jQuery UI for Drag & Drop -->
<script src="<?= ROOTPATH ?>/js/jquery-ui.min.js"></script>
<!-- Multiselect plugin for jQuery for improved usability -->
<script src="<?= ROOTPATH ?>/js/jquery.multi-select.min.js"></script>
<!-- Moment.js for date manipulation -->
<script src="<?= ROOTPATH ?>/js/moment.min.js"></script>
<!-- Quill for rich text editing -->
<script src="<?= ROOTPATH ?>/js/quill.min.js?v=<?=OSIRIS_BUILD?>"></script>
<!-- Selectize for enhanced select inputs -->
<script src="<?= ROOTPATH ?>/js/selectize.min.js?v=<?=OSIRIS_BUILD?>"></script>
<!-- Custom styles for the header editor -->
<link rel="stylesheet" href="<?= ROOTPATH ?>/css/selectize.css?v=<?=OSIRIS_BUILD?>">

<style>
    /* Style for the drag handle */
    .handle {
        cursor: move;
    }

    tr.ui-sortable-helper {
        display: table;
        background: #f9f9f9;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    tr.ui-sortable-helper td {
        background: #f9f9f9;
    }
</style>