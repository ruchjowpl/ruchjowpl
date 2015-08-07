/**
 * Created by grest on 7/22/14.
 */

angular.module('ruchJow.translations', ['pascalprecht.translate'])
    .config(['$translateProvider', function ($translateProvider) {
        $translateProvider
            .preferredLanguage('pl')
            .fallbackLanguage('en');

        $translateProvider.translations('pl', {

            'Privacy Policy': 'Polityka prywatności',
            'privacy policy': 'polityka prywatności',
            'Terms': 'Regulamin',
            'Rules & terms': 'Zasady działania i regulamin RuchJOW',
            'Contact': 'Kontakt',

            'I Support': 'Popieram',
            'I support': 'popieram',

            raw: {
                points: '{NUMBER} ' +
                    '{NUMBER, plural,' +
                    ' one {punkt}' +
                    ' few {punkty}' +
                    ' many {punktów}' +
                    ' other {punkty}}'
            },

            nick: {
                label: 'Nick'
            },
            displayNameFormat: {
                label: 'Sposób wyświetlania nazwy użytkownika',
                values: {
                    nick: { label: 'Nick' },
                    full_name: { label: 'Imię Nazwisko' }
                }
            },
            userDataVisibility: {
                label: 'Ustawienia widoczności danych dla innych użytkowników',
                firstName: { label: 'Imię' },
                lastName: { label: 'Nazwisko' },
                organisation: { label: 'Promowana organizacja, blog...' },
                socialLinks: { label: 'Linki do Twoich profili społecznościowych' },
                about: { label: 'Kilka słów o Tobie' }
            },
            userDataDeleteAccount: {
                label: 'Usunięcie konta',
                'delete': {
                    label: 'Usuń konto'
                }
            },
            email: {
                label: 'E-mail'
            },
            country: {
                label: 'Kraj'
            },
            commune: {
                label: 'Gmina'
            },

            address: {
                label: 'Adres',
                firstName: {
                    label: 'Imię',
                    placeholder: 'imię',
                    error: {
                        pattern: 'Niepoprawny format imienia.',
                        required: 'Imię nie może być puste.'
                    }
                },
                lastName: {
                    label: 'Nazwisko',
                    placeholder: 'nazwisko',
                    error: {
                        pattern: 'Niepoprawny format nazwiska.',
                        required: 'Nazwisko nie może być puste.'
                    }
                },
                street: {
                    label: 'Ulica',
                    placeholder: 'ulica',
                    error: {
                        pattern: 'Niepoprawny format nazwy ulicy.'
                    }
                },
                house: {
                    label: 'Nr domu',
                    placeholder: 'nr domu',
                    error: {
                        pattern: 'Niepoprawny format numeru domu.',
                        required: 'Numer domu nie może być pusty.'
                    }
                },
                flat: {
                    label: 'Nr lokalu',
                    placeholder: 'nr lokalu',
                    error: {
                        pattern: 'Niepoprawny format numeru lokalu.'
                    }
                },
                postCode: {
                    label: 'Kod pocztowy',
                    placeholder: 'kod pocztowy',
                    error: {
                        pattern: 'Kod pocztowy powinien mieć format 54-321.',
                        required: 'Kod pocztowy nie może być pusty.'
                    }
                },
                city: {
                    label: 'Miejscowość',
                    placeholder: 'miejscowość',
                    error: {
                        pattern: 'Niepoprawny format nazwy miejscowości.',
                        required: 'Nazwa miejscowości nie może być pusta.'
                    }
                }
            },

            organisation: {
                label: 'Chcę promować swoją organizację, ulubionego bloga, serwis internetowy czy fanpage',
                name: {
                    label: 'Nazwa',
                    placeholder: 'nazwa'
                },
                url: {
                    label: 'Strona',
                    placeholder: 'strona'
                }
            },

            password: {
                label: 'Hasło',
                currentPassword: {
                    label: 'Aktualne hasło',
                    placeholder: 'aktualne hasło',
                    error: {
                        ruchJowEquals: 'Aktualne hasło jest poprawne',
                        required: 'Aktualne hasło nie może być puste'
                    }
                },
                newPassword: {
                    label: 'Nowe hasło',
                    placeholder: 'nowe hasło',
                    error: {
                        pattern: 'Format hasła jest nieprawidłowy. Hasło musi zawierać wielką literę i cyfrę oraz musi mieć długość od 5 do 20 znaków',
                        required: 'Hasło nie może być puste'
                    }
                },
                newPasswordRepeat: {
                    label: 'Powtórz nowe hasło',
                    placeholder: 'powtórz hasło',
                    error: {
                        ruchJowEquals: 'Hasło i powtórzone hasło muszą być identyczne'

                    }
                }

            },

            phone: {
                label: 'Telefon',
                placeholder: 'nr telefonu',
                error: {
                    pattern: 'Numer telefonu powinien mieć format 71 987 65 43 lub 987 654 321. Dodatkowo może być poprzedzony numerem kierunkowym kraju (np. +48).',
                    required: 'Numer telefonu nie może być pusty.'
                }
            },

            socialLinks: {
                label: 'Twoje profile społecznościowe',
                error: {
                    pattern: 'Niepoprawny format linku.'
                }

            },

            form: {
                edit: 'zmień',
                save: 'zapisz',
                delete: 'usuń',
                cancel: 'anuluj'
            },

            aboutUser: {
                label: 'Kilka słów o Tobie'
            },

            supportForm: {
                invite_friends: {
                    title: 'Zaproszenie znajomych',

                    email: {
                        label: 'email',
                        placeholder: 'Podaj adres email znajomego',
                        'pattern.error': 'wprowadzony adres ma niepoprawny format'
                    },

                    invite: 'ZAPROŚ',

                    'sent.confirmation.message':
                        '{NUMBER, plural,' +
                        ' one {Zaproszenie zostało wysłane}' +
                        //' few {Poparły}' +
                        //' many {Poparło}' +
                        ' other {Zaproszenia zostały wysłane}}',


                    copy_referral: {
                        label: 'Skopiuj i wyślij Twój unikalny link'
                    },

                    share: {
                        label: 'Lub podziel się ze znajomymi'
                    }

                },

                organise_event: {
                    title: 'Zorganizuj wydarzenie',
                    description: {
                        label: 'Opis wydarzenia / dodatkowe informacje'
                    },

                    submit: 'Zgłoś chęć organizacji',

                    'sent.confirmation.message': 'Zgłoszenie chęci organizacji wydarzenia zostało wysłane',
                    'send.error.message': 'Wystąpił nieprzewidziany błąd. Prosimy o ponowne zgłoszenie. Jeżeli problem będzie nadal występował, prosimy o bezpośredni kontakt.',
                    'send.error.message_empty_address': 'Zgłoszenie chęci organizacji wydarzenia wymaga podania adresu w Edycji profilu'
                },
                organise_referendum_point: {
                    title: 'Zorganizuj punkt referendalny',
                    description: {
                        label: 'Opis punktu referendalnego'
                    },

                    submit: 'Zgłoś chęć organizacji',

                    'sent.confirmation.message': 'Zgłoszenie chęci organizacji punktu referendalnego zostało wysłane',
                    'send.error.message': 'Wystąpił nieprzewidziany błąd. Prosimy o ponowne zgłoszenie. Jeżeli problem będzie nadal występował, prosimy o bezpośredni kontakt.',
                    'send.error.message_empty_address': 'Zgłoszenie chęci organizacji punktu referendalnego wymaga podania adresu w Edycji profilu'
                },

                distribute_leaflets: {
                    title: 'Kolportuj ulotki',

                    description: {
                        label: 'Dodatkowe informacje'
                    },

                    submit: 'Zgłoś chęć kolportażu',

                    'sent.confirmation.message': 'Zgłoszenie chęci kolportażu ulotek zostało wysłane',
                    'send.error.message': 'Wystąpił nieprzewidziany błąd. Prosimy o ponowne zgłoszenie. Jeżeli problem będzie nadal występował, prosimy o bezpośredni kontakt.',
                    'send.error.message_empty_address': 'Zgłoszenie chęci kolportażu wymaga podania adresu w Edycji profilu'
                },

                make_donation: {
                    title: 'Wspomóż finansowo',
                    average_donation_7d: {
                        label: 'Średnia wpłata w ostatnim tygodniu'
                    },
                    otherAmount: 'inna kwota',
                    submit: 'e-przelew lub PayPal',
                    alternative: {
                        description: 'lub wpłać bezpośrednio na konto dowolną kwotę:',
                        accountDescription: 'Pekao SA nr konta:',
                        iban: '81 1240 6768 1111 0010 5970 1904',
                        additionalInfo: 'podając w tytule swój nick z serwisu ruchjow.pl'
                    }
                },
                print_materials: {
                    title: 'Druk materiałów'
                }
            },


            ranks: {
                supporters: {
                    part1: 'Akcję<br/>' +
                        '{USERS, plural,' +
                        ' one {poparła}' +
                        ' few {poparły}' +
                        ' many {poparło}' +
                        ' other {poparły}} już',
                    part2:
                        '{USERS, plural,' +
                        ' one {osoba}' +
                        ' few {osoby}' +
                        ' many {osób}' +
                        ' other {osoby}}'
                },
                pointsTotal: {
                    part1: '{USERS, plural,' +
                        ' one {Zebrała}' +
                        ' few {Zebrały}' +
                        ' many {Zebrało}' +
                        ' other {Zebrały}}' +
                        '<br/>' +
                        'do tej pory',
                    part2: '{POINTS, plural,' +
                        ' one {punkt}' +
                        ' few {punkty}' +
                        ' many {punktów}' +
                        ' other {punkty}}'
                }
            },

            tu: {
                type: {
                    'country': 'kraj',
                    'region': 'województwo',
                    'district': 'powiat',
                    'commune': 'gmina',

                    'country_plural': 'kraje',
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

            // Menu
            menu: {
                login: 'Zaloguj',
                support_action: 'Dołącz',
                ranks: 'Rankingi',
                coordinators: 'Koordynatorzy',
                about: 'O akcji',
                sponsors: 'Sponsorzy',
                contact: 'Kontakt',
                faq: 'FAQ',
                challenges: 'Wesprzyj',
                why_jow: 'Dlaczego JOW?',
                shop: 'Sklep',
                referendum: 'Referendum'
            },

            points: {
                comment: {
                    'and more': 'i więcej',
                    'for each 1pln': 'za każdy 1 zł',
                    'for each 1pln payed for materials': 'za każdy 1 zł wydany na wydrukowanie materiałów',
                    'for each referral': 'za każdą osobę, która dołączy z Twojego polecenia (linku)'
                },
                type: {
                    user: {
                        support: 'poparcie Ruchu JOW',
                        referral: 'zaproszenie użytkownika'
                    },
                    'organise.event': 'zorganizowanie wydarzenia',
                    'organise.referendumPoint': 'zorganizowanie punktu referendalnego',
                    'distribute.leaflets': 'dystrybucja ulotek',
                    donation: 'wpłata',
                    'print.materials': 'druk materiałów',
                    other: 'inne'
                }
            }

        });
    }]);