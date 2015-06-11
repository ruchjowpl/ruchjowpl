
(function (angular) {

    angular.module('ruchJow.messages', ['fr.angUtils.data', 'pascalprecht.translate'])
        .config(['frDataProvider', function (frDataProvider) {

            // Register: messages:getFolders
            frDataProvider.register(
                'messages:getFolders',
                {
                    url: Routing.generate('msgs_cif_get_folders'),
                    method: 'GET'
                },
                ['$q', 'response', function ($q, response) {
                    var data = response.data;

                    if (
                        data.status === undefined
                        || data.status !== 'success'
                    ) {
                        return $q.reject(data.message);
                    }

                    if (
                        !data.hasOwnProperty('data')
                        || !angular.isObject(data.data)
                    ) {
                        return $q.reject('Incorrect folders data format.')
                    }

                    return data.data;
                }]
            );

            // Register messages:getMessages
            frDataProvider.register(
                'messages:getMessages',
                [
                    'folder', 'start', 'cnt',
                    function (folder, start, cnt) {
                        return {
                            url: Routing.generate('msgs_cif_get_messages', {
                                folder: folder,
                                start: start,
                                cnt: cnt
                            }),
                            method: 'GET'
                        }
                    }
                ],
                ['$q', 'response', function ($q, response) {
                    var data = response.data;

                    if (
                        data.status === undefined
                        || data.status !== 'success'
                    ) {
                        return $q.reject(data.message);
                    }

                    if (
                        !data.hasOwnProperty('data')
                        || !angular.isObject(data.data)
                    ) {
                        return $q.reject('Incorrect messages data format.')
                    }

                    return data.data;
                }]
            );

            // Register messages:getMessage
            frDataProvider.register(
                'messages:getMessage',
                [
                    'id',
                    function (id) {
                        return {
                            url: Routing.generate('msgs_cif_get_message', {
                                id: id
                            }),
                            method: 'GET'
                        }
                    }
                ],
                ['$q', 'response', function ($q, response) {
                    var data = response.data;

                    if (
                        data.status === undefined
                        || data.status !== 'success'
                    ) {
                        return $q.reject(data.message);
                    }

                    if (
                        !data.hasOwnProperty('data')
                        || !angular.isObject(data.data)
                    ) {
                        return $q.reject('Incorrect message data format.')
                    }

                    return data.data;
                }]
            );


        }])
        .config(['$translateProvider', function ($translateProvider) {
            $translateProvider.translations('pl', {
                msgs: {
                    folder: {
                        '#inbox': {
                            label: 'Odebrane'
                        },
                        '#sent': {
                            label: 'Wysłane'
                        }
                    },
                    replyForm: {
                        subject: {
                            label: 'Temat',
                            error: {
                                required: 'Temat nie może być pusty.',
                                maxlength: 'Temat wiadomości jest za długi.'
                            }
                        },
                        body: {
                            label: 'Treść',
                            error: {
                                required: 'Treść nie może być pusta.',
                                maxlength: 'Treść wiadomości jest za długa.'
                            }
                        }
                    },
                    sendForm: {
                        subject: {
                            label: 'Temat',
                            error: {
                                required: 'Temat nie może być pusty.',
                                maxlength: 'Temat wiadomości jest za długi.'
                            }
                        },
                        body: {
                            label: 'Treść',
                            error: {
                                required: 'Treść nie może być pusta.',
                                maxlength: 'Treść wiadomości jest za długa.'
                            }
                        }
                    },
                    replyPrefix: 'Odp: ',
                    messageSent: 'Wiadomość została wysłana'
                }
            });
        }])
        .config(['ruchJowSecurityProvider', function (ruchJowSecurityProvider) {
            ruchJowSecurityProvider.registerRestrictedAction('messages:send', 'ROLE_REGISTERED_USER');
        }])
        .provider('ruchJowMessages', [function () {

            var sendMessageUrl = Routing.generate('msgs_cif_send_message');

            var config = {
                templates: {
                    modalShowMessageUrl: null,
                    modalSendMessageUrl: null
                },
                maxSubjectSize: 255,
                maxBodySize: 1000
            };

            var provider = {

                setShowMessageTemplateUrl: function (url) {
                    config.templates.modalShowMessageUrl = url;
                },
                setSendMessageTemplateUrl: function (url) {
                    config.templates.modalSendMessageUrl = url;
                },
                setMaxSubjectSize: function (size) {
                    config.maxSubjectSize = size;
                },
                setMaxBodySize: function (size) {
                    config.maxBodySize = size;
                },
                $get: ['$modal', '$http', '$q', '$alert', function ($modal, $http, $q, $alert) {


                    var sendController = ['$scope', '$translate', 'frData', 'ruchJowMessages', '$modalInstance', 'recipient', function ($scope, $translate, frData, ruchJowMessages, $modalInstance, recipient) {

                        $scope.status = 'idle';
                        $scope.$form = null;
                        $scope.$setForm = function (form) {
                            $scope.$form = form;
                        };
                        $scope.recipient = recipient;
                        $scope.inputData = {
                            subject: '',
                            body: ''
                        };
                        $scope.maxSubjectSize = config.maxSubjectSize;
                        $scope.maxBodySize = config.maxBodySize;
                        $scope.validation= {
                            subject: {
                                $labels: {
                                    'required': 'msgs.sendForm.subject.error.required',
                                        'maxlength': 'msgs.sendForm.subject.error.maxlength'
                                }
                            },
                            body: {
                                $labels: {
                                    'required': 'msgs.sendForm.body.error.required',
                                        'maxlength': 'msgs.sendForm.body.error.maxlength'
                                }
                            }
                        };

                        $scope.$send = function () {
                            if (!$scope.$form.$valid) {
                                return;
                            }

                            $scope.status = 'sending';

                            ruchJowMessages.sendMessage($scope.recipient, $scope.inputData.subject, $scope.inputData.body)
                                .then(function () {
                                    $scope.status = 'success';
                                    $scope.close();
                                    $alert('msgs.messageSent');
                                }, function () {
                                    $scope.status = 'error';
                                });
                        };

                        $scope.close = function () {
                            $modalInstance.close();
                        };
                    }];


                    var replyController = ['$scope', '$translate', 'frData', 'ruchJowMessages', '$modalInstance', 'id', function ($scope, $translate, frData, ruchJowMessages, $modalInstance, id) {
                        $scope.status = 'loading';
                        $scope.message = null;
                        $scope.reply = {
                            status: 'idle',
                            $form: null,
                            $setForm: function (form) {

                                this.$form = form;
                            },
                            show: false,
                            relatedMessageId: null,
                            recipient: null,
                            inputData: {
                                subject: '',
                                body: ''
                            },
                            maxSubjectSize: config.maxSubjectSize,
                            maxBodySize: config.maxBodySize,
                            validation: {
                                subject: {
                                    $labels: {
                                        'required': 'msgs.replyForm.subject.error.required',
                                        'maxlength': 'msgs.replyForm.subject.error.maxlength'
                                    }
                                },
                                body: {
                                    $labels: {
                                        'required': 'msgs.replyForm.body.error.required',
                                        'maxlength': 'msgs.replyForm.body.error.maxlength'
                                    }
                                }
                            },
                            $init: function () {
                                if (!$scope.message) {
                                    return;
                                }

                                var self = this;

                                $translate('msgs.replyPrefix').then(function (prefix) {
                                    var subject = $scope.message.subject;

                                    if (subject.indexOf(prefix) !== 0) {
                                        subject = prefix + subject;
                                    }

                                    self.relatedMessageId = $scope.message.id;
                                    self.inputData.subject = subject;
                                    self.recipient = $scope.message.sender;
                                    self.show = true;
                                });

                            },
                            $send: function () {
                                if (!this.$form.$valid) {
                                    return;
                                }

                                this.status = 'sending';

                                var self = this;
                                ruchJowMessages.sendMessage(self.recipient, self.inputData.subject, self.inputData.body, self.relatedMessageId)
                                    .then(function () {
                                        self.status = 'success';
                                        $scope.close();
                                        $alert('msgs.messageSent');
                                    }, function () {
                                        self.status = 'error';
                                    });
                            }
                        };


                        frData.getParametrized('messages:getMessage', { id: id }, id, true)
                            .then(function (message) {
                                $scope.status = 'success';
                                $scope.message = message;
                            }, function () {
                                $scope.status = 'error';
                            });

                        $scope.showReply = function () {
                            $scope.reply.$init();
                        };

                        $scope.sendReply = function () {
                            $scope.reply.$send();
                        };

                        $scope.close = function () {
                            $modalInstance.close();
                        };
                    }];

                    var service = {
                        sendMessageModal: function (recipient, size) {
                            var modalInstance = $modal.open({
                                templateUrl: config.templates.modalSendMessageUrl,
                                controller: sendController,
                                size: size || 'lg',
                                resolve: {
                                    recipient: function () {
                                        return recipient;
                                    }
                                }
                            });

                            return modalInstance.result;
                        },
                        showMessage: function (id, size) {
                            var modalInstance = $modal.open({
                                templateUrl: config.templates.modalShowMessageUrl,
                                controller: replyController,
                                size: size || 'lg',
                                resolve: {
                                    id: function () {
                                        return id;
                                    }
                                }
                            });

                            return modalInstance.result;
                        },
                        sendMessage: function (recipientId, subject, body, relatedMessageId) {

                            var httpConfig = {
                                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                                url: sendMessageUrl,
                                method: 'POST',
                                data: {
                                    recipient: recipientId,
                                    subject: subject,
                                    body: body,
                                    relatedMessageId: relatedMessageId
                                }
                            };

                            return $http(httpConfig).then(function (response) {
                                var data = response.data;

                                if (
                                    data.status === undefined
                                    || data.status !== 'success'
                                ) {
                                    return $q.reject(data.message);
                                }
                            });

                        }
                    };

                    return service;
                }]

            };

            return provider;

        }])
    ;

})(angular);