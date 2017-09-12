# Installation und Konfiguration

## Systemvoraussetzungen
Die Systemvoraussetzungen orientieren sich im Wesentlichen an [jenen von Laravel](https://laravel.com/docs/5.5/installation#server-requirements).
* PHP >=7.0.0 inklusive den folgenden Extensions:
    * OpenSSL
    * PDO
    * Mbstring
    * Tokenizer
    * XML
    * Intl
* MySQL (getestet: 5.5, 5.6) oder PostgreSQL (getestet: 9.6)
* Composer (zur Installation, nicht zwingend am Server selbst)
* Am Entwicklungssystem:
    * Node.js (>=6.0.0)
    * npm

## Installation für Entwicklungssystem
* Repository klonen: `git clone https://github.com/gymdb/flexzeit.git`
* PHP-Dependencies installieren: `composer install`
* Korrekte Einstellungen in der Datei `.env` eintragen (siehe eigener Abschnitt)
* Datenbankstruktur erstellen: `php artisan migrate` (siehe auch [Laravel Migrations](https://laravel.com/docs/5.5/migrations#running-migrations))
* Testdaten generieren (optional): `php artisan db:seed` (siehe auch [Laravel Seeding](https://laravel.com/docs/5.5/seeding#running-seeders))

## Installation für Produktivsystem
* Repository klonen: `git clone https://github.com/gymdb/flexzeit.git`
    * Alternativ: [Archiv herunterladen](https://github.com/gymdb/flexzeit/archive/master.zip)
* PHP-Dependencies installieren: `composer install --no-dev`
* Korrekte Einstellungen in der Datei `.env` eintragen (siehe eigener Abschnitt)
* Datenbankstruktur erstellen mittels Skript `database/sql/create.sql`
    * Alternativ ist auch eine Installation wie für das Entwicklungssystem möglich, dafür muss bei der Installation der Dependencies die Option `--no-dev` weggelassen werden.
* Environment-Konfiguration und Routen cachen:
    * `php artisan config:cache`
    * `php artisan route:cache`

## Environment-Konfiguration
Die Konfigurationsoptionen werden in der Datei `.env` als Key-Value-Paare angegeben. Werte mit Leerzeichen müssen in `"` eingeschlossen werden.
Eine Beispieldatei befindet sich unter `.env.example`.

Key               | Typ                       | Beschreibung
------------------|---------------------------|---
APP_NAME          | string                    | Anzeigename des Systems
APP_ENV           | `local`&#124;`production` |
APP_KEY           | `base64:abc`              | Verschlüsselungskey für diverse Framework-Funktionen (automatisch generiert)
APP_DEBUG         | boolean                   | 
APP_LOG_LEVEL     | [Log Level](https://laravel.com/docs/5.5/errors#log-severity-levels) |
APP_URL           | URL                       | Basis-URL des Systems
APP_TIMEZONE      | [Zeitzone](https://php.net/manual/de/timezones.php) |
APP_LOCALE        | `de`&#124;`en`            | Sprache des Systems
DB_CONNECTION     | `mysql`&#124;`pgsql`      | Typ des Datenbankservers
DB_HOST           | IP oder Domain            | Host des Datenbankservers
DB_PORT           | Port                      | Port des Datenbankservers (Default: MySQL 3306, PostgreSQL 5432)
DB_DATABASE       | string                    | Name der Datenbank
DB_USERNAME       | string                    | Datenbanknutzer
DB_PASSWORD       | string                    | Datenbankpasswort
BROADCAST_DRIVER  |                           | derzeit nicht verwendet
CACHE_DRIVER      | `file`&#124;`array`       | Art des Cachings (`file`: persistent, `array`: nur für Dauer des Requests)
SESSION_DRIVER    | [Session Driver](https://laravel.com/docs/5.5/session#introduction) | Default: `file`
QUEUE_DRIVER      | `database`&#124;`sync`    | Art der Warteschlange für asynchrone Jobs (siehe unten)
MAIL_DRIVER       | `smtp`&#124;`sendmail`&#124;`log` | Driver für den Mail-Versand (`log`: Kein Versand, nur Ausgabe in Log-File)
MAIL_HOST         | IP oder Domain            | Host des SMTP-Servers
MAIL_USERNAME     | string                    | Username am SMTP-Server
MAIL_PASSWORD     | string                    | Passwort am SMTP-Server
MAIL_FROM_ADDRESS | E-Mail-Adresse            | Absenderadresse für ausgehende E-Mails
MAIL_FROM_NAME    | string                    | Absendername für ausgehende E-Mails
UNTIS_DOMAIN      | URL                       | URL der WebUntis-JSON-Schnittstelle (inklusive Schulname im Query-String)
UNTIS_USERNAME    | string                    | Username für Zugriff auf WebUntis
UNTIS_PASSWORD    | string                    | Passwort für Zugriff auf WebUntis

## System-Konfiguration
Die Systemkonfiguration erfolgt in der Datenbank-Tabelle `config` als Key-Value-Paare. Werte sind dabei als JSON anzulegen.
Konfigurationswerte werden beim ersten Zugriff gecacht. Nach Änderungen muss daher der Cache durch Aufruf von `php artisan cache:clear` oder Löschen des Verzeichnisses `storage/framework/cache/data` geleert werden.

Folgende Werte müssen eingetragen werden:

Key                     | Typ                   | Beschreibung
------------------------|-----------------------|--------------
lessons                 | Object                | Konfiguration der Zeiten der einzelnen Einheiten
course.create.day       | Relatives Datum       | Letzter Tag, an dem Kurse erstellt werden können
course.create.week      | Relatives Datum       |
registration.begin.day  | Relatives Datum       | Erster Tag, an dem man sich für Einheiten registrieren kann
registration.begin.week | Relatives Datum       |
registration.end.day    | Relatives Datum       | Letzter Tag, an dem man sich für Einheiten registrieren kann
registration.end.week   | Relatives Datum       |
documentation.day       | Relatives Datum       | Letzter Tag, an dem Dokumentation über Einheiten verfasst werden kann
documentation.week      | Relatives Datum       |
year.min                | integer (`1`)         | Kleinster Wert für Jahrgang
year.max                | integer (`8`)         | Größter Wert für Jahrgang
year.start              | string (`YYYY-MM-DD`) | Erster Tag des Schuljahres mit Flexzeit
year.end                | string (`YYYY-MM-DD`) | Letzter Tag des Schuljahres mit Flexzeit
notification.recipients | string[]              | Liste von Empfänger-Mailadressen für den Benachrichtigungsversand 

### Spezifikation der Einheiten
Pro Wochentag (`0` Sonntag bis `6` Samstag) und Einheit (beginnend bei `1`) gibt es ein Objekt der Form

```json
{"start": "HH:MM", "end": "HH:MM"}
```

Der Konfigurationseintrag muss also folgendermaßen aussehen:

```json
{
  "1": {
    "1": {"start": "09:00", "end": "09:30"},
    "2": {"start": "10:00", "end": "10:30"}
  },
  ...
  "5": {
    "1": {"start": "09:30", "end": "10:00"}
  }
}
```
Dieses Beispiel würde für Montag zwei Einheiten (9:00-9:30 und 10:00-10:30) und für Freitag eine (9:30-10:00) konfigurieren.


### Relative Datumsangabe
Ein relatives Datum besteht immer aus zwei zusammengehörigen Angaben `week` und `day` und kann auf zwei Arten spezifiziert werden:
* Feste Anzahl `n` Tage vor dem Termin: `week=0` und `day=n`
* Fester Wochentag `d`:
    * `day=d` (wobei `0` Sonntag bis `6` Samstag)
    * `week>=1` spezifiziert, wie viele Wochen im Vorhinein (`1` ist die Woche direkt vor dem Termin)
    * Wochen beginnen mit Montag, der Sonntag ist also der letzte Tag der Vorwoche

Für das Verfassen der Dokumentation gilt analog *nach dem Termin* bzw. *Folgewoche*.

**Beispiele:**
* `course.create.week=0`, `course.create.day=3`: Kurse können bis 3 Tage vorher erstellt werden, also
    * am 18.9. können Kurse für 21.9. und später erstellt werden, bzw.
    * Kurse für 27.9. können bis spätestens 24.9. erstellt werden.
* `course.create.week=1`, `course.create.day=3`: Kurse können immer bis Mittwoch der Vorwoche erstellt werden, also
    * am 13.9.2017 (Mittwoch) können Kurse für 18.9.2017 (Montag) und später erstellt werden,
    * am 14.9.2017 (Donnerstag) können Kurse für 25.9.2017 (Montag) und später erstellt werden.
* `registration.begin.day=4`, `registration.begin.week=1`, `registration.end.day=2`, `registration.end.week=0`: Die Anmeldung beginnt am Donnerstag und ist bis zwei Tage vor der Einheit möglich, also
    * am 14.9.2017 (Donnerstag) kann man sich für Einheiten ab dem 18.9.2017 (Montag) bis (theoretisch, weil Sonntag) 24.9. anmelden,
    * am 17.9.2017 (Sonntag) kann man sich nur noch für Einheiten ab dem 19.9.2017 anmelden.

## Initiale Daten

Die folgenden Daten müssen in der Datenbank angelegt werden:

### LehrerInnen
Tabelle: `teachers`
* `id`: Primary Key (autoincremented)
* `lastname`, `firstname`
* `username`: Unique über gesamtes System (auch SchülerInnen)
* `password`: Passwort-Hash, wie von [`password_verify`](https://php.net/manual/de/function.password-verify.php) akzeptiert
* `admin`: Boolean
* `info`: Info im Popover in der Übersicht für SchülerInnen (optional, Freitext)
* `image`: Absolute URL zu externem Bild oder relativer Pfad beginnend mit `storage/` (optional)

### SchülerInnen
Tabelle: `students`
* `id`: Primary Key (autoincremented)
* `lastname`, `firstname`
* `username`: Unique über gesamtes System (auch LehrerInnen)
* `password`: Passwort-Hash, wie von [`password_verify`](https://php.net/manual/de/function.password-verify.php) akzeptiert
* `image`: Absolute URL zu externem Bild oder relativer Pfad beginnend mit `storage/` (optional)
* `untis_id`: ID der Schülerin/des Schülers in WebUntis

### Unterrichtsfächer
Tabelle: `subjects`
* `id`: Primary Key (autoincremented)
* `name`: Bezeichnung des Fachs (Freitext)

### Räume
Tabelle: `rooms`
* `id`: Primary Key (autoincremented)
* `name`: Bezeichnung des Raums (Freitext)
* `type`: Typ des Raums, filterbar in der Liste für SchülerInnen (optional, Freitext)
* `capacity`: Maximale Anzahl SchülerInnen, die sich für Einheiten in diesem Raum anmelden können
* `yearfrom`, `yearto`: Minimaler/Maximaler Jahrgang, aus dem sich SchülerInnen für Einheiten in diesem Raum anmelden können (optional)

### Klassen und Gruppen
Für Klassen und Teilungsgruppen gleichermaßen ist jeweils ein Eintrag in der Tabelle `groups` anzulegen:
* `id`: Primary Key (autoincremented)
* `name`: Bezeichnung der Klasse/Gruppe (Freitext)

Für Klassen ist zusätzlich ein Eintrag in der Tabelle `forms` anzulegen:
* `group_id`: Foreign Key auf `groups`, gleichzeitig Primary Key
* `year`: Jahrgang der Klasse
* `kv_id`: KV, Foreign Key auf `teachers`

### Stundenplan der Klassen
Für jede Klasse ist der Stundenplan in der Tabelle `timetable` anzlegen. Pro Klasse/Wochentag/Einheit gibt es einen Eintrag (also ~10 pro Klasse):
* `form_id`: Foreign Key auf `forms.group_id`
* `day`: Wochentag (`0` Sonntag bis `6` Samstag)
* `number`: Nummer der Flex-Einheit

Jede hier existierende Kombination `day`/`number` muss auch im Config-Eintrag `lessons` vorhanden sein.

### Einheiten der LehrerInnen
Für jede einzelne gehaltene Einheit pro LehrerIn ist ein Eintrag in der Tabelle `lessons` anzulegen (also pro LehrerIn in der Größenordnung von 100 Einträgen): 
* `id`: Primary Key (autoincremented)
* `date`: Datum der Einheit
* `number`: Nummber der Flex-Einheit
* `room_id`: Foreign Key zu `rooms`
* `teacher_id`: Foreign Key zu `teachers`

Für `cancelled` und `course_id` sollten die Default-Werte beibehalten werden.

### M:N-Relationships
Die folgenden Relationships sind für korrekte Funktion des Systems erforderlich:
* `group_student`: Zuordnung der SchülerInnen zu Klassen und Teilungsgruppen
* `group_teacher`: Zuordnung der LehrerInnen zu Klassen und Teilungsgruppen
    * LehrerInnen, die nur eine Teilungsgruppe unterrichten, nicht jedoch die gesamte Klasse, sollten nur der Teilungsgruppe zugeordnet werden
    * LehrerInnen, die sowohl die gesamte Klasse als auch eine Teilungsgruppe unterrichten, sollten beiden zugeordnet werden
* `subjects_teacher`: Zuordnung der Fächer zu LehrerInnen

## Datei-Storage
Öffentlich abrufbare Dateien (für Bilder von LehrerInnen und SchülerInnen) sollten im Verzeichnis `storage/app/public/` bzw. Unterverzeichnissen angelegt werden. Dieses Verzeichnis ist über den Pfad `storage/` (relativ zur Base-URL) am Webserver aufrufbar.

Dafür muss der Symlink `public/storage/`&#8594;`storage/app/public/` notwendig, der im Repository bereits vorhanden ist. Sollte der Symlink fehlen, kann er auch mit `php artisan storage:link` erstellt werden.

## Betrieb

### Asynchroner Mailversand
Für bessere Reaktionszeiten sollte die Benachrichtigung beim Erstellen einer Klassen-/Gruppenbindung asynchron (über eine Queue im Hintergrund) verschickt werden. Dafür muss in der Environment-Konfiguration `QUEUE_DRIVER=database` gesetzt werden.

Die Abarbeitung der Mails erfolgt über den Start des Daemons `php artisan queue:work`, der dann im Hintergrund aktiv bleiben muss.

Für synchronen Mailversand ist `QUEUE_DRIVER=sync` zu setzen.

Weitere Details siehe in der [Laravel-Dokumentation zu Queues](https://laravel.com/docs/5.5/queues#running-the-queue-worker).

### Geplante Aufgaben
Das Laden der Daten aus WebUntis kann automatisiert durch Laravel zu den richtigen Zeitpunkten durchgeführt werden (basierend auf den im Config-Eintrag `lessons` spezifizierten Uhrzeiten der Einheiten).
Dazu muss minütlich `php artisan schedule:run` aufgerufen werden (das System bestimmt dann selbst, ob zum jeweiligen Zeitpunkt Aktionen durchgeführt werden sollen). Das kann durch folgenden Cron-Entry erfolgen:

```bash
* * * * * php /absolute_path/artisan schedule:run >> /dev/null 2>&1
```

Alternativ können die Einzelbefehle auch direkt per Cronjob aufgerufen werden:
* `php artisan untis:absences [YYYY-MM-DD]` für das Laden der Abwesenheiten des aktuellen bzw. spezifierten Tages
* `php artisan untis:offdays` für das Laden der schulfreien Tage

Weitere Informationen zu den geplanten Aufgaben siehe in der [Laravel-Dokumentation Task Scheduling](https://laravel.com/docs/5.5/scheduling).
