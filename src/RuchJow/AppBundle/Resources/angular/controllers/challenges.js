angular.module('ruchJow.ctrls.challenges', [
    'ui.bootstrap',
    'ruchJow.tools',
    'ui.router'
])
    .controller('ChallengesCtrl', [
        '$scope',
        '$state',
        'ruchJowAnchorScroll',
        function ($scope, $state, ruchJowAnchorScroll) {

            //$scope.
            $scope.supportForms = {
                activeName: null,
                active: null,
                setActive: function (name, scroll) {
                    if (!angular.isUndefined($scope.supportForms.map[name])) {
                        $scope.supportForms.activeName = name;
                        $scope.supportForms.active = $scope.supportForms.list[$scope.supportForms.map[name]];

                        if (
                            $scope.supportForms.active.hasOwnProperty('state') && !$state.includes($scope.supportForms.active.state)
                        ) {
                            $state.go($scope.supportForms.active.state);
                        }

                    }

                    if (scroll) {
                        ruchJowAnchorScroll('support-form-block');
                    }
                },
                list: [],
                map: {}
            };

            $scope.$on('$stateChangeSuccess', function () {
                for (var name in $scope.supportForms.map) {
                    if ($scope.supportForms.map.hasOwnProperty(name)) {
                        var supportForm = $scope.supportForms.list[$scope.supportForms.map[name]];

                        if (
                            supportForm.hasOwnProperty('state') &&
                            $state.includes(supportForm.state)
                        ) {
                            $scope.supportForms.setActive(name);

                            return;
                        }
                    }
                }

                $scope.supportForms.activeName = null;
                $scope.supportForms.active = null;

            });

            addSupportForm({
                name: 'invite_friends',
                title: 'supportForm.invite_friends.title',
                //templateUrl: ruchJowPartials('supportForm.invite_friends', 'app'),
                points: 'user.referral',
                state: 'challenges.invite_friends'
            });
            addSupportForm({
                name: 'organise_referendum_point',
                title: 'supportForm.organise_referendum_point.title',
                //templateUrl: ruchJowPartials('supportForm.organise_event', 'app'),
                points: 'organise.referendumPoint',
                state: 'challenges.organise_referendum_point'
            });
            addSupportForm({
                name: 'organise_event',
                title: 'supportForm.organise_event.title',
                //templateUrl: ruchJowPartials('supportForm.organise_event', 'app'),
                points: 'organise.event',
                state: 'challenges.organise_event'
            });
            addSupportForm({
                name: 'distribute_leaflets',
                title: 'supportForm.distribute_leaflets.title',
                //templateUrl: ruchJowPartials('supportForm.distribute_leaflets', 'app'),
                points: 'distribute.leaflets',
                state: 'challenges.distribute_leaflets'
            });
            addSupportForm({
                name: 'make_donation',
                title: 'supportForm.make_donation.title',
                //templateUrl: ruchJowPartials('supportForm.make_donation', 'app'),
                points: 'make.donation.1pln',
                state: 'challenges.make_donation'
            });

            function addSupportForm(data, active) {
                //noinspection UnnecessaryLocalVariableJS
                var index = $scope.supportForms.list.push(data) - 1;
                $scope.supportForms.map[data.name] = index;

                if (active) {
                    $scope.supportForms.setActive(data.name);
                }
            }
        }
    ])
;
