define('sellerNavigationScroll', [
    'jquery'
], function ($) {
    'use strict';

    var win = $(window),
        subMenuClass = '.wk-mp-submenu',
        fixedClassName = 'fixed',
        menu = $('.wk-mp-menu-wrapper'),
        content = $('.wk-mp-page-wrapper'),
        menuItems = $('#wk-mp-nav').children('li'),
        subMenus = menuItems.children(subMenuClass),
        winHeight,
        menuHeight = menu.height(),
        menuHeightRest = 0,
        menuScrollMax = 0,
        submenuHeight = 0,
        contentHeight,
        winTop = 0,
        winTopLast = 0,
        scrollStep = 0,
        nextTop = 0;

    /**
     * Check if menu is fixed
     * @returns {boolean}
     */
    function isMenuFixed() {
        return (menuHeight < contentHeight) && (contentHeight > winHeight);
    }

    /**
     * Check if class exist than add or do nothing
     * @param {jQuery} el
     * @param $class string
     */
    function checkAddClass(el, $class) {
        if (!el.hasClass($class)) {
            el.addClass($class);
        }
    }

    /**
     * Check if class exist than remove or do nothing
     * @param {jQuery} el
     * @param $class string
     */
    function checkRemoveClass(el, $class) {
        if (el.hasClass($class)) {
            el.removeClass($class);
        }
    }

    /**
     * Calculate and apply menu position
     */
    function positionMenu() {

        //  Spotting positions and heights
        winHeight = win.height();
        contentHeight = content.height();
        winTop = win.scrollTop();
        scrollStep = winTop - winTopLast;
        menuHeightRest = menuHeight - winTop; // is a visible menu height

        if (isMenuFixed()) { // fixed menu cases

            checkAddClass(menu, fixedClassName);

            if (menuHeight > winHeight) { // smart scroll cases

                if (winTop > winTopLast) { //  scroll down

                    menuScrollMax = menuHeight - winHeight;

                    nextTop < (menuScrollMax - scrollStep) ?
                        nextTop += scrollStep : nextTop = menuScrollMax;

                    menu.css('top', -nextTop);

                } else if (winTop <= winTopLast) { // scroll up

                    nextTop > -scrollStep ?
                        nextTop += scrollStep : nextTop = 0;

                    menu.css('top', -nextTop);

                }

            }

        } else { // static menu cases
            checkRemoveClass(menu, fixedClassName);
        }

        //  Save previous window scrollTop
        winTopLast = winTop;

    }

    positionMenu(); // page start calculation

    //  Change position on scroll
    win.on('scroll', function () {
        positionMenu();
    });

    win.on('resize', function () {

        winHeight = win.height();

        //  Reset position if fixed and out of smart scroll
        if ((menuHeight < contentHeight) && (menuHeight <= winHeight)) {
            menu.removeAttr('style');
            menuItems.off();
        }

    });

    //  Add event to menuItems to check submenu overlap
    menuItems.on('click', function (e) {

        var submenu = $(this).children(subMenuClass),
            delta,
            logo = $('.wk-mp-logo')[0].offsetHeight;

        submenuHeight = submenu.height();

        if (submenuHeight > menuHeight && menuHeight + logo > winHeight) {
            menu.height(submenuHeight - logo);
            delta = -menu.position().top;
            window.scrollTo(0, 0);
            positionMenu();
            window.scrollTo(0, delta);
            positionMenu();
            menuHeight = submenuHeight;
        }
    });

});

define([
    'jquery',
    'jquery/ui',
    'sellerNavigationScroll'
], function ($) {
    'use strict';

    $.widget('mage.sellerNavigation', {
        options: {
            selectors: {
                menu: '#wk-mp-nav',
                currentItem: '.current',
                topLevelItem: '.level-0',
                topLevelHref: '> a',
                subMenu: '> .wk-mp-submenu',
                closeSubmenuBtn: '[data-role="wk-mp-close-submenu"]'
            },
            overlayTmpl: '<div class="wk-mp-menu-overlay"></div>'
        },

        _create: function () {
            var selectors = this.options.selectors;

            this.menu = this.element;
            this.menuLinks = $(selectors.topLevelHref, selectors.topLevelItem);
            this.closeActions = $(selectors.closeSubmenuBtn);

            this._initOverlay()
                ._bind();
        },

        _initOverlay: function () {
            this.overlay = $(this.options.overlayTmpl).appendTo('body').hide(0);

            return this;
        },

        _bind: function () {
            var focus = this._focus.bind(this),
                open = this._open.bind(this),
                blur = this._blur.bind(this),
                keyboard = this._keyboard.bind(this);

            this.menuLinks
                .on('focus', focus)
                .on('click', open);

            this.menuLinks.last().on('blur', blur);

            this.closeActions.on('keydown', keyboard);
        },


        /**
         * Remove active class from current menu item
         * Turn back active class to current page menu item
         */
        _blur: function (e) {
            var selectors = this.options.selectors,
                menuItem = $(e.target).closest(selectors.topLevelItem),
                currentItem = $(selectors.menu).find(selectors.currentItem);

            menuItem.removeClass('active');
            currentItem.addClass('active');
        },

        /**
         * Add focus to active menu item
         */
        _keyboard: function (e) {
            var selectors = this.options.selectors,
                menuItem = $(e.target).closest(selectors.topLevelItem);

            if (e.which === 13) {
                this._close(e);
                $(selectors.topLevelHref, menuItem).focus();
            }
        },

        /**
         * Toggle active state on focus
         */
        _focus: function (e) {
            var selectors = this.options.selectors,
                menuItem = $(e.target).closest(selectors.topLevelItem);

            menuItem.addClass('active')
                .siblings(selectors.topLevelItem)
                .removeClass('active');
        },

        _closeSubmenu: function (e) {
            var selectors = this.options.selectors,
                currentItem = $(selectors.menu).find(selectors.currentItem);

            this._close(e);

            currentItem.addClass('active');
        },

        _open: function (e) {
            var selectors = this.options.selectors,
                menuItemSelector = selectors.topLevelItem,
                menuItem = $(e.target).closest(menuItemSelector),
                subMenu = $(selectors.subMenu, menuItem),
                close = this._closeSubmenu.bind(this),
                closeBtn = subMenu.find(selectors.closeSubmenuBtn);


            if (subMenu.length) {
                e.preventDefault();
            }

            menuItem.addClass('show')
                .siblings(menuItemSelector)
                .removeClass('show');

            subMenu.attr('aria-expanded', 'true');

            //subMenu.css('height', subMenu.find('ul.menu')[0].height() + subMenu.find('strong.submenu-title').height());

            closeBtn.on('click', close);

            this.overlay.show(0).on('click', close);
            this.menuLinks.last().off('blur');
        },

        _close: function (e) {
            var selectors = this.options.selectors,
                menuItem = this.menu.find(selectors.topLevelItem + '.show'),
                subMenu = $(selectors.subMenu, menuItem),
                closeBtn = subMenu.find(selectors.closeSubmenuBtn),
                blur = this._blur.bind(this);

            e.preventDefault();

            this.overlay.hide(0).off('click');

            this.menuLinks.last().on('blur', blur);

            closeBtn.off('click');

            subMenu.attr('aria-expanded', 'false');

            menuItem.removeClass('show active');
        }
    });

    return $.mage.sellerNavigation;
});