    <!--! ================================================================ !-->
    <!--! Footer Script !-->
    <!--! ================================================================ !-->
    <!--! BEGIN: Vendors JS !-->
<?php $assetBasePath = $calendarBasePath ?? ''; ?>
    <script src="<?php echo $assetBasePath; ?>/assets/vendors/js/vendors.min.js"></script>

    <script src="<?php echo $assetBasePath; ?>/assets/vendors/js/tui-code-snippet.min.js"></script>
    <script src="<?php echo $assetBasePath; ?>/assets/vendors/js/tui-time-picker.min.js"></script>
    <script src="<?php echo $assetBasePath; ?>/assets/vendors/js/tui-date-picker.min.js"></script>
    <script src="<?php echo $assetBasePath; ?>/assets/vendors/js/moment.min.js"></script>
    <script src="<?php echo $assetBasePath; ?>/assets/vendors/js/tui-calendar.min.js"></script>
<?php echo (isset($script) ? $script : '') ?>
    <script src="<?php echo $assetBasePath; ?>/assets/js/calendar-events.js"></script>