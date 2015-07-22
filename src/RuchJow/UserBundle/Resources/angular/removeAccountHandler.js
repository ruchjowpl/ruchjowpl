/**
 * Created by grest on 9/10/14.
 */

angular.module('ruchJow.user.removeAccountHandler', ['ruchJow.homepageActions', 'ipCookie', 'ruchJow.user.translations'])
    .factory('ruchJowRemoveAccountHandler', ['$modal', '$http', 'ruchJowSecurity', function ($modal, $http, ruchJowSecurity) {
        return {
            handleRemoveAccountToken: function (token) {

                var modalInstance = $modal.open({
                    templateUrl: 'removeAccountHandlerModal.html',
                    controller: function ($scope, $modalInstance) {
                        $scope.cancel = function () {
                            $modalInstance.close();
                        };
                    }
                });

                return modalInstance.result;
            }
        };

    }])
    .config(['ruchJowHomepageActionsProvider',  function (homepageActionsProvider) {
        homepageActionsProvider.register('referral', 'ruchJowUserReferralHandler.handleReferralToken');
    }]);