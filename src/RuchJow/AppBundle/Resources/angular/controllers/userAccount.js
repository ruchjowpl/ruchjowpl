
angular.module('ruchJow.ctrls.userAccount', [
    'ui.bootstrap',
    'ruchJow.pfound.data',
    'ruchJow.points',
    'ruchJow.tools',
    'ui.router'
])
    .controller('UserAccountCtrl', [
        '$scope',
        '$state',
        function ($scope, $state) {

            $scope.tabs = {
                yourData: {
                    active: false,
                    disabled: false,
                    state: 'user.data'
                },
                history: {
                    active: false,
                    disabled: false,
                    state: 'user.history'
                },
                messages: {
                    active: false,
                    disabled: false,
                    state: 'user.messages'
                }
            };

            $scope.$on('$stateChangeSuccess', function () {
                for (var name in $scope.tabs) {
                    if (
                        $scope.tabs.hasOwnProperty(name) &&
                        $scope.tabs[name].hasOwnProperty('state') &&
                        $state.includes($scope.tabs[name].state)
                    ) {
                        $scope.tabs[name].active = true;
                    }
                }
            });

            // WATCHES
            for (var name in $scope.tabs) {
                if (
                    $scope.tabs.hasOwnProperty(name) &&
                    $scope.tabs[name].hasOwnProperty('state')
                ) {

                    (function (name) {
                        var str = 'tabs.' + name + '.active';

                        $scope.$watch(str, function (newV) {
                            if (
                                newV &&
                                !$state.includes($scope.tabs[name].state)
                            ) {
                                $state.go($scope.tabs[name].state)
                            }
                        });
                    }) (name);
                }
            }
        }
    ])
    .controller('UserHistoryCtrl', [
        '$scope',
        '$q',
        '$http',
        function ($scope, $q, $http) {

            var url = Routing.generate('page_foundation_cif_user_points_history');
            var request = null;

            $scope.history = [];

            refreshData();

            function refreshData() {
                if (request) {
                    request.canceller.resolve('Request canceled.')
                }

                $scope.loading = true;


                var canceller = $q.defer();
                var config = {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    method: 'GET',
                    url: url,
                    timeout: canceller.promise
                };

                request = $http(config);
                request.canceller = canceller;

                request.then(function (request) {

                    if (!angular.isArray(request.data)) {
                        $scope.history = []
                    } else {

                        $scope.history = [];
                        for (var i = 0; i < request.data.length; i++) {
                            var row = request.data[i];

                            $scope.history.push({
                                date: row.date ? new Date(row.date) : null,
                                type: checkType(row.type),
                                points: row.points,
                                details: checkDetails(row.details)
                            });
                        }
                    }
                })
                ['finally'](function () {
                    $scope.loading = false;
                    request = null;
                });
            }

            function checkType (type) {
                var allowedTypes = [
                    'user.support',
                    'user.referral',
                    'organise.event',
                    'distribute.leaflets',
                    'donation'
                ];

                return allowedTypes.indexOf(type) !== -1 ? type : 'other';
            }

            function checkDetails (details) {

                if (details == '%unknown%') {
                    return 'nieznany użytkownik';
                }

                return details;
            }


        }
    ])
    .controller('UserDataCtrl', [
        '$scope',
        'ruchJowSecurity',
        'ruchJowFindOrganisations',
        'ruchJowFindCountries',
        'ruchJowFindCommunes',
        function ($scope, ruchJowSecurity, ruchJowFindOrganisations, ruchJowFindCountries, ruchJowFindCommunes) {

            var getOrgsPromise, getCountriesPromise, getCommunesPromise;

            // Country
            $scope.country = {
                edit: false,
                loading: false,
                saveInProgress: false,

                validation: {
                    $labels: {
                        country: 'registerForm.country.country.error',
                        required: 'registerForm.country.required.error'
                    }
                },
                inputName: null,
                selectedCommuneLabel: null,
                data: {
                    commune: null
                },

                get: function (input) {

                    if (getCountriesPromise) {
                        ruchJowFindCountries.cancel(getCountriesPromise);
                    }

                    getCountriesPromise = ruchJowFindCountries.getCountries(input);

                    return getCountriesPromise.then(function (countries) {
                        angular.forEach(countries, function (country) {
                            country.label = country.name;
                        });

                        return countries;
                    });

                },
                set: function (item) {
                    if (!item) {
                        $scope.country.data.country = null;
                        $scope.country.selectedCountryLabel = null;
                    } else {
                        $scope.country.data.country = item.id;
                        $scope.country.selectedCountryLabel = item.label;
                    }
                },
                initEdit: function () {
                    var country = ruchJowSecurity.currentUser.country

                    if (country) {
                        $scope.country.selectedCountryLabel = country.name;
                        $scope.country.data.country = country.id;
                    } else {
                        $scope.country.selectedCountryLabel = null;
                        $scope.country.data.country = null;
                    }

                    $scope.country.edit = true;
                },

                save: function () {
                    $scope.country.saveInProgress = true;
                    ruchJowSecurity.getUserService().updateUserCountry($scope.country.data.country)
                        .then(function () {
                            return ruchJowSecurity.requestCurrentUser()
                        })
                        .then(function () {
                            $scope.country.edit = false;
                        })['finally'](function () {
                        $scope.country.saveInProgress = false;
                    });
                }
            };

            // Commune
            $scope.commune = {
                edit: false,
                loading: false,
                saveInProgress: false,

                validation: {
                    $labels: {
                        commune: 'registerForm.commune.commune.error',
                        required: 'registerForm.commune.required.error'
                    }
                },
                inputName: null,
                selectedCommuneLabel: null,
                data: {
                    commune: null
                },

                get: function (input) {

                    if (getCommunesPromise) {
                        ruchJowFindCommunes.cancel(getCommunesPromise);
                    }

                    getCommunesPromise = ruchJowFindCommunes.getCommunes(input);

                    return getCommunesPromise.then(function (communes) {
                        angular.forEach(communes, function (commune) {
                            commune.label = commune.name + ' (' +
                                commune.region + ', ' +
                                commune.district + ', ' +
                                commune.type +
                                ')';
                        });

                        return communes;
                    });

                },
                set: function (item) {
                    if (!item) {
                        $scope.commune.data.commune = null;
                        $scope.commune.selectedCommuneLabel = null;
                    } else {
                        $scope.commune.data.commune = item.id;
                        $scope.commune.selectedCommuneLabel = item.label;
                    }
                },
                initEdit: function () {
                    var commune = ruchJowSecurity.currentUser.commune

                    if (commune) {
                        $scope.commune.selectedCommuneLabel = commune.name;
                        $scope.commune.data.commune = commune.id;
                    } else {
                        $scope.commune.selectedCommuneLabel = null;
                        $scope.commune.data.commune = null;
                    }

                    $scope.commune.edit = true;
                },

                save: function () {
                    $scope.commune.saveInProgress = true;
                    ruchJowSecurity.getUserService().updateUserCommune($scope.commune.data.commune)
                        .then(function () {
                            return ruchJowSecurity.requestCurrentUser()
                        })
                        .then(function () {
                            $scope.commune.edit = false;
                        })['finally'](function () {
                        $scope.commune.saveInProgress = false;
                    });
                }
            };

            // ORGANISATION
            $scope.orgs = {
                edit: false,
                editName: false,
                loading: false,
                saveInProgress: false,

                validation: {
                    url: {
                        pattern: /^(https?:\/\/)?([0-9a-ąćęłńóśźż\.-]+)\.([a-z\.]{2,6})(\/[a-zA-Z0-9\.-_]*)*\/?$/,
                        $labels: {
                            pattern: 'registerForm.organisationUrl.pattern.error',
                            required: 'registerForm.organisationUrl.required.error'
                        }
                    },
                    name: {
                        pattern: /^(?=[^ ])( ?[0-9a-ząćęłńóśźżA-ZĄĆĘŁŃÓŚŹŻ.,\-_"'!@#$%^&*()+=\\\/\][<>:;]){3,}$/,
                        $labels: {
                            pattern: 'registerForm.organisationName.pattern.error',
                            required: 'registerForm.organisationName.required.error'
                        }
                    }
                },

                input: {
                    name: null,
                        url: null
                },
                data: {
                    name: null,
                        url: null
                },


                get: function (urlPart) {
                    if (getOrgsPromise) {
                        ruchJowFindOrganisations.cancel(getOrgsPromise);
                    }

                    getOrgsPromise = ruchJowFindOrganisations.getOrganisations(urlPart);

                    return getOrgsPromise.then(function (organisations) {
                        var found = false;
                        angular.forEach(organisations, function (organisation) {
                            if (organisation.url === urlPart) {
                                found = true;
                            }
                            organisation.label = organisation.url + ' - ' + organisation.name;
                        });
                        if (!found) {
                            organisations.unshift({
                                id: null,
                                label: urlPart,
                                url: urlPart,
                                name: $scope.orgs.input.name
                            });
                        }

                        return organisations;
                    })
                },
                set: function (item) {
                    if (!item) {
                        $scope.orgs.editName = true;
                        $scope.orgs.data.url = null;
                        $scope.orgs.data.name = null;
                        $scope.orgs.input.url = '';
                        $scope.orgs.input.name = '';

                        return;
                    }

                    $scope.orgs.editName = (item.id === null);

                    $scope.orgs.data.url = item.url;
                    $scope.orgs.data.name = item.name;

                    if (!$scope.orgs.editName) {
                        $scope.orgs.input.name = $scope.orgs.data.name;
                    }
                },
                updateUrl: function () {
                    $scope.orgs.input.url = $scope.orgs.data.url;
                },

                initEdit: function () {
                    var organisation = ruchJowSecurity.currentUser.organisation;

                    if (!organisation) {
                        $scope.orgs.input.name = null;
                        $scope.orgs.input.url = null;
                        $scope.orgs.data.name = null;
                        $scope.orgs.data.url = null;

                    } else {
                        $scope.orgs.input.name = organisation.name;
                        $scope.orgs.input.url = organisation.url;
                        $scope.orgs.data.name = organisation.name;
                        $scope.orgs.data.url = organisation.url;
                    }

                    $scope.orgs.edit = true;
                },

                save: function () {
                    $scope.orgs.saveInProgress = true;
                    ruchJowSecurity.getUserService().updateUserOrganisation($scope.orgs.data.url, $scope.orgs.data.name)
                        .then(function () {
                            return ruchJowSecurity.requestCurrentUser()
                        })
                        .then(function () {
                            $scope.orgs.edit = false;
                        })['finally'](function () {
                            $scope.orgs.saveInProgress = false;
                        });
                }
            };

            $scope.$watch('orgs.input.name', function (val) {
                if ($scope.orgs.editName) {
                    $scope.orgs.data.name = val;
                }
            });

            $scope.$watch('orgs.input.url', function (val) {
                if (val === "") {
                    $scope.orgs.set(null);
                }
            });


            // PHONE
            $scope.phone = {
                edit: false,
                saveInProgress: false,
                data: null,
                validation: {
                    pattern: /^[ ()-]*(?:(\+|00)[ ()-]*1?[ ()-]*[1-9](?:[ ()-]*\d)){0,2}(?:[ ()-]*\d){9}[ ()-]*$/,
                    $labels: {
                        pattern: 'phone.error.pattern',
                        required: 'phone.error.required'
                    }
                },
                initEdit: function () {
                    $scope.phone.edit = true;
                    $scope.phone.data = ruchJowSecurity.currentUser.phone;
                },
                save: function () {
                    $scope.phone.saveInProgress = true;
                    ruchJowSecurity.getUserService().updateUserPhone($scope.phone.data)
                        .then(function () {
                            return ruchJowSecurity.requestCurrentUser()
                        })
                        .then(function () {
                            $scope.phone.edit = false;
                        })['finally'](function () {
                        $scope.phone.saveInProgress = false;
                    });
                }
            };

            // DISPLAY NAME FORMAT
            $scope.displayNameFormat = {
                edit: false,
                saveInProgress: false,
                data: null,
                values: [
                    'nick',
                    'full_name'
                ],
                //validation: {
                //    pattern: /^[ ()-]*(?:(\+|00)[ ()-]*1?[ ()-]*[1-9](?:[ ()-]*\d)){0,2}(?:[ ()-]*\d){9}[ ()-]*$/,
                //    $labels: {
                //        pattern: 'phone.error.pattern',
                //        required: 'phone.error.required'
                //    }
                //},
                initEdit: function () {
                    $scope.displayNameFormat.edit = true;
                    $scope.displayNameFormat.data = ruchJowSecurity.currentUser.displayNameFormat;
                },
                save: function () {
                    $scope.displayNameFormat.saveInProgress = true;
                    ruchJowSecurity.getUserService().updateDisplayNameFormat($scope.displayNameFormat.data)
                        .then(function () {
                            return ruchJowSecurity.requestCurrentUser()
                        })
                        .then(function () {
                            $scope.displayNameFormat.edit = false;
                        })['finally'](function () {
                            $scope.displayNameFormat.saveInProgress = false;
                        });
                }
            };

            //PASSWORD
            $scope.password = {
                edit: false,
                inProgress: false,
                saveInProgress: false,
                deleteInProgress: false,
                data: {
                    currentPassword: null,
                    newPassword: null,
                    newPasswordRepeat: null

                },
                input: {
                    currentPassword: null,
                    newPassword: null,
                    newPasswordRepeat: null
                },
                validation: {
                    currentPassword: {
                        $labels: {
                            ruchJowEquals: 'password.newPasswordRepeat.error.ruchJowEquals',
                            required: 'password.currentPassword.error.required'
                        }
                    },
                    newPassword: {
                        pattern: /^(?=.*[A-Z])(?=.*[0-9]).{5,20}$/,
                        $labels: {
                            pattern: 'password.newPassword.error.pattern',
                            required: 'password.newPassword.error.required'
                        }
                    },
                    newPasswordRepeat: {
                        $labels: {
                            ruchJowEquals: 'password.newPasswordRepeat.error.ruchJowEquals',
                            required: 'password.newPasswordRepeat.error.required'
                        }
                    }

                },
                isEmpty: function () {
                    return $scope.password.data.newPassword === null;
                },
                initEdit: function () {
                    $scope.password.data.currentPassword = null;
                    $scope.password.data.newPassword = null;
                    $scope.password.data.newPasswordRepeat = null;

                    $scope.password.edit = true;
                },
                save: function () {
                    $scope.password.inProgress = true;
                    $scope.password.saveInProgress = true;
                    ruchJowSecurity.getUserService().updateUserPassword(
                        $scope.password.data.newPassword,
                        $scope.password.data.currentPassword
                    )
                        .then(function () {
                            return ruchJowSecurity.requestCurrentUser()
                        })
                        .then(function () {
                            $scope.password.edit = false;
                        })['finally'](function () {
                        $scope.password.inProgress = false;
                        $scope.password.saveInProgress = false;

                    });
                }

            },

            // ADDRESS
            $scope.address = {
                edit: false,
                inProgress: false,
                saveInProgress: false,
                deleteInProgress: false,
                data: {
                    firstName: null,
                    lastName: null,
                    street: null,
                    house: null,
                    flat: null,
                    postCode: null,
                    city: null
                },
                validation: {
                    street: {
                        pattern: /^[0-9a-ząćęłńóśźżA-ZĄĆĘŁŃÓŚŹŻ+=()*&%$#@!?,;: "\/-]{2,}/,
                        $labels: {
                            pattern: 'address.street.error.pattern'
                        }
                    },
                    house: {
                        pattern: /^[0-9a-zA-Z\/-]+$/,
                        $labels: {
                            pattern: 'address.house.error.pattern',
                            required: 'address.house.error.required'
                        }
                    },
                    flat: {
                        pattern: /^[0-9a-zA-Z\/-]+$/,
                        $labels: {
                            pattern: 'address.flat.error.pattern'
                        }
                    },
                    postCode: {
                        pattern: /^\d\d[ -.]?\d\d\d$/,
                        $labels: {
                            pattern: 'address.postCode.error.pattern',
                            required: 'address.postCode.error.required'
                        }
                    },
                    city: {
                        pattern: /^([A-ZĄĆĘŁŃÓŚŹŻ]|[a-ząćęłńóśźż]){2,}([ -]?([A-ZĄĆĘŁŃÓŚŹŻ]|[a-ząćęłńóśźż]))*$/,
                        $labels: {
                            pattern: 'address.city.error.pattern',
                            required: 'address.city.error.required'
                        }
                    }

                },
                isEmpty: function () {
                    return $scope.address.data.firstName === null &&
                        $scope.address.data.lastName === null &&
                        $scope.address.data.street === null &&
                        $scope.address.data.house === null &&
                        $scope.address.data.flat === null &&
                        $scope.address.data.postCode === null &&
                        $scope.address.data.city === null;
                },
                initEdit: function () {
                    var user = ruchJowSecurity.currentUser;
                    $scope.address.data.firstName = user.address.firstName;
                    $scope.address.data.lastName = user.address.lastName;
                    $scope.address.data.street = user.address.street;
                    $scope.address.data.house = user.address.house;
                    $scope.address.data.flat = user.address.flat;
                    $scope.address.data.postCode = user.address.postCode;
                    $scope.address.data.city = user.address.city;

                    $scope.address.edit = true;
                },
                save: function () {
                    $scope.address.inProgress = true;
                    $scope.address.saveInProgress = true;
                    ruchJowSecurity.getUserService().updateUserAddress(
                        $scope.address.data.firstName,
                        $scope.address.data.lastName,
                        $scope.address.data.street,
                        $scope.address.data.house,
                        $scope.address.data.flat,
                        $scope.address.data.postCode,
                        $scope.address.data.city
                    )
                        .then(function () {
                            return ruchJowSecurity.requestCurrentUser()
                        })
                        .then(function () {
                            $scope.address.edit = false;
                        })['finally'](function () {
                        $scope.address.inProgress = false;
                        $scope.address.saveInProgress = false;
                    });
                },
                'delete': function () {
                    $scope.address.inProgress = true;
                    $scope.address.deleteInProgress = true;
                    ruchJowSecurity.getUserService().updateUserAddress()
                        .then(function () {
                            return ruchJowSecurity.requestCurrentUser()
                        })
                        .then(function () {
                            $scope.address.edit = false;
                        })['finally'](function () {
                        $scope.address.inProgress = false;
                        $scope.address.deleteInProgress = false;
                    });
                }

            };

            // SOCIAL LINKS
            $scope.socialLinks = {
                edit: false,
                saveInProgress: false,
                deleteInProgress: false,
                availableServices: ruchJowSecurity.getUserService().getSocialLinkDefinitions(),
                data: {},
                validation: {
                    //pattern: '',
                    $labels: {
                        pattern: 'socialLinks.error.pattern'
                    }
                },
                initEdit: function () {
                    angular.forEach(ruchJowSecurity.currentUser.socialLinks, function (value, name) {
                        $scope.socialLinks.data[name] = value;
                    });

                    $scope.socialLinks.edit = true;
                },
                save: function () {
                    $scope.socialLinks.saveInProgress = true;
                    ruchJowSecurity.getUserService().updateUserSocialLinks($scope.socialLinks.data)
                        .then(function () {
                            return ruchJowSecurity.requestCurrentUser()
                        })
                        .then(function () {
                            $scope.socialLinks.edit = false;
                        })['finally'](function () {
                        $scope.socialLinks.saveInProgress = false;
                    });
                }
            };

            // ABOUT
            $scope.about = {
                edit: false,
                saveInProgress: false,
                data: null,
                initEdit: function () {
                    $scope.about.data = ruchJowSecurity.currentUser.about;

                    $scope.about.edit = true;
                },
                save: function () {
                    $scope.about.saveInProgress = true;
                    ruchJowSecurity.getUserService().updateUserAbout($scope.about.data)
                        .then(function () {
                            return ruchJowSecurity.requestCurrentUser()
                        })
                        .then(function () {
                            $scope.about.edit = false;
                        })['finally'](function () {
                        $scope.about.saveInProgress = false;
                    });
                },
                validation: {
                    $labels: {}
                }
            };

            // VISIBILITY
            $scope.visibility = {
                edit: false,
                saveInProgress: false,
                data: null,
                list: [
                    'firstName',
                    'lastName',
                    'organisation',
                    'socialLinks',
                    'about'
                ],
                initEdit: function () {
                    $scope.visibility.data = ruchJowSecurity.currentUser.visibility;
                    $scope.visibility.edit = true;
                },
                save: function () {
                    $scope.visibility.saveInProgress = true;
                    ruchJowSecurity.getUserService().updateUserVisibility($scope.visibility.data)
                        .then(function () {
                            return ruchJowSecurity.requestCurrentUser()
                        })
                        .then(function () {
                            $scope.visibility.edit = false;
                        })['finally'](function () {
                        $scope.visibility.saveInProgress = false;
                    });
                },
                toggle: function (name) {
                    if ($scope.visibility.data.hasOwnProperty(name)) {
                        $scope.visibility.data[name] = !$scope.visibility.data[name];
                    }
                }
            };



        }
    ]);

