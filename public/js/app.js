(window["webpackJsonp"] = window["webpackJsonp"] || []).push([["/js/app"],{

/***/ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/consultentries.vue?vue&type=script&lang=js&":
/*!*************************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib??ref--4-0!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/components/consultentries.vue?vue&type=script&lang=js& ***!
  \*************************************************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
//
//
//
//
//
/* harmony default export */ __webpack_exports__["default"] = ({
  props: ['entries'],
  name: "consultentries",
  data: function data() {
    return {
      loaddata: {}
    };
  },
  mounted: function mounted() {
    this.fetchData();
    this.drawGraph();
  },
  methods: {
    fetchData: function fetchData() {
      this.loaddatadata = JSON.parse(this.entries);
    },
    drawGraph: function drawGraph() {}
  }
});

/***/ }),

/***/ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/editproject.vue?vue&type=script&lang=js&":
/*!**********************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib??ref--4-0!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/components/editproject.vue?vue&type=script&lang=js& ***!
  \**********************************************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
/* harmony default export */ __webpack_exports__["default"] = ({
  props: ['project', 'data'],
  computed: {
    'formattedinputstring': function formattedinputstring() {
      console.log("computed formattedstring");
      return JSON.stringify(this.thisproject.inputs);
    }
  },
  watch: {
    'thisproject.ninputs': function thisprojectNinputs(newVal, oldVal) {
      if (!this.firstLoading) {
        console.log(this.firstLoading);
        console.log("token number watcher fired");

        if (newVal < 0 || oldVal < 0) {
          newVal = 0;
          oldVal = 0;
        }

        var direction = newVal - oldVal;

        if (direction > 0) {
          var inputtemplate = {
            name: "",
            type: "",
            numberofanswer: 0,
            mandatory: true,
            answers: []
          };

          for (var i = 0; i < direction; i++) {
            this.thisproject.inputs.push(inputtemplate);
          }
        } else if (newVal == 0) {
          // special case
          this.thisproject.inputs = [];
        } else {
          // decrease
          for (var i = 0; i < Math.abs(direction); i++) {
            this.thisproject.inputs.pop();
          }
        }
      } else {
        this.firstLoading = false;
      }
    }
  },
  data: function data() {
    return {
      response: '',
      firstLoading: true,
      thisproject: {
        edit: false,
        name: "",
        ninputs: 0,
        inputs: [],
        config: window.inputs,
        response: "",
        media: "",
        places: "",
        cp: "",
        checkedmedia: [],
        checkedplaces: [],
        checkedcp: []
      }
    };
  },
  created: function created() {
    this.fillInputs();
  },
  methods: {
    fillInputs: function fillInputs() {
      console.log(this.project);
      this.thisproject.inputs = this.project.inputs;
      this.thisproject.ninputs = this.project.inputs.length;
      this.thisproject.name = this.project.name;
      this.thisproject.description = this.project.description;
      this.thisproject.duration = this.project.duration;
      this.thisproject.checkedmedia = this.project.media;
      this.thisproject.checkedplaces = this.project.places;
      this.thisproject.checkedcp = this.project.communication_partners;
    },
    save: function save() {
      var _this = this;

      var submitObject = {};

      _.merge(submitObject, {
        id: this.project.id
      }, {
        name: this.thisproject.name
      }, {
        description: this.thisproject.description
      }, {
        duration: this.thisproject.duration
      }, {
        inputs: this.formattedinputstring
      }, {
        media: this.thisproject.checkedmedia
      }, {
        places: this.thisproject.checkedplaces
      }, {
        cp: this.thisproject.checkedcp
      });

      window.axios.patch('../projects/' + submitObject.id, submitObject).then(function (response) {
        if (response.message) _this.response = response.message;else {
          _this.$snackbar.open(response.data);
        }
      }).catch(function (error) {
        console.log(error);
        if (error.message) _this.$snackbar.open(error.message);else {
          _this.$snackbar.open(error.response.data);
        }
      });
    },
    selectAllHandler: function selectAllHandler(which) {
      var items = document.getElementsByName(which);
      var element = document.getElementById(which + "i");
      console.log(which);
      console.log(element.checked);
      var varname = 'checked' + which;

      if (!element.checked) {
        for (var i = 0; i < items.length; i++) {
          if (items[i].type == 'checkbox') items[i].checked = false;
        }

        console.log(varname);
        this.thisproject[varname] = [];
      } else {
        for (var i = 0; i < items.length; i++) {
          if (items[i].type == 'checkbox') {
            items[i].checked = true;
          }

          this.thisproject[varname] = this.data[which];
        }
      }
    }
  }
});

/***/ }),

/***/ "./node_modules/buefy/dist/buefy.js":
/*!******************************************!*\
  !*** ./node_modules/buefy/dist/buefy.js ***!
  \******************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

/*! Buefy v0.7.4 | MIT License | github.com/buefy/buefy */ 
(function webpackUniversalModuleDefinition(root, factory) {
	if(true)
		module.exports = factory(__webpack_require__(/*! vue */ "./node_modules/vue/dist/vue.common.js"));
	else {}
})(typeof self !== 'undefined' ? self : this, function(__WEBPACK_EXTERNAL_MODULE_17__) {
return /******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, {
/******/ 				configurable: false,
/******/ 				enumerable: true,
/******/ 				get: getter
/******/ 			});
/******/ 		}
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "/";
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 69);
/******/ })
/************************************************************************/
/******/ ([
/* 0 */
/***/ (function(module, exports) {

/* globals __VUE_SSR_CONTEXT__ */

// this module is a runtime utility for cleaner component module output and will
// be included in the final webpack user bundle

module.exports = function normalizeComponent (
  rawScriptExports,
  compiledTemplate,
  injectStyles,
  scopeId,
  moduleIdentifier /* server only */
) {
  var esModule
  var scriptExports = rawScriptExports = rawScriptExports || {}

  // ES6 modules interop
  var type = typeof rawScriptExports.default
  if (type === 'object' || type === 'function') {
    esModule = rawScriptExports
    scriptExports = rawScriptExports.default
  }

  // Vue.extend constructor export interop
  var options = typeof scriptExports === 'function'
    ? scriptExports.options
    : scriptExports

  // render functions
  if (compiledTemplate) {
    options.render = compiledTemplate.render
    options.staticRenderFns = compiledTemplate.staticRenderFns
  }

  // scopedId
  if (scopeId) {
    options._scopeId = scopeId
  }

  var hook
  if (moduleIdentifier) { // server build
    hook = function (context) {
      // 2.3 injection
      context =
        context || // cached call
        (this.$vnode && this.$vnode.ssrContext) || // stateful
        (this.parent && this.parent.$vnode && this.parent.$vnode.ssrContext) // functional
      // 2.2 with runInNewContext: true
      if (!context && typeof __VUE_SSR_CONTEXT__ !== 'undefined') {
        context = __VUE_SSR_CONTEXT__
      }
      // inject component styles
      if (injectStyles) {
        injectStyles.call(this, context)
      }
      // register component module identifier for async chunk inferrence
      if (context && context._registeredComponents) {
        context._registeredComponents.add(moduleIdentifier)
      }
    }
    // used by ssr in case component is cached and beforeCreate
    // never gets called
    options._ssrRegister = hook
  } else if (injectStyles) {
    hook = injectStyles
  }

  if (hook) {
    var functional = options.functional
    var existing = functional
      ? options.render
      : options.beforeCreate
    if (!functional) {
      // inject component registration as beforeCreate hook
      options.beforeCreate = existing
        ? [].concat(existing, hook)
        : [hook]
    } else {
      // register for functioal component in vue file
      options.render = function renderWithStyleInjection (h, context) {
        hook.call(context)
        return existing(h, context)
      }
    }
  }

  return {
    esModule: esModule,
    exports: scriptExports,
    options: options
  }
}


/***/ }),
/* 1 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


exports.__esModule = true;

var _defineProperty = __webpack_require__(101);

var _defineProperty2 = _interopRequireDefault(_defineProperty);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

exports.default = function (obj, key, value) {
  if (key in obj) {
    (0, _defineProperty2.default)(obj, key, {
      value: value,
      enumerable: true,
      configurable: true,
      writable: true
    });
  } else {
    obj[key] = value;
  }

  return obj;
};

/***/ }),
/* 2 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "b", function() { return setOptions; });
var config = {
    defaultContainerElement: null,
    defaultIconPack: 'mdi',
    defaultIconComponent: null,
    defaultDialogConfirmText: null,
    defaultDialogCancelText: null,
    defaultSnackbarDuration: 3500,
    defaultSnackbarPosition: null,
    defaultToastDuration: 2000,
    defaultToastPosition: null,
    detaultNotificationDuration: 2000,
    defaultNotificationPosition: null,
    defaultTooltipType: 'is-primary',
    defaultTooltipAnimated: false,
    defaultInputAutocomplete: 'on',
    defaultDateFormatter: null,
    defaultDateParser: null,
    defaultDateCreator: null,
    defaultDayNames: null,
    defaultMonthNames: null,
    defaultFirstDayOfWeek: null,
    defaultUnselectableDaysOfWeek: null,
    defaultTimeFormatter: null,
    defaultTimeParser: null,
    defaultModalCanCancel: null,
    defaultModalScroll: null,
    defaultDatepickerMobileNative: true,
    defaultTimepickerMobileNative: true,
    defaultNoticeQueue: true,
    defaultInputHasCounter: true,
    defaultUseHtml5Validation: true
};

/* harmony default export */ __webpack_exports__["a"] = (config);

var setOptions = function setOptions(options) {
    config = options;
};

/***/ }),
/* 3 */
/***/ (function(module, exports, __webpack_require__) {

var Component = __webpack_require__(0)(
  /* script */
  __webpack_require__(105),
  /* template */
  __webpack_require__(106),
  /* styles */
  null,
  /* scopeId */
  null,
  /* moduleIdentifier (server only) */
  null
)

module.exports = Component.exports


/***/ }),
/* 4 */
/***/ (function(module, exports, __webpack_require__) {

var store = __webpack_require__(37)('wks');
var uid = __webpack_require__(26);
var Symbol = __webpack_require__(8).Symbol;
var USE_SYMBOL = typeof Symbol == 'function';

var $exports = module.exports = function (name) {
  return store[name] || (store[name] =
    USE_SYMBOL && Symbol[name] || (USE_SYMBOL ? Symbol : uid)('Symbol.' + name));
};

$exports.store = store;


/***/ }),
/* 5 */
/***/ (function(module, exports, __webpack_require__) {

module.exports = { "default": __webpack_require__(88), __esModule: true };

/***/ }),
/* 6 */
/***/ (function(module, exports) {

var core = module.exports = { version: '2.5.7' };
if (typeof __e == 'number') __e = core; // eslint-disable-line no-undef


/***/ }),
/* 7 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony export (immutable) */ __webpack_exports__["a"] = getValueByPath;
/* harmony export (immutable) */ __webpack_exports__["b"] = indexOf;
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "c", function() { return isMobile; });
/* harmony export (immutable) */ __webpack_exports__["d"] = removeElement;
/* unused harmony export escapeRegExpChars */
/**
 * Get value of an object property/path even if it's nested
 */
function getValueByPath(obj, path) {
    var value = path.split('.').reduce(function (o, i) {
        return o[i];
    }, obj);
    return value;
}

/**
 * Extension of indexOf method by equality function if specified
 */
function indexOf(array, obj, fn) {
    if (!array) return -1;

    if (!fn || typeof fn !== 'function') return array.indexOf(obj);

    for (var i = 0; i < array.length; i++) {
        if (fn(array[i], obj)) {
            return i;
        }
    }

    return -1;
}

/**
 * Mobile detection
 * https://www.abeautifulsite.net/detecting-mobile-devices-with-javascript
 */
var isMobile = {
    Android: function Android() {
        return typeof window !== 'undefined' && window.navigator.userAgent.match(/Android/i);
    },
    BlackBerry: function BlackBerry() {
        return typeof window !== 'undefined' && window.navigator.userAgent.match(/BlackBerry/i);
    },
    iOS: function iOS() {
        return typeof window !== 'undefined' && window.navigator.userAgent.match(/iPhone|iPad|iPod/i);
    },
    Opera: function Opera() {
        return typeof window !== 'undefined' && window.navigator.userAgent.match(/Opera Mini/i);
    },
    Windows: function Windows() {
        return typeof window !== 'undefined' && window.navigator.userAgent.match(/IEMobile/i);
    },
    any: function any() {
        return isMobile.Android() || isMobile.BlackBerry() || isMobile.iOS() || isMobile.Opera() || isMobile.Windows();
    }
};

function removeElement(el) {
    if (typeof el.remove !== 'undefined') {
        el.remove();
    } else if (typeof el.parentNode !== 'undefined') {
        el.parentNode.removeChild(el);
    }
}

/**
 * Escape regex characters
 * http://stackoverflow.com/a/6969486
 */
function escapeRegExpChars(value) {
    if (!value) return value;

    // eslint-disable-next-line
    return value.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, '\\$&');
}

/***/ }),
/* 8 */
/***/ (function(module, exports) {

// https://github.com/zloirock/core-js/issues/86#issuecomment-115759028
var global = module.exports = typeof window != 'undefined' && window.Math == Math
  ? window : typeof self != 'undefined' && self.Math == Math ? self
  // eslint-disable-next-line no-new-func
  : Function('return this')();
if (typeof __g == 'number') __g = global; // eslint-disable-line no-undef


/***/ }),
/* 9 */
/***/ (function(module, exports, __webpack_require__) {

var anObject = __webpack_require__(15);
var IE8_DOM_DEFINE = __webpack_require__(47);
var toPrimitive = __webpack_require__(32);
var dP = Object.defineProperty;

exports.f = __webpack_require__(11) ? Object.defineProperty : function defineProperty(O, P, Attributes) {
  anObject(O);
  P = toPrimitive(P, true);
  anObject(Attributes);
  if (IE8_DOM_DEFINE) try {
    return dP(O, P, Attributes);
  } catch (e) { /* empty */ }
  if ('get' in Attributes || 'set' in Attributes) throw TypeError('Accessors not supported!');
  if ('value' in Attributes) O[P] = Attributes.value;
  return O;
};


/***/ }),
/* 10 */
/***/ (function(module, exports, __webpack_require__) {

module.exports = { "default": __webpack_require__(70), __esModule: true };

/***/ }),
/* 11 */
/***/ (function(module, exports, __webpack_require__) {

// Thank's IE8 for his funny defineProperty
module.exports = !__webpack_require__(20)(function () {
  return Object.defineProperty({}, 'a', { get: function () { return 7; } }).a != 7;
});


/***/ }),
/* 12 */
/***/ (function(module, exports) {

var hasOwnProperty = {}.hasOwnProperty;
module.exports = function (it, key) {
  return hasOwnProperty.call(it, key);
};


/***/ }),
/* 13 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__utils_config__ = __webpack_require__(2);


/* harmony default export */ __webpack_exports__["a"] = ({
    props: {
        size: String,
        expanded: Boolean,
        loading: Boolean,
        rounded: Boolean,
        icon: String,
        iconPack: String,
        // Native options to use in HTML5 validation
        autocomplete: String,
        maxlength: [Number, String],
        useHtml5Validation: {
            type: Boolean,
            default: function _default() {
                return __WEBPACK_IMPORTED_MODULE_0__utils_config__["a" /* default */].defaultUseHtml5Validation;
            }
        }
    },
    data: function data() {
        return {
            isValid: true,
            isFocused: false,
            newIconPack: this.iconPack || __WEBPACK_IMPORTED_MODULE_0__utils_config__["a" /* default */].defaultIconPack
        };
    },

    computed: {
        /**
         * Find parent Field, max 3 levels deep.
         */
        parentField: function parentField() {
            var parent = this.$parent;
            for (var i = 0; i < 3; i++) {
                if (parent && !parent.$data._isField) {
                    parent = parent.$parent;
                }
            }
            return parent;
        },


        /**
         * Get the type prop from parent if it's a Field.
         */
        statusType: function statusType() {
            if (!this.parentField) return;
            if (!this.parentField.newType) return;
            if (typeof this.parentField.newType === 'string') {
                return this.parentField.newType;
            } else {
                for (var key in this.parentField.newType) {
                    if (this.parentField.newType[key]) {
                        return key;
                    }
                }
            }
        },


        /**
         * Get the message prop from parent if it's a Field.
         */
        statusMessage: function statusMessage() {
            if (!this.parentField) return;

            return this.parentField.newMessage;
        },


        /**
         * Fix icon size for inputs, large was too big
         */
        iconSize: function iconSize() {
            switch (this.size) {
                case 'is-small':
                    return this.size;
                case 'is-medium':
                    return;
                case 'is-large':
                    return this.newIconPack === 'mdi' ? 'is-medium' : '';
            }
        }
    },
    methods: {
        /**
         * Focus method that work dynamically depending on the component.
         */
        focus: function focus() {
            var _this = this;

            if (this.$data._elementRef === undefined) return;

            this.$nextTick(function () {
                var el = _this.$el.querySelector(_this.$data._elementRef);
                if (el) el.focus();
            });
        },
        onBlur: function onBlur($event) {
            this.isFocused = false;
            this.$emit('blur', $event);
            this.checkHtml5Validity();
        },
        onFocus: function onFocus($event) {
            this.isFocused = true;
            this.$emit('focus', $event);
        },


        /**
         * Check HTML5 validation, set isValid property.
         * If validation fail, send 'is-danger' type,
         * and error message to parent if it's a Field.
         */
        checkHtml5Validity: function checkHtml5Validity() {
            var _this2 = this;

            if (!this.useHtml5Validation) return;

            if (this.$refs[this.$data._elementRef] === undefined) return;

            var el = this.$el.querySelector(this.$data._elementRef);

            var type = null;
            var message = null;
            var isValid = true;
            if (!el.checkValidity()) {
                type = 'is-danger';
                message = el.validationMessage;
                isValid = false;
            }
            this.isValid = isValid;

            this.$nextTick(function () {
                if (_this2.parentField) {
                    // Set type only if not defined
                    if (!_this2.parentField.type) {
                        _this2.parentField.newType = type;
                    }
                    // Set message only if not defined
                    if (!_this2.parentField.message) {
                        _this2.parentField.newMessage = message;
                    }
                }
            });

            return this.isValid;
        }
    }
});

/***/ }),
/* 14 */
/***/ (function(module, exports, __webpack_require__) {

var dP = __webpack_require__(9);
var createDesc = __webpack_require__(21);
module.exports = __webpack_require__(11) ? function (object, key, value) {
  return dP.f(object, key, createDesc(1, value));
} : function (object, key, value) {
  object[key] = value;
  return object;
};


/***/ }),
/* 15 */
/***/ (function(module, exports, __webpack_require__) {

var isObject = __webpack_require__(19);
module.exports = function (it) {
  if (!isObject(it)) throw TypeError(it + ' is not an object!');
  return it;
};


/***/ }),
/* 16 */
/***/ (function(module, exports, __webpack_require__) {

// to indexed object, toObject with fallback for non-array-like ES3 strings
var IObject = __webpack_require__(50);
var defined = __webpack_require__(34);
module.exports = function (it) {
  return IObject(defined(it));
};


/***/ }),
/* 17 */
/***/ (function(module, exports) {

module.exports = __WEBPACK_EXTERNAL_MODULE_17__;

/***/ }),
/* 18 */
/***/ (function(module, exports, __webpack_require__) {

var global = __webpack_require__(8);
var core = __webpack_require__(6);
var ctx = __webpack_require__(46);
var hide = __webpack_require__(14);
var has = __webpack_require__(12);
var PROTOTYPE = 'prototype';

var $export = function (type, name, source) {
  var IS_FORCED = type & $export.F;
  var IS_GLOBAL = type & $export.G;
  var IS_STATIC = type & $export.S;
  var IS_PROTO = type & $export.P;
  var IS_BIND = type & $export.B;
  var IS_WRAP = type & $export.W;
  var exports = IS_GLOBAL ? core : core[name] || (core[name] = {});
  var expProto = exports[PROTOTYPE];
  var target = IS_GLOBAL ? global : IS_STATIC ? global[name] : (global[name] || {})[PROTOTYPE];
  var key, own, out;
  if (IS_GLOBAL) source = name;
  for (key in source) {
    // contains in native
    own = !IS_FORCED && target && target[key] !== undefined;
    if (own && has(exports, key)) continue;
    // export native or passed
    out = own ? target[key] : source[key];
    // prevent global pollution for namespaces
    exports[key] = IS_GLOBAL && typeof target[key] != 'function' ? source[key]
    // bind timers to global for call from export context
    : IS_BIND && own ? ctx(out, global)
    // wrap global constructors for prevent change them in library
    : IS_WRAP && target[key] == out ? (function (C) {
      var F = function (a, b, c) {
        if (this instanceof C) {
          switch (arguments.length) {
            case 0: return new C();
            case 1: return new C(a);
            case 2: return new C(a, b);
          } return new C(a, b, c);
        } return C.apply(this, arguments);
      };
      F[PROTOTYPE] = C[PROTOTYPE];
      return F;
    // make static versions for prototype methods
    })(out) : IS_PROTO && typeof out == 'function' ? ctx(Function.call, out) : out;
    // export proto methods to core.%CONSTRUCTOR%.methods.%NAME%
    if (IS_PROTO) {
      (exports.virtual || (exports.virtual = {}))[key] = out;
      // export proto methods to core.%CONSTRUCTOR%.prototype.%NAME%
      if (type & $export.R && expProto && !expProto[key]) hide(expProto, key, out);
    }
  }
};
// type bitmap
$export.F = 1;   // forced
$export.G = 2;   // global
$export.S = 4;   // static
$export.P = 8;   // proto
$export.B = 16;  // bind
$export.W = 32;  // wrap
$export.U = 64;  // safe
$export.R = 128; // real proto method for `library`
module.exports = $export;


/***/ }),
/* 19 */
/***/ (function(module, exports) {

module.exports = function (it) {
  return typeof it === 'object' ? it !== null : typeof it === 'function';
};


/***/ }),
/* 20 */
/***/ (function(module, exports) {

module.exports = function (exec) {
  try {
    return !!exec();
  } catch (e) {
    return true;
  }
};


/***/ }),
/* 21 */
/***/ (function(module, exports) {

module.exports = function (bitmap, value) {
  return {
    enumerable: !(bitmap & 1),
    configurable: !(bitmap & 2),
    writable: !(bitmap & 4),
    value: value
  };
};


/***/ }),
/* 22 */
/***/ (function(module, exports) {

module.exports = {};


/***/ }),
/* 23 */
/***/ (function(module, exports, __webpack_require__) {

var Component = __webpack_require__(0)(
  /* script */
  __webpack_require__(104),
  /* template */
  __webpack_require__(107),
  /* styles */
  null,
  /* scopeId */
  null,
  /* moduleIdentifier (server only) */
  null
)

module.exports = Component.exports


/***/ }),
/* 24 */
/***/ (function(module, exports, __webpack_require__) {

// 19.1.2.14 / 15.2.3.14 Object.keys(O)
var $keys = __webpack_require__(49);
var enumBugKeys = __webpack_require__(38);

module.exports = Object.keys || function keys(O) {
  return $keys(O, enumBugKeys);
};


/***/ }),
/* 25 */
/***/ (function(module, exports) {

module.exports = true;


/***/ }),
/* 26 */
/***/ (function(module, exports) {

var id = 0;
var px = Math.random();
module.exports = function (key) {
  return 'Symbol('.concat(key === undefined ? '' : key, ')_', (++id + px).toString(36));
};


/***/ }),
/* 27 */
/***/ (function(module, exports) {

exports.f = {}.propertyIsEnumerable;


/***/ }),
/* 28 */
/***/ (function(module, exports, __webpack_require__) {

var Component = __webpack_require__(0)(
  /* script */
  __webpack_require__(122),
  /* template */
  __webpack_require__(123),
  /* styles */
  null,
  /* scopeId */
  null,
  /* moduleIdentifier (server only) */
  null
)

module.exports = Component.exports


/***/ }),
/* 29 */
/***/ (function(module, exports, __webpack_require__) {

var Component = __webpack_require__(0)(
  /* script */
  __webpack_require__(124),
  /* template */
  __webpack_require__(125),
  /* styles */
  null,
  /* scopeId */
  null,
  /* moduleIdentifier (server only) */
  null
)

module.exports = Component.exports


/***/ }),
/* 30 */
/***/ (function(module, exports, __webpack_require__) {

var Component = __webpack_require__(0)(
  /* script */
  __webpack_require__(126),
  /* template */
  __webpack_require__(129),
  /* styles */
  null,
  /* scopeId */
  null,
  /* moduleIdentifier (server only) */
  null
)

module.exports = Component.exports


/***/ }),
/* 31 */
/***/ (function(module, exports, __webpack_require__) {

var Component = __webpack_require__(0)(
  /* script */
  __webpack_require__(136),
  /* template */
  __webpack_require__(137),
  /* styles */
  null,
  /* scopeId */
  null,
  /* moduleIdentifier (server only) */
  null
)

module.exports = Component.exports


/***/ }),
/* 32 */
/***/ (function(module, exports, __webpack_require__) {

// 7.1.1 ToPrimitive(input [, PreferredType])
var isObject = __webpack_require__(19);
// instead of the ES6 spec version, we didn't implement @@toPrimitive case
// and the second argument - flag - preferred type is a string
module.exports = function (it, S) {
  if (!isObject(it)) return it;
  var fn, val;
  if (S && typeof (fn = it.toString) == 'function' && !isObject(val = fn.call(it))) return val;
  if (typeof (fn = it.valueOf) == 'function' && !isObject(val = fn.call(it))) return val;
  if (!S && typeof (fn = it.toString) == 'function' && !isObject(val = fn.call(it))) return val;
  throw TypeError("Can't convert object to primitive value");
};


/***/ }),
/* 33 */
/***/ (function(module, exports) {

var toString = {}.toString;

module.exports = function (it) {
  return toString.call(it).slice(8, -1);
};


/***/ }),
/* 34 */
/***/ (function(module, exports) {

// 7.2.1 RequireObjectCoercible(argument)
module.exports = function (it) {
  if (it == undefined) throw TypeError("Can't call method on  " + it);
  return it;
};


/***/ }),
/* 35 */
/***/ (function(module, exports) {

// 7.1.4 ToInteger
var ceil = Math.ceil;
var floor = Math.floor;
module.exports = function (it) {
  return isNaN(it = +it) ? 0 : (it > 0 ? floor : ceil)(it);
};


/***/ }),
/* 36 */
/***/ (function(module, exports, __webpack_require__) {

var shared = __webpack_require__(37)('keys');
var uid = __webpack_require__(26);
module.exports = function (key) {
  return shared[key] || (shared[key] = uid(key));
};


/***/ }),
/* 37 */
/***/ (function(module, exports, __webpack_require__) {

var core = __webpack_require__(6);
var global = __webpack_require__(8);
var SHARED = '__core-js_shared__';
var store = global[SHARED] || (global[SHARED] = {});

(module.exports = function (key, value) {
  return store[key] || (store[key] = value !== undefined ? value : {});
})('versions', []).push({
  version: core.version,
  mode: __webpack_require__(25) ? 'pure' : 'global',
  copyright: '© 2018 Denis Pushkarev (zloirock.ru)'
});


/***/ }),
/* 38 */
/***/ (function(module, exports) {

// IE 8- don't enum bug keys
module.exports = (
  'constructor,hasOwnProperty,isPrototypeOf,propertyIsEnumerable,toLocaleString,toString,valueOf'
).split(',');


/***/ }),
/* 39 */
/***/ (function(module, exports) {

exports.f = Object.getOwnPropertySymbols;


/***/ }),
/* 40 */
/***/ (function(module, exports, __webpack_require__) {

// 7.1.13 ToObject(argument)
var defined = __webpack_require__(34);
module.exports = function (it) {
  return Object(defined(it));
};


/***/ }),
/* 41 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

var $at = __webpack_require__(80)(true);

// 21.1.3.27 String.prototype[@@iterator]()
__webpack_require__(54)(String, 'String', function (iterated) {
  this._t = String(iterated); // target
  this._i = 0;                // next index
// 21.1.5.2.1 %StringIteratorPrototype%.next()
}, function () {
  var O = this._t;
  var index = this._i;
  var point;
  if (index >= O.length) return { value: undefined, done: true };
  point = $at(O, index);
  this._i += point.length;
  return { value: point, done: false };
});


/***/ }),
/* 42 */
/***/ (function(module, exports, __webpack_require__) {

var def = __webpack_require__(9).f;
var has = __webpack_require__(12);
var TAG = __webpack_require__(4)('toStringTag');

module.exports = function (it, tag, stat) {
  if (it && !has(it = stat ? it : it.prototype, TAG)) def(it, TAG, { configurable: true, value: tag });
};


/***/ }),
/* 43 */
/***/ (function(module, exports, __webpack_require__) {

exports.f = __webpack_require__(4);


/***/ }),
/* 44 */
/***/ (function(module, exports, __webpack_require__) {

var global = __webpack_require__(8);
var core = __webpack_require__(6);
var LIBRARY = __webpack_require__(25);
var wksExt = __webpack_require__(43);
var defineProperty = __webpack_require__(9).f;
module.exports = function (name) {
  var $Symbol = core.Symbol || (core.Symbol = LIBRARY ? {} : global.Symbol || {});
  if (name.charAt(0) != '_' && !(name in $Symbol)) defineProperty($Symbol, name, { value: wksExt.f(name) });
};


/***/ }),
/* 45 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__config__ = __webpack_require__(2);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__helpers__ = __webpack_require__(7);



/* harmony default export */ __webpack_exports__["a"] = ({
    props: {
        type: {
            type: String,
            default: 'is-dark'
        },
        message: String,
        duration: Number,
        queue: {
            type: Boolean,
            default: undefined
        },
        position: {
            type: String,
            default: 'is-top',
            validator: function validator(value) {
                return ['is-top-right', 'is-top', 'is-top-left', 'is-bottom-right', 'is-bottom', 'is-bottom-left'].indexOf(value) > -1;
            }
        },
        container: String
    },
    data: function data() {
        return {
            isActive: false,
            parentTop: null,
            parentBottom: null,
            newContainer: this.container || __WEBPACK_IMPORTED_MODULE_0__config__["a" /* default */].defaultContainerElement
        };
    },

    computed: {
        correctParent: function correctParent() {
            switch (this.position) {
                case 'is-top-right':
                case 'is-top':
                case 'is-top-left':
                    return this.parentTop;

                case 'is-bottom-right':
                case 'is-bottom':
                case 'is-bottom-left':
                    return this.parentBottom;
            }
        },
        transition: function transition() {
            switch (this.position) {
                case 'is-top-right':
                case 'is-top':
                case 'is-top-left':
                    return {
                        enter: 'fadeInDown',
                        leave: 'fadeOut'
                    };
                case 'is-bottom-right':
                case 'is-bottom':
                case 'is-bottom-left':
                    return {
                        enter: 'fadeInUp',
                        leave: 'fadeOut'
                    };
            }
        }
    },
    methods: {
        shouldQueue: function shouldQueue() {
            var queue = this.queue !== undefined ? this.queue : __WEBPACK_IMPORTED_MODULE_0__config__["a" /* default */].defaultNoticeQueue;

            if (!queue) return false;

            return this.parentTop.childElementCount > 0 || this.parentBottom.childElementCount > 0;
        },
        close: function close() {
            var _this = this;

            clearTimeout(this.timer);
            this.isActive = false;

            // Timeout for the animation complete before destroying
            setTimeout(function () {
                _this.$destroy();
                Object(__WEBPACK_IMPORTED_MODULE_1__helpers__["d" /* removeElement */])(_this.$el);
            }, 150);
        },
        showNotice: function showNotice() {
            var _this2 = this;

            if (this.shouldQueue()) {
                // Call recursively if should queue
                setTimeout(function () {
                    return _this2.showNotice();
                }, 250);
                return;
            }
            this.correctParent.insertAdjacentElement('afterbegin', this.$el);
            this.isActive = true;

            if (!this.indefinite) {
                this.timer = setTimeout(function () {
                    return _this2.close();
                }, this.newDuration);
            }
        },
        setupContainer: function setupContainer() {
            this.parentTop = document.querySelector('.notices.is-top');
            this.parentBottom = document.querySelector('.notices.is-bottom');

            if (this.parentTop && this.parentBottom) return;

            if (!this.parentTop) {
                this.parentTop = document.createElement('div');
                this.parentTop.className = 'notices is-top';
            }

            if (!this.parentBottom) {
                this.parentBottom = document.createElement('div');
                this.parentBottom.className = 'notices is-bottom';
            }

            var container = document.querySelector(this.newContainer) || document.body;

            container.appendChild(this.parentTop);
            container.appendChild(this.parentBottom);

            if (this.newContainer) {
                this.parentTop.classList.add('has-custom-container');
                this.parentBottom.classList.add('has-custom-container');
            }
        }
    },
    beforeMount: function beforeMount() {
        this.setupContainer();
    },
    mounted: function mounted() {
        this.showNotice();
    }
});

/***/ }),
/* 46 */
/***/ (function(module, exports, __webpack_require__) {

// optional / simple context binding
var aFunction = __webpack_require__(72);
module.exports = function (fn, that, length) {
  aFunction(fn);
  if (that === undefined) return fn;
  switch (length) {
    case 1: return function (a) {
      return fn.call(that, a);
    };
    case 2: return function (a, b) {
      return fn.call(that, a, b);
    };
    case 3: return function (a, b, c) {
      return fn.call(that, a, b, c);
    };
  }
  return function (/* ...args */) {
    return fn.apply(that, arguments);
  };
};


/***/ }),
/* 47 */
/***/ (function(module, exports, __webpack_require__) {

module.exports = !__webpack_require__(11) && !__webpack_require__(20)(function () {
  return Object.defineProperty(__webpack_require__(48)('div'), 'a', { get: function () { return 7; } }).a != 7;
});


/***/ }),
/* 48 */
/***/ (function(module, exports, __webpack_require__) {

var isObject = __webpack_require__(19);
var document = __webpack_require__(8).document;
// typeof document.createElement is 'object' in old IE
var is = isObject(document) && isObject(document.createElement);
module.exports = function (it) {
  return is ? document.createElement(it) : {};
};


/***/ }),
/* 49 */
/***/ (function(module, exports, __webpack_require__) {

var has = __webpack_require__(12);
var toIObject = __webpack_require__(16);
var arrayIndexOf = __webpack_require__(74)(false);
var IE_PROTO = __webpack_require__(36)('IE_PROTO');

module.exports = function (object, names) {
  var O = toIObject(object);
  var i = 0;
  var result = [];
  var key;
  for (key in O) if (key != IE_PROTO) has(O, key) && result.push(key);
  // Don't enum bug & hidden keys
  while (names.length > i) if (has(O, key = names[i++])) {
    ~arrayIndexOf(result, key) || result.push(key);
  }
  return result;
};


/***/ }),
/* 50 */
/***/ (function(module, exports, __webpack_require__) {

// fallback for non-array-like ES3 and non-enumerable old V8 strings
var cof = __webpack_require__(33);
// eslint-disable-next-line no-prototype-builtins
module.exports = Object('z').propertyIsEnumerable(0) ? Object : function (it) {
  return cof(it) == 'String' ? it.split('') : Object(it);
};


/***/ }),
/* 51 */
/***/ (function(module, exports, __webpack_require__) {

// 7.1.15 ToLength
var toInteger = __webpack_require__(35);
var min = Math.min;
module.exports = function (it) {
  return it > 0 ? min(toInteger(it), 0x1fffffffffffff) : 0; // pow(2, 53) - 1 == 9007199254740991
};


/***/ }),
/* 52 */
/***/ (function(module, exports, __webpack_require__) {

var Component = __webpack_require__(0)(
  /* script */
  __webpack_require__(77),
  /* template */
  __webpack_require__(108),
  /* styles */
  null,
  /* scopeId */
  null,
  /* moduleIdentifier (server only) */
  null
)

module.exports = Component.exports


/***/ }),
/* 53 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


exports.__esModule = true;

var _iterator = __webpack_require__(78);

var _iterator2 = _interopRequireDefault(_iterator);

var _symbol = __webpack_require__(5);

var _symbol2 = _interopRequireDefault(_symbol);

var _typeof = typeof _symbol2.default === "function" && typeof _iterator2.default === "symbol" ? function (obj) { return typeof obj; } : function (obj) { return obj && typeof _symbol2.default === "function" && obj.constructor === _symbol2.default && obj !== _symbol2.default.prototype ? "symbol" : typeof obj; };

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

exports.default = typeof _symbol2.default === "function" && _typeof(_iterator2.default) === "symbol" ? function (obj) {
  return typeof obj === "undefined" ? "undefined" : _typeof(obj);
} : function (obj) {
  return obj && typeof _symbol2.default === "function" && obj.constructor === _symbol2.default && obj !== _symbol2.default.prototype ? "symbol" : typeof obj === "undefined" ? "undefined" : _typeof(obj);
};

/***/ }),
/* 54 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

var LIBRARY = __webpack_require__(25);
var $export = __webpack_require__(18);
var redefine = __webpack_require__(55);
var hide = __webpack_require__(14);
var Iterators = __webpack_require__(22);
var $iterCreate = __webpack_require__(81);
var setToStringTag = __webpack_require__(42);
var getPrototypeOf = __webpack_require__(84);
var ITERATOR = __webpack_require__(4)('iterator');
var BUGGY = !([].keys && 'next' in [].keys()); // Safari has buggy iterators w/o `next`
var FF_ITERATOR = '@@iterator';
var KEYS = 'keys';
var VALUES = 'values';

var returnThis = function () { return this; };

module.exports = function (Base, NAME, Constructor, next, DEFAULT, IS_SET, FORCED) {
  $iterCreate(Constructor, NAME, next);
  var getMethod = function (kind) {
    if (!BUGGY && kind in proto) return proto[kind];
    switch (kind) {
      case KEYS: return function keys() { return new Constructor(this, kind); };
      case VALUES: return function values() { return new Constructor(this, kind); };
    } return function entries() { return new Constructor(this, kind); };
  };
  var TAG = NAME + ' Iterator';
  var DEF_VALUES = DEFAULT == VALUES;
  var VALUES_BUG = false;
  var proto = Base.prototype;
  var $native = proto[ITERATOR] || proto[FF_ITERATOR] || DEFAULT && proto[DEFAULT];
  var $default = $native || getMethod(DEFAULT);
  var $entries = DEFAULT ? !DEF_VALUES ? $default : getMethod('entries') : undefined;
  var $anyNative = NAME == 'Array' ? proto.entries || $native : $native;
  var methods, key, IteratorPrototype;
  // Fix native
  if ($anyNative) {
    IteratorPrototype = getPrototypeOf($anyNative.call(new Base()));
    if (IteratorPrototype !== Object.prototype && IteratorPrototype.next) {
      // Set @@toStringTag to native iterators
      setToStringTag(IteratorPrototype, TAG, true);
      // fix for some old engines
      if (!LIBRARY && typeof IteratorPrototype[ITERATOR] != 'function') hide(IteratorPrototype, ITERATOR, returnThis);
    }
  }
  // fix Array#{values, @@iterator}.name in V8 / FF
  if (DEF_VALUES && $native && $native.name !== VALUES) {
    VALUES_BUG = true;
    $default = function values() { return $native.call(this); };
  }
  // Define iterator
  if ((!LIBRARY || FORCED) && (BUGGY || VALUES_BUG || !proto[ITERATOR])) {
    hide(proto, ITERATOR, $default);
  }
  // Plug for library
  Iterators[NAME] = $default;
  Iterators[TAG] = returnThis;
  if (DEFAULT) {
    methods = {
      values: DEF_VALUES ? $default : getMethod(VALUES),
      keys: IS_SET ? $default : getMethod(KEYS),
      entries: $entries
    };
    if (FORCED) for (key in methods) {
      if (!(key in proto)) redefine(proto, key, methods[key]);
    } else $export($export.P + $export.F * (BUGGY || VALUES_BUG), NAME, methods);
  }
  return methods;
};


/***/ }),
/* 55 */
/***/ (function(module, exports, __webpack_require__) {

module.exports = __webpack_require__(14);


/***/ }),
/* 56 */
/***/ (function(module, exports, __webpack_require__) {

// 19.1.2.2 / 15.2.3.5 Object.create(O [, Properties])
var anObject = __webpack_require__(15);
var dPs = __webpack_require__(82);
var enumBugKeys = __webpack_require__(38);
var IE_PROTO = __webpack_require__(36)('IE_PROTO');
var Empty = function () { /* empty */ };
var PROTOTYPE = 'prototype';

// Create object with fake `null` prototype: use iframe Object with cleared prototype
var createDict = function () {
  // Thrash, waste and sodomy: IE GC bug
  var iframe = __webpack_require__(48)('iframe');
  var i = enumBugKeys.length;
  var lt = '<';
  var gt = '>';
  var iframeDocument;
  iframe.style.display = 'none';
  __webpack_require__(83).appendChild(iframe);
  iframe.src = 'javascript:'; // eslint-disable-line no-script-url
  // createDict = iframe.contentWindow.Object;
  // html.removeChild(iframe);
  iframeDocument = iframe.contentWindow.document;
  iframeDocument.open();
  iframeDocument.write(lt + 'script' + gt + 'document.F=Object' + lt + '/script' + gt);
  iframeDocument.close();
  createDict = iframeDocument.F;
  while (i--) delete createDict[PROTOTYPE][enumBugKeys[i]];
  return createDict();
};

module.exports = Object.create || function create(O, Properties) {
  var result;
  if (O !== null) {
    Empty[PROTOTYPE] = anObject(O);
    result = new Empty();
    Empty[PROTOTYPE] = null;
    // add "__proto__" for Object.getPrototypeOf polyfill
    result[IE_PROTO] = O;
  } else result = createDict();
  return Properties === undefined ? result : dPs(result, Properties);
};


/***/ }),
/* 57 */
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__(85);
var global = __webpack_require__(8);
var hide = __webpack_require__(14);
var Iterators = __webpack_require__(22);
var TO_STRING_TAG = __webpack_require__(4)('toStringTag');

var DOMIterables = ('CSSRuleList,CSSStyleDeclaration,CSSValueList,ClientRectList,DOMRectList,DOMStringList,' +
  'DOMTokenList,DataTransferItemList,FileList,HTMLAllCollection,HTMLCollection,HTMLFormElement,HTMLSelectElement,' +
  'MediaList,MimeTypeArray,NamedNodeMap,NodeList,PaintRequestList,Plugin,PluginArray,SVGLengthList,SVGNumberList,' +
  'SVGPathSegList,SVGPointList,SVGStringList,SVGTransformList,SourceBufferList,StyleSheetList,TextTrackCueList,' +
  'TextTrackList,TouchList').split(',');

for (var i = 0; i < DOMIterables.length; i++) {
  var NAME = DOMIterables[i];
  var Collection = global[NAME];
  var proto = Collection && Collection.prototype;
  if (proto && !proto[TO_STRING_TAG]) hide(proto, TO_STRING_TAG, NAME);
  Iterators[NAME] = Iterators.Array;
}


/***/ }),
/* 58 */
/***/ (function(module, exports, __webpack_require__) {

// 19.1.2.7 / 15.2.3.4 Object.getOwnPropertyNames(O)
var $keys = __webpack_require__(49);
var hiddenKeys = __webpack_require__(38).concat('length', 'prototype');

exports.f = Object.getOwnPropertyNames || function getOwnPropertyNames(O) {
  return $keys(O, hiddenKeys);
};


/***/ }),
/* 59 */
/***/ (function(module, exports, __webpack_require__) {

module.exports = { "default": __webpack_require__(98), __esModule: true };

/***/ }),
/* 60 */
/***/ (function(module, exports, __webpack_require__) {

var classof = __webpack_require__(100);
var ITERATOR = __webpack_require__(4)('iterator');
var Iterators = __webpack_require__(22);
module.exports = __webpack_require__(6).getIteratorMethod = function (it) {
  if (it != undefined) return it[ITERATOR]
    || it['@@iterator']
    || Iterators[classof(it)];
};


/***/ }),
/* 61 */
/***/ (function(module, exports, __webpack_require__) {

var Component = __webpack_require__(0)(
  /* script */
  __webpack_require__(112),
  /* template */
  __webpack_require__(113),
  /* styles */
  null,
  /* scopeId */
  null,
  /* moduleIdentifier (server only) */
  null
)

module.exports = Component.exports


/***/ }),
/* 62 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__FormElementMixin__ = __webpack_require__(13);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__helpers__ = __webpack_require__(7);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__config__ = __webpack_require__(2);
var _this = this;





var AM = 'AM';
var PM = 'PM';
var HOUR_FORMAT_24 = '24';
var HOUR_FORMAT_12 = '12';

/* harmony default export */ __webpack_exports__["a"] = ({
    mixins: [__WEBPACK_IMPORTED_MODULE_0__FormElementMixin__["a" /* default */]],
    inheritAttrs: false,
    props: {
        value: Date,
        inline: Boolean,
        minTime: Date,
        maxTime: Date,
        placeholder: String,
        editable: Boolean,
        disabled: Boolean,
        hourFormat: {
            type: String,
            default: HOUR_FORMAT_24,
            validator: function validator(value) {
                return value === HOUR_FORMAT_24 || value === HOUR_FORMAT_12;
            }
        },
        incrementMinutes: {
            type: Number,
            default: 1
        },
        timeFormatter: {
            type: Function,
            default: function _default(date) {
                _this.formatTime(date);
            }
        },
        timeParser: {
            type: Function,
            default: function _default(date) {
                _this.parseTime(date);
            }
        },
        mobileNative: {
            type: Boolean,
            default: function _default() {
                return __WEBPACK_IMPORTED_MODULE_2__config__["a" /* default */].defaultTimepickerMobileNative;
            }
        },
        position: String,
        unselectableTimes: Array
    },
    data: function data() {
        return {
            dateSelected: this.value,
            hoursSelected: null,
            minutesSelected: null,
            meridienSelected: null,
            _elementRef: 'input',
            AM: AM,
            PM: PM,
            HOUR_FORMAT_24: HOUR_FORMAT_24,
            HOUR_FORMAT_12: HOUR_FORMAT_12
        };
    },

    computed: {
        hours: function hours() {
            var hours = [];
            var numberOfHours = this.isHourFormat24 ? 24 : 12;
            for (var i = 0; i < numberOfHours; i++) {
                var value = i;
                var label = value;
                if (!this.isHourFormat24) {
                    value = i + 1;
                    label = value;
                    if (this.meridienSelected === this.AM) {
                        if (value === 12) {
                            value = 0;
                        }
                    } else if (this.meridienSelected === this.PM) {
                        if (value !== 12) {
                            value += 12;
                        }
                    }
                }
                hours.push({
                    label: this.formatNumber(label),
                    value: value
                });
            }
            return hours;
        },
        minutes: function minutes() {
            var minutes = [];
            for (var i = 0; i < 60; i += this.incrementMinutes) {
                minutes.push({
                    label: this.formatNumber(i),
                    value: i
                });
            }
            return minutes;
        },
        meridiens: function meridiens() {
            return [AM, PM];
        },
        isMobile: function isMobile() {
            return this.mobileNative && __WEBPACK_IMPORTED_MODULE_1__helpers__["c" /* isMobile */].any();
        },
        isHourFormat24: function isHourFormat24() {
            return this.hourFormat === HOUR_FORMAT_24;
        }
    },
    watch: {
        hourFormat: function hourFormat(value) {
            if (this.hoursSelected !== null) {
                this.meridienSelected = this.hoursSelected >= 12 ? PM : AM;
            }
        },


        /**
        * Emit input event with selected date as payload.
        */
        dateSelected: function dateSelected(value) {
            this.$emit('input', value);
        },


        /**
         * When v-model is changed:
         *   1. Update internal value.
         *   2. If it's invalid, validate again.
         */
        value: function value(_value) {
            this.updateInternalState(_value);
            this.dateSelected = _value;

            !this.isValid && this.$refs.input.checkHtml5Validity();
        }
    },
    methods: {
        onMeridienChange: function onMeridienChange(value) {
            if (this.hoursSelected !== null) {
                if (value === PM) {
                    this.hoursSelected += 12;
                } else if (value === AM) {
                    this.hoursSelected -= 12;
                }
            }
            this.updateDateSelected(this.hoursSelected, this.minutesSelected, value);
        },
        onHoursChange: function onHoursChange(value) {
            this.updateDateSelected(parseInt(value, 10), this.minutesSelected, this.meridienSelected);
        },
        onMinutesChange: function onMinutesChange(value) {
            this.updateDateSelected(this.hoursSelected, parseInt(value, 10), this.meridienSelected);
        },
        updateDateSelected: function updateDateSelected(hours, minutes, meridiens) {
            if (hours != null && minutes != null && (!this.isHourFormat24 && meridiens !== null || this.isHourFormat24)) {
                if (this.dateSelected && !isNaN(this.dateSelected)) {
                    this.dateSelected = new Date(this.dateSelected);
                } else {
                    this.dateSelected = new Date();
                    this.dateSelected.setMilliseconds(0);
                    this.dateSelected.setSeconds(0);
                }
                this.dateSelected.setHours(hours);
                this.dateSelected.setMinutes(minutes);
            }
        },
        updateInternalState: function updateInternalState(value) {
            if (value) {
                this.hoursSelected = value.getHours();
                this.minutesSelected = value.getMinutes();
                this.meridienSelected = value.getHours() >= 12 ? PM : AM;
            } else {
                this.hoursSelected = null;
                this.minutesSelected = null;
                this.meridienSelected = AM;
            }
        },
        isHourDisabled: function isHourDisabled(hour) {
            var _this2 = this;

            var disabled = false;
            if (this.minTime) {
                var minHours = this.minTime.getHours();
                disabled = hour < minHours;
            }
            if (this.maxTime) {
                if (!disabled) {
                    var maxHours = this.maxTime.getHours();
                    disabled = hour > maxHours;
                }
            }
            if (this.unselectableTimes) {
                if (!disabled) {
                    if (this.minutesSelected !== null) {
                        var unselectable = this.unselectableTimes.filter(function (time) {
                            return time.getHours() === hour && time.getMinutes() === _this2.minutesSelected;
                        });
                        disabled = unselectable.length > 0;
                    } else {
                        var _unselectable = this.unselectableTimes.filter(function (time) {
                            return time.getHours() === hour;
                        });
                        disabled = _unselectable.length === this.minutes.length;
                    }
                }
            }
            return disabled;
        },
        isMinuteDisabled: function isMinuteDisabled(minute) {
            var _this3 = this;

            var disabled = false;
            if (this.hoursSelected !== null) {
                if (this.isHourDisabled(this.hoursSelected)) {
                    disabled = true;
                } else {
                    if (this.minTime) {
                        var minHours = this.minTime.getHours();
                        var minMinutes = this.minTime.getMinutes();
                        disabled = this.hoursSelected === minHours && minute < minMinutes;
                    }
                    if (this.maxTime) {
                        if (!disabled) {
                            var maxHours = this.maxTime.getHours();
                            var _minMinutes = this.maxTime.getMinutes();
                            disabled = this.hoursSelected === maxHours && minute > _minMinutes;
                        }
                    }
                }
                if (this.unselectableTimes) {
                    if (!disabled) {
                        var unselectable = this.unselectableTimes.filter(function (time) {
                            return time.getHours() === _this3.hoursSelected && time.getMinutes() === minute;
                        });
                        disabled = unselectable.length > 0;
                    }
                }
            }
            return disabled;
        },


        /*
        * Parse string into date
        */
        onChange: function onChange(value) {
            var date = this.parseTime(value);
            this.updateInternalState(date);
            if (date && !isNaN(date)) {
                this.dateSelected = date;
            } else {
                // Force refresh input value when not valid date
                this.dateSelected = null;
                this.$refs.input.newValue = this.dateSelected;
            }
        },


        /*
        * Close dropdown time picker
        */
        close: function close() {
            if (this.$refs.dropdown) {
                this.$refs.dropdown.isActive = false;
            }
        },


        /*
        * Format date into string 'HH-MM-SS'
        */
        formatHHMMSS: function formatHHMMSS(value) {
            var date = new Date(value);
            if (value && !isNaN(date)) {
                var hours = date.getHours();
                var minutes = date.getMinutes();
                return this.formatNumber(hours) + ':' + this.formatNumber(minutes, true) + ':00';
            }
            return '';
        },


        /*
        * Parse time from string
        */
        onChangeNativePicker: function onChangeNativePicker(event) {
            var date = event.target.value;
            if (date) {
                if (this.dateSelected && !isNaN(this.dateSelected)) {
                    this.dateSelected = new Date(this.dateSelected);
                } else {
                    this.dateSelected = new Date();
                    this.dateSelected.setMilliseconds(0);
                    this.dateSelected.setSeconds(0);
                }
                var time = date.split(':');
                this.dateSelected.setHours(parseInt(time[0], 10));
                this.dateSelected.setMinutes(parseInt(time[1], 10));
            } else {
                this.dateSelected = null;
            }
        },
        formatNumber: function formatNumber(value, isMinute) {
            return this.isHourFormat24 || isMinute ? this.pad(value) : value;
        },
        pad: function pad(value) {
            return (value < 10 ? '0' : '') + value;
        },


        /*
        * Format date into string
        */
        formatValue: function formatValue(date) {
            if (date && !isNaN(date)) {
                return this.formatTime(date);
            } else {
                return null;
            }
        },
        formatTime: function formatTime(date) {
            if (typeof __WEBPACK_IMPORTED_MODULE_2__config__["a" /* default */].defaultTimeFormatter === 'function') {
                return __WEBPACK_IMPORTED_MODULE_2__config__["a" /* default */].defaultTimeFormatter(date);
            } else {
                return this.defaultTimeFormatter(date);
            }
        },
        parseTime: function parseTime(date) {
            if (typeof __WEBPACK_IMPORTED_MODULE_2__config__["a" /* default */].defaultTimeParser === 'function') {
                return __WEBPACK_IMPORTED_MODULE_2__config__["a" /* default */].defaultTimeParser(date);
            } else {
                return this.defaultTimeParser(date);
            }
        },
        defaultTimeFormatter: function defaultTimeFormatter(date) {
            var hours = date.getHours();
            var minutes = date.getMinutes();
            var period = '';
            if (this.hourFormat === HOUR_FORMAT_12) {
                period = ' ' + (hours < 12 ? AM : PM);
                if (hours > 12) {
                    hours -= 12;
                } else if (hours === 0) {
                    hours = 12;
                }
            }
            return this.pad(hours) + ':' + this.pad(minutes) + period;
        },
        defaultTimeParser: function defaultTimeParser(timeString) {
            if (timeString) {
                var am = false;
                if (this.hourFormat === HOUR_FORMAT_12) {
                    var dateString12 = timeString.split(' ');
                    timeString = dateString12[0];
                    am = dateString12[1] === AM;
                }
                var time = timeString.split(':');
                var hours = parseInt(time[0], 10);
                var minutes = parseInt(time[1], 10);
                if (isNaN(hours) || hours < 0 || hours > 23 || this.hourFormat === HOUR_FORMAT_12 && (hours < 1 || hours > 12) || isNaN(minutes) || minutes < 0 || minutes > 59) {
                    return null;
                }
                var d = null;
                if (this.dateSelected && !isNaN(this.dateSelected)) {
                    d = new Date(this.dateSelected);
                } else {
                    d = new Date();
                    d.setMilliseconds(0);
                    d.setSeconds(0);
                }
                d.setMinutes(minutes);
                if (this.hourFormat === HOUR_FORMAT_12) {
                    if (am && hours === 12) {
                        hours = 0;
                    } else if (!am && hours !== 12) {
                        hours += 12;
                    }
                }
                d.setHours(hours);
                return d;
            }
            return null;
        }
    },
    mounted: function mounted() {
        this.updateInternalState(this.value);
    }
});

/***/ }),
/* 63 */
/***/ (function(module, exports, __webpack_require__) {

var Component = __webpack_require__(0)(
  /* script */
  __webpack_require__(147),
  /* template */
  __webpack_require__(148),
  /* styles */
  null,
  /* scopeId */
  null,
  /* moduleIdentifier (server only) */
  null
)

module.exports = Component.exports


/***/ }),
/* 64 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* unused harmony export isSSR */
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "b", function() { return HTMLElement; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "a", function() { return File; });
// Polyfills for SSR

var isSSR = typeof window === 'undefined';

var HTMLElement = isSSR ? Object : window.HTMLElement;
var File = isSSR ? Object : window.File;

/***/ }),
/* 65 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_defineProperty__ = __webpack_require__(1);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_defineProperty___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_defineProperty__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__components_icon_Icon__ = __webpack_require__(3);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__components_icon_Icon___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_1__components_icon_Icon__);



/* harmony default export */ __webpack_exports__["a"] = ({
    components: __WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_defineProperty___default()({}, __WEBPACK_IMPORTED_MODULE_1__components_icon_Icon___default.a.name, __WEBPACK_IMPORTED_MODULE_1__components_icon_Icon___default.a),
    props: {
        active: {
            type: Boolean,
            default: true
        },
        title: String,
        closable: {
            type: Boolean,
            default: true
        },
        message: String,
        type: String,
        hasIcon: Boolean,
        size: String,
        iconPack: String,
        iconSize: String,
        autoClose: {
            type: Boolean,
            default: false
        },
        duration: {
            type: Number,
            default: 2000
        }
    },
    data: function data() {
        return {
            isActive: this.active
        };
    },

    watch: {
        active: function active(value) {
            this.isActive = value;
        },
        isActive: function isActive(value) {
            if (value) {
                this.setAutoClose();
            } else {
                if (this.timer) {
                    clearTimeout(this.timer);
                }
            }
        }
    },
    computed: {
        /**
         * Icon name (MDI) based on type.
         */
        icon: function icon() {
            switch (this.type) {
                case 'is-info':
                    return 'information';
                case 'is-success':
                    return 'check-circle';
                case 'is-warning':
                    return 'alert';
                case 'is-danger':
                    return 'alert-circle';
                default:
                    return null;
            }
        }
    },
    methods: {
        /**
         * Close the Message and emit events.
         */
        close: function close() {
            this.isActive = false;
            this.$emit('close');
            this.$emit('update:active', false);
        },

        /**
         * Set timer to auto close message
         */
        setAutoClose: function setAutoClose() {
            var _this = this;

            if (this.autoClose) {
                this.timer = setTimeout(function () {
                    if (_this.isActive) {
                        _this.close();
                    }
                }, this.duration);
            }
        }
    },
    mounted: function mounted() {
        this.setAutoClose();
    }
});

/***/ }),
/* 66 */
/***/ (function(module, exports, __webpack_require__) {

var Component = __webpack_require__(0)(
  /* script */
  __webpack_require__(162),
  /* template */
  __webpack_require__(163),
  /* styles */
  null,
  /* scopeId */
  null,
  /* moduleIdentifier (server only) */
  null
)

module.exports = Component.exports


/***/ }),
/* 67 */
/***/ (function(module, exports, __webpack_require__) {

var Component = __webpack_require__(0)(
  /* script */
  __webpack_require__(189),
  /* template */
  __webpack_require__(190),
  /* styles */
  null,
  /* scopeId */
  null,
  /* moduleIdentifier (server only) */
  null
)

module.exports = Component.exports


/***/ }),
/* 68 */
/***/ (function(module, exports, __webpack_require__) {

var Component = __webpack_require__(0)(
  /* script */
  __webpack_require__(197),
  /* template */
  __webpack_require__(198),
  /* styles */
  null,
  /* scopeId */
  null,
  /* moduleIdentifier (server only) */
  null
)

module.exports = Component.exports


/***/ }),
/* 69 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
var components_namespaceObject = {};
__webpack_require__.d(components_namespaceObject, "Autocomplete", function() { return autocomplete; });
__webpack_require__.d(components_namespaceObject, "Button", function() { return components_button; });
__webpack_require__.d(components_namespaceObject, "Checkbox", function() { return components_checkbox; });
__webpack_require__.d(components_namespaceObject, "Clockpicker", function() { return clockpicker; });
__webpack_require__.d(components_namespaceObject, "Collapse", function() { return collapse; });
__webpack_require__.d(components_namespaceObject, "Datepicker", function() { return datepicker; });
__webpack_require__.d(components_namespaceObject, "Dialog", function() { return dialog; });
__webpack_require__.d(components_namespaceObject, "Dropdown", function() { return dropdown; });
__webpack_require__.d(components_namespaceObject, "Field", function() { return field; });
__webpack_require__.d(components_namespaceObject, "Icon", function() { return icon; });
__webpack_require__.d(components_namespaceObject, "Input", function() { return input; });
__webpack_require__.d(components_namespaceObject, "Loading", function() { return loading; });
__webpack_require__.d(components_namespaceObject, "Message", function() { return components_message; });
__webpack_require__.d(components_namespaceObject, "Modal", function() { return modal; });
__webpack_require__.d(components_namespaceObject, "Notification", function() { return notification; });
__webpack_require__.d(components_namespaceObject, "Pagination", function() { return pagination; });
__webpack_require__.d(components_namespaceObject, "Radio", function() { return components_radio; });
__webpack_require__.d(components_namespaceObject, "Select", function() { return components_select; });
__webpack_require__.d(components_namespaceObject, "Snackbar", function() { return snackbar; });
__webpack_require__.d(components_namespaceObject, "Switch", function() { return components_switch; });
__webpack_require__.d(components_namespaceObject, "Table", function() { return table; });
__webpack_require__.d(components_namespaceObject, "Tabs", function() { return tabs; });
__webpack_require__.d(components_namespaceObject, "Tag", function() { return tag; });
__webpack_require__.d(components_namespaceObject, "Taginput", function() { return taginput; });
__webpack_require__.d(components_namespaceObject, "Timepicker", function() { return timepicker; });
__webpack_require__.d(components_namespaceObject, "Toast", function() { return toast; });
__webpack_require__.d(components_namespaceObject, "Tooltip", function() { return tooltip; });
__webpack_require__.d(components_namespaceObject, "Upload", function() { return upload; });

// EXTERNAL MODULE: ./node_modules/babel-runtime/core-js/object/assign.js
var object_assign = __webpack_require__(10);
var assign_default = /*#__PURE__*/__webpack_require__.n(object_assign);

// EXTERNAL MODULE: ./src/scss/buefy-build.scss
var buefy_build = __webpack_require__(76);
var buefy_build_default = /*#__PURE__*/__webpack_require__.n(buefy_build);

// EXTERNAL MODULE: ./src/components/autocomplete/Autocomplete.vue
var Autocomplete = __webpack_require__(52);
var Autocomplete_default = /*#__PURE__*/__webpack_require__.n(Autocomplete);

// CONCATENATED MODULE: ./src/utils/plugins.js

var use = function use(plugin) {
    if (typeof window !== 'undefined' && window.Vue) {
        window.Vue.use(plugin);
    }
};

var registerComponent = function registerComponent(Vue, component) {
    Vue.component(component.name, component);
};

var registerComponentProgrammatic = function registerComponentProgrammatic(Vue, property, component) {
    Vue.prototype[property] = component;
};
// CONCATENATED MODULE: ./src/components/autocomplete/index.js




var Plugin = {
    install: function install(Vue) {
        registerComponent(Vue, Autocomplete_default.a);
    }
};

use(Plugin);

/* harmony default export */ var autocomplete = (Plugin);


// EXTERNAL MODULE: ./src/components/button/Button.vue
var Button = __webpack_require__(109);
var Button_default = /*#__PURE__*/__webpack_require__.n(Button);

// CONCATENATED MODULE: ./src/components/button/index.js




var button_Plugin = {
    install: function install(Vue) {
        registerComponent(Vue, Button_default.a);
    }
};

use(button_Plugin);

/* harmony default export */ var components_button = (button_Plugin);


// EXTERNAL MODULE: ./src/components/checkbox/Checkbox.vue
var Checkbox = __webpack_require__(61);
var Checkbox_default = /*#__PURE__*/__webpack_require__.n(Checkbox);

// EXTERNAL MODULE: ./src/components/checkbox/CheckboxButton.vue
var CheckboxButton = __webpack_require__(114);
var CheckboxButton_default = /*#__PURE__*/__webpack_require__.n(CheckboxButton);

// CONCATENATED MODULE: ./src/components/checkbox/index.js





var checkbox_Plugin = {
    install: function install(Vue) {
        registerComponent(Vue, Checkbox_default.a);
        registerComponent(Vue, CheckboxButton_default.a);
    }
};

use(checkbox_Plugin);

/* harmony default export */ var components_checkbox = (checkbox_Plugin);


// EXTERNAL MODULE: ./src/components/collapse/Collapse.vue
var Collapse = __webpack_require__(117);
var Collapse_default = /*#__PURE__*/__webpack_require__.n(Collapse);

// CONCATENATED MODULE: ./src/components/collapse/index.js




var collapse_Plugin = {
    install: function install(Vue) {
        registerComponent(Vue, Collapse_default.a);
    }
};

use(collapse_Plugin);

/* harmony default export */ var collapse = (collapse_Plugin);


// EXTERNAL MODULE: ./src/components/clockpicker/Clockpicker.vue
var Clockpicker = __webpack_require__(120);
var Clockpicker_default = /*#__PURE__*/__webpack_require__.n(Clockpicker);

// CONCATENATED MODULE: ./src/components/clockpicker/index.js




var clockpicker_Plugin = {
    install: function install(Vue) {
        registerComponent(Vue, Clockpicker_default.a);
    }
};

use(clockpicker_Plugin);

/* harmony default export */ var clockpicker = (clockpicker_Plugin);


// EXTERNAL MODULE: ./src/components/datepicker/Datepicker.vue
var Datepicker = __webpack_require__(134);
var Datepicker_default = /*#__PURE__*/__webpack_require__.n(Datepicker);

// CONCATENATED MODULE: ./src/components/datepicker/index.js




var datepicker_Plugin = {
    install: function install(Vue) {
        registerComponent(Vue, Datepicker_default.a);
    }
};

use(datepicker_Plugin);

/* harmony default export */ var datepicker = (datepicker_Plugin);


// EXTERNAL MODULE: external {"commonjs":"vue","commonjs2":"vue","amd":"vue","root":"Vue"}
var external___commonjs___vue___commonjs2___vue___amd___vue___root___Vue__ = __webpack_require__(17);
var external___commonjs___vue___commonjs2___vue___amd___vue___root___Vue___default = /*#__PURE__*/__webpack_require__.n(external___commonjs___vue___commonjs2___vue___amd___vue___root___Vue__);

// EXTERNAL MODULE: ./src/components/dialog/Dialog.vue
var Dialog = __webpack_require__(145);
var Dialog_default = /*#__PURE__*/__webpack_require__.n(Dialog);

// CONCATENATED MODULE: ./src/components/dialog/index.js






function dialog_open(propsData) {
    var vm = typeof window !== 'undefined' && window.Vue ? window.Vue : external___commonjs___vue___commonjs2___vue___amd___vue___root___Vue___default.a;
    var DialogComponent = vm.extend(Dialog_default.a);
    return new DialogComponent({
        el: document.createElement('div'),
        propsData: propsData
    });
}

var DialogProgrammatic = {
    alert: function alert(params) {
        var message = void 0;
        if (typeof params === 'string') message = params;
        var defaultParam = {
            canCancel: false,
            message: message
        };
        var propsData = assign_default()(defaultParam, params);
        return dialog_open(propsData);
    },
    confirm: function confirm(params) {
        var defaultParam = {};
        var propsData = assign_default()(defaultParam, params);
        return dialog_open(propsData);
    },
    prompt: function prompt(params) {
        var defaultParam = {
            hasInput: true,
            confirmText: 'Done'
        };
        var propsData = assign_default()(defaultParam, params);
        return dialog_open(propsData);
    }
};

var dialog_Plugin = {
    install: function install(Vue) {
        registerComponent(Vue, Dialog_default.a);
        registerComponentProgrammatic(Vue, '$dialog', DialogProgrammatic);
    }
};

use(dialog_Plugin);

/* harmony default export */ var dialog = (dialog_Plugin);


// EXTERNAL MODULE: ./src/components/dropdown/Dropdown.vue
var Dropdown = __webpack_require__(28);
var Dropdown_default = /*#__PURE__*/__webpack_require__.n(Dropdown);

// EXTERNAL MODULE: ./src/components/dropdown/DropdownItem.vue
var DropdownItem = __webpack_require__(29);
var DropdownItem_default = /*#__PURE__*/__webpack_require__.n(DropdownItem);

// CONCATENATED MODULE: ./src/components/dropdown/index.js





var dropdown_Plugin = {
    install: function install(Vue) {
        registerComponent(Vue, Dropdown_default.a);
        registerComponent(Vue, DropdownItem_default.a);
    }
};

use(dropdown_Plugin);

/* harmony default export */ var dropdown = (dropdown_Plugin);


// EXTERNAL MODULE: ./src/components/field/Field.vue
var Field = __webpack_require__(30);
var Field_default = /*#__PURE__*/__webpack_require__.n(Field);

// CONCATENATED MODULE: ./src/components/field/index.js




var field_Plugin = {
    install: function install(Vue) {
        registerComponent(Vue, Field_default.a);
    }
};

use(field_Plugin);

/* harmony default export */ var field = (field_Plugin);


// EXTERNAL MODULE: ./src/components/icon/Icon.vue
var Icon = __webpack_require__(3);
var Icon_default = /*#__PURE__*/__webpack_require__.n(Icon);

// CONCATENATED MODULE: ./src/components/icon/index.js




var icon_Plugin = {
    install: function install(Vue) {
        registerComponent(Vue, Icon_default.a);
    }
};

use(icon_Plugin);

/* harmony default export */ var icon = (icon_Plugin);


// EXTERNAL MODULE: ./src/components/input/Input.vue
var Input = __webpack_require__(23);
var Input_default = /*#__PURE__*/__webpack_require__.n(Input);

// CONCATENATED MODULE: ./src/components/input/index.js




var input_Plugin = {
    install: function install(Vue) {
        registerComponent(Vue, Input_default.a);
    }
};

use(input_Plugin);

/* harmony default export */ var input = (input_Plugin);


// EXTERNAL MODULE: ./src/components/loading/Loading.vue
var Loading = __webpack_require__(150);
var Loading_default = /*#__PURE__*/__webpack_require__.n(Loading);

// CONCATENATED MODULE: ./src/components/loading/index.js






var LoadingProgrammatic = {
    open: function open(params) {
        var defaultParam = {
            programmatic: true
        };
        var propsData = assign_default()(defaultParam, params);

        var vm = typeof window !== 'undefined' && window.Vue ? window.Vue : external___commonjs___vue___commonjs2___vue___amd___vue___root___Vue___default.a;
        var LoadingComponent = vm.extend(Loading_default.a);
        return new LoadingComponent({
            el: document.createElement('div'),
            propsData: propsData
        });
    }
};

var loading_Plugin = {
    install: function install(Vue) {
        registerComponent(Vue, Loading_default.a);
        registerComponentProgrammatic(Vue, '$loading', LoadingProgrammatic);
    }
};

use(loading_Plugin);

/* harmony default export */ var loading = (loading_Plugin);


// EXTERNAL MODULE: ./src/components/message/Message.vue
var Message = __webpack_require__(153);
var Message_default = /*#__PURE__*/__webpack_require__.n(Message);

// CONCATENATED MODULE: ./src/components/message/index.js




var message_Plugin = {
    install: function install(Vue) {
        registerComponent(Vue, Message_default.a);
    }
};

use(message_Plugin);

/* harmony default export */ var components_message = (message_Plugin);


// EXTERNAL MODULE: ./src/components/modal/Modal.vue
var Modal = __webpack_require__(63);
var Modal_default = /*#__PURE__*/__webpack_require__.n(Modal);

// CONCATENATED MODULE: ./src/components/modal/index.js






var ModalProgrammatic = {
    open: function open(params) {
        var content = void 0;
        var parent = void 0;
        if (typeof params === 'string') content = params;

        var defaultParam = {
            programmatic: true,
            content: content
        };
        if (params.parent) {
            parent = params.parent;
            delete params.parent;
        }
        var propsData = assign_default()(defaultParam, params);

        var vm = typeof window !== 'undefined' && window.Vue ? window.Vue : external___commonjs___vue___commonjs2___vue___amd___vue___root___Vue___default.a;
        var ModalComponent = vm.extend(Modal_default.a);
        return new ModalComponent({
            parent: parent,
            el: document.createElement('div'),
            propsData: propsData
        });
    }
};

var modal_Plugin = {
    install: function install(Vue) {
        registerComponent(Vue, Modal_default.a);
        registerComponentProgrammatic(Vue, '$modal', ModalProgrammatic);
    }
};

use(modal_Plugin);

/* harmony default export */ var modal = (modal_Plugin);


// EXTERNAL MODULE: ./src/components/notification/Notification.vue
var Notification = __webpack_require__(156);
var Notification_default = /*#__PURE__*/__webpack_require__.n(Notification);

// EXTERNAL MODULE: ./src/components/notification/NotificationNotice.vue
var NotificationNotice = __webpack_require__(159);
var NotificationNotice_default = /*#__PURE__*/__webpack_require__.n(NotificationNotice);

// EXTERNAL MODULE: ./src/utils/config.js
var config = __webpack_require__(2);

// CONCATENATED MODULE: ./src/components/notification/index.js








var NotificationProgrammatic = {
    open: function open(params) {
        var message = void 0;
        var parent = void 0;
        if (typeof params === 'string') message = params;

        var defaultParam = {
            message: message,
            position: config["a" /* default */].defaultNotificationPosition || 'is-top-right'
        };
        if (params.parent) {
            parent = params.parent;
            delete params.parent;
        }
        var propsData = assign_default()(defaultParam, typeof params === 'string' ? {} : params);

        var vm = typeof window !== 'undefined' && window.Vue ? window.Vue : external___commonjs___vue___commonjs2___vue___amd___vue___root___Vue___default.a;
        var NotificationNoticeComponent = vm.extend(NotificationNotice_default.a);
        return new NotificationNoticeComponent({
            parent: parent,
            el: document.createElement('div'),
            propsData: propsData
        });
    }
};

var notification_Plugin = {
    install: function install(Vue) {
        registerComponent(Vue, Notification_default.a);
        registerComponentProgrammatic(Vue, '$notification', NotificationProgrammatic);
    }
};

use(notification_Plugin);

/* harmony default export */ var notification = (notification_Plugin);


// EXTERNAL MODULE: ./src/components/pagination/Pagination.vue
var Pagination = __webpack_require__(66);
var Pagination_default = /*#__PURE__*/__webpack_require__.n(Pagination);

// CONCATENATED MODULE: ./src/components/pagination/index.js




var pagination_Plugin = {
    install: function install(Vue) {
        registerComponent(Vue, Pagination_default.a);
    }
};

use(pagination_Plugin);

/* harmony default export */ var pagination = (pagination_Plugin);


// EXTERNAL MODULE: ./src/components/radio/Radio.vue
var Radio = __webpack_require__(164);
var Radio_default = /*#__PURE__*/__webpack_require__.n(Radio);

// EXTERNAL MODULE: ./src/components/radio/RadioButton.vue
var RadioButton = __webpack_require__(167);
var RadioButton_default = /*#__PURE__*/__webpack_require__.n(RadioButton);

// CONCATENATED MODULE: ./src/components/radio/index.js





var radio_Plugin = {
    install: function install(Vue) {
        registerComponent(Vue, Radio_default.a);
        registerComponent(Vue, RadioButton_default.a);
    }
};

use(radio_Plugin);

/* harmony default export */ var components_radio = (radio_Plugin);


// EXTERNAL MODULE: ./src/components/select/Select.vue
var Select = __webpack_require__(31);
var Select_default = /*#__PURE__*/__webpack_require__.n(Select);

// CONCATENATED MODULE: ./src/components/select/index.js




var select_Plugin = {
    install: function install(Vue) {
        registerComponent(Vue, Select_default.a);
    }
};

use(select_Plugin);

/* harmony default export */ var components_select = (select_Plugin);


// EXTERNAL MODULE: ./src/components/snackbar/Snackbar.vue
var Snackbar = __webpack_require__(170);
var Snackbar_default = /*#__PURE__*/__webpack_require__.n(Snackbar);

// CONCATENATED MODULE: ./src/components/snackbar/index.js







var SnackbarProgrammatic = {
    open: function open(params) {
        var message = void 0;
        var parent = void 0;
        if (typeof params === 'string') message = params;

        var defaultParam = {
            type: 'is-success',
            position: config["a" /* default */].defaultSnackbarPosition || 'is-bottom-right',
            message: message
        };
        if (params.parent) {
            parent = params.parent;
            delete params.parent;
        }
        var propsData = assign_default()(defaultParam, params);

        var vm = typeof window !== 'undefined' && window.Vue ? window.Vue : external___commonjs___vue___commonjs2___vue___amd___vue___root___Vue___default.a;
        var SnackbarComponent = vm.extend(Snackbar_default.a);
        return new SnackbarComponent({
            parent: parent,
            el: document.createElement('div'),
            propsData: propsData
        });
    }
};

var snackbar_Plugin = {
    install: function install(Vue) {
        registerComponentProgrammatic(Vue, '$snackbar', SnackbarProgrammatic);
    }
};

use(snackbar_Plugin);

/* harmony default export */ var snackbar = (snackbar_Plugin);


// EXTERNAL MODULE: ./src/components/switch/Switch.vue
var Switch = __webpack_require__(173);
var Switch_default = /*#__PURE__*/__webpack_require__.n(Switch);

// CONCATENATED MODULE: ./src/components/switch/index.js




var switch_Plugin = {
    install: function install(Vue) {
        registerComponent(Vue, Switch_default.a);
    }
};

use(switch_Plugin);

/* harmony default export */ var components_switch = (switch_Plugin);


// EXTERNAL MODULE: ./src/components/table/Table.vue
var Table = __webpack_require__(176);
var Table_default = /*#__PURE__*/__webpack_require__.n(Table);

// EXTERNAL MODULE: ./src/components/table/TableColumn.vue
var TableColumn = __webpack_require__(67);
var TableColumn_default = /*#__PURE__*/__webpack_require__.n(TableColumn);

// CONCATENATED MODULE: ./src/components/table/index.js





var table_Plugin = {
    install: function install(Vue) {
        registerComponent(Vue, Table_default.a);
        registerComponent(Vue, TableColumn_default.a);
    }
};

use(table_Plugin);

/* harmony default export */ var table = (table_Plugin);


// EXTERNAL MODULE: ./src/components/tabs/Tabs.vue
var Tabs = __webpack_require__(192);
var Tabs_default = /*#__PURE__*/__webpack_require__.n(Tabs);

// EXTERNAL MODULE: ./src/components/tabs/TabItem.vue
var TabItem = __webpack_require__(195);
var TabItem_default = /*#__PURE__*/__webpack_require__.n(TabItem);

// CONCATENATED MODULE: ./src/components/tabs/index.js





var tabs_Plugin = {
    install: function install(Vue) {
        registerComponent(Vue, Tabs_default.a);
        registerComponent(Vue, TabItem_default.a);
    }
};

use(tabs_Plugin);

/* harmony default export */ var tabs = (tabs_Plugin);


// EXTERNAL MODULE: ./src/components/tag/Tag.vue
var Tag = __webpack_require__(68);
var Tag_default = /*#__PURE__*/__webpack_require__.n(Tag);

// EXTERNAL MODULE: ./src/components/tag/Taglist.vue
var Taglist = __webpack_require__(199);
var Taglist_default = /*#__PURE__*/__webpack_require__.n(Taglist);

// CONCATENATED MODULE: ./src/components/tag/index.js





var tag_Plugin = {
    install: function install(Vue) {
        registerComponent(Vue, Tag_default.a);
        registerComponent(Vue, Taglist_default.a);
    }
};

use(tag_Plugin);

/* harmony default export */ var tag = (tag_Plugin);


// EXTERNAL MODULE: ./src/components/taginput/Taginput.vue
var Taginput = __webpack_require__(202);
var Taginput_default = /*#__PURE__*/__webpack_require__.n(Taginput);

// CONCATENATED MODULE: ./src/components/taginput/index.js




var taginput_Plugin = {
    install: function install(Vue) {
        registerComponent(Vue, Taginput_default.a);
    }
};

use(taginput_Plugin);

/* harmony default export */ var taginput = (taginput_Plugin);


// EXTERNAL MODULE: ./src/components/timepicker/Timepicker.vue
var Timepicker = __webpack_require__(205);
var Timepicker_default = /*#__PURE__*/__webpack_require__.n(Timepicker);

// CONCATENATED MODULE: ./src/components/timepicker/index.js




var timepicker_Plugin = {
    install: function install(Vue) {
        registerComponent(Vue, Timepicker_default.a);
    }
};

use(timepicker_Plugin);

/* harmony default export */ var timepicker = (timepicker_Plugin);


// EXTERNAL MODULE: ./src/components/toast/Toast.vue
var Toast = __webpack_require__(208);
var Toast_default = /*#__PURE__*/__webpack_require__.n(Toast);

// CONCATENATED MODULE: ./src/components/toast/index.js







var ToastProgrammatic = {
    open: function open(params) {
        var message = void 0;
        var parent = void 0;
        if (typeof params === 'string') message = params;

        var defaultParam = {
            message: message,
            position: config["a" /* default */].defaultToastPosition || 'is-top'
        };
        if (params.parent) {
            parent = params.parent;
            delete params.parent;
        }
        var propsData = assign_default()(defaultParam, params);

        var vm = typeof window !== 'undefined' && window.Vue ? window.Vue : external___commonjs___vue___commonjs2___vue___amd___vue___root___Vue___default.a;
        var ToastComponent = vm.extend(Toast_default.a);
        return new ToastComponent({
            parent: parent,
            el: document.createElement('div'),
            propsData: propsData
        });
    }
};

var toast_Plugin = {
    install: function install(Vue) {
        registerComponentProgrammatic(Vue, '$toast', ToastProgrammatic);
    }
};

use(toast_Plugin);

/* harmony default export */ var toast = (toast_Plugin);


// EXTERNAL MODULE: ./src/components/tooltip/Tooltip.vue
var Tooltip = __webpack_require__(211);
var Tooltip_default = /*#__PURE__*/__webpack_require__.n(Tooltip);

// CONCATENATED MODULE: ./src/components/tooltip/index.js




var tooltip_Plugin = {
    install: function install(Vue) {
        registerComponent(Vue, Tooltip_default.a);
    }
};

use(tooltip_Plugin);

/* harmony default export */ var tooltip = (tooltip_Plugin);


// EXTERNAL MODULE: ./src/components/upload/Upload.vue
var Upload = __webpack_require__(214);
var Upload_default = /*#__PURE__*/__webpack_require__.n(Upload);

// CONCATENATED MODULE: ./src/components/upload/index.js




var upload_Plugin = {
    install: function install(Vue) {
        registerComponent(Vue, Upload_default.a);
    }
};

use(upload_Plugin);

/* harmony default export */ var upload = (upload_Plugin);


// CONCATENATED MODULE: ./src/components/index.js






























// CONCATENATED MODULE: ./src/index.js









var Buefy = {
    install: function install(Vue) {
        var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};

        // Options
        Object(config["b" /* setOptions */])(assign_default()(config["a" /* default */], options));
        // Components
        for (var componentKey in components_namespaceObject) {
            Vue.use(components_namespaceObject[componentKey]);
        }
        // Config component
        var BuefyProgrammatic = {
            setOptions: function setOptions(options) {
                Object(config["b" /* setOptions */])(assign_default()(config["a" /* default */], options));
            }
        };
        registerComponentProgrammatic(Vue, '$buefy', BuefyProgrammatic);
    }
};

use(Buefy);

/* harmony default export */ var src = __webpack_exports__["default"] = (Buefy);

/***/ }),
/* 70 */
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__(71);
module.exports = __webpack_require__(6).Object.assign;


/***/ }),
/* 71 */
/***/ (function(module, exports, __webpack_require__) {

// 19.1.3.1 Object.assign(target, source)
var $export = __webpack_require__(18);

$export($export.S + $export.F, 'Object', { assign: __webpack_require__(73) });


/***/ }),
/* 72 */
/***/ (function(module, exports) {

module.exports = function (it) {
  if (typeof it != 'function') throw TypeError(it + ' is not a function!');
  return it;
};


/***/ }),
/* 73 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

// 19.1.2.1 Object.assign(target, source, ...)
var getKeys = __webpack_require__(24);
var gOPS = __webpack_require__(39);
var pIE = __webpack_require__(27);
var toObject = __webpack_require__(40);
var IObject = __webpack_require__(50);
var $assign = Object.assign;

// should work with symbols and should have deterministic property order (V8 bug)
module.exports = !$assign || __webpack_require__(20)(function () {
  var A = {};
  var B = {};
  // eslint-disable-next-line no-undef
  var S = Symbol();
  var K = 'abcdefghijklmnopqrst';
  A[S] = 7;
  K.split('').forEach(function (k) { B[k] = k; });
  return $assign({}, A)[S] != 7 || Object.keys($assign({}, B)).join('') != K;
}) ? function assign(target, source) { // eslint-disable-line no-unused-vars
  var T = toObject(target);
  var aLen = arguments.length;
  var index = 1;
  var getSymbols = gOPS.f;
  var isEnum = pIE.f;
  while (aLen > index) {
    var S = IObject(arguments[index++]);
    var keys = getSymbols ? getKeys(S).concat(getSymbols(S)) : getKeys(S);
    var length = keys.length;
    var j = 0;
    var key;
    while (length > j) if (isEnum.call(S, key = keys[j++])) T[key] = S[key];
  } return T;
} : $assign;


/***/ }),
/* 74 */
/***/ (function(module, exports, __webpack_require__) {

// false -> Array#indexOf
// true  -> Array#includes
var toIObject = __webpack_require__(16);
var toLength = __webpack_require__(51);
var toAbsoluteIndex = __webpack_require__(75);
module.exports = function (IS_INCLUDES) {
  return function ($this, el, fromIndex) {
    var O = toIObject($this);
    var length = toLength(O.length);
    var index = toAbsoluteIndex(fromIndex, length);
    var value;
    // Array#includes uses SameValueZero equality algorithm
    // eslint-disable-next-line no-self-compare
    if (IS_INCLUDES && el != el) while (length > index) {
      value = O[index++];
      // eslint-disable-next-line no-self-compare
      if (value != value) return true;
    // Array#indexOf ignores holes, Array#includes - not
    } else for (;length > index; index++) if (IS_INCLUDES || index in O) {
      if (O[index] === el) return IS_INCLUDES || index || 0;
    } return !IS_INCLUDES && -1;
  };
};


/***/ }),
/* 75 */
/***/ (function(module, exports, __webpack_require__) {

var toInteger = __webpack_require__(35);
var max = Math.max;
var min = Math.min;
module.exports = function (index, length) {
  index = toInteger(index);
  return index < 0 ? max(index + length, 0) : min(index, length);
};


/***/ }),
/* 76 */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),
/* 77 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_typeof__ = __webpack_require__(53);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_typeof___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_typeof__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_babel_runtime_core_js_get_iterator__ = __webpack_require__(59);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_babel_runtime_core_js_get_iterator___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_1_babel_runtime_core_js_get_iterator__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2_babel_runtime_helpers_defineProperty__ = __webpack_require__(1);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2_babel_runtime_helpers_defineProperty___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_2_babel_runtime_helpers_defineProperty__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_3__utils_helpers__ = __webpack_require__(7);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_4__utils_FormElementMixin__ = __webpack_require__(13);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_5__input_Input__ = __webpack_require__(23);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_5__input_Input___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_5__input_Input__);



//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//





/* harmony default export */ __webpack_exports__["default"] = ({
    name: 'BAutocomplete',
    components: __WEBPACK_IMPORTED_MODULE_2_babel_runtime_helpers_defineProperty___default()({}, __WEBPACK_IMPORTED_MODULE_5__input_Input___default.a.name, __WEBPACK_IMPORTED_MODULE_5__input_Input___default.a),
    mixins: [__WEBPACK_IMPORTED_MODULE_4__utils_FormElementMixin__["a" /* default */]],
    inheritAttrs: false,
    props: {
        value: [Number, String],
        data: {
            type: Array,
            default: function _default() {
                return [];
            }
        },
        field: {
            type: String,
            default: 'value'
        },
        keepFirst: Boolean,
        clearOnSelect: Boolean,
        openOnFocus: Boolean,
        customFormatter: Function
    },
    data: function data() {
        return {
            selected: null,
            hovered: null,
            isActive: false,
            newValue: this.value,
            newAutocomplete: this.autocomplete || 'off',
            isListInViewportVertically: true,
            hasFocus: false,
            _isAutocomplete: true,
            _elementRef: 'input'
        };
    },

    computed: {
        /**
         * White-listed items to not close when clicked.
         * Add input, dropdown and all children.
         */
        whiteList: function whiteList() {
            var whiteList = [];
            whiteList.push(this.$refs.input.$el.querySelector('input'));
            whiteList.push(this.$refs.dropdown);
            // Add all chidren from dropdown
            if (this.$refs.dropdown !== undefined) {
                var children = this.$refs.dropdown.querySelectorAll('*');
                var _iteratorNormalCompletion = true;
                var _didIteratorError = false;
                var _iteratorError = undefined;

                try {
                    for (var _iterator = __WEBPACK_IMPORTED_MODULE_1_babel_runtime_core_js_get_iterator___default()(children), _step; !(_iteratorNormalCompletion = (_step = _iterator.next()).done); _iteratorNormalCompletion = true) {
                        var child = _step.value;

                        whiteList.push(child);
                    }
                } catch (err) {
                    _didIteratorError = true;
                    _iteratorError = err;
                } finally {
                    try {
                        if (!_iteratorNormalCompletion && _iterator.return) {
                            _iterator.return();
                        }
                    } finally {
                        if (_didIteratorError) {
                            throw _iteratorError;
                        }
                    }
                }
            }

            return whiteList;
        },


        /**
         * Check if exists default slot
         */
        hasDefaultSlot: function hasDefaultSlot() {
            return !!this.$scopedSlots.default;
        },


        /**
         * Check if exists "empty" slot
         */
        hasEmptySlot: function hasEmptySlot() {
            return !!this.$slots.empty;
        },


        /**
         * Check if exists "header" slot
         */
        hasHeaderSlot: function hasHeaderSlot() {
            return !!this.$slots.header;
        }
    },
    watch: {
        /**
         * When dropdown is toggled, check the visibility to know when
         * to open upwards.
         */
        isActive: function isActive(active) {
            var _this = this;

            if (active) {
                this.calcDropdownInViewportVertical();
            } else {
                this.$nextTick(function () {
                    return _this.setHovered(null);
                });
                // Timeout to wait for the animation to finish before recalculating
                setTimeout(function () {
                    _this.calcDropdownInViewportVertical();
                }, 100);
            }
        },


        /**
         * When updating input's value
         *   1. Emit changes
         *   2. If value isn't the same as selected, set null
         *   3. Close dropdown if value is clear or else open it
         */
        newValue: function newValue(value) {
            this.$emit('input', value);
            // Check if selected is invalid
            var currentValue = this.getValue(this.selected);
            if (currentValue && currentValue !== value) {
                this.setSelected(null, false);
            }
            // Close dropdown if input is clear or else open it
            if (this.hasFocus && (!this.openOnFocus || value)) {
                this.isActive = !!value;
            }
        },


        /**
         * When v-model is changed:
         *   1. Update internal value.
         *   2. If it's invalid, validate again.
         */
        value: function value(_value) {
            this.newValue = _value;
            !this.isValid && this.$refs.input.checkHtml5Validity();
        },


        /**
         * Select first option if "keep-first
         */
        data: function data(value) {
            // Keep first option always pre-selected
            if (this.keepFirst) {
                this.selectFirstOption(value);
            }
        }
    },
    methods: {
        /**
         * Set which option is currently hovered.
         */
        setHovered: function setHovered(option) {
            if (option === undefined) return;

            this.hovered = option;
        },


        /**
         * Set which option is currently selected, update v-model,
         * update input value and close dropdown.
         */
        setSelected: function setSelected(option) {
            var _this2 = this;

            var closeDropdown = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : true;

            if (option === undefined) return;

            this.selected = option;
            this.$emit('select', this.selected);
            if (this.selected !== null) {
                this.newValue = this.clearOnSelect ? '' : this.getValue(this.selected);
            }
            closeDropdown && this.$nextTick(function () {
                _this2.isActive = false;
            });
        },


        /**
         * Select first option
         */
        selectFirstOption: function selectFirstOption(options) {
            var _this3 = this;

            this.$nextTick(function () {
                if (options.length) {
                    // If has visible data or open on focus, keep updating the hovered
                    if (_this3.openOnFocus || _this3.newValue !== '' && _this3.hovered !== options[0]) {
                        _this3.setHovered(options[0]);
                    }
                } else {
                    _this3.setHovered(null);
                }
            });
        },


        /**
         * Enter key listener.
         * Select the hovered option.
         */
        enterPressed: function enterPressed() {
            if (this.hovered === null) return;
            this.setSelected(this.hovered);
        },


        /**
         * Tab key listener.
         * Select hovered option if it exists, close dropdown, then allow
         * native handling to move to next tabbable element.
         */
        tabPressed: function tabPressed() {
            if (this.hovered === null) {
                this.isActive = false;
                return;
            }
            this.setSelected(this.hovered);
        },


        /**
         * Close dropdown if clicked outside.
         */
        clickedOutside: function clickedOutside(event) {
            if (this.whiteList.indexOf(event.target) < 0) this.isActive = false;
        },


        /**
         * Return display text for the input.
         * If object, get value from path, or else just the value.
         */
        getValue: function getValue(option) {
            if (!option) return;

            if (typeof this.customFormatter !== 'undefined') {
                return this.customFormatter(option);
            }
            return (typeof option === 'undefined' ? 'undefined' : __WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_typeof___default()(option)) === 'object' ? Object(__WEBPACK_IMPORTED_MODULE_3__utils_helpers__["a" /* getValueByPath */])(option, this.field) : option;
        },


        /**
         * Calculate if the dropdown is vertically visible when activated,
         * otherwise it is openened upwards.
         */
        calcDropdownInViewportVertical: function calcDropdownInViewportVertical() {
            var _this4 = this;

            this.$nextTick(function () {
                /**
                 * this.$refs.dropdown may be undefined
                 * when Autocomplete is conditional rendered
                 */
                if (_this4.$refs.dropdown === undefined) return;

                var rect = _this4.$refs.dropdown.getBoundingClientRect();

                _this4.isListInViewportVertically = rect.top >= 0 && rect.bottom <= (window.innerHeight || document.documentElement.clientHeight);
            });
        },


        /**
         * Arrows keys listener.
         * If dropdown is active, set hovered option, or else just open.
         */
        keyArrows: function keyArrows(direction) {
            var sum = direction === 'down' ? 1 : -1;
            if (this.isActive) {
                var index = this.data.indexOf(this.hovered) + sum;
                index = index > this.data.length - 1 ? this.data.length : index;
                index = index < 0 ? 0 : index;

                this.setHovered(this.data[index]);

                var list = this.$refs.dropdown.querySelector('.dropdown-content');
                var element = list.querySelectorAll('a.dropdown-item:not(.is-disabled)')[index];

                if (!element) return;

                var visMin = list.scrollTop;
                var visMax = list.scrollTop + list.clientHeight - element.clientHeight;

                if (element.offsetTop < visMin) {
                    list.scrollTop = element.offsetTop;
                } else if (element.offsetTop >= visMax) {
                    list.scrollTop = element.offsetTop - list.clientHeight + element.clientHeight;
                }
            } else {
                this.isActive = true;
            }
        },


        /**
         * Focus listener.
         * If value is the same as selected, select all text.
         */
        focused: function focused(event) {
            if (this.getValue(this.selected) === this.newValue) {
                this.$el.querySelector('input').select();
            }
            if (this.openOnFocus) {
                this.isActive = true;
                if (this.keepFirst) {
                    this.selectFirstOption(this.data);
                }
            }
            this.hasFocus = true;
            this.$emit('focus', event);
        },


        /**
         * Blur listener.
        */
        onBlur: function onBlur(event) {
            this.hasFocus = false;
            this.$emit('blur', event);
        },
        onInput: function onInput(event) {
            var currentValue = this.getValue(this.selected);
            if (currentValue && currentValue === this.newValue) return;
            this.$emit('typing', this.newValue);
        }
    },
    created: function created() {
        if (typeof window !== 'undefined') {
            document.addEventListener('click', this.clickedOutside);
            window.addEventListener('resize', this.calcDropdownInViewportVertical);
        }
    },
    beforeDestroy: function beforeDestroy() {
        if (typeof window !== 'undefined') {
            document.removeEventListener('click', this.clickedOutside);
            window.removeEventListener('resize', this.calcDropdownInViewportVertical);
        }
    }
});

/***/ }),
/* 78 */
/***/ (function(module, exports, __webpack_require__) {

module.exports = { "default": __webpack_require__(79), __esModule: true };

/***/ }),
/* 79 */
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__(41);
__webpack_require__(57);
module.exports = __webpack_require__(43).f('iterator');


/***/ }),
/* 80 */
/***/ (function(module, exports, __webpack_require__) {

var toInteger = __webpack_require__(35);
var defined = __webpack_require__(34);
// true  -> String#at
// false -> String#codePointAt
module.exports = function (TO_STRING) {
  return function (that, pos) {
    var s = String(defined(that));
    var i = toInteger(pos);
    var l = s.length;
    var a, b;
    if (i < 0 || i >= l) return TO_STRING ? '' : undefined;
    a = s.charCodeAt(i);
    return a < 0xd800 || a > 0xdbff || i + 1 === l || (b = s.charCodeAt(i + 1)) < 0xdc00 || b > 0xdfff
      ? TO_STRING ? s.charAt(i) : a
      : TO_STRING ? s.slice(i, i + 2) : (a - 0xd800 << 10) + (b - 0xdc00) + 0x10000;
  };
};


/***/ }),
/* 81 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

var create = __webpack_require__(56);
var descriptor = __webpack_require__(21);
var setToStringTag = __webpack_require__(42);
var IteratorPrototype = {};

// 25.1.2.1.1 %IteratorPrototype%[@@iterator]()
__webpack_require__(14)(IteratorPrototype, __webpack_require__(4)('iterator'), function () { return this; });

module.exports = function (Constructor, NAME, next) {
  Constructor.prototype = create(IteratorPrototype, { next: descriptor(1, next) });
  setToStringTag(Constructor, NAME + ' Iterator');
};


/***/ }),
/* 82 */
/***/ (function(module, exports, __webpack_require__) {

var dP = __webpack_require__(9);
var anObject = __webpack_require__(15);
var getKeys = __webpack_require__(24);

module.exports = __webpack_require__(11) ? Object.defineProperties : function defineProperties(O, Properties) {
  anObject(O);
  var keys = getKeys(Properties);
  var length = keys.length;
  var i = 0;
  var P;
  while (length > i) dP.f(O, P = keys[i++], Properties[P]);
  return O;
};


/***/ }),
/* 83 */
/***/ (function(module, exports, __webpack_require__) {

var document = __webpack_require__(8).document;
module.exports = document && document.documentElement;


/***/ }),
/* 84 */
/***/ (function(module, exports, __webpack_require__) {

// 19.1.2.9 / 15.2.3.2 Object.getPrototypeOf(O)
var has = __webpack_require__(12);
var toObject = __webpack_require__(40);
var IE_PROTO = __webpack_require__(36)('IE_PROTO');
var ObjectProto = Object.prototype;

module.exports = Object.getPrototypeOf || function (O) {
  O = toObject(O);
  if (has(O, IE_PROTO)) return O[IE_PROTO];
  if (typeof O.constructor == 'function' && O instanceof O.constructor) {
    return O.constructor.prototype;
  } return O instanceof Object ? ObjectProto : null;
};


/***/ }),
/* 85 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

var addToUnscopables = __webpack_require__(86);
var step = __webpack_require__(87);
var Iterators = __webpack_require__(22);
var toIObject = __webpack_require__(16);

// 22.1.3.4 Array.prototype.entries()
// 22.1.3.13 Array.prototype.keys()
// 22.1.3.29 Array.prototype.values()
// 22.1.3.30 Array.prototype[@@iterator]()
module.exports = __webpack_require__(54)(Array, 'Array', function (iterated, kind) {
  this._t = toIObject(iterated); // target
  this._i = 0;                   // next index
  this._k = kind;                // kind
// 22.1.5.2.1 %ArrayIteratorPrototype%.next()
}, function () {
  var O = this._t;
  var kind = this._k;
  var index = this._i++;
  if (!O || index >= O.length) {
    this._t = undefined;
    return step(1);
  }
  if (kind == 'keys') return step(0, index);
  if (kind == 'values') return step(0, O[index]);
  return step(0, [index, O[index]]);
}, 'values');

// argumentsList[@@iterator] is %ArrayProto_values% (9.4.4.6, 9.4.4.7)
Iterators.Arguments = Iterators.Array;

addToUnscopables('keys');
addToUnscopables('values');
addToUnscopables('entries');


/***/ }),
/* 86 */
/***/ (function(module, exports) {

module.exports = function () { /* empty */ };


/***/ }),
/* 87 */
/***/ (function(module, exports) {

module.exports = function (done, value) {
  return { value: value, done: !!done };
};


/***/ }),
/* 88 */
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__(89);
__webpack_require__(95);
__webpack_require__(96);
__webpack_require__(97);
module.exports = __webpack_require__(6).Symbol;


/***/ }),
/* 89 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

// ECMAScript 6 symbols shim
var global = __webpack_require__(8);
var has = __webpack_require__(12);
var DESCRIPTORS = __webpack_require__(11);
var $export = __webpack_require__(18);
var redefine = __webpack_require__(55);
var META = __webpack_require__(90).KEY;
var $fails = __webpack_require__(20);
var shared = __webpack_require__(37);
var setToStringTag = __webpack_require__(42);
var uid = __webpack_require__(26);
var wks = __webpack_require__(4);
var wksExt = __webpack_require__(43);
var wksDefine = __webpack_require__(44);
var enumKeys = __webpack_require__(91);
var isArray = __webpack_require__(92);
var anObject = __webpack_require__(15);
var isObject = __webpack_require__(19);
var toIObject = __webpack_require__(16);
var toPrimitive = __webpack_require__(32);
var createDesc = __webpack_require__(21);
var _create = __webpack_require__(56);
var gOPNExt = __webpack_require__(93);
var $GOPD = __webpack_require__(94);
var $DP = __webpack_require__(9);
var $keys = __webpack_require__(24);
var gOPD = $GOPD.f;
var dP = $DP.f;
var gOPN = gOPNExt.f;
var $Symbol = global.Symbol;
var $JSON = global.JSON;
var _stringify = $JSON && $JSON.stringify;
var PROTOTYPE = 'prototype';
var HIDDEN = wks('_hidden');
var TO_PRIMITIVE = wks('toPrimitive');
var isEnum = {}.propertyIsEnumerable;
var SymbolRegistry = shared('symbol-registry');
var AllSymbols = shared('symbols');
var OPSymbols = shared('op-symbols');
var ObjectProto = Object[PROTOTYPE];
var USE_NATIVE = typeof $Symbol == 'function';
var QObject = global.QObject;
// Don't use setters in Qt Script, https://github.com/zloirock/core-js/issues/173
var setter = !QObject || !QObject[PROTOTYPE] || !QObject[PROTOTYPE].findChild;

// fallback for old Android, https://code.google.com/p/v8/issues/detail?id=687
var setSymbolDesc = DESCRIPTORS && $fails(function () {
  return _create(dP({}, 'a', {
    get: function () { return dP(this, 'a', { value: 7 }).a; }
  })).a != 7;
}) ? function (it, key, D) {
  var protoDesc = gOPD(ObjectProto, key);
  if (protoDesc) delete ObjectProto[key];
  dP(it, key, D);
  if (protoDesc && it !== ObjectProto) dP(ObjectProto, key, protoDesc);
} : dP;

var wrap = function (tag) {
  var sym = AllSymbols[tag] = _create($Symbol[PROTOTYPE]);
  sym._k = tag;
  return sym;
};

var isSymbol = USE_NATIVE && typeof $Symbol.iterator == 'symbol' ? function (it) {
  return typeof it == 'symbol';
} : function (it) {
  return it instanceof $Symbol;
};

var $defineProperty = function defineProperty(it, key, D) {
  if (it === ObjectProto) $defineProperty(OPSymbols, key, D);
  anObject(it);
  key = toPrimitive(key, true);
  anObject(D);
  if (has(AllSymbols, key)) {
    if (!D.enumerable) {
      if (!has(it, HIDDEN)) dP(it, HIDDEN, createDesc(1, {}));
      it[HIDDEN][key] = true;
    } else {
      if (has(it, HIDDEN) && it[HIDDEN][key]) it[HIDDEN][key] = false;
      D = _create(D, { enumerable: createDesc(0, false) });
    } return setSymbolDesc(it, key, D);
  } return dP(it, key, D);
};
var $defineProperties = function defineProperties(it, P) {
  anObject(it);
  var keys = enumKeys(P = toIObject(P));
  var i = 0;
  var l = keys.length;
  var key;
  while (l > i) $defineProperty(it, key = keys[i++], P[key]);
  return it;
};
var $create = function create(it, P) {
  return P === undefined ? _create(it) : $defineProperties(_create(it), P);
};
var $propertyIsEnumerable = function propertyIsEnumerable(key) {
  var E = isEnum.call(this, key = toPrimitive(key, true));
  if (this === ObjectProto && has(AllSymbols, key) && !has(OPSymbols, key)) return false;
  return E || !has(this, key) || !has(AllSymbols, key) || has(this, HIDDEN) && this[HIDDEN][key] ? E : true;
};
var $getOwnPropertyDescriptor = function getOwnPropertyDescriptor(it, key) {
  it = toIObject(it);
  key = toPrimitive(key, true);
  if (it === ObjectProto && has(AllSymbols, key) && !has(OPSymbols, key)) return;
  var D = gOPD(it, key);
  if (D && has(AllSymbols, key) && !(has(it, HIDDEN) && it[HIDDEN][key])) D.enumerable = true;
  return D;
};
var $getOwnPropertyNames = function getOwnPropertyNames(it) {
  var names = gOPN(toIObject(it));
  var result = [];
  var i = 0;
  var key;
  while (names.length > i) {
    if (!has(AllSymbols, key = names[i++]) && key != HIDDEN && key != META) result.push(key);
  } return result;
};
var $getOwnPropertySymbols = function getOwnPropertySymbols(it) {
  var IS_OP = it === ObjectProto;
  var names = gOPN(IS_OP ? OPSymbols : toIObject(it));
  var result = [];
  var i = 0;
  var key;
  while (names.length > i) {
    if (has(AllSymbols, key = names[i++]) && (IS_OP ? has(ObjectProto, key) : true)) result.push(AllSymbols[key]);
  } return result;
};

// 19.4.1.1 Symbol([description])
if (!USE_NATIVE) {
  $Symbol = function Symbol() {
    if (this instanceof $Symbol) throw TypeError('Symbol is not a constructor!');
    var tag = uid(arguments.length > 0 ? arguments[0] : undefined);
    var $set = function (value) {
      if (this === ObjectProto) $set.call(OPSymbols, value);
      if (has(this, HIDDEN) && has(this[HIDDEN], tag)) this[HIDDEN][tag] = false;
      setSymbolDesc(this, tag, createDesc(1, value));
    };
    if (DESCRIPTORS && setter) setSymbolDesc(ObjectProto, tag, { configurable: true, set: $set });
    return wrap(tag);
  };
  redefine($Symbol[PROTOTYPE], 'toString', function toString() {
    return this._k;
  });

  $GOPD.f = $getOwnPropertyDescriptor;
  $DP.f = $defineProperty;
  __webpack_require__(58).f = gOPNExt.f = $getOwnPropertyNames;
  __webpack_require__(27).f = $propertyIsEnumerable;
  __webpack_require__(39).f = $getOwnPropertySymbols;

  if (DESCRIPTORS && !__webpack_require__(25)) {
    redefine(ObjectProto, 'propertyIsEnumerable', $propertyIsEnumerable, true);
  }

  wksExt.f = function (name) {
    return wrap(wks(name));
  };
}

$export($export.G + $export.W + $export.F * !USE_NATIVE, { Symbol: $Symbol });

for (var es6Symbols = (
  // 19.4.2.2, 19.4.2.3, 19.4.2.4, 19.4.2.6, 19.4.2.8, 19.4.2.9, 19.4.2.10, 19.4.2.11, 19.4.2.12, 19.4.2.13, 19.4.2.14
  'hasInstance,isConcatSpreadable,iterator,match,replace,search,species,split,toPrimitive,toStringTag,unscopables'
).split(','), j = 0; es6Symbols.length > j;)wks(es6Symbols[j++]);

for (var wellKnownSymbols = $keys(wks.store), k = 0; wellKnownSymbols.length > k;) wksDefine(wellKnownSymbols[k++]);

$export($export.S + $export.F * !USE_NATIVE, 'Symbol', {
  // 19.4.2.1 Symbol.for(key)
  'for': function (key) {
    return has(SymbolRegistry, key += '')
      ? SymbolRegistry[key]
      : SymbolRegistry[key] = $Symbol(key);
  },
  // 19.4.2.5 Symbol.keyFor(sym)
  keyFor: function keyFor(sym) {
    if (!isSymbol(sym)) throw TypeError(sym + ' is not a symbol!');
    for (var key in SymbolRegistry) if (SymbolRegistry[key] === sym) return key;
  },
  useSetter: function () { setter = true; },
  useSimple: function () { setter = false; }
});

$export($export.S + $export.F * !USE_NATIVE, 'Object', {
  // 19.1.2.2 Object.create(O [, Properties])
  create: $create,
  // 19.1.2.4 Object.defineProperty(O, P, Attributes)
  defineProperty: $defineProperty,
  // 19.1.2.3 Object.defineProperties(O, Properties)
  defineProperties: $defineProperties,
  // 19.1.2.6 Object.getOwnPropertyDescriptor(O, P)
  getOwnPropertyDescriptor: $getOwnPropertyDescriptor,
  // 19.1.2.7 Object.getOwnPropertyNames(O)
  getOwnPropertyNames: $getOwnPropertyNames,
  // 19.1.2.8 Object.getOwnPropertySymbols(O)
  getOwnPropertySymbols: $getOwnPropertySymbols
});

// 24.3.2 JSON.stringify(value [, replacer [, space]])
$JSON && $export($export.S + $export.F * (!USE_NATIVE || $fails(function () {
  var S = $Symbol();
  // MS Edge converts symbol values to JSON as {}
  // WebKit converts symbol values to JSON as null
  // V8 throws on boxed symbols
  return _stringify([S]) != '[null]' || _stringify({ a: S }) != '{}' || _stringify(Object(S)) != '{}';
})), 'JSON', {
  stringify: function stringify(it) {
    var args = [it];
    var i = 1;
    var replacer, $replacer;
    while (arguments.length > i) args.push(arguments[i++]);
    $replacer = replacer = args[1];
    if (!isObject(replacer) && it === undefined || isSymbol(it)) return; // IE8 returns string on undefined
    if (!isArray(replacer)) replacer = function (key, value) {
      if (typeof $replacer == 'function') value = $replacer.call(this, key, value);
      if (!isSymbol(value)) return value;
    };
    args[1] = replacer;
    return _stringify.apply($JSON, args);
  }
});

// 19.4.3.4 Symbol.prototype[@@toPrimitive](hint)
$Symbol[PROTOTYPE][TO_PRIMITIVE] || __webpack_require__(14)($Symbol[PROTOTYPE], TO_PRIMITIVE, $Symbol[PROTOTYPE].valueOf);
// 19.4.3.5 Symbol.prototype[@@toStringTag]
setToStringTag($Symbol, 'Symbol');
// 20.2.1.9 Math[@@toStringTag]
setToStringTag(Math, 'Math', true);
// 24.3.3 JSON[@@toStringTag]
setToStringTag(global.JSON, 'JSON', true);


/***/ }),
/* 90 */
/***/ (function(module, exports, __webpack_require__) {

var META = __webpack_require__(26)('meta');
var isObject = __webpack_require__(19);
var has = __webpack_require__(12);
var setDesc = __webpack_require__(9).f;
var id = 0;
var isExtensible = Object.isExtensible || function () {
  return true;
};
var FREEZE = !__webpack_require__(20)(function () {
  return isExtensible(Object.preventExtensions({}));
});
var setMeta = function (it) {
  setDesc(it, META, { value: {
    i: 'O' + ++id, // object ID
    w: {}          // weak collections IDs
  } });
};
var fastKey = function (it, create) {
  // return primitive with prefix
  if (!isObject(it)) return typeof it == 'symbol' ? it : (typeof it == 'string' ? 'S' : 'P') + it;
  if (!has(it, META)) {
    // can't set metadata to uncaught frozen object
    if (!isExtensible(it)) return 'F';
    // not necessary to add metadata
    if (!create) return 'E';
    // add missing metadata
    setMeta(it);
  // return object ID
  } return it[META].i;
};
var getWeak = function (it, create) {
  if (!has(it, META)) {
    // can't set metadata to uncaught frozen object
    if (!isExtensible(it)) return true;
    // not necessary to add metadata
    if (!create) return false;
    // add missing metadata
    setMeta(it);
  // return hash weak collections IDs
  } return it[META].w;
};
// add metadata on freeze-family methods calling
var onFreeze = function (it) {
  if (FREEZE && meta.NEED && isExtensible(it) && !has(it, META)) setMeta(it);
  return it;
};
var meta = module.exports = {
  KEY: META,
  NEED: false,
  fastKey: fastKey,
  getWeak: getWeak,
  onFreeze: onFreeze
};


/***/ }),
/* 91 */
/***/ (function(module, exports, __webpack_require__) {

// all enumerable object keys, includes symbols
var getKeys = __webpack_require__(24);
var gOPS = __webpack_require__(39);
var pIE = __webpack_require__(27);
module.exports = function (it) {
  var result = getKeys(it);
  var getSymbols = gOPS.f;
  if (getSymbols) {
    var symbols = getSymbols(it);
    var isEnum = pIE.f;
    var i = 0;
    var key;
    while (symbols.length > i) if (isEnum.call(it, key = symbols[i++])) result.push(key);
  } return result;
};


/***/ }),
/* 92 */
/***/ (function(module, exports, __webpack_require__) {

// 7.2.2 IsArray(argument)
var cof = __webpack_require__(33);
module.exports = Array.isArray || function isArray(arg) {
  return cof(arg) == 'Array';
};


/***/ }),
/* 93 */
/***/ (function(module, exports, __webpack_require__) {

// fallback for IE11 buggy Object.getOwnPropertyNames with iframe and window
var toIObject = __webpack_require__(16);
var gOPN = __webpack_require__(58).f;
var toString = {}.toString;

var windowNames = typeof window == 'object' && window && Object.getOwnPropertyNames
  ? Object.getOwnPropertyNames(window) : [];

var getWindowNames = function (it) {
  try {
    return gOPN(it);
  } catch (e) {
    return windowNames.slice();
  }
};

module.exports.f = function getOwnPropertyNames(it) {
  return windowNames && toString.call(it) == '[object Window]' ? getWindowNames(it) : gOPN(toIObject(it));
};


/***/ }),
/* 94 */
/***/ (function(module, exports, __webpack_require__) {

var pIE = __webpack_require__(27);
var createDesc = __webpack_require__(21);
var toIObject = __webpack_require__(16);
var toPrimitive = __webpack_require__(32);
var has = __webpack_require__(12);
var IE8_DOM_DEFINE = __webpack_require__(47);
var gOPD = Object.getOwnPropertyDescriptor;

exports.f = __webpack_require__(11) ? gOPD : function getOwnPropertyDescriptor(O, P) {
  O = toIObject(O);
  P = toPrimitive(P, true);
  if (IE8_DOM_DEFINE) try {
    return gOPD(O, P);
  } catch (e) { /* empty */ }
  if (has(O, P)) return createDesc(!pIE.f.call(O, P), O[P]);
};


/***/ }),
/* 95 */
/***/ (function(module, exports) {



/***/ }),
/* 96 */
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__(44)('asyncIterator');


/***/ }),
/* 97 */
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__(44)('observable');


/***/ }),
/* 98 */
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__(57);
__webpack_require__(41);
module.exports = __webpack_require__(99);


/***/ }),
/* 99 */
/***/ (function(module, exports, __webpack_require__) {

var anObject = __webpack_require__(15);
var get = __webpack_require__(60);
module.exports = __webpack_require__(6).getIterator = function (it) {
  var iterFn = get(it);
  if (typeof iterFn != 'function') throw TypeError(it + ' is not iterable!');
  return anObject(iterFn.call(it));
};


/***/ }),
/* 100 */
/***/ (function(module, exports, __webpack_require__) {

// getting tag from 19.1.3.6 Object.prototype.toString()
var cof = __webpack_require__(33);
var TAG = __webpack_require__(4)('toStringTag');
// ES3 wrong here
var ARG = cof(function () { return arguments; }()) == 'Arguments';

// fallback for IE11 Script Access Denied error
var tryGet = function (it, key) {
  try {
    return it[key];
  } catch (e) { /* empty */ }
};

module.exports = function (it) {
  var O, T, B;
  return it === undefined ? 'Undefined' : it === null ? 'Null'
    // @@toStringTag case
    : typeof (T = tryGet(O = Object(it), TAG)) == 'string' ? T
    // builtinTag case
    : ARG ? cof(O)
    // ES3 arguments fallback
    : (B = cof(O)) == 'Object' && typeof O.callee == 'function' ? 'Arguments' : B;
};


/***/ }),
/* 101 */
/***/ (function(module, exports, __webpack_require__) {

module.exports = { "default": __webpack_require__(102), __esModule: true };

/***/ }),
/* 102 */
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__(103);
var $Object = __webpack_require__(6).Object;
module.exports = function defineProperty(it, key, desc) {
  return $Object.defineProperty(it, key, desc);
};


/***/ }),
/* 103 */
/***/ (function(module, exports, __webpack_require__) {

var $export = __webpack_require__(18);
// 19.1.2.4 / 15.2.3.6 Object.defineProperty(O, P, Attributes)
$export($export.S + $export.F * !__webpack_require__(11), 'Object', { defineProperty: __webpack_require__(9).f });


/***/ }),
/* 104 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_defineProperty__ = __webpack_require__(1);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_defineProperty___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_defineProperty__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__icon_Icon__ = __webpack_require__(3);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__icon_Icon___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_1__icon_Icon__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__utils_config__ = __webpack_require__(2);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_3__utils_FormElementMixin__ = __webpack_require__(13);

//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//





/* harmony default export */ __webpack_exports__["default"] = ({
    name: 'BInput',
    components: __WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_defineProperty___default()({}, __WEBPACK_IMPORTED_MODULE_1__icon_Icon___default.a.name, __WEBPACK_IMPORTED_MODULE_1__icon_Icon___default.a),
    mixins: [__WEBPACK_IMPORTED_MODULE_3__utils_FormElementMixin__["a" /* default */]],
    inheritAttrs: false,
    props: {
        value: [Number, String],
        type: {
            type: String,
            default: 'text'
        },
        passwordReveal: Boolean,
        hasCounter: {
            type: Boolean,
            default: function _default() {
                return __WEBPACK_IMPORTED_MODULE_2__utils_config__["a" /* default */].defaultInputHasCounter;
            }
        },
        customClass: {
            type: String,
            default: ''
        }
    },
    data: function data() {
        return {
            newValue: this.value,
            newType: this.type,
            newAutocomplete: this.autocomplete || __WEBPACK_IMPORTED_MODULE_2__utils_config__["a" /* default */].defaultInputAutocomplete,
            isPasswordVisible: false,
            _elementRef: this.type === 'textarea' ? 'textarea' : 'input'
        };
    },

    computed: {
        computedValue: {
            get: function get() {
                return this.newValue;
            },
            set: function set(value) {
                this.newValue = value;
                this.$emit('input', value);
                !this.isValid && this.checkHtml5Validity();
            }
        },
        rootClasses: function rootClasses() {
            return [this.iconPosition, this.size, {
                'is-expanded': this.expanded,
                'is-loading': this.loading,
                'is-clearfix': !this.hasMessage
            }];
        },
        inputClasses: function inputClasses() {
            return [this.statusType, this.size, { 'is-rounded': this.rounded }];
        },
        hasIconRight: function hasIconRight() {
            return this.passwordReveal || this.loading || this.statusType;
        },


        /**
         * Position of the icon or if it's both sides.
         */
        iconPosition: function iconPosition() {
            if (this.icon && this.hasIconRight) {
                return 'has-icons-left has-icons-right';
            } else if (!this.icon && this.hasIconRight) {
                return 'has-icons-right';
            } else if (this.icon) {
                return 'has-icons-left';
            }
        },


        /**
         * Icon name (MDI) based on the type.
         */
        statusTypeIcon: function statusTypeIcon() {
            switch (this.statusType) {
                case 'is-success':
                    return 'check';
                case 'is-danger':
                    return 'alert-circle';
                case 'is-info':
                    return 'information';
                case 'is-warning':
                    return 'alert';
            }
        },


        /**
         * Check if have any message prop from parent if it's a Field.
         */
        hasMessage: function hasMessage() {
            return !!this.statusMessage;
        },


        /**
         * Current password-reveal icon name.
         */
        passwordVisibleIcon: function passwordVisibleIcon() {
            return !this.isPasswordVisible ? 'eye' : 'eye-off';
        },

        /**
         * Get value length
         */
        valueLength: function valueLength() {
            if (typeof this.computedValue === 'string') {
                return this.computedValue.length;
            } else if (typeof this.computedValue === 'number') {
                return this.computedValue.toString().length;
            }
            return 0;
        }
    },
    watch: {
        /**
         * When v-model is changed:
         *   1. Set internal value.
         */
        value: function value(_value) {
            this.newValue = _value;
        }
    },
    methods: {
        /**
         * Toggle the visibility of a password-reveal input
         * by changing the type and focus the input right away.
         */
        togglePasswordVisibility: function togglePasswordVisibility() {
            var _this = this;

            this.isPasswordVisible = !this.isPasswordVisible;
            this.newType = this.isPasswordVisible ? 'text' : 'password';

            this.$nextTick(function () {
                _this.$refs.input.focus();
            });
        },


        /**
         * Input's 'input' event listener, 'nextTick' is used to prevent event firing
         * before ui update, helps when using masks (Cleavejs and potentially others).
         */
        onInput: function onInput(event) {
            var _this2 = this;

            this.$nextTick(function () {
                if (event.target) {
                    _this2.computedValue = event.target.value;
                }
            });
        }
    }
});

/***/ }),
/* 105 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__utils_config__ = __webpack_require__(2);
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//



/* harmony default export */ __webpack_exports__["default"] = ({
    name: 'BIcon',
    props: {
        type: [String, Object],
        pack: String,
        icon: String,
        size: String,
        customSize: String,
        customClass: String,
        both: Boolean // This is used internally to show both MDI and FA icon
    },
    computed: {
        /**
         * Internal icon name based on the pack.
         * If pack is 'fa', gets the equivalent FA icon name of the MDI,
         * internal icons are always MDI.
         */
        newIcon: function newIcon() {
            return this.newPack === 'mdi' ? this.newPack + '-' + this.icon : this.addFAPrefix(this.getEquivalentIconOf(this.icon));
        },
        newPack: function newPack() {
            return this.pack || __WEBPACK_IMPORTED_MODULE_0__utils_config__["a" /* default */].defaultIconPack;
        },
        newType: function newType() {
            if (!this.type) return;

            var splitType = [];
            if (typeof this.type === 'string') {
                splitType = this.type.split('-');
            } else {
                for (var key in this.type) {
                    if (this.type[key]) {
                        splitType = key.split('-');
                        break;
                    }
                }
            }
            if (splitType.length <= 1) return;

            return 'has-text-' + splitType[1];
        },
        newCustomSize: function newCustomSize() {
            return this.customSize || this.customSizeByPack;
        },
        customSizeByPack: function customSizeByPack() {
            var defaultSize = this.newPack === 'mdi' ? 'mdi-24px' : this.addFAPrefix('lg');
            var mediumSize = this.newPack === 'mdi' ? 'mdi-36px' : this.addFAPrefix('2x');
            var largeSize = this.newPack === 'mdi' ? 'mdi-48px' : this.addFAPrefix('3x');
            switch (this.size) {
                case 'is-small':
                    return;
                case 'is-medium':
                    return mediumSize;
                case 'is-large':
                    return largeSize;
                default:
                    return defaultSize;
            }
        },
        useIconComponent: function useIconComponent() {
            return __WEBPACK_IMPORTED_MODULE_0__utils_config__["a" /* default */].defaultIconComponent;
        }
    },
    methods: {
        addFAPrefix: function addFAPrefix(value) {
            if (this.useIconComponent) {
                return value;
            }
            return 'fa-' + value;
        },


        /**
         * Equivalent FA icon name of the MDI.
         */
        getEquivalentIconOf: function getEquivalentIconOf(value) {
            // Only transform the class if the both prop is set to true
            if (!this.both) {
                return value;
            }

            switch (value) {
                case 'check':
                    return 'check';
                case 'information':
                    return 'info-circle';
                case 'check-circle':
                    return 'check-circle';
                case 'alert':
                    return 'exclamation-triangle';
                case 'alert-circle':
                    return 'exclamation-circle';
                case 'arrow-up':
                    return 'arrow-up';
                case 'chevron-right':
                    return 'angle-right';
                case 'chevron-left':
                    return 'angle-left';
                case 'chevron-down':
                    return 'angle-down';
                case 'eye':
                    return 'eye';
                case 'eye-off':
                    return 'eye-slash';
                case 'menu-down':
                    return 'caret-down';
                case 'menu-up':
                    return 'caret-up';
                default:
                    return value;
            }
        }
    }
});

/***/ }),
/* 106 */
/***/ (function(module, exports) {

module.exports={render:function (){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;
  return _c('span', {
    staticClass: "icon",
    class: [_vm.newType, _vm.size]
  }, [(!_vm.useIconComponent) ? _c('i', {
    class: [_vm.newPack, _vm.newIcon, _vm.newCustomSize, _vm.customClass]
  }) : _c(_vm.useIconComponent, {
    tag: "component",
    class: [_vm.customClass],
    attrs: {
      "icon": [_vm.newPack, _vm.newIcon],
      "size": _vm.newCustomSize
    }
  })], 1)
},staticRenderFns: []}

/***/ }),
/* 107 */
/***/ (function(module, exports) {

module.exports={render:function (){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;
  return _c('div', {
    staticClass: "control",
    class: _vm.rootClasses
  }, [(_vm.type !== 'textarea') ? _c('input', _vm._b({
    ref: "input",
    staticClass: "input",
    class: [_vm.inputClasses, _vm.customClass],
    attrs: {
      "type": _vm.newType,
      "autocomplete": _vm.newAutocomplete,
      "maxlength": _vm.maxlength
    },
    domProps: {
      "value": _vm.computedValue
    },
    on: {
      "input": _vm.onInput,
      "blur": _vm.onBlur,
      "focus": _vm.onFocus
    }
  }, 'input', _vm.$attrs, false)) : _c('textarea', _vm._b({
    ref: "textarea",
    staticClass: "textarea",
    class: [_vm.inputClasses, _vm.customClass],
    attrs: {
      "maxlength": _vm.maxlength
    },
    domProps: {
      "value": _vm.computedValue
    },
    on: {
      "input": _vm.onInput,
      "blur": _vm.onBlur,
      "focus": _vm.onFocus
    }
  }, 'textarea', _vm.$attrs, false)), _vm._v(" "), (_vm.icon) ? _c('b-icon', {
    staticClass: "is-left",
    attrs: {
      "icon": _vm.icon,
      "pack": _vm.iconPack,
      "size": _vm.iconSize
    }
  }) : _vm._e(), _vm._v(" "), (!_vm.loading && (_vm.passwordReveal || _vm.statusType)) ? _c('b-icon', {
    staticClass: "is-right",
    class: {
      'is-clickable': _vm.passwordReveal
    },
    attrs: {
      "icon": _vm.passwordReveal ? _vm.passwordVisibleIcon : _vm.statusTypeIcon,
      "pack": _vm.iconPack,
      "size": _vm.iconSize,
      "type": !_vm.passwordReveal ? _vm.statusType : 'is-primary',
      "both": ""
    },
    nativeOn: {
      "click": function($event) {
        _vm.togglePasswordVisibility($event)
      }
    }
  }) : _vm._e(), _vm._v(" "), (_vm.maxlength && _vm.hasCounter && _vm.type !== 'number') ? _c('small', {
    staticClass: "help counter",
    class: {
      'is-invisible': !_vm.isFocused
    }
  }, [_vm._v("\n        " + _vm._s(_vm.valueLength) + " / " + _vm._s(_vm.maxlength) + "\n    ")]) : _vm._e()], 1)
},staticRenderFns: []}

/***/ }),
/* 108 */
/***/ (function(module, exports) {

module.exports={render:function (){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;
  return _c('div', {
    staticClass: "autocomplete control",
    class: {
      'is-expanded': _vm.expanded
    }
  }, [_c('b-input', _vm._b({
    ref: "input",
    attrs: {
      "type": "text",
      "size": _vm.size,
      "loading": _vm.loading,
      "rounded": _vm.rounded,
      "icon": _vm.icon,
      "icon-pack": _vm.iconPack,
      "maxlength": _vm.maxlength,
      "autocomplete": _vm.newAutocomplete
    },
    on: {
      "input": _vm.onInput,
      "focus": _vm.focused,
      "blur": _vm.onBlur
    },
    nativeOn: {
      "keyup": function($event) {
        if (!('button' in $event) && _vm._k($event.keyCode, "esc", 27, $event.key)) { return null; }
        $event.preventDefault();
        _vm.isActive = false
      },
      "keydown": [function($event) {
        if (!('button' in $event) && _vm._k($event.keyCode, "tab", 9, $event.key)) { return null; }
        _vm.tabPressed($event)
      }, function($event) {
        if (!('button' in $event) && _vm._k($event.keyCode, "enter", 13, $event.key)) { return null; }
        $event.preventDefault();
        _vm.enterPressed($event)
      }, function($event) {
        if (!('button' in $event) && _vm._k($event.keyCode, "up", 38, $event.key)) { return null; }
        $event.preventDefault();
        _vm.keyArrows('up')
      }, function($event) {
        if (!('button' in $event) && _vm._k($event.keyCode, "down", 40, $event.key)) { return null; }
        $event.preventDefault();
        _vm.keyArrows('down')
      }]
    },
    model: {
      value: (_vm.newValue),
      callback: function($$v) {
        _vm.newValue = $$v
      },
      expression: "newValue"
    }
  }, 'b-input', _vm.$attrs, false)), _vm._v(" "), _c('transition', {
    attrs: {
      "name": "fade"
    }
  }, [_c('div', {
    directives: [{
      name: "show",
      rawName: "v-show",
      value: (_vm.isActive && (_vm.data.length > 0 || _vm.hasEmptySlot || _vm.hasHeaderSlot)),
      expression: "isActive && (data.length > 0 || hasEmptySlot || hasHeaderSlot)"
    }],
    ref: "dropdown",
    staticClass: "dropdown-menu",
    class: {
      'is-opened-top': !_vm.isListInViewportVertically
    }
  }, [_c('div', {
    directives: [{
      name: "show",
      rawName: "v-show",
      value: (_vm.isActive),
      expression: "isActive"
    }],
    staticClass: "dropdown-content"
  }, [(_vm.hasHeaderSlot) ? _c('div', {
    staticClass: "dropdown-item"
  }, [_vm._t("header")], 2) : _vm._e(), _vm._v(" "), _vm._l((_vm.data), function(option, index) {
    return _c('a', {
      key: index,
      staticClass: "dropdown-item",
      class: {
        'is-hovered': option === _vm.hovered
      },
      on: {
        "click": function($event) {
          _vm.setSelected(option)
        }
      }
    }, [(_vm.hasDefaultSlot) ? _vm._t("default", null, {
      option: option,
      index: index
    }) : _c('span', [_vm._v("\n                        " + _vm._s(_vm.getValue(option, true)) + "\n                    ")])], 2)
  }), _vm._v(" "), (_vm.data.length === 0 && _vm.hasEmptySlot) ? _c('div', {
    staticClass: "dropdown-item is-disabled"
  }, [_vm._t("empty")], 2) : _vm._e()], 2)])])], 1)
},staticRenderFns: []}

/***/ }),
/* 109 */
/***/ (function(module, exports, __webpack_require__) {

var Component = __webpack_require__(0)(
  /* script */
  __webpack_require__(110),
  /* template */
  __webpack_require__(111),
  /* styles */
  null,
  /* scopeId */
  null,
  /* moduleIdentifier (server only) */
  null
)

module.exports = Component.exports


/***/ }),
/* 110 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_defineProperty__ = __webpack_require__(1);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_defineProperty___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_defineProperty__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__icon_Icon__ = __webpack_require__(3);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__icon_Icon___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_1__icon_Icon__);

//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//



/* harmony default export */ __webpack_exports__["default"] = ({
    name: 'BButton',
    components: __WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_defineProperty___default()({}, __WEBPACK_IMPORTED_MODULE_1__icon_Icon___default.a.name, __WEBPACK_IMPORTED_MODULE_1__icon_Icon___default.a),
    props: {
        type: [String, Object],
        size: String,
        label: String,
        iconPack: String,
        iconLeft: String,
        iconRight: String,
        rounded: Boolean,
        loading: Boolean,
        outlined: Boolean,
        inverted: Boolean,
        focused: Boolean,
        active: Boolean,
        hovered: Boolean,
        selected: Boolean,
        nativeType: {
            type: String,
            default: 'button',
            validator: function validator(value) {
                return ['button', 'submit', 'reset'].indexOf(value) >= 0;
            }
        },
        tag: {
            type: String,
            default: 'button',
            validator: function validator(value) {
                return ['button', 'a', 'input'].indexOf(value) >= 0;
            }
        }
    },
    computed: {
        iconSize: function iconSize() {
            if (!this.size || this.size === 'is-medium') {
                return 'is-small';
            } else if (this.size === 'is-large') {
                return 'is-medium';
            }
            return this.size;
        }
    }
});

/***/ }),
/* 111 */
/***/ (function(module, exports) {

module.exports={render:function (){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;
  return _c(_vm.tag, {
    tag: "component",
    staticClass: "button",
    class: [_vm.size, _vm.type, {
      'is-rounded': _vm.rounded,
      'is-loading': _vm.loading,
      'is-outlined': _vm.outlined,
      'is-inverted': _vm.inverted,
      'is-focused': _vm.focused,
      'is-active': _vm.active,
      'is-hovered': _vm.hovered,
      'is-selected': _vm.selected
    }],
    attrs: {
      "type": _vm.nativeType
    },
    on: {
      "click": function($event) {
        _vm.$emit('click', $event)
      }
    }
  }, [(_vm.iconLeft) ? _c('b-icon', {
    attrs: {
      "pack": _vm.iconPack,
      "icon": _vm.iconLeft,
      "size": _vm.iconSize
    }
  }) : _vm._e(), _vm._v(" "), (_vm.label) ? _c('span', [_vm._v(_vm._s(_vm.label))]) : (_vm.$slots.default) ? _c('span', [_vm._t("default")], 2) : _vm._e(), _vm._v(" "), (_vm.iconRight) ? _c('b-icon', {
    attrs: {
      "pack": _vm.iconPack,
      "icon": _vm.iconRight,
      "size": _vm.iconSize
    }
  }) : _vm._e()], 1)
},staticRenderFns: []}

/***/ }),
/* 112 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_symbol__ = __webpack_require__(5);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_symbol___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_symbol__);

//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//

/* harmony default export */ __webpack_exports__["default"] = ({
    name: 'BCheckbox',
    props: {
        value: [String, Number, Boolean, Function, Object, Array, __WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_symbol___default.a],
        nativeValue: [String, Number, Boolean, Function, Object, Array, __WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_symbol___default.a],
        indeterminate: Boolean,
        type: String,
        disabled: Boolean,
        required: Boolean,
        name: String,
        size: String,
        trueValue: {
            type: [String, Number, Boolean, Function, Object, Array, __WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_symbol___default.a],
            default: true
        },
        falseValue: {
            type: [String, Number, Boolean, Function, Object, Array, __WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_symbol___default.a],
            default: false
        }
    },
    data: function data() {
        return {
            newValue: this.value
        };
    },

    computed: {
        computedValue: {
            get: function get() {
                return this.newValue;
            },
            set: function set(value) {
                this.newValue = value;
                this.$emit('input', value);
            }
        }
    },
    watch: {
        /**
         * When v-model change, set internal value.
         */
        value: function value(_value) {
            this.newValue = _value;
        }
    }
});

/***/ }),
/* 113 */
/***/ (function(module, exports) {

module.exports={render:function (){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;
  return _c('label', {
    ref: "label",
    staticClass: "b-checkbox checkbox",
    class: [_vm.size, {
      'is-disabled': _vm.disabled
    }],
    attrs: {
      "disabled": _vm.disabled,
      "tabindex": _vm.disabled ? false : 0
    },
    on: {
      "keydown": function($event) {
        if (!('button' in $event) && _vm._k($event.keyCode, "enter", 13, $event.key) && _vm._k($event.keyCode, "space", 32, $event.key)) { return null; }
        $event.preventDefault();
        _vm.$refs.label.click()
      }
    }
  }, [_c('input', {
    directives: [{
      name: "model",
      rawName: "v-model",
      value: (_vm.computedValue),
      expression: "computedValue"
    }],
    attrs: {
      "tabindex": "-1",
      "type": "checkbox",
      "disabled": _vm.disabled,
      "required": _vm.required,
      "name": _vm.name,
      "true-value": _vm.trueValue,
      "false-value": _vm.falseValue
    },
    domProps: {
      "indeterminate": _vm.indeterminate,
      "value": _vm.nativeValue,
      "checked": Array.isArray(_vm.computedValue) ? _vm._i(_vm.computedValue, _vm.nativeValue) > -1 : _vm._q(_vm.computedValue, _vm.trueValue)
    },
    on: {
      "click": function($event) {
        $event.stopPropagation();
      },
      "change": function($event) {
        var $$a = _vm.computedValue,
          $$el = $event.target,
          $$c = $$el.checked ? (_vm.trueValue) : (_vm.falseValue);
        if (Array.isArray($$a)) {
          var $$v = _vm.nativeValue,
            $$i = _vm._i($$a, $$v);
          if ($$el.checked) {
            $$i < 0 && (_vm.computedValue = $$a.concat([$$v]))
          } else {
            $$i > -1 && (_vm.computedValue = $$a.slice(0, $$i).concat($$a.slice($$i + 1)))
          }
        } else {
          _vm.computedValue = $$c
        }
      }
    }
  }), _vm._v(" "), _c('span', {
    staticClass: "check",
    class: _vm.type
  }), _vm._v(" "), _c('span', {
    staticClass: "control-label"
  }, [_vm._t("default")], 2)])
},staticRenderFns: []}

/***/ }),
/* 114 */
/***/ (function(module, exports, __webpack_require__) {

var Component = __webpack_require__(0)(
  /* script */
  __webpack_require__(115),
  /* template */
  __webpack_require__(116),
  /* styles */
  null,
  /* scopeId */
  null,
  /* moduleIdentifier (server only) */
  null
)

module.exports = Component.exports


/***/ }),
/* 115 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_symbol__ = __webpack_require__(5);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_symbol___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_symbol__);

//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//

/* harmony default export */ __webpack_exports__["default"] = ({
    name: 'BCheckboxButton',
    props: {
        value: [String, Number, Boolean, Function, Object, Array, __WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_symbol___default.a],
        nativeValue: [String, Number, Boolean, Function, Object, Array, __WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_symbol___default.a],
        disabled: Boolean,
        required: Boolean,
        name: String,
        size: String,
        type: {
            type: String,
            default: 'is-primary'
        }
    },
    data: function data() {
        return {
            newValue: this.value
        };
    },

    computed: {
        computedValue: {
            get: function get() {
                return this.newValue;
            },
            set: function set(value) {
                this.newValue = value;
                this.$emit('input', value);
            }
        },
        checked: function checked() {
            if (Array.isArray(this.newValue)) {
                return this.newValue.indexOf(this.nativeValue) >= 0;
            }
            return this.newValue === this.nativeValue;
        }
    },
    watch: {
        /**
         * When v-model change, set internal value.
         */
        value: function value(_value) {
            this.newValue = _value;
        }
    }
});

/***/ }),
/* 116 */
/***/ (function(module, exports) {

module.exports={render:function (){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;
  return _c('div', {
    staticClass: "control"
  }, [_c('label', {
    ref: "label",
    staticClass: "b-checkbox checkbox button",
    class: [_vm.checked ? _vm.type : null, _vm.size, {
      'is-disabled': _vm.disabled
    }],
    attrs: {
      "disabled": _vm.disabled,
      "tabindex": _vm.disabled ? false : 0
    },
    on: {
      "keydown": function($event) {
        if (!('button' in $event) && _vm._k($event.keyCode, "enter", 13, $event.key) && _vm._k($event.keyCode, "space", 32, $event.key)) { return null; }
        $event.preventDefault();
        _vm.$refs.label.click()
      }
    }
  }, [_vm._t("default"), _vm._v(" "), _c('input', {
    directives: [{
      name: "model",
      rawName: "v-model",
      value: (_vm.computedValue),
      expression: "computedValue"
    }],
    attrs: {
      "tabindex": "-1",
      "type": "checkbox",
      "disabled": _vm.disabled,
      "required": _vm.required,
      "name": _vm.name
    },
    domProps: {
      "value": _vm.nativeValue,
      "checked": Array.isArray(_vm.computedValue) ? _vm._i(_vm.computedValue, _vm.nativeValue) > -1 : (_vm.computedValue)
    },
    on: {
      "click": function($event) {
        $event.stopPropagation();
      },
      "change": function($event) {
        var $$a = _vm.computedValue,
          $$el = $event.target,
          $$c = $$el.checked ? (true) : (false);
        if (Array.isArray($$a)) {
          var $$v = _vm.nativeValue,
            $$i = _vm._i($$a, $$v);
          if ($$el.checked) {
            $$i < 0 && (_vm.computedValue = $$a.concat([$$v]))
          } else {
            $$i > -1 && (_vm.computedValue = $$a.slice(0, $$i).concat($$a.slice($$i + 1)))
          }
        } else {
          _vm.computedValue = $$c
        }
      }
    }
  })], 2)])
},staticRenderFns: []}

/***/ }),
/* 117 */
/***/ (function(module, exports, __webpack_require__) {

var Component = __webpack_require__(0)(
  /* script */
  __webpack_require__(118),
  /* template */
  __webpack_require__(119),
  /* styles */
  null,
  /* scopeId */
  null,
  /* moduleIdentifier (server only) */
  null
)

module.exports = Component.exports


/***/ }),
/* 118 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//

/* harmony default export */ __webpack_exports__["default"] = ({
    name: 'BCollapse',
    props: {
        open: {
            type: Boolean,
            default: true
        },
        animation: {
            type: String,
            default: 'fade'
        },
        ariaId: {
            type: String,
            default: ''
        }
    },
    data: function data() {
        return {
            isOpen: this.open
        };
    },

    watch: {
        open: function open(value) {
            this.isOpen = value;
        }
    },
    methods: {
        /**
         * Toggle and emit events
         */
        toggle: function toggle() {
            this.isOpen = !this.isOpen;
            this.$emit('update:open', this.isOpen);
            this.$emit(this.isOpen ? 'open' : 'close');
        }
    }
});

/***/ }),
/* 119 */
/***/ (function(module, exports) {

module.exports={render:function (){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;
  return _c('div', {
    staticClass: "collapse"
  }, [_c('div', {
    staticClass: "collapse-trigger",
    on: {
      "click": _vm.toggle
    }
  }, [_vm._t("trigger", null, {
    open: _vm.isOpen
  })], 2), _vm._v(" "), _c('transition', {
    attrs: {
      "name": _vm.animation
    }
  }, [_c('div', {
    directives: [{
      name: "show",
      rawName: "v-show",
      value: (_vm.isOpen),
      expression: "isOpen"
    }],
    staticClass: "collapse-content",
    attrs: {
      "id": _vm.ariaId,
      "aria-expanded": _vm.isOpen
    }
  }, [_vm._t("default")], 2)])], 1)
},staticRenderFns: []}

/***/ }),
/* 120 */
/***/ (function(module, exports, __webpack_require__) {

var Component = __webpack_require__(0)(
  /* script */
  __webpack_require__(121),
  /* template */
  __webpack_require__(133),
  /* styles */
  null,
  /* scopeId */
  null,
  /* moduleIdentifier (server only) */
  null
)

module.exports = Component.exports


/***/ }),
/* 121 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_defineProperty__ = __webpack_require__(1);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_defineProperty___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_defineProperty__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__utils_TimepickerMixin__ = __webpack_require__(62);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__dropdown_Dropdown__ = __webpack_require__(28);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__dropdown_Dropdown___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_2__dropdown_Dropdown__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_3__dropdown_DropdownItem__ = __webpack_require__(29);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_3__dropdown_DropdownItem___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_3__dropdown_DropdownItem__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_4__input_Input__ = __webpack_require__(23);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_4__input_Input___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_4__input_Input__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_5__field_Field__ = __webpack_require__(30);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_5__field_Field___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_5__field_Field__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_6__icon_Icon__ = __webpack_require__(3);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_6__icon_Icon___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_6__icon_Icon__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_7__ClockpickerFace__ = __webpack_require__(130);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_7__ClockpickerFace___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_7__ClockpickerFace__);


var _components;

//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//










var outerPadding = 12;

/* harmony default export */ __webpack_exports__["default"] = ({
    name: 'BClockpicker',
    components: (_components = {}, __WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_defineProperty___default()(_components, __WEBPACK_IMPORTED_MODULE_7__ClockpickerFace___default.a.name, __WEBPACK_IMPORTED_MODULE_7__ClockpickerFace___default.a), __WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_defineProperty___default()(_components, __WEBPACK_IMPORTED_MODULE_4__input_Input___default.a.name, __WEBPACK_IMPORTED_MODULE_4__input_Input___default.a), __WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_defineProperty___default()(_components, __WEBPACK_IMPORTED_MODULE_5__field_Field___default.a.name, __WEBPACK_IMPORTED_MODULE_5__field_Field___default.a), __WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_defineProperty___default()(_components, __WEBPACK_IMPORTED_MODULE_6__icon_Icon___default.a.name, __WEBPACK_IMPORTED_MODULE_6__icon_Icon___default.a), __WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_defineProperty___default()(_components, __WEBPACK_IMPORTED_MODULE_2__dropdown_Dropdown___default.a.name, __WEBPACK_IMPORTED_MODULE_2__dropdown_Dropdown___default.a), __WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_defineProperty___default()(_components, __WEBPACK_IMPORTED_MODULE_3__dropdown_DropdownItem___default.a.name, __WEBPACK_IMPORTED_MODULE_3__dropdown_DropdownItem___default.a), _components),
    mixins: [__WEBPACK_IMPORTED_MODULE_1__utils_TimepickerMixin__["a" /* default */]],
    props: {
        pickerSize: {
            type: Number,
            default: 290
        },
        hourFormat: {
            type: String,
            default: '12',
            validator: function validator(value) {
                return value === '24' || value === '12';
            }
        },
        incrementMinutes: {
            type: Number,
            default: 5
        },
        autoSwitch: {
            type: Boolean,
            default: true
        },
        type: {
            type: String,
            default: 'is-primary'
        }
    },
    data: function data() {
        return {
            isSelectingHour: true,
            isDragging: false,
            _isClockpicker: true
        };
    },

    computed: {
        hoursDisplay: function hoursDisplay() {
            if (this.hoursSelected == null) return '--';
            if (this.isHourFormat24) return this.pad(this.hoursSelected);

            var display = this.hoursSelected;
            if (this.meridienSelected === this.PM) display -= 12;
            if (display === 0) display = 12;
            return display;
        },
        minutesDisplay: function minutesDisplay() {
            return this.minutesSelected == null ? '--' : this.pad(this.minutesSelected);
        },
        minFaceValue: function minFaceValue() {
            return this.isSelectingHour && !this.isHourFormat24 && this.meridienSelected === this.PM ? 12 : 0;
        },
        maxFaceValue: function maxFaceValue() {
            return this.isSelectingHour ? !this.isHourFormat24 && this.meridienSelected === this.AM ? 11 : 23 : 59;
        },
        faceFormatter: function faceFormatter() {
            return this.isSelectingHour && !this.isHourFormat24 ? function (val) {
                return val;
            } : this.formatNumber;
        },
        faceSize: function faceSize() {
            return this.pickerSize - outerPadding * 2;
        }
    },
    methods: {
        onClockInput: function onClockInput(value) {
            if (this.isSelectingHour) {
                this.hoursSelected = value;
                this.onHoursChange(value);
            } else {
                this.minutesSelected = value;
                this.onMinutesChange(value);
            }
        },
        onClockChange: function onClockChange(value) {
            if (this.autoSwitch && this.isSelectingHour) {
                this.isSelectingHour = !this.isSelectingHour;
            }
        },
        onMeridienClick: function onMeridienClick(value) {
            if (this.meridienSelected !== value) {
                this.meridienSelected = value;
                this.onMeridienChange(value);
            }
        },
        faceDisabledValues: function faceDisabledValues() {
            return this.isSelectingHour ? this.isHourDisabled : this.isMinuteDisabled;
        }
    },
    created: function created() {
        this.incrementMinutes = 5;
    }
});

/***/ }),
/* 122 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_get_iterator__ = __webpack_require__(59);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_get_iterator___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_get_iterator__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_babel_runtime_core_js_symbol__ = __webpack_require__(5);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_babel_runtime_core_js_symbol___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_1_babel_runtime_core_js_symbol__);


//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//

/* harmony default export */ __webpack_exports__["default"] = ({
    name: 'BDropdown',
    props: {
        value: {
            type: [String, Number, Boolean, Object, Array, __WEBPACK_IMPORTED_MODULE_1_babel_runtime_core_js_symbol___default.a, Function],
            default: null
        },
        disabled: Boolean,
        hoverable: Boolean,
        inline: Boolean,
        position: {
            type: String,
            validator: function validator(value) {
                return ['is-top-right', 'is-top-left', 'is-bottom-left'].indexOf(value) > -1;
            }
        },
        mobileModal: {
            type: Boolean,
            default: true
        },
        ariaRole: {
            type: String,
            default: ''
        },
        animation: {
            type: String,
            default: 'fade'
        }
    },
    data: function data() {
        return {
            selected: this.value,
            isActive: false,
            _isDropdown: true // Used internally by DropdownItem
        };
    },

    computed: {
        rootClasses: function rootClasses() {
            return [this.position, {
                'is-disabled': this.disabled,
                'is-hoverable': this.hoverable,
                'is-inline': this.inline,
                'is-active': this.isActive || this.inline,
                'is-mobile-modal': this.isMobileModal
            }];
        },
        isMobileModal: function isMobileModal() {
            return this.mobileModal && !this.inline && !this.hoverable;
        },
        ariaRoleMenu: function ariaRoleMenu() {
            return this.ariaRole === 'menu' || this.ariaRole === 'list' ? this.ariaRole : null;
        }
    },
    watch: {
        /**
         * When v-model is changed set the new selected item.
         */
        value: function value(_value) {
            this.selected = _value;
        },


        /**
         * Emit event when isActive value is changed.
         */
        isActive: function isActive(value) {
            this.$emit('active-change', value);
        }
    },
    methods: {
        /**
         * Click listener from DropdownItem.
         *   1. Set new selected item.
         *   2. Emit input event to update the user v-model.
         *   3. Close the dropdown.
         */
        selectItem: function selectItem(value) {
            if (this.selected !== value) {
                this.$emit('change', value);
                this.selected = value;
            }
            this.$emit('input', value);
            this.isActive = false;
        },


        /**
         * White-listed items to not close when clicked.
         */
        isInWhiteList: function isInWhiteList(el) {
            if (el === this.$refs.dropdownMenu) return true;
            if (el === this.$refs.trigger) return true;
            // All chidren from dropdown
            if (this.$refs.dropdownMenu !== undefined) {
                var children = this.$refs.dropdownMenu.querySelectorAll('*');
                var _iteratorNormalCompletion = true;
                var _didIteratorError = false;
                var _iteratorError = undefined;

                try {
                    for (var _iterator = __WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_get_iterator___default()(children), _step; !(_iteratorNormalCompletion = (_step = _iterator.next()).done); _iteratorNormalCompletion = true) {
                        var child = _step.value;

                        if (el === child) {
                            return true;
                        }
                    }
                } catch (err) {
                    _didIteratorError = true;
                    _iteratorError = err;
                } finally {
                    try {
                        if (!_iteratorNormalCompletion && _iterator.return) {
                            _iterator.return();
                        }
                    } finally {
                        if (_didIteratorError) {
                            throw _iteratorError;
                        }
                    }
                }
            }
            // All children from trigger
            if (this.$refs.trigger !== undefined) {
                var _children = this.$refs.trigger.querySelectorAll('*');
                var _iteratorNormalCompletion2 = true;
                var _didIteratorError2 = false;
                var _iteratorError2 = undefined;

                try {
                    for (var _iterator2 = __WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_get_iterator___default()(_children), _step2; !(_iteratorNormalCompletion2 = (_step2 = _iterator2.next()).done); _iteratorNormalCompletion2 = true) {
                        var _child = _step2.value;

                        if (el === _child) {
                            return true;
                        }
                    }
                } catch (err) {
                    _didIteratorError2 = true;
                    _iteratorError2 = err;
                } finally {
                    try {
                        if (!_iteratorNormalCompletion2 && _iterator2.return) {
                            _iterator2.return();
                        }
                    } finally {
                        if (_didIteratorError2) {
                            throw _iteratorError2;
                        }
                    }
                }
            }

            return false;
        },


        /**
         * Close dropdown if clicked outside.
         */
        clickedOutside: function clickedOutside(event) {
            if (this.inline) return;

            if (!this.isInWhiteList(event.target)) this.isActive = false;
        },


        /**
         * Toggle dropdown if it's not disabled.
         */
        toggle: function toggle() {
            var _this = this;

            if (this.disabled || this.hoverable) return;

            if (!this.isActive) {
                // if not active, toggle after clickOutside event
                // this fixes toggling programmatic
                this.$nextTick(function () {
                    _this.isActive = !_this.isActive;
                });
            } else {
                this.isActive = !this.isActive;
            }
        }
    },
    created: function created() {
        if (typeof window !== 'undefined') {
            document.addEventListener('click', this.clickedOutside);
        }
    },
    beforeDestroy: function beforeDestroy() {
        if (typeof window !== 'undefined') {
            document.removeEventListener('click', this.clickedOutside);
        }
    }
});

/***/ }),
/* 123 */
/***/ (function(module, exports) {

module.exports={render:function (){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;
  return _c('div', {
    staticClass: "dropdown",
    class: _vm.rootClasses
  }, [(!_vm.inline) ? _c('div', {
    ref: "trigger",
    staticClass: "dropdown-trigger",
    attrs: {
      "role": "button",
      "aria-haspopup": "true"
    },
    on: {
      "click": _vm.toggle
    }
  }, [_vm._t("trigger")], 2) : _vm._e(), _vm._v(" "), _c('transition', {
    attrs: {
      "name": _vm.animation
    }
  }, [(_vm.isMobileModal) ? _c('div', {
    directives: [{
      name: "show",
      rawName: "v-show",
      value: (_vm.isActive),
      expression: "isActive"
    }],
    staticClass: "background",
    attrs: {
      "aria-hidden": !_vm.isActive
    }
  }) : _vm._e()]), _vm._v(" "), _c('transition', {
    attrs: {
      "name": _vm.animation
    }
  }, [_c('div', {
    directives: [{
      name: "show",
      rawName: "v-show",
      value: ((!_vm.disabled && (_vm.isActive || _vm.hoverable)) || _vm.inline),
      expression: "(!disabled && (isActive || hoverable)) || inline"
    }],
    ref: "dropdownMenu",
    staticClass: "dropdown-menu",
    attrs: {
      "aria-hidden": !_vm.isActive
    }
  }, [_c('div', {
    staticClass: "dropdown-content",
    attrs: {
      "role": _vm.ariaRoleMenu
    }
  }, [_vm._t("default")], 2)])])], 1)
},staticRenderFns: []}

/***/ }),
/* 124 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_symbol__ = __webpack_require__(5);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_symbol___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_symbol__);

//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//

/* harmony default export */ __webpack_exports__["default"] = ({
    name: 'BDropdownItem',
    props: {
        value: {
            type: [String, Number, Boolean, Object, Array, __WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_symbol___default.a, Function],
            default: null
        },
        separator: Boolean,
        disabled: Boolean,
        custom: Boolean,
        paddingless: Boolean,
        hasLink: Boolean,
        ariaRole: {
            type: String,
            default: ''
        }
    },
    computed: {
        anchorClasses: function anchorClasses() {
            return {
                'is-disabled': this.$parent.disabled || this.disabled,
                'is-paddingless': this.paddingless,
                'is-active': this.value !== null && this.value === this.$parent.selected
            };
        },
        itemClasses: function itemClasses() {
            return {
                'dropdown-item': !this.hasLink,
                'is-disabled': this.disabled,
                'is-paddingless': this.paddingless,
                'is-active': this.value !== null && this.value === this.$parent.selected,
                'has-link': this.hasLink
            };
        },
        ariaRoleItem: function ariaRoleItem() {
            return this.ariaRole === 'menuitem' || this.ariaRole === 'listitem' ? this.ariaRole : null;
        },

        /**
         * Check if item can be clickable.
         */
        isClickable: function isClickable() {
            return !this.$parent.disabled && !this.separator && !this.disabled && !this.custom;
        }
    },
    methods: {
        /**
         * Click listener, select the item.
         */
        selectItem: function selectItem() {
            if (!this.isClickable) return;

            this.$parent.selectItem(this.value);
            this.$emit('click');
        }
    },
    created: function created() {
        if (!this.$parent.$data._isDropdown) {
            this.$destroy();
            throw new Error('You should wrap bDropdownItem on a bDropdown');
        }
    }
});

/***/ }),
/* 125 */
/***/ (function(module, exports) {

module.exports={render:function (){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;
  return (_vm.separator) ? _c('hr', {
    staticClass: "dropdown-divider"
  }) : (!_vm.custom && !_vm.hasLink) ? _c('a', {
    staticClass: "dropdown-item",
    class: _vm.anchorClasses,
    attrs: {
      "role": _vm.ariaRoleItem,
      "tabindex": "0"
    },
    on: {
      "click": _vm.selectItem
    }
  }, [_vm._t("default")], 2) : _c('div', {
    class: _vm.itemClasses,
    attrs: {
      "role": _vm.ariaRoleItem,
      "tabindex": "0"
    },
    on: {
      "click": _vm.selectItem
    }
  }, [_vm._t("default")], 2)
},staticRenderFns: []}

/***/ }),
/* 126 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_defineProperty__ = __webpack_require__(1);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_defineProperty___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_defineProperty__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__FieldBody__ = __webpack_require__(127);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__FieldBody___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_1__FieldBody__);

//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//



/* harmony default export */ __webpack_exports__["default"] = ({
    name: 'BField',
    components: __WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_defineProperty___default()({}, __WEBPACK_IMPORTED_MODULE_1__FieldBody___default.a.name, __WEBPACK_IMPORTED_MODULE_1__FieldBody___default.a),
    props: {
        type: [String, Object],
        label: String,
        labelFor: String,
        message: [String, Array, Object],
        grouped: Boolean,
        groupMultiline: Boolean,
        position: String,
        expanded: Boolean,
        horizontal: Boolean,
        addons: {
            type: Boolean,
            default: true
        },
        customClass: String
    },
    data: function data() {
        return {
            newType: this.type,
            newMessage: this.message,
            fieldLabelSize: null,
            _isField: true // Used internally by Input and Select
        };
    },

    computed: {
        rootClasses: function rootClasses() {
            return [this.newPosition, {
                'is-expanded': this.expanded,
                'is-grouped-multiline': this.groupMultiline,
                'is-horizontal': this.horizontal
            }];
        },

        /**
         * Correct Bulma class for the side of the addon or group.
         *
         * This is not kept like the others (is-small, etc.),
         * because since 'has-addons' is set automatically it
         * doesn't make sense to teach users what addons are exactly.
         */
        newPosition: function newPosition() {
            if (this.position === undefined) return;

            var position = this.position.split('-');
            if (position.length < 1) return;

            var prefix = this.grouped ? 'is-grouped-' : 'has-addons-';

            if (this.position) return prefix + position[1];
        },

        /**
         * Formatted message in case it's an array
         * (each element is separated by <br> tag)
         */
        formattedMessage: function formattedMessage() {
            if (typeof this.newMessage === 'string') {
                return this.newMessage;
            } else {
                var messages = [];
                if (Array.isArray(this.newMessage)) {
                    this.newMessage.forEach(function (message) {
                        if (typeof message === 'string') {
                            messages.push(message);
                        } else {
                            for (var key in message) {
                                if (message[key]) {
                                    messages.push(key);
                                }
                            }
                        }
                    });
                } else {
                    for (var key in this.newMessage) {
                        if (this.newMessage[key]) {
                            messages.push(key);
                        }
                    }
                }
                return messages.filter(function (m) {
                    if (m) return m;
                }).join(' <br> ');
            }
        }
    },
    watch: {
        /**
         * Set internal type when prop change.
         */
        type: function type(value) {
            this.newType = value;
        },


        /**
         * Set internal message when prop change.
         */
        message: function message(value) {
            this.newMessage = value;
        }
    },
    methods: {
        /**
         * Field has addons if there are more than one slot
         * (element / component) in the Field.
         * Or is grouped when prop is set.
         * Is a method to be called when component re-render.
         */
        fieldType: function fieldType() {
            if (this.grouped) return 'is-grouped';

            var renderedNode = 0;
            if (this.$slots.default) {
                renderedNode = this.$slots.default.reduce(function (i, node) {
                    return node.tag ? i + 1 : i;
                }, 0);
            }
            if (renderedNode > 1 && this.addons && !this.horizontal) {
                return 'has-addons';
            }
        }
    },
    mounted: function mounted() {
        if (this.horizontal) {
            // Bulma docs: .is-normal for any .input or .button
            var elements = this.$el.querySelectorAll('.input, .select, .button, .textarea');
            if (elements.length > 0) {
                this.fieldLabelSize = 'is-normal';
            }
        }
    }
});

/***/ }),
/* 127 */
/***/ (function(module, exports, __webpack_require__) {

var Component = __webpack_require__(0)(
  /* script */
  __webpack_require__(128),
  /* template */
  null,
  /* styles */
  null,
  /* scopeId */
  null,
  /* moduleIdentifier (server only) */
  null
)

module.exports = Component.exports


/***/ }),
/* 128 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });

/* harmony default export */ __webpack_exports__["default"] = ({
    name: 'BFieldBody',
    props: {
        message: {
            type: String
        },
        type: {
            type: [String, Object]
        }
    },
    render: function render(createElement) {
        var _this = this;

        return createElement('div', { attrs: { 'class': 'field-body' } }, this.$slots.default.map(function (element) {
            // skip returns and comments
            if (!element.tag) {
                return element;
            }
            if (_this.message) {
                return createElement('b-field', { attrs: { message: _this.message, 'type': _this.type } }, [element]);
            }
            return createElement('b-field', { attrs: { 'type': _this.type } }, [element]);
        }));
    }
});

/***/ }),
/* 129 */
/***/ (function(module, exports) {

module.exports={render:function (){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;
  return _c('div', {
    staticClass: "field",
    class: [_vm.rootClasses, _vm.fieldType()]
  }, [(_vm.horizontal) ? _c('div', {
    staticClass: "field-label",
    class: [_vm.customClass, _vm.fieldLabelSize]
  }, [(_vm.label) ? _c('label', {
    staticClass: "label",
    class: _vm.customClass,
    attrs: {
      "for": _vm.labelFor
    }
  }, [_vm._v("\n            " + _vm._s(_vm.label) + "\n        ")]) : _vm._e()]) : [(_vm.label) ? _c('label', {
    staticClass: "label",
    class: _vm.customClass,
    attrs: {
      "for": _vm.labelFor
    }
  }, [_vm._v("\n            " + _vm._s(_vm.label) + "\n        ")]) : _vm._e()], _vm._v(" "), (_vm.horizontal) ? _c('b-field-body', {
    attrs: {
      "message": _vm.newMessage ? _vm.formattedMessage : '',
      "type": _vm.newType
    }
  }, [_vm._t("default")], 2) : [_vm._t("default")], _vm._v(" "), _c('p', {
    directives: [{
      name: "show",
      rawName: "v-show",
      value: (_vm.newMessage && !_vm.horizontal),
      expression: "newMessage && !horizontal"
    }],
    staticClass: "help",
    class: _vm.newType,
    domProps: {
      "innerHTML": _vm._s(_vm.formattedMessage)
    }
  })], 2)
},staticRenderFns: []}

/***/ }),
/* 130 */
/***/ (function(module, exports, __webpack_require__) {

var Component = __webpack_require__(0)(
  /* script */
  __webpack_require__(131),
  /* template */
  __webpack_require__(132),
  /* styles */
  null,
  /* scopeId */
  null,
  /* moduleIdentifier (server only) */
  null
)

module.exports = Component.exports


/***/ }),
/* 131 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//

// These should match the variables in clockpicker.scss
var indicatorSize = 40;
var paddingInner = 5;

/* harmony default export */ __webpack_exports__["default"] = ({
    name: 'BClockpickerFace',
    props: {
        pickerSize: Number,
        min: Number,
        max: Number,
        double: Boolean,
        value: Number,
        faceNumbers: Array,
        disabledValues: Function
    },
    data: function data() {
        return {
            isDragging: false,
            inputValue: this.value,
            prevAngle: 720
        };
    },

    computed: {
        /**
         * How many number indicators are shown on the face
         */
        count: function count() {
            return this.max - this.min + 1;
        },

        /**
         * How many number indicators are shown per ring on the face
         */
        countPerRing: function countPerRing() {
            return this.double ? this.count / 2 : this.count;
        },

        /**
         * Radius of the clock face
         */
        radius: function radius() {
            return this.pickerSize / 2;
        },

        /**
         * Radius of the outer ring of number indicators
         */
        outerRadius: function outerRadius() {
            return this.radius - paddingInner - indicatorSize / 2;
        },

        /**
         * Radius of the inner ring of number indicators
         */
        innerRadius: function innerRadius() {
            return Math.max(this.outerRadius * 0.6, this.outerRadius - paddingInner - indicatorSize);
            // 48px gives enough room for the outer ring of numbers
        },

        /**
         * The angle for each selectable value
         * For hours this ends up being 30 degrees, for minutes 6 degrees
         */
        degreesPerUnit: function degreesPerUnit() {
            return 360 / this.countPerRing;
        },

        /**
         * Used for calculating x/y grid location based on degrees
         */
        degrees: function degrees() {
            return this.degreesPerUnit * Math.PI / 180;
        },

        /**
         * Calculates the angle the clock hand should be rotated for the
         * selected value
         */
        handRotateAngle: function handRotateAngle() {
            var currentAngle = this.prevAngle;
            while (currentAngle < 0) {
                currentAngle += 360;
            }var targetAngle = this.calcHandAngle(this.displayedValue);
            var degreesDiff = this.shortestDistanceDegrees(currentAngle, targetAngle);
            var angle = this.prevAngle + degreesDiff;
            return angle;
        },

        /**
         * Determines how long the selector hand is based on if the
         * selected value is located along the outer or inner ring
         */
        handScale: function handScale() {
            return this.calcHandScale(this.displayedValue);
        },
        handStyle: function handStyle() {
            return {
                transform: 'rotate(' + this.handRotateAngle + 'deg) scaleY(' + this.handScale + ')',
                transition: '.3s cubic-bezier(.25,.8,.50,1)'
            };
        },

        /**
         * The value the hand should be pointing at
         */
        displayedValue: function displayedValue() {
            return this.inputValue == null ? this.min : this.inputValue;
        }
    },
    watch: {
        value: function value(_value) {
            if (_value !== this.inputValue) {
                this.prevAngle = this.handRotateAngle;
            }
            this.inputValue = _value;
        }
    },
    methods: {
        isDisabled: function isDisabled(value) {
            return this.disabledValues && this.disabledValues(value);
        },

        /**
         * Calculates the distance between two points
         */
        euclidean: function euclidean(p0, p1) {
            var dx = p1.x - p0.x;
            var dy = p1.y - p0.y;

            return Math.sqrt(dx * dx + dy * dy);
        },
        shortestDistanceDegrees: function shortestDistanceDegrees(start, stop) {
            var modDiff = (stop - start) % 360;
            var shortestDistance = 180 - Math.abs(Math.abs(modDiff) - 180);
            return (modDiff + 360) % 360 < 180 ? shortestDistance * 1 : shortestDistance * -1;
        },

        /**
         * Calculates the angle of the line from the center point
         * to the given point.
         */
        coordToAngle: function coordToAngle(center, p1) {
            var value = 2 * Math.atan2(p1.y - center.y - this.euclidean(center, p1), p1.x - center.x);
            return Math.abs(value * 180 / Math.PI);
        },

        /**
         * Generates the inline style translate() property for a
         * number indicator, which determines it's location on the
         * clock face
         */
        getNumberTranslate: function getNumberTranslate(value) {
            var _getNumberCoords = this.getNumberCoords(value),
                x = _getNumberCoords.x,
                y = _getNumberCoords.y;

            return 'translate(' + x + 'px, ' + y + 'px)';
        },

        /***
         * Calculates the coordinates on the clock face for a number
         * indicator value
         */
        getNumberCoords: function getNumberCoords(value) {
            var radius = this.isInnerRing(value) ? this.innerRadius : this.outerRadius;
            return {
                x: Math.round(radius * Math.sin((value - this.min) * this.degrees)),
                y: Math.round(-radius * Math.cos((value - this.min) * this.degrees))
            };
        },
        getFaceNumberClasses: function getFaceNumberClasses(num) {
            return {
                'active': num.value === this.displayedValue,
                'disabled': this.isDisabled(num.value)
            };
        },

        /**
         * Determines if a value resides on the inner ring
         */
        isInnerRing: function isInnerRing(value) {
            return this.double && value - this.min >= this.countPerRing;
        },
        calcHandAngle: function calcHandAngle(value) {
            var angle = this.degreesPerUnit * (value - this.min);
            if (this.isInnerRing(value)) angle -= 360;
            return angle;
        },
        calcHandScale: function calcHandScale(value) {
            return this.isInnerRing(value) ? this.innerRadius / this.outerRadius : 1;
        },
        onMouseDown: function onMouseDown(e) {
            e.preventDefault();
            this.isDragging = true;
            this.onDragMove(e);
        },
        onMouseUp: function onMouseUp() {
            this.isDragging = false;
            if (!this.isDisabled(this.inputValue)) {
                this.$emit('change', this.inputValue);
            }
        },
        onDragMove: function onDragMove(e) {
            e.preventDefault();
            if (!this.isDragging && e.type !== 'click') return;

            var _$refs$clock$getBound = this.$refs.clock.getBoundingClientRect(),
                width = _$refs$clock$getBound.width,
                top = _$refs$clock$getBound.top,
                left = _$refs$clock$getBound.left;

            var _ref = 'touches' in e ? e.touches[0] : e,
                clientX = _ref.clientX,
                clientY = _ref.clientY;

            var center = { x: width / 2, y: -width / 2 };
            var coords = { x: clientX - left, y: top - clientY };
            var handAngle = Math.round(this.coordToAngle(center, coords) + 360) % 360;
            var insideClick = this.double && this.euclidean(center, coords) < (this.outerRadius + this.innerRadius) / 2 - 16;

            var value = Math.round(handAngle / this.degreesPerUnit) + this.min + (insideClick ? this.countPerRing : 0);

            // Necessary to fix edge case when selecting left part of max value
            if (handAngle >= 360 - this.degreesPerUnit / 2) {
                value = insideClick ? this.max : this.min;
            }
            this.update(value);
        },
        update: function update(value) {
            if (this.inputValue !== value && !this.isDisabled(value)) {
                this.prevAngle = this.handRotateAngle;
                this.inputValue = value;
                this.$emit('input', value);
            }
        }
    }
});

/***/ }),
/* 132 */
/***/ (function(module, exports) {

module.exports={render:function (){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;
  return _c('div', {
    staticClass: "b-clockpicker-face",
    on: {
      "mousedown": _vm.onMouseDown,
      "mouseup": _vm.onMouseUp,
      "mousemove": _vm.onDragMove,
      "touchstart": _vm.onMouseDown,
      "touchend": _vm.onMouseUp,
      "touchmove": _vm.onDragMove
    }
  }, [_c('div', {
    ref: "clock",
    staticClass: "b-clockpicker-face-outer-ring"
  }, [_c('div', {
    staticClass: "b-clockpicker-face-hand",
    style: (_vm.handStyle)
  }), _vm._v(" "), _vm._l((_vm.faceNumbers), function(num, index) {
    return _c('span', {
      key: index,
      staticClass: "b-clockpicker-face-number",
      class: _vm.getFaceNumberClasses(num),
      style: ({
        transform: _vm.getNumberTranslate(num.value)
      })
    }, [_c('span', [_vm._v(_vm._s(num.label))])])
  })], 2)])
},staticRenderFns: []}

/***/ }),
/* 133 */
/***/ (function(module, exports) {

module.exports={render:function (){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;
  return _c('div', {
    staticClass: "b-clockpicker",
    class: _vm.type
  }, [(!_vm.isMobile || _vm.inline) ? _c('b-dropdown', {
    ref: "dropdown",
    attrs: {
      "position": _vm.position,
      "disabled": _vm.disabled,
      "inline": _vm.inline
    }
  }, [(!_vm.inline) ? _c('b-input', _vm._b({
    ref: "input",
    attrs: {
      "slot": "trigger",
      "autocomplete": "off",
      "value": _vm.formatValue(_vm.dateSelected),
      "placeholder": _vm.placeholder,
      "size": _vm.size,
      "icon": _vm.icon,
      "icon-pack": _vm.iconPack,
      "loading": _vm.loading,
      "disabled": _vm.disabled,
      "readonly": !_vm.editable,
      "rounded": _vm.rounded
    },
    on: {
      "focus": function($event) {
        _vm.$emit('focus', $event)
      },
      "blur": function($event) {
        _vm.$emit('blur', $event) && _vm.checkHtml5Validity()
      }
    },
    nativeOn: {
      "change": function($event) {
        _vm.onChange($event.target.value)
      }
    },
    slot: "trigger"
  }, 'b-input', _vm.$attrs, false)) : _vm._e(), _vm._v(" "), _c('div', {
    staticClass: "card",
    staticStyle: {
      "height": "auto"
    },
    attrs: {
      "disabled": _vm.disabled,
      "custom": ""
    }
  }, [(_vm.inline) ? _c('header', {
    staticClass: "card-header"
  }, [_c('div', {
    staticClass: "b-clockpicker-header card-header-title"
  }, [_c('div', {
    staticClass: "b-clockpicker-time"
  }, [_c('span', {
    staticClass: "b-clockpicker-btn",
    class: {
      active: _vm.isSelectingHour
    },
    on: {
      "click": function($event) {
        _vm.isSelectingHour = true
      }
    }
  }, [_vm._v(_vm._s(_vm.hoursDisplay))]), _vm._v(" "), _c('span', [_vm._v(":")]), _vm._v(" "), _c('span', {
    staticClass: "b-clockpicker-btn",
    class: {
      active: !_vm.isSelectingHour
    },
    on: {
      "click": function($event) {
        _vm.isSelectingHour = false
      }
    }
  }, [_vm._v(_vm._s(_vm.minutesDisplay))])]), _vm._v(" "), (!_vm.isHourFormat24) ? _c('div', {
    staticClass: "b-clockpicker-period"
  }, [_c('div', {
    staticClass: "b-clockpicker-btn",
    class: {
      active: _vm.meridienSelected == _vm.AM
    },
    on: {
      "click": function($event) {
        _vm.onMeridienClick(_vm.AM)
      }
    }
  }, [_vm._v("am")]), _vm._v(" "), _c('div', {
    staticClass: "b-clockpicker-btn",
    class: {
      active: _vm.meridienSelected == _vm.PM
    },
    on: {
      "click": function($event) {
        _vm.onMeridienClick(_vm.PM)
      }
    }
  }, [_vm._v("pm")])]) : _vm._e()])]) : _vm._e(), _vm._v(" "), _c('div', {
    staticClass: "card-content"
  }, [_c('div', {
    staticClass: "b-clockpicker-body",
    style: ({
      width: _vm.faceSize + 'px',
      height: _vm.faceSize + 'px'
    })
  }, [(!_vm.inline) ? _c('div', {
    staticClass: "b-clockpicker-time"
  }, [_c('div', {
    staticClass: "b-clockpicker-btn",
    class: {
      active: _vm.isSelectingHour
    },
    on: {
      "click": function($event) {
        _vm.isSelectingHour = true
      }
    }
  }, [_vm._v("Hours")]), _vm._v(" "), _c('span', {
    staticClass: "b-clockpicker-btn",
    class: {
      active: !_vm.isSelectingHour
    },
    on: {
      "click": function($event) {
        _vm.isSelectingHour = false
      }
    }
  }, [_vm._v("Min")])]) : _vm._e(), _vm._v(" "), (!_vm.isHourFormat24 && !_vm.inline) ? _c('div', {
    staticClass: "b-clockpicker-period"
  }, [_c('div', {
    staticClass: "b-clockpicker-btn",
    class: {
      active: _vm.meridienSelected == _vm.AM
    },
    on: {
      "click": function($event) {
        _vm.onMeridienClick(_vm.AM)
      }
    }
  }, [_vm._v(_vm._s(_vm.AM))]), _vm._v(" "), _c('div', {
    staticClass: "b-clockpicker-btn",
    class: {
      active: _vm.meridienSelected == _vm.PM
    },
    on: {
      "click": function($event) {
        _vm.onMeridienClick(_vm.PM)
      }
    }
  }, [_vm._v(_vm._s(_vm.PM))])]) : _vm._e(), _vm._v(" "), _c('b-clockpicker-face', {
    attrs: {
      "picker-size": _vm.faceSize,
      "min": _vm.minFaceValue,
      "max": _vm.maxFaceValue,
      "face-numbers": _vm.isSelectingHour ? _vm.hours : _vm.minutes,
      "disabled-values": _vm.faceDisabledValues(),
      "double": _vm.isSelectingHour && _vm.isHourFormat24,
      "value": _vm.isSelectingHour ? _vm.hoursSelected : _vm.minutesSelected
    },
    on: {
      "input": _vm.onClockInput,
      "change": _vm.onClockChange
    }
  })], 1)]), _vm._v(" "), (_vm.$slots.default !== undefined && _vm.$slots.default.length) ? _c('footer', {
    staticClass: "b-clockpicker-footer card-footer"
  }, [_vm._t("default")], 2) : _vm._e()])], 1) : _vm._e()], 1)
},staticRenderFns: []}

/***/ }),
/* 134 */
/***/ (function(module, exports, __webpack_require__) {

var Component = __webpack_require__(0)(
  /* script */
  __webpack_require__(135),
  /* template */
  __webpack_require__(144),
  /* styles */
  null,
  /* scopeId */
  null,
  /* moduleIdentifier (server only) */
  null
)

module.exports = Component.exports


/***/ }),
/* 135 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_defineProperty__ = __webpack_require__(1);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_defineProperty___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_defineProperty__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__utils_FormElementMixin__ = __webpack_require__(13);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__utils_helpers__ = __webpack_require__(7);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_3__utils_config__ = __webpack_require__(2);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_4__dropdown_Dropdown__ = __webpack_require__(28);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_4__dropdown_Dropdown___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_4__dropdown_Dropdown__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_5__dropdown_DropdownItem__ = __webpack_require__(29);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_5__dropdown_DropdownItem___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_5__dropdown_DropdownItem__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_6__input_Input__ = __webpack_require__(23);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_6__input_Input___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_6__input_Input__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_7__field_Field__ = __webpack_require__(30);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_7__field_Field___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_7__field_Field__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_8__select_Select__ = __webpack_require__(31);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_8__select_Select___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_8__select_Select__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_9__icon_Icon__ = __webpack_require__(3);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_9__icon_Icon___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_9__icon_Icon__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_10__DatepickerTable__ = __webpack_require__(138);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_10__DatepickerTable___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_10__DatepickerTable__);


var _components;

//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//













/* harmony default export */ __webpack_exports__["default"] = ({
    name: 'BDatepicker',
    components: (_components = {}, __WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_defineProperty___default()(_components, __WEBPACK_IMPORTED_MODULE_10__DatepickerTable___default.a.name, __WEBPACK_IMPORTED_MODULE_10__DatepickerTable___default.a), __WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_defineProperty___default()(_components, __WEBPACK_IMPORTED_MODULE_6__input_Input___default.a.name, __WEBPACK_IMPORTED_MODULE_6__input_Input___default.a), __WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_defineProperty___default()(_components, __WEBPACK_IMPORTED_MODULE_7__field_Field___default.a.name, __WEBPACK_IMPORTED_MODULE_7__field_Field___default.a), __WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_defineProperty___default()(_components, __WEBPACK_IMPORTED_MODULE_8__select_Select___default.a.name, __WEBPACK_IMPORTED_MODULE_8__select_Select___default.a), __WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_defineProperty___default()(_components, __WEBPACK_IMPORTED_MODULE_9__icon_Icon___default.a.name, __WEBPACK_IMPORTED_MODULE_9__icon_Icon___default.a), __WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_defineProperty___default()(_components, __WEBPACK_IMPORTED_MODULE_4__dropdown_Dropdown___default.a.name, __WEBPACK_IMPORTED_MODULE_4__dropdown_Dropdown___default.a), __WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_defineProperty___default()(_components, __WEBPACK_IMPORTED_MODULE_5__dropdown_DropdownItem___default.a.name, __WEBPACK_IMPORTED_MODULE_5__dropdown_DropdownItem___default.a), _components),
    mixins: [__WEBPACK_IMPORTED_MODULE_1__utils_FormElementMixin__["a" /* default */]],
    inheritAttrs: false,
    props: {
        value: Date,
        dayNames: {
            type: Array,
            default: function _default() {
                if (Array.isArray(__WEBPACK_IMPORTED_MODULE_3__utils_config__["a" /* default */].defaultDayNames)) {
                    return __WEBPACK_IMPORTED_MODULE_3__utils_config__["a" /* default */].defaultDayNames;
                } else {
                    return ['Su', 'M', 'Tu', 'W', 'Th', 'F', 'S'];
                }
            }
        },
        monthNames: {
            type: Array,
            default: function _default() {
                if (Array.isArray(__WEBPACK_IMPORTED_MODULE_3__utils_config__["a" /* default */].defaultMonthNames)) {
                    return __WEBPACK_IMPORTED_MODULE_3__utils_config__["a" /* default */].defaultMonthNames;
                } else {
                    return ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
                }
            }
        },
        firstDayOfWeek: {
            type: Number,
            default: function _default() {
                if (typeof __WEBPACK_IMPORTED_MODULE_3__utils_config__["a" /* default */].defaultFirstDayOfWeek === 'number') {
                    return __WEBPACK_IMPORTED_MODULE_3__utils_config__["a" /* default */].defaultFirstDayOfWeek;
                } else {
                    return 0;
                }
            }
        },
        inline: Boolean,
        minDate: Date,
        maxDate: Date,
        focusedDate: Date,
        placeholder: String,
        editable: Boolean,
        disabled: Boolean,
        unselectableDates: Array,
        unselectableDaysOfWeek: {
            type: Array,
            default: function _default() {
                return __WEBPACK_IMPORTED_MODULE_3__utils_config__["a" /* default */].defaultUnselectableDaysOfWeek;
            }
        },
        selectableDates: Array,
        dateFormatter: {
            type: Function,
            default: function _default(date) {
                if (typeof __WEBPACK_IMPORTED_MODULE_3__utils_config__["a" /* default */].defaultDateFormatter === 'function') {
                    return __WEBPACK_IMPORTED_MODULE_3__utils_config__["a" /* default */].defaultDateFormatter(date);
                } else {
                    var yyyyMMdd = date.getFullYear() + '/' + (date.getMonth() + 1) + '/' + date.getDate();
                    var d = new Date(yyyyMMdd);
                    return d.toLocaleDateString();
                }
            }
        },
        dateParser: {
            type: Function,
            default: function _default(date) {
                if (typeof __WEBPACK_IMPORTED_MODULE_3__utils_config__["a" /* default */].defaultDateParser === 'function') {
                    return __WEBPACK_IMPORTED_MODULE_3__utils_config__["a" /* default */].defaultDateParser(date);
                } else {
                    return new Date(Date.parse(date));
                }
            }
        },
        dateCreator: {
            type: Function,
            default: function _default() {
                if (typeof __WEBPACK_IMPORTED_MODULE_3__utils_config__["a" /* default */].defaultDateCreator === 'function') {
                    return __WEBPACK_IMPORTED_MODULE_3__utils_config__["a" /* default */].defaultDateCreator();
                } else {
                    return new Date();
                }
            }
        },
        mobileNative: {
            type: Boolean,
            default: function _default() {
                return __WEBPACK_IMPORTED_MODULE_3__utils_config__["a" /* default */].defaultDatepickerMobileNative;
            }
        },
        position: String,
        events: Array,
        indicators: {
            type: String,
            default: 'dots'
        }
    },
    data: function data() {
        var focusedDate = this.value || this.focusedDate || this.dateCreator();

        return {
            dateSelected: this.value,
            focusedDateData: {
                month: focusedDate.getMonth(),
                year: focusedDate.getFullYear()
            },
            _elementRef: 'input',
            _isDatepicker: true
        };
    },

    computed: {
        /*
        * Returns an array of years for the year dropdown. If earliest/latest
        * dates are set by props, range of years will fall within those dates.
        */
        listOfYears: function listOfYears() {
            var latestYear = this.focusedDateData.year + 3;
            if (this.maxDate && this.maxDate.getFullYear() < latestYear) {
                latestYear = this.maxDate.getFullYear();
            }

            var earliestYear = latestYear - 100 + 3;
            if (this.minDate && this.minDate.getFullYear() > earliestYear) {
                earliestYear = this.minDate.getFullYear();
            }

            var arrayOfYears = [];
            for (var i = earliestYear; i <= latestYear; i++) {
                arrayOfYears.push(i);
            }

            return arrayOfYears.reverse();
        },
        isFirstMonth: function isFirstMonth() {
            if (!this.minDate) return false;
            var dateToCheck = new Date(this.focusedDateData.year, this.focusedDateData.month);
            var date = new Date(this.minDate.getFullYear(), this.minDate.getMonth());
            return dateToCheck <= date;
        },
        isLastMonth: function isLastMonth() {
            if (!this.maxDate) return false;
            var dateToCheck = new Date(this.focusedDateData.year, this.focusedDateData.month);
            var date = new Date(this.maxDate.getFullYear(), this.maxDate.getMonth());
            return dateToCheck >= date;
        },
        isMobile: function isMobile() {
            return this.mobileNative && __WEBPACK_IMPORTED_MODULE_2__utils_helpers__["c" /* isMobile */].any();
        }
    },
    watch: {
        /*
        * Emit input event with selected date as payload, set isActive to false.
        * Update internal focusedDateData
        */
        dateSelected: function dateSelected(value) {
            var currentDate = !value ? this.dateCreator() : value;
            this.focusedDateData = {
                month: currentDate.getMonth(),
                year: currentDate.getFullYear()
            };
            this.$emit('input', value);
            if (this.$refs.dropdown) {
                this.$refs.dropdown.isActive = false;
            }
        },


        /**
         * When v-model is changed:
         *   1. Update internal value.
         *   2. If it's invalid, validate again.
         */
        value: function value(_value) {
            this.dateSelected = _value;

            !this.isValid && this.$refs.input.checkHtml5Validity();
        },
        focusedDate: function focusedDate(value) {
            if (value) {
                this.focusedDateData = {
                    month: value.getMonth(),
                    year: value.getFullYear()
                };
            }
        },


        /*
        * Emit input event on month and/or year change
        */
        'focusedDateData.month': function focusedDateDataMonth(value) {
            this.$emit('change-month', value);
        },
        'focusedDateData.year': function focusedDateDataYear(value) {
            this.$emit('change-year', value);
        }
    },
    methods: {
        /*
        * Emit input event with selected date as payload for v-model in parent
        */
        updateSelectedDate: function updateSelectedDate(date) {
            this.dateSelected = date;
        },


        /*
        * Parse string into date
        */
        onChange: function onChange(value) {
            var date = this.dateParser(value);
            if (date && !isNaN(date)) {
                this.dateSelected = date;
            } else {
                // Force refresh input value when not valid date
                this.dateSelected = null;
                this.$refs.input.newValue = this.dateSelected;
            }
        },


        /*
        * Format date into string
        */
        formatValue: function formatValue(value) {
            if (value && !isNaN(value)) {
                return this.dateFormatter(value);
            } else {
                return null;
            }
        },


        /*
        * Either decrement month by 1 if not January or decrement year by 1
        * and set month to 11 (December)
        */
        decrementMonth: function decrementMonth() {
            if (this.disabled) return;

            if (this.focusedDateData.month > 0) {
                this.focusedDateData.month -= 1;
            } else {
                this.focusedDateData.month = 11;
                this.focusedDateData.year -= 1;
            }
        },


        /*
        * Either increment month by 1 if not December or increment year by 1
        * and set month to 0 (January)
        */
        incrementMonth: function incrementMonth() {
            if (this.disabled) return;

            if (this.focusedDateData.month < 11) {
                this.focusedDateData.month += 1;
            } else {
                this.focusedDateData.month = 0;
                this.focusedDateData.year += 1;
            }
        },


        /*
        * Format date into string 'YYYY-MM-DD'
        */
        formatYYYYMMDD: function formatYYYYMMDD(value) {
            var date = new Date(value);
            if (value && !isNaN(date)) {
                var year = date.getFullYear();
                var month = date.getMonth() + 1;
                var day = date.getDate();
                return year + '-' + ((month < 10 ? '0' : '') + month) + '-' + ((day < 10 ? '0' : '') + day);
            }
            return '';
        },


        /*
        * Parse date from string
        */
        onChangeNativePicker: function onChangeNativePicker(event) {
            var date = event.target.value;
            this.dateSelected = date ? new Date(date.replace(/-/g, '/')) : null;
        }
    }
});

/***/ }),
/* 136 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_symbol__ = __webpack_require__(5);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_symbol___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_symbol__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_babel_runtime_helpers_defineProperty__ = __webpack_require__(1);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_babel_runtime_helpers_defineProperty___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_1_babel_runtime_helpers_defineProperty__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__icon_Icon__ = __webpack_require__(3);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__icon_Icon___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_2__icon_Icon__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_3__utils_FormElementMixin__ = __webpack_require__(13);


//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//




/* harmony default export */ __webpack_exports__["default"] = ({
    name: 'BSelect',
    components: __WEBPACK_IMPORTED_MODULE_1_babel_runtime_helpers_defineProperty___default()({}, __WEBPACK_IMPORTED_MODULE_2__icon_Icon___default.a.name, __WEBPACK_IMPORTED_MODULE_2__icon_Icon___default.a),
    mixins: [__WEBPACK_IMPORTED_MODULE_3__utils_FormElementMixin__["a" /* default */]],
    inheritAttrs: false,
    props: {
        value: {
            type: [String, Number, Boolean, Object, Array, __WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_symbol___default.a, Function],
            default: null
        },
        placeholder: String,
        multiple: Boolean,
        nativeSize: [String, Number]
    },
    data: function data() {
        return {
            selected: this.value,
            _elementRef: 'select'
        };
    },

    computed: {
        computedValue: {
            get: function get() {
                return this.selected;
            },
            set: function set(value) {
                this.selected = value;
                this.$emit('input', value);
                !this.isValid && this.checkHtml5Validity();
            }
        },
        spanClasses: function spanClasses() {
            return [this.size, this.statusType, {
                'is-fullwidth': this.expanded,
                'is-loading': this.loading,
                'is-multiple': this.multiple,
                'is-rounded': this.rounded,
                'is-empty': this.selected === null
            }];
        }
    },
    watch: {
        /**
         * When v-model is changed:
         *   1. Set the selected option.
         *   2. If it's invalid, validate again.
         */
        value: function value(_value) {
            this.selected = _value;
            !this.isValid && this.checkHtml5Validity();
        }
    }
});

/***/ }),
/* 137 */
/***/ (function(module, exports) {

module.exports={render:function (){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;
  return _c('div', {
    staticClass: "control",
    class: {
      'is-expanded': _vm.expanded, 'has-icons-left': _vm.icon
    }
  }, [_c('span', {
    staticClass: "select",
    class: _vm.spanClasses
  }, [_c('select', _vm._b({
    directives: [{
      name: "model",
      rawName: "v-model",
      value: (_vm.computedValue),
      expression: "computedValue"
    }],
    ref: "select",
    attrs: {
      "multiple": _vm.multiple,
      "size": _vm.nativeSize
    },
    on: {
      "blur": function($event) {
        _vm.$emit('blur', $event) && _vm.checkHtml5Validity()
      },
      "focus": function($event) {
        _vm.$emit('focus', $event)
      },
      "change": function($event) {
        var $$selectedVal = Array.prototype.filter.call($event.target.options, function(o) {
          return o.selected
        }).map(function(o) {
          var val = "_value" in o ? o._value : o.value;
          return val
        });
        _vm.computedValue = $event.target.multiple ? $$selectedVal : $$selectedVal[0]
      }
    }
  }, 'select', _vm.$attrs, false), [(_vm.placeholder) ? [(_vm.computedValue == null) ? _c('option', {
    attrs: {
      "disabled": "",
      "hidden": ""
    },
    domProps: {
      "value": null
    }
  }, [_vm._v("\n                    " + _vm._s(_vm.placeholder) + "\n                ")]) : _vm._e()] : _vm._e(), _vm._v(" "), _vm._t("default")], 2)]), _vm._v(" "), (_vm.icon) ? _c('b-icon', {
    staticClass: "is-left",
    attrs: {
      "icon": _vm.icon,
      "pack": _vm.iconPack,
      "size": _vm.iconSize
    }
  }) : _vm._e()], 1)
},staticRenderFns: []}

/***/ }),
/* 138 */
/***/ (function(module, exports, __webpack_require__) {

var Component = __webpack_require__(0)(
  /* script */
  __webpack_require__(139),
  /* template */
  __webpack_require__(143),
  /* styles */
  null,
  /* scopeId */
  null,
  /* moduleIdentifier (server only) */
  null
)

module.exports = Component.exports


/***/ }),
/* 139 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_defineProperty__ = __webpack_require__(1);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_defineProperty___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_defineProperty__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__DatepickerTableRow__ = __webpack_require__(140);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__DatepickerTableRow___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_1__DatepickerTableRow__);

//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//



/* harmony default export */ __webpack_exports__["default"] = ({
    name: 'BDatepickerTable',
    components: __WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_defineProperty___default()({}, __WEBPACK_IMPORTED_MODULE_1__DatepickerTableRow___default.a.name, __WEBPACK_IMPORTED_MODULE_1__DatepickerTableRow___default.a),
    props: {
        value: Date,
        dayNames: Array,
        monthNames: Array,
        firstDayOfWeek: Number,
        events: Array,
        indicators: String,
        minDate: Date,
        maxDate: Date,
        focused: Object,
        disabled: Boolean,
        dateCreator: Function,
        unselectableDates: Array,
        unselectableDaysOfWeek: Array,
        selectableDates: Array
    },
    computed: {
        visibleDayNames: function visibleDayNames() {
            var visibleDayNames = [];
            var index = this.firstDayOfWeek;
            while (visibleDayNames.length < this.dayNames.length) {
                var currentDayName = this.dayNames[index % this.dayNames.length];
                visibleDayNames.push(currentDayName);
                index++;
            }
            return visibleDayNames;
        },
        hasEvents: function hasEvents() {
            return this.events && this.events.length;
        },


        /*
        * Return array of all events in the specified month
        */
        eventsInThisMonth: function eventsInThisMonth() {
            if (!this.events) return [];

            var monthEvents = [];

            for (var i = 0; i < this.events.length; i++) {
                var event = this.events[i];

                if (!event.hasOwnProperty('date')) {
                    event = { date: event };
                }
                if (!event.hasOwnProperty('type')) {
                    event.type = 'is-primary';
                }
                if (event.date.getMonth() === this.focused.month && event.date.getFullYear() === this.focused.year) {
                    monthEvents.push(event);
                }
            }

            return monthEvents;
        },

        /*
        * Return array of all weeks in the specified month
        */
        weeksInThisMonth: function weeksInThisMonth() {
            var month = this.focused.month;
            var year = this.focused.year;
            var weeksInThisMonth = [];
            var daysInThisMonth = new Date(year, month + 1, 0).getDate();

            var startingDay = 1;

            while (startingDay <= daysInThisMonth + 6) {
                var newWeek = this.weekBuilder(startingDay, month, year);
                var weekValid = false;

                newWeek.forEach(function (day) {
                    if (day.getMonth() === month) {
                        weekValid = true;
                    }
                });

                if (weekValid) {
                    weeksInThisMonth.push(newWeek);
                }

                startingDay += 7;
            }

            return weeksInThisMonth;
        }
    },
    methods: {
        /*
        * Emit input event with selected date as payload for v-model in parent
        */
        updateSelectedDate: function updateSelectedDate(date) {
            this.$emit('input', date);
        },


        /*
        * Return array of all days in the week that the startingDate is within
        */
        weekBuilder: function weekBuilder(startingDate, month, year) {
            var thisMonth = new Date(year, month);

            var thisWeek = [];

            var dayOfWeek = new Date(year, month, startingDate).getDay();

            var end = dayOfWeek >= this.firstDayOfWeek ? dayOfWeek - this.firstDayOfWeek : 7 - this.firstDayOfWeek + dayOfWeek;

            var daysAgo = 1;
            for (var i = 0; i < end; i++) {
                thisWeek.unshift(new Date(thisMonth.getFullYear(), thisMonth.getMonth(), startingDate - daysAgo));
                daysAgo++;
            }

            thisWeek.push(new Date(year, month, startingDate));

            var daysForward = 1;
            while (thisWeek.length < 7) {
                thisWeek.push(new Date(year, month, startingDate + daysForward));
                daysForward++;
            }

            return thisWeek;
        },
        eventsInThisWeek: function eventsInThisWeek(week) {
            return this.eventsInThisMonth.filter(function (event) {
                var stripped = new Date(Date.parse(event.date));
                stripped.setHours(0);
                stripped.setMinutes(0);
                stripped.setSeconds(0);
                stripped.setMilliseconds(0);
                var timed = stripped.getTime();

                return week.some(function (weekDate) {
                    return weekDate.getTime() === timed;
                });
            });
        }
    }
});

/***/ }),
/* 140 */
/***/ (function(module, exports, __webpack_require__) {

var Component = __webpack_require__(0)(
  /* script */
  __webpack_require__(141),
  /* template */
  __webpack_require__(142),
  /* styles */
  null,
  /* scopeId */
  null,
  /* moduleIdentifier (server only) */
  null
)

module.exports = Component.exports


/***/ }),
/* 141 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//

/* harmony default export */ __webpack_exports__["default"] = ({
    name: 'BDatepickerTableRow',
    props: {
        selectedDate: Date,
        week: {
            type: Array,
            required: true
        },
        month: {
            type: Number,
            required: true
        },
        minDate: Date,
        maxDate: Date,
        disabled: Boolean,
        unselectableDates: Array,
        unselectableDaysOfWeek: Array,
        selectableDates: Array,
        events: Array,
        indicators: String,
        dateCreator: Function
    },
    methods: {
        /*
        * Check that selected day is within earliest/latest params and
        * is within this month
        */
        selectableDate: function selectableDate(day) {
            var validity = [];

            if (this.minDate) {
                validity.push(day >= this.minDate);
            }

            if (this.maxDate) {
                validity.push(day <= this.maxDate);
            }

            validity.push(day.getMonth() === this.month);

            if (this.selectableDates) {
                for (var i = 0; i < this.selectableDates.length; i++) {
                    var enabledDate = this.selectableDates[i];
                    if (day.getDate() === enabledDate.getDate() && day.getFullYear() === enabledDate.getFullYear() && day.getMonth() === enabledDate.getMonth()) {
                        return true;
                    } else {
                        validity.push(false);
                    }
                }
            }

            if (this.unselectableDates) {
                for (var _i = 0; _i < this.unselectableDates.length; _i++) {
                    var disabledDate = this.unselectableDates[_i];
                    validity.push(day.getDate() !== disabledDate.getDate() || day.getFullYear() !== disabledDate.getFullYear() || day.getMonth() !== disabledDate.getMonth());
                }
            }

            if (this.unselectableDaysOfWeek) {
                for (var _i2 = 0; _i2 < this.unselectableDaysOfWeek.length; _i2++) {
                    var dayOfWeek = this.unselectableDaysOfWeek[_i2];
                    validity.push(day.getDay() !== dayOfWeek);
                }
            }

            return validity.indexOf(false) < 0;
        },


        /*
        * Emit select event with chosen date as payload
        */
        emitChosenDate: function emitChosenDate(day) {
            if (this.disabled) return;

            if (this.selectableDate(day)) {
                this.$emit('select', day);
            }
        },
        eventsDateMatch: function eventsDateMatch(day) {
            if (!this.events.length) return false;

            var dayEvents = [];

            for (var i = 0; i < this.events.length; i++) {
                if (this.events[i].date.getDay() === day.getDay()) {
                    dayEvents.push(this.events[i]);
                }
            }

            if (!dayEvents.length) {
                return false;
            }

            return dayEvents;
        },


        /*
        * Build classObject for cell using validations
        */
        classObject: function classObject(day) {
            function dateMatch(dateOne, dateTwo) {
                // if either date is null or undefined, return false
                if (!dateOne || !dateTwo) {
                    return false;
                }

                return dateOne.getDate() === dateTwo.getDate() && dateOne.getFullYear() === dateTwo.getFullYear() && dateOne.getMonth() === dateTwo.getMonth();
            }

            return {
                'is-selected': dateMatch(day, this.selectedDate),
                'is-today': dateMatch(day, this.dateCreator()),
                'is-selectable': this.selectableDate(day) && !this.disabled,
                'is-unselectable': !this.selectableDate(day) || this.disabled
            };
        }
    }
});

/***/ }),
/* 142 */
/***/ (function(module, exports) {

module.exports={render:function (){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;
  return _c('div', {
    staticClass: "datepicker-row"
  }, [_vm._l((_vm.week), function(day, index) {
    return [(_vm.selectableDate(day) && !_vm.disabled) ? _c('a', {
      key: index,
      staticClass: "datepicker-cell",
      class: [_vm.classObject(day), {
        'has-event': _vm.eventsDateMatch(day)
      }, _vm.indicators],
      attrs: {
        "role": "button",
        "href": "#",
        "disabled": _vm.disabled
      },
      on: {
        "click": function($event) {
          $event.preventDefault();
          _vm.emitChosenDate(day)
        },
        "keydown": [function($event) {
          if (!('button' in $event) && _vm._k($event.keyCode, "enter", 13, $event.key)) { return null; }
          $event.preventDefault();
          _vm.emitChosenDate(day)
        }, function($event) {
          if (!('button' in $event) && _vm._k($event.keyCode, "space", 32, $event.key)) { return null; }
          $event.preventDefault();
          _vm.emitChosenDate(day)
        }]
      }
    }, [_vm._v("\n            " + _vm._s(day.getDate()) + "\n\n            "), (_vm.eventsDateMatch(day)) ? _c('div', {
      staticClass: "events"
    }, _vm._l((_vm.eventsDateMatch(day)), function(event, index) {
      return _c('div', {
        key: index,
        staticClass: "event",
        class: event.type
      })
    })) : _vm._e()]) : _c('div', {
      key: index,
      staticClass: "datepicker-cell",
      class: _vm.classObject(day)
    }, [_vm._v("\n            " + _vm._s(day.getDate()) + "\n        ")])]
  })], 2)
},staticRenderFns: []}

/***/ }),
/* 143 */
/***/ (function(module, exports) {

module.exports={render:function (){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;
  return _c('section', {
    staticClass: "datepicker-table"
  }, [_c('header', {
    staticClass: "datepicker-header"
  }, _vm._l((_vm.visibleDayNames), function(day, index) {
    return _c('div', {
      key: index,
      staticClass: "datepicker-cell"
    }, [_vm._v("\n            " + _vm._s(day) + "\n        ")])
  })), _vm._v(" "), _c('div', {
    staticClass: "datepicker-body",
    class: {
      'has-events': _vm.hasEvents
    }
  }, _vm._l((_vm.weeksInThisMonth), function(week, index) {
    return _c('b-datepicker-table-row', {
      key: index,
      attrs: {
        "selected-date": _vm.value,
        "week": week,
        "month": _vm.focused.month,
        "min-date": _vm.minDate,
        "max-date": _vm.maxDate,
        "disabled": _vm.disabled,
        "unselectable-dates": _vm.unselectableDates,
        "unselectable-days-of-week": _vm.unselectableDaysOfWeek,
        "selectable-dates": _vm.selectableDates,
        "events": _vm.eventsInThisWeek(week),
        "indicators": _vm.indicators,
        "date-creator": _vm.dateCreator
      },
      on: {
        "select": _vm.updateSelectedDate
      }
    })
  }))])
},staticRenderFns: []}

/***/ }),
/* 144 */
/***/ (function(module, exports) {

module.exports={render:function (){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;
  return _c('div', {
    staticClass: "datepicker control",
    class: [_vm.size, {
      'is-expanded': _vm.expanded
    }]
  }, [(!_vm.isMobile || _vm.inline) ? _c('b-dropdown', {
    ref: "dropdown",
    attrs: {
      "position": _vm.position,
      "disabled": _vm.disabled,
      "inline": _vm.inline
    }
  }, [(!_vm.inline) ? _c('b-input', _vm._b({
    ref: "input",
    attrs: {
      "slot": "trigger",
      "autocomplete": "off",
      "value": _vm.formatValue(_vm.dateSelected),
      "placeholder": _vm.placeholder,
      "size": _vm.size,
      "icon": _vm.icon,
      "icon-pack": _vm.iconPack,
      "rounded": _vm.rounded,
      "loading": _vm.loading,
      "disabled": _vm.disabled,
      "readonly": !_vm.editable
    },
    on: {
      "focus": function($event) {
        _vm.$emit('focus', $event)
      },
      "blur": function($event) {
        _vm.$emit('blur', $event) && _vm.checkHtml5Validity()
      }
    },
    nativeOn: {
      "change": function($event) {
        _vm.onChange($event.target.value)
      }
    },
    slot: "trigger"
  }, 'b-input', _vm.$attrs, false)) : _vm._e(), _vm._v(" "), _c('b-dropdown-item', {
    attrs: {
      "disabled": _vm.disabled,
      "custom": ""
    }
  }, [_c('header', {
    staticClass: "datepicker-header"
  }, [(_vm.$slots.header !== undefined && _vm.$slots.header.length) ? [_vm._t("header")] : _c('div', {
    staticClass: "pagination field is-centered",
    class: _vm.size
  }, [_c('a', {
    directives: [{
      name: "show",
      rawName: "v-show",
      value: (!_vm.isFirstMonth && !_vm.disabled),
      expression: "!isFirstMonth && !disabled"
    }],
    staticClass: "pagination-previous",
    attrs: {
      "role": "button",
      "href": "#",
      "disabled": _vm.disabled
    },
    on: {
      "click": function($event) {
        $event.preventDefault();
        _vm.decrementMonth($event)
      },
      "keydown": [function($event) {
        if (!('button' in $event) && _vm._k($event.keyCode, "enter", 13, $event.key)) { return null; }
        $event.preventDefault();
        _vm.decrementMonth($event)
      }, function($event) {
        if (!('button' in $event) && _vm._k($event.keyCode, "space", 32, $event.key)) { return null; }
        $event.preventDefault();
        _vm.decrementMonth($event)
      }]
    }
  }, [_c('b-icon', {
    attrs: {
      "icon": "chevron-left",
      "pack": _vm.iconPack,
      "both": "",
      "type": "is-primary is-clickable"
    }
  })], 1), _vm._v(" "), _c('a', {
    directives: [{
      name: "show",
      rawName: "v-show",
      value: (!_vm.isLastMonth && !_vm.disabled),
      expression: "!isLastMonth && !disabled"
    }],
    staticClass: "pagination-next",
    attrs: {
      "role": "button",
      "href": "#",
      "disabled": _vm.disabled
    },
    on: {
      "click": function($event) {
        $event.preventDefault();
        _vm.incrementMonth($event)
      },
      "keydown": [function($event) {
        if (!('button' in $event) && _vm._k($event.keyCode, "enter", 13, $event.key)) { return null; }
        $event.preventDefault();
        _vm.incrementMonth($event)
      }, function($event) {
        if (!('button' in $event) && _vm._k($event.keyCode, "space", 32, $event.key)) { return null; }
        $event.preventDefault();
        _vm.incrementMonth($event)
      }]
    }
  }, [_c('b-icon', {
    attrs: {
      "icon": "chevron-right",
      "pack": _vm.iconPack,
      "both": "",
      "type": "is-primary is-clickable"
    }
  })], 1), _vm._v(" "), _c('div', {
    staticClass: "pagination-list"
  }, [_c('b-field', [_c('b-select', {
    attrs: {
      "disabled": _vm.disabled,
      "size": _vm.size
    },
    model: {
      value: (_vm.focusedDateData.month),
      callback: function($$v) {
        _vm.$set(_vm.focusedDateData, "month", $$v)
      },
      expression: "focusedDateData.month"
    }
  }, _vm._l((_vm.monthNames), function(month, index) {
    return _c('option', {
      key: month,
      domProps: {
        "value": index
      }
    }, [_vm._v("\n                                    " + _vm._s(month) + "\n                                ")])
  })), _vm._v(" "), _c('b-select', {
    attrs: {
      "disabled": _vm.disabled,
      "size": _vm.size
    },
    model: {
      value: (_vm.focusedDateData.year),
      callback: function($$v) {
        _vm.$set(_vm.focusedDateData, "year", $$v)
      },
      expression: "focusedDateData.year"
    }
  }, _vm._l((_vm.listOfYears), function(year) {
    return _c('option', {
      key: year,
      domProps: {
        "value": year
      }
    }, [_vm._v("\n                                    " + _vm._s(year) + "\n                                ")])
  }))], 1)], 1)])], 2), _vm._v(" "), _c('div', {
    staticClass: "datepicker-content"
  }, [_c('b-datepicker-table', {
    attrs: {
      "day-names": _vm.dayNames,
      "month-names": _vm.monthNames,
      "first-day-of-week": _vm.firstDayOfWeek,
      "min-date": _vm.minDate,
      "max-date": _vm.maxDate,
      "focused": _vm.focusedDateData,
      "disabled": _vm.disabled,
      "unselectable-dates": _vm.unselectableDates,
      "unselectable-days-of-week": _vm.unselectableDaysOfWeek,
      "selectable-dates": _vm.selectableDates,
      "events": _vm.events,
      "indicators": _vm.indicators,
      "date-creator": _vm.dateCreator
    },
    on: {
      "close": function($event) {
        _vm.$refs.dropdown.isActive = false
      }
    },
    model: {
      value: (_vm.dateSelected),
      callback: function($$v) {
        _vm.dateSelected = $$v
      },
      expression: "dateSelected"
    }
  })], 1), _vm._v(" "), (_vm.$slots.default !== undefined && _vm.$slots.default.length) ? _c('footer', {
    staticClass: "datepicker-footer"
  }, [_vm._t("default")], 2) : _vm._e()])], 1) : _c('b-input', _vm._b({
    ref: "input",
    attrs: {
      "type": "date",
      "autocomplete": "off",
      "value": _vm.formatYYYYMMDD(_vm.value),
      "placeholder": _vm.placeholder,
      "size": _vm.size,
      "icon": _vm.icon,
      "icon-pack": _vm.iconPack,
      "loading": _vm.loading,
      "max": _vm.formatYYYYMMDD(_vm.maxDate),
      "min": _vm.formatYYYYMMDD(_vm.minDate),
      "disabled": _vm.disabled,
      "readonly": false
    },
    on: {
      "focus": function($event) {
        _vm.$emit('focus', $event)
      },
      "blur": function($event) {
        _vm.$emit('blur', $event) && _vm.checkHtml5Validity()
      }
    },
    nativeOn: {
      "change": function($event) {
        _vm.onChangeNativePicker($event)
      }
    }
  }, 'b-input', _vm.$attrs, false))], 1)
},staticRenderFns: []}

/***/ }),
/* 145 */
/***/ (function(module, exports, __webpack_require__) {

var Component = __webpack_require__(0)(
  /* script */
  __webpack_require__(146),
  /* template */
  __webpack_require__(149),
  /* styles */
  null,
  /* scopeId */
  null,
  /* moduleIdentifier (server only) */
  null
)

module.exports = Component.exports


/***/ }),
/* 146 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_defineProperty__ = __webpack_require__(1);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_defineProperty___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_defineProperty__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__icon_Icon__ = __webpack_require__(3);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__icon_Icon___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_1__icon_Icon__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__modal_Modal__ = __webpack_require__(63);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__modal_Modal___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_2__modal_Modal__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_3__utils_config__ = __webpack_require__(2);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_4__utils_helpers__ = __webpack_require__(7);

//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//






/* harmony default export */ __webpack_exports__["default"] = ({
    name: 'BDialog',
    components: __WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_defineProperty___default()({}, __WEBPACK_IMPORTED_MODULE_1__icon_Icon___default.a.name, __WEBPACK_IMPORTED_MODULE_1__icon_Icon___default.a),
    extends: __WEBPACK_IMPORTED_MODULE_2__modal_Modal___default.a,
    props: {
        title: String,
        message: String,
        icon: String,
        iconPack: String,
        hasIcon: Boolean,
        type: {
            type: String,
            default: 'is-primary'
        },
        size: String,
        confirmText: {
            type: String,
            default: function _default() {
                return __WEBPACK_IMPORTED_MODULE_3__utils_config__["a" /* default */].defaultDialogConfirmText ? __WEBPACK_IMPORTED_MODULE_3__utils_config__["a" /* default */].defaultDialogConfirmText : 'OK';
            }
        },
        cancelText: {
            type: String,
            default: function _default() {
                return __WEBPACK_IMPORTED_MODULE_3__utils_config__["a" /* default */].defaultDialogCancelText ? __WEBPACK_IMPORTED_MODULE_3__utils_config__["a" /* default */].defaultDialogCancelText : 'Cancel';
            }
        },
        hasInput: Boolean, // Used internally to know if it's prompt
        inputAttrs: {
            type: Object,
            default: function _default() {
                return {};
            }
        },
        onConfirm: {
            type: Function,
            default: function _default() {}
        },
        focusOn: {
            type: String,
            default: 'confirm'
        }
    },
    data: function data() {
        var prompt = this.hasInput ? this.inputAttrs.value || '' : '';

        return {
            prompt: prompt,
            isActive: false,
            validationMessage: ''
        };
    },

    computed: {
        /**
         * Icon name (MDI) based on the type.
         */
        iconByType: function iconByType() {
            switch (this.type) {
                case 'is-info':
                    return 'information';
                case 'is-success':
                    return 'check-circle';
                case 'is-warning':
                    return 'alert';
                case 'is-danger':
                    return 'alert-circle';
                default:
                    return null;
            }
        },
        showCancel: function showCancel() {
            return this.cancelOptions.indexOf('button') >= 0;
        }
    },
    methods: {
        /**
         * If it's a prompt Dialog, validate the input.
         * Call the onConfirm prop (function) and close the Dialog.
         */
        confirm: function confirm() {
            var _this = this;

            if (this.$refs.input !== undefined) {
                if (!this.$refs.input.checkValidity()) {
                    this.validationMessage = this.$refs.input.validationMessage;
                    this.$nextTick(function () {
                        return _this.$refs.input.select();
                    });
                    return;
                }
            }

            this.onConfirm(this.prompt);
            this.close();
        },


        /**
         * Close the Dialog.
         */
        close: function close() {
            var _this2 = this;

            this.isActive = false;
            // Timeout for the animation complete before destroying
            setTimeout(function () {
                _this2.$destroy();
                Object(__WEBPACK_IMPORTED_MODULE_4__utils_helpers__["d" /* removeElement */])(_this2.$el);
            }, 150);
        }
    },
    beforeMount: function beforeMount() {
        var _this3 = this;

        // Insert the Dialog component in body tag
        this.$nextTick(function () {
            document.body.appendChild(_this3.$el);
        });
    },
    mounted: function mounted() {
        var _this4 = this;

        this.isActive = true;

        if (typeof this.inputAttrs.required === 'undefined') {
            this.$set(this.inputAttrs, 'required', true);
        }

        this.$nextTick(function () {
            // Handle which element receives focus
            if (_this4.hasInput) {
                _this4.$refs.input.focus();
            } else if (_this4.focusOn === 'cancel' && _this4.showCancel) {
                _this4.$refs.cancelButton.focus();
            } else {
                _this4.$refs.confirmButton.focus();
            }
        });
    }
});

/***/ }),
/* 147 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__utils_helpers__ = __webpack_require__(7);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__utils_config__ = __webpack_require__(2);
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//




/* harmony default export */ __webpack_exports__["default"] = ({
    name: 'BModal',
    props: {
        active: Boolean,
        component: [Object, Function],
        content: String,
        programmatic: Boolean,
        props: Object,
        events: Object,
        width: {
            type: [String, Number],
            default: 960
        },
        hasModalCard: Boolean,
        animation: {
            type: String,
            default: 'zoom-out'
        },
        canCancel: {
            type: [Array, Boolean],
            default: function _default() {
                return __WEBPACK_IMPORTED_MODULE_1__utils_config__["a" /* default */].defaultModalCanCancel ? __WEBPACK_IMPORTED_MODULE_1__utils_config__["a" /* default */].defaultModalCanCancel : ['escape', 'x', 'outside', 'button'];
            }
        },
        onCancel: {
            type: Function,
            default: function _default() {}
        },
        scroll: {
            type: String,
            default: function _default() {
                return __WEBPACK_IMPORTED_MODULE_1__utils_config__["a" /* default */].defaultModalScroll ? __WEBPACK_IMPORTED_MODULE_1__utils_config__["a" /* default */].defaultModalScroll : 'clip';
            },
            validator: function validator(value) {
                return ['clip', 'keep'].indexOf(value) >= 0;
            }
        }
    },
    data: function data() {
        return {
            isActive: this.active || false,
            savedScrollTop: null,
            newWidth: typeof this.width === 'number' ? this.width + 'px' : this.width
        };
    },

    computed: {
        cancelOptions: function cancelOptions() {
            return typeof this.canCancel === 'boolean' ? this.canCancel ? ['escape', 'x', 'outside', 'button'] : [] : this.canCancel;
        },
        showX: function showX() {
            return this.cancelOptions.indexOf('x') >= 0;
        }
    },
    watch: {
        active: function active(value) {
            this.isActive = value;
        },
        isActive: function isActive() {
            this.handleScroll();
        }
    },
    methods: {
        handleScroll: function handleScroll() {
            if (typeof window === 'undefined') return;

            if (this.scroll === 'clip') {
                if (this.isActive) {
                    document.documentElement.classList.add('is-clipped');
                } else {
                    document.documentElement.classList.remove('is-clipped');
                }
                return;
            }

            this.savedScrollTop = !this.savedScrollTop ? document.documentElement.scrollTop : this.savedScrollTop;

            if (this.isActive) {
                document.body.classList.add('is-noscroll');
            } else {
                document.body.classList.remove('is-noscroll');
            }

            if (this.isActive) {
                document.body.style.top = '-' + this.savedScrollTop + 'px';
                return;
            }

            document.documentElement.scrollTop = this.savedScrollTop;
            document.body.style.top = null;
            this.savedScrollTop = null;
        },


        /**
         * Close the Modal if canCancel and call the onCancel prop (function).
         */
        cancel: function cancel(method) {
            if (this.cancelOptions.indexOf(method) < 0) return;

            this.onCancel.apply(null, arguments);
            this.close();
        },


        /**
         * Call the onCancel prop (function).
         * Emit events, and destroy modal if it's programmatic.
         */
        close: function close() {
            var _this = this;

            this.$emit('close');
            this.$emit('update:active', false);

            // Timeout for the animation complete before destroying
            if (this.programmatic) {
                this.isActive = false;
                setTimeout(function () {
                    _this.$destroy();
                    Object(__WEBPACK_IMPORTED_MODULE_0__utils_helpers__["d" /* removeElement */])(_this.$el);
                }, 150);
            }
        },


        /**
         * Keypress event that is bound to the document.
         */
        keyPress: function keyPress(event) {
            // Esc key
            if (this.isActive && event.keyCode === 27) this.cancel('escape');
        }
    },
    created: function created() {
        if (typeof window !== 'undefined') {
            document.addEventListener('keyup', this.keyPress);
        }
    },
    beforeMount: function beforeMount() {
        // Insert the Modal component in body tag
        // only if it's programmatic
        this.programmatic && document.body.appendChild(this.$el);
    },
    mounted: function mounted() {
        if (this.programmatic) this.isActive = true;else if (this.isActive) this.handleScroll();
    },
    beforeDestroy: function beforeDestroy() {
        if (typeof window !== 'undefined') {
            document.removeEventListener('keyup', this.keyPress);
            // reset scroll
            document.documentElement.classList.remove('is-clipped');
            var savedScrollTop = !this.savedScrollTop ? document.documentElement.scrollTop : this.savedScrollTop;
            document.body.classList.remove('is-noscroll');
            document.documentElement.scrollTop = savedScrollTop;
            document.body.style.top = null;
        }
    }
});

/***/ }),
/* 148 */
/***/ (function(module, exports) {

module.exports={render:function (){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;
  return _c('transition', {
    attrs: {
      "name": _vm.animation
    }
  }, [(_vm.isActive) ? _c('div', {
    staticClass: "modal is-active"
  }, [_c('div', {
    staticClass: "modal-background",
    on: {
      "click": function($event) {
        _vm.cancel('outside')
      }
    }
  }), _vm._v(" "), _c('div', {
    staticClass: "animation-content",
    class: {
      'modal-content': !_vm.hasModalCard
    },
    style: ({
      maxWidth: _vm.newWidth
    })
  }, [(_vm.component) ? _c(_vm.component, _vm._g(_vm._b({
    tag: "component",
    on: {
      "close": _vm.close
    }
  }, 'component', _vm.props, false), _vm.events)) : (_vm.content) ? _c('div', {
    domProps: {
      "innerHTML": _vm._s(_vm.content)
    }
  }) : _vm._t("default")], 2), _vm._v(" "), (_vm.showX) ? _c('button', {
    staticClass: "modal-close is-large",
    attrs: {
      "type": "button"
    },
    on: {
      "click": function($event) {
        _vm.cancel('x')
      }
    }
  }) : _vm._e()]) : _vm._e()])
},staticRenderFns: []}

/***/ }),
/* 149 */
/***/ (function(module, exports) {

module.exports={render:function (){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;
  return _c('transition', {
    attrs: {
      "name": _vm.animation
    }
  }, [(_vm.isActive) ? _c('div', {
    staticClass: "dialog modal is-active",
    class: _vm.size
  }, [_c('div', {
    staticClass: "modal-background",
    on: {
      "click": function($event) {
        _vm.cancel('outside')
      }
    }
  }), _vm._v(" "), _c('div', {
    staticClass: "modal-card animation-content"
  }, [(_vm.title) ? _c('header', {
    staticClass: "modal-card-head"
  }, [_c('p', {
    staticClass: "modal-card-title"
  }, [_vm._v(_vm._s(_vm.title))])]) : _vm._e(), _vm._v(" "), _c('section', {
    staticClass: "modal-card-body",
    class: {
      'is-titleless': !_vm.title, 'is-flex': _vm.hasIcon
    }
  }, [_c('div', {
    staticClass: "media"
  }, [(_vm.hasIcon) ? _c('div', {
    staticClass: "media-left"
  }, [_c('b-icon', {
    attrs: {
      "icon": _vm.icon ? _vm.icon : _vm.iconByType,
      "pack": _vm.iconPack,
      "type": _vm.type,
      "both": !_vm.icon,
      "size": "is-large"
    }
  })], 1) : _vm._e(), _vm._v(" "), _c('div', {
    staticClass: "media-content"
  }, [_c('p', {
    domProps: {
      "innerHTML": _vm._s(_vm.message)
    }
  }), _vm._v(" "), (_vm.hasInput) ? _c('div', {
    staticClass: "field"
  }, [_c('div', {
    staticClass: "control"
  }, [_c('input', _vm._b({
    directives: [{
      name: "model",
      rawName: "v-model",
      value: (_vm.prompt),
      expression: "prompt"
    }],
    ref: "input",
    staticClass: "input",
    class: {
      'is-danger': _vm.validationMessage
    },
    domProps: {
      "value": (_vm.prompt)
    },
    on: {
      "keyup": function($event) {
        if (!('button' in $event) && _vm._k($event.keyCode, "enter", 13, $event.key)) { return null; }
        _vm.confirm($event)
      },
      "input": function($event) {
        if ($event.target.composing) { return; }
        _vm.prompt = $event.target.value
      }
    }
  }, 'input', _vm.inputAttrs, false))]), _vm._v(" "), _c('p', {
    staticClass: "help is-danger"
  }, [_vm._v(_vm._s(_vm.validationMessage))])]) : _vm._e()])])]), _vm._v(" "), _c('footer', {
    staticClass: "modal-card-foot"
  }, [(_vm.showCancel) ? _c('button', {
    ref: "cancelButton",
    staticClass: "button",
    on: {
      "click": function($event) {
        _vm.cancel('button')
      }
    }
  }, [_vm._v("\n                    " + _vm._s(_vm.cancelText) + "\n                ")]) : _vm._e(), _vm._v(" "), _c('button', {
    ref: "confirmButton",
    staticClass: "button",
    class: _vm.type,
    on: {
      "click": _vm.confirm
    }
  }, [_vm._v("\n                    " + _vm._s(_vm.confirmText) + "\n                ")])])])]) : _vm._e()])
},staticRenderFns: []}

/***/ }),
/* 150 */
/***/ (function(module, exports, __webpack_require__) {

var Component = __webpack_require__(0)(
  /* script */
  __webpack_require__(151),
  /* template */
  __webpack_require__(152),
  /* styles */
  null,
  /* scopeId */
  null,
  /* moduleIdentifier (server only) */
  null
)

module.exports = Component.exports


/***/ }),
/* 151 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__utils_helpers__ = __webpack_require__(7);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__utils_ssr__ = __webpack_require__(64);
//
//
//
//
//
//
//
//
//
//
//
//




/* harmony default export */ __webpack_exports__["default"] = ({
    name: 'BLoading',
    props: {
        active: Boolean,
        programmatic: Boolean,
        container: [Object, Function, __WEBPACK_IMPORTED_MODULE_1__utils_ssr__["b" /* HTMLElement */]],
        isFullPage: {
            type: Boolean,
            default: true
        },
        animation: {
            type: String,
            default: 'fade'
        },
        canCancel: {
            type: Boolean,
            default: false
        },
        onCancel: {
            type: Function,
            default: function _default() {}
        }
    },
    data: function data() {
        return {
            isActive: this.active || false
        };
    },

    watch: {
        active: function active(value) {
            this.isActive = value;
        }
    },
    methods: {
        /**
         * Close the Modal if canCancel.
         */
        cancel: function cancel() {
            if (!this.canCancel || !this.isActive) return;

            this.close();
        },

        /**
         * Emit events, and destroy modal if it's programmatic.
         */
        close: function close() {
            var _this = this;

            this.onCancel.apply(null, arguments);
            this.$emit('close');
            this.$emit('update:active', false);

            // Timeout for the animation complete before destroying
            if (this.programmatic) {
                this.isActive = false;
                setTimeout(function () {
                    _this.$destroy();
                    Object(__WEBPACK_IMPORTED_MODULE_0__utils_helpers__["d" /* removeElement */])(_this.$el);
                }, 150);
            }
        },

        /**
         * Keypress event that is bound to the document.
         */
        keyPress: function keyPress(event) {
            // Esc key
            if (event.keyCode === 27) this.cancel();
        }
    },
    created: function created() {
        if (typeof window !== 'undefined') {
            document.addEventListener('keyup', this.keyPress);
        }
    },
    beforeMount: function beforeMount() {
        // Insert the Loading component in body tag
        // only if it's programmatic
        if (this.programmatic) {
            if (!this.container) {
                document.body.appendChild(this.$el);
            } else {
                this.isFullPage = false;
                this.container.appendChild(this.$el);
            }
        }
    },
    mounted: function mounted() {
        if (this.programmatic) this.isActive = true;
    },
    beforeDestroy: function beforeDestroy() {
        if (typeof window !== 'undefined') {
            document.removeEventListener('keyup', this.keyPress);
        }
    }
});

/***/ }),
/* 152 */
/***/ (function(module, exports) {

module.exports={render:function (){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;
  return _c('transition', {
    attrs: {
      "name": _vm.animation
    }
  }, [(_vm.isActive) ? _c('div', {
    staticClass: "loading-overlay is-active",
    class: {
      'is-full-page': _vm.isFullPage
    }
  }, [_c('div', {
    staticClass: "loading-background",
    on: {
      "click": _vm.cancel
    }
  }), _vm._v(" "), _c('div', {
    staticClass: "loading-icon"
  })]) : _vm._e()])
},staticRenderFns: []}

/***/ }),
/* 153 */
/***/ (function(module, exports, __webpack_require__) {

var Component = __webpack_require__(0)(
  /* script */
  __webpack_require__(154),
  /* template */
  __webpack_require__(155),
  /* styles */
  null,
  /* scopeId */
  null,
  /* moduleIdentifier (server only) */
  null
)

module.exports = Component.exports


/***/ }),
/* 154 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__utils_MessageMixin_js__ = __webpack_require__(65);
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//



/* harmony default export */ __webpack_exports__["default"] = ({
    name: 'BMessage',
    mixins: [__WEBPACK_IMPORTED_MODULE_0__utils_MessageMixin_js__["a" /* default */]],
    props: {
        ariaCloseLabel: String
    },
    data: function data() {
        return {
            newIconSize: this.iconSize || this.size || 'is-large'
        };
    }
});

/***/ }),
/* 155 */
/***/ (function(module, exports) {

module.exports={render:function (){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;
  return _c('transition', {
    attrs: {
      "name": "fade"
    }
  }, [(_vm.isActive) ? _c('article', {
    staticClass: "message",
    class: [_vm.type, _vm.size]
  }, [(_vm.title) ? _c('header', {
    staticClass: "message-header"
  }, [_c('p', [_vm._v(_vm._s(_vm.title))]), _vm._v(" "), (_vm.closable) ? _c('button', {
    staticClass: "delete",
    attrs: {
      "type": "button",
      "aria-label": _vm.ariaCloseLabel
    },
    on: {
      "click": _vm.close
    }
  }) : _vm._e()]) : _vm._e(), _vm._v(" "), _c('section', {
    staticClass: "message-body"
  }, [_c('div', {
    staticClass: "media"
  }, [(_vm.icon && _vm.hasIcon) ? _c('div', {
    staticClass: "media-left"
  }, [_c('b-icon', {
    class: _vm.type,
    attrs: {
      "icon": _vm.icon,
      "pack": _vm.iconPack,
      "both": "",
      "size": _vm.newIconSize
    }
  })], 1) : _vm._e(), _vm._v(" "), _c('div', {
    staticClass: "media-content"
  }, [_vm._t("default")], 2)])])]) : _vm._e()])
},staticRenderFns: []}

/***/ }),
/* 156 */
/***/ (function(module, exports, __webpack_require__) {

var Component = __webpack_require__(0)(
  /* script */
  __webpack_require__(157),
  /* template */
  __webpack_require__(158),
  /* styles */
  null,
  /* scopeId */
  null,
  /* moduleIdentifier (server only) */
  null
)

module.exports = Component.exports


/***/ }),
/* 157 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__utils_MessageMixin_js__ = __webpack_require__(65);
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//



/* harmony default export */ __webpack_exports__["default"] = ({
    name: 'BNotification',
    mixins: [__WEBPACK_IMPORTED_MODULE_0__utils_MessageMixin_js__["a" /* default */]],
    props: {
        position: String,
        ariaCloseLabel: String
    }
});

/***/ }),
/* 158 */
/***/ (function(module, exports) {

module.exports={render:function (){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;
  return _c('transition', {
    attrs: {
      "name": "fade"
    }
  }, [_c('article', {
    directives: [{
      name: "show",
      rawName: "v-show",
      value: (_vm.isActive),
      expression: "isActive"
    }],
    staticClass: "notification",
    class: [_vm.type, _vm.position]
  }, [(_vm.closable) ? _c('button', {
    staticClass: "delete",
    attrs: {
      "type": "button",
      "aria-label": _vm.ariaCloseLabel
    },
    on: {
      "click": _vm.close
    }
  }) : _vm._e(), _vm._v(" "), _c('div', {
    staticClass: "media"
  }, [(_vm.icon && _vm.hasIcon) ? _c('div', {
    staticClass: "media-left"
  }, [_c('b-icon', {
    attrs: {
      "icon": _vm.icon,
      "pack": _vm.iconPack,
      "both": "",
      "size": "is-large",
      "aria-hidden": ""
    }
  })], 1) : _vm._e(), _vm._v(" "), _c('div', {
    staticClass: "media-content"
  }, [(_vm.message) ? _c('p', {
    staticClass: "text"
  }, [_vm._v(_vm._s(_vm.message))]) : _vm._t("default")], 2)])])])
},staticRenderFns: []}

/***/ }),
/* 159 */
/***/ (function(module, exports, __webpack_require__) {

var Component = __webpack_require__(0)(
  /* script */
  __webpack_require__(160),
  /* template */
  __webpack_require__(161),
  /* styles */
  null,
  /* scopeId */
  null,
  /* moduleIdentifier (server only) */
  null
)

module.exports = Component.exports


/***/ }),
/* 160 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__utils_config__ = __webpack_require__(2);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__utils_NoticeMixin_js__ = __webpack_require__(45);
//
//
//
//
//
//




/* harmony default export */ __webpack_exports__["default"] = ({
    name: 'BNotificationNotice',
    mixins: [__WEBPACK_IMPORTED_MODULE_1__utils_NoticeMixin_js__["a" /* default */]],
    props: {
        indefinite: {
            type: Boolean,
            default: false
        }
    },
    data: function data() {
        return {
            newDuration: this.duration || __WEBPACK_IMPORTED_MODULE_0__utils_config__["a" /* default */].detaultNotificationDuration
        };
    }
});

/***/ }),
/* 161 */
/***/ (function(module, exports) {

module.exports={render:function (){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;
  return _c('b-notification', _vm._b({
    on: {
      "close": _vm.close
    }
  }, 'b-notification', _vm.$options.propsData, false))
},staticRenderFns: []}

/***/ }),
/* 162 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_defineProperty__ = __webpack_require__(1);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_defineProperty___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_defineProperty__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__icon_Icon__ = __webpack_require__(3);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__icon_Icon___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_1__icon_Icon__);

//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//



/* harmony default export */ __webpack_exports__["default"] = ({
    name: 'BPagination',
    components: __WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_defineProperty___default()({}, __WEBPACK_IMPORTED_MODULE_1__icon_Icon___default.a.name, __WEBPACK_IMPORTED_MODULE_1__icon_Icon___default.a),
    props: {
        total: [Number, String],
        perPage: {
            type: [Number, String],
            default: 20
        },
        current: {
            type: [Number, String],
            default: 1
        },
        size: String,
        simple: Boolean,
        rounded: Boolean,
        order: String,
        iconPack: String,
        ariaNextLabel: String,
        ariaPreviousLabel: String,
        ariaPageLabel: String,
        ariaCurrentLabel: String
    },
    computed: {
        rootClasses: function rootClasses() {
            return [this.order, this.size, {
                'is-simple': this.simple,
                'is-rounded': this.rounded
            }];
        },


        /**
         * Total page size (count).
         */
        pageCount: function pageCount() {
            return Math.ceil(this.total / this.perPage);
        },


        /**
         * First item of the page (count).
         */
        firstItem: function firstItem() {
            var firstItem = this.current * this.perPage - this.perPage + 1;
            return firstItem >= 0 ? firstItem : 0;
        },


        /**
         * Check if previous button is available.
         */
        hasPrev: function hasPrev() {
            return this.current > 1;
        },


        /**
         * Check if first page button should be visible.
         */
        hasFirst: function hasFirst() {
            return this.current >= 3;
        },


        /**
         * Check if first ellipsis should be visible.
         */
        hasFirstEllipsis: function hasFirstEllipsis() {
            return this.current >= 4;
        },


        /**
         * Check if last page button should be visible.
         */
        hasLast: function hasLast() {
            return this.current <= this.pageCount - 2;
        },


        /**
         * Check if last ellipsis should be visible.
         */
        hasLastEllipsis: function hasLastEllipsis() {
            return this.current < this.pageCount - 2 && this.current <= this.pageCount - 3;
        },


        /**
         * Check if next button is available.
         */
        hasNext: function hasNext() {
            return this.current < this.pageCount;
        },


        /**
         * Get near pages, 1 before and 1 after the current.
         * Also add the click event to the array.
         */
        pagesInRange: function pagesInRange() {
            var _this = this;

            if (this.simple) return;

            var left = Math.max(1, this.current - 1);
            var right = Math.min(this.current + 1, this.pageCount);

            var pages = [];

            var _loop = function _loop(i) {
                pages.push({
                    number: i,
                    isCurrent: _this.current === i,
                    click: function click(event) {
                        if (_this.current === i) return;
                        _this.$emit('change', i);
                        _this.$emit('update:current', i);

                        // Set focus on element to keep tab order
                        _this.$nextTick(function () {
                            return event.target.focus();
                        });
                    }
                });
            };

            for (var i = left; i <= right; i++) {
                _loop(i);
            }
            return pages;
        }
    },
    watch: {
        /**
         * If current page is trying to be greater than page count, set to last.
         */
        pageCount: function pageCount(value) {
            if (this.current > value) this.last();
        }
    },
    methods: {
        /**
         * Previous button click listener.
         */
        prev: function prev() {
            if (!this.hasPrev) return;
            this.$emit('change', this.current - 1);
            this.$emit('update:current', this.current - 1);
        },


        /**
         * First button click listener.
         */
        first: function first() {
            this.$emit('change', 1);
            this.$emit('update:current', 1);
        },


        /**
         * Last button click listener.
         */
        last: function last() {
            this.$emit('change', this.pageCount);
            this.$emit('update:current', this.pageCount);
        },


        /**
         * Next button click listener.
         */
        next: function next() {
            if (!this.hasNext) return;
            this.$emit('change', this.current + 1);
            this.$emit('update:current', this.current + 1);
        },


        /**
         * Get text for aria-label according to page number.
         */
        getAriaPageLabel: function getAriaPageLabel(pageNumber, isCurrent) {
            if (this.ariaPageLabel && (!isCurrent || !this.ariaCurrentLabel)) {
                return this.ariaPageLabel + ' ' + pageNumber + '.';
            } else if (this.ariaPageLabel && isCurrent && this.ariaCurrentLabel) {
                return this.ariaCurrentLabel + ', ' + this.ariaPageLabel + ' ' + pageNumber + '.';
            }
            return null;
        }
    }
});

/***/ }),
/* 163 */
/***/ (function(module, exports) {

module.exports={render:function (){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;
  return _c('nav', {
    staticClass: "pagination",
    class: _vm.rootClasses
  }, [_c('a', {
    staticClass: "pagination-previous",
    attrs: {
      "role": "button",
      "href": "#",
      "disabled": !_vm.hasPrev,
      "aria-label": _vm.ariaPreviousLabel
    },
    on: {
      "click": function($event) {
        $event.preventDefault();
        _vm.prev($event)
      }
    }
  }, [_c('b-icon', {
    attrs: {
      "icon": "chevron-left",
      "pack": _vm.iconPack,
      "both": "",
      "aria-hidden": ""
    }
  })], 1), _vm._v(" "), _c('a', {
    staticClass: "pagination-next",
    attrs: {
      "role": "button",
      "href": "#",
      "disabled": !_vm.hasNext,
      "aria-label": _vm.ariaNextLabel
    },
    on: {
      "click": function($event) {
        $event.preventDefault();
        _vm.next($event)
      }
    }
  }, [_c('b-icon', {
    attrs: {
      "icon": "chevron-right",
      "pack": _vm.iconPack,
      "both": "",
      "aria-hidden": ""
    }
  })], 1), _vm._v(" "), (!_vm.simple) ? _c('ul', {
    staticClass: "pagination-list"
  }, [(_vm.hasFirst) ? _c('li', [_c('a', {
    staticClass: "pagination-link",
    attrs: {
      "role": "button",
      "href": "#",
      "aria-label": _vm.getAriaPageLabel(1, false)
    },
    on: {
      "click": function($event) {
        $event.preventDefault();
        _vm.first($event)
      }
    }
  }, [_vm._v("\n                1\n            ")])]) : _vm._e(), _vm._v(" "), (_vm.hasFirstEllipsis) ? _c('li', [_c('span', {
    staticClass: "pagination-ellipsis"
  }, [_vm._v("…")])]) : _vm._e(), _vm._v(" "), _vm._l((_vm.pagesInRange), function(page) {
    return _c('li', {
      key: page.number
    }, [_c('a', {
      staticClass: "pagination-link",
      class: {
        'is-current': page.isCurrent
      },
      attrs: {
        "role": "button",
        "href": "#",
        "aria-label": _vm.getAriaPageLabel(page.number, page.isCurrent),
        "aria-current": page.isCurrent
      },
      on: {
        "click": function($event) {
          $event.preventDefault();
          page.click($event)
        }
      }
    }, [_vm._v("\n                " + _vm._s(page.number) + "\n            ")])])
  }), _vm._v(" "), (_vm.hasLastEllipsis) ? _c('li', [_c('span', {
    staticClass: "pagination-ellipsis"
  }, [_vm._v("…")])]) : _vm._e(), _vm._v(" "), (_vm.hasLast) ? _c('li', [_c('a', {
    staticClass: "pagination-link",
    attrs: {
      "role": "button",
      "href": "#",
      "aria-label": _vm.getAriaPageLabel(_vm.pageCount, false)
    },
    on: {
      "click": function($event) {
        $event.preventDefault();
        _vm.last($event)
      }
    }
  }, [_vm._v("\n                " + _vm._s(_vm.pageCount) + "\n            ")])]) : _vm._e()], 2) : _vm._e(), _vm._v(" "), (_vm.simple) ? _c('small', {
    staticClass: "info"
  }, [(_vm.perPage == 1) ? [_vm._v("\n            " + _vm._s(_vm.firstItem) + " / " + _vm._s(_vm.total) + "\n        ")] : [_vm._v("\n            " + _vm._s(_vm.firstItem) + "-" + _vm._s(Math.min(_vm.current * _vm.perPage, _vm.total)) + " / " + _vm._s(_vm.total) + "\n        ")]], 2) : _vm._e()])
},staticRenderFns: []}

/***/ }),
/* 164 */
/***/ (function(module, exports, __webpack_require__) {

var Component = __webpack_require__(0)(
  /* script */
  __webpack_require__(165),
  /* template */
  __webpack_require__(166),
  /* styles */
  null,
  /* scopeId */
  null,
  /* moduleIdentifier (server only) */
  null
)

module.exports = Component.exports


/***/ }),
/* 165 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_symbol__ = __webpack_require__(5);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_symbol___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_symbol__);

//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//

/* harmony default export */ __webpack_exports__["default"] = ({
    name: 'BRadio',
    props: {
        value: [String, Number, Boolean, Function, Object, Array, __WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_symbol___default.a],
        nativeValue: [String, Number, Boolean, Function, Object, Array, __WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_symbol___default.a],
        type: String,
        disabled: Boolean,
        required: Boolean,
        name: String,
        size: String
    },
    data: function data() {
        return {
            newValue: this.value
        };
    },

    computed: {
        computedValue: {
            get: function get() {
                return this.newValue;
            },
            set: function set(value) {
                this.newValue = value;
                this.$emit('input', value);
            }
        }
    },
    watch: {
        /**
         * When v-model change, set internal value.
         */
        value: function value(_value) {
            this.newValue = _value;
        }
    }
});

/***/ }),
/* 166 */
/***/ (function(module, exports) {

module.exports={render:function (){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;
  return _c('label', {
    ref: "label",
    staticClass: "b-radio radio",
    class: [_vm.size, {
      'is-disabled': _vm.disabled
    }],
    attrs: {
      "disabled": _vm.disabled,
      "tabindex": _vm.disabled ? false : 0
    },
    on: {
      "keydown": function($event) {
        if (!('button' in $event) && _vm._k($event.keyCode, "enter", 13, $event.key) && _vm._k($event.keyCode, "space", 32, $event.key)) { return null; }
        $event.preventDefault();
        _vm.$refs.label.click()
      }
    }
  }, [_c('input', {
    directives: [{
      name: "model",
      rawName: "v-model",
      value: (_vm.computedValue),
      expression: "computedValue"
    }],
    attrs: {
      "tabindex": "-1",
      "type": "radio",
      "disabled": _vm.disabled,
      "required": _vm.required,
      "name": _vm.name
    },
    domProps: {
      "value": _vm.nativeValue,
      "checked": _vm._q(_vm.computedValue, _vm.nativeValue)
    },
    on: {
      "click": function($event) {
        $event.stopPropagation();
      },
      "change": function($event) {
        _vm.computedValue = _vm.nativeValue
      }
    }
  }), _vm._v(" "), _c('span', {
    staticClass: "check",
    class: _vm.type
  }), _vm._v(" "), _c('span', {
    staticClass: "control-label"
  }, [_vm._t("default")], 2)])
},staticRenderFns: []}

/***/ }),
/* 167 */
/***/ (function(module, exports, __webpack_require__) {

var Component = __webpack_require__(0)(
  /* script */
  __webpack_require__(168),
  /* template */
  __webpack_require__(169),
  /* styles */
  null,
  /* scopeId */
  null,
  /* moduleIdentifier (server only) */
  null
)

module.exports = Component.exports


/***/ }),
/* 168 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_symbol__ = __webpack_require__(5);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_symbol___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_symbol__);

//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//

/* harmony default export */ __webpack_exports__["default"] = ({
    name: 'BRadioButton',
    props: {
        value: [String, Number, Boolean, Function, Object, Array, __WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_symbol___default.a],
        nativeValue: [String, Number, Boolean, Function, Object, Array, __WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_symbol___default.a],
        type: {
            type: String,
            default: 'is-primary'
        },
        disabled: Boolean,
        required: Boolean,
        name: String,
        size: String
    },
    data: function data() {
        return {
            newValue: this.value
        };
    },

    computed: {
        computedValue: {
            get: function get() {
                return this.newValue;
            },
            set: function set(value) {
                this.newValue = value;
                this.$emit('input', value);
            }
        }
    },
    watch: {
        /**
         * When v-model change, set internal value.
         */
        value: function value(_value) {
            this.newValue = _value;
        }
    }
});

/***/ }),
/* 169 */
/***/ (function(module, exports) {

module.exports={render:function (){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;
  return _c('div', {
    staticClass: "control"
  }, [_c('label', {
    ref: "label",
    staticClass: "b-radio radio button",
    class: [_vm.newValue === _vm.nativeValue ? _vm.type : null, _vm.size],
    attrs: {
      "disabled": _vm.disabled,
      "tabindex": _vm.disabled ? false : 0
    },
    on: {
      "keydown": function($event) {
        if (!('button' in $event) && _vm._k($event.keyCode, "enter", 13, $event.key) && _vm._k($event.keyCode, "space", 32, $event.key)) { return null; }
        $event.preventDefault();
        _vm.$refs.label.click()
      }
    }
  }, [_vm._t("default"), _vm._v(" "), _c('input', {
    directives: [{
      name: "model",
      rawName: "v-model",
      value: (_vm.computedValue),
      expression: "computedValue"
    }],
    attrs: {
      "tabindex": "-1",
      "type": "radio",
      "disabled": _vm.disabled,
      "required": _vm.required,
      "name": _vm.name
    },
    domProps: {
      "value": _vm.nativeValue,
      "checked": _vm._q(_vm.computedValue, _vm.nativeValue)
    },
    on: {
      "click": function($event) {
        $event.stopPropagation();
      },
      "change": function($event) {
        _vm.computedValue = _vm.nativeValue
      }
    }
  })], 2)])
},staticRenderFns: []}

/***/ }),
/* 170 */
/***/ (function(module, exports, __webpack_require__) {

var Component = __webpack_require__(0)(
  /* script */
  __webpack_require__(171),
  /* template */
  __webpack_require__(172),
  /* styles */
  null,
  /* scopeId */
  null,
  /* moduleIdentifier (server only) */
  null
)

module.exports = Component.exports


/***/ }),
/* 171 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__utils_config__ = __webpack_require__(2);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__utils_NoticeMixin_js__ = __webpack_require__(45);
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//




/* harmony default export */ __webpack_exports__["default"] = ({
    name: 'BSnackbar',
    mixins: [__WEBPACK_IMPORTED_MODULE_1__utils_NoticeMixin_js__["a" /* default */]],
    props: {
        actionText: {
            type: String,
            default: 'OK'
        },
        onAction: {
            type: Function,
            default: function _default() {}
        },
        indefinite: {
            type: Boolean,
            default: false
        }
    },
    data: function data() {
        return {
            newDuration: this.duration || __WEBPACK_IMPORTED_MODULE_0__utils_config__["a" /* default */].defaultSnackbarDuration
        };
    },

    methods: {
        /**
         * Click listener.
         * Call action prop before closing (from Mixin).
         */
        action: function action() {
            this.onAction();
            this.close();
        }
    }
});

/***/ }),
/* 172 */
/***/ (function(module, exports) {

module.exports={render:function (){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;
  return _c('transition', {
    attrs: {
      "enter-active-class": _vm.transition.enter,
      "leave-active-class": _vm.transition.leave
    }
  }, [_c('div', {
    directives: [{
      name: "show",
      rawName: "v-show",
      value: (_vm.isActive),
      expression: "isActive"
    }],
    staticClass: "snackbar",
    class: [_vm.type, _vm.position]
  }, [_c('p', {
    staticClass: "text"
  }, [_vm._v(_vm._s(_vm.message))]), _vm._v(" "), (_vm.actionText) ? _c('div', {
    staticClass: "action",
    class: _vm.type,
    on: {
      "click": _vm.action
    }
  }, [_c('button', {
    staticClass: "button"
  }, [_vm._v(_vm._s(_vm.actionText))])]) : _vm._e()])])
},staticRenderFns: []}

/***/ }),
/* 173 */
/***/ (function(module, exports, __webpack_require__) {

var Component = __webpack_require__(0)(
  /* script */
  __webpack_require__(174),
  /* template */
  __webpack_require__(175),
  /* styles */
  null,
  /* scopeId */
  null,
  /* moduleIdentifier (server only) */
  null
)

module.exports = Component.exports


/***/ }),
/* 174 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_symbol__ = __webpack_require__(5);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_symbol___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_symbol__);

//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//

/* harmony default export */ __webpack_exports__["default"] = ({
    name: 'BSwitch',
    props: {
        value: [String, Number, Boolean, Function, Object, Array, __WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_symbol___default.a],
        nativeValue: [String, Number, Boolean, Function, Object, Array, __WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_symbol___default.a],
        disabled: Boolean,
        type: String,
        name: String,
        required: Boolean,
        size: String,
        trueValue: {
            type: [String, Number, Boolean, Function, Object, Array, __WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_symbol___default.a],
            default: true
        },
        falseValue: {
            type: [String, Number, Boolean, Function, Object, Array, __WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_symbol___default.a],
            default: false
        }
    },
    data: function data() {
        return {
            newValue: this.value,
            isMouseDown: false
        };
    },

    computed: {
        computedValue: {
            get: function get() {
                return this.newValue;
            },
            set: function set(value) {
                this.newValue = value;
                this.$emit('input', value);
            }
        }
    },
    watch: {
        /**
         * When v-model change, set internal value.
         */
        value: function value(_value) {
            this.newValue = _value;
        }
    }
});

/***/ }),
/* 175 */
/***/ (function(module, exports) {

module.exports={render:function (){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;
  return _c('label', {
    ref: "label",
    staticClass: "switch",
    class: [_vm.size, {
      'is-disabled': _vm.disabled
    }],
    attrs: {
      "disabled": _vm.disabled,
      "tabindex": _vm.disabled ? false : 0
    },
    on: {
      "keydown": function($event) {
        if (!('button' in $event) && _vm._k($event.keyCode, "enter", 13, $event.key) && _vm._k($event.keyCode, "space", 32, $event.key)) { return null; }
        $event.preventDefault();
        _vm.$refs.label.click()
      },
      "mousedown": function($event) {
        _vm.isMouseDown = true
      },
      "mouseup": function($event) {
        _vm.isMouseDown = false
      },
      "mouseout": function($event) {
        _vm.isMouseDown = false
      },
      "blur": function($event) {
        _vm.isMouseDown = false
      }
    }
  }, [_c('input', {
    directives: [{
      name: "model",
      rawName: "v-model",
      value: (_vm.computedValue),
      expression: "computedValue"
    }],
    attrs: {
      "type": "checkbox",
      "tabindex": "-1",
      "disabled": _vm.disabled,
      "name": _vm.name,
      "required": _vm.required,
      "true-value": _vm.trueValue,
      "false-value": _vm.falseValue
    },
    domProps: {
      "value": _vm.nativeValue,
      "checked": Array.isArray(_vm.computedValue) ? _vm._i(_vm.computedValue, _vm.nativeValue) > -1 : _vm._q(_vm.computedValue, _vm.trueValue)
    },
    on: {
      "click": function($event) {
        $event.stopPropagation();
      },
      "change": function($event) {
        var $$a = _vm.computedValue,
          $$el = $event.target,
          $$c = $$el.checked ? (_vm.trueValue) : (_vm.falseValue);
        if (Array.isArray($$a)) {
          var $$v = _vm.nativeValue,
            $$i = _vm._i($$a, $$v);
          if ($$el.checked) {
            $$i < 0 && (_vm.computedValue = $$a.concat([$$v]))
          } else {
            $$i > -1 && (_vm.computedValue = $$a.slice(0, $$i).concat($$a.slice($$i + 1)))
          }
        } else {
          _vm.computedValue = $$c
        }
      }
    }
  }), _vm._v(" "), _c('span', {
    staticClass: "check",
    class: [{
      'is-elastic': _vm.isMouseDown && !_vm.disabled
    }, _vm.type]
  }), _vm._v(" "), _c('span', {
    staticClass: "control-label"
  }, [_vm._t("default")], 2)])
},staticRenderFns: []}

/***/ }),
/* 176 */
/***/ (function(module, exports, __webpack_require__) {

var Component = __webpack_require__(0)(
  /* script */
  __webpack_require__(177),
  /* template */
  __webpack_require__(191),
  /* styles */
  null,
  /* scopeId */
  null,
  /* moduleIdentifier (server only) */
  null
)

module.exports = Component.exports


/***/ }),
/* 177 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_toConsumableArray__ = __webpack_require__(178);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_toConsumableArray___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_toConsumableArray__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_babel_runtime_helpers_defineProperty__ = __webpack_require__(1);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_babel_runtime_helpers_defineProperty___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_1_babel_runtime_helpers_defineProperty__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__utils_helpers__ = __webpack_require__(7);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_3__checkbox_Checkbox__ = __webpack_require__(61);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_3__checkbox_Checkbox___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_3__checkbox_Checkbox__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_4__icon_Icon__ = __webpack_require__(3);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_4__icon_Icon___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_4__icon_Icon__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_5__pagination_Pagination__ = __webpack_require__(66);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_5__pagination_Pagination___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_5__pagination_Pagination__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_6__TableMobileSort__ = __webpack_require__(186);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_6__TableMobileSort___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_6__TableMobileSort__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_7__TableColumn__ = __webpack_require__(67);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_7__TableColumn___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_7__TableColumn__);



var _components;

//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//










/* harmony default export */ __webpack_exports__["default"] = ({
    name: 'BTable',
    components: (_components = {}, __WEBPACK_IMPORTED_MODULE_1_babel_runtime_helpers_defineProperty___default()(_components, __WEBPACK_IMPORTED_MODULE_3__checkbox_Checkbox___default.a.name, __WEBPACK_IMPORTED_MODULE_3__checkbox_Checkbox___default.a), __WEBPACK_IMPORTED_MODULE_1_babel_runtime_helpers_defineProperty___default()(_components, __WEBPACK_IMPORTED_MODULE_4__icon_Icon___default.a.name, __WEBPACK_IMPORTED_MODULE_4__icon_Icon___default.a), __WEBPACK_IMPORTED_MODULE_1_babel_runtime_helpers_defineProperty___default()(_components, __WEBPACK_IMPORTED_MODULE_5__pagination_Pagination___default.a.name, __WEBPACK_IMPORTED_MODULE_5__pagination_Pagination___default.a), __WEBPACK_IMPORTED_MODULE_1_babel_runtime_helpers_defineProperty___default()(_components, __WEBPACK_IMPORTED_MODULE_6__TableMobileSort___default.a.name, __WEBPACK_IMPORTED_MODULE_6__TableMobileSort___default.a), __WEBPACK_IMPORTED_MODULE_1_babel_runtime_helpers_defineProperty___default()(_components, __WEBPACK_IMPORTED_MODULE_7__TableColumn___default.a.name, __WEBPACK_IMPORTED_MODULE_7__TableColumn___default.a), _components),
    props: {
        data: {
            type: Array,
            default: function _default() {
                return [];
            }
        },
        columns: {
            type: Array,
            default: function _default() {
                return [];
            }
        },
        bordered: Boolean,
        striped: Boolean,
        narrowed: Boolean,
        hoverable: Boolean,
        loading: Boolean,
        detailed: Boolean,
        checkable: Boolean,
        selected: Object,
        focusable: Boolean,
        customIsChecked: Function,
        isRowCheckable: {
            type: Function,
            default: function _default() {
                return true;
            }
        },
        checkedRows: {
            type: Array,
            default: function _default() {
                return [];
            }
        },
        mobileCards: {
            type: Boolean,
            default: true
        },
        defaultSort: [String, Array],
        defaultSortDirection: {
            type: String,
            default: 'asc'
        },
        paginated: Boolean,
        currentPage: {
            type: Number,
            default: 1
        },
        perPage: {
            type: [Number, String],
            default: 20
        },
        showDetailIcon: {
            type: Boolean,
            default: true
        },
        paginationSimple: Boolean,
        paginationSize: String,
        backendSorting: Boolean,
        rowClass: {
            type: Function,
            default: function _default() {
                return '';
            }
        },
        openedDetailed: {
            type: Array,
            default: function _default() {
                return [];
            }
        },
        hasDetailedVisible: {
            type: Function,
            default: function _default() {
                return true;
            }
        },
        detailKey: {
            type: String,
            default: ''
        },
        backendPagination: Boolean,
        total: {
            type: [Number, String],
            default: 0
        },
        iconPack: String,
        mobileSortPlaceholder: String,
        customRowKey: String,
        draggable: {
            type: Boolean,
            defualt: false
        },
        ariaNextLabel: String,
        ariaPreviousLabel: String,
        ariaPageLabel: String,
        ariaCurrentLabel: String
    },
    data: function data() {
        return {
            getValueByPath: __WEBPACK_IMPORTED_MODULE_2__utils_helpers__["a" /* getValueByPath */],
            newColumns: [].concat(__WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_toConsumableArray___default()(this.columns)),
            visibleDetailRows: this.openedDetailed,
            newData: this.data,
            newDataTotal: this.backendPagination ? this.total : this.data.length,
            newCheckedRows: [].concat(__WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_toConsumableArray___default()(this.checkedRows)),
            newCurrentPage: this.currentPage,
            currentSortColumn: {},
            isAsc: true,
            firstTimeSort: true, // Used by first time initSort
            _isTable: true // Used by TableColumn
        };
    },

    computed: {
        /**
         * return if detailed row tabled
         * will be with chevron column & icon or not
         */
        showDetailRowIcon: function showDetailRowIcon() {
            return this.detailed && this.showDetailIcon;
        },
        tableClasses: function tableClasses() {
            return {
                'is-bordered': this.bordered,
                'is-striped': this.striped,
                'is-narrow': this.narrowed,
                'has-mobile-cards': this.mobileCards,
                'is-hoverable': (this.hoverable || this.focusable) && this.visibleData.length
            };
        },


        /**
         * Splitted data based on the pagination.
         */
        visibleData: function visibleData() {
            if (!this.paginated) return this.newData;

            var currentPage = this.newCurrentPage;
            var perPage = this.perPage;

            if (this.newData.length <= perPage) {
                return this.newData;
            } else {
                var start = (currentPage - 1) * perPage;
                var end = parseInt(start, 10) + parseInt(perPage, 10);
                return this.newData.slice(start, end);
            }
        },
        visibleColumns: function visibleColumns() {
            if (!this.newColumns) return this.newColumns;
            return this.newColumns.filter(function (column) {
                return column.visible || column.visible === undefined;
            });
        },


        /**
         * Check if all rows in the page are checked.
         */
        isAllChecked: function isAllChecked() {
            var _this = this;

            var validVisibleData = this.visibleData.filter(function (row) {
                return _this.isRowCheckable(row);
            });
            if (validVisibleData.length === 0) return false;
            var isAllChecked = validVisibleData.some(function (currentVisibleRow) {
                return Object(__WEBPACK_IMPORTED_MODULE_2__utils_helpers__["b" /* indexOf */])(_this.newCheckedRows, currentVisibleRow, _this.customIsChecked) < 0;
            });
            return !isAllChecked;
        },


        /**
         * Check if all rows in the page are checkable.
         */
        isAllUncheckable: function isAllUncheckable() {
            var _this2 = this;

            var validVisibleData = this.visibleData.filter(function (row) {
                return _this2.isRowCheckable(row);
            });
            return validVisibleData.length === 0;
        },


        /**
         * Check if has any sortable column.
         */
        hasSortablenewColumns: function hasSortablenewColumns() {
            return this.newColumns.some(function (column) {
                return column.sortable;
            });
        },


        /**
         * Return total column count based if it's checkable or expanded
         */
        columnCount: function columnCount() {
            var count = this.newColumns.length;
            count += this.checkable ? 1 : 0;
            count += this.detailed ? 1 : 0;

            return count;
        }
    },
    watch: {
        /**
         * When data prop change:
         *   1. Update internal value.
         *   2. Reset newColumns (thead), in case it's on a v-for loop.
         *   3. Sort again if it's not backend-sort.
         *   4. Set new total if it's not backend-paginated.
         */
        data: function data(value) {
            var _this3 = this;

            // Save newColumns before resetting
            var newColumns = this.newColumns;

            this.newColumns = [];
            this.newData = value;

            // Prevent table from being headless, data could change and created hook
            // on column might not trigger
            this.$nextTick(function () {
                if (!_this3.newColumns.length) _this3.newColumns = newColumns;
            });

            if (!this.backendSorting) {
                this.sort(this.currentSortColumn, true);
            }
            if (!this.backendPagination) {
                this.newDataTotal = value.length;
            }
        },


        /**
         * When Pagination total change, update internal total
         * only if it's backend-paginated.
         */
        total: function total(newTotal) {
            if (!this.backendPagination) return;

            this.newDataTotal = newTotal;
        },


        /**
         * When checkedRows prop change, update internal value without
         * mutating original data.
         */
        checkedRows: function checkedRows(rows) {
            this.newCheckedRows = [].concat(__WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_toConsumableArray___default()(rows));
        },
        columns: function columns(value) {
            this.newColumns = [].concat(__WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_toConsumableArray___default()(value));
        },
        newColumns: function newColumns(value) {
            this.checkSort();
        },


        /**
        * When the user wants to control the detailed rows via props.
        * Or wants to open the details of certain row with the router for example.
        */
        openedDetailed: function openedDetailed(expandedRows) {
            this.visibleDetailRows = expandedRows;
        },
        currentPage: function currentPage(newVal) {
            this.newCurrentPage = newVal;
        }
    },
    methods: {
        /**
         * Sort an array by key without mutating original data.
         * Call the user sort function if it was passed.
         */
        sortBy: function sortBy(array, key, fn, isAsc) {
            var sorted = [];
            // Sorting without mutating original data
            if (fn && typeof fn === 'function') {
                sorted = [].concat(__WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_toConsumableArray___default()(array)).sort(function (a, b) {
                    return fn(a, b, isAsc);
                });
            } else {
                sorted = [].concat(__WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_toConsumableArray___default()(array)).sort(function (a, b) {
                    // Get nested values from objects
                    var newA = Object(__WEBPACK_IMPORTED_MODULE_2__utils_helpers__["a" /* getValueByPath */])(a, key);
                    var newB = Object(__WEBPACK_IMPORTED_MODULE_2__utils_helpers__["a" /* getValueByPath */])(b, key);

                    // sort boolean type
                    if (typeof newA === 'boolean' && typeof newB === 'boolean') {
                        return isAsc ? newA - newB : newB - newA;
                    }

                    if (!newA && newA !== 0) return 1;
                    if (!newB && newB !== 0) return -1;
                    if (newA === newB) return 0;

                    newA = typeof newA === 'string' ? newA.toUpperCase() : newA;
                    newB = typeof newB === 'string' ? newB.toUpperCase() : newB;

                    return isAsc ? newA > newB ? 1 : -1 : newA > newB ? -1 : 1;
                });
            }

            return sorted;
        },


        /**
         * Sort the column.
         * Toggle current direction on column if it's sortable
         * and not just updating the prop.
         */
        sort: function sort(column) {
            var updatingData = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;

            if (!column || !column.sortable) return;

            if (!updatingData) {
                this.isAsc = column === this.currentSortColumn ? !this.isAsc : this.defaultSortDirection.toLowerCase() !== 'desc';
            }
            if (!this.firstTimeSort) {
                this.$emit('sort', column.field, this.isAsc ? 'asc' : 'desc');
            }
            if (!this.backendSorting) {
                this.newData = this.sortBy(this.newData, column.field, column.customSort, this.isAsc);
            }
            this.currentSortColumn = column;
        },


        /**
         * Check if the row is checked (is added to the array).
         */
        isRowChecked: function isRowChecked(row) {
            return Object(__WEBPACK_IMPORTED_MODULE_2__utils_helpers__["b" /* indexOf */])(this.newCheckedRows, row, this.customIsChecked) >= 0;
        },


        /**
         * Remove a checked row from the array.
         */
        removeCheckedRow: function removeCheckedRow(row) {
            var index = Object(__WEBPACK_IMPORTED_MODULE_2__utils_helpers__["b" /* indexOf */])(this.newCheckedRows, row, this.customIsChecked);
            if (index >= 0) {
                this.newCheckedRows.splice(index, 1);
            }
        },


        /**
         * Header checkbox click listener.
         * Add or remove all rows in current page.
         */
        checkAll: function checkAll() {
            var _this4 = this;

            var isAllChecked = this.isAllChecked;
            this.visibleData.forEach(function (currentRow) {
                _this4.removeCheckedRow(currentRow);
                if (!isAllChecked) {
                    if (_this4.isRowCheckable(currentRow)) {
                        _this4.newCheckedRows.push(currentRow);
                    }
                }
            });

            this.$emit('check', this.newCheckedRows);
            this.$emit('check-all', this.newCheckedRows);

            // Emit checked rows to update user variable
            this.$emit('update:checkedRows', this.newCheckedRows);
        },


        /**
         * Row checkbox click listener.
         * Add or remove a single row.
         */
        checkRow: function checkRow(row) {
            if (!this.isRowChecked(row)) {
                this.newCheckedRows.push(row);
            } else {
                this.removeCheckedRow(row);
            }

            this.$emit('check', this.newCheckedRows, row);

            // Emit checked rows to update user variable
            this.$emit('update:checkedRows', this.newCheckedRows);
        },


        /**
         * Row click listener.
         * Emit all necessary events.
         */
        selectRow: function selectRow(row, index) {
            this.$emit('click', row);

            if (this.selected === row) return;

            // Emit new and old row
            this.$emit('select', row, this.selected);

            // Emit new row to update user variable
            this.$emit('update:selected', row);
        },


        /**
         * Paginator change listener.
         */
        pageChanged: function pageChanged(page) {
            this.newCurrentPage = page > 0 ? page : 1;
            this.$emit('page-change', this.newCurrentPage);
            this.$emit('update:currentPage', this.newCurrentPage);
        },


        /**
         * Toggle to show/hide details slot
         */
        toggleDetails: function toggleDetails(obj) {
            var found = this.isVisibleDetailRow(obj);

            if (found) {
                this.closeDetailRow(obj);
                this.$emit('details-close', obj);
            } else {
                this.openDetailRow(obj);
                this.$emit('details-open', obj);
            }

            // Syncs the detailed rows with the parent component
            this.$emit('update:openedDetailed', this.visibleDetailRows);
        },
        openDetailRow: function openDetailRow(obj) {
            var index = this.handleDetailKey(obj);
            this.visibleDetailRows.push(index);
        },
        closeDetailRow: function closeDetailRow(obj) {
            var index = this.handleDetailKey(obj);
            var i = this.visibleDetailRows.indexOf(index);
            this.visibleDetailRows.splice(i, 1);
        },
        isVisibleDetailRow: function isVisibleDetailRow(obj) {
            var index = this.handleDetailKey(obj);
            var result = this.visibleDetailRows.indexOf(index) >= 0;
            return result;
        },


        /**
        * When the detailKey is defined we use the object[detailKey] as index.
        * If not, use the object reference by default.
        */
        handleDetailKey: function handleDetailKey(index) {
            var key = this.detailKey;
            return !key.length ? index : index[key];
        },
        checkPredefinedDetailedRows: function checkPredefinedDetailedRows() {
            var defaultExpandedRowsDefined = this.openedDetailed.length > 0;
            if (defaultExpandedRowsDefined && !this.detailKey.length) {
                throw new Error('If you set a predefined opened-detailed, you must provide a unique key using the prop "detail-key"');
            }
        },


        /**
         * Call initSort only first time (For example async data).
         */
        checkSort: function checkSort() {
            if (this.newColumns.length && this.firstTimeSort) {
                this.initSort();
                this.firstTimeSort = false;
            } else if (this.newColumns.length) {
                if (this.currentSortColumn.field) {
                    for (var i = 0; i < this.newColumns.length; i++) {
                        if (this.newColumns[i].field === this.currentSortColumn.field) {
                            this.currentSortColumn = this.newColumns[i];
                            break;
                        }
                    }
                }
            }
        },


        /**
         * Check if footer slot has custom content.
         */
        hasCustomFooterSlot: function hasCustomFooterSlot() {
            if (this.$slots.footer.length > 1) return true;

            var tag = this.$slots.footer[0].tag;
            if (tag !== 'th' && tag !== 'td') return false;

            return true;
        },


        /**
         * Check if bottom-left slot exists.
         */
        hasBottomLeftSlot: function hasBottomLeftSlot() {
            return typeof this.$slots['bottom-left'] !== 'undefined';
        },


        /**
         * Table arrow keys listener, change selection.
         */
        pressedArrow: function pressedArrow(pos) {
            if (!this.visibleData.length) return;

            var index = this.visibleData.indexOf(this.selected) + pos;

            // Prevent from going up from first and down from last
            index = index < 0 ? 0 : index > this.visibleData.length - 1 ? this.visibleData.length - 1 : index;

            this.selectRow(this.visibleData[index]);
        },


        /**
         * Focus table element if has selected prop.
         */
        focus: function focus() {
            if (!this.focusable) return;

            this.$el.querySelector('table').focus();
        },


        /**
         * Initial sorted column based on the default-sort prop.
         */
        initSort: function initSort() {
            var _this5 = this;

            if (!this.defaultSort) return;

            var sortField = '';
            var sortDirection = this.defaultSortDirection;

            if (Array.isArray(this.defaultSort)) {
                sortField = this.defaultSort[0];
                if (this.defaultSort[1]) {
                    sortDirection = this.defaultSort[1];
                }
            } else {
                sortField = this.defaultSort;
            }

            this.newColumns.forEach(function (column) {
                if (column.field === sortField) {
                    _this5.isAsc = sortDirection.toLowerCase() !== 'desc';
                    _this5.sort(column, true);
                }
            });
        },

        /**
         * Emits drag start event
         */
        handleDragStart: function handleDragStart(event, row, index) {
            this.$emit('dragstart', { event: event, row: row, index: index });
        },

        /**
         * Emits drop event
         */
        handleDrop: function handleDrop(event, row, index) {
            this.$emit('drop', { event: event, row: row, index: index });
        },

        /**
         * Emits drag over event
         */
        handleDragOver: function handleDragOver(event, row, index) {
            this.$emit('dragover', { event: event, row: row, index: index });
        },

        /**
         * Emits drag leave event
         */
        handleDragLeave: function handleDragLeave(event, row, index) {
            this.$emit('dragleave', { event: event, row: row, index: index });
        }
    },

    mounted: function mounted() {
        this.checkPredefinedDetailedRows();
        this.checkSort();
    }
});

/***/ }),
/* 178 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";


exports.__esModule = true;

var _from = __webpack_require__(179);

var _from2 = _interopRequireDefault(_from);

function _interopRequireDefault(obj) { return obj && obj.__esModule ? obj : { default: obj }; }

exports.default = function (arr) {
  if (Array.isArray(arr)) {
    for (var i = 0, arr2 = Array(arr.length); i < arr.length; i++) {
      arr2[i] = arr[i];
    }

    return arr2;
  } else {
    return (0, _from2.default)(arr);
  }
};

/***/ }),
/* 179 */
/***/ (function(module, exports, __webpack_require__) {

module.exports = { "default": __webpack_require__(180), __esModule: true };

/***/ }),
/* 180 */
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__(41);
__webpack_require__(181);
module.exports = __webpack_require__(6).Array.from;


/***/ }),
/* 181 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

var ctx = __webpack_require__(46);
var $export = __webpack_require__(18);
var toObject = __webpack_require__(40);
var call = __webpack_require__(182);
var isArrayIter = __webpack_require__(183);
var toLength = __webpack_require__(51);
var createProperty = __webpack_require__(184);
var getIterFn = __webpack_require__(60);

$export($export.S + $export.F * !__webpack_require__(185)(function (iter) { Array.from(iter); }), 'Array', {
  // 22.1.2.1 Array.from(arrayLike, mapfn = undefined, thisArg = undefined)
  from: function from(arrayLike /* , mapfn = undefined, thisArg = undefined */) {
    var O = toObject(arrayLike);
    var C = typeof this == 'function' ? this : Array;
    var aLen = arguments.length;
    var mapfn = aLen > 1 ? arguments[1] : undefined;
    var mapping = mapfn !== undefined;
    var index = 0;
    var iterFn = getIterFn(O);
    var length, result, step, iterator;
    if (mapping) mapfn = ctx(mapfn, aLen > 2 ? arguments[2] : undefined, 2);
    // if object isn't iterable or it's array with default iterator - use simple case
    if (iterFn != undefined && !(C == Array && isArrayIter(iterFn))) {
      for (iterator = iterFn.call(O), result = new C(); !(step = iterator.next()).done; index++) {
        createProperty(result, index, mapping ? call(iterator, mapfn, [step.value, index], true) : step.value);
      }
    } else {
      length = toLength(O.length);
      for (result = new C(length); length > index; index++) {
        createProperty(result, index, mapping ? mapfn(O[index], index) : O[index]);
      }
    }
    result.length = index;
    return result;
  }
});


/***/ }),
/* 182 */
/***/ (function(module, exports, __webpack_require__) {

// call something on iterator step with safe closing on error
var anObject = __webpack_require__(15);
module.exports = function (iterator, fn, value, entries) {
  try {
    return entries ? fn(anObject(value)[0], value[1]) : fn(value);
  // 7.4.6 IteratorClose(iterator, completion)
  } catch (e) {
    var ret = iterator['return'];
    if (ret !== undefined) anObject(ret.call(iterator));
    throw e;
  }
};


/***/ }),
/* 183 */
/***/ (function(module, exports, __webpack_require__) {

// check on default Array iterator
var Iterators = __webpack_require__(22);
var ITERATOR = __webpack_require__(4)('iterator');
var ArrayProto = Array.prototype;

module.exports = function (it) {
  return it !== undefined && (Iterators.Array === it || ArrayProto[ITERATOR] === it);
};


/***/ }),
/* 184 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

var $defineProperty = __webpack_require__(9);
var createDesc = __webpack_require__(21);

module.exports = function (object, index, value) {
  if (index in object) $defineProperty.f(object, index, createDesc(0, value));
  else object[index] = value;
};


/***/ }),
/* 185 */
/***/ (function(module, exports, __webpack_require__) {

var ITERATOR = __webpack_require__(4)('iterator');
var SAFE_CLOSING = false;

try {
  var riter = [7][ITERATOR]();
  riter['return'] = function () { SAFE_CLOSING = true; };
  // eslint-disable-next-line no-throw-literal
  Array.from(riter, function () { throw 2; });
} catch (e) { /* empty */ }

module.exports = function (exec, skipClosing) {
  if (!skipClosing && !SAFE_CLOSING) return false;
  var safe = false;
  try {
    var arr = [7];
    var iter = arr[ITERATOR]();
    iter.next = function () { return { done: safe = true }; };
    arr[ITERATOR] = function () { return iter; };
    exec(arr);
  } catch (e) { /* empty */ }
  return safe;
};


/***/ }),
/* 186 */
/***/ (function(module, exports, __webpack_require__) {

var Component = __webpack_require__(0)(
  /* script */
  __webpack_require__(187),
  /* template */
  __webpack_require__(188),
  /* styles */
  null,
  /* scopeId */
  null,
  /* moduleIdentifier (server only) */
  null
)

module.exports = Component.exports


/***/ }),
/* 187 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_defineProperty__ = __webpack_require__(1);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_defineProperty___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_defineProperty__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__select_Select__ = __webpack_require__(31);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__select_Select___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_1__select_Select__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__icon_Icon__ = __webpack_require__(3);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__icon_Icon___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_2__icon_Icon__);


var _components;

//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//




/* harmony default export */ __webpack_exports__["default"] = ({
    name: 'BTableMobileSort',
    components: (_components = {}, __WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_defineProperty___default()(_components, __WEBPACK_IMPORTED_MODULE_1__select_Select___default.a.name, __WEBPACK_IMPORTED_MODULE_1__select_Select___default.a), __WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_defineProperty___default()(_components, __WEBPACK_IMPORTED_MODULE_2__icon_Icon___default.a.name, __WEBPACK_IMPORTED_MODULE_2__icon_Icon___default.a), _components),
    props: {
        currentSortColumn: Object,
        isAsc: Boolean,
        columns: Array,
        placeholder: String
    },
    data: function data() {
        return {
            mobileSort: this.currentSortColumn
        };
    },

    computed: {
        showPlaceholder: function showPlaceholder() {
            var _this = this;

            return !this.columns || !this.columns.some(function (column) {
                return column === _this.mobileSort;
            });
        }
    },
    watch: {
        mobileSort: function mobileSort(column) {
            if (this.currentSortColumn === column) return;

            this.$emit('sort', column);
        },
        currentSortColumn: function currentSortColumn(column) {
            this.mobileSort = column;
        }
    },
    methods: {
        sort: function sort() {
            this.$emit('sort', this.mobileSort);
        }
    }
});

/***/ }),
/* 188 */
/***/ (function(module, exports) {

module.exports={render:function (){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;
  return _c('div', {
    staticClass: "field table-mobile-sort"
  }, [_c('div', {
    staticClass: "field has-addons"
  }, [_c('b-select', {
    attrs: {
      "expanded": ""
    },
    model: {
      value: (_vm.mobileSort),
      callback: function($$v) {
        _vm.mobileSort = $$v
      },
      expression: "mobileSort"
    }
  }, [(_vm.placeholder) ? [_c('option', {
    directives: [{
      name: "show",
      rawName: "v-show",
      value: (_vm.showPlaceholder),
      expression: "showPlaceholder"
    }],
    attrs: {
      "selected": "",
      "disabled": "",
      "hidden": ""
    },
    domProps: {
      "value": {}
    }
  }, [_vm._v("\n                    " + _vm._s(_vm.placeholder) + "\n                ")])] : _vm._e(), _vm._v(" "), _vm._l((_vm.columns), function(column, index) {
    return (column.sortable) ? _c('option', {
      key: index,
      domProps: {
        "value": column
      }
    }, [_vm._v("\n                " + _vm._s(column.label) + "\n            ")]) : _vm._e()
  })], 2), _vm._v(" "), _c('div', {
    staticClass: "control"
  }, [_c('button', {
    staticClass: "button is-primary",
    on: {
      "click": _vm.sort
    }
  }, [_c('b-icon', {
    directives: [{
      name: "show",
      rawName: "v-show",
      value: (_vm.currentSortColumn === _vm.mobileSort),
      expression: "currentSortColumn === mobileSort"
    }],
    class: {
      'is-desc': !_vm.isAsc
    },
    attrs: {
      "icon": "arrow-up",
      "size": "is-small",
      "both": ""
    }
  })], 1)])], 1)])
},staticRenderFns: []}

/***/ }),
/* 189 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_symbol__ = __webpack_require__(5);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_symbol___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_symbol__);

//
//
//
//
//
//
//
//
//

/* harmony default export */ __webpack_exports__["default"] = ({
    name: 'BTableColumn',
    props: {
        label: String,
        customKey: [String, Number],
        field: String,
        meta: [String, Number, Boolean, Function, Object, Array, __WEBPACK_IMPORTED_MODULE_0_babel_runtime_core_js_symbol___default.a],
        width: [Number, String],
        numeric: Boolean,
        centered: Boolean,
        sortable: Boolean,
        visible: {
            type: Boolean,
            default: true
        },
        customSort: Function,
        internal: Boolean // Used internally by Table
    },
    data: function data() {
        return {
            newKey: this.customKey || this.label
        };
    },

    computed: {
        rootClasses: function rootClasses() {
            return {
                'has-text-right': this.numeric && !this.centered,
                'has-text-centered': this.centered
            };
        }
    },
    methods: {
        addRefToTable: function addRefToTable() {
            var _this = this;

            if (!this.$parent.$data._isTable) {
                this.$destroy();
                throw new Error('You should wrap bTableColumn on a bTable');
            }

            if (this.internal) return;

            // Since we're using scoped prop the columns gonna be multiplied,
            // this finds when to stop based on the newKey property.
            var repeated = this.$parent.newColumns.some(function (column) {
                return column.newKey === _this.newKey;
            });
            !repeated && this.$parent.newColumns.push(this);
        }
    },
    beforeMount: function beforeMount() {
        this.addRefToTable();
    },
    beforeUpdate: function beforeUpdate() {
        this.addRefToTable();
    },
    beforeDestroy: function beforeDestroy() {
        var index = this.$parent.newColumns.map(function (column) {
            return column.newKey;
        }).indexOf(this.newKey);
        if (index >= 0) {
            this.$parent.newColumns.splice(index, 1);
        }
    }
});

/***/ }),
/* 190 */
/***/ (function(module, exports) {

module.exports={render:function (){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;
  return (_vm.visible) ? _c('td', {
    class: _vm.rootClasses,
    attrs: {
      "data-label": _vm.label
    }
  }, [_c('span', [_vm._t("default")], 2)]) : _vm._e()
},staticRenderFns: []}

/***/ }),
/* 191 */
/***/ (function(module, exports) {

module.exports={render:function (){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;
  return _c('div', {
    staticClass: "b-table",
    class: {
      'is-loading': _vm.loading
    }
  }, [(_vm.mobileCards && _vm.hasSortablenewColumns) ? _c('b-table-mobile-sort', {
    attrs: {
      "current-sort-column": _vm.currentSortColumn,
      "is-asc": _vm.isAsc,
      "columns": _vm.newColumns,
      "placeholder": _vm.mobileSortPlaceholder
    },
    on: {
      "sort": function (column) { return _vm.sort(column); }
    }
  }) : _vm._e(), _vm._v(" "), _c('div', {
    staticClass: "table-wrapper"
  }, [_c('table', {
    staticClass: "table",
    class: _vm.tableClasses,
    attrs: {
      "tabindex": !_vm.focusable ? false : 0
    },
    on: {
      "keydown": [function($event) {
        if (!('button' in $event) && _vm._k($event.keyCode, "up", 38, $event.key)) { return null; }
        if ($event.target !== $event.currentTarget) { return null; }
        $event.preventDefault();
        _vm.pressedArrow(-1)
      }, function($event) {
        if (!('button' in $event) && _vm._k($event.keyCode, "down", 40, $event.key)) { return null; }
        if ($event.target !== $event.currentTarget) { return null; }
        $event.preventDefault();
        _vm.pressedArrow(1)
      }]
    }
  }, [(_vm.newColumns.length) ? _c('thead', [_c('tr', [(_vm.showDetailRowIcon) ? _c('th', {
    attrs: {
      "width": "40px"
    }
  }) : _vm._e(), _vm._v(" "), (_vm.checkable) ? _c('th', {
    staticClass: "checkbox-cell"
  }, [_c('b-checkbox', {
    attrs: {
      "value": _vm.isAllChecked,
      "disabled": _vm.isAllUncheckable
    },
    nativeOn: {
      "change": function($event) {
        _vm.checkAll($event)
      }
    }
  })], 1) : _vm._e(), _vm._v(" "), _vm._l((_vm.visibleColumns), function(column, index) {
    return _c('th', {
      key: index,
      class: {
        'is-current-sort': _vm.currentSortColumn === column,
          'is-sortable': column.sortable
      },
      style: ({
        width: column.width + 'px'
      }),
      on: {
        "click": function($event) {
          $event.stopPropagation();
          _vm.sort(column)
        }
      }
    }, [_c('div', {
      staticClass: "th-wrap",
      class: {
        'is-numeric': column.numeric,
          'is-centered': column.centered
      }
    }, [(_vm.$scopedSlots.header) ? _vm._t("header", null, {
      column: column,
      index: index
    }) : [_vm._v(_vm._s(column.label))], _vm._v(" "), _c('b-icon', {
      directives: [{
        name: "show",
        rawName: "v-show",
        value: (_vm.currentSortColumn === column),
        expression: "currentSortColumn === column"
      }],
      class: {
        'is-desc': !_vm.isAsc
      },
      attrs: {
        "icon": "arrow-up",
        "pack": _vm.iconPack,
        "both": "",
        "size": "is-small"
      }
    })], 2)])
  })], 2)]) : _vm._e(), _vm._v(" "), (_vm.visibleData.length) ? _c('tbody', [_vm._l((_vm.visibleData), function(row, index) {
    return [_c('tr', {
      key: _vm.customRowKey ? row[_vm.customRowKey] : index,
      class: [_vm.rowClass(row, index), {
        'is-selected': row === _vm.selected,
        'is-checked': _vm.isRowChecked(row),
      }],
      attrs: {
        "draggable": _vm.draggable
      },
      on: {
        "click": function($event) {
          _vm.selectRow(row)
        },
        "dblclick": function($event) {
          _vm.$emit('dblclick', row)
        },
        "contextmenu": function($event) {
          _vm.$emit('contextmenu', row, $event)
        },
        "dragstart": function($event) {
          _vm.handleDragStart($event, row, index)
        },
        "drop": function($event) {
          _vm.handleDrop($event, row, index)
        },
        "dragover": function($event) {
          _vm.handleDragOver($event, row, index)
        },
        "dragleave": function($event) {
          _vm.handleDragLeave($event, row, index)
        }
      }
    }, [(_vm.showDetailRowIcon) ? _c('td', {
      staticClass: "chevron-cell"
    }, [(_vm.hasDetailedVisible(row)) ? _c('a', {
      attrs: {
        "role": "button"
      },
      on: {
        "click": function($event) {
          $event.stopPropagation();
          _vm.toggleDetails(row)
        }
      }
    }, [_c('b-icon', {
      class: {
        'is-expanded': _vm.isVisibleDetailRow(row)
      },
      attrs: {
        "icon": "chevron-right",
        "pack": _vm.iconPack,
        "both": ""
      }
    })], 1) : _vm._e()]) : _vm._e(), _vm._v(" "), (_vm.checkable) ? _c('td', {
      staticClass: "checkbox-cell"
    }, [_c('b-checkbox', {
      attrs: {
        "disabled": !_vm.isRowCheckable(row),
        "value": _vm.isRowChecked(row)
      },
      nativeOn: {
        "change": function($event) {
          _vm.checkRow(row)
        },
        "click": function($event) {
          $event.stopPropagation();
        }
      }
    })], 1) : _vm._e(), _vm._v(" "), (_vm.$scopedSlots.default) ? _vm._t("default", null, {
      row: row,
      index: index
    }) : _vm._l((_vm.newColumns), function(column) {
      return _c('BTableColumn', _vm._b({
        key: column.field,
        attrs: {
          "internal": ""
        }
      }, 'BTableColumn', column, false), [(column.renderHtml) ? _c('span', {
        domProps: {
          "innerHTML": _vm._s(_vm.getValueByPath(row, column.field))
        }
      }) : [_vm._v("\n                                    " + _vm._s(_vm.getValueByPath(row, column.field)) + "\n                                ")]], 2)
    })], 2), _vm._v(" "), (_vm.detailed && _vm.isVisibleDetailRow(row)) ? _c('tr', {
      staticClass: "detail"
    }, [_c('td', {
      attrs: {
        "colspan": _vm.columnCount
      }
    }, [_c('div', {
      staticClass: "detail-container"
    }, [_vm._t("detail", null, {
      row: row,
      index: index
    })], 2)])]) : _vm._e()]
  })], 2) : _c('tbody', [_c('tr', {
    staticClass: "is-empty"
  }, [_c('td', {
    attrs: {
      "colspan": _vm.columnCount
    }
  }, [_vm._t("empty")], 2)])]), _vm._v(" "), (_vm.$slots.footer !== undefined) ? _c('tfoot', [_c('tr', {
    staticClass: "table-footer"
  }, [(_vm.hasCustomFooterSlot()) ? _vm._t("footer") : _c('th', {
    attrs: {
      "colspan": _vm.columnCount
    }
  }, [_vm._t("footer")], 2)], 2)]) : _vm._e()])]), _vm._v(" "), ((_vm.checkable && _vm.hasBottomLeftSlot()) || _vm.paginated) ? _c('div', {
    staticClass: "level"
  }, [_c('div', {
    staticClass: "level-left"
  }, [_vm._t("bottom-left")], 2), _vm._v(" "), _c('div', {
    staticClass: "level-right"
  }, [(_vm.paginated) ? _c('div', {
    staticClass: "level-item"
  }, [_c('b-pagination', {
    attrs: {
      "icon-pack": _vm.iconPack,
      "total": _vm.newDataTotal,
      "per-page": _vm.perPage,
      "simple": _vm.paginationSimple,
      "size": _vm.paginationSize,
      "current": _vm.newCurrentPage,
      "aria-next-label": _vm.ariaNextLabel,
      "aria-previous-label": _vm.ariaPreviousLabel,
      "aria-page-label": _vm.ariaPageLabel,
      "aria-current-label": _vm.ariaCurrentLabel
    },
    on: {
      "change": _vm.pageChanged
    }
  })], 1) : _vm._e()])]) : _vm._e()], 1)
},staticRenderFns: []}

/***/ }),
/* 192 */
/***/ (function(module, exports, __webpack_require__) {

var Component = __webpack_require__(0)(
  /* script */
  __webpack_require__(193),
  /* template */
  __webpack_require__(194),
  /* styles */
  null,
  /* scopeId */
  null,
  /* moduleIdentifier (server only) */
  null
)

module.exports = Component.exports


/***/ }),
/* 193 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });

// EXTERNAL MODULE: ./node_modules/babel-runtime/helpers/defineProperty.js
var defineProperty = __webpack_require__(1);
var defineProperty_default = /*#__PURE__*/__webpack_require__.n(defineProperty);

// EXTERNAL MODULE: ./src/components/icon/Icon.vue
var Icon = __webpack_require__(3);
var Icon_default = /*#__PURE__*/__webpack_require__.n(Icon);

// CONCATENATED MODULE: ./src/utils/SlotComponent.js
/* harmony default export */ var SlotComponent = ({
    name: 'BSlotComponent',
    props: {
        component: {
            type: Object,
            required: true
        },
        name: {
            type: String,
            default: 'default'
        },
        tag: {
            type: String,
            default: 'div'
        },
        event: {
            type: String,
            default: 'hook:updated'
        }
    },
    methods: {
        refresh: function refresh() {
            this.$forceUpdate();
        },
        isVueComponent: function isVueComponent() {
            return this.component && this.component._isVue;
        }
    },
    created: function created() {
        if (this.isVueComponent()) {
            this.component.$on(this.event, this.refresh);
        }
    },
    beforeDestroy: function beforeDestroy() {
        if (this.isVueComponent()) {
            this.component.$off(this.event, this.refresh);
        }
    },
    render: function render(h) {
        if (this.isVueComponent()) {
            var slots = this.component.$slots[this.name];
            return h(this.tag, {}, slots);
        }
    }
});
// CONCATENATED MODULE: ./node_modules/babel-loader/lib!./node_modules/vue-loader/lib/selector.js?type=script&index=0!./src/components/tabs/Tabs.vue


var _components;

//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//




/* harmony default export */ var Tabs = __webpack_exports__["default"] = ({
    name: 'BTabs',
    components: (_components = {}, defineProperty_default()(_components, Icon_default.a.name, Icon_default.a), defineProperty_default()(_components, SlotComponent.name, SlotComponent), _components),
    props: {
        value: Number,
        expanded: Boolean,
        type: String,
        size: String,
        position: String,
        animated: {
            type: Boolean,
            default: true
        }
    },
    data: function data() {
        return {
            activeTab: this.value || 0,
            tabItems: [],
            contentHeight: 0,
            isTransitioning: false,
            _isTabs: true // Used internally by TabItem
        };
    },

    computed: {
        navClasses: function navClasses() {
            return [this.type, this.size, this.position, {
                'is-fullwidth': this.expanded,
                'is-toggle-rounded is-toggle': this.type === 'is-toggle-rounded'
            }];
        }
    },
    watch: {
        /**
         * When v-model is changed set the new active tab.
         */
        value: function value(_value) {
            this.changeTab(_value);
        },


        /**
         * When tab-items are updated, set active one.
         */
        tabItems: function tabItems() {
            if (this.activeTab < this.tabItems.length) {
                this.tabItems[this.activeTab].isActive = true;
            }
        }
    },
    methods: {
        /**
         * Change the active tab and emit change event.
         */
        changeTab: function changeTab(newIndex) {
            if (this.activeTab === newIndex) return;

            if (this.activeTab < this.tabItems.length) {
                this.tabItems[this.activeTab].deactivate(this.activeTab, newIndex);
            }
            this.tabItems[newIndex].activate(this.activeTab, newIndex);
            this.activeTab = newIndex;
            this.$emit('change', newIndex);
        },


        /**
         * Tab click listener, emit input event and change active tab.
         */
        tabClick: function tabClick(value) {
            this.$emit('input', value);
            this.changeTab(value);
        }
    },
    mounted: function mounted() {
        if (this.activeTab < this.tabItems.length) {
            this.tabItems[this.activeTab].isActive = true;
        }
    }
});

/***/ }),
/* 194 */
/***/ (function(module, exports) {

module.exports={render:function (){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;
  return _c('div', {
    staticClass: "b-tabs",
    class: {
      'is-fullwidth': _vm.expanded
    }
  }, [_c('nav', {
    staticClass: "tabs",
    class: _vm.navClasses
  }, [_c('ul', _vm._l((_vm.tabItems), function(tabItem, index) {
    return _c('li', {
      directives: [{
        name: "show",
        rawName: "v-show",
        value: (tabItem.visible),
        expression: "tabItem.visible"
      }],
      key: index,
      class: {
        'is-active': _vm.activeTab === index, 'is-disabled': tabItem.disabled
      }
    }, [_c('a', {
      on: {
        "click": function($event) {
          _vm.tabClick(index)
        }
      }
    }, [(tabItem.$slots.header) ? [_c('b-slot-component', {
      attrs: {
        "component": tabItem,
        "name": "header",
        "tag": "span"
      }
    })] : [(tabItem.icon) ? _c('b-icon', {
      attrs: {
        "icon": tabItem.icon,
        "pack": tabItem.iconPack,
        "size": _vm.size
      }
    }) : _vm._e(), _vm._v(" "), _c('span', [_vm._v(_vm._s(tabItem.label))])]], 2)])
  }))]), _vm._v(" "), _c('section', {
    staticClass: "tab-content",
    class: {
      'is-transitioning': _vm.isTransitioning
    }
  }, [_vm._t("default")], 2)])
},staticRenderFns: []}

/***/ }),
/* 195 */
/***/ (function(module, exports, __webpack_require__) {

var Component = __webpack_require__(0)(
  /* script */
  __webpack_require__(196),
  /* template */
  null,
  /* styles */
  null,
  /* scopeId */
  null,
  /* moduleIdentifier (server only) */
  null
)

module.exports = Component.exports


/***/ }),
/* 196 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });

/* harmony default export */ __webpack_exports__["default"] = ({
    name: 'BTabItem',
    props: {
        label: String,
        icon: String,
        iconPack: String,
        disabled: Boolean,
        visible: {
            type: Boolean,
            default: true
        }
    },
    data: function data() {
        return {
            isActive: false,
            transitionName: null
        };
    },

    methods: {
        /**
         * Activate tab, alter animation name based on the index.
         */
        activate: function activate(oldIndex, index) {
            this.transitionName = index < oldIndex ? 'slide-next' : 'slide-prev';
            this.isActive = true;
        },


        /**
         * Deactivate tab, alter animation name based on the index.
         */
        deactivate: function deactivate(oldIndex, index) {
            this.transitionName = index < oldIndex ? 'slide-next' : 'slide-prev';
            this.isActive = false;
        }
    },
    created: function created() {
        if (!this.$parent.$data._isTabs) {
            this.$destroy();
            throw new Error('You should wrap bTabItem on a bTabs');
        }
        this.$parent.tabItems.push(this);
    },
    beforeDestroy: function beforeDestroy() {
        var index = this.$parent.tabItems.indexOf(this);
        if (index >= 0) {
            this.$parent.tabItems.splice(index, 1);
        }
    },
    render: function render(createElement) {
        var _this = this;

        if (this.isActive && this.visible) {
            var vnode = createElement('div', { attrs: { 'class': 'tab-item' } }, this.$slots.default);
            if (!this.$parent.animated) {
                return vnode;
            }
            return createElement('transition', {
                props: {
                    'name': this.transitionName
                },
                on: {
                    'before-enter': function beforeEnter() {
                        _this.$parent.isTransitioning = true;
                    },
                    'after-enter': function afterEnter() {
                        _this.$parent.isTransitioning = false;
                    }
                }
            }, [vnode]);
        }
    }
});

/***/ }),
/* 197 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//

/* harmony default export */ __webpack_exports__["default"] = ({
    name: 'BTag',
    props: {
        attached: Boolean,
        closable: Boolean,
        type: String,
        size: String,
        rounded: Boolean,
        disabled: Boolean,
        ellipsis: Boolean,
        tabstop: {
            type: Boolean,
            default: true
        }
    },
    methods: {
        /**
         * Emit close event when delete button is clicked
         * or delete key is pressed.
         */
        close: function close() {
            if (this.disabled) return;

            this.$emit('close');
        }
    }
});

/***/ }),
/* 198 */
/***/ (function(module, exports) {

module.exports={render:function (){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;
  return (_vm.attached && _vm.closable) ? _c('div', {
    staticClass: "tags has-addons"
  }, [_c('span', {
    staticClass: "tag",
    class: [_vm.type, _vm.size, {
      'is-rounded': _vm.rounded
    }]
  }, [_c('span', {
    class: {
      'has-ellipsis': _vm.ellipsis
    }
  }, [_vm._t("default")], 2)]), _vm._v(" "), _c('a', {
    staticClass: "tag is-delete",
    class: [_vm.size, {
      'is-rounded': _vm.rounded
    }],
    attrs: {
      "role": "button",
      "tabindex": _vm.tabstop ? 0 : false,
      "disabled": _vm.disabled
    },
    on: {
      "click": function($event) {
        _vm.close()
      },
      "keyup": function($event) {
        if (!('button' in $event) && _vm._k($event.keyCode, "delete", [8, 46], $event.key)) { return null; }
        $event.preventDefault();
        _vm.close()
      }
    }
  })]) : _c('span', {
    staticClass: "tag",
    class: [_vm.type, _vm.size, {
      'is-rounded': _vm.rounded
    }]
  }, [_c('span', {
    class: {
      'has-ellipsis': _vm.ellipsis
    }
  }, [_vm._t("default")], 2), _vm._v(" "), (_vm.closable) ? _c('a', {
    staticClass: "delete is-small",
    attrs: {
      "role": "button",
      "disabled": _vm.disabled,
      "tabindex": _vm.tabstop ? 0 : false
    },
    on: {
      "click": function($event) {
        _vm.close()
      },
      "keyup": function($event) {
        if (!('button' in $event) && _vm._k($event.keyCode, "delete", [8, 46], $event.key)) { return null; }
        $event.preventDefault();
        _vm.close()
      }
    }
  }) : _vm._e()])
},staticRenderFns: []}

/***/ }),
/* 199 */
/***/ (function(module, exports, __webpack_require__) {

var Component = __webpack_require__(0)(
  /* script */
  __webpack_require__(200),
  /* template */
  __webpack_require__(201),
  /* styles */
  null,
  /* scopeId */
  null,
  /* moduleIdentifier (server only) */
  null
)

module.exports = Component.exports


/***/ }),
/* 200 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
//
//
//
//
//
//

/* harmony default export */ __webpack_exports__["default"] = ({
    name: 'BTaglist',
    props: {
        attached: Boolean
    }
});

/***/ }),
/* 201 */
/***/ (function(module, exports) {

module.exports={render:function (){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;
  return _c('div', {
    staticClass: "tags",
    class: {
      'has-addons': _vm.attached
    }
  }, [_vm._t("default")], 2)
},staticRenderFns: []}

/***/ }),
/* 202 */
/***/ (function(module, exports, __webpack_require__) {

var Component = __webpack_require__(0)(
  /* script */
  __webpack_require__(203),
  /* template */
  __webpack_require__(204),
  /* styles */
  null,
  /* scopeId */
  null,
  /* moduleIdentifier (server only) */
  null
)

module.exports = Component.exports


/***/ }),
/* 203 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_typeof__ = __webpack_require__(53);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_typeof___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_typeof__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_babel_runtime_helpers_defineProperty__ = __webpack_require__(1);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1_babel_runtime_helpers_defineProperty___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_1_babel_runtime_helpers_defineProperty__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__utils_helpers__ = __webpack_require__(7);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_3__tag_Tag__ = __webpack_require__(68);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_3__tag_Tag___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_3__tag_Tag__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_4__autocomplete_Autocomplete__ = __webpack_require__(52);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_4__autocomplete_Autocomplete___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_4__autocomplete_Autocomplete__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_5__utils_FormElementMixin__ = __webpack_require__(13);



var _components;

//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//






/* harmony default export */ __webpack_exports__["default"] = ({
    name: 'BTaginput',
    components: (_components = {}, __WEBPACK_IMPORTED_MODULE_1_babel_runtime_helpers_defineProperty___default()(_components, __WEBPACK_IMPORTED_MODULE_4__autocomplete_Autocomplete___default.a.name, __WEBPACK_IMPORTED_MODULE_4__autocomplete_Autocomplete___default.a), __WEBPACK_IMPORTED_MODULE_1_babel_runtime_helpers_defineProperty___default()(_components, __WEBPACK_IMPORTED_MODULE_3__tag_Tag___default.a.name, __WEBPACK_IMPORTED_MODULE_3__tag_Tag___default.a), _components),
    mixins: [__WEBPACK_IMPORTED_MODULE_5__utils_FormElementMixin__["a" /* default */]],
    inheritAttrs: false,
    props: {
        value: {
            type: Array,
            default: function _default() {
                return [];
            }
        },
        data: {
            type: Array,
            default: function _default() {
                return [];
            }
        },
        type: String,
        rounded: {
            type: Boolean,
            default: false
        },
        attached: {
            type: Boolean,
            default: false
        },
        maxtags: {
            type: [Number, String],
            required: false
        },
        field: {
            type: String,
            default: 'value'
        },
        autocomplete: Boolean,
        nativeAutocomplete: String,
        disabled: Boolean,
        ellipsis: Boolean,
        closable: {
            type: Boolean,
            default: true
        },
        confirmKeyCodes: {
            type: Array,
            default: function _default() {
                return [13, 188];
            }
        },
        removeOnKeys: {
            type: Array,
            default: function _default() {
                return [8];
            }
        },
        allowNew: Boolean,
        onPasteSeparators: {
            type: Array,
            default: function _default() {
                return [','];
            }
        },
        beforeAdding: {
            type: Function,
            default: function _default() {
                return true;
            }
        },
        allowDuplicates: {
            type: Boolean,
            default: false
        }
    },
    data: function data() {
        return {
            tags: this.value || [],
            newTag: '',
            _elementRef: 'input',
            _isTaginput: true
        };
    },

    computed: {
        rootClasses: function rootClasses() {
            return {
                'is-expanded': this.expanded
            };
        },
        containerClasses: function containerClasses() {
            return {
                'is-focused': this.isFocused,
                'is-focusable': this.hasInput
            };
        },
        valueLength: function valueLength() {
            return this.newTag.trim().length;
        },
        defaultSlotName: function defaultSlotName() {
            return this.hasDefaultSlot ? 'default' : 'dontrender';
        },
        emptySlotName: function emptySlotName() {
            return this.hasEmptySlot ? 'empty' : 'dontrender';
        },
        headerSlotName: function headerSlotName() {
            return this.hasHeaderSlot ? 'header' : 'dontrender';
        },
        hasDefaultSlot: function hasDefaultSlot() {
            return !!this.$scopedSlots.default;
        },
        hasEmptySlot: function hasEmptySlot() {
            return !!this.$slots.empty;
        },
        hasHeaderSlot: function hasHeaderSlot() {
            return !!this.$slots.header;
        },


        /**
         * Show the input field if a maxtags hasn't been set or reached.
         */
        hasInput: function hasInput() {
            return this.maxtags == null || this.tagsLength < this.maxtags;
        },
        tagsLength: function tagsLength() {
            return this.tags.length;
        },


        /**
         * If Taginput has onPasteSeparators prop,
         * returning new RegExp used to split pasted string.
         */
        separatorsAsRegExp: function separatorsAsRegExp() {
            var sep = this.onPasteSeparators;

            return sep.length ? new RegExp(sep.map(function (s) {
                return s ? s.replace(/[-[\]{}()*+?.,\\^$|#\s]/g, '\\$&') : null;
            }).join('|'), 'g') : null;
        }
    },
    watch: {
        /**
         * When v-model is changed set internal value.
         */
        value: function value(_value) {
            this.tags = _value;
        },
        hasInput: function hasInput() {
            if (!this.hasInput) this.onBlur();
        }
    },
    methods: {
        addTag: function addTag(tag) {
            var tagToAdd = tag || this.newTag.trim();

            if (tagToAdd) {
                if (!this.autocomplete) {
                    var reg = this.separatorsAsRegExp;
                    if (reg && tagToAdd.match(reg)) {
                        tagToAdd.split(reg).map(function (t) {
                            return t.trim();
                        }).filter(function (t) {
                            return t.length !== 0;
                        }).map(this.addTag);
                        return;
                    }
                }

                // Add the tag input if it is not blank
                // or previously added (if not allowDuplicates).
                var add = !this.allowDuplicates ? this.tags.indexOf(tagToAdd) === -1 : true;
                if (add && this.beforeAdding(tagToAdd)) {
                    this.tags.push(tagToAdd);
                    this.$emit('input', this.tags);
                    this.$emit('add', tagToAdd);
                }
            }

            this.newTag = '';
        },
        getNormalizedTagText: function getNormalizedTagText(tag) {
            if ((typeof tag === 'undefined' ? 'undefined' : __WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_typeof___default()(tag)) === 'object') {
                return Object(__WEBPACK_IMPORTED_MODULE_2__utils_helpers__["a" /* getValueByPath */])(tag, this.field);
            }

            return tag;
        },
        customOnBlur: function customOnBlur($event) {
            // Add tag on-blur if not select only
            if (!this.autocomplete) this.addTag();

            this.onBlur($event);
        },
        onSelect: function onSelect(option) {
            var _this = this;

            if (!option) return;

            this.addTag(option);
            this.$nextTick(function () {
                _this.newTag = '';
            });
        },
        removeTag: function removeTag(index) {
            var tag = this.tags.splice(index, 1)[0];
            this.$emit('input', this.tags);
            this.$emit('remove', tag);
            return tag;
        },
        removeLastTag: function removeLastTag() {
            if (this.tagsLength > 0) {
                this.removeTag(this.tagsLength - 1);
            }
        },
        keydown: function keydown(event) {
            if (this.removeOnKeys.indexOf(event.keyCode) !== -1 && !this.newTag.length) {
                this.removeLastTag();
            }
            // Stop if is to accept select only
            if (this.autocomplete && !this.allowNew) return;

            if (this.confirmKeyCodes.indexOf(event.keyCode) >= 0) {
                event.preventDefault();
                this.addTag();
            }
        },
        onTyping: function onTyping($event) {
            this.$emit('typing', $event.trim());
        }
    }
});

/***/ }),
/* 204 */
/***/ (function(module, exports) {

module.exports={render:function (){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;
  return _c('div', {
    staticClass: "taginput control",
    class: _vm.rootClasses
  }, [_c('div', {
    staticClass: "taginput-container",
    class: [_vm.statusType, _vm.size, _vm.containerClasses],
    attrs: {
      "disabled": _vm.disabled
    },
    on: {
      "click": function($event) {
        _vm.hasInput && _vm.focus($event)
      }
    }
  }, [_vm._l((_vm.tags), function(tag, index) {
    return _c('b-tag', {
      key: index,
      attrs: {
        "type": _vm.type,
        "size": _vm.size,
        "rounded": _vm.rounded,
        "attached": _vm.attached,
        "tabstop": false,
        "disabled": _vm.disabled,
        "ellipsis": _vm.ellipsis,
        "closable": _vm.closable
      },
      on: {
        "close": function($event) {
          _vm.removeTag(index)
        }
      }
    }, [_vm._v("\n            " + _vm._s(_vm.getNormalizedTagText(tag)) + "\n        ")])
  }), _vm._v(" "), (_vm.hasInput) ? _c('b-autocomplete', _vm._b({
    ref: "autocomplete",
    attrs: {
      "data": _vm.data,
      "field": _vm.field,
      "icon": _vm.icon,
      "icon-pack": _vm.iconPack,
      "maxlength": _vm.maxlength,
      "has-counter": false,
      "size": _vm.size,
      "disabled": _vm.disabled,
      "loading": _vm.loading,
      "autocomplete": _vm.nativeAutocomplete,
      "keep-first": !_vm.allowNew
    },
    on: {
      "typing": _vm.onTyping,
      "focus": _vm.onFocus,
      "blur": _vm.customOnBlur,
      "select": _vm.onSelect
    },
    nativeOn: {
      "keydown": function($event) {
        _vm.keydown($event)
      }
    },
    scopedSlots: _vm._u([{
      key: _vm.defaultSlotName,
      fn: function(props) {
        return [_vm._t("default", null, {
          option: props.option,
          index: props.index
        })]
      }
    }]),
    model: {
      value: (_vm.newTag),
      callback: function($$v) {
        _vm.newTag = $$v
      },
      expression: "newTag"
    }
  }, 'b-autocomplete', _vm.$attrs, false), [_c('template', {
    slot: _vm.headerSlotName
  }, [_vm._t("header")], 2), _vm._v(" "), _c('template', {
    slot: _vm.emptySlotName
  }, [_vm._t("empty")], 2)], 2) : _vm._e()], 2), _vm._v(" "), (_vm.maxtags || _vm.maxlength) ? _c('p', {
    staticClass: "help counter"
  }, [(_vm.maxlength && _vm.valueLength > 0) ? [_vm._v("\n            " + _vm._s(_vm.valueLength) + " / " + _vm._s(_vm.maxlength) + "\n        ")] : (_vm.maxtags) ? [_vm._v("\n            " + _vm._s(_vm.tagsLength) + " / " + _vm._s(_vm.maxtags) + "\n        ")] : _vm._e()], 2) : _vm._e()])
},staticRenderFns: []}

/***/ }),
/* 205 */
/***/ (function(module, exports, __webpack_require__) {

var Component = __webpack_require__(0)(
  /* script */
  __webpack_require__(206),
  /* template */
  __webpack_require__(207),
  /* styles */
  null,
  /* scopeId */
  null,
  /* moduleIdentifier (server only) */
  null
)

module.exports = Component.exports


/***/ }),
/* 206 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_defineProperty__ = __webpack_require__(1);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_defineProperty___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_defineProperty__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__utils_TimepickerMixin__ = __webpack_require__(62);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__dropdown_Dropdown__ = __webpack_require__(28);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_2__dropdown_Dropdown___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_2__dropdown_Dropdown__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_3__dropdown_DropdownItem__ = __webpack_require__(29);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_3__dropdown_DropdownItem___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_3__dropdown_DropdownItem__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_4__input_Input__ = __webpack_require__(23);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_4__input_Input___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_4__input_Input__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_5__field_Field__ = __webpack_require__(30);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_5__field_Field___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_5__field_Field__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_6__select_Select__ = __webpack_require__(31);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_6__select_Select___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_6__select_Select__);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_7__icon_Icon__ = __webpack_require__(3);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_7__icon_Icon___default = __webpack_require__.n(__WEBPACK_IMPORTED_MODULE_7__icon_Icon__);


var _components;

//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//









/* harmony default export */ __webpack_exports__["default"] = ({
    name: 'BTimepicker',
    components: (_components = {}, __WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_defineProperty___default()(_components, __WEBPACK_IMPORTED_MODULE_4__input_Input___default.a.name, __WEBPACK_IMPORTED_MODULE_4__input_Input___default.a), __WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_defineProperty___default()(_components, __WEBPACK_IMPORTED_MODULE_5__field_Field___default.a.name, __WEBPACK_IMPORTED_MODULE_5__field_Field___default.a), __WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_defineProperty___default()(_components, __WEBPACK_IMPORTED_MODULE_6__select_Select___default.a.name, __WEBPACK_IMPORTED_MODULE_6__select_Select___default.a), __WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_defineProperty___default()(_components, __WEBPACK_IMPORTED_MODULE_7__icon_Icon___default.a.name, __WEBPACK_IMPORTED_MODULE_7__icon_Icon___default.a), __WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_defineProperty___default()(_components, __WEBPACK_IMPORTED_MODULE_2__dropdown_Dropdown___default.a.name, __WEBPACK_IMPORTED_MODULE_2__dropdown_Dropdown___default.a), __WEBPACK_IMPORTED_MODULE_0_babel_runtime_helpers_defineProperty___default()(_components, __WEBPACK_IMPORTED_MODULE_3__dropdown_DropdownItem___default.a.name, __WEBPACK_IMPORTED_MODULE_3__dropdown_DropdownItem___default.a), _components),
    mixins: [__WEBPACK_IMPORTED_MODULE_1__utils_TimepickerMixin__["a" /* default */]],
    inheritAttrs: false,
    data: function data() {
        return {
            _isTimepicker: true
        };
    }
});

/***/ }),
/* 207 */
/***/ (function(module, exports) {

module.exports={render:function (){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;
  return _c('div', {
    staticClass: "timepicker control",
    class: [_vm.size, {
      'is-expanded': _vm.expanded
    }]
  }, [(!_vm.isMobile || _vm.inline) ? _c('b-dropdown', {
    ref: "dropdown",
    attrs: {
      "position": _vm.position,
      "disabled": _vm.disabled,
      "inline": _vm.inline
    }
  }, [(!_vm.inline) ? _c('b-input', _vm._b({
    ref: "input",
    attrs: {
      "slot": "trigger",
      "autocomplete": "off",
      "value": _vm.formatValue(_vm.dateSelected),
      "placeholder": _vm.placeholder,
      "size": _vm.size,
      "icon": _vm.icon,
      "icon-pack": _vm.iconPack,
      "loading": _vm.loading,
      "disabled": _vm.disabled,
      "readonly": !_vm.editable,
      "rounded": _vm.rounded
    },
    on: {
      "focus": function($event) {
        _vm.$emit('focus', $event)
      },
      "blur": function($event) {
        _vm.$emit('blur', $event) && _vm.checkHtml5Validity()
      }
    },
    nativeOn: {
      "change": function($event) {
        _vm.onChange($event.target.value)
      }
    },
    slot: "trigger"
  }, 'b-input', _vm.$attrs, false)) : _vm._e(), _vm._v(" "), _c('b-dropdown-item', {
    attrs: {
      "disabled": _vm.disabled,
      "custom": ""
    }
  }, [_c('b-field', {
    attrs: {
      "grouped": "",
      "position": "is-centered"
    }
  }, [_c('b-select', {
    attrs: {
      "disabled": _vm.disabled,
      "placeholder": "00"
    },
    nativeOn: {
      "change": function($event) {
        _vm.onHoursChange($event.target.value)
      }
    },
    model: {
      value: (_vm.hoursSelected),
      callback: function($$v) {
        _vm.hoursSelected = $$v
      },
      expression: "hoursSelected"
    }
  }, _vm._l((_vm.hours), function(hour) {
    return _c('option', {
      key: hour.value,
      attrs: {
        "disabled": _vm.isHourDisabled(hour.value)
      },
      domProps: {
        "value": hour.value
      }
    }, [_vm._v("\n                        " + _vm._s(hour.label) + "\n                    ")])
  })), _vm._v(" "), _c('span', {
    staticClass: "control is-colon"
  }, [_vm._v(":")]), _vm._v(" "), _c('b-select', {
    attrs: {
      "disabled": _vm.disabled,
      "placeholder": "00"
    },
    nativeOn: {
      "change": function($event) {
        _vm.onMinutesChange($event.target.value)
      }
    },
    model: {
      value: (_vm.minutesSelected),
      callback: function($$v) {
        _vm.minutesSelected = $$v
      },
      expression: "minutesSelected"
    }
  }, _vm._l((_vm.minutes), function(minute) {
    return _c('option', {
      key: minute.value,
      attrs: {
        "disabled": _vm.isMinuteDisabled(minute.value)
      },
      domProps: {
        "value": minute.value
      }
    }, [_vm._v("\n                        " + _vm._s(minute.label) + "\n                    ")])
  })), _vm._v(" "), (!_vm.isHourFormat24) ? _c('b-select', {
    attrs: {
      "disabled": _vm.disabled
    },
    nativeOn: {
      "change": function($event) {
        _vm.onMeridienChange($event.target.value)
      }
    },
    model: {
      value: (_vm.meridienSelected),
      callback: function($$v) {
        _vm.meridienSelected = $$v
      },
      expression: "meridienSelected"
    }
  }, _vm._l((_vm.meridiens), function(meridien) {
    return _c('option', {
      key: meridien,
      domProps: {
        "value": meridien
      }
    }, [_vm._v("\n                        " + _vm._s(meridien) + "\n                    ")])
  })) : _vm._e()], 1), _vm._v(" "), (_vm.$slots.default !== undefined && _vm.$slots.default.length) ? _c('footer', {
    staticClass: "timepicker-footer"
  }, [_vm._t("default")], 2) : _vm._e()], 1)], 1) : _c('b-input', _vm._b({
    ref: "input",
    attrs: {
      "type": "time",
      "autocomplete": "off",
      "value": _vm.formatHHMMSS(_vm.value),
      "placeholder": _vm.placeholder,
      "size": _vm.size,
      "icon": _vm.icon,
      "icon-pack": _vm.iconPack,
      "loading": _vm.loading,
      "max": _vm.formatHHMMSS(_vm.maxTime),
      "min": _vm.formatHHMMSS(_vm.minTime),
      "disabled": _vm.disabled,
      "readonly": false
    },
    on: {
      "focus": function($event) {
        _vm.$emit('focus', $event)
      },
      "blur": function($event) {
        _vm.$emit('blur', $event) && _vm.checkHtml5Validity()
      }
    },
    nativeOn: {
      "change": function($event) {
        _vm.onChangeNativePicker($event)
      }
    }
  }, 'b-input', _vm.$attrs, false))], 1)
},staticRenderFns: []}

/***/ }),
/* 208 */
/***/ (function(module, exports, __webpack_require__) {

var Component = __webpack_require__(0)(
  /* script */
  __webpack_require__(209),
  /* template */
  __webpack_require__(210),
  /* styles */
  null,
  /* scopeId */
  null,
  /* moduleIdentifier (server only) */
  null
)

module.exports = Component.exports


/***/ }),
/* 209 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__utils_config__ = __webpack_require__(2);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__utils_NoticeMixin_js__ = __webpack_require__(45);
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//




/* harmony default export */ __webpack_exports__["default"] = ({
    name: 'BToast',
    mixins: [__WEBPACK_IMPORTED_MODULE_1__utils_NoticeMixin_js__["a" /* default */]],
    data: function data() {
        return {
            newDuration: this.duration || __WEBPACK_IMPORTED_MODULE_0__utils_config__["a" /* default */].defaultToastDuration
        };
    }
});

/***/ }),
/* 210 */
/***/ (function(module, exports) {

module.exports={render:function (){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;
  return _c('transition', {
    attrs: {
      "enter-active-class": _vm.transition.enter,
      "leave-active-class": _vm.transition.leave
    }
  }, [_c('div', {
    directives: [{
      name: "show",
      rawName: "v-show",
      value: (_vm.isActive),
      expression: "isActive"
    }],
    staticClass: "toast",
    class: [_vm.type, _vm.position],
    attrs: {
      "aria-hidden": !_vm.isActive,
      "role": "alert"
    }
  }, [_c('div', {
    domProps: {
      "innerHTML": _vm._s(_vm.message)
    }
  })])])
},staticRenderFns: []}

/***/ }),
/* 211 */
/***/ (function(module, exports, __webpack_require__) {

var Component = __webpack_require__(0)(
  /* script */
  __webpack_require__(212),
  /* template */
  __webpack_require__(213),
  /* styles */
  null,
  /* scopeId */
  null,
  /* moduleIdentifier (server only) */
  null
)

module.exports = Component.exports


/***/ }),
/* 212 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__utils_config__ = __webpack_require__(2);
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//



/* harmony default export */ __webpack_exports__["default"] = ({
    name: 'BTooltip',
    props: {
        active: {
            type: Boolean,
            default: true
        },
        type: String,
        label: String,
        position: {
            type: String,
            default: 'is-top',
            validator: function validator(value) {
                return ['is-top', 'is-bottom', 'is-left', 'is-right'].indexOf(value) > -1;
            }
        },
        always: Boolean,
        animated: Boolean,
        square: Boolean,
        dashed: Boolean,
        multilined: Boolean,
        size: {
            type: String,
            default: 'is-medium'
        }
    },
    computed: {
        newType: function newType() {
            return this.type || __WEBPACK_IMPORTED_MODULE_0__utils_config__["a" /* default */].defaultTooltipType;
        },
        newAnimated: function newAnimated() {
            return this.animated || __WEBPACK_IMPORTED_MODULE_0__utils_config__["a" /* default */].defaultTooltipAnimated;
        }
    }
});

/***/ }),
/* 213 */
/***/ (function(module, exports) {

module.exports={render:function (){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;
  return _c('span', {
    class: [_vm.newType, _vm.position, _vm.size, {
      'b-tooltip': _vm.active,
      'is-square': _vm.square,
      'is-animated': _vm.newAnimated,
      'is-always': _vm.always,
      'is-multiline': _vm.multilined,
      'is-dashed': _vm.dashed
    }],
    attrs: {
      "data-label": _vm.label
    }
  }, [_vm._t("default")], 2)
},staticRenderFns: []}

/***/ }),
/* 214 */
/***/ (function(module, exports, __webpack_require__) {

var Component = __webpack_require__(0)(
  /* script */
  __webpack_require__(215),
  /* template */
  __webpack_require__(216),
  /* styles */
  null,
  /* scopeId */
  null,
  /* moduleIdentifier (server only) */
  null
)

module.exports = Component.exports


/***/ }),
/* 215 */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
Object.defineProperty(__webpack_exports__, "__esModule", { value: true });
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_0__utils_FormElementMixin__ = __webpack_require__(13);
/* harmony import */ var __WEBPACK_IMPORTED_MODULE_1__utils_ssr__ = __webpack_require__(64);
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//




/* harmony default export */ __webpack_exports__["default"] = ({
    name: 'BUpload',
    mixins: [__WEBPACK_IMPORTED_MODULE_0__utils_FormElementMixin__["a" /* default */]],
    inheritAttrs: false,
    props: {
        value: {
            type: [Object, Function, __WEBPACK_IMPORTED_MODULE_1__utils_ssr__["a" /* File */], Array]
        },
        multiple: Boolean,
        disabled: Boolean,
        accept: String,
        dragDrop: Boolean,
        type: {
            type: String,
            default: 'is-primary'
        },
        native: {
            type: Boolean,
            default: false
        }
    },
    data: function data() {
        return {
            newValue: this.value,
            dragDropFocus: false,
            _elementRef: 'input'
        };
    },

    watch: {
        /**
         * When v-model is changed:
         *   1. Set internal value.
         *   2. Reset input value if array is empty
         *   3. If it's invalid, validate again.
         */
        value: function value(_value) {
            this.newValue = _value;
            if (!this.newValue || Array.isArray(this.newValue) && this.newValue.length === 0) {
                this.$refs.input.value = null;
            }
            !this.isValid && !this.dragDrop && this.checkHtml5Validity();
        }
    },
    methods: {

        /**
         * Listen change event on input type 'file',
         * emit 'input' event and validate
         */
        onFileChange: function onFileChange(event) {
            if (this.disabled || this.loading) return;
            if (this.dragDrop) {
                this.updateDragDropFocus(false);
            }
            var value = event.target.files || event.dataTransfer.files;
            if (value) {
                if (value.length === 0) {
                    this.newValue = null;
                } else if (!this.multiple) {
                    // only one element in case drag drop mode and isn't multiple
                    if (this.dragDrop && value.length !== 1) return false;else {
                        var file = value[0];
                        if (this.checkType(file)) {
                            this.newValue = file;
                        }
                    }
                } else {
                    // always new values if native or undefined local
                    if (this.native || !this.newValue) {
                        this.newValue = [];
                    }
                    for (var i = 0; i < value.length; i++) {
                        var _file = value[i];
                        if (this.checkType(_file)) {
                            this.newValue.push(_file);
                        }
                    }
                }
            }
            this.$emit('input', this.newValue);
            !this.dragDrop && this.checkHtml5Validity();
        },


        /**
         * Listen drag-drop to update internal variable
         */
        updateDragDropFocus: function updateDragDropFocus(focus) {
            if (!this.disabled && !this.loading) {
                this.dragDropFocus = focus;
            }
        },


        /**
         * Check mime type of file
         */
        checkType: function checkType(file) {
            if (!this.accept) return true;
            var types = this.accept.split(',');
            if (types.length === 0) return true;
            var valid = false;
            for (var i = 0; i < types.length && !valid; i++) {
                var type = types[i].trim();
                if (type) {
                    if (type.substring(0, 1) === '.') {
                        // check extension
                        var extIndex = file.name.lastIndexOf('.');
                        if (extIndex >= 0 && file.name.substring(extIndex) === type) {
                            valid = true;
                        }
                    } else {
                        // check mime type
                        if (file.type.match(type)) {
                            valid = true;
                        }
                    }
                }
            }
            return valid;
        }
    }
});

/***/ }),
/* 216 */
/***/ (function(module, exports) {

module.exports={render:function (){var _vm=this;var _h=_vm.$createElement;var _c=_vm._self._c||_h;
  return _c('label', {
    staticClass: "upload control"
  }, [(!_vm.dragDrop) ? [_vm._t("default")] : _c('div', {
    staticClass: "upload-draggable",
    class: [_vm.type, {
      'is-loading': _vm.loading,
      'is-disabled': _vm.disabled,
      'is-hovered': _vm.dragDropFocus
    }],
    on: {
      "dragover": function($event) {
        $event.preventDefault();
        _vm.updateDragDropFocus(true)
      },
      "dragleave": function($event) {
        $event.preventDefault();
        _vm.updateDragDropFocus(false)
      },
      "dragenter": function($event) {
        $event.preventDefault();
        _vm.updateDragDropFocus(true)
      },
      "drop": function($event) {
        $event.preventDefault();
        _vm.onFileChange($event)
      }
    }
  }, [_vm._t("default")], 2), _vm._v(" "), _c('input', _vm._b({
    ref: "input",
    attrs: {
      "type": "file",
      "multiple": _vm.multiple,
      "accept": _vm.accept,
      "disabled": _vm.disabled
    },
    on: {
      "change": _vm.onFileChange
    }
  }, 'input', _vm.$attrs, false))], 2)
},staticRenderFns: []}

/***/ })
/******/ ]);
});

/***/ }),

/***/ "./node_modules/chartkick/dist/chartkick.esm.js":
/*!******************************************************!*\
  !*** ./node_modules/chartkick/dist/chartkick.esm.js ***!
  \******************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/*
 * Chartkick.js
 * Create beautiful charts with one line of JavaScript
 * https://github.com/ankane/chartkick.js
 * v3.0.2
 * MIT License
 */

function isArray(variable) {
  return Object.prototype.toString.call(variable) === "[object Array]";
}

function isFunction(variable) {
  return variable instanceof Function;
}

function isPlainObject(variable) {
  return !isFunction(variable) && variable instanceof Object;
}

// https://github.com/madrobby/zepto/blob/master/src/zepto.js
function extend(target, source) {
  var key;
  for (key in source) {
    if (isPlainObject(source[key]) || isArray(source[key])) {
      if (isPlainObject(source[key]) && !isPlainObject(target[key])) {
        target[key] = {};
      }
      if (isArray(source[key]) && !isArray(target[key])) {
        target[key] = [];
      }
      extend(target[key], source[key]);
    } else if (source[key] !== undefined) {
      target[key] = source[key];
    }
  }
}

function merge(obj1, obj2) {
  var target = {};
  extend(target, obj1);
  extend(target, obj2);
  return target;
}

var DATE_PATTERN = /^(\d\d\d\d)(-)?(\d\d)(-)?(\d\d)$/i;

// https://github.com/Do/iso8601.js
var ISO8601_PATTERN = /(\d\d\d\d)(-)?(\d\d)(-)?(\d\d)(T)?(\d\d)(:)?(\d\d)?(:)?(\d\d)?([.,]\d+)?($|Z|([+-])(\d\d)(:)?(\d\d)?)/i;
var DECIMAL_SEPARATOR = String(1.5).charAt(1);

function parseISO8601(input) {
  var day, hour, matches, milliseconds, minutes, month, offset, result, seconds, type, year;
  type = Object.prototype.toString.call(input);
  if (type === "[object Date]") {
    return input;
  }
  if (type !== "[object String]") {
    return;
  }
  matches = input.match(ISO8601_PATTERN);
  if (matches) {
    year = parseInt(matches[1], 10);
    month = parseInt(matches[3], 10) - 1;
    day = parseInt(matches[5], 10);
    hour = parseInt(matches[7], 10);
    minutes = matches[9] ? parseInt(matches[9], 10) : 0;
    seconds = matches[11] ? parseInt(matches[11], 10) : 0;
    milliseconds = matches[12] ? parseFloat(DECIMAL_SEPARATOR + matches[12].slice(1)) * 1000 : 0;
    result = Date.UTC(year, month, day, hour, minutes, seconds, milliseconds);
    if (matches[13] && matches[14]) {
      offset = matches[15] * 60;
      if (matches[17]) {
        offset += parseInt(matches[17], 10);
      }
      offset *= matches[14] === "-" ? -1 : 1;
      result -= offset * 60 * 1000;
    }
    return new Date(result);
  }
}
// end iso8601.js

function negativeValues(series) {
  var i, j, data;
  for (i = 0; i < series.length; i++) {
    data = series[i].data;
    for (j = 0; j < data.length; j++) {
      if (data[j][1] < 0) {
        return true;
      }
    }
  }
  return false;
}

function toStr(n) {
  return "" + n;
}

function toFloat(n) {
  return parseFloat(n);
}

function toDate(n) {
  var matches, year, month, day;
  if (typeof n !== "object") {
    if (typeof n === "number") {
      n = new Date(n * 1000); // ms
    } else {
      n = toStr(n);
      if ((matches = n.match(DATE_PATTERN))) {
      year = parseInt(matches[1], 10);
      month = parseInt(matches[3], 10) - 1;
      day = parseInt(matches[5], 10);
      return new Date(year, month, day);
      } else { // str
        // try our best to get the str into iso8601
        // TODO be smarter about this
        var str = n.replace(/ /, "T").replace(" ", "").replace("UTC", "Z");
        n = parseISO8601(str) || new Date(n);
      }
    }
  }
  return n;
}

function toArr(n) {
  if (!isArray(n)) {
    var arr = [], i;
    for (i in n) {
      if (n.hasOwnProperty(i)) {
        arr.push([i, n[i]]);
      }
    }
    n = arr;
  }
  return n;
}

function jsOptionsFunc(defaultOptions, hideLegend, setTitle, setMin, setMax, setStacked, setXtitle, setYtitle) {
  return function (chart, opts, chartOptions) {
    var series = chart.data;
    var options = merge({}, defaultOptions);
    options = merge(options, chartOptions || {});

    if (chart.hideLegend || "legend" in opts) {
      hideLegend(options, opts.legend, chart.hideLegend);
    }

    if (opts.title) {
      setTitle(options, opts.title);
    }

    // min
    if ("min" in opts) {
      setMin(options, opts.min);
    } else if (!negativeValues(series)) {
      setMin(options, 0);
    }

    // max
    if (opts.max) {
      setMax(options, opts.max);
    }

    if ("stacked" in opts) {
      setStacked(options, opts.stacked);
    }

    if (opts.colors) {
      options.colors = opts.colors;
    }

    if (opts.xtitle) {
      setXtitle(options, opts.xtitle);
    }

    if (opts.ytitle) {
      setYtitle(options, opts.ytitle);
    }

    // merge library last
    options = merge(options, opts.library || {});

    return options;
  };
}

function sortByTime(a, b) {
  return a[0].getTime() - b[0].getTime();
}

function sortByNumberSeries(a, b) {
  return a[0] - b[0];
}

function sortByNumber(a, b) {
  return a - b;
}

function isMinute(d) {
  return d.getMilliseconds() === 0 && d.getSeconds() === 0;
}

function isHour(d) {
  return isMinute(d) && d.getMinutes() === 0;
}

function isDay(d) {
  return isHour(d) && d.getHours() === 0;
}

function isWeek(d, dayOfWeek) {
  return isDay(d) && d.getDay() === dayOfWeek;
}

function isMonth(d) {
  return isDay(d) && d.getDate() === 1;
}

function isYear(d) {
  return isMonth(d) && d.getMonth() === 0;
}

function isDate(obj) {
  return !isNaN(toDate(obj)) && toStr(obj).length >= 6;
}

function isNumber(obj) {
  return typeof obj === "number";
}

function formatValue(pre, value, options) {
  pre = pre || "";
  if (options.prefix) {
    if (value < 0) {
      value = value * -1;
      pre += "-";
    }
    pre += options.prefix;
  }

  if (options.thousands || options.decimal) {
    value = toStr(value);
    var parts = value.split(".");
    value = parts[0];
    if (options.thousands) {
      value = value.replace(/\B(?=(\d{3})+(?!\d))/g, options.thousands);
    }
    if (parts.length > 1) {
      value += (options.decimal || ".") + parts[1];
    }
  }

  return pre + value + (options.suffix || "");
}

function seriesOption(chart, series, option) {
  if (option in series) {
    return series[option];
  } else if (option in chart.options) {
    return chart.options[option];
  }
  return null;
}

function allZeros(data) {
  var i, j, d;
  for (i = 0; i < data.length; i++) {
    d = data[i].data;
    for (j = 0; j < d.length; j++) {
      if (d[j][1] != 0) {
        return false;
      }
    }
  }
  return true;
}

var baseOptions = {
  maintainAspectRatio: false,
  animation: false,
  tooltips: {
    displayColors: false,
    callbacks: {}
  },
  legend: {},
  title: {fontSize: 20, fontColor: "#333"}
};

var defaultOptions = {
  scales: {
    yAxes: [
      {
        ticks: {
          maxTicksLimit: 4
        },
        scaleLabel: {
          fontSize: 16,
          // fontStyle: "bold",
          fontColor: "#333"
        }
      }
    ],
    xAxes: [
      {
        gridLines: {
          drawOnChartArea: false
        },
        scaleLabel: {
          fontSize: 16,
          // fontStyle: "bold",
          fontColor: "#333"
        },
        time: {},
        ticks: {}
      }
    ]
  }
};

// http://there4.io/2012/05/02/google-chart-color-list/
var defaultColors = [
  "#3366CC", "#DC3912", "#FF9900", "#109618", "#990099", "#3B3EAC", "#0099C6",
  "#DD4477", "#66AA00", "#B82E2E", "#316395", "#994499", "#22AA99", "#AAAA11",
  "#6633CC", "#E67300", "#8B0707", "#329262", "#5574A6", "#651067"
];

var hideLegend = function (options, legend, hideLegend) {
  if (legend !== undefined) {
    options.legend.display = !!legend;
    if (legend && legend !== true) {
      options.legend.position = legend;
    }
  } else if (hideLegend) {
    options.legend.display = false;
  }
};

var setTitle = function (options, title) {
  options.title.display = true;
  options.title.text = title;
};

var setMin = function (options, min) {
  if (min !== null) {
    options.scales.yAxes[0].ticks.min = toFloat(min);
  }
};

var setMax = function (options, max) {
  options.scales.yAxes[0].ticks.max = toFloat(max);
};

var setBarMin = function (options, min) {
  if (min !== null) {
    options.scales.xAxes[0].ticks.min = toFloat(min);
  }
};

var setBarMax = function (options, max) {
  options.scales.xAxes[0].ticks.max = toFloat(max);
};

var setStacked = function (options, stacked) {
  options.scales.xAxes[0].stacked = !!stacked;
  options.scales.yAxes[0].stacked = !!stacked;
};

var setXtitle = function (options, title) {
  options.scales.xAxes[0].scaleLabel.display = true;
  options.scales.xAxes[0].scaleLabel.labelString = title;
};

var setYtitle = function (options, title) {
  options.scales.yAxes[0].scaleLabel.display = true;
  options.scales.yAxes[0].scaleLabel.labelString = title;
};

// https://stackoverflow.com/questions/5623838/rgb-to-hex-and-hex-to-rgb
var addOpacity = function(hex, opacity) {
  var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
  return result ? "rgba(" + parseInt(result[1], 16) + ", " + parseInt(result[2], 16) + ", " + parseInt(result[3], 16) + ", " + opacity + ")" : hex;
};

var setLabelSize = function (chart, data, options) {
  var maxLabelSize = Math.ceil(chart.element.offsetWidth / 4.0 / data.labels.length);
  if (maxLabelSize > 25) {
    maxLabelSize = 25;
  } else if (maxLabelSize < 10) {
    maxLabelSize = 10;
  }
  if (!options.scales.xAxes[0].ticks.callback) {
    options.scales.xAxes[0].ticks.callback = function (value) {
      value = toStr(value);
      if (value.length > maxLabelSize) {
        return value.substring(0, maxLabelSize - 2) + "...";
      } else {
        return value;
      }
    };
  }
};

var setFormatOptions = function(chart, options, chartType) {
  var formatOptions = {
    prefix: chart.options.prefix,
    suffix: chart.options.suffix,
    thousands: chart.options.thousands,
    decimal: chart.options.decimal
  };

  if (chartType !== "pie") {
    var myAxes = options.scales.yAxes;
    if (chartType === "bar") {
      myAxes = options.scales.xAxes;
    }

    if (!myAxes[0].ticks.callback) {
      myAxes[0].ticks.callback = function (value) {
        return formatValue("", value, formatOptions);
      };
    }
  }

  if (!options.tooltips.callbacks.label) {
    if (chartType === "scatter") {
      options.tooltips.callbacks.label = function (item, data) {
        var label = data.datasets[item.datasetIndex].label || '';
        if (label) {
          label += ': ';
        }
        return label + '(' + item.xLabel + ', ' + item.yLabel + ')';
      };
    } else if (chartType === "bubble") {
      options.tooltips.callbacks.label = function (item, data) {
        var label = data.datasets[item.datasetIndex].label || '';
        if (label) {
          label += ': ';
        }
        var dataPoint = data.datasets[item.datasetIndex].data[item.index];
        return label + '(' + item.xLabel + ', ' + item.yLabel + ', ' + dataPoint.v + ')';
      };
    } else if (chartType === "pie") {
      // need to use separate label for pie charts
      options.tooltips.callbacks.label = function (tooltipItem, data) {
        var dataLabel = data.labels[tooltipItem.index];
        var value = ': ';

        if (isArray(dataLabel)) {
          // show value on first line of multiline label
          // need to clone because we are changing the value
          dataLabel = dataLabel.slice();
          dataLabel[0] += value;
        } else {
          dataLabel += value;
        }

        return formatValue(dataLabel, data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index], formatOptions);
      };
    } else {
      var valueLabel = chartType === "bar" ? "xLabel" : "yLabel";
      options.tooltips.callbacks.label = function (tooltipItem, data) {
        var label = data.datasets[tooltipItem.datasetIndex].label || '';
        if (label) {
          label += ': ';
        }
        return formatValue(label, tooltipItem[valueLabel], formatOptions);
      };
    }
  }
};

var jsOptions = jsOptionsFunc(merge(baseOptions, defaultOptions), hideLegend, setTitle, setMin, setMax, setStacked, setXtitle, setYtitle);

var createDataTable = function (chart, options, chartType) {
  var datasets = [];
  var labels = [];

  var colors = chart.options.colors || defaultColors;

  var day = true;
  var week = true;
  var dayOfWeek;
  var month = true;
  var year = true;
  var hour = true;
  var minute = true;

  var series = chart.data;

  var max = 0;
  if (chartType === "bubble") {
    for (var i$1 = 0; i$1 < series.length; i$1++) {
      var s$1 = series[i$1];
      for (var j$1 = 0; j$1 < s$1.data.length; j$1++) {
        if (s$1.data[j$1][2] > max) {
          max = s$1.data[j$1][2];
        }
      }
    }
  }

  var i, j, s, d, key, rows = [], rows2 = [];

  if (chartType === "bar" || chartType === "column" || (chart.xtype !== "number" && chart.xtype !== "bubble")) {
    var sortedLabels = [];

    for (i = 0; i < series.length; i++) {
      s = series[i];

      for (j = 0; j < s.data.length; j++) {
        d = s.data[j];
        key = chart.xtype == "datetime" ? d[0].getTime() : d[0];
        if (!rows[key]) {
          rows[key] = new Array(series.length);
        }
        rows[key][i] = toFloat(d[1]);
        if (sortedLabels.indexOf(key) === -1) {
          sortedLabels.push(key);
        }
      }
    }

    if (chart.xtype === "datetime" || chart.xtype === "number") {
      sortedLabels.sort(sortByNumber);
    }

    for (j = 0; j < series.length; j++) {
      rows2.push([]);
    }

    var value;
    var k;
    for (k = 0; k < sortedLabels.length; k++) {
      i = sortedLabels[k];
      if (chart.xtype === "datetime") {
        value = new Date(toFloat(i));
        // TODO make this efficient
        day = day && isDay(value);
        if (!dayOfWeek) {
          dayOfWeek = value.getDay();
        }
        week = week && isWeek(value, dayOfWeek);
        month = month && isMonth(value);
        year = year && isYear(value);
        hour = hour && isHour(value);
        minute = minute && isMinute(value);
      } else {
        value = i;
      }
      labels.push(value);
      for (j = 0; j < series.length; j++) {
        // Chart.js doesn't like undefined
        rows2[j].push(rows[i][j] === undefined ? null : rows[i][j]);
      }
    }
  } else {
    for (var i$2 = 0; i$2 < series.length; i$2++) {
      var s$2 = series[i$2];
      var d$1 = [];
      for (var j$2 = 0; j$2 < s$2.data.length; j$2++) {
        var point = {
          x: toFloat(s$2.data[j$2][0]),
          y: toFloat(s$2.data[j$2][1])
        };
        if (chartType === "bubble") {
          point.r = toFloat(s$2.data[j$2][2]) * 20 / max;
          // custom attribute, for tooltip
          point.v = s$2.data[j$2][2];
        }
        d$1.push(point);
      }
      rows2.push(d$1);
    }
  }

  for (i = 0; i < series.length; i++) {
    s = series[i];

    var color = s.color || colors[i];
    var backgroundColor = chartType !== "line" ? addOpacity(color, 0.5) : color;

    var dataset = {
      label: s.name || "",
      data: rows2[i],
      fill: chartType === "area",
      borderColor: color,
      backgroundColor: backgroundColor,
      pointBackgroundColor: color,
      borderWidth: 2,
      pointHoverBackgroundColor: color
    };

    if (s.stack) {
      dataset.stack = s.stack;
    }

    var curve = seriesOption(chart, s, "curve");
    if (curve === false) {
      dataset.lineTension = 0;
    }

    var points = seriesOption(chart, s, "points");
    if (points === false) {
      dataset.pointRadius = 0;
      dataset.pointHitRadius = 5;
    }

    dataset = merge(dataset, chart.options.dataset || {});
    dataset = merge(dataset, s.library || {});
    dataset = merge(dataset, s.dataset || {});

    datasets.push(dataset);
  }

  if (chart.xtype === "datetime" && labels.length > 0) {
    var minTime = labels[0].getTime();
    var maxTime = labels[0].getTime();
    for (i = 1; i < labels.length; i++) {
      var value$1 = labels[i].getTime();
      if (value$1 < minTime) {
        minTime = value$1;
      }
      if (value$1 > maxTime) {
        maxTime = value$1;
      }
    }

    var timeDiff = (maxTime - minTime) / (86400 * 1000.0);

    if (!options.scales.xAxes[0].time.unit) {
      var step;
      if (year || timeDiff > 365 * 10) {
        options.scales.xAxes[0].time.unit = "year";
        step = 365;
      } else if (month || timeDiff > 30 * 10) {
        options.scales.xAxes[0].time.unit = "month";
        step = 30;
      } else if (day || timeDiff > 10) {
        options.scales.xAxes[0].time.unit = "day";
        step = 1;
      } else if (hour || timeDiff > 0.5) {
        options.scales.xAxes[0].time.displayFormats = {hour: "MMM D, h a"};
        options.scales.xAxes[0].time.unit = "hour";
        step = 1 / 24.0;
      } else if (minute) {
        options.scales.xAxes[0].time.displayFormats = {minute: "h:mm a"};
        options.scales.xAxes[0].time.unit = "minute";
        step = 1 / 24.0 / 60.0;
      }

      if (step && timeDiff > 0) {
        var unitStepSize = Math.ceil(timeDiff / step / (chart.element.offsetWidth / 100.0));
        if (week && step === 1) {
          unitStepSize = Math.ceil(unitStepSize / 7.0) * 7;
        }
        options.scales.xAxes[0].time.unitStepSize = unitStepSize;
      }
    }

    if (!options.scales.xAxes[0].time.tooltipFormat) {
      if (day) {
        options.scales.xAxes[0].time.tooltipFormat = "ll";
      } else if (hour) {
        options.scales.xAxes[0].time.tooltipFormat = "MMM D, h a";
      } else if (minute) {
        options.scales.xAxes[0].time.tooltipFormat = "h:mm a";
      }
    }
  }

  var data = {
    labels: labels,
    datasets: datasets
  };

  return data;
};

var defaultExport = function defaultExport(library) {
  this.name = "chartjs";
  this.library = library;
};

defaultExport.prototype.renderLineChart = function renderLineChart (chart, chartType) {
  var chartOptions = {};
  // fix for https://github.com/chartjs/Chart.js/issues/2441
  if (!chart.options.max && allZeros(chart.data)) {
    chartOptions.max = 1;
  }

  var options = jsOptions(chart, merge(chartOptions, chart.options));
  setFormatOptions(chart, options, chartType);

  var data = createDataTable(chart, options, chartType || "line");

  if (chart.xtype === "number") {
    options.scales.xAxes[0].type = "linear";
    options.scales.xAxes[0].position = "bottom";
  } else {
    options.scales.xAxes[0].type = chart.xtype === "string" ? "category" : "time";
  }

  this.drawChart(chart, "line", data, options);
};

defaultExport.prototype.renderPieChart = function renderPieChart (chart) {
  var options = merge({}, baseOptions);
  if (chart.options.donut) {
    options.cutoutPercentage = 50;
  }

  if ("legend" in chart.options) {
    hideLegend(options, chart.options.legend);
  }

  if (chart.options.title) {
    setTitle(options, chart.options.title);
  }

  options = merge(options, chart.options.library || {});
  setFormatOptions(chart, options, "pie");

  var labels = [];
  var values = [];
  for (var i = 0; i < chart.data.length; i++) {
    var point = chart.data[i];
    labels.push(point[0]);
    values.push(point[1]);
  }

  var dataset = {
    data: values,
    backgroundColor: chart.options.colors || defaultColors
  };
  dataset = merge(dataset, chart.options.dataset || {});

  var data = {
    labels: labels,
    datasets: [dataset]
  };

  this.drawChart(chart, "pie", data, options);
};

defaultExport.prototype.renderColumnChart = function renderColumnChart (chart, chartType) {
  var options;
  if (chartType === "bar") {
    options = jsOptionsFunc(merge(baseOptions, defaultOptions), hideLegend, setTitle, setBarMin, setBarMax, setStacked, setXtitle, setYtitle)(chart, chart.options);
  } else {
    options = jsOptions(chart, chart.options);
  }
  setFormatOptions(chart, options, chartType);
  var data = createDataTable(chart, options, "column");
  if (chartType !== "bar") {
    setLabelSize(chart, data, options);
  }
  this.drawChart(chart, (chartType === "bar" ? "horizontalBar" : "bar"), data, options);
};

defaultExport.prototype.renderAreaChart = function renderAreaChart (chart) {
  this.renderLineChart(chart, "area");
};

defaultExport.prototype.renderBarChart = function renderBarChart (chart) {
  this.renderColumnChart(chart, "bar");
};

defaultExport.prototype.renderScatterChart = function renderScatterChart (chart, chartType) {
  chartType = chartType || "scatter";

  var options = jsOptions(chart, chart.options);
  setFormatOptions(chart, options, chartType);

  if (!("showLines" in options)) {
    options.showLines = false;
  }

  var data = createDataTable(chart, options, chartType);

  options.scales.xAxes[0].type = "linear";
  options.scales.xAxes[0].position = "bottom";

  this.drawChart(chart, chartType, data, options);
};

defaultExport.prototype.renderBubbleChart = function renderBubbleChart (chart) {
  this.renderScatterChart(chart, "bubble");
};

defaultExport.prototype.destroy = function destroy (chart) {
  if (chart.chart) {
    chart.chart.destroy();
  }
};

defaultExport.prototype.drawChart = function drawChart (chart, type, data, options) {
  this.destroy(chart);

  var chartOptions = {
    type: type,
    data: data,
    options: options
  };

  if (chart.options.code) {
    window.console.log("new Chart(ctx, " + JSON.stringify(chartOptions) + ");");
  }

  chart.element.innerHTML = "<canvas></canvas>";
  var ctx = chart.element.getElementsByTagName("CANVAS")[0];
  chart.chart = new this.library(ctx, chartOptions);
};

var defaultOptions$1 = {
  chart: {},
  xAxis: {
    title: {
      text: null
    },
    labels: {
      style: {
        fontSize: "12px"
      }
    }
  },
  yAxis: {
    title: {
      text: null
    },
    labels: {
      style: {
        fontSize: "12px"
      }
    }
  },
  title: {
    text: null
  },
  credits: {
    enabled: false
  },
  legend: {
    borderWidth: 0
  },
  tooltip: {
    style: {
      fontSize: "12px"
    }
  },
  plotOptions: {
    areaspline: {},
    series: {
      marker: {}
    }
  }
};

var hideLegend$1 = function (options, legend, hideLegend) {
  if (legend !== undefined) {
    options.legend.enabled = !!legend;
    if (legend && legend !== true) {
      if (legend === "top" || legend === "bottom") {
        options.legend.verticalAlign = legend;
      } else {
        options.legend.layout = "vertical";
        options.legend.verticalAlign = "middle";
        options.legend.align = legend;
      }
    }
  } else if (hideLegend) {
    options.legend.enabled = false;
  }
};

var setTitle$1 = function (options, title) {
  options.title.text = title;
};

var setMin$1 = function (options, min) {
  options.yAxis.min = min;
};

var setMax$1 = function (options, max) {
  options.yAxis.max = max;
};

var setStacked$1 = function (options, stacked) {
  options.plotOptions.series.stacking = stacked ? (stacked === true ? "normal" : stacked) : null;
};

var setXtitle$1 = function (options, title) {
  options.xAxis.title.text = title;
};

var setYtitle$1 = function (options, title) {
  options.yAxis.title.text = title;
};

var jsOptions$1 = jsOptionsFunc(defaultOptions$1, hideLegend$1, setTitle$1, setMin$1, setMax$1, setStacked$1, setXtitle$1, setYtitle$1);

var setFormatOptions$1 = function(chart, options, chartType) {
  var formatOptions = {
    prefix: chart.options.prefix,
    suffix: chart.options.suffix,
    thousands: chart.options.thousands,
    decimal: chart.options.decimal
  };

  if (chartType !== "pie" && !options.yAxis.labels.formatter) {
    options.yAxis.labels.formatter = function () {
      return formatValue("", this.value, formatOptions);
    };
  }

  if (!options.tooltip.pointFormatter) {
    options.tooltip.pointFormatter = function () {
      return '<span style="color:' + this.color + '>\u25CF</span> ' + formatValue(this.series.name + ': <b>', this.y, formatOptions) + '</b><br/>';
    };
  }
};

var defaultExport$1 = function defaultExport(library) {
  this.name = "highcharts";
  this.library = library;
};

defaultExport$1.prototype.renderLineChart = function renderLineChart (chart, chartType) {
  chartType = chartType || "spline";
  var chartOptions = {};
  if (chartType === "areaspline") {
    chartOptions = {
      plotOptions: {
        areaspline: {
          stacking: "normal"
        },
        area: {
          stacking: "normal"
        },
        series: {
          marker: {
            enabled: false
          }
        }
      }
    };
  }

  if (chart.options.curve === false) {
    if (chartType === "areaspline") {
      chartType = "area";
    } else if (chartType === "spline") {
      chartType = "line";
    }
  }

  var options = jsOptions$1(chart, chart.options, chartOptions), data, i, j;
  options.xAxis.type = chart.xtype === "string" ? "category" : (chart.xtype === "number" ? "linear" : "datetime");
  if (!options.chart.type) {
    options.chart.type = chartType;
  }
  setFormatOptions$1(chart, options, chartType);

  var series = chart.data;
  for (i = 0; i < series.length; i++) {
    series[i].name = series[i].name || "Value";
    data = series[i].data;
    if (chart.xtype === "datetime") {
      for (j = 0; j < data.length; j++) {
        data[j][0] = data[j][0].getTime();
      }
    }
    series[i].marker = {symbol: "circle"};
    if (chart.options.points === false) {
      series[i].marker.enabled = false;
    }
  }

  this.drawChart(chart, series, options);
};

defaultExport$1.prototype.renderScatterChart = function renderScatterChart (chart) {
  var options = jsOptions$1(chart, chart.options, {});
  options.chart.type = "scatter";
  this.drawChart(chart, chart.data, options);
};

defaultExport$1.prototype.renderPieChart = function renderPieChart (chart) {
  var chartOptions = merge(defaultOptions$1, {});

  if (chart.options.colors) {
    chartOptions.colors = chart.options.colors;
  }
  if (chart.options.donut) {
    chartOptions.plotOptions = {pie: {innerSize: "50%"}};
  }

  if ("legend" in chart.options) {
    hideLegend$1(chartOptions, chart.options.legend);
  }

  if (chart.options.title) {
    setTitle$1(chartOptions, chart.options.title);
  }

  var options = merge(chartOptions, chart.options.library || {});
  setFormatOptions$1(chart, options, "pie");
  var series = [{
    type: "pie",
    name: chart.options.label || "Value",
    data: chart.data
  }];

  this.drawChart(chart, series, options);
};

defaultExport$1.prototype.renderColumnChart = function renderColumnChart (chart, chartType) {
  chartType = chartType || "column";
  var series = chart.data;
  var options = jsOptions$1(chart, chart.options), i, j, s, d, rows = [], categories = [];
  options.chart.type = chartType;
  setFormatOptions$1(chart, options, chartType);

  for (i = 0; i < series.length; i++) {
    s = series[i];

    for (j = 0; j < s.data.length; j++) {
      d = s.data[j];
      if (!rows[d[0]]) {
        rows[d[0]] = new Array(series.length);
        categories.push(d[0]);
      }
      rows[d[0]][i] = d[1];
    }
  }

  if (chart.xtype === "number") {
    categories.sort(sortByNumber);
  }

  options.xAxis.categories = categories;

  var newSeries = [], d2;
  for (i = 0; i < series.length; i++) {
    d = [];
    for (j = 0; j < categories.length; j++) {
      d.push(rows[categories[j]][i] || 0);
    }

    d2 = {
      name: series[i].name || "Value",
      data: d
    };
    if (series[i].stack) {
      d2.stack = series[i].stack;
    }

    newSeries.push(d2);
  }

  this.drawChart(chart, newSeries, options);
};

defaultExport$1.prototype.renderBarChart = function renderBarChart (chart) {
  this.renderColumnChart(chart, "bar");
};

defaultExport$1.prototype.renderAreaChart = function renderAreaChart (chart) {
  this.renderLineChart(chart, "areaspline");
};

defaultExport$1.prototype.destroy = function destroy (chart) {
  if (chart.chart) {
    chart.chart.destroy();
  }
};

defaultExport$1.prototype.drawChart = function drawChart (chart, data, options) {
  this.destroy(chart);

  options.chart.renderTo = chart.element.id;
  options.series = data;

  if (chart.options.code) {
    window.console.log("new Highcharts.Chart(" + JSON.stringify(options) + ");");
  }

  chart.chart = new this.library.Chart(options);
};

var loaded = {};
var callbacks = [];

// Set chart options
var defaultOptions$2 = {
  chartArea: {},
  fontName: "'Lucida Grande', 'Lucida Sans Unicode', Verdana, Arial, Helvetica, sans-serif",
  pointSize: 6,
  legend: {
    textStyle: {
      fontSize: 12,
      color: "#444"
    },
    alignment: "center",
    position: "right"
  },
  curveType: "function",
  hAxis: {
    textStyle: {
      color: "#666",
      fontSize: 12
    },
    titleTextStyle: {},
    gridlines: {
      color: "transparent"
    },
    baselineColor: "#ccc",
    viewWindow: {}
  },
  vAxis: {
    textStyle: {
      color: "#666",
      fontSize: 12
    },
    titleTextStyle: {},
    baselineColor: "#ccc",
    viewWindow: {}
  },
  tooltip: {
    textStyle: {
      color: "#666",
      fontSize: 12
    }
  }
};

var hideLegend$2 = function (options, legend, hideLegend) {
  if (legend !== undefined) {
    var position;
    if (!legend) {
      position = "none";
    } else if (legend === true) {
      position = "right";
    } else {
      position = legend;
    }
    options.legend.position = position;
  } else if (hideLegend) {
    options.legend.position = "none";
  }
};

var setTitle$2 = function (options, title) {
  options.title = title;
  options.titleTextStyle = {color: "#333", fontSize: "20px"};
};

var setMin$2 = function (options, min) {
  options.vAxis.viewWindow.min = min;
};

var setMax$2 = function (options, max) {
  options.vAxis.viewWindow.max = max;
};

var setBarMin$1 = function (options, min) {
  options.hAxis.viewWindow.min = min;
};

var setBarMax$1 = function (options, max) {
  options.hAxis.viewWindow.max = max;
};

var setStacked$2 = function (options, stacked) {
  options.isStacked = stacked ? stacked : false;
};

var setXtitle$2 = function (options, title) {
  options.hAxis.title = title;
  options.hAxis.titleTextStyle.italic = false;
};

var setYtitle$2 = function (options, title) {
  options.vAxis.title = title;
  options.vAxis.titleTextStyle.italic = false;
};

var jsOptions$2 = jsOptionsFunc(defaultOptions$2, hideLegend$2, setTitle$2, setMin$2, setMax$2, setStacked$2, setXtitle$2, setYtitle$2);

var resize = function (callback) {
  if (window.attachEvent) {
    window.attachEvent("onresize", callback);
  } else if (window.addEventListener) {
    window.addEventListener("resize", callback, true);
  }
  callback();
};

var defaultExport$2 = function defaultExport(library) {
  this.name = "google";
  this.library = library;
};

defaultExport$2.prototype.renderLineChart = function renderLineChart (chart) {
    var this$1 = this;

  this.waitForLoaded(chart, function () {
    var chartOptions = {};

    if (chart.options.curve === false) {
      chartOptions.curveType = "none";
    }

    if (chart.options.points === false) {
      chartOptions.pointSize = 0;
    }

    var options = jsOptions$2(chart, chart.options, chartOptions);
    var data = this$1.createDataTable(chart.data, chart.xtype);

    this$1.drawChart(chart, "LineChart", data, options);
  });
};

defaultExport$2.prototype.renderPieChart = function renderPieChart (chart) {
    var this$1 = this;

  this.waitForLoaded(chart, function () {
    var chartOptions = {
      chartArea: {
        top: "10%",
        height: "80%"
      },
      legend: {}
    };
    if (chart.options.colors) {
      chartOptions.colors = chart.options.colors;
    }
    if (chart.options.donut) {
      chartOptions.pieHole = 0.5;
    }
    if ("legend" in chart.options) {
      hideLegend$2(chartOptions, chart.options.legend);
    }
    if (chart.options.title) {
      setTitle$2(chartOptions, chart.options.title);
    }
    var options = merge(merge(defaultOptions$2, chartOptions), chart.options.library || {});

    var data = new this$1.library.visualization.DataTable();
    data.addColumn("string", "");
    data.addColumn("number", "Value");
    data.addRows(chart.data);

    this$1.drawChart(chart, "PieChart", data, options);
  });
};

defaultExport$2.prototype.renderColumnChart = function renderColumnChart (chart) {
    var this$1 = this;

  this.waitForLoaded(chart, function () {
    var options = jsOptions$2(chart, chart.options);
    var data = this$1.createDataTable(chart.data, chart.xtype);

    this$1.drawChart(chart, "ColumnChart", data, options);
  });
};

defaultExport$2.prototype.renderBarChart = function renderBarChart (chart) {
    var this$1 = this;

  this.waitForLoaded(chart, function () {
    var chartOptions = {
      hAxis: {
        gridlines: {
          color: "#ccc"
        }
      }
    };
    var options = jsOptionsFunc(defaultOptions$2, hideLegend$2, setTitle$2, setBarMin$1, setBarMax$1, setStacked$2, setXtitle$2, setYtitle$2)(chart, chart.options, chartOptions);
    var data = this$1.createDataTable(chart.data, chart.xtype);

    this$1.drawChart(chart, "BarChart", data, options);
  });
};

defaultExport$2.prototype.renderAreaChart = function renderAreaChart (chart) {
    var this$1 = this;

  this.waitForLoaded(chart, function () {
    var chartOptions = {
      isStacked: true,
      pointSize: 0,
      areaOpacity: 0.5
    };

    var options = jsOptions$2(chart, chart.options, chartOptions);
    var data = this$1.createDataTable(chart.data, chart.xtype);

    this$1.drawChart(chart, "AreaChart", data, options);
  });
};

defaultExport$2.prototype.renderGeoChart = function renderGeoChart (chart) {
    var this$1 = this;

  this.waitForLoaded(chart, function () {
    var chartOptions = {
      legend: "none",
      colorAxis: {
        colors: chart.options.colors || ["#f6c7b6", "#ce502d"]
      }
    };
    var options = merge(merge(defaultOptions$2, chartOptions), chart.options.library || {});

    var data = new this$1.library.visualization.DataTable();
    data.addColumn("string", "");
    data.addColumn("number", chart.options.label || "Value");
    data.addRows(chart.data);

    this$1.drawChart(chart, "GeoChart", data, options);
  });
};

defaultExport$2.prototype.renderScatterChart = function renderScatterChart (chart) {
    var this$1 = this;

  this.waitForLoaded(chart, function () {
    var chartOptions = {};
    var options = jsOptions$2(chart, chart.options, chartOptions);

    var series = chart.data, rows2 = [], i, j, data, d;
    for (i = 0; i < series.length; i++) {
      series[i].name = series[i].name || "Value";
      d = series[i].data;
      for (j = 0; j < d.length; j++) {
        var row = new Array(series.length + 1);
        row[0] = d[j][0];
        row[i + 1] = d[j][1];
        rows2.push(row);
      }
    }

    data = new this$1.library.visualization.DataTable();
    data.addColumn("number", "");
    for (i = 0; i < series.length; i++) {
      data.addColumn("number", series[i].name);
    }
    data.addRows(rows2);

    this$1.drawChart(chart, "ScatterChart", data, options);
  });
};

defaultExport$2.prototype.renderTimeline = function renderTimeline (chart) {
    var this$1 = this;

  this.waitForLoaded(chart, "timeline", function () {
    var chartOptions = {
      legend: "none"
    };

    if (chart.options.colors) {
      chartOptions.colors = chart.options.colors;
    }
    var options = merge(merge(defaultOptions$2, chartOptions), chart.options.library || {});

    var data = new this$1.library.visualization.DataTable();
    data.addColumn({type: "string", id: "Name"});
    data.addColumn({type: "date", id: "Start"});
    data.addColumn({type: "date", id: "End"});
    data.addRows(chart.data);

    chart.element.style.lineHeight = "normal";

    this$1.drawChart(chart, "Timeline", data, options);
  });
};

defaultExport$2.prototype.destroy = function destroy (chart) {
  if (chart.chart) {
    chart.chart.clearChart();
  }
};

defaultExport$2.prototype.drawChart = function drawChart (chart, type, data, options) {
  this.destroy(chart);

  if (chart.options.code) {
    window.console.log("var data = new google.visualization.DataTable(" + data.toJSON() + ");\nvar chart = new google.visualization." + type + "(element);\nchart.draw(data, " + JSON.stringify(options) + ");");
  }

  chart.chart = new this.library.visualization[type](chart.element);
  resize(function () {
    chart.chart.draw(data, options);
  });
};

defaultExport$2.prototype.waitForLoaded = function waitForLoaded (chart, pack, callback) {
    var this$1 = this;

  if (!callback) {
    callback = pack;
    pack = "corechart";
  }

  callbacks.push({pack: pack, callback: callback});

  if (loaded[pack]) {
    this.runCallbacks();
  } else {
    loaded[pack] = true;

    // https://groups.google.com/forum/#!topic/google-visualization-api/fMKJcyA2yyI
    var loadOptions = {
      packages: [pack],
      callback: function () { this$1.runCallbacks(); }
    };
    var config = chart.__config();
    if (config.language) {
      loadOptions.language = config.language;
    }
    if (pack === "corechart" && config.mapsApiKey) {
      loadOptions.mapsApiKey = config.mapsApiKey;
    }

    this.library.charts.load("current", loadOptions);
  }
};

defaultExport$2.prototype.runCallbacks = function runCallbacks () {
  var cb, call;
  for (var i = 0; i < callbacks.length; i++) {
    cb = callbacks[i];
    call = this.library.visualization && ((cb.pack === "corechart" && this.library.visualization.LineChart) || (cb.pack === "timeline" && this.library.visualization.Timeline));
    if (call) {
      cb.callback();
      callbacks.splice(i, 1);
      i--;
    }
  }
};

// cant use object as key
defaultExport$2.prototype.createDataTable = function createDataTable (series, columnType) {
  var i, j, s, d, key, rows = [], sortedLabels = [];
  for (i = 0; i < series.length; i++) {
    s = series[i];
    series[i].name = series[i].name || "Value";

    for (j = 0; j < s.data.length; j++) {
      d = s.data[j];
      key = (columnType === "datetime") ? d[0].getTime() : d[0];
      if (!rows[key]) {
        rows[key] = new Array(series.length);
        sortedLabels.push(key);
      }
      rows[key][i] = toFloat(d[1]);
    }
  }

  var rows2 = [];
  var day = true;
  var value;
  for (j = 0; j < sortedLabels.length; j++) {
    i = sortedLabels[j];
    if (columnType === "datetime") {
      value = new Date(toFloat(i));
      day = day && isDay(value);
    } else if (columnType === "number") {
      value = toFloat(i);
    } else {
      value = i;
    }
    rows2.push([value].concat(rows[i]));
  }
  if (columnType === "datetime") {
    rows2.sort(sortByTime);
  } else if (columnType === "number") {
    rows2.sort(sortByNumberSeries);

    for (i = 0; i < rows2.length; i++) {
      rows2[i][0] = toStr(rows2[i][0]);
    }

    columnType = "string";
  }

  // create datatable
  var data = new this.library.visualization.DataTable();
  columnType = columnType === "datetime" && day ? "date" : columnType;
  data.addColumn(columnType, "");
  for (i = 0; i < series.length; i++) {
    data.addColumn("number", series[i].name);
  }
  data.addRows(rows2);

  return data;
};

var pendingRequests = [], runningRequests = 0, maxRequests = 4;

function pushRequest(url, success, error) {
  pendingRequests.push([url, success, error]);
  runNext();
}

function runNext() {
  if (runningRequests < maxRequests) {
    var request = pendingRequests.shift();
    if (request) {
      runningRequests++;
      getJSON(request[0], request[1], request[2]);
      runNext();
    }
  }
}

function requestComplete() {
  runningRequests--;
  runNext();
}

function getJSON(url, success, error) {
  ajaxCall(url, success, function (jqXHR, textStatus, errorThrown) {
    var message = (typeof errorThrown === "string") ? errorThrown : errorThrown.message;
    error(message);
  });
}

function ajaxCall(url, success, error) {
  var $ = window.jQuery || window.Zepto || window.$;

  if ($) {
    $.ajax({
      dataType: "json",
      url: url,
      success: success,
      error: error,
      complete: requestComplete
    });
  } else {
    var xhr = new XMLHttpRequest();
    xhr.open("GET", url, true);
    xhr.setRequestHeader("Content-Type", "application/json");
    xhr.onload = function () {
      requestComplete();
      if (xhr.status === 200) {
        success(JSON.parse(xhr.responseText), xhr.statusText, xhr);
      } else {
        error(xhr, "error", xhr.statusText);
      }
    };
    xhr.send();
  }
}

var config = {};
var adapters = [];

// helpers

function setText(element, text) {
  if (document.body.innerText) {
    element.innerText = text;
  } else {
    element.textContent = text;
  }
}

function chartError(element, message) {
  setText(element, "Error Loading Chart: " + message);
  element.style.color = "#ff0000";
}

function errorCatcher(chart) {
  try {
    chart.__render();
  } catch (err) {
    chartError(chart.element, err.message);
    throw err;
  }
}

function fetchDataSource(chart, dataSource) {
  if (typeof dataSource === "string") {
    pushRequest(dataSource, function (data) {
      chart.rawData = data;
      errorCatcher(chart);
    }, function (message) {
      chartError(chart.element, message);
    });
  } else {
    chart.rawData = dataSource;
    errorCatcher(chart);
  }
}

function addDownloadButton(chart) {
  var element = chart.element;
  var link = document.createElement("a");

  var download = chart.options.download;
  if (download === true) {
    download = {};
  } else if (typeof download === "string") {
    download = {filename: download};
  }
  link.download = download.filename || "chart.png"; // https://caniuse.com/download

  link.style.position = "absolute";
  link.style.top = "20px";
  link.style.right = "20px";
  link.style.zIndex = 1000;
  link.style.lineHeight = "20px";
  link.target = "_blank"; // for safari
  var image = document.createElement("img");
  image.alt = "Download";
  image.style.border = "none";
  // icon from font-awesome
  // http://fa2png.io/
  image.src = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABQAAAAUCAMAAAC6V+0/AAABCFBMVEUAAADMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMzMywEsqxAAAAV3RSTlMAAQIDBggJCgsMDQ4PERQaHB0eISIjJCouLzE0OTo/QUJHSUpLTU5PUllhYmltcHh5foWLjI+SlaCio6atr7S1t7m6vsHHyM7R2tze5Obo7fHz9ff5+/1hlxK2AAAA30lEQVQYGUXBhVYCQQBA0TdYWAt2d3d3YWAHyur7/z9xgD16Lw0DW+XKx+1GgX+FRzM3HWQWrHl5N/oapW5RPe0PkBu+UYeICvozTWZVK23Ao04B79oJrOsJDOoxkZoQPWgX29pHpCZEk7rEvQYiNSFq1UMqvlCjJkRBS1R8hb00Vb/TajtBL7nTHE1X1vyMQF732dQhyF2o6SAwrzP06iUQzvwsArlnzcOdrgBhJyHa1QOgO9U1GsKuvjUTjavliZYQ8nNPapG6sap/3nrIdJ6bOWzmX/fy0XVpfzZP3S8OJT3g9EEiJwAAAABJRU5ErkJggg==";
  link.appendChild(image);
  element.style.position = "relative";

  chart.__downloadAttached = true;

  // mouseenter
  chart.__enterEvent = addEvent(element, "mouseover", function(e) {
    var related = e.relatedTarget;
    // check download option again to ensure it wasn't changed
    if ((!related || (related !== this && !childOf(this, related))) && chart.options.download) {
      link.href = chart.toImage(download);
      element.appendChild(link);
    }
  });

  // mouseleave
  chart.__leaveEvent = addEvent(element, "mouseout", function(e) {
    var related = e.relatedTarget;
    if (!related || (related !== this && !childOf(this, related))) {
      if (link.parentNode) {
        link.parentNode.removeChild(link);
      }
    }
  });
}

// https://stackoverflow.com/questions/10149963/adding-event-listener-cross-browser
function addEvent(elem, event, fn) {
  if (elem.addEventListener) {
    elem.addEventListener(event, fn, false);
    return fn;
  } else {
    var fn2 = function() {
      // set the this pointer same as addEventListener when fn is called
      return(fn.call(elem, window.event));
    };
    elem.attachEvent("on" + event, fn2);
    return fn2;
  }
}

function removeEvent(elem, event, fn) {
  if (elem.removeEventListener) {
    elem.removeEventListener(event, fn, false);
  } else {
    elem.detachEvent("on" + event, fn);
  }
}

// https://gist.github.com/shawnbot/4166283
function childOf(p, c) {
  if (p === c) { return false; }
  while (c && c !== p) { c = c.parentNode; }
  return c === p;
}

function getAdapterType(library) {
  if (library) {
    if (library.product === "Highcharts") {
      return defaultExport$1;
    } else if (library.charts) {
      return defaultExport$2;
    } else if (isFunction(library)) {
      return defaultExport;
    }
  }
  throw new Error("Unknown adapter");
}

function addAdapter(library) {
  var adapterType = getAdapterType(library);
  var adapter = new adapterType(library);

  if (adapters.indexOf(adapter) === -1) {
    adapters.push(adapter);
  }
}

function loadAdapters() {
  if ("Chart" in window) {
    addAdapter(window.Chart);
  }

  if ("Highcharts" in window) {
    addAdapter(window.Highcharts);
  }

  if (window.google && window.google.charts) {
    addAdapter(window.google);
  }
}

function dataEmpty(data, chartType) {
  if (chartType === "PieChart" || chartType === "GeoChart" || chartType === "Timeline") {
    return data.length === 0;
  } else {
    for (var i = 0; i < data.length; i++) {
      if (data[i].data.length > 0) {
        return false;
      }
    }
    return true;
  }
}

function renderChart(chartType, chart) {
  if (chart.options.messages && chart.options.messages.empty && dataEmpty(chart.data, chartType)) {
    setText(chart.element, chart.options.messages.empty);
  } else {
    callAdapter(chartType, chart);
    if (chart.options.download && !chart.__downloadAttached && chart.adapter === "chartjs") {
      addDownloadButton(chart);
    }
  }
}

// TODO remove chartType if cross-browser way
// to get the name of the chart class
function callAdapter(chartType, chart) {
  var i, adapter, fnName, adapterName;
  fnName = "render" + chartType;
  adapterName = chart.options.adapter;

  loadAdapters();

  for (i = 0; i < adapters.length; i++) {
    adapter = adapters[i];
    if ((!adapterName || adapterName === adapter.name) && isFunction(adapter[fnName])) {
      chart.adapter = adapter.name;
      chart.__adapterObject = adapter;
      return adapter[fnName](chart);
    }
  }

  if (adapters.length > 0) {
    throw new Error("No charting library found for " + chartType);
  } else {
    throw new Error("No charting libraries found - be sure to include one before your charts");
  }
}

// process data

var toFormattedKey = function (key, keyType) {
  if (keyType === "number") {
    key = toFloat(key);
  } else if (keyType === "datetime") {
    key = toDate(key);
  } else {
    key = toStr(key);
  }
  return key;
};

var formatSeriesData = function (data, keyType) {
  var r = [], key, j;
  for (j = 0; j < data.length; j++) {
    if (keyType === "bubble") {
      r.push([toFloat(data[j][0]), toFloat(data[j][1]), toFloat(data[j][2])]);
    } else {
      key = toFormattedKey(data[j][0], keyType);
      r.push([key, toFloat(data[j][1])]);
    }
  }
  if (keyType === "datetime") {
    r.sort(sortByTime);
  } else if (keyType === "number") {
    r.sort(sortByNumberSeries);
  }
  return r;
};

function detectXType(series, noDatetime) {
  if (detectXTypeWithFunction(series, isNumber)) {
    return "number";
  } else if (!noDatetime && detectXTypeWithFunction(series, isDate)) {
    return "datetime";
  } else {
    return "string";
  }
}

function detectXTypeWithFunction(series, func) {
  var i, j, data;
  for (i = 0; i < series.length; i++) {
    data = toArr(series[i].data);
    for (j = 0; j < data.length; j++) {
      if (!func(data[j][0])) {
        return false;
      }
    }
  }
  return true;
}

// creates a shallow copy of each element of the array
// elements are expected to be objects
function copySeries(series) {
  var newSeries = [], i, j;
  for (i = 0; i < series.length; i++) {
    var copy = {};
    for (j in series[i]) {
      if (series[i].hasOwnProperty(j)) {
        copy[j] = series[i][j];
      }
    }
    newSeries.push(copy);
  }
  return newSeries;
}

function processSeries(chart, keyType, noDatetime) {
  var i;

  var opts = chart.options;
  var series = chart.rawData;

  // see if one series or multiple
  if (!isArray(series) || typeof series[0] !== "object" || isArray(series[0])) {
    series = [{name: opts.label, data: series}];
    chart.hideLegend = true;
  } else {
    chart.hideLegend = false;
  }

  chart.xtype = keyType ? keyType : (opts.discrete ? "string" : detectXType(series, noDatetime));

  // right format
  series = copySeries(series);
  for (i = 0; i < series.length; i++) {
    series[i].data = formatSeriesData(toArr(series[i].data), chart.xtype);
  }

  return series;
}

function processSimple(chart) {
  var perfectData = toArr(chart.rawData), i;
  for (i = 0; i < perfectData.length; i++) {
    perfectData[i] = [toStr(perfectData[i][0]), toFloat(perfectData[i][1])];
  }
  return perfectData;
}

// define classes

var Chart = function Chart(element, dataSource, options) {
  var elementId;
  if (typeof element === "string") {
    elementId = element;
    element = document.getElementById(element);
    if (!element) {
      throw new Error("No element with id " + elementId);
    }
  }
  this.element = element;
  this.options = merge(Chartkick.options, options || {});
  this.dataSource = dataSource;

  Chartkick.charts[element.id] = this;

  fetchDataSource(this, dataSource);

  if (this.options.refresh) {
    this.startRefresh();
  }
};

Chart.prototype.getElement = function getElement () {
  return this.element;
};

Chart.prototype.getDataSource = function getDataSource () {
  return this.dataSource;
};

Chart.prototype.getData = function getData () {
  return this.data;
};

Chart.prototype.getOptions = function getOptions () {
  return this.options;
};

Chart.prototype.getChartObject = function getChartObject () {
  return this.chart;
};

Chart.prototype.getAdapter = function getAdapter () {
  return this.adapter;
};

Chart.prototype.updateData = function updateData (dataSource, options) {
  this.dataSource = dataSource;
  if (options) {
    this.__updateOptions(options);
  }
  fetchDataSource(this, dataSource);
};

Chart.prototype.setOptions = function setOptions (options) {
  this.__updateOptions(options);
  this.redraw();
};

Chart.prototype.redraw = function redraw () {
  fetchDataSource(this, this.rawData);
};

Chart.prototype.refreshData = function refreshData () {
  if (typeof this.dataSource === "string") {
    // prevent browser from caching
    var sep = this.dataSource.indexOf("?") === -1 ? "?" : "&";
    var url = this.dataSource + sep + "_=" + (new Date()).getTime();
    fetchDataSource(this, url);
  }
};

Chart.prototype.startRefresh = function startRefresh () {
    var this$1 = this;

  var refresh = this.options.refresh;

  if (refresh && typeof this.dataSource !== "string") {
    throw new Error("Data source must be a URL for refresh");
  }

  if (!this.intervalId) {
    if (refresh) {
      this.intervalId = setInterval( function () {
        this$1.refreshData();
      }, refresh * 1000);
    } else {
      throw new Error("No refresh interval");
    }
  }
};

Chart.prototype.stopRefresh = function stopRefresh () {
  if (this.intervalId) {
    clearInterval(this.intervalId);
    this.intervalId = null;
  }
};

Chart.prototype.toImage = function toImage (download) {
  if (this.adapter === "chartjs") {
    if (download && download.background && download.background !== "transparent") {
      // https://stackoverflow.com/questions/30464750/chartjs-line-chart-set-background-color
      var canvas = this.chart.chart.canvas;
      var ctx = this.chart.chart.ctx;
      var tmpCanvas = document.createElement("canvas");
      var tmpCtx = tmpCanvas.getContext("2d");
      tmpCanvas.width = ctx.canvas.width;
      tmpCanvas.height = ctx.canvas.height;
      tmpCtx.fillStyle = download.background;
      tmpCtx.fillRect(0, 0, tmpCanvas.width, tmpCanvas.height);
      tmpCtx.drawImage(canvas, 0, 0);
      return tmpCanvas.toDataURL("image/png");
    } else {
      return this.chart.toBase64Image();
    }
  } else {
    // TODO throw error in next major version
    // throw new Error("Feature only available for Chart.js");
    return null;
  }
};

Chart.prototype.destroy = function destroy () {
  if (this.__adapterObject) {
    this.__adapterObject.destroy(this);
  }

  if (this.__enterEvent) {
    removeEvent(this.element, "mouseover", this.__enterEvent);
  }

  if (this.__leaveEvent) {
    removeEvent(this.element, "mouseout", this.__leaveEvent);
  }
};

Chart.prototype.__updateOptions = function __updateOptions (options) {
  var updateRefresh = options.refresh && options.refresh !== this.options.refresh;
  this.options = merge(Chartkick.options, options);
  if (updateRefresh) {
    this.stopRefresh();
    this.startRefresh();
  }
};

Chart.prototype.__render = function __render () {
  this.data = this.__processData();
  renderChart(this.__chartName(), this);
};

Chart.prototype.__config = function __config () {
  return config;
};

var LineChart = /*@__PURE__*/(function (Chart) {
  function LineChart () {
    Chart.apply(this, arguments);
  }

  if ( Chart ) LineChart.__proto__ = Chart;
  LineChart.prototype = Object.create( Chart && Chart.prototype );
  LineChart.prototype.constructor = LineChart;

  LineChart.prototype.__processData = function __processData () {
    return processSeries(this);
  };

  LineChart.prototype.__chartName = function __chartName () {
    return "LineChart";
  };

  return LineChart;
}(Chart));

var PieChart = /*@__PURE__*/(function (Chart) {
  function PieChart () {
    Chart.apply(this, arguments);
  }

  if ( Chart ) PieChart.__proto__ = Chart;
  PieChart.prototype = Object.create( Chart && Chart.prototype );
  PieChart.prototype.constructor = PieChart;

  PieChart.prototype.__processData = function __processData () {
    return processSimple(this);
  };

  PieChart.prototype.__chartName = function __chartName () {
    return "PieChart";
  };

  return PieChart;
}(Chart));

var ColumnChart = /*@__PURE__*/(function (Chart) {
  function ColumnChart () {
    Chart.apply(this, arguments);
  }

  if ( Chart ) ColumnChart.__proto__ = Chart;
  ColumnChart.prototype = Object.create( Chart && Chart.prototype );
  ColumnChart.prototype.constructor = ColumnChart;

  ColumnChart.prototype.__processData = function __processData () {
    return processSeries(this, null, true);
  };

  ColumnChart.prototype.__chartName = function __chartName () {
    return "ColumnChart";
  };

  return ColumnChart;
}(Chart));

var BarChart = /*@__PURE__*/(function (Chart) {
  function BarChart () {
    Chart.apply(this, arguments);
  }

  if ( Chart ) BarChart.__proto__ = Chart;
  BarChart.prototype = Object.create( Chart && Chart.prototype );
  BarChart.prototype.constructor = BarChart;

  BarChart.prototype.__processData = function __processData () {
    return processSeries(this, null, true);
  };

  BarChart.prototype.__chartName = function __chartName () {
    return "BarChart";
  };

  return BarChart;
}(Chart));

var AreaChart = /*@__PURE__*/(function (Chart) {
  function AreaChart () {
    Chart.apply(this, arguments);
  }

  if ( Chart ) AreaChart.__proto__ = Chart;
  AreaChart.prototype = Object.create( Chart && Chart.prototype );
  AreaChart.prototype.constructor = AreaChart;

  AreaChart.prototype.__processData = function __processData () {
    return processSeries(this);
  };

  AreaChart.prototype.__chartName = function __chartName () {
    return "AreaChart";
  };

  return AreaChart;
}(Chart));

var GeoChart = /*@__PURE__*/(function (Chart) {
  function GeoChart () {
    Chart.apply(this, arguments);
  }

  if ( Chart ) GeoChart.__proto__ = Chart;
  GeoChart.prototype = Object.create( Chart && Chart.prototype );
  GeoChart.prototype.constructor = GeoChart;

  GeoChart.prototype.__processData = function __processData () {
    return processSimple(this);
  };

  GeoChart.prototype.__chartName = function __chartName () {
    return "GeoChart";
  };

  return GeoChart;
}(Chart));

var ScatterChart = /*@__PURE__*/(function (Chart) {
  function ScatterChart () {
    Chart.apply(this, arguments);
  }

  if ( Chart ) ScatterChart.__proto__ = Chart;
  ScatterChart.prototype = Object.create( Chart && Chart.prototype );
  ScatterChart.prototype.constructor = ScatterChart;

  ScatterChart.prototype.__processData = function __processData () {
    return processSeries(this, "number");
  };

  ScatterChart.prototype.__chartName = function __chartName () {
    return "ScatterChart";
  };

  return ScatterChart;
}(Chart));

var BubbleChart = /*@__PURE__*/(function (Chart) {
  function BubbleChart () {
    Chart.apply(this, arguments);
  }

  if ( Chart ) BubbleChart.__proto__ = Chart;
  BubbleChart.prototype = Object.create( Chart && Chart.prototype );
  BubbleChart.prototype.constructor = BubbleChart;

  BubbleChart.prototype.__processData = function __processData () {
    return processSeries(this, "bubble");
  };

  BubbleChart.prototype.__chartName = function __chartName () {
    return "BubbleChart";
  };

  return BubbleChart;
}(Chart));

var Timeline = /*@__PURE__*/(function (Chart) {
  function Timeline () {
    Chart.apply(this, arguments);
  }

  if ( Chart ) Timeline.__proto__ = Chart;
  Timeline.prototype = Object.create( Chart && Chart.prototype );
  Timeline.prototype.constructor = Timeline;

  Timeline.prototype.__processData = function __processData () {
    var i, data = this.rawData;
    for (i = 0; i < data.length; i++) {
      data[i][1] = toDate(data[i][1]);
      data[i][2] = toDate(data[i][2]);
    }
    return data;
  };

  Timeline.prototype.__chartName = function __chartName () {
    return "Timeline";
  };

  return Timeline;
}(Chart));

var Chartkick = {
  LineChart: LineChart,
  PieChart: PieChart,
  ColumnChart: ColumnChart,
  BarChart: BarChart,
  AreaChart: AreaChart,
  GeoChart: GeoChart,
  ScatterChart: ScatterChart,
  BubbleChart: BubbleChart,
  Timeline: Timeline,
  charts: {},
  configure: function (options) {
    for (var key in options) {
      if (options.hasOwnProperty(key)) {
        config[key] = options[key];
      }
    }
  },
  setDefaultOptions: function (opts) {
    Chartkick.options = opts;
  },
  eachChart: function (callback) {
    for (var chartId in Chartkick.charts) {
      if (Chartkick.charts.hasOwnProperty(chartId)) {
        callback(Chartkick.charts[chartId]);
      }
    }
  },
  config: config,
  options: {},
  adapters: adapters,
  addAdapter: addAdapter
};

/* harmony default export */ __webpack_exports__["default"] = (Chartkick);


/***/ }),

/***/ "./node_modules/css-loader/index.js?!./node_modules/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/postcss-loader/src/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/consultentries.vue?vue&type=style&index=0&id=38b5f7a2&scoped=true&lang=css&":
/*!********************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/css-loader??ref--6-1!./node_modules/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/postcss-loader/src??ref--6-2!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/components/consultentries.vue?vue&type=style&index=0&id=38b5f7a2&scoped=true&lang=css& ***!
  \********************************************************************************************************************************************************************************************************************************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

exports = module.exports = __webpack_require__(/*! ../../../node_modules/css-loader/lib/css-base.js */ "./node_modules/css-loader/lib/css-base.js")(false);
// imports


// module
exports.push([module.i, "\n.line[data-v-38b5f7a2] {\n    fill: none;\n    stroke: steelblue;\n    stroke-width: 2px;\n}\n.axisRed line[data-v-38b5f7a2]{\n    stroke: red;\n}\n.axisRed path[data-v-38b5f7a2]{\n    stroke: red;\n}\n.axisRed text[data-v-38b5f7a2]{\n    fill: red;\n}\n\n", ""]);

// exports


/***/ }),

/***/ "./node_modules/css-loader/lib/css-base.js":
/*!*************************************************!*\
  !*** ./node_modules/css-loader/lib/css-base.js ***!
  \*************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

/*
	MIT License http://www.opensource.org/licenses/mit-license.php
	Author Tobias Koppers @sokra
*/
// css base code, injected by the css-loader
module.exports = function(useSourceMap) {
	var list = [];

	// return the list of modules as css string
	list.toString = function toString() {
		return this.map(function (item) {
			var content = cssWithMappingToString(item, useSourceMap);
			if(item[2]) {
				return "@media " + item[2] + "{" + content + "}";
			} else {
				return content;
			}
		}).join("");
	};

	// import a list of modules into the list
	list.i = function(modules, mediaQuery) {
		if(typeof modules === "string")
			modules = [[null, modules, ""]];
		var alreadyImportedModules = {};
		for(var i = 0; i < this.length; i++) {
			var id = this[i][0];
			if(typeof id === "number")
				alreadyImportedModules[id] = true;
		}
		for(i = 0; i < modules.length; i++) {
			var item = modules[i];
			// skip already imported module
			// this implementation is not 100% perfect for weird media query combinations
			//  when a module is imported multiple times with different media queries.
			//  I hope this will never occur (Hey this way we have smaller bundles)
			if(typeof item[0] !== "number" || !alreadyImportedModules[item[0]]) {
				if(mediaQuery && !item[2]) {
					item[2] = mediaQuery;
				} else if(mediaQuery) {
					item[2] = "(" + item[2] + ") and (" + mediaQuery + ")";
				}
				list.push(item);
			}
		}
	};
	return list;
};

function cssWithMappingToString(item, useSourceMap) {
	var content = item[1] || '';
	var cssMapping = item[3];
	if (!cssMapping) {
		return content;
	}

	if (useSourceMap && typeof btoa === 'function') {
		var sourceMapping = toComment(cssMapping);
		var sourceURLs = cssMapping.sources.map(function (source) {
			return '/*# sourceURL=' + cssMapping.sourceRoot + source + ' */'
		});

		return [content].concat(sourceURLs).concat([sourceMapping]).join('\n');
	}

	return [content].join('\n');
}

// Adapted from convert-source-map (MIT)
function toComment(sourceMap) {
	// eslint-disable-next-line no-undef
	var base64 = btoa(unescape(encodeURIComponent(JSON.stringify(sourceMap))));
	var data = 'sourceMappingURL=data:application/json;charset=utf-8;base64,' + base64;

	return '/*# ' + data + ' */';
}


/***/ }),

/***/ "./node_modules/deep-equal/index.js":
/*!******************************************!*\
  !*** ./node_modules/deep-equal/index.js ***!
  \******************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

var pSlice = Array.prototype.slice;
var objectKeys = __webpack_require__(/*! ./lib/keys.js */ "./node_modules/deep-equal/lib/keys.js");
var isArguments = __webpack_require__(/*! ./lib/is_arguments.js */ "./node_modules/deep-equal/lib/is_arguments.js");

var deepEqual = module.exports = function (actual, expected, opts) {
  if (!opts) opts = {};
  // 7.1. All identical values are equivalent, as determined by ===.
  if (actual === expected) {
    return true;

  } else if (actual instanceof Date && expected instanceof Date) {
    return actual.getTime() === expected.getTime();

  // 7.3. Other pairs that do not both pass typeof value == 'object',
  // equivalence is determined by ==.
  } else if (!actual || !expected || typeof actual != 'object' && typeof expected != 'object') {
    return opts.strict ? actual === expected : actual == expected;

  // 7.4. For all other Object pairs, including Array objects, equivalence is
  // determined by having the same number of owned properties (as verified
  // with Object.prototype.hasOwnProperty.call), the same set of keys
  // (although not necessarily the same order), equivalent values for every
  // corresponding key, and an identical 'prototype' property. Note: this
  // accounts for both named and indexed properties on Arrays.
  } else {
    return objEquiv(actual, expected, opts);
  }
}

function isUndefinedOrNull(value) {
  return value === null || value === undefined;
}

function isBuffer (x) {
  if (!x || typeof x !== 'object' || typeof x.length !== 'number') return false;
  if (typeof x.copy !== 'function' || typeof x.slice !== 'function') {
    return false;
  }
  if (x.length > 0 && typeof x[0] !== 'number') return false;
  return true;
}

function objEquiv(a, b, opts) {
  var i, key;
  if (isUndefinedOrNull(a) || isUndefinedOrNull(b))
    return false;
  // an identical 'prototype' property.
  if (a.prototype !== b.prototype) return false;
  //~~~I've managed to break Object.keys through screwy arguments passing.
  //   Converting to array solves the problem.
  if (isArguments(a)) {
    if (!isArguments(b)) {
      return false;
    }
    a = pSlice.call(a);
    b = pSlice.call(b);
    return deepEqual(a, b, opts);
  }
  if (isBuffer(a)) {
    if (!isBuffer(b)) {
      return false;
    }
    if (a.length !== b.length) return false;
    for (i = 0; i < a.length; i++) {
      if (a[i] !== b[i]) return false;
    }
    return true;
  }
  try {
    var ka = objectKeys(a),
        kb = objectKeys(b);
  } catch (e) {//happens when one is a string literal and the other isn't
    return false;
  }
  // having the same number of owned properties (keys incorporates
  // hasOwnProperty)
  if (ka.length != kb.length)
    return false;
  //the same set of keys (although not necessarily the same order),
  ka.sort();
  kb.sort();
  //~~~cheap key test
  for (i = ka.length - 1; i >= 0; i--) {
    if (ka[i] != kb[i])
      return false;
  }
  //equivalent values for every corresponding key, and
  //~~~possibly expensive deep test
  for (i = ka.length - 1; i >= 0; i--) {
    key = ka[i];
    if (!deepEqual(a[key], b[key], opts)) return false;
  }
  return typeof a === typeof b;
}


/***/ }),

/***/ "./node_modules/deep-equal/lib/is_arguments.js":
/*!*****************************************************!*\
  !*** ./node_modules/deep-equal/lib/is_arguments.js ***!
  \*****************************************************/
/*! no static exports found */
/***/ (function(module, exports) {

var supportsArgumentsClass = (function(){
  return Object.prototype.toString.call(arguments)
})() == '[object Arguments]';

exports = module.exports = supportsArgumentsClass ? supported : unsupported;

exports.supported = supported;
function supported(object) {
  return Object.prototype.toString.call(object) == '[object Arguments]';
};

exports.unsupported = unsupported;
function unsupported(object){
  return object &&
    typeof object == 'object' &&
    typeof object.length == 'number' &&
    Object.prototype.hasOwnProperty.call(object, 'callee') &&
    !Object.prototype.propertyIsEnumerable.call(object, 'callee') ||
    false;
};


/***/ }),

/***/ "./node_modules/deep-equal/lib/keys.js":
/*!*********************************************!*\
  !*** ./node_modules/deep-equal/lib/keys.js ***!
  \*********************************************/
/*! no static exports found */
/***/ (function(module, exports) {

exports = module.exports = typeof Object.keys === 'function'
  ? Object.keys : shim;

exports.shim = shim;
function shim (obj) {
  var keys = [];
  for (var key in obj) keys.push(key);
  return keys;
}


/***/ }),

/***/ "./node_modules/deepmerge/dist/es.js":
/*!*******************************************!*\
  !*** ./node_modules/deepmerge/dist/es.js ***!
  \*******************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
var isMergeableObject = function isMergeableObject(value) {
	return isNonNullObject(value)
		&& !isSpecial(value)
};

function isNonNullObject(value) {
	return !!value && typeof value === 'object'
}

function isSpecial(value) {
	var stringValue = Object.prototype.toString.call(value);

	return stringValue === '[object RegExp]'
		|| stringValue === '[object Date]'
		|| isReactElement(value)
}

// see https://github.com/facebook/react/blob/b5ac963fb791d1298e7f396236383bc955f916c1/src/isomorphic/classic/element/ReactElement.js#L21-L25
var canUseSymbol = typeof Symbol === 'function' && Symbol.for;
var REACT_ELEMENT_TYPE = canUseSymbol ? Symbol.for('react.element') : 0xeac7;

function isReactElement(value) {
	return value.$$typeof === REACT_ELEMENT_TYPE
}

function emptyTarget(val) {
	return Array.isArray(val) ? [] : {}
}

function cloneUnlessOtherwiseSpecified(value, options) {
	return (options.clone !== false && options.isMergeableObject(value))
		? deepmerge(emptyTarget(value), value, options)
		: value
}

function defaultArrayMerge(target, source, options) {
	return target.concat(source).map(function(element) {
		return cloneUnlessOtherwiseSpecified(element, options)
	})
}

function mergeObject(target, source, options) {
	var destination = {};
	if (options.isMergeableObject(target)) {
		Object.keys(target).forEach(function(key) {
			destination[key] = cloneUnlessOtherwiseSpecified(target[key], options);
		});
	}
	Object.keys(source).forEach(function(key) {
		if (!options.isMergeableObject(source[key]) || !target[key]) {
			destination[key] = cloneUnlessOtherwiseSpecified(source[key], options);
		} else {
			destination[key] = deepmerge(target[key], source[key], options);
		}
	});
	return destination
}

function deepmerge(target, source, options) {
	options = options || {};
	options.arrayMerge = options.arrayMerge || defaultArrayMerge;
	options.isMergeableObject = options.isMergeableObject || isMergeableObject;

	var sourceIsArray = Array.isArray(source);
	var targetIsArray = Array.isArray(target);
	var sourceAndTargetTypesMatch = sourceIsArray === targetIsArray;

	if (!sourceAndTargetTypesMatch) {
		return cloneUnlessOtherwiseSpecified(source, options)
	} else if (sourceIsArray) {
		return options.arrayMerge(target, source, options)
	} else {
		return mergeObject(target, source, options)
	}
}

deepmerge.all = function deepmergeAll(array, options) {
	if (!Array.isArray(array)) {
		throw new Error('first argument should be an array')
	}

	return array.reduce(function(prev, next) {
		return deepmerge(prev, next, options)
	}, {})
};

var deepmerge_1 = deepmerge;

/* harmony default export */ __webpack_exports__["default"] = (deepmerge_1);


/***/ }),

/***/ "./node_modules/google-charts/dist/googleCharts.esm.js":
/*!*************************************************************!*\
  !*** ./node_modules/google-charts/dist/googleCharts.esm.js ***!
  \*************************************************************/
/*! exports provided: default, GoogleCharts */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "GoogleCharts", function() { return GoogleCharts; });
/* googleCharts.js Version: 1.5.0 Built On: 2018-12-30 */
const loadScript = Symbol('loadScript');
const instance = Symbol('instance');
let _instance;

class GoogleChartsManager {
    get [instance]() {
        return _instance
    }

    set [instance](value) {
        _instance = value;
    }

    constructor() {
        if (this[instance]) {
            return this[instance]
        }

        this[instance] = this;
    }

    reset() {
        _instance = null;
    }

    [loadScript]() {
        if (!this.scriptPromise) {
            this.scriptPromise = new Promise(resolve => {
                const body = document.getElementsByTagName('body')[0];
                const script = document.createElement('script');
                script.type = 'text/javascript';
                script.onload = function() {
                    GoogleCharts.api = window.google;
                    GoogleCharts.api.charts.load('current', {
                        packages: ['corechart', 'table'],
                    });
                    GoogleCharts.api.charts.setOnLoadCallback(() => {
                        resolve();
                    });
                };
                script.src = 'https://www.gstatic.com/charts/loader.js';
                body.appendChild(script);
            });
        }
        return this.scriptPromise
    }

    load(callback, type) {
        return this[loadScript]().then(() => {
            if (type) {
                let config = {};
                if (type instanceof Object) {
                    config = type;
                } else if (Array.isArray(type)) {
                    config = { packages: type };
                } else {
                    config = { packages: [type] };
                }
                this.api.charts.load('current', config);
                this.api.charts.setOnLoadCallback(callback);
            } else {
                if(typeof callback != 'function') {
                    throw('callback must be a function');
                } else {
                    callback();               
                }
            }
        })
    }
}

const GoogleCharts = new GoogleChartsManager();

/* harmony default export */ __webpack_exports__["default"] = (GoogleChartsManager);



/***/ }),

/***/ "./node_modules/is-buffer/index.js":
/*!*****************************************!*\
  !*** ./node_modules/is-buffer/index.js ***!
  \*****************************************/
/*! no static exports found */
/***/ (function(module, exports) {

/*!
 * Determine if an object is a Buffer
 *
 * @author   Feross Aboukhadijeh <https://feross.org>
 * @license  MIT
 */

// The _isBuffer check is for Safari 5-7 support, because it's missing
// Object.prototype.constructor. Remove this eventually
module.exports = function (obj) {
  return obj != null && (isBuffer(obj) || isSlowBuffer(obj) || !!obj._isBuffer)
}

function isBuffer (obj) {
  return !!obj.constructor && typeof obj.constructor.isBuffer === 'function' && obj.constructor.isBuffer(obj)
}

// For Node v0.10 support. Remove this eventually.
function isSlowBuffer (obj) {
  return typeof obj.readFloatLE === 'function' && typeof obj.slice === 'function' && isBuffer(obj.slice(0, 0))
}


/***/ }),

/***/ "./node_modules/process/browser.js":
/*!*****************************************!*\
  !*** ./node_modules/process/browser.js ***!
  \*****************************************/
/*! no static exports found */
/***/ (function(module, exports) {

// shim for using process in browser
var process = module.exports = {};

// cached from whatever global is present so that test runners that stub it
// don't break things.  But we need to wrap it in a try catch in case it is
// wrapped in strict mode code which doesn't define any globals.  It's inside a
// function because try/catches deoptimize in certain engines.

var cachedSetTimeout;
var cachedClearTimeout;

function defaultSetTimout() {
    throw new Error('setTimeout has not been defined');
}
function defaultClearTimeout () {
    throw new Error('clearTimeout has not been defined');
}
(function () {
    try {
        if (typeof setTimeout === 'function') {
            cachedSetTimeout = setTimeout;
        } else {
            cachedSetTimeout = defaultSetTimout;
        }
    } catch (e) {
        cachedSetTimeout = defaultSetTimout;
    }
    try {
        if (typeof clearTimeout === 'function') {
            cachedClearTimeout = clearTimeout;
        } else {
            cachedClearTimeout = defaultClearTimeout;
        }
    } catch (e) {
        cachedClearTimeout = defaultClearTimeout;
    }
} ())
function runTimeout(fun) {
    if (cachedSetTimeout === setTimeout) {
        //normal enviroments in sane situations
        return setTimeout(fun, 0);
    }
    // if setTimeout wasn't available but was latter defined
    if ((cachedSetTimeout === defaultSetTimout || !cachedSetTimeout) && setTimeout) {
        cachedSetTimeout = setTimeout;
        return setTimeout(fun, 0);
    }
    try {
        // when when somebody has screwed with setTimeout but no I.E. maddness
        return cachedSetTimeout(fun, 0);
    } catch(e){
        try {
            // When we are in I.E. but the script has been evaled so I.E. doesn't trust the global object when called normally
            return cachedSetTimeout.call(null, fun, 0);
        } catch(e){
            // same as above but when it's a version of I.E. that must have the global object for 'this', hopfully our context correct otherwise it will throw a global error
            return cachedSetTimeout.call(this, fun, 0);
        }
    }


}
function runClearTimeout(marker) {
    if (cachedClearTimeout === clearTimeout) {
        //normal enviroments in sane situations
        return clearTimeout(marker);
    }
    // if clearTimeout wasn't available but was latter defined
    if ((cachedClearTimeout === defaultClearTimeout || !cachedClearTimeout) && clearTimeout) {
        cachedClearTimeout = clearTimeout;
        return clearTimeout(marker);
    }
    try {
        // when when somebody has screwed with setTimeout but no I.E. maddness
        return cachedClearTimeout(marker);
    } catch (e){
        try {
            // When we are in I.E. but the script has been evaled so I.E. doesn't  trust the global object when called normally
            return cachedClearTimeout.call(null, marker);
        } catch (e){
            // same as above but when it's a version of I.E. that must have the global object for 'this', hopfully our context correct otherwise it will throw a global error.
            // Some versions of I.E. have different rules for clearTimeout vs setTimeout
            return cachedClearTimeout.call(this, marker);
        }
    }



}
var queue = [];
var draining = false;
var currentQueue;
var queueIndex = -1;

function cleanUpNextTick() {
    if (!draining || !currentQueue) {
        return;
    }
    draining = false;
    if (currentQueue.length) {
        queue = currentQueue.concat(queue);
    } else {
        queueIndex = -1;
    }
    if (queue.length) {
        drainQueue();
    }
}

function drainQueue() {
    if (draining) {
        return;
    }
    var timeout = runTimeout(cleanUpNextTick);
    draining = true;

    var len = queue.length;
    while(len) {
        currentQueue = queue;
        queue = [];
        while (++queueIndex < len) {
            if (currentQueue) {
                currentQueue[queueIndex].run();
            }
        }
        queueIndex = -1;
        len = queue.length;
    }
    currentQueue = null;
    draining = false;
    runClearTimeout(timeout);
}

process.nextTick = function (fun) {
    var args = new Array(arguments.length - 1);
    if (arguments.length > 1) {
        for (var i = 1; i < arguments.length; i++) {
            args[i - 1] = arguments[i];
        }
    }
    queue.push(new Item(fun, args));
    if (queue.length === 1 && !draining) {
        runTimeout(drainQueue);
    }
};

// v8 likes predictible objects
function Item(fun, array) {
    this.fun = fun;
    this.array = array;
}
Item.prototype.run = function () {
    this.fun.apply(null, this.array);
};
process.title = 'browser';
process.browser = true;
process.env = {};
process.argv = [];
process.version = ''; // empty string to avoid regexp issues
process.versions = {};

function noop() {}

process.on = noop;
process.addListener = noop;
process.once = noop;
process.off = noop;
process.removeListener = noop;
process.removeAllListeners = noop;
process.emit = noop;
process.prependListener = noop;
process.prependOnceListener = noop;

process.listeners = function (name) { return [] }

process.binding = function (name) {
    throw new Error('process.binding is not supported');
};

process.cwd = function () { return '/' };
process.chdir = function (dir) {
    throw new Error('process.chdir is not supported');
};
process.umask = function() { return 0; };


/***/ }),

/***/ "./node_modules/setimmediate/setImmediate.js":
/*!***************************************************!*\
  !*** ./node_modules/setimmediate/setImmediate.js ***!
  \***************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

/* WEBPACK VAR INJECTION */(function(global, process) {(function (global, undefined) {
    "use strict";

    if (global.setImmediate) {
        return;
    }

    var nextHandle = 1; // Spec says greater than zero
    var tasksByHandle = {};
    var currentlyRunningATask = false;
    var doc = global.document;
    var registerImmediate;

    function setImmediate(callback) {
      // Callback can either be a function or a string
      if (typeof callback !== "function") {
        callback = new Function("" + callback);
      }
      // Copy function arguments
      var args = new Array(arguments.length - 1);
      for (var i = 0; i < args.length; i++) {
          args[i] = arguments[i + 1];
      }
      // Store and register the task
      var task = { callback: callback, args: args };
      tasksByHandle[nextHandle] = task;
      registerImmediate(nextHandle);
      return nextHandle++;
    }

    function clearImmediate(handle) {
        delete tasksByHandle[handle];
    }

    function run(task) {
        var callback = task.callback;
        var args = task.args;
        switch (args.length) {
        case 0:
            callback();
            break;
        case 1:
            callback(args[0]);
            break;
        case 2:
            callback(args[0], args[1]);
            break;
        case 3:
            callback(args[0], args[1], args[2]);
            break;
        default:
            callback.apply(undefined, args);
            break;
        }
    }

    function runIfPresent(handle) {
        // From the spec: "Wait until any invocations of this algorithm started before this one have completed."
        // So if we're currently running a task, we'll need to delay this invocation.
        if (currentlyRunningATask) {
            // Delay by doing a setTimeout. setImmediate was tried instead, but in Firefox 7 it generated a
            // "too much recursion" error.
            setTimeout(runIfPresent, 0, handle);
        } else {
            var task = tasksByHandle[handle];
            if (task) {
                currentlyRunningATask = true;
                try {
                    run(task);
                } finally {
                    clearImmediate(handle);
                    currentlyRunningATask = false;
                }
            }
        }
    }

    function installNextTickImplementation() {
        registerImmediate = function(handle) {
            process.nextTick(function () { runIfPresent(handle); });
        };
    }

    function canUsePostMessage() {
        // The test against `importScripts` prevents this implementation from being installed inside a web worker,
        // where `global.postMessage` means something completely different and can't be used for this purpose.
        if (global.postMessage && !global.importScripts) {
            var postMessageIsAsynchronous = true;
            var oldOnMessage = global.onmessage;
            global.onmessage = function() {
                postMessageIsAsynchronous = false;
            };
            global.postMessage("", "*");
            global.onmessage = oldOnMessage;
            return postMessageIsAsynchronous;
        }
    }

    function installPostMessageImplementation() {
        // Installs an event handler on `global` for the `message` event: see
        // * https://developer.mozilla.org/en/DOM/window.postMessage
        // * http://www.whatwg.org/specs/web-apps/current-work/multipage/comms.html#crossDocumentMessages

        var messagePrefix = "setImmediate$" + Math.random() + "$";
        var onGlobalMessage = function(event) {
            if (event.source === global &&
                typeof event.data === "string" &&
                event.data.indexOf(messagePrefix) === 0) {
                runIfPresent(+event.data.slice(messagePrefix.length));
            }
        };

        if (global.addEventListener) {
            global.addEventListener("message", onGlobalMessage, false);
        } else {
            global.attachEvent("onmessage", onGlobalMessage);
        }

        registerImmediate = function(handle) {
            global.postMessage(messagePrefix + handle, "*");
        };
    }

    function installMessageChannelImplementation() {
        var channel = new MessageChannel();
        channel.port1.onmessage = function(event) {
            var handle = event.data;
            runIfPresent(handle);
        };

        registerImmediate = function(handle) {
            channel.port2.postMessage(handle);
        };
    }

    function installReadyStateChangeImplementation() {
        var html = doc.documentElement;
        registerImmediate = function(handle) {
            // Create a <script> element; its readystatechange event will be fired asynchronously once it is inserted
            // into the document. Do so, thus queuing up the task. Remember to clean up once it's been called.
            var script = doc.createElement("script");
            script.onreadystatechange = function () {
                runIfPresent(handle);
                script.onreadystatechange = null;
                html.removeChild(script);
                script = null;
            };
            html.appendChild(script);
        };
    }

    function installSetTimeoutImplementation() {
        registerImmediate = function(handle) {
            setTimeout(runIfPresent, 0, handle);
        };
    }

    // If supported, we should attach to the prototype of global, since that is where setTimeout et al. live.
    var attachTo = Object.getPrototypeOf && Object.getPrototypeOf(global);
    attachTo = attachTo && attachTo.setTimeout ? attachTo : global;

    // Don't get fooled by e.g. browserify environments.
    if ({}.toString.call(global.process) === "[object process]") {
        // For Node.js before 0.9
        installNextTickImplementation();

    } else if (canUsePostMessage()) {
        // For non-IE10 modern browsers
        installPostMessageImplementation();

    } else if (global.MessageChannel) {
        // For web workers, where supported
        installMessageChannelImplementation();

    } else if (doc && "onreadystatechange" in doc.createElement("script")) {
        // For IE 6–8
        installReadyStateChangeImplementation();

    } else {
        // For older browsers
        installSetTimeoutImplementation();
    }

    attachTo.setImmediate = setImmediate;
    attachTo.clearImmediate = clearImmediate;
}(typeof self === "undefined" ? typeof global === "undefined" ? this : global : self));

/* WEBPACK VAR INJECTION */}.call(this, __webpack_require__(/*! ./../webpack/buildin/global.js */ "./node_modules/webpack/buildin/global.js"), __webpack_require__(/*! ./../process/browser.js */ "./node_modules/process/browser.js")))

/***/ }),

/***/ "./node_modules/style-loader/index.js!./node_modules/css-loader/index.js?!./node_modules/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/postcss-loader/src/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/consultentries.vue?vue&type=style&index=0&id=38b5f7a2&scoped=true&lang=css&":
/*!************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/style-loader!./node_modules/css-loader??ref--6-1!./node_modules/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/postcss-loader/src??ref--6-2!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/components/consultentries.vue?vue&type=style&index=0&id=38b5f7a2&scoped=true&lang=css& ***!
  \************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {


var content = __webpack_require__(/*! !../../../node_modules/css-loader??ref--6-1!../../../node_modules/vue-loader/lib/loaders/stylePostLoader.js!../../../node_modules/postcss-loader/src??ref--6-2!../../../node_modules/vue-loader/lib??vue-loader-options!./consultentries.vue?vue&type=style&index=0&id=38b5f7a2&scoped=true&lang=css& */ "./node_modules/css-loader/index.js?!./node_modules/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/postcss-loader/src/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/consultentries.vue?vue&type=style&index=0&id=38b5f7a2&scoped=true&lang=css&");

if(typeof content === 'string') content = [[module.i, content, '']];

var transform;
var insertInto;



var options = {"hmr":true}

options.transform = transform
options.insertInto = undefined;

var update = __webpack_require__(/*! ../../../node_modules/style-loader/lib/addStyles.js */ "./node_modules/style-loader/lib/addStyles.js")(content, options);

if(content.locals) module.exports = content.locals;

if(false) {}

/***/ }),

/***/ "./node_modules/style-loader/lib/addStyles.js":
/*!****************************************************!*\
  !*** ./node_modules/style-loader/lib/addStyles.js ***!
  \****************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

/*
	MIT License http://www.opensource.org/licenses/mit-license.php
	Author Tobias Koppers @sokra
*/

var stylesInDom = {};

var	memoize = function (fn) {
	var memo;

	return function () {
		if (typeof memo === "undefined") memo = fn.apply(this, arguments);
		return memo;
	};
};

var isOldIE = memoize(function () {
	// Test for IE <= 9 as proposed by Browserhacks
	// @see http://browserhacks.com/#hack-e71d8692f65334173fee715c222cb805
	// Tests for existence of standard globals is to allow style-loader
	// to operate correctly into non-standard environments
	// @see https://github.com/webpack-contrib/style-loader/issues/177
	return window && document && document.all && !window.atob;
});

var getTarget = function (target, parent) {
  if (parent){
    return parent.querySelector(target);
  }
  return document.querySelector(target);
};

var getElement = (function (fn) {
	var memo = {};

	return function(target, parent) {
                // If passing function in options, then use it for resolve "head" element.
                // Useful for Shadow Root style i.e
                // {
                //   insertInto: function () { return document.querySelector("#foo").shadowRoot }
                // }
                if (typeof target === 'function') {
                        return target();
                }
                if (typeof memo[target] === "undefined") {
			var styleTarget = getTarget.call(this, target, parent);
			// Special case to return head of iframe instead of iframe itself
			if (window.HTMLIFrameElement && styleTarget instanceof window.HTMLIFrameElement) {
				try {
					// This will throw an exception if access to iframe is blocked
					// due to cross-origin restrictions
					styleTarget = styleTarget.contentDocument.head;
				} catch(e) {
					styleTarget = null;
				}
			}
			memo[target] = styleTarget;
		}
		return memo[target]
	};
})();

var singleton = null;
var	singletonCounter = 0;
var	stylesInsertedAtTop = [];

var	fixUrls = __webpack_require__(/*! ./urls */ "./node_modules/style-loader/lib/urls.js");

module.exports = function(list, options) {
	if (typeof DEBUG !== "undefined" && DEBUG) {
		if (typeof document !== "object") throw new Error("The style-loader cannot be used in a non-browser environment");
	}

	options = options || {};

	options.attrs = typeof options.attrs === "object" ? options.attrs : {};

	// Force single-tag solution on IE6-9, which has a hard limit on the # of <style>
	// tags it will allow on a page
	if (!options.singleton && typeof options.singleton !== "boolean") options.singleton = isOldIE();

	// By default, add <style> tags to the <head> element
        if (!options.insertInto) options.insertInto = "head";

	// By default, add <style> tags to the bottom of the target
	if (!options.insertAt) options.insertAt = "bottom";

	var styles = listToStyles(list, options);

	addStylesToDom(styles, options);

	return function update (newList) {
		var mayRemove = [];

		for (var i = 0; i < styles.length; i++) {
			var item = styles[i];
			var domStyle = stylesInDom[item.id];

			domStyle.refs--;
			mayRemove.push(domStyle);
		}

		if(newList) {
			var newStyles = listToStyles(newList, options);
			addStylesToDom(newStyles, options);
		}

		for (var i = 0; i < mayRemove.length; i++) {
			var domStyle = mayRemove[i];

			if(domStyle.refs === 0) {
				for (var j = 0; j < domStyle.parts.length; j++) domStyle.parts[j]();

				delete stylesInDom[domStyle.id];
			}
		}
	};
};

function addStylesToDom (styles, options) {
	for (var i = 0; i < styles.length; i++) {
		var item = styles[i];
		var domStyle = stylesInDom[item.id];

		if(domStyle) {
			domStyle.refs++;

			for(var j = 0; j < domStyle.parts.length; j++) {
				domStyle.parts[j](item.parts[j]);
			}

			for(; j < item.parts.length; j++) {
				domStyle.parts.push(addStyle(item.parts[j], options));
			}
		} else {
			var parts = [];

			for(var j = 0; j < item.parts.length; j++) {
				parts.push(addStyle(item.parts[j], options));
			}

			stylesInDom[item.id] = {id: item.id, refs: 1, parts: parts};
		}
	}
}

function listToStyles (list, options) {
	var styles = [];
	var newStyles = {};

	for (var i = 0; i < list.length; i++) {
		var item = list[i];
		var id = options.base ? item[0] + options.base : item[0];
		var css = item[1];
		var media = item[2];
		var sourceMap = item[3];
		var part = {css: css, media: media, sourceMap: sourceMap};

		if(!newStyles[id]) styles.push(newStyles[id] = {id: id, parts: [part]});
		else newStyles[id].parts.push(part);
	}

	return styles;
}

function insertStyleElement (options, style) {
	var target = getElement(options.insertInto)

	if (!target) {
		throw new Error("Couldn't find a style target. This probably means that the value for the 'insertInto' parameter is invalid.");
	}

	var lastStyleElementInsertedAtTop = stylesInsertedAtTop[stylesInsertedAtTop.length - 1];

	if (options.insertAt === "top") {
		if (!lastStyleElementInsertedAtTop) {
			target.insertBefore(style, target.firstChild);
		} else if (lastStyleElementInsertedAtTop.nextSibling) {
			target.insertBefore(style, lastStyleElementInsertedAtTop.nextSibling);
		} else {
			target.appendChild(style);
		}
		stylesInsertedAtTop.push(style);
	} else if (options.insertAt === "bottom") {
		target.appendChild(style);
	} else if (typeof options.insertAt === "object" && options.insertAt.before) {
		var nextSibling = getElement(options.insertAt.before, target);
		target.insertBefore(style, nextSibling);
	} else {
		throw new Error("[Style Loader]\n\n Invalid value for parameter 'insertAt' ('options.insertAt') found.\n Must be 'top', 'bottom', or Object.\n (https://github.com/webpack-contrib/style-loader#insertat)\n");
	}
}

function removeStyleElement (style) {
	if (style.parentNode === null) return false;
	style.parentNode.removeChild(style);

	var idx = stylesInsertedAtTop.indexOf(style);
	if(idx >= 0) {
		stylesInsertedAtTop.splice(idx, 1);
	}
}

function createStyleElement (options) {
	var style = document.createElement("style");

	if(options.attrs.type === undefined) {
		options.attrs.type = "text/css";
	}

	if(options.attrs.nonce === undefined) {
		var nonce = getNonce();
		if (nonce) {
			options.attrs.nonce = nonce;
		}
	}

	addAttrs(style, options.attrs);
	insertStyleElement(options, style);

	return style;
}

function createLinkElement (options) {
	var link = document.createElement("link");

	if(options.attrs.type === undefined) {
		options.attrs.type = "text/css";
	}
	options.attrs.rel = "stylesheet";

	addAttrs(link, options.attrs);
	insertStyleElement(options, link);

	return link;
}

function addAttrs (el, attrs) {
	Object.keys(attrs).forEach(function (key) {
		el.setAttribute(key, attrs[key]);
	});
}

function getNonce() {
	if (false) {}

	return __webpack_require__.nc;
}

function addStyle (obj, options) {
	var style, update, remove, result;

	// If a transform function was defined, run it on the css
	if (options.transform && obj.css) {
	    result = typeof options.transform === 'function'
		 ? options.transform(obj.css) 
		 : options.transform.default(obj.css);

	    if (result) {
	    	// If transform returns a value, use that instead of the original css.
	    	// This allows running runtime transformations on the css.
	    	obj.css = result;
	    } else {
	    	// If the transform function returns a falsy value, don't add this css.
	    	// This allows conditional loading of css
	    	return function() {
	    		// noop
	    	};
	    }
	}

	if (options.singleton) {
		var styleIndex = singletonCounter++;

		style = singleton || (singleton = createStyleElement(options));

		update = applyToSingletonTag.bind(null, style, styleIndex, false);
		remove = applyToSingletonTag.bind(null, style, styleIndex, true);

	} else if (
		obj.sourceMap &&
		typeof URL === "function" &&
		typeof URL.createObjectURL === "function" &&
		typeof URL.revokeObjectURL === "function" &&
		typeof Blob === "function" &&
		typeof btoa === "function"
	) {
		style = createLinkElement(options);
		update = updateLink.bind(null, style, options);
		remove = function () {
			removeStyleElement(style);

			if(style.href) URL.revokeObjectURL(style.href);
		};
	} else {
		style = createStyleElement(options);
		update = applyToTag.bind(null, style);
		remove = function () {
			removeStyleElement(style);
		};
	}

	update(obj);

	return function updateStyle (newObj) {
		if (newObj) {
			if (
				newObj.css === obj.css &&
				newObj.media === obj.media &&
				newObj.sourceMap === obj.sourceMap
			) {
				return;
			}

			update(obj = newObj);
		} else {
			remove();
		}
	};
}

var replaceText = (function () {
	var textStore = [];

	return function (index, replacement) {
		textStore[index] = replacement;

		return textStore.filter(Boolean).join('\n');
	};
})();

function applyToSingletonTag (style, index, remove, obj) {
	var css = remove ? "" : obj.css;

	if (style.styleSheet) {
		style.styleSheet.cssText = replaceText(index, css);
	} else {
		var cssNode = document.createTextNode(css);
		var childNodes = style.childNodes;

		if (childNodes[index]) style.removeChild(childNodes[index]);

		if (childNodes.length) {
			style.insertBefore(cssNode, childNodes[index]);
		} else {
			style.appendChild(cssNode);
		}
	}
}

function applyToTag (style, obj) {
	var css = obj.css;
	var media = obj.media;

	if(media) {
		style.setAttribute("media", media)
	}

	if(style.styleSheet) {
		style.styleSheet.cssText = css;
	} else {
		while(style.firstChild) {
			style.removeChild(style.firstChild);
		}

		style.appendChild(document.createTextNode(css));
	}
}

function updateLink (link, options, obj) {
	var css = obj.css;
	var sourceMap = obj.sourceMap;

	/*
		If convertToAbsoluteUrls isn't defined, but sourcemaps are enabled
		and there is no publicPath defined then lets turn convertToAbsoluteUrls
		on by default.  Otherwise default to the convertToAbsoluteUrls option
		directly
	*/
	var autoFixUrls = options.convertToAbsoluteUrls === undefined && sourceMap;

	if (options.convertToAbsoluteUrls || autoFixUrls) {
		css = fixUrls(css);
	}

	if (sourceMap) {
		// http://stackoverflow.com/a/26603875
		css += "\n/*# sourceMappingURL=data:application/json;base64," + btoa(unescape(encodeURIComponent(JSON.stringify(sourceMap)))) + " */";
	}

	var blob = new Blob([css], { type: "text/css" });

	var oldSrc = link.href;

	link.href = URL.createObjectURL(blob);

	if(oldSrc) URL.revokeObjectURL(oldSrc);
}


/***/ }),

/***/ "./node_modules/style-loader/lib/urls.js":
/*!***********************************************!*\
  !*** ./node_modules/style-loader/lib/urls.js ***!
  \***********************************************/
/*! no static exports found */
/***/ (function(module, exports) {


/**
 * When source maps are enabled, `style-loader` uses a link element with a data-uri to
 * embed the css on the page. This breaks all relative urls because now they are relative to a
 * bundle instead of the current page.
 *
 * One solution is to only use full urls, but that may be impossible.
 *
 * Instead, this function "fixes" the relative urls to be absolute according to the current page location.
 *
 * A rudimentary test suite is located at `test/fixUrls.js` and can be run via the `npm test` command.
 *
 */

module.exports = function (css) {
  // get current location
  var location = typeof window !== "undefined" && window.location;

  if (!location) {
    throw new Error("fixUrls requires window.location");
  }

	// blank or null?
	if (!css || typeof css !== "string") {
	  return css;
  }

  var baseUrl = location.protocol + "//" + location.host;
  var currentDir = baseUrl + location.pathname.replace(/\/[^\/]*$/, "/");

	// convert each url(...)
	/*
	This regular expression is just a way to recursively match brackets within
	a string.

	 /url\s*\(  = Match on the word "url" with any whitespace after it and then a parens
	   (  = Start a capturing group
	     (?:  = Start a non-capturing group
	         [^)(]  = Match anything that isn't a parentheses
	         |  = OR
	         \(  = Match a start parentheses
	             (?:  = Start another non-capturing groups
	                 [^)(]+  = Match anything that isn't a parentheses
	                 |  = OR
	                 \(  = Match a start parentheses
	                     [^)(]*  = Match anything that isn't a parentheses
	                 \)  = Match a end parentheses
	             )  = End Group
              *\) = Match anything and then a close parens
          )  = Close non-capturing group
          *  = Match anything
       )  = Close capturing group
	 \)  = Match a close parens

	 /gi  = Get all matches, not the first.  Be case insensitive.
	 */
	var fixedCss = css.replace(/url\s*\(((?:[^)(]|\((?:[^)(]+|\([^)(]*\))*\))*)\)/gi, function(fullMatch, origUrl) {
		// strip quotes (if they exist)
		var unquotedOrigUrl = origUrl
			.trim()
			.replace(/^"(.*)"$/, function(o, $1){ return $1; })
			.replace(/^'(.*)'$/, function(o, $1){ return $1; });

		// already a full url? no change
		if (/^(#|data:|http:\/\/|https:\/\/|file:\/\/\/|\s*$)/i.test(unquotedOrigUrl)) {
		  return fullMatch;
		}

		// convert the url to a full url
		var newUrl;

		if (unquotedOrigUrl.indexOf("//") === 0) {
		  	//TODO: should we add protocol?
			newUrl = unquotedOrigUrl;
		} else if (unquotedOrigUrl.indexOf("/") === 0) {
			// path should be relative to the base url
			newUrl = baseUrl + unquotedOrigUrl; // already starts with '/'
		} else {
			// path should be relative to current directory
			newUrl = currentDir + unquotedOrigUrl.replace(/^\.\//, ""); // Strip leading './'
		}

		// send back the fixed url(...)
		return "url(" + JSON.stringify(newUrl) + ")";
	});

	// send back the fixed css
	return fixedCss;
};


/***/ }),

/***/ "./node_modules/timers-browserify/main.js":
/*!************************************************!*\
  !*** ./node_modules/timers-browserify/main.js ***!
  \************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

/* WEBPACK VAR INJECTION */(function(global) {var scope = (typeof global !== "undefined" && global) ||
            (typeof self !== "undefined" && self) ||
            window;
var apply = Function.prototype.apply;

// DOM APIs, for completeness

exports.setTimeout = function() {
  return new Timeout(apply.call(setTimeout, scope, arguments), clearTimeout);
};
exports.setInterval = function() {
  return new Timeout(apply.call(setInterval, scope, arguments), clearInterval);
};
exports.clearTimeout =
exports.clearInterval = function(timeout) {
  if (timeout) {
    timeout.close();
  }
};

function Timeout(id, clearFn) {
  this._id = id;
  this._clearFn = clearFn;
}
Timeout.prototype.unref = Timeout.prototype.ref = function() {};
Timeout.prototype.close = function() {
  this._clearFn.call(scope, this._id);
};

// Does not start the time, just sets up the members needed.
exports.enroll = function(item, msecs) {
  clearTimeout(item._idleTimeoutId);
  item._idleTimeout = msecs;
};

exports.unenroll = function(item) {
  clearTimeout(item._idleTimeoutId);
  item._idleTimeout = -1;
};

exports._unrefActive = exports.active = function(item) {
  clearTimeout(item._idleTimeoutId);

  var msecs = item._idleTimeout;
  if (msecs >= 0) {
    item._idleTimeoutId = setTimeout(function onTimeout() {
      if (item._onTimeout)
        item._onTimeout();
    }, msecs);
  }
};

// setimmediate attaches itself to the global object
__webpack_require__(/*! setimmediate */ "./node_modules/setimmediate/setImmediate.js");
// On some exotic environments, it's not clear which object `setimmediate` was
// able to install onto.  Search each possibility in the same order as the
// `setimmediate` library.
exports.setImmediate = (typeof self !== "undefined" && self.setImmediate) ||
                       (typeof global !== "undefined" && global.setImmediate) ||
                       (this && this.setImmediate);
exports.clearImmediate = (typeof self !== "undefined" && self.clearImmediate) ||
                         (typeof global !== "undefined" && global.clearImmediate) ||
                         (this && this.clearImmediate);

/* WEBPACK VAR INJECTION */}.call(this, __webpack_require__(/*! ./../webpack/buildin/global.js */ "./node_modules/webpack/buildin/global.js")))

/***/ }),

/***/ "./node_modules/vue-chartkick/dist/vue-chartkick.esm.js":
/*!**************************************************************!*\
  !*** ./node_modules/vue-chartkick/dist/vue-chartkick.esm.js ***!
  \**************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var chartkick__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! chartkick */ "./node_modules/chartkick/dist/chartkick.esm.js");
/* harmony import */ var deep_equal__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! deep-equal */ "./node_modules/deep-equal/index.js");
/* harmony import */ var deep_equal__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(deep_equal__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var deepmerge__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! deepmerge */ "./node_modules/deepmerge/dist/es.js");
/*
 * Vue Chartkick
 * Create beautiful JavaScript charts with one line of Vue
 * https://github.com/ankane/vue-chartkick
 * v0.5.0
 * MIT License
 */





var chartId = 1;

var createComponent = function(Vue, tagName, chartType) {
  var chartProps = [
    "colors", "curve", "dataset", "decimal", "discrete", "donut", "download", "label",
    "legend", "library", "max", "messages", "min", "points", "prefix", "refresh",
    "stacked", "suffix", "thousands", "title", "xtitle", "ytitle"
  ];
  Vue.component(tagName, {
    props: ["data", "id", "width", "height"].concat(chartProps),
    render: function(createElement) {
      return createElement(
        "div",
        {
          attrs: {
            id: this.chartId
          },
          style: this.chartStyle
        },
        ["Loading..."]
      )
    },
    data: function() {
      return {
        chartId: null
      }
    },
    computed: {
      chartStyle: function() {
        // hack to watch data and options
        this.data;
        this.chartOptions;

        return {
          height: this.height || "300px",
          lineHeight: this.height || "300px",
          width: this.width || "100%",
          textAlign: "center",
          color: "#999",
          fontSize: "14px",
          fontFamily: "'Lucida Grande', 'Lucida Sans Unicode', Verdana, Arial, Helvetica, sans-serif"
        }
      },
      chartOptions: function() {
        var this$1 = this;

        var options = {};
        var props = chartProps;
        for (var i = 0; i < props.length; i++) {
          var prop = props[i];
          if (this$1[prop] !== undefined) {
            options[prop] = this$1[prop];
          }
        }
        return options
      }
    },
    created: function() {
      this.chartId = this.chartId || this.id || ("chart-" + chartId++);
    },
    mounted: function() {
      this.updateChart();
      this.savedState = this.currentState();
    },
    updated: function() {
      // avoid updates when literal objects are used as props
      // see https://github.com/ankane/vue-chartkick/pull/52
      // and https://github.com/vuejs/vue/issues/4060
      var currentState = this.currentState();
      if (!deep_equal__WEBPACK_IMPORTED_MODULE_1___default()(currentState, this.savedState)) {
        this.updateChart();
        this.savedState = currentState;
      }
    },
    beforeDestroy: function() {
      if (this.chart) {
        this.chart.destroy();
      }
    },
    methods: {
      updateChart: function() {
        if (this.data !== null) {
          if (this.chart) {
            this.chart.updateData(this.data, this.chartOptions);
          } else {
            this.chart = new chartType(this.chartId, this.data, this.chartOptions);
          }
        } else if (this.chart) {
          this.chart.destroy();
          this.chart = null;
          this.$el.innerText = "Loading...";
        }
      },
      currentState: function() {
        return Object(deepmerge__WEBPACK_IMPORTED_MODULE_2__["default"])({}, {
          data: this.data,
          chartOptions: this.chartOptions
        })
      }
    }
  });
};

var VueChartkick = {
  version: "0.5.0",
  install: function(Vue, options) {
    if (options && options.adapter) {
      chartkick__WEBPACK_IMPORTED_MODULE_0__["default"].addAdapter(options.adapter);
    }
    createComponent(Vue, "line-chart", chartkick__WEBPACK_IMPORTED_MODULE_0__["default"].LineChart);
    createComponent(Vue, "pie-chart", chartkick__WEBPACK_IMPORTED_MODULE_0__["default"].PieChart);
    createComponent(Vue, "column-chart", chartkick__WEBPACK_IMPORTED_MODULE_0__["default"].ColumnChart);
    createComponent(Vue, "bar-chart", chartkick__WEBPACK_IMPORTED_MODULE_0__["default"].BarChart);
    createComponent(Vue, "area-chart", chartkick__WEBPACK_IMPORTED_MODULE_0__["default"].AreaChart);
    createComponent(Vue, "scatter-chart", chartkick__WEBPACK_IMPORTED_MODULE_0__["default"].ScatterChart);
    createComponent(Vue, "geo-chart", chartkick__WEBPACK_IMPORTED_MODULE_0__["default"].GeoChart);
    createComponent(Vue, "timeline", chartkick__WEBPACK_IMPORTED_MODULE_0__["default"].Timeline);
  },
  addAdapter: function(library) {
    chartkick__WEBPACK_IMPORTED_MODULE_0__["default"].addAdapter(library);
  }
};

// in browser
if (typeof window !== "undefined" && window.Vue) {
  window.Vue.use(VueChartkick);
}

/* harmony default export */ __webpack_exports__["default"] = (VueChartkick);


/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/consultentries.vue?vue&type=template&id=38b5f7a2&scoped=true&":
/*!*****************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/components/consultentries.vue?vue&type=template&id=38b5f7a2&scoped=true& ***!
  \*****************************************************************************************************************************************************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "render", function() { return render; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "staticRenderFns", function() { return staticRenderFns; });
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c("svg", { attrs: { width: "200", height: "500" } })
}
var staticRenderFns = []
render._withStripped = true



/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/editproject.vue?vue&type=template&id=2718bd58&":
/*!**************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib??vue-loader-options!./resources/js/components/editproject.vue?vue&type=template&id=2718bd58& ***!
  \**************************************************************************************************************************************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "render", function() { return render; });
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "staticRenderFns", function() { return staticRenderFns; });
var render = function() {
  var _vm = this
  var _h = _vm.$createElement
  var _c = _vm._self._c || _h
  return _c("div", { staticClass: "content" }, [
    _c("label", { staticClass: "checkbox" }, [
      _c("input", {
        directives: [
          {
            name: "model",
            rawName: "v-model",
            value: _vm.thisproject.edit,
            expression: "thisproject.edit"
          }
        ],
        attrs: { type: "checkbox" },
        domProps: {
          checked: Array.isArray(_vm.thisproject.edit)
            ? _vm._i(_vm.thisproject.edit, null) > -1
            : _vm.thisproject.edit
        },
        on: {
          change: function($event) {
            var $$a = _vm.thisproject.edit,
              $$el = $event.target,
              $$c = $$el.checked ? true : false
            if (Array.isArray($$a)) {
              var $$v = null,
                $$i = _vm._i($$a, $$v)
              if ($$el.checked) {
                $$i < 0 && _vm.$set(_vm.thisproject, "edit", $$a.concat([$$v]))
              } else {
                $$i > -1 &&
                  _vm.$set(
                    _vm.thisproject,
                    "edit",
                    $$a.slice(0, $$i).concat($$a.slice($$i + 1))
                  )
              }
            } else {
              _vm.$set(_vm.thisproject, "edit", $$c)
            }
          }
        }
      }),
      _vm._v("\n          Edit name and description\n      ")
    ]),
    _vm._v(" "),
    _c("div", { staticClass: "level" }, [
      _c("input", {
        attrs: { type: "hidden", name: "id" },
        domProps: { value: _vm.project.id }
      }),
      _vm._v(" "),
      _c("div", { staticClass: "level-left" }, [
        !_vm.thisproject.edit
          ? _c("h1", { domProps: { innerHTML: _vm._s(_vm.thisproject.name) } })
          : _vm._e(),
        _vm._v(" "),
        _vm.thisproject.edit
          ? _c("div", { staticClass: "field has-addons" }, [
              _c("input", {
                directives: [
                  {
                    name: "model",
                    rawName: "v-model",
                    value: _vm.thisproject.name,
                    expression: "thisproject.name"
                  }
                ],
                staticClass: "input text is-large",
                attrs: { name: "name" },
                domProps: { value: _vm.thisproject.name },
                on: {
                  input: function($event) {
                    if ($event.target.composing) {
                      return
                    }
                    _vm.$set(_vm.thisproject, "name", $event.target.value)
                  }
                }
              }),
              _vm._v(" "),
              _c("div", { staticClass: "control" })
            ])
          : _vm._e()
      ]),
      _vm._v(" "),
      _vm._m(0)
    ]),
    _vm._v(" "),
    _c("div", { staticClass: "level" }, [
      _c("input", {
        attrs: { type: "hidden", name: "id" },
        domProps: { value: _vm.project.id }
      }),
      _vm._v(" "),
      _c("div", { staticClass: "level-left" }, [
        !_vm.thisproject.edit
          ? _c("p", {
              domProps: { innerHTML: _vm._s(_vm.thisproject.description) }
            })
          : _vm._e(),
        _vm._v(" "),
        _vm.thisproject.edit
          ? _c("div", { staticClass: "field has-addons" }, [
              _c("textarea", {
                directives: [
                  {
                    name: "model",
                    rawName: "v-model",
                    value: _vm.thisproject.description,
                    expression: "thisproject.description"
                  }
                ],
                staticClass: "input textarea",
                attrs: { name: "description" },
                domProps: { value: _vm.thisproject.description },
                on: {
                  input: function($event) {
                    if ($event.target.composing) {
                      return
                    }
                    _vm.$set(
                      _vm.thisproject,
                      "description",
                      $event.target.value
                    )
                  }
                }
              }),
              _vm._v(" "),
              _c("div", { staticClass: "control" })
            ])
          : _vm._e()
      ]),
      _vm._v(" "),
      _c("div", { staticClass: "level-right" })
    ]),
    _vm._v(" "),
    _c("div", { staticClass: "level" }, [
      _c("div", { staticClass: "field" }, [
        _c("h3", [_vm._v(" Duration")]),
        _vm._v(" "),
        !_vm.thisproject.edit
          ? _c("p", {
              domProps: { innerHTML: _vm._s(_vm.thisproject.duration) }
            })
          : _vm._e(),
        _vm._v(" "),
        _vm.thisproject.edit
          ? _c("div", { staticClass: "field has-addons" }, [
              _c("input", {
                directives: [
                  {
                    name: "model",
                    rawName: "v-model",
                    value: _vm.thisproject.duration,
                    expression: "thisproject.duration"
                  }
                ],
                staticClass: "input text",
                attrs: { name: "duration" },
                domProps: { value: _vm.thisproject.duration },
                on: {
                  input: function($event) {
                    if ($event.target.composing) {
                      return
                    }
                    _vm.$set(_vm.thisproject, "duration", $event.target.value)
                  }
                }
              })
            ])
          : _vm._e()
      ])
    ]),
    _vm._v(" "),
    _c("h2", [_vm._v("Inputs")]),
    _vm._v(" "),
    _c(
      "div",
      { staticClass: "columns is-multiline is-mobile" },
      _vm._l(_vm.project.inputs, function(c, index) {
        return _c(
          "div",
          { key: index, staticClass: "inputs column is-4" },
          [
            _c("h4", { domProps: { innerHTML: _vm._s(c.name) } }),
            _vm._v(" "),
            _c("p", { domProps: { innerHTML: _vm._s(c.type) } }),
            _vm._v(" "),
            _vm._l(c.answers, function(answer, index) {
              return c.answers
                ? _c("div", [
                    _c("p", { domProps: { innerHTML: _vm._s(answer) } })
                  ])
                : _vm._e()
            })
          ],
          2
        )
      }),
      0
    ),
    _vm._v(" "),
    _c("input", {
      attrs: { type: "hidden", name: "inputs" },
      domProps: { value: JSON.stringify(_vm.thisproject.inputs) }
    }),
    _vm._v(" "),
    _c("div", { staticClass: "field" }, [
      _c("label", { staticClass: "label", attrs: { for: "ninputs" } }, [
        _vm._v("\n            Number of inputs\n        ")
      ]),
      _vm._v(" "),
      _c("div", { staticClass: "control" }, [
        _c("input", {
          directives: [
            {
              name: "model",
              rawName: "v-model.number",
              value: _vm.thisproject.ninputs,
              expression: "thisproject.ninputs",
              modifiers: { number: true }
            }
          ],
          staticClass: "input",
          attrs: {
            type: "number",
            id: "ninputs",
            min: "0",
            max: "10",
            value: "1"
          },
          domProps: { value: _vm.thisproject.ninputs },
          on: {
            input: function($event) {
              if ($event.target.composing) {
                return
              }
              _vm.$set(_vm.thisproject, "ninputs", _vm._n($event.target.value))
            },
            blur: function($event) {
              return _vm.$forceUpdate()
            }
          }
        })
      ])
    ]),
    _vm._v(" "),
    _c(
      "div",
      { staticClass: "columns is-multiline is-mobile" },
      _vm._l(_vm.thisproject.inputs, function(t, index) {
        return _c("div", { key: index, staticClass: "inputs" }, [
          _c("div", { staticClass: "column" }, [
            _c("div", { staticClass: "field" }, [
              _c("label", { staticClass: "label", attrs: { for: "name" } }, [
                _vm._v(
                  "\n                        Input Name\n                    "
                )
              ]),
              _vm._v(" "),
              _c("div", { staticClass: "control" }, [
                _c("input", {
                  directives: [
                    {
                      name: "model",
                      rawName: "v-model",
                      value: _vm.thisproject.inputs[index].name,
                      expression: "thisproject.inputs[index].name"
                    }
                  ],
                  staticClass: "input",
                  attrs: { type: "text" },
                  domProps: { value: _vm.thisproject.inputs[index].name },
                  on: {
                    input: function($event) {
                      if ($event.target.composing) {
                        return
                      }
                      _vm.$set(
                        _vm.thisproject.inputs[index],
                        "name",
                        $event.target.value
                      )
                    }
                  }
                })
              ])
            ])
          ]),
          _vm._v(" "),
          _c("div", { staticClass: "column" }, [
            _c("div", { staticClass: "field" }, [
              _c("label", { staticClass: "checkbox" }, [
                _c("input", {
                  directives: [
                    {
                      name: "model",
                      rawName: "v-model",
                      value: _vm.thisproject.inputs[index].mandatory,
                      expression: "thisproject.inputs[index].mandatory"
                    }
                  ],
                  attrs: { type: "checkbox", checked: "checked" },
                  domProps: {
                    checked: Array.isArray(
                      _vm.thisproject.inputs[index].mandatory
                    )
                      ? _vm._i(_vm.thisproject.inputs[index].mandatory, null) >
                        -1
                      : _vm.thisproject.inputs[index].mandatory
                  },
                  on: {
                    change: function($event) {
                      var $$a = _vm.thisproject.inputs[index].mandatory,
                        $$el = $event.target,
                        $$c = $$el.checked ? true : false
                      if (Array.isArray($$a)) {
                        var $$v = null,
                          $$i = _vm._i($$a, $$v)
                        if ($$el.checked) {
                          $$i < 0 &&
                            _vm.$set(
                              _vm.thisproject.inputs[index],
                              "mandatory",
                              $$a.concat([$$v])
                            )
                        } else {
                          $$i > -1 &&
                            _vm.$set(
                              _vm.thisproject.inputs[index],
                              "mandatory",
                              $$a.slice(0, $$i).concat($$a.slice($$i + 1))
                            )
                        }
                      } else {
                        _vm.$set(
                          _vm.thisproject.inputs[index],
                          "mandatory",
                          $$c
                        )
                      }
                    }
                  }
                }),
                _vm._v(
                  "\n                        Mandatory\n                    "
                )
              ])
            ]),
            _vm._v(" "),
            _vm.thisproject.inputs[index].type == "multiple choice" ||
            _vm.thisproject.inputs[index].type == "one choice"
              ? _c(
                  "span",
                  [
                    _c("div", { staticClass: "field" }, [
                      _c("label", { staticClass: "label" }, [
                        _vm._v("Number of Answers")
                      ]),
                      _vm._v(" "),
                      _c("div", { staticClass: "control" }, [
                        _c("input", {
                          directives: [
                            {
                              name: "model",
                              rawName: "v-model.number",
                              value:
                                _vm.thisproject.inputs[index].numberofanswer,
                              expression:
                                "thisproject.inputs[index].numberofanswer",
                              modifiers: { number: true }
                            }
                          ],
                          staticClass: "input",
                          attrs: { type: "number", placeholder: "" },
                          domProps: {
                            value: _vm.thisproject.inputs[index].numberofanswer
                          },
                          on: {
                            input: function($event) {
                              if ($event.target.composing) {
                                return
                              }
                              _vm.$set(
                                _vm.thisproject.inputs[index],
                                "numberofanswer",
                                _vm._n($event.target.value)
                              )
                            },
                            blur: function($event) {
                              return _vm.$forceUpdate()
                            }
                          }
                        })
                      ])
                    ]),
                    _vm._v(" "),
                    _vm._l(
                      _vm.thisproject.inputs[index].numberofanswer,
                      function(na) {
                        return _c("div", { staticClass: "field" }, [
                          _c("label", { staticClass: "label" }, [
                            _vm._v("Answers")
                          ]),
                          _vm._v(" "),
                          _c("div", { staticClass: "control" }, [
                            _c("input", {
                              directives: [
                                {
                                  name: "model",
                                  rawName: "v-model",
                                  value:
                                    _vm.thisproject.inputs[index].answers[
                                      na - 1
                                    ],
                                  expression:
                                    "thisproject.inputs[index].answers[na-1]"
                                }
                              ],
                              staticClass: "input",
                              attrs: { type: "text", placeholder: "" },
                              domProps: {
                                value:
                                  _vm.thisproject.inputs[index].answers[na - 1]
                              },
                              on: {
                                input: function($event) {
                                  if ($event.target.composing) {
                                    return
                                  }
                                  _vm.$set(
                                    _vm.thisproject.inputs[index].answers,
                                    na - 1,
                                    $event.target.value
                                  )
                                }
                              }
                            })
                          ])
                        ])
                      }
                    )
                  ],
                  2
                )
              : _vm._e()
          ])
        ])
      }),
      0
    ),
    _vm._v(" "),
    _c("div", { staticClass: "columns" }, [
      _c("div", { staticClass: "column is-4" }, [
        _c(
          "details",
          [
            _c("summary", [
              _c(
                "label",
                {
                  staticClass: "label",
                  staticStyle: { display: "inline-flex" },
                  attrs: { for: "media" }
                },
                [
                  _vm._v("\n                    Media\n                    "),
                  _c(
                    "label",
                    {
                      staticClass: "checkbox",
                      staticStyle: {
                        display: "inline-block",
                        "margin-left": "5px"
                      },
                      attrs: { id: "media[]" },
                      on: {
                        click: function($event) {
                          return _vm.selectAllHandler("media")
                        }
                      }
                    },
                    [
                      _c("input", {
                        attrs: { type: "checkbox", id: "mediai" }
                      }),
                      _vm._v(
                        "\n                        Select all\n                    "
                      )
                    ]
                  )
                ]
              )
            ]),
            _vm._v(" "),
            _vm._l(_vm.data.media, function(m) {
              return _c("div", { staticClass: "field" }, [
                _c("label", { staticClass: "checkbox" }, [
                  _c("input", {
                    directives: [
                      {
                        name: "model",
                        rawName: "v-model",
                        value: _vm.thisproject.checkedmedia,
                        expression: "thisproject.checkedmedia"
                      }
                    ],
                    attrs: { type: "checkbox", id: m.id, name: "media" },
                    domProps: {
                      value: m.id,
                      checked: Array.isArray(_vm.thisproject.checkedmedia)
                        ? _vm._i(_vm.thisproject.checkedmedia, m.id) > -1
                        : _vm.thisproject.checkedmedia
                    },
                    on: {
                      change: function($event) {
                        var $$a = _vm.thisproject.checkedmedia,
                          $$el = $event.target,
                          $$c = $$el.checked ? true : false
                        if (Array.isArray($$a)) {
                          var $$v = m.id,
                            $$i = _vm._i($$a, $$v)
                          if ($$el.checked) {
                            $$i < 0 &&
                              _vm.$set(
                                _vm.thisproject,
                                "checkedmedia",
                                $$a.concat([$$v])
                              )
                          } else {
                            $$i > -1 &&
                              _vm.$set(
                                _vm.thisproject,
                                "checkedmedia",
                                $$a.slice(0, $$i).concat($$a.slice($$i + 1))
                              )
                          }
                        } else {
                          _vm.$set(_vm.thisproject, "checkedmedia", $$c)
                        }
                      }
                    }
                  }),
                  _vm._v(
                    "\n                    " +
                      _vm._s(m.name) +
                      "\n                "
                  )
                ]),
                _c("br")
              ])
            })
          ],
          2
        )
      ]),
      _vm._v(" "),
      _c("div", { staticClass: "column is-4" }, [
        _c(
          "details",
          [
            _c("summary", [
              _c(
                "label",
                {
                  staticClass: "label",
                  staticStyle: { display: "inline-flex" },
                  attrs: { for: "places" }
                },
                [
                  _vm._v("\n                    Places\n                    "),
                  _c(
                    "label",
                    {
                      staticClass: "checkbox",
                      staticStyle: {
                        display: "inline-block",
                        "margin-left": "5px"
                      },
                      attrs: { id: "places" },
                      on: {
                        click: function($event) {
                          return _vm.selectAllHandler("places")
                        }
                      }
                    },
                    [
                      _c("input", {
                        attrs: { type: "checkbox", id: "placesi" }
                      }),
                      _vm._v(
                        "\n                        Select all\n                    "
                      )
                    ]
                  )
                ]
              )
            ]),
            _vm._v(" "),
            _vm._l(_vm.data.places, function(p) {
              return _c("div", { staticClass: "field" }, [
                _c("label", { staticClass: "checkbox" }, [
                  _c("input", {
                    directives: [
                      {
                        name: "model",
                        rawName: "v-model",
                        value: _vm.thisproject.checkedplaces,
                        expression: "thisproject.checkedplaces"
                      }
                    ],
                    attrs: { type: "checkbox", id: p.id, name: "places" },
                    domProps: {
                      value: p.id,
                      checked: Array.isArray(_vm.thisproject.checkedplaces)
                        ? _vm._i(_vm.thisproject.checkedplaces, p.id) > -1
                        : _vm.thisproject.checkedplaces
                    },
                    on: {
                      change: function($event) {
                        var $$a = _vm.thisproject.checkedplaces,
                          $$el = $event.target,
                          $$c = $$el.checked ? true : false
                        if (Array.isArray($$a)) {
                          var $$v = p.id,
                            $$i = _vm._i($$a, $$v)
                          if ($$el.checked) {
                            $$i < 0 &&
                              _vm.$set(
                                _vm.thisproject,
                                "checkedplaces",
                                $$a.concat([$$v])
                              )
                          } else {
                            $$i > -1 &&
                              _vm.$set(
                                _vm.thisproject,
                                "checkedplaces",
                                $$a.slice(0, $$i).concat($$a.slice($$i + 1))
                              )
                          }
                        } else {
                          _vm.$set(_vm.thisproject, "checkedplaces", $$c)
                        }
                      }
                    }
                  }),
                  _vm._v(
                    "\n                    " +
                      _vm._s(p.name) +
                      "\n                "
                  )
                ]),
                _c("br")
              ])
            })
          ],
          2
        )
      ]),
      _vm._v(" "),
      _c("div", { staticClass: "column is-4" }, [
        _c(
          "details",
          [
            _c("summary", [
              _c(
                "label",
                {
                  staticClass: "label",
                  staticStyle: { display: "inline-flex" },
                  attrs: { for: "cp" }
                },
                [
                  _vm._v(
                    "\n                    Communication Partner\n                    "
                  ),
                  _c(
                    "label",
                    {
                      staticClass: "checkbox",
                      staticStyle: {
                        display: "inline-block",
                        "margin-left": "5px"
                      },
                      attrs: { id: "cp" },
                      on: {
                        click: function($event) {
                          return _vm.selectAllHandler("cp")
                        }
                      }
                    },
                    [
                      _c("input", { attrs: { type: "checkbox", id: "cpi" } }),
                      _vm._v(
                        "\n                        Select all\n                    "
                      )
                    ]
                  )
                ]
              )
            ]),
            _vm._v(" "),
            _vm._l(_vm.data.cp, function(c) {
              return _c("div", { staticClass: "field" }, [
                _c("label", { staticClass: "checkbox" }, [
                  _c("input", {
                    directives: [
                      {
                        name: "model",
                        rawName: "v-model",
                        value: _vm.thisproject.checkedcp,
                        expression: "thisproject.checkedcp"
                      }
                    ],
                    attrs: { type: "checkbox", id: c.id, name: "cp" },
                    domProps: {
                      value: c.id,
                      checked: Array.isArray(_vm.thisproject.checkedcp)
                        ? _vm._i(_vm.thisproject.checkedcp, c.id) > -1
                        : _vm.thisproject.checkedcp
                    },
                    on: {
                      change: function($event) {
                        var $$a = _vm.thisproject.checkedcp,
                          $$el = $event.target,
                          $$c = $$el.checked ? true : false
                        if (Array.isArray($$a)) {
                          var $$v = c.id,
                            $$i = _vm._i($$a, $$v)
                          if ($$el.checked) {
                            $$i < 0 &&
                              _vm.$set(
                                _vm.thisproject,
                                "checkedcp",
                                $$a.concat([$$v])
                              )
                          } else {
                            $$i > -1 &&
                              _vm.$set(
                                _vm.thisproject,
                                "checkedcp",
                                $$a.slice(0, $$i).concat($$a.slice($$i + 1))
                              )
                          }
                        } else {
                          _vm.$set(_vm.thisproject, "checkedcp", $$c)
                        }
                      }
                    }
                  }),
                  _vm._v(
                    "\n                    " +
                      _vm._s(c.name) +
                      "\n                "
                  )
                ]),
                _c("br")
              ])
            })
          ],
          2
        )
      ])
    ]),
    _vm._v(" "),
    _c("div", { staticClass: "field" }, [
      _c("div", { staticClass: "control" }, [
        _c(
          "button",
          {
            staticClass: "button is-link",
            on: {
              click: function($event) {
                if (
                  !$event.type.indexOf("key") &&
                  _vm._k(
                    $event.keyCode,
                    "preventdefault",
                    undefined,
                    $event.key,
                    undefined
                  )
                ) {
                  return null
                }
                return _vm.save($event)
              }
            }
          },
          [_vm._v("Edit Project")]
        )
      ])
    ]),
    _vm._v(" "),
    _c("div", { staticClass: "level" }, [
      _c("div", { staticClass: "columns" }, [
        _c("div", { staticClass: "column" }, [
          _vm.response != ""
            ? _c(
                "div",
                {
                  staticClass: "notification is-danger",
                  domProps: { innerHTML: _vm._s(_vm.response) }
                },
                [
                  _c("button", {
                    staticClass: "delete",
                    on: {
                      click: function($event) {
                        if (
                          !$event.type.indexOf("key") &&
                          _vm._k(
                            $event.keyCode,
                            "preventdefault",
                            undefined,
                            $event.key,
                            undefined
                          )
                        ) {
                          return null
                        }
                        _vm.response = ""
                      }
                    }
                  })
                ]
              )
            : _vm._e()
        ])
      ])
    ])
  ])
}
var staticRenderFns = [
  function() {
    var _vm = this
    var _h = _vm.$createElement
    var _c = _vm._self._c || _h
    return _c("div", { staticClass: "level-right" }, [
      _c("div", { staticClass: "field" })
    ])
  }
]
render._withStripped = true



/***/ }),

/***/ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js":
/*!********************************************************************!*\
  !*** ./node_modules/vue-loader/lib/runtime/componentNormalizer.js ***!
  \********************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, "default", function() { return normalizeComponent; });
/* globals __VUE_SSR_CONTEXT__ */

// IMPORTANT: Do NOT use ES2015 features in this file (except for modules).
// This module is a runtime utility for cleaner component module output and will
// be included in the final webpack user bundle.

function normalizeComponent (
  scriptExports,
  render,
  staticRenderFns,
  functionalTemplate,
  injectStyles,
  scopeId,
  moduleIdentifier, /* server only */
  shadowMode /* vue-cli only */
) {
  // Vue.extend constructor export interop
  var options = typeof scriptExports === 'function'
    ? scriptExports.options
    : scriptExports

  // render functions
  if (render) {
    options.render = render
    options.staticRenderFns = staticRenderFns
    options._compiled = true
  }

  // functional template
  if (functionalTemplate) {
    options.functional = true
  }

  // scopedId
  if (scopeId) {
    options._scopeId = 'data-v-' + scopeId
  }

  var hook
  if (moduleIdentifier) { // server build
    hook = function (context) {
      // 2.3 injection
      context =
        context || // cached call
        (this.$vnode && this.$vnode.ssrContext) || // stateful
        (this.parent && this.parent.$vnode && this.parent.$vnode.ssrContext) // functional
      // 2.2 with runInNewContext: true
      if (!context && typeof __VUE_SSR_CONTEXT__ !== 'undefined') {
        context = __VUE_SSR_CONTEXT__
      }
      // inject component styles
      if (injectStyles) {
        injectStyles.call(this, context)
      }
      // register component module identifier for async chunk inferrence
      if (context && context._registeredComponents) {
        context._registeredComponents.add(moduleIdentifier)
      }
    }
    // used by ssr in case component is cached and beforeCreate
    // never gets called
    options._ssrRegister = hook
  } else if (injectStyles) {
    hook = shadowMode
      ? function () { injectStyles.call(this, this.$root.$options.shadowRoot) }
      : injectStyles
  }

  if (hook) {
    if (options.functional) {
      // for template-only hot-reload because in that case the render fn doesn't
      // go through the normalizer
      options._injectStyles = hook
      // register for functioal component in vue file
      var originalRender = options.render
      options.render = function renderWithStyleInjection (h, context) {
        hook.call(context)
        return originalRender(h, context)
      }
    } else {
      // inject component registration as beforeCreate hook
      var existing = options.beforeCreate
      options.beforeCreate = existing
        ? [].concat(existing, hook)
        : [hook]
    }
  }

  return {
    exports: scriptExports,
    options: options
  }
}


/***/ }),

/***/ "./node_modules/webpack/buildin/global.js":
/*!***********************************!*\
  !*** (webpack)/buildin/global.js ***!
  \***********************************/
/*! no static exports found */
/***/ (function(module, exports) {

var g;

// This works in non-strict mode
g = (function() {
	return this;
})();

try {
	// This works if eval is allowed (see CSP)
	g = g || new Function("return this")();
} catch (e) {
	// This works if the window reference is available
	if (typeof window === "object") g = window;
}

// g can still be undefined, but nothing to do about it...
// We return undefined, instead of nothing here, so it's
// easier to handle this case. if(!global) { ...}

module.exports = g;


/***/ }),

/***/ "./node_modules/webpack/buildin/module.js":
/*!***********************************!*\
  !*** (webpack)/buildin/module.js ***!
  \***********************************/
/*! no static exports found */
/***/ (function(module, exports) {

module.exports = function(module) {
	if (!module.webpackPolyfill) {
		module.deprecate = function() {};
		module.paths = [];
		// module.parent = undefined by default
		if (!module.children) module.children = [];
		Object.defineProperty(module, "loaded", {
			enumerable: true,
			get: function() {
				return module.l;
			}
		});
		Object.defineProperty(module, "id", {
			enumerable: true,
			get: function() {
				return module.i;
			}
		});
		module.webpackPolyfill = 1;
	}
	return module;
};


/***/ }),

/***/ "./resources/js/app.js":
/*!*****************************!*\
  !*** ./resources/js/app.js ***!
  \*****************************/
/*! no exports provided */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var buefy__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! buefy */ "./node_modules/buefy/dist/buefy.js");
/* harmony import */ var buefy__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(buefy__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var google_charts__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! google-charts */ "./node_modules/google-charts/dist/googleCharts.esm.js");
/* harmony import */ var vue_chartkick__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! vue-chartkick */ "./node_modules/vue-chartkick/dist/vue-chartkick.esm.js");
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */
__webpack_require__(/*! ./bootstrap */ "./resources/js/bootstrap.js");


window.Vue = __webpack_require__(/*! vue */ "./node_modules/vue/dist/vue.common.js");
/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */
// const files = require.context('./', true, /\.vue$/i);
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default));

Vue.component('edit-project', __webpack_require__(/*! ./components/editproject.vue */ "./resources/js/components/editproject.vue").default);
Vue.component('consult-entries', __webpack_require__(/*! ./components/consultentries.vue */ "./resources/js/components/consultentries.vue").default);
Vue.use(buefy__WEBPACK_IMPORTED_MODULE_0___default.a);


Vue.use(vue_chartkick__WEBPACK_IMPORTED_MODULE_2__["default"]);
Vue.use(google_charts__WEBPACK_IMPORTED_MODULE_1__["GoogleCharts"]);
/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

var app = new Vue({
  el: '#app',
  computed: {
    'newproject.formattedinputstring': function newprojectFormattedinputstring() {
      console.log("computed formattedstring");
      return JSON.stringify(this.newproject.inputs);
    }
  },
  watch: {
    'newproject.duration.selectedUnit': function newprojectDurationSelectedUnit(newVal, OldVal) {
      if (!_.isEmpty(this.newproject.duration.input)) {
        console.log(newVal);
        if (newVal == 'week') var numberOfDaysToAdd = parseInt(this.newproject.duration.input) * 7;else var numberOfDaysToAdd = parseInt(this.newproject.duration.input);
        var today = new Date();
        today.setDate(today.getDate() + numberOfDaysToAdd);
        var dd = today.getDate();
        var mm = today.getMonth() + 1;
        var y = today.getFullYear();
        this.newproject.duration.message = dd + '.' + mm + '.' + y;
      }
    },
    'newproject.duration.input': function newprojectDurationInput(newVal, OldVal) {
      this.newproject.duration.input = newVal.replace(/\D/g, '');

      if (!_.isEmpty(this.newproject.duration.selectedUnit)) {
        console.log(newVal);
        if (this.newproject.duration.selectedUnit == 'week') var numberOfDaysToAdd = parseInt(newVal) * 7;else var numberOfDaysToAdd = parseInt(newVal);
        var today = new Date();
        today.setDate(today.getDate() + numberOfDaysToAdd);
        var dd = today.getDate();
        var mm = today.getMonth() + 1;
        var y = today.getFullYear();
        this.newproject.duration.message = dd + '.' + mm + '.' + y;
      }
    },
    'newproject.ninputs': function newprojectNinputs(newVal, oldVal) {
      console.log("token number watcher fired");

      if (newVal < 0 || oldVal < 0) {
        newVal = 0;
        oldVal = 0;
      }

      var direction = newVal - oldVal;

      if (direction > 0) {
        var inputtemplate = {
          name: "",
          type: "",
          mandatory: true,
          numberofanswer: 0,
          answers: []
        };

        for (var i = 0; i < direction; i++) {
          this.newproject.inputs.push(inputtemplate);
        }
      } else if (newVal == 0) {
        // special case
        this.newproject.inputs = [];
      } else {
        // decrease
        for (var i = 0; i < Math.abs(direction); i++) {
          this.newproject.inputs.pop();
        }
      }
    }
  },
  data: {
    errormessages: {
      namemissing: "name is required. <br>",
      inputnamemissing: "input name is required. <br>",
      inputtypemissing: "input type is required. <br>",
      multipleinputnoanswer: "provide a valid number of answers. <br>"
    },
    newproject: {
      name: "",
      duration: {
        input: "",
        selectedUnit: "",
        allowedUnits: ["day(s)", "week(s)"],
        message: ""
      },
      ninputs: 0,
      inputs: [],
      config: window.inputs,
      response: ""
    }
  },
  methods: {
    validateSubmitCaseForm: function validateSubmitCaseForm() {
      this.newproject.response = "";
      if (this.newproject.name == "") this.newproject.response += this.errormessages.namemissing;

      if (this.newproject.ninputs > 0) {
        if (_.find(this.newproject.inputs, {
          name: ''
        })) this.newproject.response += this.errormessages.inputnamemissing;
        if (_.find(this.newproject.inputs, {
          type: ''
        })) this.newproject.response += this.errormessages.inputtypemissing; // if multiple or onechoice and no answers throw error

        if (_.find(this.newproject.inputs, function (o) {
          if (o.type == 'multiple choice' || o.type == 'one choice' && o.numberofanswer != o.answers.length) return true;
        })) this.newproject.response += this.errormessages.multipleinputnoanswer;
      }

      if (this.newproject.response == "") return true;else return false;
    }
  }
});

/***/ }),

/***/ "./resources/js/bootstrap.js":
/*!***********************************!*\
  !*** ./resources/js/bootstrap.js ***!
  \***********************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

window._ = __webpack_require__(/*! lodash */ "./node_modules/lodash/lodash.js");
/**
 * We'll load jQuery and the Bootstrap jQuery plugin which provides support
 * for JavaScript based Bootstrap features such as modals and tabs. This
 * code may be modified to fit the specific needs of your application.
 */

try {
  window.Popper = __webpack_require__(/*! popper.js */ "./node_modules/popper.js/dist/esm/popper.js").default;
  window.$ = window.jQuery = __webpack_require__(/*! jquery */ "./node_modules/jquery/dist/jquery.js");

  __webpack_require__(/*! bootstrap */ "./node_modules/bootstrap/dist/js/bootstrap.js");
} catch (e) {}
/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */


window.axios = __webpack_require__(/*! axios */ "./node_modules/axios/index.js");
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
/**
 * Next we will register the CSRF Token as a common header with Axios so that
 * all outgoing HTTP requests automatically have it attached. This is just
 * a simple convenience so we don't have to attach every token manually.
 */

var token = document.head.querySelector('meta[name="csrf-token"]');

if (token) {
  window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
  console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
}
/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */
// import Echo from 'laravel-echo'
// window.Pusher = require('pusher-js');
// window.Echo = new Echo({
//     broadcaster: 'pusher',
//     key: process.env.MIX_PUSHER_APP_KEY,
//     cluster: process.env.MIX_PUSHER_APP_CLUSTER,
//     encrypted: true
// });

/***/ }),

/***/ "./resources/js/components/consultentries.vue":
/*!****************************************************!*\
  !*** ./resources/js/components/consultentries.vue ***!
  \****************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _consultentries_vue_vue_type_template_id_38b5f7a2_scoped_true___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./consultentries.vue?vue&type=template&id=38b5f7a2&scoped=true& */ "./resources/js/components/consultentries.vue?vue&type=template&id=38b5f7a2&scoped=true&");
/* harmony import */ var _consultentries_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./consultentries.vue?vue&type=script&lang=js& */ "./resources/js/components/consultentries.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport *//* harmony import */ var _consultentries_vue_vue_type_style_index_0_id_38b5f7a2_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./consultentries.vue?vue&type=style&index=0&id=38b5f7a2&scoped=true&lang=css& */ "./resources/js/components/consultentries.vue?vue&type=style&index=0&id=38b5f7a2&scoped=true&lang=css&");
/* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");






/* normalize component */

var component = Object(_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_3__["default"])(
  _consultentries_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__["default"],
  _consultentries_vue_vue_type_template_id_38b5f7a2_scoped_true___WEBPACK_IMPORTED_MODULE_0__["render"],
  _consultentries_vue_vue_type_template_id_38b5f7a2_scoped_true___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"],
  false,
  null,
  "38b5f7a2",
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "resources/js/components/consultentries.vue"
/* harmony default export */ __webpack_exports__["default"] = (component.exports);

/***/ }),

/***/ "./resources/js/components/consultentries.vue?vue&type=script&lang=js&":
/*!*****************************************************************************!*\
  !*** ./resources/js/components/consultentries.vue?vue&type=script&lang=js& ***!
  \*****************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_consultentries_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/babel-loader/lib??ref--4-0!../../../node_modules/vue-loader/lib??vue-loader-options!./consultentries.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/consultentries.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport */ /* harmony default export */ __webpack_exports__["default"] = (_node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_consultentries_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__["default"]); 

/***/ }),

/***/ "./resources/js/components/consultentries.vue?vue&type=style&index=0&id=38b5f7a2&scoped=true&lang=css&":
/*!*************************************************************************************************************!*\
  !*** ./resources/js/components/consultentries.vue?vue&type=style&index=0&id=38b5f7a2&scoped=true&lang=css& ***!
  \*************************************************************************************************************/
/*! no static exports found */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_style_loader_index_js_node_modules_css_loader_index_js_ref_6_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_2_node_modules_vue_loader_lib_index_js_vue_loader_options_consultentries_vue_vue_type_style_index_0_id_38b5f7a2_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/style-loader!../../../node_modules/css-loader??ref--6-1!../../../node_modules/vue-loader/lib/loaders/stylePostLoader.js!../../../node_modules/postcss-loader/src??ref--6-2!../../../node_modules/vue-loader/lib??vue-loader-options!./consultentries.vue?vue&type=style&index=0&id=38b5f7a2&scoped=true&lang=css& */ "./node_modules/style-loader/index.js!./node_modules/css-loader/index.js?!./node_modules/vue-loader/lib/loaders/stylePostLoader.js!./node_modules/postcss-loader/src/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/consultentries.vue?vue&type=style&index=0&id=38b5f7a2&scoped=true&lang=css&");
/* harmony import */ var _node_modules_style_loader_index_js_node_modules_css_loader_index_js_ref_6_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_2_node_modules_vue_loader_lib_index_js_vue_loader_options_consultentries_vue_vue_type_style_index_0_id_38b5f7a2_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_node_modules_style_loader_index_js_node_modules_css_loader_index_js_ref_6_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_2_node_modules_vue_loader_lib_index_js_vue_loader_options_consultentries_vue_vue_type_style_index_0_id_38b5f7a2_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0__);
/* harmony reexport (unknown) */ for(var __WEBPACK_IMPORT_KEY__ in _node_modules_style_loader_index_js_node_modules_css_loader_index_js_ref_6_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_2_node_modules_vue_loader_lib_index_js_vue_loader_options_consultentries_vue_vue_type_style_index_0_id_38b5f7a2_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0__) if(__WEBPACK_IMPORT_KEY__ !== 'default') (function(key) { __webpack_require__.d(__webpack_exports__, key, function() { return _node_modules_style_loader_index_js_node_modules_css_loader_index_js_ref_6_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_2_node_modules_vue_loader_lib_index_js_vue_loader_options_consultentries_vue_vue_type_style_index_0_id_38b5f7a2_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0__[key]; }) }(__WEBPACK_IMPORT_KEY__));
 /* harmony default export */ __webpack_exports__["default"] = (_node_modules_style_loader_index_js_node_modules_css_loader_index_js_ref_6_1_node_modules_vue_loader_lib_loaders_stylePostLoader_js_node_modules_postcss_loader_src_index_js_ref_6_2_node_modules_vue_loader_lib_index_js_vue_loader_options_consultentries_vue_vue_type_style_index_0_id_38b5f7a2_scoped_true_lang_css___WEBPACK_IMPORTED_MODULE_0___default.a); 

/***/ }),

/***/ "./resources/js/components/consultentries.vue?vue&type=template&id=38b5f7a2&scoped=true&":
/*!***********************************************************************************************!*\
  !*** ./resources/js/components/consultentries.vue?vue&type=template&id=38b5f7a2&scoped=true& ***!
  \***********************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_consultentries_vue_vue_type_template_id_38b5f7a2_scoped_true___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../node_modules/vue-loader/lib??vue-loader-options!./consultentries.vue?vue&type=template&id=38b5f7a2&scoped=true& */ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/consultentries.vue?vue&type=template&id=38b5f7a2&scoped=true&");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "render", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_consultentries_vue_vue_type_template_id_38b5f7a2_scoped_true___WEBPACK_IMPORTED_MODULE_0__["render"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "staticRenderFns", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_consultentries_vue_vue_type_template_id_38b5f7a2_scoped_true___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"]; });



/***/ }),

/***/ "./resources/js/components/editproject.vue":
/*!*************************************************!*\
  !*** ./resources/js/components/editproject.vue ***!
  \*************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _editproject_vue_vue_type_template_id_2718bd58___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./editproject.vue?vue&type=template&id=2718bd58& */ "./resources/js/components/editproject.vue?vue&type=template&id=2718bd58&");
/* harmony import */ var _editproject_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./editproject.vue?vue&type=script&lang=js& */ "./resources/js/components/editproject.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport *//* harmony import */ var _node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../../../node_modules/vue-loader/lib/runtime/componentNormalizer.js */ "./node_modules/vue-loader/lib/runtime/componentNormalizer.js");





/* normalize component */

var component = Object(_node_modules_vue_loader_lib_runtime_componentNormalizer_js__WEBPACK_IMPORTED_MODULE_2__["default"])(
  _editproject_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_1__["default"],
  _editproject_vue_vue_type_template_id_2718bd58___WEBPACK_IMPORTED_MODULE_0__["render"],
  _editproject_vue_vue_type_template_id_2718bd58___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"],
  false,
  null,
  null,
  null
  
)

/* hot reload */
if (false) { var api; }
component.options.__file = "resources/js/components/editproject.vue"
/* harmony default export */ __webpack_exports__["default"] = (component.exports);

/***/ }),

/***/ "./resources/js/components/editproject.vue?vue&type=script&lang=js&":
/*!**************************************************************************!*\
  !*** ./resources/js/components/editproject.vue?vue&type=script&lang=js& ***!
  \**************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_editproject_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/babel-loader/lib??ref--4-0!../../../node_modules/vue-loader/lib??vue-loader-options!./editproject.vue?vue&type=script&lang=js& */ "./node_modules/babel-loader/lib/index.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/editproject.vue?vue&type=script&lang=js&");
/* empty/unused harmony star reexport */ /* harmony default export */ __webpack_exports__["default"] = (_node_modules_babel_loader_lib_index_js_ref_4_0_node_modules_vue_loader_lib_index_js_vue_loader_options_editproject_vue_vue_type_script_lang_js___WEBPACK_IMPORTED_MODULE_0__["default"]); 

/***/ }),

/***/ "./resources/js/components/editproject.vue?vue&type=template&id=2718bd58&":
/*!********************************************************************************!*\
  !*** ./resources/js/components/editproject.vue?vue&type=template&id=2718bd58& ***!
  \********************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_editproject_vue_vue_type_template_id_2718bd58___WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! -!../../../node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!../../../node_modules/vue-loader/lib??vue-loader-options!./editproject.vue?vue&type=template&id=2718bd58& */ "./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/vue-loader/lib/index.js?!./resources/js/components/editproject.vue?vue&type=template&id=2718bd58&");
/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "render", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_editproject_vue_vue_type_template_id_2718bd58___WEBPACK_IMPORTED_MODULE_0__["render"]; });

/* harmony reexport (safe) */ __webpack_require__.d(__webpack_exports__, "staticRenderFns", function() { return _node_modules_vue_loader_lib_loaders_templateLoader_js_vue_loader_options_node_modules_vue_loader_lib_index_js_vue_loader_options_editproject_vue_vue_type_template_id_2718bd58___WEBPACK_IMPORTED_MODULE_0__["staticRenderFns"]; });



/***/ }),

/***/ "./resources/sass/app.scss":
/*!*********************************!*\
  !*** ./resources/sass/app.scss ***!
  \*********************************/
/*! no static exports found */
/***/ (function(module, exports) {

// removed by extract-text-webpack-plugin

/***/ }),

/***/ 0:
/*!*************************************************************!*\
  !*** multi ./resources/js/app.js ./resources/sass/app.scss ***!
  \*************************************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

__webpack_require__(/*! /Users/belli/code/metag_analyze/resources/js/app.js */"./resources/js/app.js");
module.exports = __webpack_require__(/*! /Users/belli/code/metag_analyze/resources/sass/app.scss */"./resources/sass/app.scss");


/***/ })

},[[0,"/js/manifest","/js/vendor"]]]);