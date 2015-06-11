
angular.module('ruchJow.backend.ctrls.users', [])

    .provider('ruchJowBackendUser', [function () {

        var promise = null,
            canceller = null,
            routeName = 'backend_cif_user_data';

        var provider = {

            $get: ['$q', '$http', function ($q, $http) {
                var service = function ($username) {
                    if (canceller) {
                        canceller.resolve("Request cancelled");
                    }

                    canceller = $q.defer();

                    var config = {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        //method: 'POST',
                        url: Routing.generate(routeName, { name: $username }),
                        timeout: canceller.promise
                    };

                    promise = $http(config);
                    promise['finally'](function () {
                        promise = null;
                    });

                    return promise.then(function (response) {
                        response.data.createdAt = response.data.createdAt
                            ? new Date(response.data.createdAt)
                            : null;
                        response.data.supportedAt = response.data.supportedAt
                            ? new Date(response.data.supportedAt)
                            : null;

                        return response.data;
                    });
                };

                return service;

            }]
        };

        return provider;

    }])

    .controller('BackendUserCtrl', [
        '$scope',
        '$stateParams',
        'ruchJowBackendUser',
        function ($scope, $stateParams, ruchJowBackendUser) {

            $scope.loading = false;
            $scope.user = null;

            $scope.username = $stateParams.username;

            $scope.refresh = function () {
                $scope.loading = true;

                $scope.user = null;
                ruchJowBackendUser($stateParams.username).then(function (data) {
                    $scope.user = data;
                })
                ['finally'](function () {
                    $scope.loading = false;
                });
            };

            $scope.refresh();
        }
    ])
    .controller('BackendUserPointsAddCtrl', [
        '$scope',
        '$http',
        '$timeout',
        '$alert',
        function ($scope, $http, $timeout, $alert) {
            $scope.isCollapsed = true;

            $scope.datePicker = {
                format: 'yyyy-MM-dd',
                options: {
                    formatYear: 'yy',
                    startingDay: 1
                },
                opened: false,
                open: function($event) {
                    $event.preventDefault();
                    $event.stopPropagation();

                    $scope.datePicker.opened = true;
                }
            };

            $scope.loading = true;
            $scope.options = null;


            var routeName = 'backend_cif_points_add_options';
            var config = {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                url: Routing.generate(routeName)
            };

            $http(config).then(function (response) {
                $scope.options = response.data;
            })
            ['finally'](function () {
                $scope.loading = false;
            });


            $scope.data = {
                type: null,
                points: null,
                date: new Date(),
                description: ''
            };

            $scope.$watch('data.type', function (newV) {
                if (newV) {
                    $scope.data.points = newV.points;
                }
            });


            $scope.submit = function () {
                if ($scope.userAddPoints.$valid) {

                    var config = {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        url: Routing.generate('backend_cif_user_points_add'),
                        data: {
                            username: $scope.user.nick,
                            type: $scope.data.type.type,
                            points: $scope.data.points,
                            date: $scope.data.date,
                            description: $scope.data.description
                        },
                        method: 'POST'
                    };

                    $scope.sending = true;
                    $http(config).then(function (response) {
                        $alert('raw.Points added');
                    }, function (response) {
                        if (
                            typeof response.data === 'object'
                            && response.data.hasOwnProperty('message')
                        ) {
                            $alert(response.data.message);
                        } else {
                            $alert('raw.Internal response error')
                        }
                    })
                    ['finally'](function () {
                        $scope.sending = false;
                    });
                }
            };

            $scope.showErrors = function (fieldName) {
              return (
                    $scope.userAddPoints.$submitted
                    || $scope.userAddPoints[fieldName].$touched
                )
                && !$scope.userAddPoints[fieldName].$valid;
            };

            $scope.showError = function (fieldName, type) {
                return $scope.userAddPoints[fieldName].$error.hasOwnProperty(type)
                    && $scope.userAddPoints[fieldName].$error[type];
            };


        }
    ])
    .controller('BackendUserDonationAddCtrl', [
        '$scope',
        '$http',
        '$timeout',
        '$alert',
        function ($scope, $http, $timeout, $alert) {
            $scope.isCollapsed = true;
            $scope.status = 'idle';

            $scope.data = {
                amount: null
            };

            $scope.submit = function () {

                if ($scope.status === 'loading' || !$scope.userAddDonation.$valid) {
                    return;
                }

                var config = {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    url: Routing.generate('backend_cif_user_donation_add'),
                    data: {
                        username: $scope.user.nick,
                        amount: parseInt($scope.data.amount)
                    },
                    method: 'POST'
                };

                $scope.status = 'loading';
                $http(config).then(function (response) {
                    $alert('raw.Donation added');
                    $scope.status = 'success';
                }, function (response) {
                    if (
                        typeof response.data === 'object'
                        && response.data.hasOwnProperty('message')
                    ) {
                        $alert(response.data.message);
                    } else {
                        $alert('raw.Internal response error')
                    }
                    $scope.status = 'error';
                });
            };

            $scope.showErrors = function (form, fieldName) {
                return (
                    form.$submitted
                    || form[fieldName].$touched
                    )
                    && !form[fieldName].$valid;
            };

            $scope.showError = function (form, fieldName, type) {
                return form[fieldName].$error.hasOwnProperty(type)
                    && form[fieldName].$error[type];
            };

        }
    ])
    .controller('FindUserCtrl', [
        '$scope',
        '$state',
        function ($scope, $state) {
            $scope.username = null;

            $scope.find = function () {
                if ($scope.username) {
                    $state.go('userUser', { username: $scope.username });
                }
            };
        }
    ])

;