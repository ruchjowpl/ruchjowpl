angular.module('ruchJow.security.passwordReset', ['ui.bootstrap.modal'])
    .controller('ForgotPasswordFormCtrl', ['$scope', '$modalInstance', '$q', '$timeout', 'generateLinkCallback', function ($scope, $modalInstance, $q, $timeout, generateLinkCallback) {

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

        $scope.data = {
            email: null
        };

        $scope.validation = {
            email: {
                pattern: /^[a-zA-Z0-9.!#$%&'*+/?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/,
                $labels: {
                    required: 'forgotPasswordForm.email.required.error',
                    pattern: 'forgotPasswordForm.email.pattern.error'
                }
            }
        };

        $scope.errorMessage = null;
        $scope.inProgress = false;

        $scope.generateLink = function () {

            $scope.inProgress = true;
            $scope.errorMessage = null;

            $q.when(generateLinkCallback($scope.data.email))
                .then(function (data) {

                    if (!data.status) {
                        $scope.errorMessage = 'forgotPasswordForm.error.internal_server_error';
                    } else {
                        switch (data.status) {
                            case 'success':
                                $modalInstance.close();
                                break;
                            case 'user_not_found':
                                $scope.errorMessage = 'forgotPasswordForm.error.' + data.status;
                                break;
                            default :
                                $scope.errorMessage = 'forgotPasswordForm.error.unknown_error';
                        }
                    }
                }, function (msg) {
                    $scope.errorMessage = 'forgotPasswordForm.error.internal_server_error';
                })
                ['finally'](function () { // ie8 treats finally as keyword so it must be accessed this way
                    $scope.inProgress = false;
                });
        };


        $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
        };
    }])
    .factory('ruchJowSecurityForgotPasswordForm', ['$modal', function ($modal) {

        var service = {
            modal: null,
            open: function (generateLinkCallback) {
                if (service.modal) {
                    throw new Error('Trying to open a dialog that is already open!');
                }

                service.modal = $modal.open({
                    templateUrl: 'forgotPasswordFormModal.html',
                    controller: 'ForgotPasswordFormCtrl',
                    resolve: {
                        generateLinkCallback: function () {
                            return generateLinkCallback;
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
    }])
    .controller('NewPasswordFormCtrl', ['$scope', '$modalInstance', '$q', '$timeout', 'setNewPasswordCallback', 'checkPasswordResetTokenCallback', 'token', function ($scope, $modalInstance, $q, $timeout, setNewPasswordCallback, checkPasswordResetTokenCallback, token) {
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

        $scope.data = {
            password: null,
            passwordRpeat: null
        };

        $scope.isTokenValid = null;
        var checkPasswordResetToken = checkPasswordResetTokenCallback(token)
            .then(function () {
                $scope.isTokenValid = true;
            }, function () {
                $scope.isTokenValid = false;
            });

        $scope.validation = {
            password: {
                pattern: /^(?=.*[A-Z])(?=.*[0-9]).{5,20}$/,
                $labels: {
                    pattern: 'registerForm.password.pattern.error',
                    required: 'newPasswordForm.password.required.error'
                }
            },
            passwordRepeat: {
                $labels: {
                    ruchJowEquals: 'newPasswordForm.passwordRepeat.ruchJowEquals.error',
                    required: 'newPasswordForm.passwordRepeat.required.error'
                }
            }
        };

        $scope.errorMessage = null;
        $scope.inProgress = false;

        $scope.setNewPassword = function () {

            $scope.inProgress = true;
            $scope.errorMessage = null;

            $q.when(setNewPasswordCallback(token, $scope.data.password))
                .then(function (data) {
                    if (!data.status) {
                        $scope.errorMessage = 'newPasswordForm.error.internal_server_error';
                    } else {
                        switch (data.status) {
                            case 'success':
                                $modalInstance.close();
                                break;
                            case 'incorrect_token':
                                $scope.errorMessage = 'newPasswordForm.error.' + data.status;
                                break;
                            default :
                                $scope.errorMessage = 'newPasswordForm.error.unknown_error';
                        }
                    }
                }, function () {
                    $scope.errorMessage = 'newPasswordForm.error.internal_server_error';
                })
                ['finally'](function () { // ie8 treats finally as keyword so it must be accessed this way
                $scope.inProgress = false;
            });
        };


        $scope.cancel = function () {
            $modalInstance.dismiss('cancel');
        };
    }])
    .factory('ruchJowSecurityNewPasswordForm', ['$modal', function ($modal) {

        var service = {
            modal: null,
            open: function (setNewPasswordCallback, checkPasswordResetTokenCallback, token) {
                if (service.modal) {
                    throw new Error('Trying to open a dialog that is already open!');
                }

                service.modal = $modal.open({
                    templateUrl: 'newPasswordFormModal.html',
                    controller: 'NewPasswordFormCtrl',
                    resolve: {
                        setNewPasswordCallback: function () {
                            return setNewPasswordCallback;
                        },
                        checkPasswordResetTokenCallback: function () {
                            return checkPasswordResetTokenCallback;
                        },
                        token: function () {
                            return token;
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
