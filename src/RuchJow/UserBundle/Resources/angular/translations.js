/**
 * Created by grest on 7/22/14.
 */

angular.module('ruchJow.user.translations', ['pascalprecht.translate'])
    .config(['$translateProvider', function ($translateProvider) {
        $translateProvider.translations('pl', {

            user: {
                confirmation: {
                    msg: {
                        confirmed: 'Adres email został potwierdzony. Dziękujemy za Twoje poparcie.',
                        token_not_exists: 'Link weryfikacyjny jest niepoprawny',
                        internal_error: 'Wystąpił wewnętrzny błąd po stronie serwera. Jeśli błąd się powtórzy prosimy o kontakt z administratorami',
                        pending: 'Trwa sprawdzanie linku weryfikacyjnego'
                    }
                },
                referralInfo: {
                    title: 'Dziękujemy za zainteresowanie naszą akcją',
                    msg: 'Jeśli zdecydujesz się ją poprzeć, osoba, która poleciła Ci naszą stronę, otrzyma dodatkowe punkty'
                },
                preSignedRegister: {
                    pending: 'Trwa pobieranie danych do rejestracji',
                    token_not_found: 'Niepoprawny link. Mimo to zapraszamy do poparcia efektu JOW.',
                    email_taken: 'Użytkownik z adresem email, który mamy zapisany w bazie dla tego linku, już poparł efekt JOW',
                    internal_error: 'Wystąpił wewnętrzny błąd. Mimo to zapraszamy do poparcia efektu JOW.'
                }
            }
        });
    }]);