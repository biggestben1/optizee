(function($) {
    "use strict";

    // Initialize PerfectScrollbar only if element exists
    if (typeof PerfectScrollbar !== 'undefined') {
        const sidebarRightEl = document.querySelector('.sidebar-right');
        if (sidebarRightEl) {
            const ps11 = new PerfectScrollbar(sidebarRightEl, {
                useBothWheelAxes: true,
                suppressScrollX: true,
            });
        }
    }

})(jQuery);