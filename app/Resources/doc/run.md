# Uruchomienie aplikacji

## Klonowanie
`git clone https://github.com/ruchjowpl/ruchjowpl.git`

## Pobieranie niezbędnych bibliotek

`composer install`

Po zainstalowaniu bibliotek zostaniesz poproszony u ustawienie wymaganych parametrów do konfiguracji
Szczególnie trzeba zwrócić uwagę na następujące parametry:

* `router.request_context.host` host pod jakim chcesz uruchomić aplikację lokalnie np. `ruchjow.dev`
* `router.request_context.scheme` ustaw `http`
* `router.request_context.base_url` ustaw jeżeli uruchamiasz aplikacje w jakieś konkretnej ścieżce np. 'ruchjow' gdy aplikacja działa pod adresem 'dev/ruchjow`

więcej informacji [tutaj](http://symfony.com/doc/current/cookbook/console/sending_emails.html#configuring-the-request-context-globally)

* parametry `database_` - definiujesz połączenie do bazy danych
* parametry `app_dev_security_` - konfiguracje zabezpieczeń trybu dev. Jeżeli uruchamiasz aplikacje np. na vagrancie to najlepiej wyłącz zabezpieczenia `app_dev_security_disable: false`
* `node: /usr/bin/nodejs` zmień ścieżkę jeżeli jest inna

## Budowanie aplikacji

Zamiast uruchamiania szeregu komend zalecamy użycie gotowego skryptu [phing](https://www.phing.info/)

`bin/phing`

Skrypt jest interaktywny i przeprowadzi cię przez proces budowania aplikacji.

Podczas pracy z projektem można łatwo używać konkretnych targetów do przebudowania/zaktualizowania składowych aplikacji

`bin/phing db` - przebudowanie bazy danych

`bin/phing assets` - przebudowanie assetsów

konfiguracja dla skryptów phing mieści sie w pliku `parameters.dist`. Jeżeli chcesz zmienić niektóre parametry to stwrzórz swój plik `properties` i ustaw w nim tylko te parametry które chcesz zmienić
 
 np.: `alwaysRecreateDatabase=true` - (domyślnie jest false) spowoduje że baza będzie zawsze przebudowywana
