
angular.module('ruchJow.backend.ctrls.unverifiedUsers', [])

    .provider('ruchJowBackendUnverifiedUsers', [function () {

        var promise = null,
            canceller = null,
            url = Routing.generate('backend_cif_users_unverified');

        var provider = {

            $get: ['$q', '$http', function ($q, $http) {
                var service = function () {
                    if (canceller) {
                        canceller.resolve("Request cancelled");
                    }

                    canceller = $q.defer();

                    var config = {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        //method: 'POST',
                        url: url,
                        timeout: canceller.promise
                    };

                    promise = $http(config);
                    promise['finally'](function () {
                        promise = null;
                    });

                    return promise.then(function (response) {

                        if (angular.isArray(response.data)) {
                            for (var i = 0; i < response.data.length; i++) {
                                response.data[i].createdAt = response.data[i].createdAt
                                    ? new Date(response.data[i].createdAt)
                                    : null;
                            }
                        }

                        return response.data;
                    });
                };

                return service;

            }]
        };

        return provider;

    }])

    .controller('BackendUsersCtrl', [
        '$scope',
        'ruchJowBackendUnverifiedUsers',
        function ($scope, ruchJowBackendUnverifiedUsers) {

            $scope.loading = false;
            $scope.unverifiedUsers = [];

            $scope.refresh = function () {
                $scope.loading = true;

                $scope.unverifiedUsers = [];
                ruchJowBackendUnverifiedUsers().then(function (data) {
                    $scope.unverifiedUsers = data;
                })
                ['finally'](function () {
                    $scope.loading = false;
                });
            };

            $scope.refresh();
        }
    ])

;