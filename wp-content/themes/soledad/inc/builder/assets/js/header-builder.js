!function (e, n) {
    "use strict";
    var t = n("body");

    function a(e, n) {
        return n.substr(0, e.length) == e
    }

    function i(e) {
        var t = n(e),
            a = t.parents(".header-builder-column").data("column");
        return [t.parents(".header-builder-row").data("row"), a, t.parents(".header-builder-device").data("device")]
    }

    function o(t, a) {
        var i = e.control(t);
        i && n(i.container.find("select")).selectize()[0].selectize.setValue(a, !0);
        var o = e.instance(t);
        o && o.set(a)
    }

    function r(e, n, t, a, i) {
        o(n + "_" + e + "_" + t + "_" + a, i)
    }

    function s() {
        n(document).on("click", "[data-section^=penci]", (function () {
            !function (t) {
                var i = n(t),
                    o = i.data("section"),
                    r = "",
                    s = i.parents(".header-builder-device").data("device");
                if ("penci_header_ads" === o) r = "penci_ads_header_section";
                else if (a("penci_header_html", o)) r = "penci_header_html_section";
                else if (a("penci_header_button", o)) r = "penci_header_button_section";
                else if (a("penci_header_verticalmenu", o)) r = "penci_header_vertical_menu_section";
                else if (a("penci_header_search_icon", o)) {
                    if ("mobile" === s) return void d("penci_header_search_icon_section[penci_header_search_icon_mobile]");
                    r = o + "_section"
                } else r = o + "_section";
                var c = e.section(r);
                c && c.focus()
            }(this)
        })), n("[data-control^=penci]").on("click", (function () {
            d(this)
        }))
    }

    function d(t) {
        var a = "string" == typeof t ? t : n(t).data("control"),
            i = /([^[]+)\[([^\]]+)\]/g;
        if (a.match(i)) {
            var o = i.exec(a),
                r = e.section(o[1]);
            if (r.loaded) c(o[2]);
            else r.expand();
        } else c(a)
    }

    function c(n) {
        var t = e.control(n);
        t && t.focus()
    }

    function l() {
        t.addClass("header-builder-open")
    }

    function u() {
        t.removeClass("header-builder-open")
    }

    function h(e, t, a, i) {
        var o = n("[data-device='" + e + "'] [data-row='" + t + "'] [data-column='" + a + "'] [data-align='" + i + "']"),
            r = o.parents(".header-builder-column");
        o.parent().find("> li").removeClass("active"), o.addClass("active"), r.removeClass("left center right").addClass(i)
    }

    function _(e, t, a, i) {
        var o = n("[data-device='" + e + "'] [data-row='" + t + "'] [data-column='" + a + "'] [data-display='" + i + "']");
        o.parent().find("> li").removeClass("active"), o.addClass("active"), o.parents(".header-builder-column").removeClass("grow normal").addClass(i)
    }
    
    function flex(e, t, a, i) {
        var o = n("[data-device='" + e + "'] [data-row='" + t + "'] [data-column='" + a + "'] [data-flex='" + i + "']");
        o.parent().find("> li").removeClass("active"), o.addClass("active"), o.parents(".header-builder-column").removeClass("row column").addClass(i)
    }

    function f(e) {
        var t = n(e),
            a = t.parents(".header-builder-device").find(".header-builder-list");
        t.find(".header-element").appendTo(a)
    }

    function v(e) {
        if ((e = e.hasClass("header-builder-drop-zone") ? e : e.parents(".header-builder-drop-zone")).hasClass("header-builder-list")) return null;
        var t = [],
            a = i(e);
        e.find(".header-element").each((function () {
            t.push(n(this).data("element"))
        })), r(a[2], "penci_hb_element", a[0], a[1], t)
    }

    function p(e, t, a, i) {
        var o = n("[data-device='" + e + "'] [data-row='" + t + "'] [data-column='" + a + "']"),
            r = o.find(".header-builder-drop-zone ");
        f(o), n.each(i, (function (t, a) {
            (function (e, t) {
                return n("[data-device='" + e + "'] [data-element='" + t + "']")
            })(e, a).appendTo(r)
        }))
    }

    function m() {
        n(".header-builder-wrapper").sortable({
            handle: ".header-builder-row-drag-handle", cancel: ".header-builder-top", update: function () {
                var e;
                e = [], n("[data-device='desktop'] .header-builder-row").each((function () {
                    e.push(n(this).data("row"))
                })), o("penci_hb_arrange_bar", e)
            }, beforeStop: function (e, t) {
                var a, i = n(t.placeholder).index(),
                    can_move = '',
                    o = n(t.item).data("row");
                can_move = "mid" === o || "bottom" === o, 1 === i && can_move && (n(this).sortable("cancel"), a = "Unable to move <strong>Middle Bar & Bottom Bar</strong> above <strong>Top Bar</strong>", n(".warning-text").html(a), n(".header-builder-warning").fadeIn("fast"), n(".close-warning").on("click", (function () {
                    n(this).parents(".header-builder-warning").fadeOut("fast")
                })))
            }
        })
    }

    n((function () {
        t.on("penci-open-header-builder", l), n(".top-menu.close").on("click", (function () {
            u()
        })), n(".header-builder-open").on("click", l), n(".device-mode > div").on("click", (function () {
            n(".devices .preview-" + n(this).data("mode")).click()
        })), n(".desktop-mode li").on("click", (function () {
            var e = n(this).data("desktop-mode");
            n(".wp-full-overlay").removeClass("sticky normal").addClass(e)
        })), n(".mobile-mode li").on("click", (function () {
            var e = n(this).data("mobile-mode");
            n(".wp-full-overlay").removeClass("mobile_menu drawer").addClass(e)
        })), m(), n(".header-column-option-align li").on("click", (function (e) {
            var t = n(this);
            if (!t.hasClass("active")) {
                var a = i(this),
                    o = t.data("align");
                h(a[2], a[0], a[1], o), r(a[2], "penci_hb_align", a[0], a[1], o)
            }
        })), n(".header-column-option-display li").on("click", (function (e) {
            var t = n(this);
            if (!t.hasClass("active")) {
                var a = i(this),
                    o = t.data("display");
                _(a[2], a[0], a[1], o), r(a[2], "penci_hb_display", a[0], a[1], o)
            }
        })), n(".header-column-option-flex li").on("click", (function (e) {
            var t = n(this);
            if (!t.hasClass("active")) {
                var a = i(this),
                    o = t.data("flex");
                flex(a[2], a[0], a[1], o), r(a[2], "penci_hb_flex", a[0], a[1], o)
            }
        })), n(".header-builder-drop-zone").sortable({
            items: ".header-element",
            connectWith: ".header-builder-drop-zone",
            update: function (e, n) {
                null !== n.sender && v(n.sender), v(n.item)
            }
        }), n(".header-element-close").on("click", (function () {
            var e = n(this),
                t = e.parent(),
                a = t.parent(),
                i = e.parents(".header-builder-body").find(".header-builder-list");
            t.appendTo(i), v(a)
        })), s(), n("#customize-header-actions").append("<div class='penci-btn-header-builder'><i class='fa fa-bars'></i></div>"), n(".penci-btn-header-builder").on("click", (function () {
            t.hasClass("header-builder-open") ? u() : l()
        }))
    }))

    n('body').on('click', '.header-setting', (function (e) {
       e.preventDefault();
       var t = n(this).closest('.header-builder-column');

       t.toggleClass('active');
    }));

    // Update popover item visibility based on what's already added
    function updatePopoverItems() {
        // Get all elements currently added to columns
        var addedElements = [];
        n('.header-builder-column .header-builder-drop-zone .header-element').each(function() {
            var elementKey = n(this).data('element');
            if (elementKey && addedElements.indexOf(elementKey) === -1) {
                addedElements.push(elementKey);
            }
        });

        // Hide/show items in popover based on what's added
        n('#header-item-popover .header-element').each(function() {
            var elementKey = n(this).data('element');
            if (addedElements.indexOf(elementKey) !== -1) {
                n(this).css('display', 'none').attr('data-hidden', 'true');
            } else {
                n(this).css('display', '').removeAttr('data-hidden');
            }
        });
    }

    // Populate the shared popover with items from the current context
    function populatePopover(button) {
        var column = button.closest('.header-builder-column');
        var device = column.closest('.header-builder-device').data('device');
        var popover = n('#header-item-popover');
        var listSelector = '.header-builder-device[data-device="' + device + '"] .header-builder-list';
        var listItems = n(listSelector).find('.header-element').clone();

        // Clear and populate popover
        popover.empty();
        
        if (listItems.length > 0) {
            listItems.each(function() {
                n(this).appendTo(popover);
            });
        } else {
            n('<div class="no-elements">' + penci_hb_trans.all_items_added + '</div>').appendTo(popover);
        }
        
        // Update visibility
        updatePopoverItems();
        
        // Store the current button reference
        popover.data('current-button', button);
    }

    // Position popover to be always visible on screen
    function positionPopover(button, popover) {
        var buttonRect = button[0].getBoundingClientRect();
        var popoverHeight = popover.outerHeight();
        var viewportHeight = n(window).height();
        var viewportWidth = n(window).width();
        var padding = 10;

        // Always position above the button
        var popoverTop = buttonRect.top - popoverHeight - 5;
        
        // If not enough space above, position below
        if (popoverTop < padding) {
            popoverTop = buttonRect.bottom + 5;
        }
        
        // Constrain within viewport vertically
        popoverTop = Math.max(popoverTop, padding);
        popoverTop = Math.min(popoverTop, viewportHeight - popoverHeight - padding);

        // Position left, aligned with button
        var popoverLeft = buttonRect.left;
        
        // Keep within viewport horizontally
        var popoverWidth = 350;
        if (popoverLeft + popoverWidth > viewportWidth) {
            popoverLeft = viewportWidth - popoverWidth - padding;
        }
        popoverLeft = Math.max(popoverLeft, padding);

        popover.css({
            'top': popoverTop + 'px',
            'left': popoverLeft + 'px'
        });
    }

    // Popover item selection functionality
    n('body').on('click', '.header-column-add-item-btn', (function (e) {
        e.preventDefault();
        var button = n(this);
        var popover = n('#header-item-popover');
        var currentButton = popover.data('current-button');
        
        // If clicking the same button, toggle; if different button, just move popover
        if (currentButton && currentButton.length && currentButton[0] === button[0]) {
            // Same button clicked - toggle popover
            if (popover.hasClass('active')) {
                popover.removeClass('active');
                return;
            }
        }
        
        // Different button or popover was closed - populate and show
        populatePopover(button);
        popover.addClass('active');
        positionPopover(button, popover);
    }));

    // Handle item selection from shared popover
    n('body').on('click', '#header-item-popover .header-element:not([data-hidden])', (function (e) {
        e.preventDefault();
        var item = n(this);
        var popover = n('#header-item-popover');
        var button = popover.data('current-button');
        
        if (!button || !button.length) return;
        
        var column = button.closest('.header-builder-column');
        var dropZone = column.find('.header-builder-drop-zone');
        var device = column.closest('.header-builder-device').data('device');
        var listSelector = '.header-builder-device[data-device="' + device + '"] .header-builder-list';
        var originalList = n(listSelector);
        var elementKey = item.data('element');

        // Clone the element from original list
        var originalElement = originalList.find('[data-element="' + elementKey + '"]').first();
        
        if (originalElement.length) {
            var clonedElement = originalElement.clone();
            
            // Append to drop zone (before the button)
            clonedElement.insertBefore(dropZone.find('.header-column-add-item-btn'));
            
            // Update the customizer setting
            v(dropZone);
            
            // Update popover items visibility
            updatePopoverItems();
            
            // Close popover
            popover.removeClass('active');
        }
    }));

    // Update popover when element is removed
    n('body').on('click', '.header-element-close', (function () {
        var e = n(this);
        var t = e.parent();
        var a = t.parent();
        var device = a.closest('.header-builder-device').data('device');
        var listSelector = '.header-builder-device[data-device="' + device + '"] .header-builder-list';
        var i = n(listSelector);
        t.appendTo(i);
        v(a);
        
        // Update popover items visibility after removal
        setTimeout(updatePopoverItems, 50);
    }));

    // Close popover when clicking outside
    n('body').on('click', (function (e) {
        var target = n(e.target);
        if (!target.closest('.header-column-add-item-btn').length && 
            !target.closest('#header-item-popover').length) {
            n('#header-item-popover').removeClass('active');
        }
    }));

    // Reposition popovers on window resize
    n(window).on('resize', function() {
        var popover = n('#header-item-popover');
        if (popover.hasClass('active')) {
            var button = popover.data('current-button');
            if (button && button.length) {
                positionPopover(button, popover);
            }
        }
    });

    n('body').on('click', '.penci-builder-element-mode-btn', (function (e) {
        e.preventDefault();
        n('.header-builder').toggleClass('show-element-modes');
    }));
}(wp.customize, jQuery);
