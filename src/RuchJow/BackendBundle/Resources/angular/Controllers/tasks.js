
angular.module('ruchJow.backend.ctrls.tasks', [])

    .provider('ruchJowBackendTasks', [function () {

        var promise = null,
            canceller = null,
            url = Routing.generate('backend_cif_tasks');

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
                                response.data[i].canceledAt = response.data[i].createdAt
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

    .controller('BackendTasksCtrl', [
        '$scope',
        'ruchJowBackendTasks',
        function ($scope, ruchJowBackendTasks) {

            $scope.loading = false;
            $scope.tasks = [];

            $scope.refresh = function () {
                $scope.loading = true;

                $scope.tasks = [];
                ruchJowBackendTasks().then(function (data) {
                    $scope.tasks = data;
                })
                ['finally'](function () {
                    $scope.loading = false;
                });
            };

            $scope.refresh();
        }
    ])

;