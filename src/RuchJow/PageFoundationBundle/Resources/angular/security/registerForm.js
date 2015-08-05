angular.module('ruchJow.security.registerForm', ['ui.bootstrap.modal', 'ui.bootstrap.typeahead', 'ruchJow.forms', 'ruchJow.pfound.data'])
    .controller('RegisterFormSubmitCtrl', ['$scope', '$timeout', function ($scope, $timeout) {
        $scope.registerAsync = function () {
            $timeout(function () {
                if ($scope.ruchJowRegister.$valid && !$scope.ruchJowRegister.$pending) {
                    $scope.register();
                }
            }, 100);
        };
    }])
    .controller('RegisterFormCtrl', [
        '$scope',
        '$modalInstance',
        '$q',
        '$timeout',
        'ipCookie',
        'ruchJowFindCountries',
        'ruchJowFindCommunes',
        'ruchJowFindOrganisations',
        'ruchJowStatistics',
        'registerCallback',
        'initData',
        function ($scope, $modalInstance, $q, $timeout, ipCookie, ruchJowFindCountries, ruchJowFindCommunes, ruchJowFindOrganisations, ruchJowStatistics, registerCallback, initData) {

        $scope.showUntouched = false;

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

        $scope.getSupportersCntTranslationData = function () {
            var value = ruchJowStatistics.get('basic.supportersCnt');

            return { NUMBER: value, FORMATTED_NUMBER: value };
        };

        // This vars must be encapsulated in obj. because there will be created child $scope
        // with its own version of primitive vars.
        $scope.data = {
            nick: (initData && initData.hasOwnProperty('nick')) ? initData.nick : null,
            email: (initData && initData.hasOwnProperty('email')) ? initData.email : null,
            //phone: null,
            country: null,
            commune: null,
            organisationUrl: null,
            organisationName: null,
            password: null,
            referral: ipCookie('referralToken'),
            isRegulationsAccepted: false
        };
        $scope.localData = {
            passwordRepeat: null,
            passwordVisible: false,
            countryInputName: null,
            selectedCountryLabel: null,
            communeInputName: null,
            selectedCommuneLabel: null
        };
        $scope.$watch('localData.passwordVisible', function () {
            $scope.data.password = null;
            $scope.localData.passwordRepeat = null;
        });

        $scope.validation = {
            nick: {
                pattern: /^(?=.*[^ _\-]$)([a-ząćęłńóśźżA-ZĄĆĘŁŃÓŚŹŻ0-9][ _.\-]?){4,}$/,
                //pattern: /^[a-ząćęłńóśźżA-ZĄĆĘŁŃÓŚŹŻ0-9]{4,}$/,
                $labels: {
                    pattern: 'registerForm.nick.pattern.error',
                    required: 'registerForm.nick.required.error',
                    unique: 'registerForm.nick.unique.error'
                }
            },
            //firstName: {
            //    pattern: /^[A-ZĄĆĘŁŃÓŚŹŻ][a-ząćęłńóśźż]+$/,
            //    $labels: {
            //        pattern: 'registerForm.firstName.pattern.error',
            //        required: 'registerForm.firstName.required.error'
            //    }
            //},
            //lastName: {
            //    pattern: /^[A-ZĄĆĘŁŃÓŚŹŻ][a-ząćęłńóśźż]+(-[A-ZĄĆĘŁŃÓŚŹŻ][a-ząćęłńóśźż]+)?$/,
            //    $labels: {
            //        pattern: 'registerForm.lastName.pattern.error',
            //        required: 'registerForm.lastName.required.error'
            //    }
            //},
            email: {
                pattern: /^[a-zA-Z0-9.!#$%&'*+/?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/,
                $labels: {
                    pattern: 'registerForm.email.pattern.error',
                    required: 'registerForm.email.required.error',
                    unique: 'registerForm.email.unique.error'
                }
            },
            //phone: {
            //    pattern: /^[ ()-]*(?:(\+|00)[ ()-]*1?[ ()-]*[1-9](?:[ ()-]*\d)){0,2}(?:[ ()-]*\d){9}[ ()-]*$/,
            //    $labels: {
            //        pattern: 'registerForm.phone.pattern.error',
            //        required: 'registerForm.phone.required.error'
            //    }
            //},
            country: {
                $labels: {
                    country: 'registerForm.country.country.error',
                    required: 'registerForm.country.required.error'
                }
            },
            commune: {
                $labels: {
                    commune: 'registerForm.commune.commune'
                }
            },
            organisationUrl: {
                pattern: /^(https?:\/\/)?([0-9a-ąćęłńóśźż\.-]+)\.([a-z\.]{2,6})(\/[a-zA-Z0-9\.-_]*)*\/?$/,
                $labels: {
                    pattern: 'registerForm.organisationUrl.pattern.error'
                }
            },
            organisationName: {
                pattern: /^(?=[^ ])( ?[0-9a-ząćęłńóśźżA-ZĄĆĘŁŃÓŚŹŻ.,\-_"'!@#$%^&*()+=\\\/\][<>:;]){3,}$/,
                $labels: {
                    pattern: 'registerForm.organisationName.pattern.error',
                    required: 'registerForm.organisationName.required.error'
                }
            },
            password: {
                pattern: /^(?=.*[A-Z])(?=.*[0-9]).{5,20}$/,
                $labels: {
                    pattern: 'registerForm.password.pattern.error'
                }
            },
            passwordRepeat: {
                $labels: {
                    ruchJowEquals: 'registerForm.passwordRepeat.ruchJowEquals.error'
                }
            },
            isRegulationsAccepted: {
                $labels: {
                    required: 'registerForm.isRegulationsAccepted.required.error'
                }
            }

        };

        // Organisation
        var getOrgsPromise;
        $scope.orgs = {
            visible: false,
            loading: false,
            input: {
                url: null,
                name: null
            },
            get: function (urlPart) {
                if (getOrgsPromise) {
                    ruchJowFindOrganisations.cancel(getOrgsPromise);
                }

                getOrgsPromise = ruchJowFindOrganisations.getOrganisations(urlPart);

                return getOrgsPromise.then(function (organisations) {
                    organisations = organisations || [];
                    var found = false;
                    angular.forEach(organisations, function (organisation) {
                        if (organisation.url === urlPart) {
                            found = true;
                        }
                        organisation.label = organisation.url + ' - ' + organisation.name;
                        //organisation.id = organisation.id;
                        //organisation.url = organisation.url;
                        //organisation.name = organisation.name;
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
                    $scope.orgs.edit = true;
                    $scope.data.organisationUrl = null;
                    $scope.data.organisationName = null;
                    $scope.orgs.input.url = '';
                    $scope.orgs.input.name = '';

                    return;
                }

                $scope.orgs.edit = (item.id === null);

                $scope.data.organisationUrl = item.url;
                $scope.data.organisationName = item.name;

                if (!$scope.orgs.edit) {
                    $scope.orgs.input.name = '';
                }
            },
            updateUrl: function () {
                $scope.orgs.input.url = $scope.data.organisationUrl;
            },
            edit: true
        };

        $scope.$watch('orgs.input.name', function (val) {
            if ($scope.orgs.edit) {
                $scope.data.organisationName = val;
            }
        });

        $scope.$watch('orgs.input.url', function (val) {
            if (val === "") {
                $scope.orgs.set(null);
            }
        });

        $scope.$watch('orgs.visible', function (val) {
            if (!val) {
                $scope.orgs.set(null);
            }
        });


        // Countries
        $scope.loading = { countries: false };

        var getCountriesPromise;
        $scope.getCountries = function (input) {

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

        };
        $scope.setCountry = function (item) {
            if (!item) {
                $scope.data.country = null;
                $scope.localData.selectedCountryLabel = null;
            } else {
                $scope.data.country = item.code;
                $scope.localData.selectedCountryLabel = item.label;
            }
        };
        $scope.setCountry({code: 'PL', label: 'Polska'});

        // Communes
        $scope.loading = { communes: false };

        var getCommunesPromise;
        $scope.getCommunes = function (input) {

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

        };
        $scope.setCommune = function (item) {
            if (!item) {
                $scope.data.commune = null;
                $scope.localData.selectedCommuneLabel = null;
            } else {
                $scope.data.commune = item.id;
                $scope.localData.selectedCommuneLabel = item.label;
            }
        };

        $scope.errorMessage = null;
        $scope.inProgress = false;

        $scope.register = function () {

            $scope.inProgress = true;
            $scope.errorMessage = null;

            $q.when(registerCallback($scope.data))
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
    }])
    .factory('ruchJowSecurityRegisterForm', ['$modal', function ($modal) {

        var service = {
            modal: null,
            open: function (registerCallback, initData) {
                if (service.modal) {
                    throw new Error('Trying to open a dialog that is already open!');
                }

                service.modal = $modal.open({
                    templateUrl: 'registerFormModal.html',
                    controller: 'RegisterFormCtrl',
                    resolve: {
                        initData: function () {
                            return initData || {};
                        },
                        registerCallback: function () {
                            return registerCallback;
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
;