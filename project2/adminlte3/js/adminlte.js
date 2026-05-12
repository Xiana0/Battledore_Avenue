/*!
 * AdminLTE v3.2.2 (https://adminlte.io)
 * Copyright 2014-2026 Colorlib <https://colorlib.com>
 * Licensed under MIT (https://github.com/ColorlibHQ/AdminLTE/blob/master/LICENSE)
 */
(function (global, factory) {
  typeof exports === 'object' && typeof module !== 'undefined' ? factory(exports, require('jquery')) :
  typeof define === 'function' && define.amd ? define(['exports', 'jquery'], factory) :
  (global = typeof globalThis !== 'undefined' ? globalThis : global || self, factory(global.adminlte = {}, global.jQuery));
})(this, (function (exports, $) { 'use strict';

  /**
   * --------------------------------------------
   * AdminLTE CardRefresh.js
   * License MIT
   * --------------------------------------------
   */


  /**
   * Constants
   * ====================================================
   */

  var NAME$e = 'CardRefresh';
  var DATA_KEY$e = 'lte.cardrefresh';
  var EVENT_KEY$7 = "." + DATA_KEY$e;
  var JQUERY_NO_CONFLICT$e = $.fn[NAME$e];
  var EVENT_LOADED = "loaded" + EVENT_KEY$7;
  var EVENT_LOAD_SUCCEEDED = "load.succeeded" + EVENT_KEY$7; //***
  var EVENT_LOAD_FAILED = "load.failed" + EVENT_KEY$7; //***
  var EVENT_OVERLAY_ADDED = "overlay.added" + EVENT_KEY$7;
  var EVENT_OVERLAY_REMOVED = "overlay.removed" + EVENT_KEY$7;
  var CLASS_NAME_CARD$1 = 'card';
  var SELECTOR_CARD$1 = "." + CLASS_NAME_CARD$1;
  var SELECTOR_DATA_REFRESH = '[data-card-widget="card-refresh"]';
  var Default$c = {
    source: '',
    sourceSelector: '',
    params: {},
    trigger: SELECTOR_DATA_REFRESH,
    content: '.card-body',
    loadInContent: true,
    loadOnInit: true,
    loadErrorTemplate: true,
    responseType: '',
    //*** overlayTemplate: '<div class="overlay"><i class="fa-solid fa-2x fa-rotate fa-spin"></i></div>',
    errorTemplate: '<span class="text-danger"></span>',
    onLoadStart: function onLoadStart() {},
    onLoadDone: function onLoadDone(response) {
      return response;
    },
    onLoadFail: function onLoadFail(_jqXHR, _textStatus, _errorThrown) {},
    ajaxSettings: {} //***
  };
  var CardRefresh = /*#__PURE__*/function () {
    //***

    function CardRefresh(element, settings) {
      var _this$_settings$overl;
      this._element = element;
      this._parent = element.parents(SELECTOR_CARD$1).first();
      this._settings = $.extend({}, Default$c, settings);
      this._overlay = $((_this$_settings$overl = this._settings.overlayTemplate) != null ? _this$_settings$overl : this.constructor.overlayTemplate); //***

      if (element.hasClass(CLASS_NAME_CARD$1)) {
        this._parent = element;
      }
      if (this._settings.source === '') {
        throw new Error('Source url was not defined. Please specify a url in your CardRefresh source option.');
      }
    }
    var _proto = CardRefresh.prototype;
    _proto.load = function load() {
      var _this = this;
      this._addOverlay();
      this._settings.onLoadStart.call($(this));
      $.ajax(this._settings.source, $.extend(true, {
        data: this._settings.params,
        success: function success(response) {
          //***
          if (_this._settings.loadInContent && typeof response == 'string') {
            //***
            if (_this._settings.sourceSelector !== '') {
              response = $(response).find(_this._settings.sourceSelector).html();
            }
            _this._parent.find(_this._settings.content).html(response);
          }
          _this._settings.onLoadDone.call($(_this), response);
          _this._removeOverlay();
          $(_this._element).trigger($.Event(EVENT_LOAD_SUCCEEDED), [response]); //***
        },
        dataType: this._settings.responseType !== '' && this._settings.responseType
      }, this._settings.ajaxSettings)) //***
      .fail(function (jqXHR, textStatus, errorThrown) {
        _this._removeOverlay();
        if (_this._settings.loadErrorTemplate) {
          var msg = $(_this._settings.errorTemplate).text(errorThrown);
          _this._parent.find(_this._settings.content).empty().append(msg);
        }
        _this._settings.onLoadFail.call($(_this), jqXHR, textStatus, errorThrown);
        $(_this._element).trigger($.Event(EVENT_LOAD_FAILED), [jqXHR, textStatus, errorThrown]); //***
      });
      $(this._element).trigger($.Event(EVENT_LOADED));
    };
    _proto.dispose = function dispose() {
      //***
      $.removeData(this._element, DATA_KEY$e);
      this._element = null;
    };
    _proto._addOverlay = function _addOverlay() {
      this._parent.append(this._overlay);
      $(this._element).trigger($.Event(EVENT_OVERLAY_ADDED));
    };
    _proto._removeOverlay = function _removeOverlay() {
      this._parent.find(this._overlay).remove();
      $(this._element).trigger($.Event(EVENT_OVERLAY_REMOVED));
    }

    // Private
    ;
    _proto._init = function _init() {
      var _this2 = this;
      $(this).find(this._settings.trigger).on('click', function () {
        _this2.load();
      });
      if (this._settings.loadOnInit) {
        this.load();
      }
    }

    // Static
    ;
    CardRefresh._jQueryInterface = function _jQueryInterface(config) {
      return this.each(function () {
        var data = $(this).data(DATA_KEY$e);
        var _config = $.extend({}, Default$c, config !== null && typeof config === 'object' ? config : $(this).data());
        if (!data) {
          data = new CardRefresh($(this), _config);
          $(this).data(DATA_KEY$e, data);
          data._init();
        }
        if (typeof config === 'string') {
          var _data$config, _data;
          //***
          (_data$config = (_data = data)[config]) == null || _data$config.call(_data);
        }
      });
    };
    return CardRefresh;
  }();
  /**
   * Data API
   * ====================================================
   */
  CardRefresh.overlayTemplate = void 0;
  $(document).on('click', SELECTOR_DATA_REFRESH, function (event) {
    if (event) {
      event.preventDefault();
    }
    CardRefresh._jQueryInterface.call($(this), 'load');
  });
  $(function () {
    return CardRefresh._jQueryInterface.call($(SELECTOR_DATA_REFRESH));
  }); //***

  /**
   * jQuery API
   * ====================================================
   */

  $.fn[NAME$e] = CardRefresh._jQueryInterface;
  $.fn[NAME$e].Constructor = CardRefresh;
  $.fn[NAME$e].noConflict = function () {
    $.fn[NAME$e] = JQUERY_NO_CONFLICT$e;
    return CardRefresh._jQueryInterface;
  };

  /**
   * --------------------------------------------
   * AdminLTE CardWidget.js
   * License MIT
   * --------------------------------------------
   */


  /**
   * Constants
   * ====================================================
   */

  var NAME$d = 'CardWidget';
  var DATA_KEY$d = 'lte.cardwidget';
  var EVENT_KEY$6 = "." + DATA_KEY$d;
  var JQUERY_NO_CONFLICT$d = $.fn[NAME$d];
  var EVENT_EXPANDED$3 = "expanded" + EVENT_KEY$6;
  var EVENT_COLLAPSED$4 = "collapsed" + EVENT_KEY$6;
  var EVENT_MAXIMIZED = "maximized" + EVENT_KEY$6;
  var EVENT_MINIMIZED = "minimized" + EVENT_KEY$6;
  var EVENT_REMOVED$1 = "removed" + EVENT_KEY$6;
  var CLASS_NAME_CARD = 'card';
  var CLASS_NAME_COLLAPSED$1 = 'collapsed-card';
  var CLASS_NAME_COLLAPSING = 'collapsing-card';
  var CLASS_NAME_EXPANDING = 'expanding-card';
  var CLASS_NAME_WAS_COLLAPSED = 'was-collapsed';
  var CLASS_NAME_MAXIMIZED = 'maximized-card';
  var SELECTOR_DATA_REMOVE = '[data-card-widget="remove"]';
  var SELECTOR_DATA_COLLAPSE = '[data-card-widget="collapse"]';
  var SELECTOR_DATA_MAXIMIZE = '[data-card-widget="maximize"]';
  var SELECTOR_CARD = "." + CLASS_NAME_CARD;
  var SELECTOR_CARD_HEADER = '.card-header';
  var SELECTOR_CARD_BODY = '.card-body';
  var SELECTOR_CARD_FOOTER = '.card-footer';
  var Default$b = {
    animationSpeed: 'normal',
    collapseTrigger: SELECTOR_DATA_COLLAPSE,
    removeTrigger: SELECTOR_DATA_REMOVE,
    maximizeTrigger: SELECTOR_DATA_MAXIMIZE,
    collapseIcon: 'fa-minus',
    expandIcon: 'fa-plus',
    maximizeIcon: 'fa-maximize',
    //***
    minimizeIcon: 'fa-minimize' //***
  };
  var CardWidget = /*#__PURE__*/function () {
    function CardWidget(element, settings) {
      this._element = element;
      this._parent = element.parents(SELECTOR_CARD).first();
      if (element.hasClass(CLASS_NAME_CARD)) {
        this._parent = element;
      }
      this._settings = $.extend({}, Default$b, settings);
    }
    var _proto = CardWidget.prototype;
    _proto.collapse = function collapse() {
      var _this = this;
      this._parent.addClass(CLASS_NAME_COLLAPSING).children(SELECTOR_CARD_BODY + ", " + SELECTOR_CARD_FOOTER).slideUp(this._settings.animationSpeed, function () {
        _this._parent.addClass(CLASS_NAME_COLLAPSED$1).removeClass(CLASS_NAME_COLLAPSING);
      });
      this._parent.find("> " + SELECTOR_CARD_HEADER + " " + this._settings.collapseTrigger + " ." + this._settings.collapseIcon).addClass(this._settings.expandIcon).removeClass(this._settings.collapseIcon);
      this._element.trigger($.Event(EVENT_COLLAPSED$4), this._parent);
    };
    _proto.expand = function expand() {
      var _this2 = this;
      this._parent.addClass(CLASS_NAME_EXPANDING).children(SELECTOR_CARD_BODY + ", " + SELECTOR_CARD_FOOTER).slideDown(this._settings.animationSpeed, function () {
        _this2._parent.removeClass(CLASS_NAME_COLLAPSED$1).removeClass(CLASS_NAME_EXPANDING);
      });
      this._parent.find("> " + SELECTOR_CARD_HEADER + " " + this._settings.collapseTrigger + " ." + this._settings.expandIcon).addClass(this._settings.collapseIcon).removeClass(this._settings.expandIcon);
      this._element.trigger($.Event(EVENT_EXPANDED$3), this._parent);
    };
    _proto.remove = function remove() {
      this._parent.slideUp();
      this._element.trigger($.Event(EVENT_REMOVED$1), this._parent);
    };
    _proto.toggle = function toggle() {
      if (this._parent.hasClass(CLASS_NAME_COLLAPSED$1)) {
        this.expand();
        return;
      }
      this.collapse();
    };
    _proto.maximize = function maximize() {
      this._parent.find(this._settings.maximizeTrigger + " ." + this._settings.maximizeIcon).addClass(this._settings.minimizeIcon).removeClass(this._settings.maximizeIcon);
      this._parent.css({
        height: this._parent.height(),
        width: this._parent.width(),
        position: 'fixed',
        transition: 'all .15s'
      }).delay(150).queue(function () {
        var $element = $(this);
        $element.addClass(CLASS_NAME_MAXIMIZED);
        $('html').addClass(CLASS_NAME_MAXIMIZED);
        if ($element.hasClass(CLASS_NAME_COLLAPSED$1)) {
          $element.addClass(CLASS_NAME_WAS_COLLAPSED);
        }
        $element.dequeue();
      });
      this._element.trigger($.Event(EVENT_MAXIMIZED), this._parent);
    };
    _proto.minimize = function minimize() {
      this._parent.find(this._settings.maximizeTrigger + " ." + this._settings.minimizeIcon).addClass(this._settings.maximizeIcon).removeClass(this._settings.minimizeIcon);
      this._parent.css('cssText', "height: " + this._parent[0].style.height + " !important; width: " + this._parent[0].style.width + " !important; transition: all .15s;").delay(10).queue(function () {
        var $element = $(this);
        $element.removeClass(CLASS_NAME_MAXIMIZED);
        $('html').removeClass(CLASS_NAME_MAXIMIZED);
        $element.css({
          height: 'auto',
          //*** 'inherit',
          width: 'auto' //*** 'inherit',
        });
        if ($element.hasClass(CLASS_NAME_WAS_COLLAPSED)) {
          $element.removeClass(CLASS_NAME_WAS_COLLAPSED);
        }
        $element.dequeue();
      });
      this._element.trigger($.Event(EVENT_MINIMIZED), this._parent);
    };
    _proto.toggleMaximize = function toggleMaximize() {
      if (this._parent.hasClass(CLASS_NAME_MAXIMIZED)) {
        this.minimize();
        return;
      }
      this.maximize();
    }

    // Private
    ;
    _proto._init = function _init() {
      var _this3 = this;
      //***
      var $parent = $(this._parent);
      $parent.find(this._settings.collapseTrigger).on('click', function () {
        _this3.toggle();
      });
      $parent.find(this._settings.maximizeTrigger).on('click', function () {
        _this3.toggleMaximize();
      });
      $parent.find(this._settings.removeTrigger).on('click', function () {
        _this3.remove();
      });
    }

    // Static
    ;
    CardWidget._jQueryInterface = function _jQueryInterface(config) {
      var data = $(this).data(DATA_KEY$d);
      var _config = $.extend({}, Default$b, $(this).data());
      if (!data) {
        data = new CardWidget($(this), _config);
        $(this).data(DATA_KEY$d, data);
        data._init(); //***
      }
      if (typeof config === 'string') {
        var _data$config, _data;
        //***
        (_data$config = (_data = data)[config]) == null || _data$config.call(_data);
      }
    };
    return CardWidget;
  }();
  /**
   * Data API
   * ====================================================
   */
  $(document).on('click', SELECTOR_DATA_COLLAPSE, function (event) {
    if (event) {
      event.preventDefault();
    }
    CardWidget._jQueryInterface.call($(this), 'toggle');
  });
  $(document).on('click', SELECTOR_DATA_REMOVE, function (event) {
    if (event) {
      event.preventDefault();
    }
    CardWidget._jQueryInterface.call($(this), 'remove');
  });
  $(document).on('click', SELECTOR_DATA_MAXIMIZE, function (event) {
    if (event) {
      event.preventDefault();
    }
    CardWidget._jQueryInterface.call($(this), 'toggleMaximize');
  });

  /**
   * jQuery API
   * ====================================================
   */

  $.fn[NAME$d] = CardWidget._jQueryInterface;
  $.fn[NAME$d].Constructor = CardWidget;
  $.fn[NAME$d].noConflict = function () {
    $.fn[NAME$d] = JQUERY_NO_CONFLICT$d;
    return CardWidget._jQueryInterface;
  };

  /**
   * --------------------------------------------
   * AdminLTE ControlSidebar.js
   * License MIT
   * --------------------------------------------
   */


  /**
   * Constants
   * ====================================================
   */

  var NAME$c = 'ControlSidebar';
  var DATA_KEY$c = 'lte.controlsidebar';
  var EVENT_KEY$5 = "." + DATA_KEY$c;
  var JQUERY_NO_CONFLICT$c = $.fn[NAME$c];
  var EVENT_COLLAPSED$3 = "collapsed" + EVENT_KEY$5;
  var EVENT_COLLAPSED_DONE$1 = "collapsed-done" + EVENT_KEY$5;
  var EVENT_EXPANDED$2 = "expanded" + EVENT_KEY$5;
  var SELECTOR_CONTROL_SIDEBAR = '.control-sidebar';
  var SELECTOR_CONTROL_SIDEBAR_CONTENT$1 = '.control-sidebar-content';
  var SELECTOR_DATA_TOGGLE$4 = '[data-widget="control-sidebar"]';
  var SELECTOR_HEADER$1 = '.main-header';
  var SELECTOR_FOOTER$1 = '.main-footer';
  var CLASS_NAME_CONTROL_SIDEBAR_ANIMATE = 'control-sidebar-animate';
  var CLASS_NAME_CONTROL_SIDEBAR_OPEN$1 = 'control-sidebar-open';
  var CLASS_NAME_CONTROL_SIDEBAR_SLIDE = 'control-sidebar-slide-open';
  var CLASS_NAME_LAYOUT_FIXED$1 = 'layout-fixed';
  var CLASS_NAME_NAVBAR_FIXED = 'layout-navbar-fixed';
  var CLASS_NAME_NAVBAR_SM_FIXED = 'layout-sm-navbar-fixed';
  var CLASS_NAME_NAVBAR_MD_FIXED = 'layout-md-navbar-fixed';
  var CLASS_NAME_NAVBAR_LG_FIXED = 'layout-lg-navbar-fixed';
  var CLASS_NAME_NAVBAR_XL_FIXED = 'layout-xl-navbar-fixed';
  var CLASS_NAME_FOOTER_FIXED = 'layout-footer-fixed';
  var CLASS_NAME_FOOTER_SM_FIXED = 'layout-sm-footer-fixed';
  var CLASS_NAME_FOOTER_MD_FIXED = 'layout-md-footer-fixed';
  var CLASS_NAME_FOOTER_LG_FIXED = 'layout-lg-footer-fixed';
  var CLASS_NAME_FOOTER_XL_FIXED = 'layout-xl-footer-fixed';
  var Default$a = {
    controlsidebarSlide: true,
    // scrollbarTheme: 'os-theme-light', //***
    // scrollbarAutoHide: 'l',
    target: SELECTOR_CONTROL_SIDEBAR,
    animationSpeed: 300
  };

  /**
   * Class Definition
   * ====================================================
   */
  var ControlSidebar = /*#__PURE__*/function () {
    function ControlSidebar(element, config) {
      this._element = element;
      this._config = config;
    }

    // Public
    var _proto = ControlSidebar.prototype;
    _proto.collapse = function collapse() {
      var _this = this;
      var $body = $('body');
      var $html = $('html');

      // Show the control sidebar
      if (this._config.controlsidebarSlide) {
        $html.addClass(CLASS_NAME_CONTROL_SIDEBAR_ANIMATE);
        $body.removeClass(CLASS_NAME_CONTROL_SIDEBAR_SLIDE).delay(300).queue(function () {
          $(SELECTOR_CONTROL_SIDEBAR).hide();
          $html.removeClass(CLASS_NAME_CONTROL_SIDEBAR_ANIMATE);
          $(this).dequeue();
        });
      } else {
        $body.removeClass(CLASS_NAME_CONTROL_SIDEBAR_OPEN$1);
      }
      $(this._element).trigger($.Event(EVENT_COLLAPSED$3));
      setTimeout(function () {
        $(_this._element).trigger($.Event(EVENT_COLLAPSED_DONE$1));
      }, this._config.animationSpeed);
    };
    _proto.show = function show(toggle) {
      if (toggle === void 0) {
        toggle = false;
      }
      var $body = $('body');
      var $html = $('html');
      if (toggle) {
        $(SELECTOR_CONTROL_SIDEBAR).hide();
      }

      // Collapse the control sidebar
      if (this._config.controlsidebarSlide) {
        $html.addClass(CLASS_NAME_CONTROL_SIDEBAR_ANIMATE);
        $(this._config.target).show().delay(10).queue(function () {
          $body.addClass(CLASS_NAME_CONTROL_SIDEBAR_SLIDE).delay(300).queue(function () {
            $html.removeClass(CLASS_NAME_CONTROL_SIDEBAR_ANIMATE);
            $(this).dequeue();
          });
          $(this).dequeue();
        });
      } else {
        $body.addClass(CLASS_NAME_CONTROL_SIDEBAR_OPEN$1);
      }
      this._fixHeight();
      this._fixScrollHeight();
      $(this._element).trigger($.Event(EVENT_EXPANDED$2));
    };
    _proto.toggle = function toggle() {
      var $body = $('body');
      var target = this._config.target;
      var notVisible = !$(target).is(':visible');
      var shouldClose = $body.hasClass(CLASS_NAME_CONTROL_SIDEBAR_OPEN$1) || $body.hasClass(CLASS_NAME_CONTROL_SIDEBAR_SLIDE);
      var shouldToggle = notVisible && ($body.hasClass(CLASS_NAME_CONTROL_SIDEBAR_OPEN$1) || $body.hasClass(CLASS_NAME_CONTROL_SIDEBAR_SLIDE));
      if (notVisible || shouldToggle) {
        // Open the control sidebar
        this.show(notVisible);
      } else if (shouldClose) {
        // Close the control sidebar
        this.collapse();
      }
    }

    // Private
    ;
    _proto._init = function _init() {
      var _this2 = this;
      var $body = $('body');
      var shouldNotHideAll = $body.hasClass(CLASS_NAME_CONTROL_SIDEBAR_OPEN$1) || $body.hasClass(CLASS_NAME_CONTROL_SIDEBAR_SLIDE);
      if (shouldNotHideAll) {
        $(SELECTOR_CONTROL_SIDEBAR).not(this._config.target).hide();
        $(this._config.target).css('display', 'block');
      } else {
        $(SELECTOR_CONTROL_SIDEBAR).hide();
      }
      this._fixHeight();
      this._fixScrollHeight();
      $(window).resize(function () {
        _this2._fixHeight();
        _this2._fixScrollHeight();
      });
      $(window).scroll(function () {
        var $body = $('body');
        var shouldFixHeight = $body.hasClass(CLASS_NAME_CONTROL_SIDEBAR_OPEN$1) || $body.hasClass(CLASS_NAME_CONTROL_SIDEBAR_SLIDE);
        if (shouldFixHeight) {
          _this2._fixScrollHeight();
        }
      });
    };
    _proto._isNavbarFixed = function _isNavbarFixed() {
      var $body = $('body');
      return $body.hasClass(CLASS_NAME_NAVBAR_FIXED) || $body.hasClass(CLASS_NAME_NAVBAR_SM_FIXED) || $body.hasClass(CLASS_NAME_NAVBAR_MD_FIXED) || $body.hasClass(CLASS_NAME_NAVBAR_LG_FIXED) || $body.hasClass(CLASS_NAME_NAVBAR_XL_FIXED);
    };
    _proto._isFooterFixed = function _isFooterFixed() {
      var $body = $('body');
      return $body.hasClass(CLASS_NAME_FOOTER_FIXED) || $body.hasClass(CLASS_NAME_FOOTER_SM_FIXED) || $body.hasClass(CLASS_NAME_FOOTER_MD_FIXED) || $body.hasClass(CLASS_NAME_FOOTER_LG_FIXED) || $body.hasClass(CLASS_NAME_FOOTER_XL_FIXED);
    };
    _proto._fixScrollHeight = function _fixScrollHeight() {
      var $body = $('body');
      var $controlSidebar = $(this._config.target);
      if (!$body.hasClass(CLASS_NAME_LAYOUT_FIXED$1)) {
        return;
      }
      var heights = {
        scroll: $(document).height(),
        window: $(window).height(),
        header: $(SELECTOR_HEADER$1).outerHeight(),
        footer: $(SELECTOR_FOOTER$1).outerHeight()
      };
      var positions = {
        bottom: Math.abs(heights.window + $(window).scrollTop() - heights.scroll),
        top: $(window).scrollTop()
      };
      var navbarFixed = this._isNavbarFixed() && $(SELECTOR_HEADER$1).css('position') === 'fixed';
      var footerFixed = this._isFooterFixed() && $(SELECTOR_FOOTER$1).css('position') === 'fixed';
      var $controlsidebarContent = $(this._config.target + ", " + this._config.target + " " + SELECTOR_CONTROL_SIDEBAR_CONTENT$1);
      if (positions.top === 0 && positions.bottom === 0) {
        $controlSidebar.css({
          bottom: heights.footer,
          top: heights.header
        });
        $controlsidebarContent.css('height', heights.window - (heights.header + heights.footer));
      } else if (positions.bottom <= heights.footer) {
        if (footerFixed === false) {
          var top = heights.header - positions.top;
          $controlSidebar.css('bottom', heights.footer - positions.bottom).css('top', top >= 0 ? top : 0);
          $controlsidebarContent.css('height', heights.window - (heights.footer - positions.bottom));
        } else {
          $controlSidebar.css('bottom', heights.footer);
        }
      } else if (positions.top <= heights.header) {
        if (navbarFixed === false) {
          $controlSidebar.css('top', heights.header - positions.top);
          $controlsidebarContent.css('height', heights.window - (heights.header - positions.top));
        } else {
          $controlSidebar.css('top', heights.header);
        }
      } else if (navbarFixed === false) {
        $controlSidebar.css('top', 0);
        $controlsidebarContent.css('height', heights.window);
      } else {
        $controlSidebar.css('top', heights.header);
      }
      if (footerFixed && navbarFixed) {
        $controlsidebarContent.css('height', '100%');
        $controlSidebar.css('height', '');
      } else if (footerFixed || navbarFixed) {
        $controlsidebarContent.css('height', '100%');
        $controlsidebarContent.css('height', '');
      }
    };
    _proto._fixHeight = function _fixHeight() {
      var $body = $('body');
      var $controlSidebar = $(this._config.target + " " + SELECTOR_CONTROL_SIDEBAR_CONTENT$1);
      if (!$body.hasClass(CLASS_NAME_LAYOUT_FIXED$1)) {
        $controlSidebar.attr('style', '');
        return;
      }
      var heights = {
        window: $(window).height(),
        header: $(SELECTOR_HEADER$1).outerHeight(),
        footer: $(SELECTOR_FOOTER$1).outerHeight()
      };
      var sidebarHeight = heights.window - heights.header;
      if (this._isFooterFixed() && $(SELECTOR_FOOTER$1).css('position') === 'fixed') {
        sidebarHeight = heights.window - heights.header - heights.footer;
      }
      $controlSidebar.css('height', sidebarHeight);
    }

    // Static
    ;
    ControlSidebar._jQueryInterface = function _jQueryInterface(config) {
      return this.each(function () {
        var data = $(this).data(DATA_KEY$c);
        var _config = $.extend({}, Default$a, config !== null && typeof config === 'object' ? config : $(this).data());
        if (!data) {
          data = new ControlSidebar($(this), _config);
          $(this).data(DATA_KEY$c, data);
          data._init();
        }
        if (typeof config === 'string') {
          var _data$config, _data;
          //***
          (_data$config = (_data = data)[config]) == null || _data$config.call(_data);
        }
      });
    };
    return ControlSidebar;
  }();
  /**
   *
   * Data Api implementation
   * ====================================================
   */
  $(document).on('click', SELECTOR_DATA_TOGGLE$4, function (event) {
    event.preventDefault();
    ControlSidebar._jQueryInterface.call($(this), 'toggle');
  });
  $(function () {
    return ControlSidebar._jQueryInterface.call($(SELECTOR_DATA_TOGGLE$4), '_init');
  }); //***

  /**
   * jQuery API
   * ====================================================
   */

  $.fn[NAME$c] = ControlSidebar._jQueryInterface;
  $.fn[NAME$c].Constructor = ControlSidebar;
  $.fn[NAME$c].noConflict = function () {
    $.fn[NAME$c] = JQUERY_NO_CONFLICT$c;
    return ControlSidebar._jQueryInterface;
  };

  /**
   * --------------------------------------------
   * AdminLTE DirectChat.js
   * License MIT
   * --------------------------------------------
   */


  /**
   * Constants
   * ====================================================
   */

  var NAME$b = 'DirectChat';
  var DATA_KEY$b = 'lte.directchat';
  var EVENT_KEY$4 = "." + DATA_KEY$b;
  var JQUERY_NO_CONFLICT$b = $.fn[NAME$b];
  var EVENT_TOGGLED = "toggled" + EVENT_KEY$4;
  var SELECTOR_DATA_TOGGLE$3 = '[data-widget="chat-pane-toggle"]';
  var SELECTOR_DIRECT_CHAT = '.direct-chat';
  var CLASS_NAME_DIRECT_CHAT_OPEN = 'direct-chat-contacts-open';

  /**
   * Class Definition
   * ====================================================
   */
  var DirectChat = /*#__PURE__*/function () {
    function DirectChat(element) {
      this._element = element;
    }
    var _proto = DirectChat.prototype;
    _proto.toggle = function toggle() {
      $(this._element).parents(SELECTOR_DIRECT_CHAT).first().toggleClass(CLASS_NAME_DIRECT_CHAT_OPEN);
      $(this._element).trigger($.Event(EVENT_TOGGLED));
    }

    // Static
    ;
    DirectChat._jQueryInterface = function _jQueryInterface(config) {
      return this.each(function () {
        var data = $(this).data(DATA_KEY$b);
        if (!data) {
          data = new DirectChat($(this));
          $(this).data(DATA_KEY$b, data);
        }
        if (typeof config === 'string') {
          var _data$config, _data;
          //***
          (_data$config = (_data = data)[config]) == null || _data$config.call(_data);
        }
      });
    };
    return DirectChat;
  }();
  /**
   *
   * Data Api implementation
   * ====================================================
   */
  $(document).on('click', SELECTOR_DATA_TOGGLE$3, function (event) {
    if (event) {
      event.preventDefault();
    }
    DirectChat._jQueryInterface.call($(this), 'toggle');
  });

  /**
   * jQuery API
   * ====================================================
   */

  $.fn[NAME$b] = DirectChat._jQueryInterface;
  $.fn[NAME$b].Constructor = DirectChat;
  $.fn[NAME$b].noConflict = function () {
    $.fn[NAME$b] = JQUERY_NO_CONFLICT$b;
    return DirectChat._jQueryInterface;
  };

  /**
   * --------------------------------------------
   * AdminLTE Dropdown.js
   * License MIT
   * --------------------------------------------
   */


  /**
   * Constants
   * ====================================================
   */

  var NAME$a = 'Dropdown';
  var DATA_KEY$a = 'lte.dropdown';
  var JQUERY_NO_CONFLICT$a = $.fn[NAME$a];
  var SELECTOR_NAVBAR = '.navbar';
  var SELECTOR_DROPDOWN_MENU = '.dropdown-menu';
  // const SELECTOR_DROPDOWN_MENU_ACTIVE = '.dropdown-menu.show'
  var SELECTOR_DROPDOWN_TOGGLE = '[data-bs-toggle="dropdown"]'; //***

  // const CLASS_NAME_DROPDOWN_RIGHT = 'dropdown-menu-right'
  // const CLASS_NAME_DROPDOWN_SUBMENU = 'dropdown-submenu'

  // TODO: this is unused; should be removed along with the extend?
  var Default$9 = {};

  /**
   * Class Definition
   * ====================================================
   */
  var Dropdown = /*#__PURE__*/function () {
    function Dropdown(element, config) {
      this._config = config;
      this._element = element;
    }

    // Public
    var _proto = Dropdown.prototype;
    _proto.toggleSubmenu = function toggleSubmenu() {
      this._element.siblings().toggleClass('show'); //***

      if (!this._element.next().hasClass('show')) {
        this._element.parents(SELECTOR_DROPDOWN_MENU).first().find('.show').removeClass('show'); //***
      }
      this._element.parents('li.nav-item.dropdown.show').on('hidden.bs.dropdown', function () {
        $('.dropdown-submenu .show').removeClass('show'); //***
      });
    }

    // fixPosition() {
    //   const $element = $(SELECTOR_DROPDOWN_MENU_ACTIVE)

    //   if ($element.length === 0) {
    //     return
    //   }

    //   if ($element.hasClass(CLASS_NAME_DROPDOWN_RIGHT)) {
    //     $element.css({
    //       left: 'inherit',
    //       right: 0
    //     })
    //   } else {
    //     $element.css({
    //       left: 0,
    //       right: 'inherit'
    //     })
    //   }

    //   const offset = $element.offset()
    //   const width = $element.width()
    //   const visiblePart = $(window).width() - offset.left

    //   if (offset.left < 0) {
    //     $element.css({
    //       left: 'inherit',
    //       right: offset.left - 5
    //     })
    //   } else if (visiblePart < width) {
    //     $element.css({
    //       left: 'inherit',
    //       right: 0
    //     })
    //   }
    // }

    // Static
    ;
    Dropdown._jQueryInterface = function _jQueryInterface(config) {
      return this.each(function () {
        var data = $(this).data(DATA_KEY$a);
        var _config = $.extend({}, Default$9, config !== null && typeof config === 'object' ? config : $(this).data());
        if (!data) {
          data = new Dropdown($(this), _config);
          $(this).data(DATA_KEY$a, data);
        }
        if (typeof config === 'string') {
          var _data$config, _data;
          //***
          (_data$config = (_data = data)[config]) == null || _data$config.call(_data);
        }
      });
    };
    return Dropdown;
  }();
  /**
   * Data API
   * ====================================================
   */
  $(SELECTOR_DROPDOWN_MENU + " " + SELECTOR_DROPDOWN_TOGGLE).on('click', function (event) {
    // Dropdown menu parent item with href //***
    var href = $(this).attr("href");
    if (event.target.tagName == "SPAN" && href && href != "#" && !href.startsWith("javascript:")) {
      window.location = href;
      return;
    }
    event.preventDefault();
    event.stopPropagation();
    Dropdown._jQueryInterface.call($(this), 'toggleSubmenu');
  });
  $(SELECTOR_NAVBAR + " " + SELECTOR_DROPDOWN_TOGGLE).on('click', function (event) {
    event.preventDefault();

    //*** if ($(event.target).parent().hasClass(CLASS_NAME_DROPDOWN_SUBMENU)) {
    //   return
    // }

    // setTimeout(function () {
    //   Dropdown._jQueryInterface.call($(this), 'fixPosition')
    // }, 1)
  });

  /**
   * jQuery API
   * ====================================================
   */

  $.fn[NAME$a] = Dropdown._jQueryInterface;
  $.fn[NAME$a].Constructor = Dropdown;
  $.fn[NAME$a].noConflict = function () {
    $.fn[NAME$a] = JQUERY_NO_CONFLICT$a;
    return Dropdown._jQueryInterface;
  };

  /**
   * --------------------------------------------
   * AdminLTE ExpandableTable.js
   * License MIT
   * --------------------------------------------
   */


  /**
    * Constants
    * ====================================================
    */

  var NAME$9 = 'ExpandableTable';
  var DATA_KEY$9 = 'lte.expandableTable';
  var EVENT_KEY$3 = "." + DATA_KEY$9;
  var JQUERY_NO_CONFLICT$9 = $.fn[NAME$9];
  var EVENT_EXPAND = "expand" + EVENT_KEY$3;
  var EVENT_COLLAPSE = "collapse" + EVENT_KEY$3;
  var EVENT_EXPANDED$1 = "expanded" + EVENT_KEY$3;
  var EVENT_COLLAPSED$2 = "collapsed" + EVENT_KEY$3;

  // const SELECTOR_TABLE = '.expandable-table'
  var SELECTOR_EXPANDABLE_BODY = '.expandable-body';
  var SELECTOR_DATA_TOGGLE$2 = '[data-widget="expandable-table"]';
  var SELECTOR_ARIA_ATTR = 'aria-expanded';

  /**
    * Class Definition
    * ====================================================
    */
  var ExpandableTable = /*#__PURE__*/function () {
    function ExpandableTable(element, options) {
      this._options = options;
      this._element = element;
    }

    // Public
    var _proto = ExpandableTable.prototype;
    _proto.toggleRow = function toggleRow(relatedTarget) {
      var $element = this._element;
      if ($element[0].nodeName !== 'TR') {
        $element = $element.parent();
        if ($element[0].nodeName !== 'TR') {
          $element = $element.parent();
        }
      }
      var time = 500;
      var $type = $element.attr(SELECTOR_ARIA_ATTR);
      var $body = $element.next(SELECTOR_EXPANDABLE_BODY).children().first().children();
      $body.stop();
      if ($type === 'true') {
        var collapseEvent = $.Event(EVENT_COLLAPSE);
        $element.trigger(collapseEvent);
        if (collapseEvent.isDefaultPrevented()) {
          return;
        }
        $body.slideUp(time, function () {
          $element.attr(SELECTOR_ARIA_ATTR, 'false');
        });
        var collapsedEvent = $.Event(EVENT_COLLAPSED$2);
        collapsedEvent.relatedTarget = relatedTarget;
        $element.trigger(collapsedEvent);
      } else if ($type === 'false') {
        var expandEvent = $.Event(EVENT_EXPAND);
        $element.trigger(expandEvent);
        if (expandEvent.isDefaultPrevented()) {
          return;
        }
        $element.attr(SELECTOR_ARIA_ATTR, 'true');
        $body.slideDown(time);
        var expandedEvent = $.Event(EVENT_EXPANDED$1);
        expandedEvent.relatedTarget = relatedTarget;
        $element.trigger(expandedEvent);
      }
    }

    // Static
    ;
    ExpandableTable._jQueryInterface = function _jQueryInterface(config, relatedTarget) {
      return this.each(function () {
        var data = $(this).data(DATA_KEY$9);
        if (!data) {
          data = new ExpandableTable($(this));
          $(this).data(DATA_KEY$9, data);
        }
        if (typeof config === 'string') {
          var _data$config, _data;
          (_data$config = (_data = data)[config]) == null || _data$config.call(_data, relatedTarget);
        }
      });
    };
    return ExpandableTable;
  }();
  /**
    * Data API
    * ====================================================
    */
  $(document).on('click', SELECTOR_DATA_TOGGLE$2, function (e) {
    ExpandableTable._jQueryInterface.call($(this), 'toggleRow', e.target);
  });

  /**
    * jQuery API
    * ====================================================
    */

  $.fn[NAME$9] = ExpandableTable._jQueryInterface;
  $.fn[NAME$9].Constructor = ExpandableTable;
  $.fn[NAME$9].noConflict = function () {
    $.fn[NAME$9] = JQUERY_NO_CONFLICT$9;
    return ExpandableTable._jQueryInterface;
  };

  /**
   * --------------------------------------------
   * AdminLTE Fullscreen.js
   * License MIT
   * --------------------------------------------
   */


  /**
   * Constants
   * ====================================================
   */

  var NAME$8 = 'Fullscreen';
  var DATA_KEY$8 = 'lte.fullscreen';
  var JQUERY_NO_CONFLICT$8 = $.fn[NAME$8];
  var SELECTOR_DATA_WIDGET$2 = '[data-widget="fullscreen"]';
  var SELECTOR_ICON = SELECTOR_DATA_WIDGET$2 + " i";
  var EVENT_FULLSCREEN_CHANGE = 'fullscreenchange'; //***

  var Default$8 = {
    minimizeIcon: 'fa-maximize',
    //***
    maximizeIcon: 'fa-minimize' //***
  };

  /**
   * Class Definition
   * ====================================================
   */
  var Fullscreen = /*#__PURE__*/function () {
    function Fullscreen(_element, _options) {
      this.element = _element;
      this.options = $.extend({}, Default$8, _options);
    }

    // Public
    var _proto = Fullscreen.prototype;
    _proto.toggle = function toggle() {
      if (document.fullscreenElement || document.mozFullScreenElement || document.webkitFullscreenElement || document.msFullscreenElement) {
        this.windowed();
      } else {
        this.fullscreen();
      }
    };
    _proto.toggleIcon = function toggleIcon() {
      if (document.fullscreenElement || document.mozFullScreenElement || document.webkitFullscreenElement || document.msFullscreenElement) {
        $(SELECTOR_ICON).removeClass(this.options.maximizeIcon).addClass(this.options.minimizeIcon);
      } else {
        $(SELECTOR_ICON).removeClass(this.options.minimizeIcon).addClass(this.options.maximizeIcon);
      }
    };
    _proto.fullscreen = function fullscreen() {
      if (document.documentElement.requestFullscreen) {
        document.documentElement.requestFullscreen();
      } else if (document.documentElement.webkitRequestFullscreen) {
        document.documentElement.webkitRequestFullscreen();
      } else if (document.documentElement.msRequestFullscreen) {
        document.documentElement.msRequestFullscreen();
      }
    };
    _proto.windowed = function windowed() {
      if (document.exitFullscreen) {
        document.exitFullscreen();
      } else if (document.webkitExitFullscreen) {
        document.webkitExitFullscreen();
      } else if (document.msExitFullscreen) {
        document.msExitFullscreen();
      }
    }

    // Static
    ;
    Fullscreen._jQueryInterface = function _jQueryInterface(config) {
      return this.each(function () {
        var data = $(this).data(DATA_KEY$8);
        var _config = $.extend({}, Default$8, config !== null && typeof config === 'object' ? config : $(this).data());
        if (!data) {
          data = new Fullscreen($(this), _config);
          $(this).data(DATA_KEY$8, data);
        }
        if (typeof config === 'string') {
          var _data$config, _data;
          //***
          (_data$config = (_data = data)[config]) == null || _data$config.call(_data);
        }
      });
    };
    return Fullscreen;
  }();
  /**
    * Data API
    * ====================================================
    */
  $(document).on('click', SELECTOR_DATA_WIDGET$2, function () {
    Fullscreen._jQueryInterface.call($(this), 'toggle');
  });
  $(document).on(EVENT_FULLSCREEN_CHANGE, function () {
    return Fullscreen._jQueryInterface.call($(SELECTOR_DATA_WIDGET$2), 'toggleIcon');
  });

  /**
   * jQuery API
   * ====================================================
   */

  $.fn[NAME$8] = Fullscreen._jQueryInterface;
  $.fn[NAME$8].Constructor = Fullscreen;
  $.fn[NAME$8].noConflict = function () {
    $.fn[NAME$8] = JQUERY_NO_CONFLICT$8;
    return Fullscreen._jQueryInterface;
  };

  function _extends() {
    return _extends = Object.assign ? Object.assign.bind() : function (n) {
      for (var e = 1; e < arguments.length; e++) {
        var t = arguments[e];
        for (var r in t) ({}).hasOwnProperty.call(t, r) && (n[r] = t[r]);
      }
      return n;
    }, _extends.apply(null, arguments);
  }

  /**
   * Constants
   * ====================================================
   */

  var NAME$7 = 'IFrame';
  var DATA_KEY$7 = 'lte.iframe';
  var JQUERY_NO_CONFLICT$7 = $.fn[NAME$7];
  var SELECTOR_DATA_TOGGLE$1 = '[data-widget="iframe"]';
  var SELECTOR_DATA_TOGGLE_CLOSE = '[data-widget="iframe-close"]';
  var SELECTOR_DATA_TOGGLE_SCROLL_LEFT = '[data-widget="iframe-scrollleft"]';
  var SELECTOR_DATA_TOGGLE_SCROLL_RIGHT = '[data-widget="iframe-scrollright"]';
  var SELECTOR_DATA_TOGGLE_FULLSCREEN = '[data-widget="iframe-fullscreen"]';
  var SELECTOR_CONTENT_WRAPPER = '.content-wrapper';
  var SELECTOR_CONTENT_IFRAME = SELECTOR_CONTENT_WRAPPER + " iframe";
  var SELECTOR_TAB_NAV = SELECTOR_CONTENT_WRAPPER + ".iframe-mode .nav";
  var SELECTOR_TAB_NAVBAR_NAV = SELECTOR_CONTENT_WRAPPER + ".iframe-mode .navbar-nav";
  var SELECTOR_TAB_NAVBAR_NAV_ITEM = SELECTOR_TAB_NAVBAR_NAV + " .nav-item";
  var SELECTOR_TAB_NAVBAR_NAV_LINK = SELECTOR_TAB_NAVBAR_NAV + " .nav-link";
  var SELECTOR_TAB_CONTENT = SELECTOR_CONTENT_WRAPPER + ".iframe-mode .tab-content";
  var SELECTOR_TAB_EMPTY = SELECTOR_TAB_CONTENT + " .tab-empty";
  var SELECTOR_TAB_LOADING = SELECTOR_TAB_CONTENT + " .tab-loading";
  var SELECTOR_TAB_PANE = SELECTOR_TAB_CONTENT + " .tab-pane";
  var SELECTOR_SIDEBAR_MENU_ITEM = '.main-sidebar .nav-item > a.nav-link';
  var SELECTOR_SIDEBAR_SEARCH_ITEM = '.sidebar-search-results .list-group-item';
  var SELECTOR_HEADER_MENU_ITEM = '.main-header .nav-item a.nav-link';
  var SELECTOR_HEADER_DROPDOWN_ITEM = '.main-header a.dropdown-item';
  var CLASS_NAME_IFRAME_MODE$1 = 'iframe-mode';
  var CLASS_NAME_FULLSCREEN_MODE = 'iframe-mode-fullscreen';
  var Default$7 = {
    onTabClick: function onTabClick(item) {
      return item;
    },
    onTabChanged: function onTabChanged(item) {
      return item;
    },
    onTabCreated: function onTabCreated(item) {
      return item;
    },
    autoIframeMode: true,
    autoItemActive: true,
    autoShowNewTab: true,
    autoDarkMode: false,
    allowDuplicates: false,
    allowReload: true,
    loadingScreen: true,
    useNavbarItems: true,
    scrollOffset: 40,
    scrollBehaviorSwap: false,
    iconMaximize: 'fa-expand',
    iconMinimize: 'fa-compress'
  };

  /**
   * Class Definition
   * ====================================================
   */
  var IFrame = /*#__PURE__*/function () {
    function IFrame(element, config) {
      this._config = config;
      this._element = element;
      this._init();
    }

    // Public
    var _proto = IFrame.prototype;
    _proto.onTabClick = function onTabClick(item) {
      this._config.onTabClick(item);
    };
    _proto.onTabChanged = function onTabChanged(item) {
      this._config.onTabChanged(item);
    };
    _proto.onTabCreated = function onTabCreated(item) {
      this._config.onTabCreated(item);
    };
    _proto.createTab = function createTab(title, link, uniqueName, autoOpen) {
      var _this = this;
      var tabId = "panel-" + uniqueName;
      var navId = "tab-" + uniqueName;
      if (this._config.allowDuplicates) {
        tabId += "-" + Math.floor(Math.random() * 1000);
        navId += "-" + Math.floor(Math.random() * 1000);
      }
      var newNavItem = "<li class=\"nav-item\" role=\"presentation\"><a href=\"#\" class=\"btn-iframe-close\" data-widget=\"iframe-close\" data-type=\"only-this\"><i class=\"fa-solid fa-xmark\"></i></a><a class=\"nav-link\" data-toggle=\"row\" id=\"" + navId + "\" href=\"#" + tabId + "\" role=\"tab\" aria-controls=\"" + tabId + "\" aria-selected=\"false\">" + title + "</a></li>";
      $(SELECTOR_TAB_NAVBAR_NAV).append(newNavItem);
      var newTabItem = "<div class=\"tab-pane fade\" id=\"" + tabId + "\" role=\"tabpanel\" aria-labelledby=\"" + navId + "\"><iframe src=\"" + link + "\"></iframe></div>";
      $(SELECTOR_TAB_CONTENT).append(newTabItem);
      if (autoOpen) {
        if (this._config.loadingScreen) {
          var $loadingScreen = $(SELECTOR_TAB_LOADING);
          if (!$loadingScreen.is(':animated')) {
            $loadingScreen.fadeIn();
          }
          $(tabId + " iframe").ready(function () {
            if (typeof _this._config.loadingScreen === 'number') {
              _this.switchTab("#" + navId);
              setTimeout(function () {
                $loadingScreen.fadeOut();
              }, _this._config.loadingScreen);
            } else {
              _this.switchTab("#" + navId);
              $loadingScreen.fadeOut();
            }
          });
        } else {
          this.switchTab("#" + navId);
        }
      }
      this.onTabCreated($("#" + navId));
    };
    _proto.openTabSidebar = function openTabSidebar(item, autoOpen) {
      if (autoOpen === void 0) {
        autoOpen = this._config.autoShowNewTab;
      }
      var $item = $(item).clone();
      if ($item.attr('href') === undefined) {
        $item = $(item).parent('a').clone();
      }
      $item.find('.right, .search-path').remove();
      var title = $item.find('p').text();
      if (title === '') {
        title = $item.text();
      }
      var link = $item.attr('href');
      if (link === '#' || link === '' || link === undefined) {
        return;
      }
      var uniqueName = decodeURI(link).replace('./', '').replace(/["#&'./:=?[\]]/gi, '-').replace(/(--)/gi, '');
      var navId = "tab-" + uniqueName;
      if (!this._config.allowDuplicates && $("#" + navId).length > 0) {
        return this.switchTab("#" + navId, this._config.allowReload);
      }
      if (!this._config.allowDuplicates && $("#" + navId).length === 0 || this._config.allowDuplicates) {
        this.createTab(title, link, uniqueName, autoOpen);
      }
    };
    _proto.switchTab = function switchTab(item, reload) {
      var _this2 = this,
        _bootstrap$Tab$getIns;
      if (reload === void 0) {
        reload = false;
      }
      var $item = $(item);
      var tabId = $item.attr('href');
      $(SELECTOR_TAB_EMPTY).hide();
      if (reload) {
        var $loadingScreen = $(SELECTOR_TAB_LOADING);
        if (this._config.loadingScreen) {
          $loadingScreen.show(0, function () {
            $(tabId + " iframe").attr('src', $(tabId + " iframe").attr('src')).ready(function () {
              if (_this2._config.loadingScreen) {
                if (typeof _this2._config.loadingScreen === 'number') {
                  setTimeout(function () {
                    $loadingScreen.fadeOut();
                  }, _this2._config.loadingScreen);
                } else {
                  $loadingScreen.fadeOut();
                }
              }
            });
          });
        } else {
          $(tabId + " iframe").attr('src', $(tabId + " iframe").attr('src'));
        }
      }
      var $tab = $(SELECTOR_TAB_NAVBAR_NAV + " .active");
      (_bootstrap$Tab$getIns = bootstrap.Tab.getInstance($tab)) == null || _bootstrap$Tab$getIns.dispose();
      $tab.removeClass('active');
      this._fixHeight();
      bootstrap.Tab.getOrCreateInstance($item).show();
      $item.parents('li').addClass('active');
      this.onTabChanged($item);
      if (this._config.autoItemActive) {
        this._setItemActive($(tabId + " iframe").attr('src'));
      }
    };
    _proto.removeActiveTab = function removeActiveTab(type, element) {
      if (type == 'all') {
        $(SELECTOR_TAB_NAVBAR_NAV_ITEM).remove();
        $(SELECTOR_TAB_PANE).remove();
        $(SELECTOR_TAB_EMPTY).show();
      } else if (type == 'all-other') {
        $(SELECTOR_TAB_NAVBAR_NAV_ITEM + ":not(.active)").remove();
        $(SELECTOR_TAB_PANE + ":not(.active)").remove();
      } else if (type == 'only-this') {
        var $navClose = $(element);
        var $navItem = $navClose.parent('.nav-item');
        var $navItemParent = $navItem.parent();
        var navItemIndex = $navItem.index();
        var tabId = $navClose.siblings('.nav-link').attr('aria-controls');
        $navItem.remove();
        $("#" + tabId).remove();
        if ($(SELECTOR_TAB_CONTENT).children().length == $(SELECTOR_TAB_EMPTY + ", " + SELECTOR_TAB_LOADING).length) {
          $(SELECTOR_TAB_EMPTY).show();
        } else {
          var prevNavItemIndex = navItemIndex - 1;
          this.switchTab($navItemParent.children().eq(prevNavItemIndex).find('a.nav-link'));
        }
      } else {
        var _$navItem = $(SELECTOR_TAB_NAVBAR_NAV_ITEM + ".active");
        var _$navItemParent = _$navItem.parent();
        var _navItemIndex = _$navItem.index();
        _$navItem.remove();
        $(SELECTOR_TAB_PANE + ".active").remove();
        if ($(SELECTOR_TAB_CONTENT).children().length == $(SELECTOR_TAB_EMPTY + ", " + SELECTOR_TAB_LOADING).length) {
          $(SELECTOR_TAB_EMPTY).show();
        } else {
          var _prevNavItemIndex = _navItemIndex - 1;
          this.switchTab(_$navItemParent.children().eq(_prevNavItemIndex).find('a.nav-link'));
        }
      }
    };
    _proto.toggleFullscreen = function toggleFullscreen() {
      if ($('body').hasClass(CLASS_NAME_FULLSCREEN_MODE)) {
        $(SELECTOR_DATA_TOGGLE_FULLSCREEN + " i").removeClass(this._config.iconMinimize).addClass(this._config.iconMaximize);
        $('body').removeClass(CLASS_NAME_FULLSCREEN_MODE);
        $(SELECTOR_TAB_EMPTY + ", " + SELECTOR_TAB_LOADING).height('100%');
        $(SELECTOR_CONTENT_WRAPPER).height('100%');
        $(SELECTOR_CONTENT_IFRAME).height('100%');
      } else {
        $(SELECTOR_DATA_TOGGLE_FULLSCREEN + " i").removeClass(this._config.iconMaximize).addClass(this._config.iconMinimize);
        $('body').addClass(CLASS_NAME_FULLSCREEN_MODE);
      }
      $(window).trigger('resize');
      this._fixHeight(true);
    }

    // Private
    ;
    _proto._init = function _init() {
      var usingDefTab = $(SELECTOR_TAB_CONTENT).children().length > 2;
      this._setupListeners();
      this._fixHeight(true);
      if (usingDefTab) {
        var $el = $("" + SELECTOR_TAB_PANE).first();
        var uniqueName = $el.attr('id').replace('panel-', '');
        var navId = "#tab-" + uniqueName;
        this.switchTab(navId, true);
      }
    };
    _proto._initFrameElement = function _initFrameElement() {
      var _this$_config;
      if (window.frameElement && (_this$_config = this._config) != null && _this$_config.autoIframeMode) {
        //***
        var $body = $('body');
        $body.addClass(CLASS_NAME_IFRAME_MODE$1);
        if (this._config.autoDarkMode) {
          $('html').data('bsTheme', 'dark'); //***
        }
      }
    };
    _proto._navScroll = function _navScroll(offset) {
      var leftPos = $(SELECTOR_TAB_NAVBAR_NAV).scrollLeft();
      $(SELECTOR_TAB_NAVBAR_NAV).animate({
        scrollLeft: leftPos + offset
      }, 250, 'linear');
    };
    _proto._setupListeners = function _setupListeners() {
      var _this3 = this;
      $(window).on('resize', function () {
        setTimeout(function () {
          _this3._fixHeight();
        }, 1);
      });
      if ($(SELECTOR_CONTENT_WRAPPER).hasClass(CLASS_NAME_IFRAME_MODE$1)) {
        $(document).on('click', SELECTOR_SIDEBAR_MENU_ITEM + ", " + SELECTOR_SIDEBAR_SEARCH_ITEM, function (e) {
          e.preventDefault();
          _this3.openTabSidebar(e.target);
        });
        if (this._config.useNavbarItems) {
          $(document).on('click', SELECTOR_HEADER_MENU_ITEM + ", " + SELECTOR_HEADER_DROPDOWN_ITEM, function (e) {
            e.preventDefault();
            _this3.openTabSidebar(e.target);
          });
        }
      }
      $(document).on('click', SELECTOR_TAB_NAVBAR_NAV_LINK, function (e) {
        e.preventDefault();
        _this3.onTabClick(e.target);
        _this3.switchTab(e.target);
      });
      $(document).on('click', SELECTOR_TAB_NAVBAR_NAV_LINK, function (e) {
        e.preventDefault();
        _this3.onTabClick(e.target);
        _this3.switchTab(e.target);
      });
      $(document).on('click', SELECTOR_DATA_TOGGLE_CLOSE, function (e) {
        e.preventDefault();
        var target = e.target;
        if (target.nodeName === 'I') {
          target = e.target.offsetParent;
        }
        _this3.removeActiveTab(target.attributes['data-type'] ? target.attributes['data-type'].nodeValue : null, target);
      });
      $(document).on('click', SELECTOR_DATA_TOGGLE_FULLSCREEN, function (e) {
        e.preventDefault();
        _this3.toggleFullscreen();
      });
      var mousedown = false;
      var mousedownInterval = null;
      $(document).on('mousedown', SELECTOR_DATA_TOGGLE_SCROLL_LEFT, function (e) {
        e.preventDefault();
        clearInterval(mousedownInterval);
        var scrollOffset = _this3._config.scrollOffset;
        if (!_this3._config.scrollBehaviorSwap) {
          scrollOffset = -scrollOffset;
        }
        mousedown = true;
        _this3._navScroll(scrollOffset);
        mousedownInterval = setInterval(function () {
          _this3._navScroll(scrollOffset);
        }, 250);
      });
      $(document).on('mousedown', SELECTOR_DATA_TOGGLE_SCROLL_RIGHT, function (e) {
        e.preventDefault();
        clearInterval(mousedownInterval);
        var scrollOffset = _this3._config.scrollOffset;
        if (_this3._config.scrollBehaviorSwap) {
          scrollOffset = -scrollOffset;
        }
        mousedown = true;
        _this3._navScroll(scrollOffset);
        mousedownInterval = setInterval(function () {
          _this3._navScroll(scrollOffset);
        }, 250);
      });
      $(document).on('mouseup', function () {
        if (mousedown) {
          mousedown = false;
          clearInterval(mousedownInterval);
          mousedownInterval = null;
        }
      });
    };
    _proto._setItemActive = function _setItemActive(href) {
      $(SELECTOR_SIDEBAR_MENU_ITEM + ", " + SELECTOR_HEADER_DROPDOWN_ITEM).removeClass('active');
      $(SELECTOR_HEADER_MENU_ITEM).parent().removeClass('active');
      var $headerMenuItem = $(SELECTOR_HEADER_MENU_ITEM + "[href$=\"" + href + "\"]");
      var $headerDropdownItem = $(SELECTOR_HEADER_DROPDOWN_ITEM + "[href$=\"" + href + "\"]");
      var $sidebarMenuItem = $(SELECTOR_SIDEBAR_MENU_ITEM + "[href$=\"" + href + "\"]");
      $headerMenuItem.each(function (i, e) {
        $(e).parent().addClass('active');
      });
      $headerDropdownItem.each(function (i, e) {
        $(e).addClass('active');
      });
      $sidebarMenuItem.each(function (i, e) {
        $(e).addClass('active');
        $(e).parents('.nav-treeview').prevAll('.nav-link').addClass('active');
      });
    };
    _proto._fixHeight = function _fixHeight(tabEmpty) {
      if (tabEmpty === void 0) {
        tabEmpty = false;
      }
      if ($('body').hasClass(CLASS_NAME_FULLSCREEN_MODE)) {
        var windowHeight = $(window).height();
        var navbarHeight = $(SELECTOR_TAB_NAV).outerHeight();
        $(SELECTOR_TAB_EMPTY + ", " + SELECTOR_TAB_LOADING + ", " + SELECTOR_CONTENT_IFRAME).height(windowHeight - navbarHeight);
        $(SELECTOR_CONTENT_WRAPPER).height(windowHeight);
      } else {
        var contentWrapperHeight = parseFloat($(SELECTOR_CONTENT_WRAPPER).css('height'));
        var _navbarHeight = $(SELECTOR_TAB_NAV).outerHeight();
        if (tabEmpty == true) {
          setTimeout(function () {
            $(SELECTOR_TAB_EMPTY + ", " + SELECTOR_TAB_LOADING).height(contentWrapperHeight - _navbarHeight);
          }, 50);
        } else {
          $(SELECTOR_CONTENT_IFRAME).height(contentWrapperHeight - _navbarHeight);
        }
      }
    }

    // Static
    // eslint-disable-next-line max-params
    ;
    IFrame._jQueryInterface = function _jQueryInterface(config, name, link, id, reload) {
      if ($(SELECTOR_DATA_TOGGLE$1).length > 0) {
        var data = $(this).data(DATA_KEY$7);
        if (!data) {
          data = $(this).data();
        }
        var _options = $.extend({}, Default$7, config !== null && typeof config === 'object' ? config : data);
        localStorage.setItem('AdminLTE:IFrame:Options', JSON.stringify(_options));
        var plugin = new IFrame($(this), _options);
        window.iFrameInstance = plugin;
        $(this).data(DATA_KEY$7, config !== null && typeof config === 'object' ? config : _extends({
          link: link,
          name: name,
          id: id,
          reload: reload
        }, data));
        if (typeof config === 'string' && /createTab|openTabSidebar|switchTab|removeActiveTab/.test(config)) {
          plugin[config](name, link, id, reload);
        }
      } else {
        window.iFrameInstance = new IFrame($(this), JSON.parse(localStorage.getItem('AdminLTE:IFrame:Options')))._initFrameElement();
      }
    };
    return IFrame;
  }();
  /**
   * Data API
   * ====================================================
   */
  $(window).on('load.lte.iframe', function () {
    return IFrame._jQueryInterface.call($(SELECTOR_DATA_TOGGLE$1));
  }); //***

  /**
   * jQuery API
   * ====================================================
   */

  $.fn[NAME$7] = IFrame._jQueryInterface;
  $.fn[NAME$7].Constructor = IFrame;
  $.fn[NAME$7].noConflict = function () {
    $.fn[NAME$7] = JQUERY_NO_CONFLICT$7;
    return IFrame._jQueryInterface;
  };

  /**
   * --------------------------------------------
   * AdminLTE Layout.js
   * License MIT
   * --------------------------------------------
   */


  /**
   * Constants
   * ====================================================
   */

  var NAME$6 = 'Layout';
  var DATA_KEY$6 = 'lte.layout';
  var JQUERY_NO_CONFLICT$6 = $.fn[NAME$6];
  var SELECTOR_HEADER = '.main-header';
  var SELECTOR_MAIN_SIDEBAR = '.main-sidebar';
  var SELECTOR_SIDEBAR$1 = '.main-sidebar .sidebar';
  var SELECTOR_CONTENT = '.content-wrapper';
  var SELECTOR_CONTROL_SIDEBAR_CONTENT = '.control-sidebar-content';
  var SELECTOR_CONTROL_SIDEBAR_BTN = '[data-widget="control-sidebar"]';
  var SELECTOR_FOOTER = '.main-footer';
  var SELECTOR_PUSHMENU_BTN = '[data-widget="pushmenu"]';
  var SELECTOR_LOGIN_BOX = '.login-box';
  var SELECTOR_REGISTER_BOX = '.register-box';
  var SELECTOR_PRELOADER = '.preloader';
  var CLASS_NAME_SIDEBAR_COLLAPSED$1 = 'sidebar-collapse';
  var CLASS_NAME_SIDEBAR_FOCUSED = 'sidebar-focused';
  var CLASS_NAME_LAYOUT_FIXED = 'layout-fixed';
  var CLASS_NAME_CONTROL_SIDEBAR_SLIDE_OPEN = 'control-sidebar-slide-open';
  var CLASS_NAME_CONTROL_SIDEBAR_OPEN = 'control-sidebar-open';
  var CLASS_NAME_IFRAME_MODE = 'iframe-mode';
  var Default$6 = {
    scrollbarTheme: 'os-theme-light',
    scrollbarAutoHide: 'l',
    panelAutoHeight: true,
    panelAutoHeightMode: 'min-height',
    preloadDuration: 200,
    loginRegisterAutoHeight: true
  };

  /**
   * Class Definition
   * ====================================================
   */
  var Layout = /*#__PURE__*/function () {
    function Layout(element, config) {
      this._config = config;
      this._element = element;
    }

    // Public
    var _proto = Layout.prototype;
    _proto.fixLayoutHeight = function fixLayoutHeight(extra) {
      if (extra === void 0) {
        extra = null;
      }
      var $body = $('body');
      var controlSidebar = 0;
      if ($body.hasClass(CLASS_NAME_CONTROL_SIDEBAR_SLIDE_OPEN) || $body.hasClass(CLASS_NAME_CONTROL_SIDEBAR_OPEN) || extra === 'control_sidebar') {
        controlSidebar = $(SELECTOR_CONTROL_SIDEBAR_CONTENT).outerHeight();
      }
      var heights = {
        window: $(window).height(),
        header: $(SELECTOR_HEADER).length > 0 ? $(SELECTOR_HEADER).outerHeight() : 0,
        footer: $(SELECTOR_FOOTER).length > 0 ? $(SELECTOR_FOOTER).outerHeight() : 0,
        sidebar: $(SELECTOR_SIDEBAR$1).length > 0 ? $(SELECTOR_SIDEBAR$1).height() : 0,
        controlSidebar: controlSidebar
      };
      var max = this._max(heights);
      var offset = this._config.panelAutoHeight;
      if (offset === true) {
        offset = 0;
      }
      var $contentSelector = $(SELECTOR_CONTENT);
      if (offset !== false) {
        if (max === heights.controlSidebar) {
          $contentSelector.css(this._config.panelAutoHeightMode, max + offset);
        } else if (max === heights.window) {
          $contentSelector.css(this._config.panelAutoHeightMode, max + offset - heights.header - heights.footer);
        } else {
          $contentSelector.css(this._config.panelAutoHeightMode, max + offset - heights.header);
        }
        if (this._isFooterFixed()) {
          $contentSelector.css(this._config.panelAutoHeightMode, parseFloat($contentSelector.css(this._config.panelAutoHeightMode)) + heights.footer);
        }
      }
      if (!$body.hasClass(CLASS_NAME_LAYOUT_FIXED)) {
        return;
      }
      $(SELECTOR_SIDEBAR$1).css('overflow-y', 'auto');
    };
    _proto.fixLoginRegisterHeight = function fixLoginRegisterHeight() {
      var $body = $('body');
      var $selector = $(SELECTOR_LOGIN_BOX + ", " + SELECTOR_REGISTER_BOX);
      if ($body.hasClass(CLASS_NAME_IFRAME_MODE)) {
        $body.css('height', '100%');
        $('.wrapper').css('height', '100%');
        $('html').css('height', '100%');
      } else if ($selector.length === 0) {
        $body.css('height', 'auto');
        $('html').css('height', 'auto');
      } else {
        var boxHeight = $selector.height();
        if ($body.css(this._config.panelAutoHeightMode) !== boxHeight) {
          $body.css(this._config.panelAutoHeightMode, boxHeight);
        }
      }
    }

    // Private
    ;
    _proto._init = function _init() {
      var _this = this;
      // Activate layout height watcher
      this.fixLayoutHeight();
      if (this._config.loginRegisterAutoHeight === true) {
        this.fixLoginRegisterHeight();
      } else if (this._config.loginRegisterAutoHeight === parseInt(this._config.loginRegisterAutoHeight, 10)) {
        setInterval(this.fixLoginRegisterHeight, this._config.loginRegisterAutoHeight);
      }
      $(SELECTOR_SIDEBAR$1).on('collapsed.lte.treeview expanded.lte.treeview', function () {
        _this.fixLayoutHeight();
      });
      $(SELECTOR_MAIN_SIDEBAR).on('mouseenter mouseleave', function () {
        if ($('body').hasClass(CLASS_NAME_SIDEBAR_COLLAPSED$1)) {
          _this.fixLayoutHeight();
        }
      });
      $(SELECTOR_PUSHMENU_BTN).on('collapsed.lte.pushmenu shown.lte.pushmenu', function () {
        setTimeout(function () {
          _this.fixLayoutHeight();
        }, 300);
      });
      $(SELECTOR_CONTROL_SIDEBAR_BTN).on('collapsed.lte.controlsidebar', function () {
        _this.fixLayoutHeight();
      }).on('expanded.lte.controlsidebar', function () {
        _this.fixLayoutHeight('control_sidebar');
      });
      $(window).resize(function () {
        _this.fixLayoutHeight();
      });
      setTimeout(function () {
        $('body.hold-transition').removeClass('hold-transition');
      }, 50);
      setTimeout(function () {
        var $preloader = $(SELECTOR_PRELOADER);
        if ($preloader) {
          $preloader.css('height', 0);
          setTimeout(function () {
            $preloader.children().hide();
          }, 200);
        }
      }, this._config.preloadDuration);
    };
    _proto._max = function _max(numbers) {
      // Calculate the maximum number in a list
      var max = 0;
      Object.keys(numbers).forEach(function (key) {
        if (numbers[key] > max) {
          max = numbers[key];
        }
      });
      return max;
    };
    _proto._isFooterFixed = function _isFooterFixed() {
      return $(SELECTOR_FOOTER).css('position') === 'fixed';
    }

    // Static
    ;
    Layout._jQueryInterface = function _jQueryInterface(config) {
      return this.each(function () {
        var data = $(this).data(DATA_KEY$6);
        var _config = $.extend({}, Default$6, config !== null && typeof config === 'object' ? config : $(this).data());
        if (!data) {
          data = new Layout($(this), _config);
          $(this).data(DATA_KEY$6, data);
          data._init();
        }
        if (typeof config === 'string') {
          var _data$config, _data;
          //***
          (_data$config = (_data = data)[config]) == null || _data$config.call(_data);
        }
      });
    };
    return Layout;
  }();
  /**
   * Data API
   * ====================================================
   */
  $(window).on('load.lte.layout', function () {
    return Layout._jQueryInterface.call($('body'));
  }); //***

  $(SELECTOR_SIDEBAR$1 + " a").on('focusin', function () {
    $(SELECTOR_MAIN_SIDEBAR).addClass(CLASS_NAME_SIDEBAR_FOCUSED);
  }).on('focusout', function () {
    $(SELECTOR_MAIN_SIDEBAR).removeClass(CLASS_NAME_SIDEBAR_FOCUSED);
  });

  /**
   * jQuery API
   * ====================================================
   */

  $.fn[NAME$6] = Layout._jQueryInterface;
  $.fn[NAME$6].Constructor = Layout;
  $.fn[NAME$6].noConflict = function () {
    $.fn[NAME$6] = JQUERY_NO_CONFLICT$6;
    return Layout._jQueryInterface;
  };

  /**
   * --------------------------------------------
   * AdminLTE PushMenu.js
   * License MIT
   * --------------------------------------------
   */


  /**
   * Constants
   * ====================================================
   */

  var NAME$5 = 'PushMenu';
  var DATA_KEY$5 = 'lte.pushmenu';
  var EVENT_KEY$2 = "." + DATA_KEY$5;
  var JQUERY_NO_CONFLICT$5 = $.fn[NAME$5];
  var EVENT_COLLAPSED$1 = "collapsed" + EVENT_KEY$2;
  var EVENT_COLLAPSED_DONE = "collapsed-done" + EVENT_KEY$2;
  var EVENT_SHOWN = "shown" + EVENT_KEY$2;
  var SELECTOR_TOGGLE_BUTTON$1 = '[data-widget="pushmenu"]';
  var SELECTOR_BODY = 'body';
  var SELECTOR_OVERLAY = '#sidebar-overlay';
  var SELECTOR_WRAPPER = '.wrapper';
  var CLASS_NAME_COLLAPSED = 'sidebar-collapse';
  var CLASS_NAME_OPEN$3 = 'sidebar-open';
  var CLASS_NAME_IS_OPENING$1 = 'sidebar-is-opening';
  var CLASS_NAME_CLOSED = 'sidebar-closed';
  var Default$5 = {
    autoCollapseSize: 992,
    enableRemember: false,
    noTransitionAfterReload: true,
    animationSpeed: 300
  };

  /**
   * Class Definition
   * ====================================================
   */
  var PushMenu = /*#__PURE__*/function () {
    function PushMenu(element, options) {
      this._element = element;
      this._options = options;
      if ($(SELECTOR_OVERLAY).length === 0) {
        this._addOverlay();
      }
      this._init();
    }

    // Public
    var _proto = PushMenu.prototype;
    _proto.expand = function expand() {
      var $bodySelector = $(SELECTOR_BODY);
      if (this._options.autoCollapseSize && $(window).width() <= this._options.autoCollapseSize) {
        $bodySelector.addClass(CLASS_NAME_OPEN$3);
      }
      $bodySelector.addClass(CLASS_NAME_IS_OPENING$1).removeClass(CLASS_NAME_COLLAPSED + " " + CLASS_NAME_CLOSED).delay(50).queue(function () {
        $bodySelector.removeClass(CLASS_NAME_IS_OPENING$1);
        $(this).dequeue();
      });
      if (this._options.enableRemember) {
        localStorage.setItem("remember" + EVENT_KEY$2, CLASS_NAME_OPEN$3);
      }
      $(this._element).trigger($.Event(EVENT_SHOWN));
    };
    _proto.collapse = function collapse() {
      var _this = this;
      var $bodySelector = $(SELECTOR_BODY);
      if (this._options.autoCollapseSize && $(window).width() <= this._options.autoCollapseSize) {
        $bodySelector.removeClass(CLASS_NAME_OPEN$3).addClass(CLASS_NAME_CLOSED);
      }
      $bodySelector.addClass(CLASS_NAME_COLLAPSED);
      if (this._options.enableRemember) {
        localStorage.setItem("remember" + EVENT_KEY$2, CLASS_NAME_COLLAPSED);
      }
      $(this._element).trigger($.Event(EVENT_COLLAPSED$1));
      setTimeout(function () {
        $(_this._element).trigger($.Event(EVENT_COLLAPSED_DONE));
      }, this._options.animationSpeed);
    };
    _proto.toggle = function toggle() {
      if ($(SELECTOR_BODY).hasClass(CLASS_NAME_COLLAPSED)) {
        this.expand();
      } else {
        this.collapse();
      }
    };
    _proto.autoCollapse = function autoCollapse(resize) {
      if (resize === void 0) {
        resize = false;
      }
      if (!this._options.autoCollapseSize) {
        return;
      }
      var $bodySelector = $(SELECTOR_BODY);
      if ($(window).width() <= this._options.autoCollapseSize) {
        if (!$bodySelector.hasClass(CLASS_NAME_OPEN$3)) {
          this.collapse();
        }
      } else if (resize === true) {
        if ($bodySelector.hasClass(CLASS_NAME_OPEN$3)) {
          $bodySelector.removeClass(CLASS_NAME_OPEN$3);
        } else if ($bodySelector.hasClass(CLASS_NAME_CLOSED)) {
          this.expand();
        }
      }
    };
    _proto.remember = function remember() {
      if (!this._options.enableRemember) {
        return;
      }
      var $body = $('body');
      var toggleState = localStorage.getItem("remember" + EVENT_KEY$2);
      if (toggleState === CLASS_NAME_COLLAPSED) {
        if (this._options.noTransitionAfterReload) {
          $body.addClass('hold-transition').addClass(CLASS_NAME_COLLAPSED).delay(50).queue(function () {
            $(this).removeClass('hold-transition');
            $(this).dequeue();
          });
        } else {
          $body.addClass(CLASS_NAME_COLLAPSED);
        }
      } else if (this._options.noTransitionAfterReload) {
        $body.addClass('hold-transition').removeClass(CLASS_NAME_COLLAPSED).delay(50).queue(function () {
          $(this).removeClass('hold-transition');
          $(this).dequeue();
        });
      } else {
        $body.removeClass(CLASS_NAME_COLLAPSED);
      }
    }

    // Private
    ;
    _proto._init = function _init() {
      var _this2 = this;
      this.remember();
      this.autoCollapse();
      $(window).resize(function () {
        _this2.autoCollapse(true);
      });
    };
    _proto._addOverlay = function _addOverlay() {
      var _this3 = this;
      var overlay = $('<div />', {
        id: 'sidebar-overlay'
      });
      overlay.on('click', function () {
        _this3.collapse();
      });
      $(SELECTOR_WRAPPER).append(overlay);
    }

    // Static
    ;
    PushMenu._jQueryInterface = function _jQueryInterface(config) {
      return this.each(function () {
        var data = $(this).data(DATA_KEY$5);
        var _config = $.extend({}, Default$5, config !== null && typeof config === 'object' ? config : $(this).data());
        if (!data) {
          data = new PushMenu($(this), _config);
          $(this).data(DATA_KEY$5, data);
          data._init();
        }
        if (typeof config === 'string') {
          var _data$config, _data;
          //***
          (_data$config = (_data = data)[config]) == null || _data$config.call(_data);
        }
      });
    };
    return PushMenu;
  }();
  /**
   * Data API
   * ====================================================
   */
  $(document).on('click', SELECTOR_TOGGLE_BUTTON$1, function (event) {
    event.preventDefault();
    var button = event.currentTarget;
    if ($(button).data('widget') !== 'pushmenu') {
      button = $(button).closest(SELECTOR_TOGGLE_BUTTON$1);
    }
    PushMenu._jQueryInterface.call($(button), 'toggle');
  });
  $(window).on('load.lte.pushmenu', function () {
    return PushMenu._jQueryInterface.call($(SELECTOR_TOGGLE_BUTTON$1));
  }); //***

  /**
   * jQuery API
   * ====================================================
   */

  $.fn[NAME$5] = PushMenu._jQueryInterface;
  $.fn[NAME$5].Constructor = PushMenu;
  $.fn[NAME$5].noConflict = function () {
    $.fn[NAME$5] = JQUERY_NO_CONFLICT$5;
    return PushMenu._jQueryInterface;
  };

  /**
   * --------------------------------------------
   * AdminLTE SidebarSearch.js
   * License MIT
   * --------------------------------------------
   */


  /**
   * Constants
   * ====================================================
   */

  var NAME$4 = 'SidebarSearch';
  var DATA_KEY$4 = 'lte.sidebar-search';
  var JQUERY_NO_CONFLICT$4 = $.fn[NAME$4];
  var CLASS_NAME_OPEN$2 = 'sidebar-search-open';
  var CLASS_NAME_ICON_SEARCH = 'fa-magnifying-glass'; //***
  var CLASS_NAME_ICON_CLOSE = 'fa-xmark'; //***
  var CLASS_NAME_HEADER = 'nav-header';
  var CLASS_NAME_SEARCH_RESULTS = 'sidebar-search-results';
  var CLASS_NAME_LIST_GROUP = 'list-group';
  var SELECTOR_DATA_WIDGET$1 = '[data-widget="sidebar-search"]';
  var SELECTOR_SIDEBAR = '.main-sidebar .nav-sidebar';
  var SELECTOR_NAV_LINK = '.nav-link';
  var SELECTOR_NAV_TREEVIEW = '.nav-treeview';
  var SELECTOR_SEARCH_INPUT$1 = SELECTOR_DATA_WIDGET$1 + " .form-control";
  var SELECTOR_SEARCH_BUTTON = SELECTOR_DATA_WIDGET$1 + " .btn";
  var SELECTOR_SEARCH_ICON = SELECTOR_SEARCH_BUTTON + " i";
  var SELECTOR_SEARCH_LIST_GROUP = "." + CLASS_NAME_LIST_GROUP;
  var SELECTOR_SEARCH_RESULTS = "." + CLASS_NAME_SEARCH_RESULTS;
  var SELECTOR_SEARCH_RESULTS_GROUP = SELECTOR_SEARCH_RESULTS + " ." + CLASS_NAME_LIST_GROUP;
  var Default$4 = {
    arrowSign: '->',
    minLength: 3,
    maxResults: 7,
    highlightName: true,
    highlightPath: false,
    highlightClass: 'text-light',
    notFoundText: 'No element found!'
  };
  var SearchItems = [];

  /**
   * Class Definition
   * ====================================================
   */
  var SidebarSearch = /*#__PURE__*/function () {
    function SidebarSearch(_element, _options) {
      this.element = _element;
      this.options = $.extend({}, Default$4, _options);
      this.items = [];
    }

    // Public
    var _proto = SidebarSearch.prototype;
    _proto._init = function _init() {
      var _this = this;
      if ($(SELECTOR_DATA_WIDGET$1).length === 0) {
        return;
      }
      if ($(SELECTOR_DATA_WIDGET$1).next(SELECTOR_SEARCH_RESULTS).length === 0) {
        $(SELECTOR_DATA_WIDGET$1).after($('<div />', {
          class: CLASS_NAME_SEARCH_RESULTS
        }));
      }
      if ($(SELECTOR_SEARCH_RESULTS).children(SELECTOR_SEARCH_LIST_GROUP).length === 0) {
        $(SELECTOR_SEARCH_RESULTS).append($('<div />', {
          class: CLASS_NAME_LIST_GROUP
        }));
      }
      this._addNotFound();
      $(SELECTOR_SIDEBAR).children().each(function (i, child) {
        _this._parseItem(child);
      });
    };
    _proto.search = function search() {
      var _this2 = this;
      var searchValue = $(SELECTOR_SEARCH_INPUT$1).val().toLowerCase();
      if (searchValue.length < this.options.minLength) {
        $(SELECTOR_SEARCH_RESULTS_GROUP).empty();
        this._addNotFound();
        this.close();
        return;
      }
      var searchResults = SearchItems.filter(function (item) {
        return item.name.toLowerCase().includes(searchValue);
      });
      var endResults = $(searchResults.slice(0, this.options.maxResults));
      $(SELECTOR_SEARCH_RESULTS_GROUP).empty();
      if (endResults.length === 0) {
        this._addNotFound();
      } else {
        endResults.each(function (i, result) {
          $(SELECTOR_SEARCH_RESULTS_GROUP).append(_this2._renderItem(encodeURIComponent(result.name), encodeURI(result.link), result.path));
        });
      }
      this.open();
    };
    _proto.open = function open() {
      $(SELECTOR_DATA_WIDGET$1).parent().addClass(CLASS_NAME_OPEN$2);
      $(SELECTOR_SEARCH_ICON).removeClass(CLASS_NAME_ICON_SEARCH).addClass(CLASS_NAME_ICON_CLOSE);
    };
    _proto.close = function close() {
      $(SELECTOR_DATA_WIDGET$1).parent().removeClass(CLASS_NAME_OPEN$2);
      $(SELECTOR_SEARCH_ICON).removeClass(CLASS_NAME_ICON_CLOSE).addClass(CLASS_NAME_ICON_SEARCH);
    };
    _proto.toggle = function toggle() {
      if ($(SELECTOR_DATA_WIDGET$1).parent().hasClass(CLASS_NAME_OPEN$2)) {
        this.close();
      } else {
        this.open();
      }
    }

    // Private
    ;
    _proto._parseItem = function _parseItem(item, path) {
      var _this3 = this;
      if (path === void 0) {
        path = [];
      }
      if ($(item).hasClass(CLASS_NAME_HEADER)) {
        return;
      }
      var itemObject = {};
      var navLink = $(item).clone().find("> " + SELECTOR_NAV_LINK);
      var navTreeview = $(item).clone().find("> " + SELECTOR_NAV_TREEVIEW);
      var link = navLink.attr('href');
      var name = navLink.find('p').children().remove().end().text();
      itemObject.name = this._trimText(name);
      itemObject.link = link;
      itemObject.path = path;
      if (navTreeview.length === 0) {
        SearchItems.push(itemObject);
      } else {
        var newPath = itemObject.path.concat([itemObject.name]);
        navTreeview.children().each(function (i, child) {
          _this3._parseItem(child, newPath);
        });
      }
    };
    _proto._trimText = function _trimText(text) {
      return text.replace(/(\r\n|\n|\r)/gm, ' ').trim();
    };
    _proto._renderItem = function _renderItem(name, link, path) {
      var _this4 = this;
      path = path.join(" " + this.options.arrowSign + " ");
      name = decodeURIComponent(name);
      link = decodeURI(link);
      if (this.options.highlightName || this.options.highlightPath) {
        var searchValue = $(SELECTOR_SEARCH_INPUT$1).val().toLowerCase();
        var regExp = new RegExp(searchValue, 'gi');
        if (this.options.highlightName) {
          name = name.replace(regExp, function (str) {
            return "<strong class=\"" + _this4.options.highlightClass + "\">" + str + "</strong>";
          });
        }
        if (this.options.highlightPath) {
          path = path.replace(regExp, function (str) {
            return "<strong class=\"" + _this4.options.highlightClass + "\">" + str + "</strong>";
          });
        }
      }
      var groupItemElement = $('<a/>', {
        href: decodeURI(link),
        class: 'list-group-item'
      });
      var searchTitleElement = $('<div/>', {
        class: 'search-title'
      }).html(name);
      var searchPathElement = $('<div/>', {
        class: 'search-path'
      }).html(path);
      groupItemElement.append(searchTitleElement).append(searchPathElement);
      return groupItemElement;
    };
    _proto._addNotFound = function _addNotFound() {
      $(SELECTOR_SEARCH_RESULTS_GROUP).append(this._renderItem(this.options.notFoundText, '#', []));
    }

    // Static
    ;
    SidebarSearch._jQueryInterface = function _jQueryInterface(config) {
      return this.each(function () {
        var data = $(this).data(DATA_KEY$4);
        var _config = $.extend({}, Default$4, config !== null && typeof config === 'object' ? config : $(this).data());
        if (!data) {
          data = new SidebarSearch($(this), _config);
          $(this).data(DATA_KEY$4, data);
          data._init();
        }
        if (typeof config === 'string') {
          var _data$config, _data;
          //***
          (_data$config = (_data = data)[config]) == null || _data$config.call(_data);
        }
      });
    };
    return SidebarSearch;
  }();
  /**
   * Data API
   * ====================================================
   */
  $(document).on('click', SELECTOR_SEARCH_BUTTON, function (event) {
    event.preventDefault();
    SidebarSearch._jQueryInterface.call($(SELECTOR_DATA_WIDGET$1), 'toggle');
  });
  $(document).on('keyup', SELECTOR_SEARCH_INPUT$1, function (event) {
    if (event.keyCode == 38) {
      event.preventDefault();
      $(SELECTOR_SEARCH_RESULTS_GROUP).children().last().focus();
      return;
    }
    if (event.keyCode == 40) {
      event.preventDefault();
      $(SELECTOR_SEARCH_RESULTS_GROUP).children().first().focus();
      return;
    }
    setTimeout(function () {
      SidebarSearch._jQueryInterface.call($(SELECTOR_DATA_WIDGET$1), 'search');
    }, 100);
  });
  $(document).on('keydown', SELECTOR_SEARCH_RESULTS_GROUP, function (event) {
    var $focused = $(':focus');
    if (event.keyCode == 38) {
      event.preventDefault();
      if ($focused.is(':first-child')) {
        $focused.siblings().last().focus();
      } else {
        $focused.prev().focus();
      }
    }
    if (event.keyCode == 40) {
      event.preventDefault();
      if ($focused.is(':last-child')) {
        $focused.siblings().first().focus();
      } else {
        $focused.next().focus();
      }
    }
  });
  $(window).on('load.lte.sidebarsearch', function () {
    return SidebarSearch._jQueryInterface.call($(SELECTOR_DATA_WIDGET$1));
  }); //***

  /**
   * jQuery API
   * ====================================================
   */

  $.fn[NAME$4] = SidebarSearch._jQueryInterface;
  $.fn[NAME$4].Constructor = SidebarSearch;
  $.fn[NAME$4].noConflict = function () {
    $.fn[NAME$4] = JQUERY_NO_CONFLICT$4;
    return SidebarSearch._jQueryInterface;
  };

  /**
   * --------------------------------------------
   * AdminLTE NavbarSearch.js
   * License MIT
   * --------------------------------------------
   */


  /**
   * Constants
   * ====================================================
   */

  var NAME$3 = 'NavbarSearch';
  var DATA_KEY$3 = 'lte.navbar-search';
  var JQUERY_NO_CONFLICT$3 = $.fn[NAME$3];
  var SELECTOR_TOGGLE_BUTTON = '[data-widget="navbar-search"]';
  var SELECTOR_SEARCH_BLOCK = '.navbar-search-block';
  var SELECTOR_SEARCH_INPUT = '.form-control';
  var CLASS_NAME_OPEN$1 = 'navbar-search-open';
  var Default$3 = {
    resetOnClose: true,
    target: SELECTOR_SEARCH_BLOCK
  };

  /**
   * Class Definition
   * ====================================================
   */
  var NavbarSearch = /*#__PURE__*/function () {
    function NavbarSearch(_element, _options) {
      this._element = _element;
      this._config = _options;
    }

    // Public
    var _proto = NavbarSearch.prototype;
    _proto.open = function open() {
      $(this._config.target).css('display', 'flex').hide().fadeIn().addClass(CLASS_NAME_OPEN$1);
      $(this._config.target + " " + SELECTOR_SEARCH_INPUT).focus();
    };
    _proto.close = function close() {
      $(this._config.target).fadeOut().removeClass(CLASS_NAME_OPEN$1);
      if (this._config.resetOnClose) {
        $(this._config.target + " " + SELECTOR_SEARCH_INPUT).val('');
      }
    };
    _proto.toggle = function toggle() {
      if ($(this._config.target).hasClass(CLASS_NAME_OPEN$1)) {
        this.close();
      } else {
        this.open();
      }
    }

    // Static
    ;
    NavbarSearch._jQueryInterface = function _jQueryInterface(config) {
      return this.each(function () {
        var data = $(this).data(DATA_KEY$3);
        var _config = $.extend({}, Default$3, config !== null && typeof config === 'object' ? config : $(this).data());
        if (!data) {
          data = new NavbarSearch($(this), _config);
          $(this).data(DATA_KEY$3, data);
        }
        if (typeof config === 'string') {
          var _data$config, _data;
          //***
          (_data$config = (_data = data)[config]) == null || _data$config.call(_data);
        }
      });
    };
    return NavbarSearch;
  }();
  /**
   * Data API
   * ====================================================
   */
  $(document).on('click', SELECTOR_TOGGLE_BUTTON, function (event) {
    event.preventDefault();
    var button = $(event.currentTarget);
    if (button.data('widget') !== 'navbar-search') {
      button = button.closest(SELECTOR_TOGGLE_BUTTON);
    }
    NavbarSearch._jQueryInterface.call(button, 'toggle');
  });

  /**
   * jQuery API
   * ====================================================
   */

  $.fn[NAME$3] = NavbarSearch._jQueryInterface;
  $.fn[NAME$3].Constructor = NavbarSearch;
  $.fn[NAME$3].noConflict = function () {
    $.fn[NAME$3] = JQUERY_NO_CONFLICT$3;
    return NavbarSearch._jQueryInterface;
  };

  /**
   * --------------------------------------------
   * AdminLTE Toasts.js
   * License MIT
   * --------------------------------------------
   */


  /**
   * Constants
   * ====================================================
   */

  var NAME$2 = 'Toasts';
  var DATA_KEY$2 = 'lte.toasts';
  var EVENT_KEY$1 = "." + DATA_KEY$2;
  var JQUERY_NO_CONFLICT$2 = $.fn[NAME$2];
  var EVENT_INIT = "init" + EVENT_KEY$1;
  var EVENT_CREATED = "created" + EVENT_KEY$1;
  var EVENT_REMOVED = "removed" + EVENT_KEY$1;
  var SELECTOR_CONTAINER_TOP_RIGHT = '#toastsContainerTopRight';
  var SELECTOR_CONTAINER_TOP_LEFT = '#toastsContainerTopLeft';
  var SELECTOR_CONTAINER_BOTTOM_RIGHT = '#toastsContainerBottomRight';
  var SELECTOR_CONTAINER_BOTTOM_LEFT = '#toastsContainerBottomLeft';

  // const CLASS_NAME_TOP_RIGHT = 'toasts-top-right'
  // const CLASS_NAME_TOP_LEFT = 'toasts-top-left'
  // const CLASS_NAME_BOTTOM_RIGHT = 'toasts-bottom-right'
  // const CLASS_NAME_BOTTOM_LEFT = 'toasts-bottom-left'

  var CLASS_NAME_TOP_RIGHT = 'toast-container position-absolute p-3 top-0 end-0'; //***
  var CLASS_NAME_TOP_LEFT = 'toast-container position-absolute p-3 top-0 start-0'; //***
  var CLASS_NAME_BOTTOM_RIGHT = 'toast-container position-absolute p-3 bottom-0 end-0'; //***
  var CLASS_NAME_BOTTOM_LEFT = 'toast-container position-absolute p-3 bottom-0 start-0'; //***

  var POSITION_TOP_RIGHT = 'topRight';
  var POSITION_TOP_LEFT = 'topLeft';
  var POSITION_BOTTOM_RIGHT = 'bottomRight';
  var POSITION_BOTTOM_LEFT = 'bottomLeft';
  var Default$2 = {
    position: POSITION_TOP_RIGHT,
    fixed: true,
    autohide: false,
    autoremove: true,
    delay: 1000,
    fade: true,
    icon: null,
    image: null,
    imageAlt: null,
    imageHeight: '25px',
    title: null,
    subtitle: null,
    close: true,
    body: null,
    class: null
  };

  /**
   * Class Definition
   * ====================================================
   */
  var Toasts = /*#__PURE__*/function () {
    function Toasts(element, config) {
      this._config = config;
      this._prepareContainer();
      $('body').trigger($.Event(EVENT_INIT));
    }

    // Public
    var _proto = Toasts.prototype;
    _proto.create = function create() {
      var cfg = {
        //***
        'autohide': this._config.autohide,
        'animation': this._config.fade,
        'delay': this._config.delay
      };
      var toast = $("<div class=\"toast\" role=\"alert\" aria-live=\"assertive\" aria-atomic=\"true\" data-bs-config='" + JSON.stringify(cfg) + "'/>");
      if (this._config.class) {
        toast.addClass(this._config.class);
      }
      var toastHeader = $('<div class="toast-header">');
      if (this._config.image != null) {
        var toastImage = $('<img />').addClass('rounded me-2').attr('src', this._config.image).attr('alt', this._config.imageAlt);
        if (this._config.imageHeight != null) {
          toastImage.height(this._config.imageHeight).width('auto');
        }
        toastHeader.append(toastImage);
      }
      if (this._config.icon != null) {
        toastHeader.append($('<i />').addClass('me-2').addClass(this._config.icon)); //***
      }
      if (this._config.title != null) {
        toastHeader.append($('<strong />').addClass('me-auto').html(this._config.title)); //***
      }
      if (this._config.subtitle != null) {
        toastHeader.append($('<small />').html(this._config.subtitle));
      }
      if (this._config.close == true) {
        var toastClose = $('<button data-bs-dismiss="toast"/>').attr('type', 'button').addClass('ms-2 mb-1 btn-close').attr('aria-label', 'Close'); //***

        if (this._config.title == null) {
          toastClose.toggleClass('ms-2 ms-auto'); //***
        }
        toastHeader.append(toastClose);
      }
      toast.append(toastHeader);
      if (this._config.body != null) {
        toast.append($('<div class="toast-body" />').html(this._config.body));
      }
      $(this._getContainerId()).prepend(toast);
      var $body = $('body');
      $body.trigger($.Event(EVENT_CREATED));

      //***toast.toast('show')

      bootstrap.Toast.getOrCreateInstance(toast[0]).show();
      if (this._config.autoremove) {
        toast.on('hidden.bs.toast', function () {
          $(this).delay(200).remove();
          $body.trigger($.Event(EVENT_REMOVED));
        });
      }
    }

    // Static
    ;
    _proto._getContainerId = function _getContainerId() {
      if (this._config.position === POSITION_TOP_RIGHT) {
        return SELECTOR_CONTAINER_TOP_RIGHT;
      }
      if (this._config.position === POSITION_TOP_LEFT) {
        return SELECTOR_CONTAINER_TOP_LEFT;
      }
      if (this._config.position === POSITION_BOTTOM_RIGHT) {
        return SELECTOR_CONTAINER_BOTTOM_RIGHT;
      }
      if (this._config.position === POSITION_BOTTOM_LEFT) {
        return SELECTOR_CONTAINER_BOTTOM_LEFT;
      }
    };
    _proto._prepareContainer = function _prepareContainer() {
      if ($(this._getContainerId()).length === 0) {
        var container = $('<div />').attr('id', this._getContainerId().replace('#', ''));
        if (this._config.position == POSITION_TOP_RIGHT) {
          container.addClass(CLASS_NAME_TOP_RIGHT);
        } else if (this._config.position == POSITION_TOP_LEFT) {
          container.addClass(CLASS_NAME_TOP_LEFT);
        } else if (this._config.position == POSITION_BOTTOM_RIGHT) {
          container.addClass(CLASS_NAME_BOTTOM_RIGHT);
        } else if (this._config.position == POSITION_BOTTOM_LEFT) {
          container.addClass(CLASS_NAME_BOTTOM_LEFT);
        }
        $('body').append(container);
      }
      if (this._config.fixed) {
        $(this._getContainerId()).addClass('position-fixed'); //***
      } else {
        $(this._getContainerId()).removeClass('position-fixed'); //***
      }
    }

    // Static
    ;
    Toasts._jQueryInterface = function _jQueryInterface(option, config) {
      return this.each(function () {
        var _options = $.extend({}, Default$2, config);
        var toast = new Toasts($(this), _options);
        if (option === 'create') {
          toast[option]();
        }
      });
    };
    return Toasts;
  }();
  /**
   * jQuery API
   * ====================================================
   */
  $.fn[NAME$2] = Toasts._jQueryInterface;
  $.fn[NAME$2].Constructor = Toasts;
  $.fn[NAME$2].noConflict = function () {
    $.fn[NAME$2] = JQUERY_NO_CONFLICT$2;
    return Toasts._jQueryInterface;
  };

  /**
   * --------------------------------------------
   * AdminLTE TodoList.js
   * License MIT
   * --------------------------------------------
   */


  /**
   * Constants
   * ====================================================
   */

  var NAME$1 = 'TodoList';
  var DATA_KEY$1 = 'lte.todolist';
  var JQUERY_NO_CONFLICT$1 = $.fn[NAME$1];
  var SELECTOR_DATA_TOGGLE = '[data-widget="todo-list"]';
  var CLASS_NAME_TODO_LIST_DONE = 'done';
  var Default$1 = {
    onCheck: function onCheck(item) {
      return item;
    },
    onUnCheck: function onUnCheck(item) {
      return item;
    }
  };

  /**
   * Class Definition
   * ====================================================
   */
  var TodoList = /*#__PURE__*/function () {
    function TodoList(element, config) {
      this._config = config;
      this._element = element;
      this._init();
    }

    // Public
    var _proto = TodoList.prototype;
    _proto.toggle = function toggle(item) {
      item.parents('li').toggleClass(CLASS_NAME_TODO_LIST_DONE);
      if (!$(item).prop('checked')) {
        this.unCheck(item);
        return;
      }
      this.check(item);
    };
    _proto.check = function check(item) {
      this._config.onCheck(item);
    };
    _proto.unCheck = function unCheck(item) {
      this._config.onUnCheck(item);
    }

    // Private
    ;
    _proto._init = function _init() {
      var _this = this;
      var $toggleSelector = this._element;
      $toggleSelector.find('input:checkbox:checked').parents('li').toggleClass(CLASS_NAME_TODO_LIST_DONE);
      $toggleSelector.on('change', 'input:checkbox', function (event) {
        _this.toggle($(event.target));
      });
    }

    // Static
    ;
    TodoList._jQueryInterface = function _jQueryInterface(config) {
      return this.each(function () {
        var data = $(this).data(DATA_KEY$1);
        var _config = $.extend({}, Default$1, config !== null && typeof config === 'object' ? config : $(this).data());
        if (!data) {
          data = new TodoList($(this), _config);
          $(this).data(DATA_KEY$1, data);
          data._init();
        }
        if (typeof config === 'string') {
          var _data$config, _data;
          //***
          (_data$config = (_data = data)[config]) == null || _data$config.call(_data);
        }
      });
    };
    return TodoList;
  }();
  /**
   * Data API
   * ====================================================
   */
  $(window).on('load.lte.todolist', function () {
    return TodoList._jQueryInterface.call($(SELECTOR_DATA_TOGGLE));
  }); //***

  /**
   * jQuery API
   * ====================================================
   */

  $.fn[NAME$1] = TodoList._jQueryInterface;
  $.fn[NAME$1].Constructor = TodoList;
  $.fn[NAME$1].noConflict = function () {
    $.fn[NAME$1] = JQUERY_NO_CONFLICT$1;
    return TodoList._jQueryInterface;
  };

  /**
   * --------------------------------------------
   * AdminLTE Treeview.js
   * License MIT
   * --------------------------------------------
   */


  /**
   * Constants
   * ====================================================
   */

  var NAME = 'Treeview';
  var DATA_KEY = 'lte.treeview';
  var EVENT_KEY = "." + DATA_KEY;
  var JQUERY_NO_CONFLICT = $.fn[NAME];
  var EVENT_EXPANDED = "expanded" + EVENT_KEY;
  var EVENT_COLLAPSED = "collapsed" + EVENT_KEY;
  var EVENT_LOAD_DATA_API = "load" + EVENT_KEY;
  var SELECTOR_LI = '.nav-item';
  var SELECTOR_LINK = '.nav-link';
  var SELECTOR_TREEVIEW_MENU = '.nav-treeview';
  var SELECTOR_OPEN = '.menu-open';
  var SELECTOR_DATA_WIDGET = '[data-widget="treeview"]';
  var CLASS_NAME_OPEN = 'menu-open';
  var CLASS_NAME_IS_OPENING = 'menu-is-opening';
  var CLASS_NAME_SIDEBAR_COLLAPSED = 'sidebar-collapse';
  var Default = {
    trigger: SELECTOR_DATA_WIDGET + " " + SELECTOR_LINK,
    animationSpeed: 300,
    accordion: true,
    expandSidebar: false,
    sidebarButtonSelector: '[data-widget="pushmenu"]'
  };

  /**
   * Class Definition
   * ====================================================
   */
  var Treeview = /*#__PURE__*/function () {
    function Treeview(element, config) {
      this._config = config;
      this._element = element;
    }

    // Public
    var _proto = Treeview.prototype;
    _proto._init = function _init() {
      $("" + SELECTOR_LI + SELECTOR_OPEN + " " + SELECTOR_TREEVIEW_MENU + SELECTOR_OPEN).addClass('d-block'); //***
      this._setupListeners();
    };
    _proto.expand = function expand(treeviewMenu, parentLi) {
      var _this = this;
      var expandedEvent = $.Event(EVENT_EXPANDED);
      if (this._config.accordion) {
        var openMenuLi = parentLi.siblings(SELECTOR_OPEN).first();
        var openTreeview = openMenuLi.find(SELECTOR_TREEVIEW_MENU).first();
        this.collapse(openTreeview, openMenuLi);
      }
      parentLi.addClass(CLASS_NAME_IS_OPENING);
      treeviewMenu.stop().slideDown(this._config.animationSpeed, function () {
        parentLi.addClass(CLASS_NAME_OPEN);
        $(_this._element).trigger(expandedEvent);
      });
      if (this._config.expandSidebar) {
        this._expandSidebar();
      }
    };
    _proto.collapse = function collapse(treeviewMenu, parentLi) {
      var _this2 = this;
      var collapsedEvent = $.Event(EVENT_COLLAPSED);
      parentLi.removeClass(CLASS_NAME_IS_OPENING + " " + CLASS_NAME_OPEN);
      treeviewMenu.stop().slideUp(this._config.animationSpeed, function () {
        $(_this2._element).trigger(collapsedEvent);
        treeviewMenu.find(SELECTOR_OPEN + " > " + SELECTOR_TREEVIEW_MENU).slideUp();
        treeviewMenu.find(SELECTOR_OPEN).removeClass(CLASS_NAME_IS_OPENING + " " + CLASS_NAME_OPEN);
      });
    };
    _proto.toggle = function toggle(event) {
      var $relativeTarget = $(event.currentTarget);
      var $parent = $relativeTarget.parent();
      var treeviewMenu = $parent.find("> " + SELECTOR_TREEVIEW_MENU);

      //***
      var href = $relativeTarget.attr('href');
      if (event.target.tagName == 'P' && href && href != '#' && !href.startsWith('javascript:'))
        // Menu text and parent with href
        return;
      if (!treeviewMenu.is(SELECTOR_TREEVIEW_MENU)) {
        if (!$parent.is(SELECTOR_LI)) {
          treeviewMenu = $parent.parent().find("> " + SELECTOR_TREEVIEW_MENU);
        }
        if (!treeviewMenu.is(SELECTOR_TREEVIEW_MENU)) {
          return;
        }
      }
      event.preventDefault();
      var parentLi = $relativeTarget.parents(SELECTOR_LI).first();
      var isOpen = parentLi.hasClass(CLASS_NAME_OPEN);
      if (isOpen) {
        this.collapse($(treeviewMenu), parentLi);
      } else {
        this.expand($(treeviewMenu), parentLi);
      }
    }

    // Private
    ;
    _proto._setupListeners = function _setupListeners() {
      var _this3 = this;
      var elementId = this._element.attr('id') !== undefined ? "#" + this._element.attr('id') : '';
      $(document).on('click', "" + elementId + this._config.trigger, function (event) {
        _this3.toggle(event);
      });
    };
    _proto._expandSidebar = function _expandSidebar() {
      if ($('body').hasClass(CLASS_NAME_SIDEBAR_COLLAPSED)) {
        $(this._config.sidebarButtonSelector).PushMenu('expand');
      }
    }

    // Static
    ;
    Treeview._jQueryInterface = function _jQueryInterface(config) {
      return this.each(function () {
        var data = $(this).data(DATA_KEY);
        var _config = $.extend({}, Default, config !== null && typeof config === 'object' ? config : $(this).data());
        if (!data) {
          data = new Treeview($(this), _config);
          $(this).data(DATA_KEY, data);
          data._init();
        }
        if (typeof config === 'string') {
          var _data$config, _data;
          (_data$config = (_data = data)[config]) == null || _data$config.call(_data);
        }
      });
    };
    return Treeview;
  }();
  /**
   * Data API
   * ====================================================
   */
  $(window).on(EVENT_LOAD_DATA_API, function () {
    return Treeview._jQueryInterface.call($(SELECTOR_DATA_WIDGET));
  }); //***

  /**
   * jQuery API
   * ====================================================
   */

  $.fn[NAME] = Treeview._jQueryInterface;
  $.fn[NAME].Constructor = Treeview;
  $.fn[NAME].noConflict = function () {
    $.fn[NAME] = JQUERY_NO_CONFLICT;
    return Treeview._jQueryInterface;
  };

  exports.CardRefresh = CardRefresh;
  exports.CardWidget = CardWidget;
  exports.ControlSidebar = ControlSidebar;
  exports.DirectChat = DirectChat;
  exports.Dropdown = Dropdown;
  exports.ExpandableTable = ExpandableTable;
  exports.Fullscreen = Fullscreen;
  exports.IFrame = IFrame;
  exports.Layout = Layout;
  exports.NavbarSearch = NavbarSearch;
  exports.PushMenu = PushMenu;
  exports.SidebarSearch = SidebarSearch;
  exports.Toasts = Toasts;
  exports.TodoList = TodoList;
  exports.Treeview = Treeview;

}));
//# sourceMappingURL=adminlte.js.map
