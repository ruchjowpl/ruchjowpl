
angular.module('ruchJow.ctrls.pointsInfo', [])
    .controller('PointsInfoCtrl', [
        '$scope',
        '$http',
        function ($scope, $http) {

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

                $http(config).then(function (request) {
                    alert('Zgłoszenie chęci organizacji wydarzenia zostało wysłane.');

                    return request.data;
                })['finally'](function () {
                    $scope.inProgress = false;
                });
            };
        }
    ])
;