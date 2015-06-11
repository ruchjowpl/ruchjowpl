angular.module('ruchJow.points', []).
    provider('ruchJowPoints', function() {

        var pointsDefs = {};

        var provider = {
            $get: ['$http', function ($http) {

                var initPromise = null;

                var service = {
                    points: pointsDefs,
                    init: function () {
                        if (!initPromise) {
                            var config = {
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest'
                                },
                                method: 'GET',
                                url: Routing.generate('page_foundation_cif_points_definitions')
                            };



                            initPromise = $http(config).then(function (request) {
                                pointsDefs = request.data;

                                return request.data;
                            });
                        }

                        return initPromise;
                    },
                    getPoints: function (name) {
                        if (angular.isUndefined(pointsDefs[name])) {
                            return null;
                        }

                        return pointsDefs[name].points;
                    },
                    getPointsDef: function (name) {
                        if (angular.isUndefined(pointsDefs[name])) {
                            return null;
                        }

                        return pointsDefs[name];
                    },
                    getPointsAsync: function (name) {
                        service.init().then(function () {
                            return service.getPoints(name);
                        });
                    }
                };

                service.init();

                return service;
            }]
        };

        return provider;
    });
