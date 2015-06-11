
angular.module('ruchJow.ctrls.challenges.organiseReferendumPoint', [])
    .controller('ChallengesOrganiseReferendumPointCtrl', [
        '$scope',
        '$http',
        '$alert',
        function ($scope, $http, $alert) {

            $scope.inProgress = false;

            $scope.data = {
                organiseReferendumPointInfo: ''
            };

            $scope.submit = function () {
                $scope.inProgress = true;

                var config = {
                    headers: {'X-Requested-With': 'XMLHttpRequest'},
                    method: 'POST',
                    url: Routing.generate('app_cif_organise_referendum_point'),
                    data: $scope.data
                };

                $http(config).then(function (response) {
                    $alert('supportForm.organise_referendum_point.sent.confirmation.message');

                    return response.data;
                }, function (response) {
                    var message = 'supportForm.organise_referendum_point.send.error.message';

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