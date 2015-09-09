(function (angular) {
    'use strict';

    angular.module('ruchJow.user', ['fr.angUtils.data'])
        .config(['frDataProvider', function (frDataProvider) {
            // Register: user:getProfileData
            frDataProvider.register(
                'user:getProfileData',
                ['username', function (username) {
                    return {
                        url: Routing.generate('page_foundation_cif_user_public_data', { username: username }),
                        method: 'GET'
                    };
                }],
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
                        return $q.reject('Incorrect user data format.')
                    }

                    return data.data;
                }]
            )
        }])
        .provider('ruchJowUserProfile', [function () {

            var config = {
                profileExpireTime: 60,
                templates: {
                    showProfileTemplateUrl: null
                }
            };

            var provider = {

                setProfileExpireTime: function (time) {
                    config.profileExpireTime = time;
                },
                setShowProfileTemplateUrl: function (url) {
                    config.templates.showProfileTemplateUrl = url;
                },
                $get: ['$modal', 'frData', function ($modal, frData) {

                    var service = {
                        showProfile: function (username, size) {
                            var modalInstance = $modal.open({
                                templateUrl: config.templates.showProfileTemplateUrl,
                                controller: 'JeUserProfileModalCtrl',
                                size: size || 'lg',
                                resolve: {
                                    username: function () {
                                        return username;
                                    }
                                }
                            });

                            return modalInstance.result;
                        },
                        getProfile: function (username) {
                            return frData.getParametrized(
                                'user:getProfileData',
                                { username: username },
                                username, // hash
                                config.profileExpireTime
                            );
                        }
                    };

                    return service;
                }]
            };

            return provider;
        }])
        .controller('JeUserProfileCtrl', ['$scope', '$state', 'frData', 'ruchJowUserProfile', function ($scope, $state, frData, ruchJowUserProfile) {

            var username = $state.params.username;
            $scope.status = 'loading';
            $scope.userData = null;

            ruchJowUserProfile.getProfile(username)
                .then(function (userData) {
                    $scope.userData = userData;
                    $scope.status = 'success';
                }, function () {
                    $scope.status = 'error';
                });
        }])
        .controller('JeUserProfileModalCtrl', ['$scope', 'ruchJowUserProfile', '$modalInstance', 'username', function ($scope, ruchJowUserProfile, $modalInstance, username) {
            $scope.status = 'loading';
            $scope.userData = null;

            ruchJowUserProfile.getProfile(username)
                .then(function (userData) {
                    $scope.userData = userData;
                    $scope.status = 'success';
                }, function () {
                    $scope.status = 'error';
                });


            $scope.close = function () {
                $modalInstance.close();
            };
        }])

        .provider('ruchJowUser', [function () {

            var User = function (username, userId, roles) {
                this.username = username;
                this.displayName = username;
                this.userId = userId;
                this.roles = roles;

                this.country = null;
                this.commune = null;
                this.email = null;
                this.phone = null;
                this.displayNameFormat = null;
                this.visibility = null;
                this.address = {
                    firstName: null,
                    lastName: null,
                    street: null,
                    house: null,
                    flat: null,
                    postCode: null,
                    city: null
                };
                this.organisation = {
                    name: null,
                    url: null
                };

                this.socialLinks = {};
                this.socialLinksFull = {};


                this.about = null;

                this.setDisplayName = function (displayName) {
                    this.displayName = displayName;
                };

                this.setOrganisation = function (name, url) {
                    this.organisation = {
                        name: name,
                        url: url
                    }
                };

                this.setAddress = function (firstName, lastName, street, house, flat, postCode, city) {
                    this.address = {
                        firstName: firstName || null,
                        lastName: lastName || null,
                        street: street || null,
                        house: house || null,
                        flat: flat || null,
                        postCode: postCode || null,
                        city: city || null
                    };
                };

                this.referralUrl = null;

                this.hasRole  = function (role) {
                    return !!this.roles[role];
                };

                this.isGranted = function (roles) {

                    // Force roles to be an array.
                    if (typeof roles === 'string') {
                        roles = [ roles ];
                    }

                    for (var i = 0; i < roles.length; i++) {
                        if (!this.hasRole(roles[i])) {
                            return false;
                        }
                    }

                    return true;
                };


            };

            var socialLinksDeffinitions = [],
                socialLinksDefsMap = {};

            //noinspection UnnecessaryLocalVariableJS
            var provider = {
                addSocialLinkDefinition: function (serviceName, pathBase, pathSuffixPattern, label) {
                    if (typeof socialLinksDefsMap[serviceName] !== 'undefined') {
                        socialLinksDeffinitions[socialLinksDefsMap[serviceName]] = {
                            service: serviceName,
                            pathBase: pathBase,
                            pathSuffixPattern: new RegExp(pathSuffixPattern),
                            label: label
                        };
                    } else {
                        socialLinksDefsMap[serviceName] = socialLinksDeffinitions.push({
                                service: serviceName,
                                pathBase: pathBase,
                                pathSuffixPattern: new RegExp(pathSuffixPattern),
                                label: label
                            }) - 1;
                    }
                },
                '$get': ['$q', '$http', 'ruchJowTools', 'ruchJowSecuritySymfonyData', function ($q, $http, ruchJowTools, ruchJowSecuritySymfonyData) {

                    var currentUserRequest;

                    //noinspection UnnecessaryLocalVariableJS
                    var service = {
                        generateResetPasswordLink: function (email) {
                            var httpConfig = {
                                url: Routing.generate('user_ajax_create_reset_password_link'),
                                method: 'POST',
                                headers: {'X-Requested-With': 'XMLHttpRequest'},
                                data: {
                                    email: email
                                }
                            };

                            return $http(httpConfig)
                                .then(function (request) {
                                    return request.data;
                                }, function (/*request*/) {
                                    return $q.reject('Internal server error - please contact administrator...');
                                });
                        },
                        setNewPassword: function (token, password) {

                            var httpConfig = {
                                url: Routing.generate('user_ajax_set_new_password'),
                                method: 'POST',
                                headers: {'X-Requested-With': 'XMLHttpRequest'},
                                data: {
                                    token: token,
                                    password: password
                                }
                            };

                            return $http(httpConfig)
                                .then(function (request) {
                                    return request.data;
                                }, function (/*request*/) {
                                    return $q.reject('Internal server error - please contact administrator...');
                                });
                        },
                        checkPasswordResetToken: function (token) {
                            var httpConfig = {
                                url: Routing.generate('user_ajax_check_reset_password_token'),
                                method: 'POST',
                                headers: {'X-Requested-With': 'XMLHttpRequest'},
                                data: JSON.stringify(token)
                            };

                            return $http(httpConfig)
                                .then(function (request) {

                                    var data = request.data;

                                    if (!data.status || data.status !== 'success') {
                                        return $q.reject(data);
                                    }

                                    return data;
                                }, function (/*request*/) {
                                    return $q.reject('Internal server error - please contact administrator...');
                                });
                        },
                        register: function (data) {
                            var httpConfig = {
                                url: Routing.generate('user_ajax_support'),
                                method: 'POST',
                                headers: {'X-Requested-With': 'XMLHttpRequest'},
                                data: data
                            };

                            return $http(httpConfig)
                                .then(function (response) {
                                    return response.data;
                                }, function (/*request*/) {
                                    return $q.reject('Internal server error - please contact administrator...');
                                });
                        },
                        login: function (username, password, rememberMe) {
                            rememberMe = rememberMe || false;

                            var prepareHttpRequest = function (forceHttpRequest) {
                                return ruchJowSecuritySymfonyData.getAuthFormData(forceHttpRequest).
                                    then(function (feData) {
                                        if (
                                            typeof feData['login_form'] === 'undefined' ||
                                            typeof feData['login_form']['csrf_token'] === 'undefined' ||
                                            typeof feData['login_form']['url'] === 'undefined'
                                        ) {
                                            return $q.reject('Problem with login form data.');
                                        }

//                                    if (!forceHttpRequest) {
//                                        feData['login_form']['csrf_token'] = 'd4';
//                                    }

                                        var postData = {
                                            _username: username,
                                            _password: password,
                                            _csrf_token: feData['login_form']['csrf_token'],
                                            _submit: ''
                                        };

                                        if (rememberMe) {
                                            postData._remember_me = 'on';
                                        }

                                        return {
                                            url: feData['login_form']['url'],
                                            method: 'POST',
                                            headers: {
                                                'Accept': 'application/json, text/plain, */*',
                                                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                                                'X-Requested-With': 'XMLHttpRequest'
                                            },
                                            data: ruchJowTools.encodeParams(postData)
                                        };

                                    }, function () {
                                        return 'Problem with login form data.';
                                    });
                            };

                            // Prepare http request...
                            return prepareHttpRequest()
                                // ...and try to login.
                                .then(function (httpConfig) {
                                    return $http(httpConfig);
                                })
                                // If server returned 200 but...
                                .then(function (response) {
                                    // ... login failed because CSRF token was incorrect...
                                    if (
                                        typeof response.data.error !== 'undefined' &&
                                        response.data.error == 'Invalid CSRF token.'
                                    ) {

                                        // ... prepare request once again (but get csrf from server - force param set to true)...
                                        response = prepareHttpRequest(true).
                                            // ... and try to login with new data.
                                            then(function (httpConfig) {
                                                return $http(httpConfig);
                                            });
                                    }

                                    // Return response (either original or new login try promise which should return response).
                                    return response;
                                }).
                                // Interpret response.
                                then(function (response) {
                                    // Request success does not mean that user has logged id.
                                    if (typeof response.data.success === 'undefinded' || !response.data.success) {
                                        // Login unsuccessful
                                        return $q.reject('Login unsuccessful');
                                    }
                                    return 'Login successful';
                                }, function (/*response*/) {
                                    return 'Internal server error';
                                });
//                            // Get logged in user.
//                            .then(function (successMessage) {
//                                return service.getCurrentUser().
//                                    then(function (user) {
//                                        return user;
//                                    }, function () {
//                                        return 'Login successful but user could not be retrieved.'
//                                    });
//                            });
                        },
                        logout: function() {

                            return ruchJowSecuritySymfonyData.getAuthFormData().
                                // Validate auth form data data.
                                then(function (authData) {

                                    if (typeof authData['logout_url'] === 'undefined') {
                                        return $q.reject('Logout url not provided.');
                                    }

                                    return authData;
                                }, function () {

                                    return 'Problem with logout data - could not retrieve logout url.';
                                }).
                                then(function (feData) {

                                    // Send logout request.
                                    return $http({
                                        url: feData['logout_url'],
                                        method: 'GET',
                                        headers: {
                                            'Accept': 'application/json, text/plain, */*',
                                            'X-Requested-With': 'XMLHttpRequest'
                                        }
                                    }).
                                        then(function (response) {

                                            // Request success does not mean that user has logged out.
                                            if (typeof response.data.success === 'undefinded' || !response.data.success) {

                                                // Logout unsuccessful
                                                return $q.reject('Logout unsuccessful');
                                            }

                                            return 'Logout successful';
                                        }, function (/*response*/) {
                                            return $q.reject('Internal server error');
                                        });
                                })/*.
                             then(function () {
                             setUser(null);
                             redirect(redirectTo);
                             })*/;
                        },
                        remove: function () {

                            var httpConfig = {
                                url: Routing.generate('user_ajax_remove_account'),
                                method: 'POST',
                                headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                                }
                            };

                            return $http(httpConfig)
                                // Interpret response.
                                .then(function (response) {
                                    // Request success does not mean that user has logged id.
                                    if (typeof response.data.status === 'undefinded' || response.data.status !== 'success') {
                                        // Login unsuccessful
                                        return $q.reject('User remove failed');
                                    }

                                    return 'User remove link send';
                                }, function (/*response*/) {
                                    return $q.reject('Internal server error');
                                });
                        },
                        confirmRemove: function (token) {

                            var httpConfig = {
                                url: Routing.generate('user_ajax_remove_account_confirm'),
                                method: 'POST',
                                headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                                },
                                data: JSON.stringify(token)
                            };

                            return $http(httpConfig)
                                // Interpret response.
                                .then(function (response) {
                                    // Request success does not mean that user has logged id.
                                    if (typeof response.data.status === 'undefinded' || response.data.status !== 'success') {
                                        // Login unsuccessful
                                        return $q.reject('token_not_exists');
                                    }

                                    return 'confirmed';
                                }, function (/*response*/) {
                                    return $q.reject('internal_error');
                                });
                        },
                        getCurrentUser: function() {

                            // TODO move get user data $http call from SymfonyData and put it here (name getUserRoles is misleading)

                            if (!currentUserRequest) {
                                currentUserRequest = ruchJowSecuritySymfonyData.getUserRoles(true).
                                    then(function (userData) {
                                        var user = null;
                                        if (userData) {
                                            user = new User(userData.username, userData.user_id, userData.roles);
                                            user.setDisplayName(userData.displayName);
                                            user.referralUrl = userData.referralUrl ? userData.referralUrl : null;
                                            user.email = userData.email ? userData.email : null;
                                            user.phone = userData.phone ? userData.phone : null;
                                            user.displayNameFormat = userData.displayNameFormat;
                                            user.visibility = userData.visibility;
                                            user.country = userData.country ? userData.country : null;
                                            user.commune = userData.commune ? userData.commune : null;
                                            user.organisation = userData.organisation ? userData.organisation : null;
                                            user.about = userData.about ? userData.about : '';
                                            user.facebookName = userData.facebookName ? userData.facebookName : null;

                                            var address;
                                            if (address = userData.address) {
                                                user.setAddress(
                                                    address.firstName,
                                                    address.lastName,
                                                    address.street,
                                                    address.house,
                                                    address.flat,
                                                    address.postCode,
                                                    address.city
                                                );
                                            }

                                            user.socialLinks = userData.socialLinks ? userData.socialLinks : null;
                                            user.socialLinks = userData.socialLinksFull ? userData.socialLinksFull : null;

                                        }

                                        return user;
                                    })
                                    ['finally'](function () {
                                    currentUserRequest = undefined;
                                });
                            }

                            return currentUserRequest;
                        },
                        updateUserCountry: function (id) {
                            return service.updateUserData({
                                country: id
                            });
                        },
                        updateUserTU: function (countryCode, communeId) {
                            return service.updateUserData({
                                territorialUnit: {
                                    country: countryCode,
                                    commune: communeId
                                }
                            });
                        },
                        updateUserOrganisation: function (url, name) {
                            return service.updateUserData({
                                organisation: {
                                    url: url || undefined,
                                    name: name || undefined
                                }
                            });
                        },
                        updateUserPhone: function (phoneNo) {
                            return service.updateUserData({
                                phone: {
                                    phone: phoneNo || undefined
                                }
                            });
                        },
                        updateDisplayNameFormat: function (displayNameFormat) {
                            return service.updateUserData({
                                displayNameFormat: displayNameFormat
                            });
                        },
                        updateUserPassword: function (newPassword, currentPassword) {
                            return service.updateUserData({
                                password: {
                                    newPassword: newPassword || undefined,
                                    currentPassword: currentPassword || undefined
                                }
                            });
                        },
                        updateUserAddress: function (firstName, lastName, street, house, flat, postCode, city) {
                            return service.updateUserData({
                                address: {
                                    firstName: firstName || undefined,
                                    lastName: lastName || undefined,
                                    street: street || undefined,
                                    house: house || undefined,
                                    flat: flat || undefined,
                                    postCode: postCode || undefined,
                                    city: city || undefined
                                }
                            });
                        },
                        updateUserSocialLinks: function (links) {
                            return service.updateUserData({
                                social_links: links
                            });
                        },
                        updateUserAbout: function (about) {
                            return service.updateUserData({
                                about: about
                            });
                        },
                        updateUserVisibility: function (visibility) {
                            return service.updateUserData({
                                visibility: visibility
                            });
                        },
                        updateUserData: function (data) {
                            return $http({
                                url: Routing.generate('user_ajax_update'),
                                method: 'POST',
                                headers: {
                                    'Accept': 'application/json, text/plain, */*',
                                    'X-Requested-With': 'XMLHttpRequest'
                                },
                                data: data
                            })
                                .then(function (request) {
                                    return request.data;
                                }, function (/*request*/) {
                                    return $q.reject('Internal server error - please contact administrator...');
                                });
                        },
                        'getSocialLinkDefinitions': function () {
                            return socialLinksDeffinitions
                        },
                        'getSocialLinkDefinition': function (name) {
                            return socialLinksDefsMap[name] && socialLinksDeffinitions[socialLinksDefsMap[name]];
                        }
                    };

                    return service
                }]
            };

            return provider;
        }])


})(angular);