
angular.module('ruchJow.ctrls.homepage', [
    'ui.bootstrap',
    'ruchJow.homepageActions'
])
    .controller('HomepageCtrl', [
        '$scope',
        '$q',
        '$timeout',
        '$alert',
        function ($scope, $q, $timeout, $alert) {
            $scope.actions = { currentAction: null };
            $timeout(function () {
                $q.when($scope.actions.currentAction)['finally'](function () {
                    $alert('', '', {templateUrl: 'alertHomepage.html'});
                });
            }, 500);

        }
    ])
    .controller('ActionsCtrl', [
        '$scope',
        '$q',
        '$stateParams',
        'ruchJowHomepageActions',
        function ($scope, $q, $stateParams, ruchJowHomepageActions) {
            if ($stateParams.hasOwnProperty('action')) {
                var params = $stateParams.action.split(':'),
                    action = params.shift();

                $scope.actions.currentAction = $q.when(ruchJowHomepageActions.call(action, params));
            }
        }
    ])

;