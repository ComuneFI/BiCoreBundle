BiCoreBundle
=============
[![Build Status](https://travis-ci.org/ComuneFI/BiCoreBundle.svg?branch=master)](https://travis-ci.org/ComuneFI/BiCoreBundle)
[![Coverage Status](https://coveralls.io/repos/github/ComuneFI/BiCoreBundle/badge.svg?branch=master)](https://coveralls.io/github/ComuneFI/BiCoreBundle?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/ComuneFI/BiCoreBundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/ComuneFI/BiCoreBundle/?branch=master)

> ⚠️ **WORK IN PROGRESS** ⚠️

# Intro:
-------------
BiCoreBundle è un bundle per symfony (3.4 o superiori) che poggia su framework Open Source (JQuery e Bootstrap e <a href="https://github.com/italia/bootstrap-italia" target="_blank">Bootstrap Italia</a>), costruito in modo da essere un accelleratore di produttività riusabile e ridistribuibile.
I prodotti creati con BiCoreBundle sono facilmente manutenibili, la documentazione dei componenti è ampiamente diffusa on line.
Le funzioni che servono ripetitivamente (p.e. login, creazione di interfacce per le tabelle, etc.) sono nativamente disponibili in tutti i programmi creati con questo prodotto.
Utilizzando doctrine si può generare velocemente una base dati su Mysql, Postgresql o sqlite (utilizzato per i test)
BiCoreBundle è in grado di convertire uno schema database creato tramite Mysqlworkbench in entity class gestite da symfony tramite doctrine (indipendentemete dal tipo di database scelto).
BiCoreBundle è inoltre dotato di un proprio pannello di amministrazione che permette velocemente di pubblicare aggiornamenti (tramite Git/Svn), di creare nuovi form per la procedura che si intende sviluppare, aggiornare lo schema database partendo dal file generato tramite Mysqlworkbench, pulizia della cache, e lancio di comandi shell (con le limitazione dell'utente con cui è in esecuzione il servizio web) tutto tramite pochi click.

# Obiettivi, destinatari e contesto:
-------------
I software sviluppati internamente al Comune di Firenze sono fruiti da due tipi di soggetti: da una parte i colleghi del Comune di Firenze hanno bisogno di accedere a una interfaccia che sia coerente, di semplice utilizzo e pratica.
Dall’altra parte i cittadini hanno la necessità di accedere ai servizi che il Comune mette a disposizione in modo semplice e intuitivo.
Per esempio, il software di gestione del Patrimonio Immobiliare è composto da molti moduli, sia rivolti a chi si occupa di gestire il patrimonio internamente, sia ai colleghi che si occupano di gestire i Bandi, sia ai cittadini che possono immettere la domanda direttamente attraverso una semplice interfaccia fruibile anche da tablet e smartphone.

# Installazione:
-------------

- Prendere il <a href="https://github.com/ComuneFI/BiCoreTemplate" target="_blank">template</a> già pronto per essere utilizzato.

# Test

```
    #Assets install
    bin/console assets:install --symlink --relative tests/public

    #Esecuzione dei test
    ant

```
## code check

```
    vendor/bin/phpmd src text tools/phpmd/ruleset.xml
    vendor/bin/phpcs --standard=tools/phpcs/ruleset.xml --extensions=php src

    vendor/bin/phpcpd src

    #php-cs-fixer
    #php /usr/local/bin/php-cs-fixer fix src/ --rules=@Symfony

    #phpcbf fix
    #vendor/bin/phpcbf --extensions=php --standard=PSR2 src/
```
