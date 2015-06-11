
angular.module('ruchJow.tools', [])

    // TOOLS SERVICE
    .factory('ruchJowTools', [ function() {

        return {
            encodeParams: function (params) {
                if (!params) return '';

                var parts = [];
                forEachSorted(params, function(value, key) {
                    if (value === null || angular.isUndefined(value)) return;
                    if (!angular.isArray(value)) value = [value];

                    angular.forEach(value, function(v) {
                        if (angular.isObject(v)) {
                            v = toJson(v);
                        }
                        parts.push(encodeUriQuery(key) + '=' +
                        encodeUriQuery(v));
                    });
                });
                return parts.join('&');
            }
        };

        function forEachSorted (obj, iterator, context) {
            var keys = sortedKeys(obj);
            for (var i = 0; i < keys.length; i++) {
                iterator.call(context, obj[keys[i]], keys[i]);
            }
            return keys;
        }

        function sortedKeys (obj) {
            var keys = [];
            for (var key in obj) {
                if (obj.hasOwnProperty(key)) {
                    keys.push(key);
                }
            }
            return keys.sort();
        }

        /**
         * This method is intended for encoding *key* or *value* parts of query component. We need a custom
         * method because encodeURIComponent is too aggressive and encodes stuff that doesn't have to be
         * encoded per http://tools.ietf.org/html/rfc3986:
         *    query       = *( pchar / "/" / "?" )
         *    pchar         = unreserved / pct-encoded / sub-delims / ":" / "@"
         *    unreserved    = ALPHA / DIGIT / "-" / "." / "_" / "~"
         *    pct-encoded   = "%" HEXDIG HEXDIG
         *    sub-delims    = "!" / "$" / "&" / "'" / "(" / ")"
         *                     / "*" / "+" / "," / ";" / "="
         */
        function encodeUriQuery (val, pctEncodeSpaces) {
            return encodeURIComponent(val).
                replace(/%40/gi, '@').
                replace(/%3A/gi, ':').
                replace(/%24/g, '$').
                replace(/%2C/gi, ',').
                replace(/%20/g, (pctEncodeSpaces ? '%20' : '+'));
        }
    }])

    // DIRECTIVES

    // CHECK FOCUS
    .directive('ruchJowMonitorFocus', function () {
        return {
            restrict: 'A',
            require: 'ngModel',
            link: function(scope, elem, attr, ngModel) {
                ngModel.$hasFocus = (elem[0] === document.activeElement);

                elem.bind('blur', function () {
                    ngModel.$hasFocus = false; // (elem[0] === document.activeElement);
                    scope.$apply();
                });

                elem.bind('focus', function () {
                    ngModel.$hasFocus = true; // (elem[0] === document.activeElement);
                    scope.$apply();
                });
            }
        }
    })

    // SIMPLE DISQUS RELOAD
    // (works well if only one disqus element is present in the dom at the same time)
    .directive('ruchJowDisqus', ['$timeout', function ($timeout) {
        return {
            restrict: 'A',
            link: function (scope, element) {

                if (typeof DISQUS !== 'undefined') {
                    $timeout(function() {
                        DISQUS.reset({ reload: true })
                    }, 0);
                }
            }
        };
    }])

    // SIMPLE TWITTER WIDGETS LOAD
    .directive('ruchJowTwitterWidget', ['$timeout', function ($timeout) {
        return {
            restrict: 'A',
            link: function (scope, element) {

                if (typeof twttr !== 'undefined') {
                    $timeout(function() {
                        twttr.widgets.load();
                    }, 0);
                }
            }
        };
    }])


    // SIMPLE FACEBOOK WIDGETS LOAD
    .directive('ruchJowFacebookWidget', ['$timeout', function ($timeout) {
        return {
            restrict: 'A',
            terminal: true,
            link: function (scope, element) {

                if (typeof FB !== 'undefined') {
                    $timeout(function() {
                        FB.XFBML.parse();
                    }, 0);
                }
            }
        };
    }])

    // ON CHANGE
    .directive('ruchJowOnChange',function() {
        var linkFn = function(scope,element,attrs) {
            element.bind("change", function(event) {
                scope.$eval(attrs['gdOnChange']);
                scope.$apply();
            });
        };

        return {
            restrict: 'A',
            link: linkFn
        };
    })

    .directive('ruchJowSelectAll', [function () {
        return {
            restrict: 'A',
            link: function (scope, element) {
                element.on('click', function () {
                    element[0].select();
                })
            }
        }
    }])


    // FILTERS
    .provider('ruchJowAnchorScroll', function () {

        var yOffset = 0;

        //noinspection UnnecessaryLocalVariableJS
        var provider = {

            setYOffset: function (offset) {
                yOffset = offset;
            },


            $get: [ '$window', function($window) {

                var document = $window.document;

                // Helper function to get first anchor from a NodeList
                // (using `Array#some()` instead of `angular#forEach()` since it's more performant
                // and working in all supported browsers.)
                function getFirstAnchor(list) {
                    var result = null;
                    Array.prototype.some.call(list, function(element) {
                        if (angular.nodeName_(element) === 'a') {
                            result = element;
                            return true;
                        }
                    });
                    return result;
                }

                function getYOffset() {
                    var offset = scroll.yOffset || yOffset;
                    if (angular.isFunction(offset)) {
                        offset = offset();
                    } else if (angular.isElement(offset)) {
                        var elem = offset[0];
                        var style = $window.getComputedStyle(elem);
                        if (style.position !== 'fixed') {
                            offset = 0;
                        } else {
                            offset = elem.getBoundingClientRect().bottom;
                        }
                    } else if (!angular.isNumber(offset)) {
                        offset = 0;
                    }
                    return offset;
                }

                function scrollTo(elem) {
                    if (elem) {
                        elem.scrollIntoView();
                        var offset = getYOffset();
                        if (offset) {
                            // `offset` is the number of pixels we should scroll UP in order to align `elem` properly.
                            // This is true ONLY if the call to `elem.scrollIntoView()` initially aligns `elem` at the
                            // top of the viewport.
                            //
                            // IF the number of pixels from the top of `elem` to the end of the page's content is less
                            // than the height of the viewport, then `elem.scrollIntoView()` will align the `elem` some
                            // way down the page.
                            //
                            // This is often the case for elements near the bottom of the page.
                            //
                            // In such cases we do not need to scroll the whole `offset` up, just the difference between
                            // the top of the element and the offset, which is enough to align the top of `elem` at the
                            // desired position.
                            var elemTop = elem.getBoundingClientRect().top;
                            $window.scrollBy(0, elemTop - offset);
                        }
                    } else {
                        $window.scrollTo(0, 0);
                    }
                }



                function scroll(hash) {
                    var elm;

                    // empty hash, scroll to the top of the page
                    if (!hash) scrollTo(null);

                    // element with given id
                    else if ((elm = document.getElementById(hash))) scrollTo(elm);

                    // first anchor with given name :-D
                    else if ((elm = getFirstAnchor(document.getElementsByName(hash)))) scrollTo(elm);

                    // no element and hash == 'top', scroll to the top of the page
                    else if (hash === 'top') scrollTo(null);
                }

                scroll.setYOffset = function (offset) {
                    yOffset = offset;
                };

                return scroll;
            }]
        };

        return provider;
    })


    .directive('ruchJowCookieInfo', ['$animate', 'ipCookie', function ($animate, ipCookie) {
        return {
            transclude: 'element',
            priority: 600,
            terminal: true,
            restrict: 'A',
            $$tlb: true,
            link: function($scope, $element, $attr, ctrl, $transclude) {

                var clonedElem, childScope;
                var cookieName = 'cookies_policy_confirmed';

                if (!ipCookie(cookieName)) {
                    if (!childScope) {
                        $transclude(function(clone, newScope) {
                            clonedElem = clone;
                            childScope = newScope;

                            childScope.accept = function () {
                                ipCookie(cookieName, 'true', { expires: 30 });

                                if (childScope) {
                                    childScope.$destroy();
                                    childScope = null;
                                }
                                if (clonedElem) {
                                    $animate.leave(clonedElem).then(function () {
                                        clonedElem = null;
                                    });
                                }
                            };

                            $animate.enter(clone, $element.parent(), $element);
                        });
                    }
                }
            }
        }
    }])
    .directive('ruchJowForceHashHref', [function () {
        return {
            priority: 1,
            restrict: 'A',
            scope: false,
            link: function (scope, element, attrs, controller) {
                if (element.prop("tagName") === "A") {
                    var href = element.attr('href');
                    if (href && href[0] === '/') {
                        element.attr('href', '#' + href);
                    }
                }
            }
        }
    }])
