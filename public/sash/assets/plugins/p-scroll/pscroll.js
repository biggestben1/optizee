(function($) {
    "use strict";

    // Initialize PerfectScrollbar only if elements exist
    if (typeof PerfectScrollbar !== 'undefined') {
        const sidebarEl = document.querySelector('.app-sidebar');
        if (sidebarEl) {
            const ps = new PerfectScrollbar(sidebarEl, {
                useBothWheelAxes: true,
                suppressScrollX: true,
                suppressScrollY: false,
            });
        }
        
        const headerDropdownEl = document.querySelector('.header-dropdown-list');
        if (headerDropdownEl) {
            const ps1 = new PerfectScrollbar(headerDropdownEl, {
                useBothWheelAxes: true,
                suppressScrollX: true,
                suppressScrollY: false,
            });
        }
        
        const notificationsEl = document.querySelector('.notifications-menu');
        if (notificationsEl) {
            const ps2 = new PerfectScrollbar(notificationsEl, {
                useBothWheelAxes: true,
                suppressScrollX: true,
                suppressScrollY: false,
            });
        }
        
        const messageMenuEl = document.querySelector('.message-menu-scroll');
        if (messageMenuEl) {
            const ps3 = new PerfectScrollbar(messageMenuEl, {
                useBothWheelAxes: true,
                suppressScrollX: true,
                suppressScrollY: false,
            });
        }
    }

    //P-scrolling
})(jQuery);