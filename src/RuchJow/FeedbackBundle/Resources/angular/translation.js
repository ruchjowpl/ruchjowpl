/**
 * Created by grest on 7/22/14.
 */

angular.module('ruchJow.feedback.translation', ['pascalprecht.translate'])
    .config(['$translateProvider', function ($translateProvider) {
        $translateProvider.translations('pl', {

            calendar:{
                link:'kalendarz'
            },
            feedback: {
                form: {
                    title: 'Zgłoś błąd',
                    nick: 'Nick',
                    subject: 'Temat',
                    description: 'Szczegółowy opis',
                    contact: 'E-mail lub telefon (nie wymagane)',
                    submit: 'Wyślij'
                },

                feedbackSent: {
                    msg: 'Zgłoszenie zostało wysłane'
                },

                link: 'błędy i sugestie'
            }
        });
    }]);