angular.module('ruchJow.backend.ctrls.jowEvents', [
    'fr.angUtils.data',
    'ruchJow.forms',
    'ruchJow.pfound.data'

])
    .config(['frDataProvider', function (frDataProvider) {
        frDataProvider.register(
            'jowEventsList',
            {
                url: Routing.generate('backend_cif_jow_events_list'),
                method: 'GET'
            },
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
                    return $q.reject('Incorrect response.')
                }

                return data.data;
            }]
        );
    }])

    .controller('BackendEditJowEventModalCtrl', ['$scope', '$http', '$modalInstance', 'ruchJowFindCommunes', 'data', function ($scope, $http, $modalInstance, ruchJowFindCommunes, data) {

        $scope.loading = { communes: false };

        var form = null;
        $scope.setForm = function (formObj) {
            form = formObj;
        };

        $scope.data = {
            address: data.address || '',
            date: data.date || null,
            venue: data.venue || null,
            title: data.title || '',
            communeId: data.commune && data.commune.id ? data.commune.id : null
        };

        if (data.id) {
            $scope.data.id = data.id;
        }

        $scope.localData = {
            communeInputName: null
        };

        $scope.validation = {
            address: {
                //pattern: /^(?=.*[^ _\-]$)([a-ząćęłńóśźżA-ZĄĆĘŁŃÓŚŹŻ0-9][ _.\-]?){4,}$/,
                $labels: {
                    required: 'jowEvents.editForm.address.required.error'
                }
            },
            date: {
                //pattern: /^(?=.*[^ _\-]$)([a-ząćęłńóśźżA-ZĄĆĘŁŃÓŚŹŻ0-9][ _.\-]?){4,}$/,
                $labels: {
                    required: 'jowEvents.editForm.date.required.error'
                }
            },
            venue: {
                //pattern: /^(?=.*[^ _\-]$)([a-ząćęłńóśźżA-ZĄĆĘŁŃÓŚŹŻ0-9][ _.\-]?){4,}$/,
                $labels: {
                    required: 'jowEvents.editForm.venue.required.error'
                }
            },
            title: {
                //pattern: /^(?=.*[^ _\-]$)([a-ząćęłńóśźżA-ZĄĆĘŁŃÓŚŹŻ0-9][ _.\-]?){4,}$/,
                $labels: {
                    required: 'jowEvents.editForm.title.required.error'
                }
            },
            commune: {
                $labels: {
                    commune: 'jowEvents.editForm.commune.required.error'
                }
            }
        };

        var getCommunesPromise;
        $scope.getCommunes = function (input) {

            if (getCommunesPromise) {
                ruchJowFindCommunes.cancel(getCommunesPromise);
            }

            getCommunesPromise = ruchJowFindCommunes.getCommunes(input);

            return getCommunesPromise.then(function (communes) {
                angular.forEach(communes, function (commune) {
                    commune.label = commune.name + ' (' +
                        commune.region + ', ' +
                        commune.district + ', ' +
                        commune.type +
                        ')';
                });

                return communes;
            });

        };
        $scope.setCommune = function (item) {
            if (!item) {
                $scope.data.communeId = null;
                $scope.localData.selectedCommuneLabel = null;
            } else {
                $scope.data.communeId = item.id;
                $scope.localData.selectedCommuneLabel = item.label;
            }
        };

        if (data.commune) {
            $scope.setCommune(data.commune);
        }

        $scope.status = 'idle';
        $scope.save = function () {
            if (form.$valid && $scope.status !== 'inProgress') {
                var httpConfig = {
                    headers: {'X-Requested-With': 'XMLHttpRequest'},
                    url: Routing.generate('backend_cif_jow_events_update'),
                    method: 'POST',
                    data: $scope.data
                };

                $scope.status = 'inProgress';
                $http(httpConfig)
                    .then(function (request) {
                        if (!request.data.hasOwnProperty('status') || request.data.status !== 'success') {
                            $scope.status = 'error';

                            return;
                        }

                        $scope.status = 'success';

                        $modalInstance.close(request.data.data);
                    }, function () {
                        $scope.status = 'error';
                    })
            }

        };

        $scope.cancel = function () {
            $modalInstance.dismiss(false);
        };
    }])

    .provider('jowEventsManager', [function () {
        var provider = {
            $get: ['$http', '$modal', 'ruchJowPartials', function ($http, $modal, ruchJowPartials) {
                var service = {
                    editEvent: function (address, date, venue, title, communeId, communeLabel, id) {
                        var instance = $modal.open({
                            templateUrl: ruchJowPartials('editJowEvent.modal','backend'),
                            controller: 'BackendEditJowEventModalCtrl',
                            size: 'lg',
                            resolve: {
                                data: function () {
                                    return {
                                        address: address,
                                        date: date,
                                        venue: venue,
                                        title: title,
                                        commune: communeId && communeLabel ? {
                                            id: communeId,
                                            label: communeLabel
                                        } : null,
                                        id: id
                                    }
                                }
                            }
                        });

                        return instance.result;
                    }
                };

                return service;
            }]
        };

        return provider;
    }])

    .controller('BackendJowEventsCtrl', [
        '$scope',
        '$state',
        '$http',
        'frData',
        'jowEventsManager',
        function ($scope, $state, $http, frData, jowEventsManager) {

            $scope.jowEvents = [];
            $scope.jowEventsMap = {};


            frData.get('jowEventsList', true)
                .then(function (jowEvents) {
                    $scope.jowEvents = jowEvents;
                    $scope.jowEventsMap = {};
                    for (var i = 0; i < $scope.jowEvents.length; i++) {
                        $scope.jowEventsMap[$scope.jowEvents[i].id] = i;
                        $scope.jowEvents[i].date = new Date($scope.jowEvents[i].date);
                    }
                });

            $scope.edit = function (id) {
                if (id !== undefined) {
                    if ($scope.jowEventsMap.hasOwnProperty(id)) {
                        var data = $scope.jowEvents[$scope.jowEventsMap[id]];
                        jowEventsManager.editEvent(
                            data.address,
                            data.date,
                            data.venue,
                            data.title,
                            data.commune.id,
                            data.commune.name + '(' + data.commune.region + ')',
                            data.id
                        )
                            .then(function (data) {
                                $scope.jowEvents[$scope.jowEventsMap[data.id]] =
                                    data;
                            });
                    } else {
                        console.log('Index not found!');
                    }
                } else {
                    jowEventsManager.editPoint()
                        .then(function (data) {
                            $scope.jowEventsMap[data.id] = $scope.jowEvents.push(data) - 1;
                        });
                }
            };

        }
    ]);