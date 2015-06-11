
angular.module('ruchJow.ctrls.homepage', [
    'ui.bootstrap',
    'ruchJow.homepageActions'
])
    .controller('HomepageCtrl', [
        '$scope',
        function ($scope) {

        }
    ])
    .controller('ActionsCtrl', [
        '$scope',
        '$stateParams',
        'ruchJowHomepageActions',
        function ($scope, $stateParams, ruchJowHomepageActions) {
            if ($stateParams.hasOwnProperty('action')) {
                var params = $stateParams.action.split(':'),
                    action = params.shift();

                ruchJowHomepageActions.call(action, params);
            }
        }
    ])

;