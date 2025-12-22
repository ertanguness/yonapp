// Sidebar submenu collapse helper
// Ensures inline styles (e.g., overflow/height) don't block nxlNavigation slideUp/slideDown.
// Safe to load globally; it only touches sidebar submenu elements.

(function ($) {
  'use strict';

  if (!$) return;

  $(function () {
    // When clicking any sidebar menu item, clear inline style on its submenu.
    // This prevents cases where a previously applied inline `style="overflow: hidden;"` keeps it expanded.
    $(document).on('click', '#side-menu .nxl-item, #side-menu .nxl-hasmenu', function () {
      $(this).children('ul.nxl-submenu').removeAttr('style');
    });

    // Also clear style when nxlNavigation toggles trigger class.
    // (Covers nested items where the click target is a child li.)
    $(document).on('click', '#side-menu li', function (e) {
      // Don't fight with link navigation; just clean up styles.
      $(this).children('ul.nxl-submenu').removeAttr('style');
    });
  });
})(window.jQuery);
