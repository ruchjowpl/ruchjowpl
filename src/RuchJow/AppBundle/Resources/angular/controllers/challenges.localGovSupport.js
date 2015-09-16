angular.module('ruchJow.ctrls.challenges.localGovSupport', [])
    .controller('ChallengesLocalGovSupportCtrl', [
        '$scope',
        '$http',
        '$alert',
        function ($scope, $http, $alert) {

            $scope.inProgress = false;

            $scope.data = {
                eventInfo: ''
            };

            $scope.submit = function () {
                $scope.inProgress = true;

                var config = {
                    headers: {'X-Requested-With': 'XMLHttpRequest'},
                    method: 'POST',
                    url: Routing.generate('app_cif_local_gov_support'),
                    data: $scope.data
                };

                $http(config).then(function (response) {
                    $alert('supportForm.local_gov_support.sent.confirmation.message');

                    return response.data;
                }, function (response) {
                    var message = 'supportForm.local_gov_support.send.error.message';

                    if (response.data.message) {
                        message = response.data.message;
                    }

                    $alert(message);

                })['finally'](function () {
                    $scope.inProgress = false;
                });
            };
        }
    ])
;