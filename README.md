# Kontaktverfolgung in Stud.IP

## Was macht dieses Plugin?
In Zeiten einer Pandemie ist es nötig, in Präsenzveranstaltungen zu verfolgen, wer anwesend war und mit anderen
Personen Kontakt hatte. Dieses Plugin bietet dafür eine einfache Möglichkeit.

## Wie funktioniert die Kontaktverfolgung?
Zu jedem Termin einer Veranstaltung wird automatisch ein QR-Code bereitgestellt. Dieser ist zu den Zeiten des Termins
und eine einstellbare Zeit davor und danach verfügbar und führt beim Scannen auf eine Stud.IP-Seite, die die aufrufende
Person als "anwesend" bei diesem Termin registriert.

Sollte es beim Scannen des Codes zu Problemen kommen, so kann die Präsenz auch manuell erfasst werden.

Lehrende sehen während eines Termins, wie viele Personen schon registriert sind und können daher steuernd eingreifen und
z.B. auf die Notwendigkeit des Registrierens hinweisen.

Für entsprechende Rechtestufen gibt es darüber hinaus eine Suchfunktion, die alle Personen findet, die zu einer
gegebenen Person innerhalb eines bestimmten Zeitfensters Kontakt in Präsenzveranstaltungen hatte. Das Suchergebnis ist
exportierbar und kann damit auch z.B. Gesundheitsämtern zur Verfügung gestellt werden.

## Wie wird das Plugin installiert?
Die Installation kann ganz normal über die Stud.IP-Oberfläche oder über das Clonen dieses Git-Repositories erfolgen
(Cloneverzeichnis muss `<studip>/public/plugins_packages/upa/ContactTracer` heißen).

## Konfigurationsoptionen
Das Plugin legt drei Einträge in der globalen Stud.IP-Konfiguration im Abschnitt "contact_tracer" an:
- CONTACT_TRACER_DAYS_BEFORE_AUTO_DELETION: Anzahl der Tage, bevor Einträge automatisch gelöscht werden.
Standard ist 28, also vier Wochen.
- CONTACT_TRACER_TIME_OFFSET_BEFORE: Wie viele Minuten vor Beginn eines Termins ist der zugehörige QR-Code verfügbar?
Standard ist 30.
- CONTACT_TRACER_TIME_OFFSET_AFTER: Wie viele Minuten nach Ende eines Termins ist der zugehörige QR-Code verfügbar?
Standard ist 0, der QR-Code wird also zum Ende eines Termins abgeschaltet.
