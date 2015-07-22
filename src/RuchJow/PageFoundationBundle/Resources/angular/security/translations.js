/**
 * Created by grest on 7/22/14.
 */

angular.module('ruchJow.security.translations', ['pascalprecht.translate'])
    .config(['$translateProvider', function ($translateProvider) {
        $translateProvider.addInterpolation('$translateMessageFormatInterpolation');
        $translateProvider.translations('pl', {

            'security.$alert': {
                new_password_set: 'Nowe hasło zostało ustawione',
                reset_link_sent: 'Link do ustawienia hasła został wysłany',
                user_registered_title: 'Dziękujemy. Na podany przez Ciebie adres e-mail został wysłany link, potrzebny do potwierdzenia Twojego poparcia.',
                user_registered_msg: 'Jeśli po godzinie nic nie otrzymasz, sprawdź czy mail nie wylądował w spamie lub w innych folderach. Jeśli mail nie dojdzie, napisz do nas na adres kontakt@ruchjow.pl',
                unsuccess: 'Błędny użytkownik lub hasło',
                account_remove_link_send: 'Na Twój adres e-mail został wysłany e-mail powtierdzający usunięcie konta.'
            },

            'security.login': {
                nick: 'E-mail lub nick',
                password: 'Hasło',
                rememberMe: 'Zapamiętaj mnie',
                resetPassword: 'Ustaw lub zresetuj hasło',
                registerLink: 'Nie masz jeszcze konta? Poprzyj zmiany'
            },

            'loginForm.login.required': 'Nick nie może być pusty.',
            'loginForm.password.required': 'Hasło nie może być puste.',

            'security.forgotPasswordForm': {
                nick: 'Nick',
                email: 'E-mail'
            },

            forgotPasswordForm: {
                nick: {
                    'required.error': 'Nick nie może być pusty'
                },
                email: {
                    'pattern.error': 'Niepoprawny format adresu e-mail',
                    'required.error': 'E-mail nie może być pusty'
                },
                error: {
                    user_not_found: 'Użytkownik z takim adresem e-mail nie został odnaleziony'
                }
            },

            'security.newPassword': {
                title: 'Resetowanie lub ustawienie pierwszego hasła',
                'token.verify': {
                    pending: 'Zaczekej, trwa sprawdzanie poprawności linku weryfikacyjnego',
                    fail: 'Link niepoprawny'
                },
                password: 'Hasło',
                passwordRepeat: 'Powtórz hasło',
                submit: 'Ustaw'
            },
            newPasswordForm: {
                password:{
                    'required.error': 'Hasło nie może być puste'
                },
                passwordRepeat:{
                    'ruchJowEquals.error': 'Hasło i powtórzone hasło muszą być identyczne'
                }
            },

            'security.remove': {
                title: 'Czy na pewno chcesz usunąć swoje konto?',
                message: 'Twoje dane oraz punkty zostaną trwale usunięte z serwisu ruchjow.pl.',
                btn: {
                    ok: 'OK',
                    cancel: 'Anuluj'
                }
            },

            'security.register.title': 'Dołącz do Ruchu JOW',
            'security.register.byReferral': 'z polecenia',
            'security.register.subtitle': '{NUMBER, plural,' +
                ' one {Dołączyła}' +
                ' few {Dołączyły}' +
                ' many {Dołączyło}' +
                ' other {Dołączyło}}' +
                ' do nas już {FORMATTED_NUMBER} ' +
                '{NUMBER, plural,' +
                ' one {osoba}' +
                ' few {osoby}' +
                ' many {osób}' +
                ' other {osoby}}',

            'security.register.nick': 'Nick',
            'security.register.firstName': 'Imię',
            'security.register.lastName': 'Nazwisko',
            'security.register.email': 'E-mail',
            'security.register.phone': 'Telefon',
            'security.register.commune': 'Gmina',
            'security.register.isRegulationsAccepted': 'Regulamin',

            'security.register.organisationUrl': 'Adres strony (http://www.abc.pl)',
            'security.register.organisationName': 'Nazwa (np. Fundacja ABC)',
            'security.register.password': 'Hasło',
            'security.register.passwordRepeat': 'Powtórz hasło',

            'security.register.postcode.or.commune.name': 'Kod pocztowy lub nazwa gminy',
            'security.register.organisation.block.info': 'Chcę promować swoją organizację, ulubionego bloga, serwis internetowy czy fanpage',
            'security.register.password.block.info': 'Ustaw hasło (zawsze będziesz mógł(a) zrobić to później)',

            'security.register.submit': 'DOŁĄCZAM',

            // Login
            'registerForm.nick.pattern.error': 'Min. 4 litery lub cyfry. Dodatkowo można używać spacji oraz znaków ".", "-", "_". Znaki specjalne nie mogą występować koło siebie.',
            'registerForm.nick.required.error': 'Nick nie może być pusty',
            'registerForm.nick.unique.error': 'Użytkownik z takim nickiem wyraził już swoje poparcie',

            // First Name
            'registerForm.firstName.pattern.error': 'Imię musi zaczynać się od wielkiej litery i składać się z co najmniej dwóch znaków.',
            'registerForm.firstName.required.error': 'Imię nie może być puste',

            // Last Name
            'registerForm.lastName.pattern.error': 'Nazwisko musi zaczynać się od wielkiej litery i składać się z co najmniej dwóch znaków. Jeśli nazwisko składa się z dwóch członów, należy je rozdzielić znakiem "-".',
            'registerForm.lastName.required.error': 'Nazwisko nie może być puste',

            // Commune
            'registerForm.commune.commune': 'Pole gmina nie musi być wypełnione, tylko jeśli zostaną uzupełnione dane promowanej organizacji, bloga czy fanpage\'a',


            // Organisation
            registerForm: {
                organisationUrl: {
                    'pattern.error': 'Niepoprawny formar adresu www'
                },
                organisationName: {
                    'pattern.error': 'Niepoprawny format nazwy',
                    'required.error': 'Nazwa nie może być pusta'
                },

                // Password
                password: {
                    'pattern.error': 'Hasło musi zawierać wielką literę i cyfrę oraz musi mieć długość od 5 do 20 znaków'
                },
                passwordRepeat: {
                    'ruchJowEquals.error': 'Hasła nie są identyczne'
                },

                // Email
                email: {
                    'pattern.error': 'Niepoprawny format adresu e-mail',
                    'required.error': 'Email nie może być pusty',
                    'unique.error': 'Użytkownik z takim adresem email wyraził już swoje poparcie'
                },

                // Phone
                phone: {
                    'pattern.error': 'Numer telfonu powinien być w formacie 98 7654321 lub 987 654 321. Podany numer może być poprzedzony numerem kierunkowym kraju np. +48.',
                    'required.error': 'Numer telefonu nie może być pusty'
                },

                // Address: city
                address: {
                    city: {
                        'pattern.error': 'Nazwa miejscowości może się składać z co najmniej dwóch znaków. W nazwie miejscowości dozwolone są jedynie litery, odstęp (spacja) i znak myślnika ("-")',
                        'required.error': 'Numer domu nie może być pusty'
                    }
                },

                isRegulationsAccepted: {
                    'required.error': 'Aby się zarejestrować musisz zaakceptować regulamin'
                }
            }
        });
    }]);