
angular.module('ruchJow.user.preSignedRegister', ['ruchJow.homepageActions', 'ruchJow.user.translations', 'ruchJow.security'])
    .factory('ruchJowUserPreSignedRegister', ['$modal', '$q', '$http', 'ruchJowSecurity', '$alert', function ($modal, $q, $http, security, $alert) {

        return {
            register: function (token) {

                var canceler = $q.defer();

                var config = {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    method: 'POST',
                    url: Routing.generate('user_ajax_get_pre_signed_data'),
                    data: JSON.stringify(token),
                    timeout: canceler.promise
                };

                // Show pending message.
                var pending = $alert('user.preSignedRegister.pending', null, {
                    showOkBtn: false,
                    type: 'pending'
                });

                var canceled = false;
                pending.then(function (result) {
                    if (result !== 'resolved') {
                        canceled = true;
                        canceler.resolve('canceled');
                    }
                }, function () {
                    canceled = true;
                    canceler.resolve();
                });

                $http(config).then(function (request) {
                    pending.close('resolved');

                    var data = request.data;
                    switch (data.status) {
                        case 'success':
                            security.register({
                                nick: data.nick,
                                email: data.email
                            });
                            break;
                        case 'token_not_exists':
                            $alert('user.preSignedRegister.token_not_found')['finally'](function () {
                                security.register();
                            });
                            break;

                        case 'email_taken':
                            $alert('user.preSignedRegister.email_taken')['finally'](function () {
                                    security.register({
                                        nick: data.nick,
                                        email: data.email
                                    });
                                });
                            break;

                        default:
                            $alert('user.preSignedRegister.internal_error')['finally'](function () {
                                security.register();
                            });
                    }
                }, function () {
                    if (!canceled) {
                        pending.close('resolved');

                        $alert('user.preSignedRegister.internal_error')['finally'](function () {
                            security.register();
                        });
                    }
                });
            }
        }
    }])
    .config(['ruchJowHomepageActionsProvider',  function (homepageActionsProvider) {
        homepageActionsProvider.register('user_register_pre_signed', 'ruchJowUserPreSignedRegister.register');
    }]);