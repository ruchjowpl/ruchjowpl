/**
 * Created by grest on 10/10/14.
 */

angular.module('ruchJow.transferujPl', [])
    .provider('ruchJowTransferujPl', [function () {



        var transferujUrl = 'https://secure.transferuj.pl',
            allowedOptions = {
                online: 'online',
                channel: 'kanal',
                kanal: null,
                blockChannel: 'zablokuj',
                zablokuj: null,
                returnUrl: 'pow_url',
                pow_url: null,
                returnErrorUrl: 'pow_url_blad',
                pow_url_blad: null,
                language: 'jezyk',
                jezyk: null
            };

        var encodeUrl;


        var users = {},
            defaultUser;

        var provider = {

            setGlobalEncodeUrl: function (url) {
                encodeUrl = url;

                return provider;
            },

            addUser: function (name, id, options, setDefault) {
                if (users.hasOwnProperty(name)) {
                    throw new Error('Transferuj.pl user with name ' + name + ' has been defined already.')
                }

                var user = new User(id);

                if (options) {
                    if (options.encodeUrl) {
                        user.setEncodeUrl(options.encodeUrl);
                    }
                }

                users[name] = user;

                if (setDefault) {
                    defaultUser = name;
                }

                return provider;
            },
            setDefaultUser: function (name) {
                if (!users[name]) {
                    throw new Error('Transferuj.pl user ' + user + ' cannot be set as default as it has not been defined yet.')
                }

                return provider;
            },
            $get: ['$q', '$http', function ($q, $http) {

                //noinspection UnnecessaryLocalVariableJS
                var service = {
                    makePayment: function (amount, description, crc, options, name) {
                        // Cast to float;
                        amount = +amount;

                        if (isNaN(+amount) || amount <= 0) {
                            return $q.reject('Incorrect amount');
                        }

                        return encodeCheckSum(name, amount, crc)
                            .then(function (md5sum) {
                                var user = getUser(name),
                                    opts = {
                                        id: user.id,
                                        kwota: amount,
                                        opis: description,
                                        crc: crc,
                                        md5sum: md5sum
                                    };

                                if (options) {
                                    for (var key in allowedOptions) {
                                        if (
                                            allowedOptions.hasOwnProperty(key) &&
                                            options.hasOwnProperty(key)
                                        ) {
                                            var translatedKey = allowedOptions[key] || key;
                                            opts[translatedKey] = options[key];
                                        }
                                    }
                                }

                                var inputs = '';
                                for (var paramName in opts) {
                                    if (opts.hasOwnProperty(paramName)) {
                                        inputs += '<input type="hidden" name="' + paramName + '" value="' + String(opts[paramName]).replace(/"/g, '&quot;') + '">';
                                    }
                                }

                                var form = angular.element(
                                    '<form style="visibility: hidden;" method="POST" action="' + transferujUrl + '">' +
                                    inputs +
                                    '</form>'
                                );
                                angular.element(window.document.body).append(form);
                                form.submit();
                            });


                        // Helper functions:
                        function encodeCheckSum(name, amount, crc) {
                            var user = getUser(name),
                                url = user.getEncodeUrl() || encodeUrl;

                            if (!url) {
                                return $q.reject('Encode url has not been defined.');
                                //throw new Error('Encode url has not been defined.')
                            }

                            var httpConfig = {
                                headers: {'X-Requested-With': 'XMLHttpRequest'},
                                method: 'POST',
                                url: url,
                                data: JSON.stringify({
                                    id: user.id,
                                    amount: amount,
                                    crc: crc
                                }),
                                transformResponse: function (data) {
                                    return JSON.parse(data);
                                }
                            };

                            return $http(httpConfig).then(function (request) {
                                return request.data;
                            });
                        }

                    }
                };

                return service;
            }]

        };

        return provider;



        // Transferuj.pl user object.
        function User(id) {
            this.id = id;

            var encodeUrl;
            this.setEncodeUrl = function (url) {
                encodeUrl = url;
            };
            this.getEncodeUrl = function () {
                return encodeUrl;
            }

        }

        // Helper functions:
        function getUser(name) {
            if (!name) {
                if (!defaultUser) {
                    throw new Error('Default Transferuj.pl user is not defined.');
                }

                name = defaultUser;
            }

            if (!users[name]) {
                throw new Error('Transferuj.pl user ' + name + ' is not defined.')
            }

            return users[name];
        }

    }]);