angular.module('ruchJow.backend.ctrls.referendumPoints', [
    'fr.angUtils.data',
    'ruchJow.forms',
    'ruchJow.pfound.data'

])
    .config(['frDataProvider', function (frDataProvider) {
        frDataProvider.register(
            'referendumPointsList',
            {
                url: Routing.generate('backend_cif_referendum_points_list'),
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
                    return $q.reject('Incorrect referendum points data format.')
                }

                return data.data;
            }]
        );
    }])
    .controller('BackendEditReferendumPointModalCtrl', ['$scope', '$http', '$modalInstance', 'ruchJowFindCommunes', 'data', function ($scope, $http, $modalInstance, ruchJowFindCommunes, data) {

        $scope.loading = { communes: false };

        var form = null;
        $scope.setForm = function (formObj) {
            form = formObj;
        };

        $scope.data = {
            title: data.title ? data.title : '',
            subtitle: data.subtitle ? data.subtitle : '',
            description: data.description ? data.description : '',
            lat: data.lat ? data.lat : 0,
            lng: data.lng ? data.lng : 0,
            communeId: data.commune && data.commune.id ? data.commune.id : null
        };

        if (data.id) {
            $scope.data.id = data.id;
        }

        $scope.localData = {
            communeInputName: null
        };

        $scope.validation = {
            title: {
                //pattern: /^(?=.*[^ _\-]$)([a-ząćęłńóśźżA-ZĄĆĘŁŃÓŚŹŻ0-9][ _.\-]?){4,}$/,
                $labels: {
                    required: 'referendumPoints.editForm.title.required.error'
                }
            },
            subtitle: {
                //pattern: /^(?=.*[^ _\-]$)([a-ząćęłńóśźżA-ZĄĆĘŁŃÓŚŹŻ0-9][ _.\-]?){4,}$/,
                $labels: {
                    //required: 'referendumPoints.editForm.title.required.error'
                }
            },
            description: {
                //pattern: /^(?=.*[^ _\-]$)([a-ząćęłńóśźżA-ZĄĆĘŁŃÓŚŹŻ0-9][ _.\-]?){4,}$/,
                $labels: {
                    required: 'referendumPoints.editForm.description.required.error'
                }
            },
            lat: {
                //pattern: /^[0-9]?[0-9]((.|,)[0-9]{1,6})?$/,
                min: 0,
                max: 90,
                $labels: {
                    min: 'referendumPoints.editForm.lat.min.error',
                    max: 'referendumPoints.editForm.lat.max.error',
                    required: 'referendumPoints.editForm.lat.required.error'
                }
            },
            lng: {
                //pattern: /^[[0-9]?[0-9]((.|,)[0-9]{1,6})?$/,
                min: 0,
                max: 90,
                $labels: {
                    min: 'referendumPoints.editForm.lng.min.error',
                    max: 'referendumPoints.editForm.lng.max.error',
                    pattern: 'referendumPoints.editForm.lat.pattern.error',
                    required: 'referendumPoints.editForm.lat.required.error'
                }
            },
            commune: {
                $labels: {
                    commune: 'referendumPoints.editForm.commune.required.error'
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
                    'X-Requested-With': 'XMLHttpRequest',
                    url: Routing.generate('backend_cif_referendum_points_update'),
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
    .provider('referendumPointManager', [function () {
        var provider = {
            $get: ['$http', '$modal', 'ruchJowPartials', function ($http, $modal, ruchJowPartials) {
                var service = {
                    editPoint: function (title, subtitle, description, lat, lng, communeId, communeLabel, id) {
                        var instance = $modal.open({
                            templateUrl: ruchJowPartials('editReferendumPoint.modal','backend'),
                            controller: 'BackendEditReferendumPointModalCtrl',
                            size: 'lg',
                            resolve: {
                                data: function () {
                                    return {
                                        title: title,
                                        subtitle: subtitle,
                                        description: description,
                                        lat: lat,
                                        lng: lng,
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
    .controller('BackendReferendumPointsCtrl', [
        '$scope',
        '$state',
        '$http',
        'frData',
        'referendumPointManager',
        function ($scope, $state, $http, frData, referendumPointManager) {

            $scope.referendumPoints = [];
            $scope.referendumPointsMap = {};


            frData.get('referendumPointsList')
                .then(function (referendumPoints) {
                    $scope.referendumPoints = referendumPoints;
                    $scope.referendumPointsMap = {};
                    for (var i = 0; i < $scope.referendumPoints.length; i++) {
                        $scope.referendumPointsMap[$scope.referendumPoints[i].id] = i;
                    }
                });

            $scope.edit = function (id) {
                if (id !== undefined) {
                    if ($scope.referendumPointsMap.hasOwnProperty(id)) {
                        var data = $scope.referendumPoints[$scope.referendumPointsMap[id]];
                        referendumPointManager.editPoint(
                            data.title,
                            data.subtitle,
                            data.description,
                            data.lat,
                            data.lng,
                            data.commune.id,
                            data.commune.name + '(' + data.commune.region + ')',
                            data.id
                        )
                            .then(function (data) {
                                $scope.referendumPoints[$scope.referendumPointsMap[data.id]] =
                                    data;
                            });
                    } else {
                        console.log('Index not found!');
                    }
                } else {
                    referendumPointManager.editPoint()
                        .then(function (data) {
                            $scope.referendumPointsMap[data.id] = $scope.referendumPoints.push(data) - 1;
                        });
                }
            };

        }
    ]);