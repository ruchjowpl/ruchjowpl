angular.module('ruchJow.security.removeConfirm', ['ui.bootstrap.modal'])
    .controller('RemoveConfirmCtrl', ['$scope', '$modalInstance', '$q', 'removeCallback', function ($scope, $modalInstance, $q, removeCallback) {


        $scope.inProgress = false;

        $scope.confirm = function () {

            $scope.inProgress = true;
            $scope.errorMessage = null;

            $q.when(removeCallback($scope.data))
                .then(function () {
                    $modalInstance.close();
                }, function (msg) {
                    $scope.errorMessage = msg;
                })
                ['finally'](function () {
                $scope.inProgress = false;
            });
        };

        $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
        };
    }]).
    factory('ruchJowSecurityRemoveConfirm', ['$modal', function ($modal) {

        var service = {
            modal: null,
            open: function (removeCallback) {
                if (service.modal) {
                    throw new Error('Trying to open a dialog that is already open!');
                }

                service.modal = $modal.open({
                    templateUrl: 'removeConfirmModal.html',
                    controller: 'RemoveConfirmCtrl',
                    resolve: {
                        removeCallback: function () {
                            return removeCallback;
                        }
                    }
                });

                service.modal.result['finally'](function () {
                    service.modal = null;
                });

                return service.modal.result;
            },
            dismiss: function (reason) {
                if (service.modal) {
                    service.modal.dismiss(reason);
                }
            }
        };

        return service;
    }]);