/**
 * Created by grest on 7/22/14.
 */

angular.module('ruchJow.backend.translations', ['pascalprecht.translate'])
    .config(['$translateProvider', function ($translateProvider) {
        $translateProvider
            .preferredLanguage('pl')
            .fallbackLanguage('en');

        $translateProvider.translations('pl', {

            raw: {
                points: '{NUMBER} ' +
                    '{NUMBER, plural,' +
                    ' one {punkt}' +
                    ' few {punkty}' +
                    ' many {punktów}' +
                    ' other {punkty}}',
                nick: {
                    label: 'Nick'
                },
                email: {
                    label: 'E-mail'
                },
                commune: {
                    label: 'Gmina'
                },
                'Find user...': 'Znajdź użytkownika...',
                'Points added': 'Punkty zostały dodane',
                'Donation added': 'Wpłata została dodana',
                'Internal response error': 'Serwer zwrócił nieoczekiwaną odpowiedź'
            },


            form: {
                edit: 'zmień',
                save: 'zapisz',
                'delete': 'usuń',
                cancel: 'anuluj',
                add: 'dodaj'
            },

            tu: {
                type: {
                    'country': 'kraj',
                    'region': 'województwo',
                    'district': 'powiat',
                    'commune': 'gmina',

                    'region_plural': 'województwa',
                    'district_plural': 'powiaty',
                    'commune_plural': 'gminy'
                },
                Type: {
                    'country': 'Kraj',
                    'region': 'Województwo',
                    'district': 'Powiat',
                    'commune': 'Gmina'
                },
                'whole_country': 'cały kraj'
            },

            points: {
                type: {
                    user: {
                        support: 'poparcie Ruchu JOW',
                        referral: 'zaproszenie użytkownika'
                    },
                    'organise.event': 'zorganizowanie wydarzenia',
                    'distribute.leaflets': 'dystrybucja ulotek',
                    donation: 'wpłata',
                    other: 'inne'
                }
            }

        });
    }]);