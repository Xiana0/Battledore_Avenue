/*!
 * Core JavaScript for PHPMaker v2026.10.0
 * Copyright (c) e.World Technology Limited. All rights reserved.
 */
var ew = (function () {
  'use strict';

  function _typeof(o) {
    "@babel/helpers - typeof";

    return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) {
      return typeof o;
    } : function (o) {
      return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o;
    }, _typeof(o);
  }

  function toPrimitive(t, r) {
    if ("object" != _typeof(t) || !t) return t;
    var e = t[Symbol.toPrimitive];
    if (void 0 !== e) {
      var i = e.call(t, r);
      if ("object" != _typeof(i)) return i;
      throw new TypeError("@@toPrimitive must return a primitive value.");
    }
    return ("string" === r ? String : Number)(t);
  }

  function toPropertyKey(t) {
    var i = toPrimitive(t, "string");
    return "symbol" == _typeof(i) ? i : i + "";
  }

  function _defineProperties(e, r) {
    for (var t = 0; t < r.length; t++) {
      var o = r[t];
      o.enumerable = o.enumerable || false, o.configurable = true, "value" in o && (o.writable = true), Object.defineProperty(e, toPropertyKey(o.key), o);
    }
  }
  function _createClass(e, r, t) {
    return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", {
      writable: false
    }), e;
  }

  function _defineProperty(e, r, t) {
    return (r = toPropertyKey(r)) in e ? Object.defineProperty(e, r, {
      value: t,
      enumerable: true,
      configurable: true,
      writable: true
    }) : e[r] = t, e;
  }

  /**
   * Language class
   */
  let Language = /*#__PURE__*/function () {
    /**
     * Constructor
     * @param {Object} data - Phrases
     */
    function Language(data) {
      _defineProperty(this, "data", {});
      this.data = data != null ? data : {};
    }

    /**
     * Get phrase by ID
     *
     * @param {string} id - ID
     * @returns {string} Phrase
     */
    var _proto = Language.prototype;
    _proto.phrase = function phrase(id) {
      var _this$data$id$toLower;
      return (_this$data$id$toLower = this.data[id.toLowerCase()]) != null ? _this$data$id$toLower : id;
    }

    /**
     * Get phrases
     */;
    return _createClass(Language, [{
      key: "phrases",
      get: function () {
        return this.data;
      }

      /**
       * Set phrases
       */,
      set: function (value) {
        this.data = value;
      }
    }]);
  }();

  function _setPrototypeOf(t, e) {
    return _setPrototypeOf = Object.setPrototypeOf ? Object.setPrototypeOf.bind() : function (t, e) {
      return t.__proto__ = e, t;
    }, _setPrototypeOf(t, e);
  }

  function _inheritsLoose(t, o) {
    t.prototype = Object.create(o.prototype), t.prototype.constructor = t, _setPrototypeOf(t, o);
  }

  function _getPrototypeOf(t) {
    return _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf.bind() : function (t) {
      return t.__proto__ || Object.getPrototypeOf(t);
    }, _getPrototypeOf(t);
  }

  function _isNativeFunction(t) {
    try {
      return -1 !== Function.toString.call(t).indexOf("[native code]");
    } catch (n) {
      return "function" == typeof t;
    }
  }

  function _isNativeReflectConstruct() {
    try {
      var t = !Boolean.prototype.valueOf.call(Reflect.construct(Boolean, [], function () {}));
    } catch (t) {}
    return (_isNativeReflectConstruct = function _isNativeReflectConstruct() {
      return !!t;
    })();
  }

  function _construct(t, e, r) {
    if (_isNativeReflectConstruct()) return Reflect.construct.apply(null, arguments);
    var o = [null];
    o.push.apply(o, e);
    var p = new (t.bind.apply(t, o))();
    return r && _setPrototypeOf(p, r.prototype), p;
  }

  function _wrapNativeSuper(t) {
    var r = "function" == typeof Map ? new Map() : void 0;
    return _wrapNativeSuper = function _wrapNativeSuper(t) {
      if (null === t || !_isNativeFunction(t)) return t;
      if ("function" != typeof t) throw new TypeError("Super expression must either be null or a function");
      if (void 0 !== r) {
        if (r.has(t)) return r.get(t);
        r.set(t, Wrapper);
      }
      function Wrapper() {
        return _construct(t, arguments, _getPrototypeOf(this).constructor);
      }
      return Wrapper.prototype = Object.create(t.prototype, {
        constructor: {
          value: Wrapper,
          enumerable: false,
          writable: true,
          configurable: true
        }
      }), _setPrototypeOf(Wrapper, t);
    }, _wrapNativeSuper(t);
  }

  /**
   * Class selection list option
   */
  let SelectionListOption = /*#__PURE__*/function () {
    /**
     * Constructor
     * @param {*} text - Inner HTML
     * @param {*} value - Value
     * @param {*} defaultSelected - Is selected
     */
    function SelectionListOption(text, value, defaultSelected) {
      _defineProperty(this, "_selected", undefined);
      _defineProperty(this, "_selectionList", undefined);
      _defineProperty(this, "_innerHTML", "");
      _defineProperty(this, "_value", "");
      _defineProperty(this, "_defaultSelected", false);
      this._innerHTML = String(text);
      this._value = String(value);
      this._defaultSelected = !!defaultSelected;
    }

    /**
     * Get text
     */
    return _createClass(SelectionListOption, [{
      key: "text",
      get: function () {
        return this._innerHTML;
      }

      /**
       * Set text
       */,
      set: function (value) {
        this._innerHTML = String(value);
      }

      /**
       * Get inner HTML
       */
    }, {
      key: "innerHTML",
      get: function () {
        return this._innerHTML;
      }

      /**
       * Set inner HTML
       */,
      set: function (value) {
        this._innerHTML = value;
      }

      /**
       * Get value
       */
    }, {
      key: "value",
      get: function () {
        return this._value;
      }

      /**
       * Set value
       */,
      set: function (value) {
        this._value = String(value);
      }

      /**
       * Get default selected
       */
    }, {
      key: "defaultSelected",
      get: function () {
        return this._defaultSelected;
      }

      /**
       * Set default selected
       */,
      set: function (value) {
        this._defaultSelected = !!value;
      }

      /**
       * Get selection list
       */
    }, {
      key: "selectionList",
      get: function () {
        return this._selectionList;
      }

      /**
       * Set selection list
       */,
      set: function (value) {
        this._selectionList = value;
      }

      /**
       * Get selected
       */
    }, {
      key: "selected",
      get: function () {
        var _this$_selected;
        return (_this$_selected = this._selected) != null ? _this$_selected : this.defaultSelected;
      }

      /**
       * Set selected
       */,
      set: function (value) {
        var _this$_selectionList;
        this._selected = value;
        (_this$_selectionList = this._selectionList) == null || _this$_selectionList.render();
      }
    }]);
  }();

  /**
   * Class Dynamic Selection List
   */
  let SelectionList = /*#__PURE__*/function (_HTMLElement) {
    /**
     * Constructor
     */
    function SelectionList() {
      var _this;
      _this = _HTMLElement.call(this) || this;
      _defineProperty(_this, "containerClass", "d-sm-table");
      _defineProperty(_this, "rowClass", "d-sm-table-row");
      _defineProperty(_this, "cellClass", "d-sm-table-cell");
      /**
       * Options
       * @type {SelectionListOption[]}
       */
      _defineProperty(_this, "options", []);
      _this._internals = _this.attachInternals();
      return _this;
    }

    /**
     * Connected
     */
    _inheritsLoose(SelectionList, _HTMLElement);
    var _proto = SelectionList.prototype;
    _proto.connectedCallback = function connectedCallback() {
      let value = this.getAttribute("value") || "",
        values = this.multiple ? value.split(ew.MULTIPLE_OPTION_SEPARATOR) : [value];
      for (let val of values) this.add(val, "", true);
    }

    /**
     * Target element id
     */;
    /**
     * Add an option
     */
    _proto.add = function add(value, text, selected) {
      let option = new SelectionListOption(text, value, selected);
      this.addOption(option);
    }

    /**
     * Add an option
     */;
    _proto.addOption = function addOption(option) {
      if (option instanceof SelectionListOption) {
        option.selectionList = this;
        let index = this.options.findIndex(opt => opt.value == option.value);
        if (index > -1) this.options[index] = option;else this.options.push(option);
      }
    }

    /**
     * Remove an option
     */;
    _proto.remove = function remove(index) {
      let option = this.options[index];
      if (option) this.options.splice(index, 1);
    }

    /**
     * Remove all options
     */;
    _proto.removeAll = function removeAll() {
      this.options.splice(0);
    }

    /**
     * Clear selection
     */;
    _proto.clear = function clear() {
      for (let option of this.options) option.selected = false;
      this.render();
    }

    /**
     * Get random number
     */;
    _proto.getRandom = function getRandom() {
      return Math.floor(Math.random() * (999999 - 100000)) + 100000;
    }

    /**
     * Trigger change event
     */;
    _proto.triggerChange = function triggerChange() {
      const event = new Event("change", {
        view: window,
        bubbles: true,
        cancelable: false
      });
      this.dispatchEvent(event);
    }

    /**
     * Check if invalid
     */;
    _proto.isInvalid = function isInvalid(className) {
      return /\bis-invalid\b/.test(className);
    }

    /**
     * Check class
     */;
    _proto.attributeChangedCallback = function attributeChangedCallback(name, oldValue, newValue) {
      if (name == "class") {
        if (this.targetId && this.isInvalid(oldValue) != this.isInvalid(newValue)) {
          // "is-invalid" toggled
          let target = document.getElementById(this.targetId),
            inputs = target.querySelectorAll("input"),
            isInvalid = this.isInvalid(newValue);
          inputs.forEach(input => input.classList.toggle("is-invalid", isInvalid));
        }
      } else if (name == "value") {
        this._internals.setFormValue(this.value);
        if (this.value !== "") this.classList.remove("is-invalid");
      } else if (name == "disabled") {
        let target = document.getElementById(this.targetId),
          inputs = target.querySelectorAll("input");
        console.log(oldValue, newValue);
        inputs.forEach(input => input.disabled = this.hasAttribute("disabled"));
      }
    }

    /**
     * Show loading
     */;
    _proto.showLoading = function showLoading() {
      var _this$target;
      (_this$target = this.target) == null || _this$target.appendChild(document.createRange().createContextualFragment(ew.spinnerTemplate()));
    }

    /**
     * Hide loading
     */;
    _proto.hideLoading = function hideLoading() {
      var _this$target2;
      (_this$target2 = this.target) == null || (_this$target2 = _this$target2.querySelector(".ew-loading")) == null || _this$target2.remove();
    }

    /**
     * Render checkbox or radio in the target element
     */;
    _proto.render = function render() {
      let target = this.target,
        template = this.template;
      if (!target || !template || !this.list) return;

      // Clear the target
      while (target.firstChild) target.removeChild(target.firstChild);

      // Render
      target.innerHTML = ew.spinnerTemplate();
      let self = this,
        content = template.content,
        node = content.cloneNode(true),
        input = node.querySelector("input"),
        cols = this.columns || 1,
        tbl = document.createElement("div"),
        cnt = this.length,
        radioSuffix = "_" + this.getRandom(),
        isInvalid = this.classList.contains("is-invalid"),
        row,
        title,
        bsToggle;
      tbl.className = this.containerClass + " ew-item-container";
      if (input.type == "radio" || input.type == "checkbox") {
        var _input$dataset;
        title = input.title, bsToggle = (_input$dataset = input.dataset) == null ? void 0 : _input$dataset.bsToggle;
        if (title && bsToggle) {
          input.removeAttribute("title");
          input.removeAttribute("data-bs-toggle");
          $(tbl).attr("title", title);
          new bootstrap.Tooltip(tbl, ew.tooltipOptions);
        }
      }
      let options = this.options.filter(opt => opt.value);
      options.forEach((option, i) => {
        let clone = node.cloneNode(true),
          input = clone.querySelector("input"),
          label = clone.querySelector("label"),
          suffix = "_" + this.getRandom(); // Make sure the id is unique
        input.name = input.name + (input.type == "radio" ? radioSuffix : suffix);
        input.id = input.id + suffix;
        input.value = option.value;
        input.setAttribute("data-index", i);
        input.checked = option.selected;
        input.disabled = this.disabled;
        input.classList.add("ew-custom-option");
        if (isInvalid) input.classList.add("is-invalid");
        input.addEventListener("click", function () {
          var _bootstrap$Tooltip$ge;
          (_bootstrap$Tooltip$ge = bootstrap.Tooltip.getInstance(tbl)) == null || _bootstrap$Tooltip$ge.hide();
          let index = parseInt(this.getAttribute("data-index"), 10);
          if (self.type == "select-one") {
            for (let option of self.options) option.selected = false;
          }
          self.options[index].selected = this.checked;
          self.setAttribute("value", self.value);
          self.triggerChange();
        });
        label.innerHTML = option.text;
        label.htmlFor = input.id;
        let cell = document.createElement("div");
        cell.className = this.cellClass;
        cell.appendChild(clone);
        if (i % cols == 0) {
          row = document.createElement("div");
          row.className = this.rowClass;
        }
        row.append(cell);
        if (i % cols == cols - 1) {
          tbl.append(row);
        } else if (i == cnt - 1) {
          // Last
          for (let j = i % cols + 1; j < cols; j++) {
            let c = document.createElement("div");
            c.className = this.cellClass;
            row.append(c);
          }
          tbl.append(row);
        }
      });
      target.innerHTML = "";
      target.append(tbl);
      this.setAttribute("value", this.value);
    }

    /**
     * Set focus
     */;
    _proto.focus = function focus() {
      if (this.list) {
        var _this$target3;
        (_this$target3 = this.target) == null || (_this$target3 = _this$target3.querySelector("input")) == null || _this$target3.focus();
      } else {
        _HTMLElement.prototype.focus.call(this);
      }
    };
    return _createClass(SelectionList, [{
      key: "form",
      get:
      /**
       * Get form
       */
      function () {
        return this._internals.form;
      }

      /**
       * Get name
       */
    }, {
      key: "name",
      get: function () {
        return this.getAttribute("name");
      }
    }, {
      key: "targetId",
      get: function () {
        return this.getAttribute("data-target");
      }

      /**
       * Target
       */
    }, {
      key: "target",
      get: function () {
        return this.parentNode.querySelector("#" + this.targetId);
      }

      /**
       * Template id
       */
    }, {
      key: "templateId",
      get: function () {
        return this.getAttribute("data-template");
      }

      /**
       * Template
       */
    }, {
      key: "template",
      get: function () {
        return this.parentNode.querySelector("#" + this.templateId);
      }

      /**
       * Input element id (for AutoSuggest)
       */
    }, {
      key: "inputId",
      get: function () {
        return this.getAttribute("data-input");
      }

      /**
       * Input element (for AutoSuggest)
       */
    }, {
      key: "input",
      get: function () {
        return this.parentNode.querySelector("#" + this.inputId);
      }

      /**
       * Is list
       */
    }, {
      key: "list",
      get: function () {
        return this.options;
      }

      /**
       * Number of columns
       */
    }, {
      key: "columns",
      get: function () {
        var _ew;
        if ((_ew = ew) != null && _ew.IS_MOBILE) {
          return 1;
        } else {
          let cols = this.getAttribute("data-repeatcolumn");
          return cols ? parseInt(cols, 10) : 1;
        }
      }

      /**
       * Length
       */
    }, {
      key: "length",
      get: function () {
        return this.options.length;
      }

      /**
       * Get selected index
       */
    }, {
      key: "selectedIndex",
      get: function () {
        return this.options.findIndex(option => option.selected);
      }

      /**
       * Set selected index
       */,
      set: function (index) {
        let option = this.options[index];
        if (option) {
          this.options.forEach(option => option.selected = false);
          option.selected = true;
          this.render();
        }
      }

      /**
       * Type
       */
    }, {
      key: "type",
      get: function () {
        return this.getAttribute("data-type") || this.getAttribute("type");
      }

      /**
       * Get disabled
       */
    }, {
      key: "disabled",
      get: function () {
        return this.hasAttribute("disabled");
      }

      /**
       * Set disabled
       * @param {bool} val
       */,
      set: function (val) {
        val ? this.setAttribute("disabled", "") : this.removeAttribute("disabled");
      }

      /**
       * Multiple
       */
    }, {
      key: "multiple",
      get: function () {
        return this.type == "select-multiple";
      }

      /**
       * Get value
       * @returns {string}
       */
    }, {
      key: "value",
      get: function () {
        if (this.type == "select-one" || this.type == "select-multiple") {
          return this.values.join(ew.MULTIPLE_OPTION_SEPARATOR || ",");
        } else {
          return this.getAttribute("value");
        }
      }

      /**
       * Get value as array
       * @returns {string[]}
       */,
      set:
      /**
       * Set value
       * @param {string|string[]} val
       */
      function (val) {
        if (this.type == "select-one") {
          for (let option of this.options) option.selected = option.value == val;
        } else if (this.type == "select-multiple") {
          let ar;
          if (Array.isArray(val)) {
            // Array
            ar = val.map(v => v != null ? v : String(v));
          } else {
            // String
            val != null ? val : val = String(val);
            ar = val ? val.split(ew.MULTIPLE_OPTION_SEPARATOR || ",") : [];
          }
          for (let option of this.options) option.selected = ar.includes(String(option.value));
        } else {
          this.setAttribute("value", val);
        }
        this.render();
      }
    }, {
      key: "values",
      get: function () {
        if (this.type == "select-one" || this.type == "select-multiple") {
          return this.options.filter(option => option.selected).map(option => option.value);
        } else {
          let val = this.getAttribute("value");
          return val ? val.split(ew.MULTIPLE_OPTION_SEPARATOR || ",") : [];
        }
      }
    }], [{
      key: "observedAttributes",
      get:
      /**
       * Specify observed attributes so that attributeChangedCallback will work
       */
      function () {
        return ["class", "value", "disabled"];
      }

      /**
       * Form associcated
       */
    }, {
      key: "formAssociated",
      get: function () {
        return true;
      }
    }]);
  }(/*#__PURE__*/_wrapNativeSuper(HTMLElement));

  /*! js-cookie v3.0.5 | MIT */
  /* eslint-disable no-var */
  function assign(target) {
    for (var i = 1; i < arguments.length; i++) {
      var source = arguments[i];
      for (var key in source) {
        target[key] = source[key];
      }
    }
    return target;
  }
  /* eslint-enable no-var */

  /* eslint-disable no-var */
  var defaultConverter = {
    read: function (value) {
      if (value[0] === '"') {
        value = value.slice(1, -1);
      }
      return value.replace(/(%[\dA-F]{2})+/gi, decodeURIComponent);
    },
    write: function (value) {
      return encodeURIComponent(value).replace(/%(2[346BF]|3[AC-F]|40|5[BDE]|60|7[BCD])/g, decodeURIComponent);
    }
  };
  /* eslint-enable no-var */

  /* eslint-disable no-var */

  function init(converter, defaultAttributes) {
    function set(name, value, attributes) {
      if (typeof document === 'undefined') {
        return;
      }
      attributes = assign({}, defaultAttributes, attributes);
      if (typeof attributes.expires === 'number') {
        attributes.expires = new Date(Date.now() + attributes.expires * 864e5);
      }
      if (attributes.expires) {
        attributes.expires = attributes.expires.toUTCString();
      }
      name = encodeURIComponent(name).replace(/%(2[346B]|5E|60|7C)/g, decodeURIComponent).replace(/[()]/g, escape);
      var stringifiedAttributes = '';
      for (var attributeName in attributes) {
        if (!attributes[attributeName]) {
          continue;
        }
        stringifiedAttributes += '; ' + attributeName;
        if (attributes[attributeName] === true) {
          continue;
        }

        // Considers RFC 6265 section 5.2:
        // ...
        // 3.  If the remaining unparsed-attributes contains a %x3B (";")
        //     character:
        // Consume the characters of the unparsed-attributes up to,
        // not including, the first %x3B (";") character.
        // ...
        stringifiedAttributes += '=' + attributes[attributeName].split(';')[0];
      }
      return document.cookie = name + '=' + converter.write(value, name) + stringifiedAttributes;
    }
    function get(name) {
      if (typeof document === 'undefined' || arguments.length && !name) {
        return;
      }

      // To prevent the for loop in the first place assign an empty array
      // in case there are no cookies at all.
      var cookies = document.cookie ? document.cookie.split('; ') : [];
      var jar = {};
      for (var i = 0; i < cookies.length; i++) {
        var parts = cookies[i].split('=');
        var value = parts.slice(1).join('=');
        try {
          var found = decodeURIComponent(parts[0]);
          jar[found] = converter.read(value, found);
          if (name === found) {
            break;
          }
        } catch (e) {}
      }
      return name ? jar[name] : jar;
    }
    return Object.create({
      set,
      get,
      remove: function (name, attributes) {
        set(name, '', assign({}, attributes, {
          expires: -1
        }));
      },
      withAttributes: function (attributes) {
        return init(this.converter, assign({}, this.attributes, attributes));
      },
      withConverter: function (converter) {
        return init(assign({}, this.converter, converter), this.attributes);
      }
    }, {
      attributes: {
        value: Object.freeze(defaultAttributes)
      },
      converter: {
        value: Object.freeze(converter)
      }
    });
  }
  var api = init(defaultConverter, {
    path: '/'
  });

  const genUUID = () => {
      // best case, `crypto.randomUUID` is available
      if (globalThis.crypto?.randomUUID) {
          return globalThis.crypto.randomUUID();
      }
      const bytes = new Uint8Array(16);
      if (globalThis.crypto?.getRandomValues) {
          // `crypto.getRandomValues` is available even in non-secure contexts
          globalThis.crypto.getRandomValues(bytes);
      }
      else {
          // fallback to Math.random, if the Crypto API is completely missing
          for (let i = 0; i < bytes.length; i++) {
              bytes[i] = Math.floor(Math.random() * 256);
          }
      }
      bytes[6] = (bytes[6] & 0x0f) | 0x40; // Set the 4 most significant bits to 0100
      bytes[8] = (bytes[8] & 0x3f) | 0x80; // Set the 2 most significant bits to 10
      const hexValues = [];
      bytes.forEach((byte) => {
          hexValues.push(byte.toString(16).padStart(2, '0'));
      });
      return (hexValues.slice(0, 4).join('') +
          '-' +
          hexValues.slice(4, 6).join('') +
          '-' +
          hexValues.slice(6, 8).join('') +
          '-' +
          hexValues.slice(8, 10).join('') +
          '-' +
          hexValues.slice(10).join(''));
  };

  // Default sessionStorage key to store the tab ID in.
  const defaultKey = 'unique-tab-id';
  // Used for in-memory storage when `sessionStorage` is not supported.
  let tabId = null;
  /**
   * Returns a unique ID for each browser tab. This can be used to construct a
   * tab scoped DB name. This allows data to sync between tabs by using one
   * named SQLite database file per tab. This is sub-optimal compared with
   * sharing a database but avoids concurrent database access.
   *
   * Handles duplicate tabs with some potential for false positives.
   * False positives result in a new tabID which means a new database name
   * and thus additional data transfer and storage.
   *
   * Uses window.sessionStorage. Some browsers disable access to sessionStorage
   * even when saying its available (e.g.: as a result of disabling third party
   * cookies o_O). So we handle this by falling back to an in-memory tabId
   * singleton -- which means each page load syncs data into a new DB.
   *
   * Other platforms can pass in a storage implementation or default to the
   * in-memory tabId singleton.
   */
  const uniqueTabId = (opts = {}) => {
      const key = opts.key ?? defaultKey;
      // Use sessionStorage as the default storage if available.
      let storage;
      if (opts.storage !== undefined) {
          storage = opts.storage;
      }
      else {
          try {
              storage = window.sessionStorage;
          }
          catch {
              // We catch errors in the Storage use (we have to to handle
              // browser security where they pretend Storage is available
              // but then throw errors when you use it) so this cast isn't
              // as bad as it looks.
              storage = {};
          }
      }
      let navEntries;
      if (opts.navEntries !== undefined) {
          navEntries = opts.navEntries;
      }
      else {
          try {
              navEntries = window.performance.getEntriesByType('navigation');
          }
          catch {
              navEntries = [];
          }
      }
      // Lookup the tabId
      let existingTabId;
      let usedSessionStorage;
      try {
          existingTabId = storage.getItem(key);
          usedSessionStorage = true;
      }
      catch {
          existingTabId = tabId;
          usedSessionStorage = false;
      }
      const hasExisting = existingTabId !== null;
      // If we got the tab ID from memory then always use it.
      if (hasExisting && !usedSessionStorage) {
          return {
              tabId: existingTabId,
              hasExisting: true,
              usingExisting: true,
          };
      }
      // If it's not a duplicate tab then use it.
      //
      // Note that only the current document is included in the [performance timeline][1],
      // so there is only one PerformanceNavigationTiming object in the navEntries array.
      //
      // [1]: https://developer.mozilla.org/en-US/docs/Web/API/PerformanceNavigationTiming
      const navEntry = navEntries && navEntries.length ? navEntries[0] : { type: null };
      const mayBeDuplicate = navEntry.type === 'back_forward';
      if (hasExisting && !mayBeDuplicate) {
          return {
              tabId: existingTabId,
              hasExisting: true,
              usingExisting: true,
              mayBeDuplicate: false,
          };
      }
      // Otherwise generate, store and return a new one.
      const newTabId = genUUID();
      try {
          storage.setItem(key, newTabId);
      }
      catch {
          tabId = newTabId;
      }
      return {
          tabId: newTabId,
          hasExisting: hasExisting,
          usingExisting: false,
          mayBeDuplicate: mayBeDuplicate,
      };
  };

  // Define SelectionList
  customElements.define("selection-list", SelectionList);

  // Select2 templateResult/templateSelection
  let noop = () => {},
    templateCallback = o => (o.element instanceof HTMLOptionElement ? o.element.innerHTML : "") || o.text;
  let ew$1 = {
    SelectionListOption: SelectionListOption,
    PAGE_ID: "",
    // Page ID // To be updated in page
    RELATIVE_PATH: "",
    // Relative path // To be updated in page
    MULTIPLE_OPTION_SEPARATOR: ",",
    GENERATE_PASSWORD_UPPERCASE: true,
    GENERATE_PASSWORD_LOWERCASE: true,
    GENERATE_PASSWORD_NUMBER: true,
    GENERATE_PASSWORD_SPECIALCHARS: 1,
    // Max. number of special characters (number|boolean)
    CONFIRM_CANCEL: true,
    UNFORMAT_YEAR: 50,
    LAZY_LOAD_DELAY: 0,
    AJAX_DELAY: 5,
    LOOKUP_DELAY: 250,
    MAX_OPTION_COUNT: 3,
    MIN_SESSION_KEEP_ALIVE_INTERVAL: 60,
    // Seconds
    NUMBERING_SYSTEM: "",
    SERVICE_WORKER: "sw.js",
    Cookies: api,
    uniqueTabId: uniqueTabId(),
    Language,
    // Class
    language: new Language(),
    // Language object
    vars: null,
    maps: {},
    addOptionDialog: null,
    emailDialog: null,
    importDialog: null,
    modalDialog: null,
    modalLookupDialog: null,
    ajaxBatchSize: 5,
    autoSuggestSettings: {
      highlight: true,
      hint: true,
      minLength: 1,
      debounce: 250,
      delay: 250,
      // For loading more results
      templates: {},
      // Custom templates for Typeahead (notFound, pending, header, footer, suggestion)
      classNames: {
        menu: "tt-menu dropdown-menu",
        input: 'tt-input form-control',
        dataset: "tt-dataset",
        suggestion: "tt-suggestion dropdown-item",
        cursor: "active"
      }
    },
    lightboxSettings: {
      transition: "none",
      photo: true,
      opacity: 0.5
    },
    calendarOptions: {
      showViewPageOnEventClick: false,
      useContextMenu: true,
      usePopover: true,
      popoverViews: ["dayGridMonth"],
      fullCalendarOptions: {
        headerToolbar: {
          start: "prev,next today",
          center: "title",
          end: "dayGridMonth,timeGridWeek,timeGridDay,listWeek"
        },
        longPressDelay: 1250,
        eventSelectLongPressDelay: 750,
        themeSystem: "bootstrap5",
        // Bootstrap5 theme with Font Awesome icons
        // See https://fullcalendar.io/docs/eventTimeFormat
        // eventTimeFormat: { hour: 'numeric', minute: '2-digit', omitZeroMinute: true, meridiem: 'narrow' },
        initialView: "dayGridMonth",
        dayMaxEventRows: true,
        timeZone: "UTC",
        droppable: true,
        editable: true
      },
      eventPopoverOptions: {
        placement: "auto",
        trigger: "hover",
        // Either "hover" or "click"
        html: true
      }
    },
    uploadOptions: {
      // See https://github.com/blueimp/jQuery-File-Upload/wiki/Options
      cropperOptions: {
        // See https://github.com/fengyuanchen/cropperjs/blob/e969348d313dafe3416926125b21388cc67cefb1/README.md#options
        autoCropArea: 1,
        // Define the automatic cropping area size (between 0 and 1)
        viewMode: 2 // Restrict the minimum canvas size to fit within the container
      },
      cropperCanvasOptions: {
        // See https://github.com/fengyuanchen/cropperjs/blob/e969348d313dafe3416926125b21388cc67cefb1/README.md#getcroppedcanvasoptions
        minWidth: 256,
        minHeight: 256,
        maxWidth: 4096,
        maxHeight: 4096,
        fillColor: "#fff",
        imageSmoothingQuality: "high" // Quality of image smoothing
      }
    },
    importUploadOptions: {
      maxFileSize: 10000000,
      maxNumberOfFiles: 10
    },
    sweetAlertSettings: {
      showClass: {
        popup: "swal2-noanimation",
        // Disable popup animation
        backdrop: "swal2-noanimation",
        // Disable backdrop animation
        icon: "" // Disable icon animation
      },
      hideClass: {
        popup: "",
        // Disable popup animation
        backdrop: "",
        // Disable backdrop animation
        icon: "" // Disable icon animation
      },
      customClass: {
        container: "ew-swal2-container",
        popup: "ew-swal2-popup",
        header: "ew-swal2-header",
        title: "ew-swal2-title",
        closeButton: "ew-swal2-close-button",
        icon: "ew-swal2-icon",
        image: "ew-swal2-image",
        htmlContainer: "ew-swal2-html-container",
        content: "ew-swal2-content",
        input: "form-control ew-swal2-input",
        inputLabel: "ew-swal2-input-label",
        validationMessage: "ew-swal2-validation-message",
        actions: "ew-swal2-actions",
        confirmButton: "btn btn-primary ew-swal2-confirm-button",
        denyButton: "btn btn-danger ew-swal2-deny-button",
        cancelButton: "btn btn-secondary ew-swal2-cancel-button",
        loader: "ew-swal2-loader",
        footer: "ew-swal2-footer"
      }
    },
    selectOptions: {
      // Select2 options
      allowClear: true,
      theme: "bootstrap5",
      width: "style",
      minimumResultsForSearch: 20,
      escapeMarkup: v => v,
      templateResult: templateCallback,
      templateSelection: templateCallback,
      // Custom options
      debounce: 250,
      // For ajax.delay, see https://select2.org/data-sources/ajax#rate-limiting-requests
      customOption: true,
      containerClass: "d-sm-table",
      rowClass: "d-sm-table-row",
      cellClass: "d-sm-table-cell text-nowrap",
      iconClass: "form-check-label"
    },
    selectMinimumInputLength: 1,
    modalLookupOptions: {
      // Select2 options
      allowClear: true,
      theme: "bootstrap5",
      width: "100%",
      escapeMarkup: v => v,
      templateResult: templateCallback,
      templateSelection: templateCallback,
      closeOnSelect: false,
      minimumInputLength: 0,
      dropdownCssClass: "ew-modal-dropdown",
      // Custom options
      modal: true,
      debounce: 250,
      // For ajax.delay, see https://select2.org/data-sources/ajax#rate-limiting-requests
      draggableOptions: {
        cursor: "grabbing"
      } // See https://api.jqueryui.com/draggable/
    },
    filterOptions: {
      // Select2 options
      allowClear: true,
      theme: "bootstrap5",
      width: "100%",
      escapeMarkup: v => v,
      templateResult: templateCallback,
      templateSelection: templateCallback,
      closeOnSelect: false,
      minimumInputLength: 0,
      dropdownAutoWidth: true,
      dropdownCssClass: "ew-filter-dropdown",
      // Custom options
      debounce: 250,
      // For ajax.delay, see https://select2.org/data-sources/ajax#rate-limiting-requests
      customOption: true,
      columns: 1,
      multiple: true,
      preventScroll: true,
      // For focusing the search box
      containerClass: "container",
      rowClass: "row",
      cellClass: "col text-nowrap",
      iconClass: "form-check-label"
    },
    importTabulatorOptions: {
      height: 300
    },
    // See http://tabulator.info/docs/5.1/options
    toastOptions: {
      position: "topRight" // topRight|topLeft|bottomRight|bottomLeft
    },
    tippyOptions: {
      theme: "translucent"
    },
    DOMPurifyConfig: {},
    sanitize: function (str) {
      return DOMPurify.sanitize(str, this.DOMPurifyConfig);
    },
    sanitizeHtml: function (html) {
      return DOMPurify.sanitize(html, {
        RETURN_TRUSTED_TYPE: true
      });
    },
    draggableOptions: {
      cursor: "grabbing"
    },
    // See https://api.jqueryui.com/draggable/
    queryBuilderOptions: {
      allowViewRules: false,
      // Show view rules button
      allowClearRules: false // Show clear rules button
    },
    // Query builder options
    queryBuilderPlugins: {
      // Query builder plugins
      "filter-description": null,
      "unique-filter": null,
      // "invert": null,
      "not-group": null
    },
    queryBuilderErrorClass: "invalid-tooltip",
    // "invalid-tooltip" or "invalid-feedback"
    tooltipOptions: {
      placement: "bottom",
      customClass: "ew-custom-tooltip",
      sanitizeFn: null
    },
    popoverOptions: {
      html: true,
      customClass: "ew-custom-popover",
      sanitizeFn: null
    },
    loginWizardOptions: {
      autoAdjustHeight: true,
      // Automatically adjust content height
      transition: {
        animation: "slideHorizontal",
        // Animation effect on navigation, none|fade|slideHorizontal|slideVertical|slideSwing|css(Animation CSS class also need to specify)
        speed: "400",
        // Animation speed, not used if animation is "css"
        easing: "" // Animation easing, not supported without a jQuery easing plugin. Not used if animation is "css"
      }
    },
    scrollIntoViewOptions: {
      block: "center",
      inline: "nearest"
    },
    securityCodeDebounce: 1000,
    PDFObjectOptions: {},
    chartConfig: {},
    spinnerClass: "spinner-border text-primary ew-spinner",
    // spinner-border or spinner-grow
    submitSpinnerClass: "ew-submit-spinner spinner-border spinner-border-sm",
    // spinner-border or spinner-grow (for submit button)
    screenMediaQuery: "(min-width: 768px)",
    // Media query string for minimum screen width (not mobile), should matches $screen-sm-min
    callbacks: {},
    // Callbacks
    jsTemplates: [],
    // JsRender templates
    jsRenderHelpers: {},
    // JsRender helpers
    jsRenderAttributes: ["src", "href", "title", "data-bs-title"],
    // Attributes supporting built-in JsRender tags
    autoHideSuccessMessage: true,
    autoHideSuccessMessageDelay: 5000,
    searchOperatorChange: noop,
    setLanguage: noop,
    addOptionDialogShow: noop,
    importDialogShow: noop,
    toggleSearchOperator: noop,
    togglePassword: noop,
    sort: noop,
    selectKey: noop,
    export: noop,
    exportWithCharts: noop,
    setSearchType: noop,
    emailDialogShow: noop,
    selectAll: noop,
    selectAllKeys: noop,
    submitAction: noop,
    addGridRow: noop,
    confirmDelete: () => false,
    deleteGridRow: () => false,
    // Events
    eventListeners: new Map(),
    // Stores event listeners
    stickyEvents: new Map(),
    // Stores the last emitted detail for sticky events
    on(event, callback, options) {
      if (!this.eventListeners.has(event)) {
        this.eventListeners.set(event, []);
      }
      this.eventListeners.get(event).push(callback);
      document.addEventListener(event, callback, options);
      // If the event has a stored sticky value, trigger the callback immediately
      if (this.stickyEvents.has(event)) {
        callback(new CustomEvent(event, {
          detail: this.stickyEvents.get(event)
        }));
      }
    },
    once(event, callback, options) {
      const onceCallback = e => {
        callback(e);
        this.off(event, onceCallback); // Remove the listener after execution
      };
      this.on(event, onceCallback, options);
    },
    off(event, callback) {
      if (this.eventListeners.has(event)) {
        const callbacks = this.eventListeners.get(event).filter(cb => cb !== callback);
        if (callbacks.length > 0) {
          this.eventListeners.set(event, callbacks);
        } else {
          this.eventListeners.delete(event);
        }
        document.removeEventListener(event, callback);
      }
    },
    emit(eventName, detail, isSticky) {
      if (detail === void 0) {
        detail = {};
      }
      if (isSticky === void 0) {
        isSticky = true;
      }
      // isSticky defaults to true
      const event = new CustomEvent(eventName, {
        detail
      });
      document.dispatchEvent(event);
      if (isSticky) {
        // If the event is sticky, store its detail
        this.stickyEvents.set(eventName, detail);
      }
    },
    clearSticky(eventName) {
      // Clear a sticky event's stored value
      this.stickyEvents.delete(eventName);
    }
  };

  /**
   * Color mode toggler (based on https://getbootstrap.com/docs/5.3/customize/color-modes/#javascript)
   */
  ew$1.getStoredTheme = () => localStorage.getItem("theme");
  ew$1.setStoredTheme = theme => localStorage.setItem("theme", theme);
  ew$1.getPreferredTheme = () => {
    const storedTheme = ew$1.getStoredTheme();
    if (storedTheme) return storedTheme;
    return window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
  };
  ew$1.isDark = () => ew$1.getPreferredTheme() == "dark";
  ew$1.updateIframes = () => document.querySelectorAll(".ew-iframe").forEach(iframe => {
    const theme = ew$1.getPreferredTheme();
    const el = iframe.contentWindow.document.documentElement;
    el.setAttribute("data-bs-theme", theme); // For Bootstrap 5.3+
    el.classList.toggle("dark", theme == "dark"); // For Tailwind
  });
  ew$1.setTheme = theme => {
    if (theme !== "light" && theme !== "dark")
      // Auto
      theme = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
    if (new URL(window.location).searchParams.get("export") == "print")
      // Print
      theme = "light";
    document.documentElement.setAttribute("data-bs-theme", theme); // For Bootstrap 5.3+
    ew$1.updateIframes();
    const event = new CustomEvent("changetheme", {
      detail: theme
    });
    document.dispatchEvent(event);
  };
  ew$1.setTheme(ew$1.getPreferredTheme());
  ew$1.showActiveTheme = function (theme, focus) {
    if (focus === void 0) {
      focus = false;
    }
    const themeSwitcher = document.querySelector("#bd-theme");
    if (!themeSwitcher) return;
    const themeSwitcherText = document.querySelector("#bd-theme-text");
    const activeThemeIcon = document.querySelector(".theme-icon-active");
    const btnToActive = document.querySelector("[data-bs-theme-value='" + theme + "']");
    if (!btnToActive) return;
    const activeBtnIcon = btnToActive.querySelector(".theme-icon");
    document.querySelectorAll("[data-bs-theme-value]").forEach(element => {
      element.classList.remove("active");
      element.setAttribute("aria-pressed", "false");
    });
    btnToActive.classList.add("active");
    btnToActive.setAttribute("aria-pressed", "true");
    let classes = Array.from(activeThemeIcon.classList.values());
    for (const cls of classes) {
      if (cls.startsWith("fa-") || cls.startsWith("bi-")) activeThemeIcon.classList.remove(cls);
    }
    let iterator = activeBtnIcon == null ? void 0 : activeBtnIcon.classList.values();
    for (const cls of iterator) {
      if (cls.startsWith("fa-") || cls.startsWith("bi-")) activeThemeIcon.classList.add(cls);
    }
    const themeSwitcherLabel = themeSwitcherText.textContent + " (" + btnToActive.dataset.bsThemeValue + ")";
    themeSwitcher.setAttribute("aria-label", themeSwitcherLabel);
    if (focus) themeSwitcher.focus();
  };
  window.matchMedia("(prefers-color-scheme: dark)").addEventListener("change", () => {
    const storedTheme = ew$1.getStoredTheme();
    if (storedTheme !== "light" && storedTheme !== "dark") ew$1.setTheme(ew$1.getPreferredTheme());
  });
  ew$1.initThemes = () => {
    const theme = ew$1.getPreferredTheme();
    ew$1.showActiveTheme(theme);
    ew$1.updateIframes();
    document.querySelectorAll("[data-bs-theme-value]").forEach(toggle => {
      toggle.addEventListener("click", () => {
        let theme = toggle.getAttribute("data-bs-theme-value");
        ew$1.setStoredTheme(theme);
        ew$1.setTheme(theme);
        ew$1.showActiveTheme(theme, true);
      });
    });
  };

  // Panel selectors
  ew$1.panelSelectors = [".user-panel", ".ew-grid-upper-panel", ".ew-grid-lower-panel", ".ew-multi-column-row > * > .card > .card-header", ".ew-multi-column-row > * > .card > .card-footer"];

  /**
   * Init panel/header/footer
   *
   * @param {HTMLElement} el - Element
   */
  ew$1.initPanel = el => {
    if (el.dataset.isset) return;
    let html = "";
    for (let child of el.children) {
      html = child.innerHTML.trim();
      if (html !== "") break;
    }
    if (html === "") el.classList.add("d-none");
    el.dataset.isset = true;
  };

  /**
   * Init panels/headers/footers
   */
  ew$1.initPanels = el => el == null ? void 0 : el.querySelectorAll(ew$1.panelSelectors.join(",")).forEach(ew$1.initPanel);

  /**
   * Waits for elements matching the given selectors to appear in the DOM,
   * then calls the callback once on each matched element and stops watching.
   * Polls using requestAnimationFrame until elements are found or DOMContentLoaded fires.
   * Does nothing if the DOM is already loaded.
   *
   * @param {string|string[]} selectors - A CSS selector string or an array of selectors to match elements.
   * @param {(element: Element) => void} callback - Function to call once for each matched element.
   */
  ew$1.waitForAndRunOnce = function (selectors, callback) {
    const query = Array.isArray(selectors) ? selectors.join(",") : selectors;
    let rafId = null;
    function stop() {
      if (rafId !== null) {
        cancelAnimationFrame(rafId);
        rafId = null;
      }
      document.removeEventListener("DOMContentLoaded", stop);
    }
    function check() {
      const elements = document.querySelectorAll(query);
      if (elements.length > 0) {
        elements.forEach(callback);
        stop();
        return;
      }
      if (document.readyState === "loading") {
        rafId = requestAnimationFrame(check);
      } else {
        // DOMContentLoaded fired but elements not found
        stop();
      }
    }
    if (document.readyState === "loading") {
      check();
      document.addEventListener("DOMContentLoaded", stop);
    }
  };

  // Run the watcher with your selectors and initPanel callback
  ew$1.waitForAndRunOnce(ew$1.panelSelectors, ew$1.initPanel);

  // DOM content loaded
  document.addEventListener("DOMContentLoaded", () => ew$1.emit("dom"));

  // Global client script
  ew$1.on("head", () => {
    var _window$jsrender;
    (_window$jsrender = window.jsrender) == null || _window$jsrender.views.helpers({
      language: ew$1.language,
      phrase: ew$1.language.phrase.bind(ew$1.language),
      sprintf: ew$1.sprintf,
      ...ew$1.jsRenderHelpers
    });
    // ew.clientScript();
  });

  // Global startup script
  ew$1.on("foot", () => {
    // ew.startupScript();
    ew$1.emit("load");
  });

  // Deep merge two or more objects into the first object
  let deepAssign = function () {
    let extended = {},
      overwrite = true,
      i = 0;
    // Check if overwrite
    if (arguments[0] === false) {
      // First argument is boolean
      overwrite = false;
      i++;
    }
    if (typeof arguments[i] == "object") {
      // Get first object
      extended = arguments[i];
      i++;
    }
    // Merge the object into the extended object
    let merge = function (obj) {
      for (let prop in obj) {
        if (obj.hasOwnProperty(prop)) {
          // If property is an object, merge properties
          if (typeof obj[prop] == "object" && !Array.isArray(obj[prop]) && obj[prop] !== null) {
            // Note: Type of array/null is "object"
            extended[prop] = deepAssign(overwrite, extended[prop], obj[prop]);
          } else {
            if (overwrite) {
              extended[prop] = obj[prop];
            } else {
              if (!extended.hasOwnProperty(prop)) extended[prop] = obj[prop];
            }
          }
        }
      }
    };
    // Loop through each object and merge
    for (; i < arguments.length; i++) {
      if (typeof arguments[i] == "object") merge(arguments[i]);
    }
    return extended;
  };
  ew$1.deepAssign = deepAssign;

  /**
   * Render a single JsRender template by element or ID.
   *
   * @param {Element|string} templateOrId - Template element or ID of the template
   */
  ew$1.renderJsTemplate = function (templateOrId) {
    var _template$dataset;
    let template;
    if (typeof templateOrId === "string") {
      template = document.getElementById(templateOrId);
      if (!template) {
        console.warn("Template with id=\"" + templateOrId + "\" not found.");
        return;
      }
    } else {
      template = templateOrId;
    }
    if (!(template instanceof Element)) {
      console.warn("Invalid template provided to ew.renderJsTemplate");
      return;
    }
    let options = Object.assign({}, (_template$dataset = template.dataset) != null ? _template$dataset : {}),
      {
        name,
        manual,
        allowCode
      } = options;
    manual = manual === ""; // Boolean data-* exists
    allowCode = allowCode === "";
    name || (name = "js-template-" + ew$1.jsTemplates.length);
    if (!jsrender.render[name]) {
      let args = {
        jsTemplate: jsrender.templates(name, {
          markup: template.text,
          allowCode
        }),
        template,
        ...options
      };
      ew$1.jsTemplates.push(args);
      if (!manual) ew$1.renderTemplate(args);
    }
  };

  /**
   * Render all JsRender templates inside given element or document.
   *
   * @param {Event|Element} [e=document] - Event or element container to search for templates
   */
  ew$1.renderJsTemplates = function (e) {
    var _e$target;
    let el = (_e$target = e == null ? void 0 : e.target) != null ? _e$target : document;
    Array.from(el.querySelectorAll(".ew-js-template")).sort((a, b) => {
      a = parseInt(a.dataset.seq, 10) || 0;
      b = parseInt(b.dataset.seq, 10) || 0;
      return a - b;
    }).forEach((template, index) => ew$1.renderJsTemplate(template, index));
  };

  /**
   * Render JsRender template
   *
   * @param args - Object { jsTemplate, template, name, method, target, allowCode, data, callback }
   * @returns
   */
  ew$1.renderTemplate = function (args) {
    let $ = jQuery,
      $document = $(document);
    $document.trigger("rendertemplate", [args]);
    let {
        template,
        jsTemplate,
        method,
        target,
        data,
        callback
      } = args,
      $target = $(target || template.parentNode);
    if (typeof data == "string") data = ew$1.vars[data] || window[data]; // Get data from ew.vars or global
    data = typeof data == "object" ? data : {};
    let html = jsTemplate.render(data);
    if (html && method && target)
      // Render by specified method to target
      $(html)[method]($target);else if (html && !method && target)
      // No method, render as inner HTML of target
      $target.html(html);else if (html && !method && !target)
      // No method and target, render locally
      $target.append(html);
    if (callback) {
      let fn = typeof callback == "string" ? ew$1.callbacks[callback] || ew$1[callback] || window[callback] : callback;
      if (typeof fn == "function") {
        let e = $.Event("templaterendered", {
          target: $target[0],
          detail: args
        });
        try {
          return fn(e);
        } finally {
          $document.trigger(e);
        }
      }
    }
  };

  /**
   * Update JsRender template
   *
   * @param {string} name - Name of template
   * @param {Object} [data] - New data
   * @returns
   */
  ew$1.refreshTemplate = function (name, data) {
    let args = ew$1.jsTemplates.find(t => t.name == name);
    if (args) {
      if (typeof data == "object") args.data = data;
      if (["config2fa", "select2fa", "verify2fa"].includes(name))
        // DN
        args.callback = () => $.later(200, null, () => $("#login-wizard").smartWizard("fixHeight")); // Fix login wizard height
      return ew$1.renderTemplate(args);
    }
    return null;
  };

  // Overlay template
  ew$1.overlayTemplate = function () {
    return '<div class="overlay"><div class="' + this.spinnerClass + '" role="status"><span class="visually-hidden">' + this.language.phrase("Loading") + '</span></div></div>';
  };

  // Spinner template
  ew$1.spinnerTemplate = function () {
    return '<div class="' + this.spinnerClass + ' m-3 ew-loading" role="status"><span class="visually-hidden">' + this.language.phrase("Loading") + '</span></div>';
  };

  // Write browser tab ID cooke
  ew$1.writeTabIdCookie = function () {
    if (ew$1.Cookies.get(ew$1.PROJECT_NAME + "_TabId") != ew$1.uniqueTabId.tabId) {
      //console.log("Tab ID: " + ew.uniqueTabId.tabId); // Uncomment to debug
      ew$1.Cookies.set(ew$1.PROJECT_NAME + "_TabId", ew$1.uniqueTabId.tabId, {
        sameSite: ew$1.COOKIE_SAMESITE,
        secure: ew$1.COOKIE_SECURE
      });
    }
  };
  ew$1.on("clientvars", ew$1.writeTabIdCookie);
  window.addEventListener("visibilitychange", () => document.visibilityState != "visible" || ew$1.writeTabIdCookie());

  // Card Refresh
  ew$1.on("head", () => adminlte.CardRefresh.overlayTemplate = ew$1.overlayTemplate());

  // Navbar
  ew$1.waitForAndRunOnce("#ew-navbar", () => {
    ew$1.renderJsTemplate("navbar-menu-items");
    // Select all toggle elements with href inside the dropdown menu
    document.getElementById("ew-navbar").querySelectorAll(".dropdown-submenu.dropdown-hover [data-bs-toggle='dropdown'][href]").forEach(toggle => {
      toggle.addEventListener("click", function (event) {
        let href = toggle.getAttribute("href");
        if (event.target.tagName === "SPAN" && href && href !== "#" && !href.startsWith("javascript:")) {
          window.location = href;
        }
      });
    });
  });

  // Sidebar
  ew$1.waitForAndRunOnce("#ew-sidebar", () => ew$1.renderJsTemplate("menu-items"));

  // Initiate sidebar
  ew$1.initiateSidebar = function (e) {
    var _e$target2, _e$target3;
    (_e$target2 = e.target) == null || _e$target2.querySelectorAll("[data-widget=treeview]").forEach(el => $(el).Treeview());
    (_e$target3 = e.target) == null || _e$target3.querySelectorAll("[data-widget=sidebar-search]").forEach(el => $(el).SidebarSearch());
  };

  return ew$1;

})();
