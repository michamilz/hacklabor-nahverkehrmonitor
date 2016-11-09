# Abfahrtsmonitor für das Hacklabor

Das [Hacklabor](https://hacklabor.de) ist ein Hackspace im [Schweriner Technologiezentrum](http://tgz-mv.de). Dieses Skript soll die nächsten Abfahrten des [ÖPNV](http://nahverkehr-schwerin.de) an den nächstgelegenen Haltestellen liefern.

## Datenbasis

Die Daten werden von dem API der [Verkehrsgesellschaft Mecklenburg-Vorpommern mbH Schwerin](http://www.vmv-mbh.de/) angefragt. Die Funktionsweise wurde auf Basis der [iOS App](https://itunes.apple.com/de/app/mv-fahrt-gut/id1022785965) analysiert. Dort ist die gewünschte Funktion implementiert.

## Funktionsweise des API

In der App werden die folgenden drei Schritte durchgeführt.

- Ortsinformationen wie Straßenname und Hausnummer anhand der Geokoordinaten abfragen
- Haltestellen in der Nähe abfragen
- Abfahrten zu den Haltestellen abfragen

### Ortsinfos zum aktuellen Standort

Für unseren Zweck erstmal nicht relevant.

### Nächstgelegene Haltestellen

Basierend auf den Geokoordinaten des Standortes werden die nächstgelegenen Haltestellen abgefragt.

**Request**

```
http://80.146.180.107/companion-vmv/XML_COORD_REQUEST?coord=3660138.493147123:216174.5266245753:NAV3:&coordListOutputFormat=STRING&coordOutputFormat=NAV3&inclFilter=1&type_1=STOP&stateless=1&max=5&radius_1=1000
```

Die dichtesten Haltestellen für Bus und Straßenbahn sind folgende:

- 44402071: Technologiezentrum
- 44402070: Rosenstraße
- 44402209: Blumenbrink
- 44402035: Gartenstadt

Die Gartenstadt taucht in der Liste nicht mit auf. Da dort aber Straßenbahnen verkehren, die nicht im/am Blumenbrink halten, wird sie ebenfalls abgefragt.

Diese IDs sind der Parameter für den Wert name_dm in der nächsten Abfrage.

### Abfahrten an Haltestellen

Je Haltestelle wird die folgende URL aufgerufen.

**Request**

```
http://80.146.180.107/companion-vmv/XML_DM_REQUEST?name_dm=<id>&type_dm=any&trITMOTvalue100=10&changeSpeed=normal&exclMOT_0=1&exclMOT_1=1&exclMOT_2=1&mergeDep=1&coordOutputFormat=NAV3&coordListOutputFormat=STRING&useAllStops=1&excludedMeans=checkbox&useRealtime=1&deleteAssignedStops=1&itOptionsActive=1&canChangeMOT=0&mode=direct&ptOptionsActive=1&limit=10&imparedOptionsActive=1&locationServerActive=1&depType=stopEvents&useProxFootSearch=0&maxTimeLoop=2&includeCompleteStopSeq=1
```

Der Wert für den Knoten tk in der Antwort scheint eine Art ID für die Fahrt zu sein.

## Funktionsweise des Skriptes

Dieses Skript dient als Proxy um CORS Probleme zu vermeiden. Die gewonnenen Daten werden zusammengefasst und nach Zeiten sortiert als JSON ausgegeben.

## ToDo

* Caching der Ergebnisse
