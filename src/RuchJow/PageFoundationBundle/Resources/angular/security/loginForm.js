angular.module('ruchJow.security.loginForm', ['ui.bootstrap.modal'])
    .controller('LoginFormSubmitCtrl', ['$scope', '$timeout', function ($scope, $timeout) {
        $scope.loginAsync = function () {
            $timeout(function () {
                if ($scope.ruchJowLogin.$valid && !$scope.ruchJowLogin.$pending) {
                    $scope.login();
                }
            }, 100);
        };

    }])
    .controller('LoginFormCtrl', ['$scope', '$modalInstance', '$q', '$timeout', 'ruchJowSecurity', 'loginCallback', 'authReason', function ($scope, $modalInstance, $q, $timeout, ruchJowSecurity, loginCallback, authReason) {

        $scope.ngModelOptions = function () {
            return {
                updateOn: 'default blur enterdown',
                debounce: {
                    'default': 2000,
                    'blur': 0,
                    'enterdown': 0
                }
            };
        };

        // This vars must be encapsulated in obj. because there will be created child $scope
        // with its own version of primitive vars.
        $scope.data = {
            username: null,
            password: null,
            rememberMe: false
        };

        $scope.validation = {
            login: {
                $labels: {
                    required: 'loginForm.login.required'
                }
            },
            password: {
                $labels: {
                    required: 'loginForm.password.required'
                }
            }
        };

        $scope.errorMessage = null;
        $scope.inProgress = false;

        // The reason that we are being asked to login - for instance because we tried to access something to which we are not authorized
        // We could do something diffent for each reason here but to keep it simple...
        $scope.authReason = authReason || null;

        $scope.login = function () {

            $scope.inProgress = true;
            $scope.errorMessage = null;

            $q.when(loginCallback($scope.data.username, $scope.data.password, $scope.data.rememberMe))
                .then(function () {
                    $modalInstance.close();
                }, function (msg) {
                    $scope.errorMessage = msg;
                })
                ['finally'](function () { // ie8 treats finally as keyword so it must be accessed this way
                $scope.inProgress = false;
            });
        };

        $scope.forgotPassword = function () {
            $modalInstance.close();
            ruchJowSecurity.generateResetPasswordLink();
        };

        $scope.register = function () {
            $modalInstance.close();
            ruchJowSecurity.register();
        };

        $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
        };
    }]).
    factory('ruchJowSecurityLoginForm', ['$modal', function ($modal) {

        var service = {
            modal: null,
            open: function (loginCallback, reason) {
                if (service.modal) {
                    throw new Error('Trying to open a dialog that is already open!');
                }



                service.modal = $modal.open({
                    templateUrl: 'loginFormModal.html',
                    controller: 'LoginFormCtrl',
                    resolve: {
                        loginCallback: function () {
                            return loginCallback;
                        },
                        authReason: function () {
                            return reason;
                        }
                    }
                });

                service.modal.result['finally'](function () {
                    service.modal = null;
                });

                return service.modal.result;
            },
//            close: function (success) {
//                if (service.modal) {
//                    service.modal.close(success);
//                }
//            },
            dismiss: function (reason) {
                if (service.modal) {
                    service.modal.dismiss(reason);
                }
            }
        };

        return service;
    }]);