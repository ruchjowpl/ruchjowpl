
angular.module('ruchJow.ctrls.challenges.distributeLeaflets', [])
    .controller('ChallengesDistributeLeafletsCtrl', [
        '$scope',
        '$http',
        '$alert',
        function ($scope, $http, $alert) {

            //$scope.inProgress = false;
            //
            //$scope.data = {
            //    distInfo: ''
            //};
            //
            //$scope.submit = function () {
            //    $scope.inProgress = true;
            //
            //    var config = {
            //        headers: {'X-Requested-With': 'XMLHttpRequest'},
            //        method: 'POST',
            //        url: Routing.generate('app_cif_distribute_leaflets'),
            //        data: $scope.data
            //    };
            //
            //    $http(config).then(function (response) {
            //        $alert('supportForm.distribute_leaflets.sent.confirmation.message');
            //
            //        return response.data;
            //    }, function (response) {
            //        var message = 'supportForm.distribute_leaflets.send.error.message';
            //
            //        if (response.data.message) {
            //            message = response.data.message;
            //        }
            //
            //        $alert(message);
            //
            //    })['finally'](function () {
            //        $scope.inProgress = false;
            //    });
            //};
        }
    ])
;