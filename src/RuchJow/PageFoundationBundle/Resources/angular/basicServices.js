angular.module('ruchJow.basicServices', ['pascalprecht.translate'])
    .provider('ruchJowConstants', function() {

        var constants = {};

        var provider = {
            set: function (name, value) {
                constants[name] = value;

                return provider;
            },
            get: function (name, defValue) {
                return constants[name] ? constants[name] : defValue;
            },
            $get: function () {
                var service = {
                    set: function (name, value) {
                        provider.set(name, value);

                        return service;
                    },
                    get: function (name, defValue) {
                        return provider.get(name, defValue);
                    }
                };

                return service;
            }
        };

        return provider;

    })
    .config(['$translateProvider', function ($translateProvider) {
        $translateProvider.translations('en', {
            $alert: {
                ok: 'OK'
            }
        });
    }])
    .provider('$alert', function () {
        return {
            $get: ['$modal', '$translate', '$q',  function ($modal, $translate, $q) {


                return function (msg, title, options) {

                    // Deafult value of  translate is true.
                    var translate = !(options && typeof options.translate !== 'undefined' && !options.translate);
                    var opts = angular.extend(
                        {
                            type: 'info',
                            btnOkCaption: translate ? '$alert.ok' : 'OK',
                            translate: translate,
                            showOkBtn: true,
                            templateUrl: '$alert.html',
                            size: 'sm'

                        },
                        options
                    );

                    if (opts.btnOkCaption) {
                        opts.btnOkCaption = trans(opts.btnOkCaption);
                    }

                    var modal = $modal.open({
                        templateUrl: opts.templateUrl,
                        size: 'lg',
                        controller: ['$scope', '$modalInstance', 'msg', 'title', 'options', function ($scope, $modalInstance, msg, title, options) {

                            $scope.type = options.type;
                            $scope.translate = options.translate;
                            $scope.showOkBtn = options.showOkBtn;

                            $q.when(msg).then(
                                function (text) { $scope.msg = text; },
                                function (text) { $scope.msg = text; }
                            );
                            $q.when(title).then(
                                function (text) { $scope.title = text; },
                                function (text) { $scope.title = text; }
                            );
                            $q.when(options.btnOkCaption).then(
                                function (text) { $scope.btnOkCaption = text; },
                                function (text) { $scope.btnOkCaption = text; }
                            );

                            $scope.close = function () {
                                $modalInstance.dismiss();
                            }
                        }],
                        resolve: {
                            msg: function () {
                                return trans(msg);
                            },
                            title: function () {
                                return trans(title);
                            },
                            options: function () {
                                return opts;
                            }
                        }
                    });

                    modal.result.close = function (result) { modal.close(result) };
                    modal.result.dismiss = modal.dismiss;

                    return modal.result;

                    function trans(txt) {
                        if (translate) {
                            if (angular.isArray(txt)) {
                                return $translate(txt[0], txt[1], txt[2]);
                            }

                            return $translate(txt);
                        }

                        return angular.isArray(txt) ? txt[0] : txt;
                    }
                };
            }]
        };
    })
    .provider('ruchJowPartials', function () {

        var routes = {},
            defaultRoute;

        var provider = {
            registerRoute: function (name, route, force) {
                if (typeof routes[name] !== 'undefined' && !force) {
                    throw new Error('Partials route name already registered.' +
                        ' If you really want to overwrite registered path yout must set force parameter to true.');
                }

                routes[name] = route;
            },
            setDefaultRoute: function (name) {
                if (typeof routes[name] === 'undefined') {
                    throw new Error('Non-existing partial route cannot be set as default.');
                }

                defaultRoute = name;
            },
            getUrl: function (templateName, partialRouteName) {
                if (typeof partialRouteName === 'undefined') {
                    if (typeof defaultRoute === 'undefined') {
                        throw new Error('Partial route name must be provided as default route has not been set.');
                    }

                    partialRouteName = defaultRoute;
                }

                if (typeof routes[partialRouteName] === 'undefined') {
                    throw new Error('Partial route ' + partialRouteName + ' is not registered.');
                }

                return Routing.generate(routes[partialRouteName], { template: templateName });
            },
            $get: function () {
                return function (template, partialRouteName) {
                    return provider.getUrl(template, partialRouteName);
                }
            }
        };

        return provider;
    })

    .provider('ruchJowDefaultTitle', function () {

        var defaultTitle;

        var provider = {
            set: function (value) {
                defaultTitle = value;

                return provider;
            },
            $get: function () {
                var service = {
                    set: function (value) {
                        provider.set(value);

                        return service;
                    },
                    get: function (defValue) {
                        return defaultTitle ? defaultTitle : defValue;
                    }
                };

                return service;
            }
        };

        return provider;

    })
    .directive('updateTitle', ['$rootScope', '$timeout', 'ruchJowDefaultTitle',
        function ($rootScope, $timeout, ruchJowDefaultTitleProvider) {

            return {
                link: function (scope, element) {

                    var listener = function (event, toState) {

                        var title = ruchJowDefaultTitleProvider.get();
                        if (toState.data && toState.data.pageTitle) {
                            title = toState.data.pageTitle;
                        }
                        $timeout(function () {
                            element.text(title);
                        }, 0, false);
                    };
                    $rootScope.$on('$stateChangeSuccess', listener);
                }
            };
        }
    ])
;