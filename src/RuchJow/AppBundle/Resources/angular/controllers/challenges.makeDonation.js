
angular.module('ruchJow.ctrls.challenges.makeDonation', ['ui.bootstrap'])
    .controller('ChallengesMakeDonationCtrl', [
        '$scope',
        '$modal',
        'ruchJowSecurity',
        'ruchJowTransferujPl',
        'ruchJowPartials',
        function ($scope, $modal, ruchJowSecurity, ruchJowTransferujPl, ruchJowPartials) {

            $scope.predefinedAmounts = [10, 25, 100];

            $scope.data = {
                predefinedAmount: $scope.predefinedAmounts[1],
                amount: 0,
                editable: false
            };

            $scope.$watch('data.predefinedAmount', function (val) {
                if (val) {
                    $scope.data.amount = val;
                }

                $scope.data.editable = !val;
            });

            $scope.submit = function() {
                $scope.data.formError = '';

                var amount = $scope.data.predefinedAmount || $scope.data.amount;

                if (!ruchJowSecurity.currentUser) {
                    var modalInstance = $modal.open({
                        templateUrl: ruchJowPartials('challenges.makeDonation.modal', 'app'),
                        controller: 'ChallengesMakeDonationModalCtrl'/*,
                        size: 'sm'*/
                    });

                    return modalInstance.result
                        .then(function (result) {
                            return ruchJowTransferujPl
                                .makePayment(
                                amount,
                                'Wpłata na rzecz akcji Ruch JOW',
                                JSON.stringify({
                                    type: 'donation',
                                    user: ruchJowSecurity.currentUser
                                        ? ruchJowSecurity.currentUser.userId
                                        : null
                                })
                            )
                        })
                        .then(null, function(errorMsg) {
                            $scope.data.formError = errorMsg;
                        });

                }

                return ruchJowTransferujPl.makePayment(
                        amount,
                        'Wpłata na rzecz akcji Ruch JOW',
                        JSON.stringify({ type: 'donation', user: ruchJowSecurity.currentUser.userId})
                    )
                    .then(null, function(errorMsg) {
                        $scope.data.formError = errorMsg;
                    });



            };
        }
    ])
    .controller('ChallengesMakeDonationModalCtrl', [
        '$scope',
        '$modalInstance',
        'ruchJowSecurity',
        function ($scope, $modalInstance, ruchJowSecurity) {
            $scope.security = ruchJowSecurity;

            $scope.ok = function (type) {
                $modalInstance.close(type);
            };

            $scope.cancel = function () {
                $modalInstance.dismiss('cancel');
            }
        }
    ])
;