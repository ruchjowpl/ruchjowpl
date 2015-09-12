// Based loosely around work by Witold Szczerba - https://github.com/witoldsz/angular-http-auth
angular.module('ruchJow.security.service', [
        'ruchJow.googleAnalytics',
        'ruchJow.security.retryQueue',    // Keeps track of failed requests that need to be retried once the user logs in
        'ruchJow.security.loginForm',     // Contains the login form template and controller
        'ruchJow.security.removeConfirm',     // Contains the remove modal template and controller
        'ruchJow.security.registerForm',     // Contains the register form template and controller
        'ruchJow.security.passwordReset',     // Contains the forgot password form template and controller
        'ruchJow.security.symfonyData',            // Used to get login, logout (like urls, csrf token) and user related data
        'ruchJow.tools'
    ])

    .provider('ruchJowSecurity', function () {

        var userServiceName;
        var restrictedActions = {};

        //noinspection UnnecessaryLocalVariableJS
        var provider = {
            setUserServiceName: function (name) {
                userServiceName = name;
            },
            registerRestrictedAction: function (name, requiredRoles) {
                if (restrictedActions.hasOwnProperty(name)) {
                    throw new Error('Restricted action "' + name + '" already registered.' );
                }

                restrictedActions[name] = requiredRoles;
            },
            '$get': [
                '$injector',
                '$q',
                '$rootScope',
                '$alert',
                'ruchJowGoogleAnalytics',
                'ruchJowSecurityRetryQueue',
                'ruchJowSecurityRegisterForm',
                'ruchJowSecurityLoginForm',
                'ruchJowSecurityRemoveConfirm',
                'ruchJowSecurityForgotPasswordForm',
                'ruchJowSecurityNewPasswordForm',
                function ($injector, $q, $rootScope, $alert, ruchJowGoogleAnalytics, retryQueue, registerForm, loginForm, removeConfirm, forgotPasswordForm, newPasswordForm) {

                    // Get user service (throw an exception if user service name has not been set).
                    if (!userServiceName) {
                        throw new Error('User service name has not been provided to ruchJowSecurity module.');
                    }
                    var userService = $injector.get(userServiceName);

                    var newPasswordModalPromise = null;
                    var resetPasswordLinkModalPromise = null;
                    var registerModalPromise = null;
                    var loginModalPromise = null;
                    var logoutModalPromise = null;
                    var removeModalPromise = null;
                    var removePromise = null;

    //                var loginCallback = function (username, password, rememberMe) {
    //                    userService.login(username, password, rememberMe)
    //                        ['finally'](function () {
    //                            return service.requestCurrentUser();
    //                        });
    //                };

                    var setUser = function (user) {
                        service.currentUser = user;

                        $rootScope.$broadcast('ruchJowUserChanged');
                    };

                    var service = {
                        setNewPassword: function (token) {
                            if (!newPasswordModalPromise) {
                                newPasswordModalPromise = newPasswordForm.open(
                                    userService.setNewPassword,
                                    userService.checkPasswordResetToken,
                                    token
                                )
                                    .then(function () {
                                        $alert('security.$alert.new_password_set');
                                    })
                                    ['finally'](function () {
                                    newPasswordModalPromise = null;
                                });
                            }

                            return newPasswordModalPromise;
                        },
                        generateResetPasswordLink: function () {
                            if (!resetPasswordLinkModalPromise) {
                                resetPasswordLinkModalPromise = forgotPasswordForm.open(userService.generateResetPasswordLink)
                                    .then(function () {
                                        $alert('security.$alert.reset_link_sent');
                                    })
                                    ['finally'](function () {
                                        resetPasswordLinkModalPromise = null;
                                    });
                            }

                            return resetPasswordLinkModalPromise;
                        },
                        register: function (initData) {
                            if (!registerModalPromise) {

                                registerModalPromise = registerForm.open(userService.register, initData)
                                    .then(function (/*msg*/) {
                                        ruchJowGoogleAnalytics('send', 'user_registered');
                                        $alert('security.$alert.user_registered_msg', 'security.$alert.user_registered_title');
                                    })
                                    ['finally'](function () {
                                        registerModalPromise = null;
                                    });
                            }

                            return registerModalPromise;
                        },
                        connectFacebook: function () {
                            service.getUserService().connectFacebook()
                                .then(function () {
                                    service.requestCurrentUser();
                                }, function () {
                                    $alert('security.$alert.connectFacebook.failed');
                                });
                        },
                        disconnectFacebook: function () {
                            service.getUserService().disconnectFacebook()
                                .then(function () {
                                    service.requestCurrentUser();
                                }, function () {
                                    $alert('security.$alert.disconnectFacebook.failed');
                                });
                        },
                        login: function (reason) {
                            if (loginModalPromise) {
                                return loginModalPromise;
                            }

    //                        loginModalPromise = loginForm.open(loginCallback, reason)
                            loginModalPromise = loginForm.open(userService.login, reason)
                                .then(function () {
                                    // We want to get new user and wait for the response, but even if it fail...
                                    return service.requestCurrentUser()
                                        ['finally'](function () {
                                            // ...we return resolved promise.
                                            return $q.when();
                                        });

                                }, function () {
                                    // We want to get user and wait for the response...
                                    return service.requestCurrentUser()
                                        ['finally'](function () {
                                            // ...and return rejected promise.
                                            return $q.reject();
                                        });
                                })
                                ['finally'](function () {
                                    loginModalPromise = null;
                                });

                            return loginModalPromise;
                        },
                        logout: function () {
                            if (logoutModalPromise) {
                                return logoutModalPromise;
                            }

                            logoutModalPromise = userService.logout()
                                .then(function () {
                                    // We want to get new user and wait for the response, but even if it fail...
                                    return service.requestCurrentUser()
                                        ['finally'](function () {
                                        // ...we return resolved promise.
                                        return $q.when();
                                    });

                                }, function () {
                                    // We want to get user and wait for the response...
                                    return service.requestCurrentUser()
                                        ['finally'](function () {
                                        // ...and return rejected promise.
                                        return $q.reject();
                                    });
                                })
                                ['finally'](function () {
                                    logoutModalPromise = null;
                                });

                            return logoutModalPromise;
                        },
                        removeAccount: function () {
                            if (removeModalPromise) {
                                return removeModalPromise;
                            }

                            removeModalPromise = removeConfirm.open(userService.remove)
                                .then(function () {
                                    return service.requestCurrentUser()
                                        ['finally'](function () {
                                        // ...we return resolved promise.
                                        return $q.when();
                                    })
                                }, function () {
                                    // We want to get user and wait for the response...
                                    return service.requestCurrentUser()
                                        ['finally'](function () {
                                        // ...and return rejected promise.
                                        return $q.reject();
                                    });
                                }).then(function() {
                                    $alert('security.$alert.account_remove_link_send');
                                })
                                ['finally'](function () {
                                    removeModalPromise = null;
                                });
                        },
                        confirmRemoveAccount: function (token) {
                            if (removePromise) {
                                return removePromise;
                            }

                            removeModalPromise = userService.confirmRemove(token)
                                .then(function () {
                                    // We want to get new user and wait for the response, but even if it fail...
                                    return service.requestCurrentUser()
                                        ['finally'](function () {
                                        // ...we return resolved promise.
                                        return $q.when();
                                    });

                                }, function () {
                                    // We want to get user and wait for the response...
                                    return service.requestCurrentUser()
                                        ['finally'](function () {
                                        // ...and return rejected promise.
                                        return $q.reject();
                                    });
                                })
                                ['finally'](function () {
                                removeModalPromise = null;
                            });

                            return removeModalPromise;
                        },
                        currentUser: undefined,
                        isAuthenticated: function(){
                            return !!service.currentUser;
                        },
                        isAuthenticatedAsync: function(force){
                            return service.requestCurrentUser(!!force)['finally'](function () {
                                if (service.isAuthenticated()) {
                                    return $q.when();
                                }

                                return $q.reject();
                            });
                        },
                        isGranted: function (roles) {
                            // If roles is undefined or an empty array or '' then return true;
                            if (!roles || !roles.length) {
                                return true;
                            }

                            return service.currentUser ? service.currentUser.isGranted(roles) : false;
                        },
                        isGrantedAsync: function (roles, force) {

                            if (!force && service.isGranted(roles)) {
                                return $q.when();
                            }

                            return service.requestCurrentUser()['finally'](function () {
                                if (service.isGranted(roles)) {
                                    return $q.when();
                                }

                                return $q.reject();
                            });
                        },
                        isGrantedAsyncReTry: function (roles, reason, force) {

                            // First check if roles are granted.
                            return service.isGrantedAsync(roles, !!force).
                                then(null, function () {

                                    // If not then we push simple action of checking if roles are granted (async way).
                                    // But earlier in this service we pushed showLogin action as a callback to be executed
                                    // always when new retry function is pushed.
                                    return retryQueue.pushRetryFn(function retryRequest() {
                                        // If roles are granted then we simply return resolved promise.
                                        if (service.isGranted(roles)) {
                                            return $q.when();
                                        }

                                        // Otherwise we return rejected promise.
                                        return $q.reject();
                                    }, reason);
                                });
                        },
                        isAllowed: function (name) {
                            if (!restrictedActions.hasOwnProperty(name)) {
                                throw new Error('Restricted action "' + name + '" not registered.')
                            }

                            return service.isGranted(restrictedActions[name]);
                        },
                        isAllowedAsync: function (name, force) {
                            if (!restrictedActions.hasOwnProperty(name)) {
                                throw new Error('Restricted action "' + name + '" not registered.')
                            }

                            return service.isGrantedAsync(restrictedActions[name], force);
                        },
                        isAllowedAsyncReTry: function (name, reason, force) {
                            if (!restrictedActions.hasOwnProperty(name)) {
                                throw new Error('Restricted action "' + name + '" not registered.')
                            }

                            return service.isGrantedAsyncReTry(restrictedActions[name], reason, force);
                        },

                        requestCurrentUser: function () {
                            return userService.getCurrentUser().then(function (user) {
                                setUser(user);
                                return service.currentUser;
                            }, function () {
                                setUser(null);
                                return $q.when(service.currentUser);
                            });
    //                        .then(function () {
    //                            return $q.when(service.currentUser);
    //                        }, function () {
    //
    //                        });
                        },
                        getUserService: function () {
                            return userService;
                        }
                    };

                    // Register a handler for when an item is added to the retry queue
                    retryQueue.setRetryCallback(function(/* retryItem */) {
                        return service.login();
                    });

                    // Get user as soon as security service is initialized.
                    service.requestCurrentUser();

                    return service;
                }
            ]
        };

        return provider;
    })

    ;
