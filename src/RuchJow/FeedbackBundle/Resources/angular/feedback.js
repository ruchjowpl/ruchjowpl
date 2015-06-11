/**
 * Created by grest on 10/21/14.
 */

angular.module('ruchJow.feedback', ['ui.bootstrap', 'ipCookie', 'ruchJow.basicServices', 'ruchJow.feedback.translation'])
    .provider('ruchJowFeedback', function () {

        var createUrl;

        var provider = {
            setCreateUrl: function (url) {
                createUrl = url;
            },
            $get: ['$http', '$q', function ($http, $q) {
                var service = {

                    sendFeedback: function (nick, title, description, contact) {

                        if (!createUrl) {
                            throw new Error('Feedback create url is not defined. Use ruchJowFeedbackProvider.setCreateUrl(\'url\') in config.')
                        }

                        // Makes nick param optional.
                        if (angular.isUndefined(description)) {
                            contact = description;
                            description = title;
                            title = nick;
                            nick = undefined;
                        }

                        var httpConfig = {
                            url: createUrl,
                            method: 'POST',
                            headers: {'X-Requested-With': 'XMLHttpRequest'},
                            data: {
                                nick: nick,
                                title: title,
                                description: description,
                                contact: contact
                            }
                        };

                        return $http(httpConfig).then(null, function () {
                            return $q.reject('feedback.sendError');
                        });
                    }

                };

                return service
            }]
        };

        return provider;
    })

    .factory('ruchJowFeedbackModal', ['$modal', 'ipCookie', function ($modal, ipCookie) {

        var data = {
            nick: ipCookie('feedbackNick'),
            title: '',
            description: '',
            contact: ''
        };

        var service = {
            modal: null,
            open: function () {
                if (service.modal) {
                    throw new Error('Trying to open a dialog that is already open!');
                }

                service.modal = $modal.open({
                    templateUrl: 'feedbackModal.html',
                    controller: ['$scope', '$modalInstance', '$alert', 'ipCookie', 'ruchJowSecurity', 'ruchJowFeedback', function ($scope, $modalInstance, $alert, ipCookie, ruchJowSecurity, ruchJowFeedback) {

                        var promise;

                        $scope.inProgress = false;
                        $scope.nickEnabled = !ruchJowSecurity.currentUser;

                        $scope.data = data;

                        //$scope.$watch('data.nick', function (newVal) {
                        //   ipCookie('feedbackNick', newVal, 30);
                        //});

                        $scope.errorMessage = null;

                        $scope.submit = function () {
                            $scope.inProgress = true;

                            if (ruchJowSecurity.currentUser) {
                                promise = ruchJowFeedback.sendFeedback($scope.data.title, $scope.data.description, $scope.data.contact);
                            } else {
                                promise = ruchJowFeedback.sendFeedback($scope.data.nick, $scope.data.title, $scope.data.description, $scope.data.contact);
                                ipCookie('feedbackNick', data.nick, { expires: 30 });
                            }

                            promise.then(function () {
                                $modalInstance.close();
                                $alert('feedback.feedbackSent.msg');
                                $scope.data.description = '';
                            }, function (msg) {
                                $scope.errorMessage = msg;
                            })['finally'](function () {
                                $scope.inProgress = false;
                            });
                        };

                    }]

                    //resolve: {
                    //    loginCallback: function () {
                    //        return loginCallback;
                    //    },
                    //    authReason: function () {
                    //        return reason;
                    //    }
                    //}
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
    }])

;