/**
 * Created by grest on 9/10/14.
 */

angular.module('ruchJow.user.referralLinkHandler', ['ruchJow.homepageActions', 'ipCookie', 'ruchJow.user.translations'])
    .factory('ruchJowUserReferralHandler', ['$modal', '$http', 'ipCookie', function ($modal, $http, ipCookie) {
        return {
            handleReferralToken: function (token) {
                ipCookie('referralToken', token, { expires: 30 });

                $modal.open({
                    templateUrl: 'referralLinkHandlerModal.html',
                    controller: function ($scope, $modalInstance) {
                        $scope.ok = function () {
                            $modalInstance.close();
                        }
                    }
                });
            }
        };

    }])
    .config(['ruchJowHomepageActionsProvider',  function (homepageActionsProvider) {
        homepageActionsProvider.register('referral', 'ruchJowUserReferralHandler.handleReferralToken');
    }]);