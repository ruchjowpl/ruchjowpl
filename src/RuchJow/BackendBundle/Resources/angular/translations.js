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
                    'organise.referendumPoint': 'organizacja punktu referendalnego',
                    'print.materials': 'druk materiałów',
                    donation: 'wpłata',
                    'local.gov.support': 'poparcie środowisk samorządowych',
                    other: 'inne'
                }
            },
            referendumPoints: {
                editForm: {
                    title: {
                        label: 'Tytuł',
                        'required.error': 'Tytuł jest wymagany'
                    },
                    subtitle: {
                        label: 'Podtytuł'
                    },
                    description: {
                        label: 'Opis',
                        'required.error': 'Opis jest wymagany'
                    },
                    lat: {
                        label: 'Szerokość geograficzna',
                        'min': 'Szerokość jest za mała',
                        'max': 'Szerokość jest za duża',
                        'required.error': 'Szerokość geograficzna jest wymagana'
                    },
                    lng: {
                        label: 'Długość geograficzna',
                        'min': 'Długość jest za mała',
                        'max': 'Długość jest za duża',
                        'required.error': 'Długość geograficzna jest wymagana'
                    },
                    commune: {
                        label: 'Kod pocztowy lub nazwa gminy',
                        'required.error': 'Pole gmina jest wymagane'
                    },
                    submit: 'Zapisz'
                }
            },
            jowEvents: {
                editForm: {
                    venue: {
                        label: 'Miejsce (adres)'
                    },
                    title: {
                        label: 'Tytuł'
                    },
                    link: {
                        label: 'Link'
                    },
                    commune: {
                        label: 'Gmina'
                    },
                    submit: 'Zapisz'
                }
            }


        });
    }]);