angular.module('ruchJow.statistics', []).
    provider('ruchJowStatistics', function() {

        var statistics = {};

        var provider = {
            $get: ['$q', '$http', '$rootScope', 'ruchJowSecurity', function ($q, $http, $rootScope, ruchJowSecurity) {

                var initPromise = null,
                    userPromise = null;

                $rootScope.$on('ruchJowUserChanged', function () {
                   service.updateUserStatistics();
                });

                var service = {
                    statistics: statistics,
                    init: function () {
                        if (!initPromise) {
                            var config = {
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest'
                                },
                                method: 'GET',
                                url: Routing.generate('statistics_ajax_basic')
                            };

                            initPromise = $http(config).then(function (request) {
                                statistics.basic = request.data;

                                return request.data;
                            });
                        }

                        return initPromise;
                    },
                    updateUserStatistics: function (force) {
                        if (ruchJowSecurity.currentUser) {

                            if (userPromise) {
                                if (force) {
                                    userPromise.cancel()
                                } else {
                                    return userPromise;
                                }
                            }

                            var canceler = $q.defer();

                            var config = {
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest'
                                },
                                method: 'GET',
                                url: Routing.generate('statistics_ajax_user'),
                                timeout: canceler.promise
                            };

                            userPromise = $http(config);
                            userPromise.cancelerPromise = canceler;
                            userPromise.cancel = function () {
                                this.cancelerPromise.resolve();
                            };

                            userPromise.then(function (request) {
                                statistics.user = request.data;

                                return request.data;
                            })['finally'](function () {
                                userPromise = null;
                            });
                        } else {
                            if (userPromise) {
                                userPromise.cancel();
                                userPromise = null;
                            }

                            delete statistics.user;
                        }

                        return userPromise;
                    },
                    get: function (name) {
                        var names = name.split('.');

                        var statisticsElement = statistics;
                        for (var i = 0; i < names.length; i++) {
                            var namePart = names[i];
                            if (angular.isUndefined(statisticsElement[namePart])) {
                                return null;
                            }
                            statisticsElement = statisticsElement[namePart];
                        }

                        return statisticsElement;
                    }
                };

                service.init();

                return service;
            }]
        };

        return provider;
    });
