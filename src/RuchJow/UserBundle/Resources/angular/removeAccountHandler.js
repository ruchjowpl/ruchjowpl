/**
 * Created by grest on 9/10/14.
 */

angular.module('ruchJow.user.removeAccountHandler', ['ruchJow.homepageActions', 'ipCookie', 'ruchJow.user.translations'])
    .factory('ruchJowRemoveAccountHandler', ['$modal', '$http', 'ruchJowSecurity', function ($modal, $http, ruchJowSecurity) {
        return {
            handleRemoveAccountToken: function (token) {

                var modalInstance = $modal.open({
                    templateUrl: 'removeAccountConfirmationModal.html',
                    controller: ['$scope', '$modalInstance', 'ruchJowSecurity', 'token', function ($scope, $modalInstance, ruchJowSecurity, token) {

                        $scope.status = 'pending';
                        $scope.message = 'user.remove_account.msg.pending';
                        ruchJowSecurity.confirmRemoveAccount(token)
                            .then(function () {
                                $scope.status = 'confirmed';
                                $scope.message = 'user.remove_account.msg.confirmed';
                            }, function (status) {
                                switch (status) {
                                    case 'token_not_exists':
                                        $scope.status = status;
                                        $scope.message = 'user.remove_account.msg.token_not_exists';
                                        break;
                                    default:
                                        $scope.status = 'internal_error';
                                        $scope.message = 'user.remove_account.msg.internal_error';
                                }
                            });

                        $scope.ok = function () {
                            $modalInstance.close();
                        };

                    }],
                    resolve: {
                        token: function () {
                            return token;
                        }
                    }
                });

                return modalInstance.result;
            }
        };

    }])
    .config(['ruchJowHomepageActionsProvider',  function (homepageActionsProvider) {
        homepageActionsProvider.register('remove_account', 'ruchJowRemoveAccountHandler.handleRemoveAccountToken');
    }]);