(function () {
    "use strict";

    angular.module('ruchJow.ctrls.jowEvents', ['fr.angUtils.data', 'ruchJow.tuData'])
        .config(['frDataProvider', function (frDataProvider) {
            frDataProvider.register(
                'jowEvents:get',
                ['limit', 'offset', function (limit, offset) {
                    var urlParams = {
                        limit: limit || 0
                    };

                    if (offset) {
                        urlParams.offset = offset;
                    }

                    return {
                        url: Routing.generate('app_cif_jow_events_list', urlParams),
                        method: 'GET'
                    };
                }],
                ['$q', 'response', function ($q, response) {
                    var data = response.data;

                    if (
                        data.status === undefined
                        || data.status !== 'success'
                    ) {
                        return $q.reject(data.message);
                    }

                    if (
                        !data.hasOwnProperty('data')
                        || !angular.isObject(data.data)
                    ) {
                        return $q.reject('Incorrect response format.')
                    }

                    return data.data;
                }]
            )
        }])
        .controller('JowEventsCtrl', ['$scope', 'frData', function ($scope, frData) {

            $scope.events = [];
            $scope.filteredEvents = [];

            $scope.getEvents = function (limit, offset) {
                frData.getParametrized('jowEvents:get', { limit: limit, offset: offset}).then(function (events) {
                    $scope.events = events;
                })['finally'](function () {
                    filterUnits();
                });


            };

            $scope.filter = {
                unitLabel: '',
                unit: null
            };

            $scope.$watch('filter.unitLabel', function (unitLabel) {
               if (!unitLabel) { $scope.setTU(null) };
            });

            $scope.$watch('filter.unit', function (unit) {
                filterUnits();
            });

            var getTUPromise;
            $scope.getTU = function (input) {

                //if (getTUPromise) {
                //    frData.cancel(getTUPromise);
                //}

                getTUPromise = frData.getParametrized('tu.search:communeDistrictRegion', {search: input}, input);

                return getTUPromise.then(function (units) {
                    angular.forEach(units, function (unit) {
                        switch (unit.unitType) {
                            case 'commune':
                                unit.label = unit.name + ' (' +
                                    unit.region + ', ' +
                                    unit.district + ', ' +
                                    unit.type +
                                    ')';
                                break;

                            case 'district':
                                unit.label = unit.name + ' (' +
                                    unit.region + ')';
                                break;

                            case 'region':
                                unit.label = unit.name;

                                break;
                        }
                    });

                    return units;
                });

            };

            $scope.setTU = function (item) {
                if (!item) {
                    $scope.filter.unit = null;
                } else {
                    $scope.filter.unitLabel = item.label;
                    $scope.filter.unit = item;
                }
            };


            function filterUnits() {
                var unit = $scope.filter.unit;

                if (!unit) {
                    $scope.filteredEvents = $scope.events;

                    return;
                }

                $scope.filteredEvents = [];

                for (var i = 0; i < $scope.events.length; i++) {
                    var event = $scope.events[i];

                    var add = false;
                    switch (unit.unitType) {
                        case 'commune':
                            if (event && event.commune && event.commune.id === unit.id) {
                                add = true;
                            }
                            break;

                        case 'district':
                            if (event && event.district && event.district.id === unit.id) {
                                add = true;
                            }
                            break;

                        case 'region':
                            if (event && event.region && event.region.id === unit.id) {
                                add = true;
                            }
                            break;
                    }

                    if (add) {
                        $scope.filteredEvents.push(event);
                    }
                }
            }
        }]);
})();