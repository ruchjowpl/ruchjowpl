
angular.module('ruchJow.ctrls.challenges.organiseEvent', [])
    .controller('ChallengesOrganiseEventCtrl', [
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
                    url: Routing.generate('app_cif_organise_event'),
                    data: $scope.data
                };

                $http(config).then(function (response) {
                    $alert('supportForm.organise_event.sent.confirmation.message');

                    return response.data;
                }, function (response) {
                    var message = 'supportForm.organise_event.send.error.message';

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