;

angular.module('ruchJow.forms', [])

    // Simple form field format.
    .directive('ruchJowFormSimpleField', ['$parse', function ($parse) {
        var filter;

        try {
            angular.module('ruchJow.security.translations');
            filter = '|translate';
        } catch (err) {
            filter = '';
        }

        return {
            restrict: 'A',
            require: '^form',
            replace: false,
            transclude: true,
            template:
            '<label class="ruch-jow-form-simple-field-label" ng-if="showLabel">{{ label' + filter + ' }}</label>' +
            '<div class="ruch-jow-form-simple-field-content" ng-transclude="" ng-class="validityClass()"></div>' +
            '<ul class="ruch-jow-form-errors" ng-show="showErrors()">' +
            '<li ng-repeat="errorLabel in errorLabels" ng-show="showError(errorLabel.fieldName, errorLabel.type)" class="ruch-jow-form-error">{{ errorLabel.label' + filter + ' }}</li>' +
            '</ul>',
            scope: {
                fieldName: '@ruchJowFormSimpleField',
                label: '@',
                errors: '=',
                labelSuffix: '=',
                labelSuffixClass: '='
            },
            link: function (scope, elem, attrs, formCtr) {

                // Show label
                scope.showLabel = 1;
                if (typeof attrs['ruchJowFormShowLabel'] !== 'undefined') {
                    var ruchJowFormShowLabelExp = $parse(attrs['ruchJowFormShowLabel']);

                    scope.$parent.$watch(ruchJowFormShowLabelExp, function (show) {
                        scope.showLabel = +!!show; // Casting to bool than to int
                    });
                }

                // Show errors params
                var showPristineErrors = false;
                var showUntouchedErrors = true;

                if (attrs['showPristineErrors']) {
                    var ruchJowFormShowPristineErrExp = $parse(attrs['showPristineErrors']);
                    scope.$parent.$watch(ruchJowFormShowPristineErrExp, function (show) {
                        showPristineErrors = show;
                    });
                }

                if (attrs['showUntouchedErrors']) {
                    var ruchJowFormShowUntuchedErrExp = $parse(attrs['showUntouchedErrors']);
                    scope.$parent.$watch(ruchJowFormShowUntuchedErrExp, function (show) {
                        showUntouchedErrors = show;
                    });
                }


                // Field names.
                var fieldNames = scope.fieldName.split(' '),
                    ngModelCtrls = [];

                for (var i = 0; i < fieldNames.length; i++) {
                    if (formCtr[fieldNames[i]]) {
                        ngModelCtrls.push(formCtr[fieldNames[i]]);
                    }
                }

                // Error labels for defined fields.
                scope.errorLabels = [];
                var errorLabels = {};

                var prepareErrElement = function (fieldName, errors) {
                    if (!errorLabels[fieldName]) {
                        errorLabels[fieldName] = {};
                    }

                    for (var errorType in errors) {
                        if (errors.hasOwnProperty(errorType)) {
                            scope.errorLabels.push({
                                fieldName: fieldName,
                                type: errorType,
                                label: errors[errorType]
                            });

                            errorLabels[fieldName][errorType] = errors[errorType];
                        }
                    }
                };


                if (fieldNames.length === 1) {
                    // When there is only one field then we expect scope.errors to an "array" of labels
                    prepareErrElement(fieldNames[0], scope.errors);
                } else {
                    // When there are more than one field then we expect scope.errors to be an "array" of "arrays" of labels
                    for (var fieldName in scope.errors) {
                        if (scope.errors.hasOwnProperty(fieldName)) {
                            prepareErrElement(fieldName, scope.errors[fieldName]);
                        }
                    }
                }

                // Show error(s) functions.
                scope.forceErrorsShow = false;
                scope.showError = function (fieldName, errorType) {
                    return formCtr[fieldName].$error[errorType];
                };
                scope.showErrors = function () {

                    for (var i = 0; i < ngModelCtrls.length; i++) {
                        if (
                            errorLabels[ngModelCtrls[i].$name] &&
                            ngModelCtrls[i].$invalid &&
                            (
                                formCtr.$submitted ||
                                (showPristineErrors || ngModelCtrls[i].$dirty) &&
                                (showUntouchedErrors || ngModelCtrls[i].$touched)
                            )
                        ) {
                            var labels = errorLabels[ngModelCtrls[i].$name];

                            for (var type in labels) {
                                if (
                                    labels.hasOwnProperty(type) &&
                                    ngModelCtrls[i].$error[type]
                                ) {
                                    return true;
                                }
                            }
                        }
                    }

                    return false;
                };

                scope.validityClass = function () {
                    var pristine = true;
                    var untouched = true;
                    var valid = true;
                    var pending = false;

                    for (var i = 0; i < ngModelCtrls.length; i++) {
                        valid = valid && ngModelCtrls[i].$valid;
                        pristine = pristine && ngModelCtrls[i].$pristine;
                        untouched = untouched && ngModelCtrls[i].$untouched;
                        pending = pending || ngModelCtrls[i].$ruchJowPending;
                    }

                    var highlighted = formCtr.$submitted || (!pristine || showPristineErrors) && (!untouched || showUntouchedErrors);

                    return {
                        'ruch-jow-pristine': pristine,
                        'ruch-jow-untouched': untouched,
                        'ruch-jow-valid': valid && !pending,
                        'ruch-jow-invalid': !valid && !pending,
                        'ruch-jow-pending': pending,
                        'ruch-jow-err-highlighted': highlighted,
                        'ruch-jow-err-disabled': !highlighted
                    };
                };

                elem.addClass('ruch-jow-form-simple-field-group');

            }
        };

    }])


    // FORM SIMPLE TABLE

    // TABLE: Main DIV transform into TABLE
    .directive('ruchJowFormTable', function () {
        return {
            restrict: 'A',
            transclude: true,
            replace: true,
            template: '<table class="ruch-jow-form-table" ng-transclude></table>'
        };
    })

    // SINGLE ROW: Inner DIV into TR > TD (with COLSPAN)
    .directive('ruchJowFormSingleRow', function () {
        return {
            restrict: 'A',
            transclude: 'element',
            replace: true,
            template: '<tr><td colspan="3" class="ruch-jow-form-single-row" ng-transclude></td></tr>'
        };
    })

    // FIELD ROW: Inner DIV with field element (e.g. INPUT) into row with errors, label, field, and validity indicator
    .directive('ruchJowFormField', ['$parse', function ($parse) {
        var filter;

        try {
            angular.module('ruchJow.security.translations');
            filter = '|translate';
        } catch (err) {
            filter = '';
        }

        return {
            restrict: 'A',
            require: '^form',
            replace: true,
            transclude: true,
            template:
            '<tbody class="ruch-jow-form-field-group">' +
            '<tr ng-if="errorsOnTop"><td colspan="[[ showLabel + showIcon + 1 ]]" class="ruch-jow-form-errors-wrapper">' +
            '<ul class="ruch-jow-form-errors" ng-show="showErrors()">' +
            '<li ng-repeat="errorLabel in errorLabels" ng-show="showError(errorLabel.fieldName, errorLabel.type)" class="ruch-jow-form-error">{{ errorLabel.label' + filter + ' }}</li>' +
            '</ul>' +
            '</td></tr>' +

            '<tr class="ruch-jow-form-field-wrapper">' +
            '<td ng-if="showLabel"><label class="ruch-jow-form-field-label">{{ label' + filter + ' }}<span ng-show="labelSuffix || labelSuffixClass" ng-class="labelSuffixClass">{{ labelSuffix }}</span></label></td>' +
            '<td class="ruch-jow-form-field" ng-transclude=""></td>' +
            '<td ng-if="showIcon" class="ruch-jow-form-field-validity-icon">' +
            '<span ng-class="validityIconClass()"></span>' +
            '</td>' +
            '</tr>' +
            '<tr ng-if="!errorsOnTop"><td colspan="[[ showLabel + showIcon + 1 ]]" class="ruch-jow-form-errors-wrapper">' +
            '<ul class="ruch-jow-form-errors" ng-show="showErrors()">' +
            '<li ng-repeat="errorLabel in errorLabels" ng-show="showError(errorLabel.fieldName, errorLabel.type)" class="ruch-jow-form-error">{{ errorLabel.label' + filter + ' }}</li>' +
            '</ul>' +
            '</td></tr>' +
            '</tbody>'
            ,
            scope: {
                fieldName: '@ruchJowFormField',
                label: '@',
                errors: '=',
                labelSuffix: '=',
                labelSuffixClass: '='
            },
            link: function (scope, elem, attrs, formCtr) {

                // Show label
                scope.showLabel = 1;
                if (typeof attrs['ruchJowFormShowLabel'] !== 'undefined') {
                    var ruchJowFormShowLabelExp = $parse(attrs['ruchJowFormShowLabel']);

                    scope.$parent.$watch(ruchJowFormShowLabelExp, function (show) {
                        scope.showLabel = +!!show; // Casting to bool and to int
                    });
                }

                // Show icon
                scope.showIcon = 1;
                if (typeof attrs['ruchJowFormShowIcon'] !== 'undefined') {
                    var ruchJowFormShowIconExp = $parse(attrs['ruchJowFormShowIcon']);

                    scope.$parent.$watch(ruchJowFormShowIconExp, function (show) {
                        scope.showLabel = +!!show; // Casting to bool and to int
                    });
                }

                // Errors on top
                scope.errorsOnTop = 1;
                if (typeof attrs['ruchJowFormErrorsOnTop'] !== 'undefined') {
                    var ruchJowFormErrorsOnTopExp = $parse(attrs['ruchJowFormErrorsOnTop']);

                    scope.$parent.$watch(ruchJowFormErrorsOnTopExp, function (show) {
                        scope.errorsOnTop = +!!show; // Casting to bool and to int
                    });
                }

                // Show errors params
                var showPristineErrors = false;
                var showUntouchedErrors = true;

                if (attrs['showPristineErrors']) {
                    scope.$parent.$watch(attrs['showPristineErrors'], function (value) {
                        showPristineErrors = value;
                    });
                }

                if (attrs['showUntouchedErrors']) {
                    scope.$parent.$watch(attrs['showUntouchedErrors'], function (value) {
                        showUntouchedErrors = value;
                    });
                }


                var fieldNames = scope.fieldName.split(' '),
                    ngModelCtrls = [];

                for (var i = 0; i < fieldNames.length; i++) {
                    if (formCtr[fieldNames[i]]) {
                        ngModelCtrls.push(formCtr[fieldNames[i]]);
                    }
                }

                scope.errorLabels = [];
                var errorLabels = {};

                var prepareErrElement = function (fieldName, errors) {
                    if (!errorLabels[fieldName]) {
                        errorLabels[fieldName] = {};
                    }

                    for (var errorType in errors) {
                        if (errors.hasOwnProperty(errorType)) {
                            scope.errorLabels.push({
                                fieldName: fieldName,
                                type: errorType,
                                label: errors[errorType]
                            });

                            errorLabels[fieldName][errorType] = errors[errorType];
                        }
                    }

                };

                if (fieldNames.length === 1) {
                    // When there is only one field then we expect scope.errors to an "array" of labels
                    prepareErrElement(fieldNames[0], scope.errors);
                } else {
                    // When there are more than one field then we expect scope.errors to be an "array" of "arrays" of labels
                    for (var fieldName in scope.errors) {
                        if (scope.errors.hasOwnProperty(fieldName)) {
                            prepareErrElement(fieldName, scope.errors[fieldName]);
                        }
                    }
                }

                // Show error(s) functions.
                scope.forceErrorsShow = false;
                scope.showError = function (fieldName, errorType) {
                    return formCtr[fieldName].$error[errorType];
                };
                scope.showErrors = function () {

                    for (var i = 0; i < ngModelCtrls.length; i++) {
                        if (
                            errorLabels[ngModelCtrls[i].$name] &&
                            ngModelCtrls[i].$invalid &&
                            (
                                formCtr.$submitted ||
                                (showPristineErrors || ngModelCtrls[i].$dirty) &&
                                (showUntouchedErrors || ngModelCtrls[i].$touched)
                            )
                        ) {
                            var labels = errorLabels[ngModelCtrls[i].$name];

                            for (var type in labels) {
                                if (
                                    labels.hasOwnProperty(type) &&
                                    ngModelCtrls[i].$error[type]
                                ) {
                                    return true;
                                }
                            }
                        }
                    }

                    return false;
                };

                scope.validityIconClass = function () {
                    var pristine = true;
                    var untouched = true;
                    var valid = true;
                    var pending = false;

                    for (var i = 0; i < ngModelCtrls.length; i++) {
                        valid = valid && ngModelCtrls[i].$valid;
                        pristine = pristine && ngModelCtrls[i].$pristine;
                        untouched = untouched && ngModelCtrls[i].$untouched;
                        pending = pending || ngModelCtrls[i].$ruchJowPending;
                    }

                    var highlighted = formCtr.$submitted || (!pristine || showPristineErrors) && (!untouched || showUntouchedErrors);

                    return {
                        'ruch-jow-pristine': pristine,
                        'ruch-jow-untouched': untouched,
                        'ruch-jow-valid-icon': valid && !pending,
                        'ruch-jow-invalid-icon': !valid && !pending,
                        'ruch-jow-pending-icon': pending,
                        'ruch-jow-highlighted': highlighted,
                        'ruch-jow-disabled': !highlighted
                    };
                };

                elem.addClass('ruch-jow-form-field-group');

            }
        };
    }])


    // PARSER: NULL IF EMPTY
    .directive('ruchJowFormNullIfEmpty', [function() {
        return {
            require: 'ngModel',
            link: function(scope, elm, attr, ctrl) {
                ctrl.$parsers.unshift(function(value) {
                    return value === '' ? null : value;
                });
            }
        };
    }])

    // GENERIC VALIDATOR
    // Based on Angular UI project validate directive.
    .directive('ruchJowValidate', [function() {

        return {
            require: 'ngModel',
            scope: false,
            link: function(scope, element, attrs, ctrl) {
                var validateExpr = scope.$eval(attrs.ruchJowValidate),
                    validators = {},
                    validateFn;

                if (angular.isString(validateExpr)) {
                    validateExpr = { validator: validateExpr };
                }

                angular.forEach(validateExpr, function (expr, key) {

                    validateFn = function (valueToValidate) {

                        return !!scope.$eval(expr, { '$value' : valueToValidate });
                    };

                    validators[key] = validateFn;
                    ctrl.$validators[key] = validateFn;
                });

                function applyWatch(watch)
                {
                    //string - update all validators on expression change
                    if (angular.isString(watch))
                    {
                        setWatch(watch, validators);

                        return;
                    }

                    //array - update all validators on change of any expression
                    if (angular.isArray(watch))
                    {
                        angular.forEach(watch, function(expression){
                            setWatch(expression, validators);
                        });
                    }

                    function setWatch(expression, validators) {
                        scope.$watch(expression, function() {
                            angular.forEach(validators, function (validatorFn, key) {
                                ctrl.$setValidity(key, validatorFn(ctrl.$modelValue));
                            });
                        });
                    }
                }

                // Support for ui-validate-watch
                if (attrs.ruchJowValidateWatch){
                    applyWatch( scope.$eval(attrs.ruchJowValidateWatch) );
                }
            }
        }
    }])

    // EXTERNAL VALIDATOR
    .directive('ruchJowExternalValidation', ['$q', '$timeout', '$injector', function($q, $timeout, $injector) {

        var addPendingChgFunction = function (ctrl, formCtrl) {

            if (ctrl.hasOwnProperty('$ruchJowPending')) {
                return;
            }

            ctrl.$ruchJowPending = 0;
            ctrl.$ruchJowPendingInc = function () {
                ctrl.$ruchJowPending++;
                if (formCtrl) {
                    formCtrl.$ruchJowPendingInc();
                }

            };
            ctrl.$ruchJowPendingDec = function () {
                ctrl.$ruchJowPending--;
                if (formCtrl) {
                    formCtrl.$ruchJowPendingDec();
                }
            };

            if (formCtrl) {
                addPendingChgFunction(formCtrl);
            }

        };

        return {
            require: ['ngModel', '^?form'],
            scope: false,
            link: function(scope, element, attrs, requireCtrls) {
                var ctrl = requireCtrls[0],
                    formCtrl = requireCtrls[1];

                var services = scope.$eval(attrs.ruchJowExternalValidation);


                addPendingChgFunction(ctrl, formCtrl);

                angular.forEach(services, function (value, key) {

                    ctrl.$validators['async_' + key + '_caller'] =
                        checkFactory(key, value);
                });

                function checkFactory(name, params)
                {
                    var service = $injector.get(params[0]),
                        method = params[1],
                        cancelMethod = params[2] || (service.cancel && 'cancel') || null,
                        requiredValidators = params[3] || null,
                        checkNull = params[4] || false,
                        promise,
                        validationErrorKey = name;

                    return function (value, viewValue) {
                        if (typeof value === 'undefined') {
                            return true;
                        }

                        if (promise && cancelMethod) {
                            service[cancelMethod](promise);
                        }

                        var validate = (value !== null || checkNull);

                        if (requiredValidators) {
                            for (var i = 0; validate && i < requiredValidators.length; i++) {

                                if (!ctrl.$validators[requiredValidators[i]](value, viewValue)) {
                                    validate = false;
                                }
                            }
                        }

                        if (validate) {
                            ctrl.$ruchJowPendingInc();

                            promise = service[method](value);

                            promise.then(function () {
                                ctrl.$setValidity(validationErrorKey, true);
                                if (ctrl.$valid) {
                                    ctrl.$modelValue = value;
                                    ctrl.$$writeModelToScope();
                                }
                            }, function () {
                                ctrl.$setValidity(validationErrorKey, false);
                                if (ctrl.$invalid) {
                                    ctrl.$modelValue = undefined;
                                    ctrl.$$writeModelToScope();
                                }
                            })['finally'](function () {
                                ctrl.$ruchJowPendingDec();
                            });
                        } else {
                            ctrl.$setValidity(validationErrorKey, true);
                        }

                        return true;
                    };
                }
            }
        }
    }])

    // EQUALS VALIDATOR
    .directive('ruchJowEquals', function() {
        return {
            restrict: 'A',
            require: 'ngModel',
            scope: { compareValue: '=ruchJowEquals' },
            link: function(scope, elem, attrs, ngModel) {
                ngModel.$validators.ruchJowEquals = function(value) {
                    return value === scope.compareValue;
                };

                scope.$watch('compareValue', function(value) {
                    ngModel.$setValidity('ruchJowEquals', ngModel.$modelValue === value);
                });
            }
        }
    })

    // Enter pressed event. To be used with
    .directive('ruchJowEnterDownTrigger', function () {
        return {
            restrict: 'A',
            link: function (scope, elm, attrs) {
                elm.on('keydown', function (e) {
                    if (e.keyCode === 13 || e.which === 13) {
                        elm.triggerHandler('enterdown');
                    }
                });
            }
        };
    })

    // Enter pressed event. To be used with
    .directive('ruchJowEscapeDown', function () {
        return {
            restrict: 'A',
            link: function (scope, elm, attrs) {
                elm.on('keydown', function (e) {
                    if (e.keyCode === 27 || e.which === 27) {
                        elm.triggerHandler('escapedown');

                        if (attrs.ruchJowEscapeDown) {
                            scope.$eval(attrs.ruchJowEscapeDown);
                            scope.$apply();
                        }
                    }
                });
            }
        };
    })


    // Input icon - best for clear input functionality.
    .directive('ruchJowInputIcon', ['$compile', function($compile) {
        return {
            restrict: 'A',
            priority: 1,
            transclude: 'element',
            replace: true,
            template: '<div></div>',
            compile: function (tElement, attrs) {

                var ruchJowInputIconAttr,
                    ruchJowInputIconAttrValue,
                    wrapperClass,
                    iconClass;

                angular.forEach(attrs, function (value, key) {
                    switch (key) {
                        case 'ruchJowInputIcon':
                            ruchJowInputIconAttr = attrs.$attr[key];
                            ruchJowInputIconAttrValue = attrs.ruchJowInputIcon;
                            break;
                        case 'ruchJowWrapperClass':
                            wrapperClass = value;
                            break;
                        case 'ruchJowIconClass':
                            iconClass = value;
                            break;
                        default:
                            tElement.removeAttr(attrs.$attr[key]);
                    }

                    tElement.addClass('ruch-jow-input-icon-wrapper');
                    if (wrapperClass) {
                        tElement.addClass(wrapperClass);
                    }
                });

                return function (scope, element, attrs, modelCtrl, transcludeFn) {

                    transcludeFn(scope, function (clone, innerScope) {
                        if (ruchJowInputIconAttr) {
                            clone.removeAttr(ruchJowInputIconAttr);
                        }

                        $compile(clone)(innerScope, function(clonedElement, scope) {
                            element.append(clonedElement);
                        });

                        var iconElement = angular.element('<span></span>')
                            .addClass('ruch-jow-input-icon')
                            .attr('ng-click', ruchJowInputIconAttrValue);


                        if (iconClass) {
                            iconElement.attr('ng-class', iconClass);
                        }

                        $compile(iconElement)(innerScope, function(clonedElement, scope) {
                            element.append(clonedElement);
                        });
                    });
                }
            }
        };
    }]);

angular.module('ruchJow.tools.mouseFollower', ['ngSanitize'])
    .service('ruchJowMouseFollower', ['$document', '$sanitize', function ($document, $sanitize) {

        var element = angular.element('<span id="ruch_jow_mouse_follower" style="position: absolute; display: none;"></span>');
        $document.find('body').append(element);

        return {
            show: function (text) {
                element.html($sanitize(text));
                $document.on('mousemove', update);
                element.css({display: 'block'});
            },
            hide: function () {
                element.html('');
                $document.off('mousemove', update);
                element.css({display: 'none'});
            }
        };

        function update(e) {
            element.css({
                top: (e.pageY) + "px",
                left: (e.pageX + 15) + "px"
            })
        }
    }]);