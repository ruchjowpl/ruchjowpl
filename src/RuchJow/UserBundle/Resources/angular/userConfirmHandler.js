
angular.module('ruchJow.user.confirmation', ['ruchJow.homepageActions', 'ruchJow.user.translations'])
    .factory('ruchJowUserConfirmation', ['$modal', '$http', function ($modal, $http) {

        return {
            showConfirmation: function (token) {

                var checkToken = function (scope, token) {

                    scope.status = 'pending';
                    scope.message = 'user.confirmation.msg.pending';

                    var config = {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        method: 'POST',
                        url: Routing.generate('user_ajax_support_confirm'),
                        data: JSON.stringify(token)
                    };

                    /*var promise = */
                    $http(config).then(function (request) {
                        var data = request.data;
                        scope.status = data.status;
                        switch (data.status) {
                            case 'success':
                                scope.status = 'confirmed';
                                scope.message = 'user.confirmation.msg.confirmed';
                                break;
                            case 'token_not_exists':
                                scope.status = 'token_not_exists';
                                scope.message = 'user.confirmation.msg.token_not_exists';
                                break;
                            default:
                                scope.status = 'internal_error';
                                scope.message = 'user.confirmation.msg.internal_error';
                        }
                        //scope.status = '';
                    }, function () {
                        scope.status = 'internal_error';
                        scope.message = 'user.confirmation.msg.internal_error';
                    });
                };


                var modalInstance = $modal.open({
                    size: 'lg',
                    templateUrl: 'userConfirmationModal.html',
                    controller: function ($scope, $modalInstance) {
                        $scope.ok = function () {
                            $modalInstance.close();
                        };

                        checkToken($scope, token);
                    }
                });

                return modalInstance.result;
            }
        }
    }])
    .config(['ruchJowHomepageActionsProvider',  function (homepageActionsProvider) {
        homepageActionsProvider.register('confirm_support', 'ruchJowUserConfirmation.showConfirmation');
    }]);