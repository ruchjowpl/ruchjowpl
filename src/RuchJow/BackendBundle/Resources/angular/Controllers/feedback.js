
angular.module('ruchJow.backend.ctrls.feedback', [])

    .provider('ruchJowBackendFeedback', [function () {

        var promise = null,
            canceller = null,
            url = Routing.generate('backend_cif_feedback');

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
                                response.data[i].date = new Date(response.data[i].date);
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

    .controller('BackendFeedbackCtrl', [
        '$scope',
        'ruchJowBackendFeedback',
        function ($scope, ruchJowBackendFeedback, $q) {

            $scope.loading = false;
            $scope.feedbackData = [];

            $scope.refresh = function () {
                $scope.loading = true;

                $scope.feedbackData = [];
                ruchJowBackendFeedback().then(function (data) {
                    $scope.feedbackData = data;
                })
                ['finally'](function () {
                    $scope.loading = false;
                });
            };

            $scope.refresh();
        }
    ])

;