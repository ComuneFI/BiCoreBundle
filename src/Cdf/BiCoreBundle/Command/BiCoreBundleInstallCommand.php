<?php

namespace Cdf\BiCoreBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\ArrayInput;
use FOS\UserBundle\Util\UserManipulator;

class BiCoreBundleInstallCommand extends Command
{
    protected static $defaultName = 'bicorebundle:install';

    protected $fixtureFile;
    private $usermanipulator;

    protected function configure()
    {
        $this
                ->setDescription('Installazione ambiente bi')
                ->setHelp('Crea il database, un utente amministratore e i dati di default')
                ->addArgument('admin', InputArgument::REQUIRED, 'Username per amministratore')
                ->addArgument('adminpass', InputArgument::REQUIRED, 'Password per amministratore')
                ->addArgument('adminemail', InputArgument::REQUIRED, 'Email per amministratore')
        ;
    }

    public function __construct(UserManipulator $usermanipulator)
    {
        $this->usermanipulator = $usermanipulator;

        // you *must* call the parent constructor
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $admin = $input->getArgument('admin');
        $adminpass = $input->getArgument('adminpass');
        $adminemail = $input->getArgument('adminemail');
        $this->fixtureFile = sys_get_temp_dir().DIRECTORY_SEPARATOR.'fixtures.yml';

        if (!$admin) {
            echo "Inserire il nome utente dell'amministratore";

            return 1;
        }
        if (!$adminpass) {
            echo "Inserire la password per dell'amministratore";

            return 1;
        }
        if (!$adminemail) {
            echo "Inserire la mail dell'amministratore";

            return 1;
        }

        $commanddb = $this->getApplication()->find('bicorebundle:createdatabase');
        $argumentsdb = array('command' => 'bicorebundle:createdatabase');
        $inputc = new ArrayInput($argumentsdb);
        $commanddb->run($inputc, $output);

        $this->generateDefaultData($admin, $adminemail);

        $commanddata = $this->getApplication()->find('bicorebundle:configuratorimport');
        $argumentsdata = array(
            'command' => 'bicorebundle:configuratorimport',
            array('--truncatetables' => true),
        );
        $inputd = new ArrayInput($argumentsdata);
        $commanddata->run($inputd, $output);

        $fs = new Filesystem();
        $fs->remove($this->fixtureFile);

        $userManipulator = $this->usermanipulator;

        $userManipulator->changePassword($admin, $adminpass);
        return 0;
    }

    /**
     * This will suppress UnusedLocalVariable
     * warnings in this method.
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    private function generateDefaultData($admin, $adminemail)
    {
        //$todaydt = new \DateTime();
        //$today = $todaydt->format("Y-m-d") . "T00:00:00+01:00";

        $defaultData = <<<EOF
Cdf\BiCoreBundle\Entity\Ruoli:
    -
        id: 1
        ruolo: 'Super Admin'
        paginainiziale: /adminpanel
        superadmin: true
        admin: true
        user: false
    -
        id: 2
        ruolo: Amministratore
        paginainiziale: /adminpanel
        superadmin: false
        admin: true
        user: false
    -
        id: 3
        ruolo: Utente
        paginainiziale: /
        superadmin: false
        admin: false
        user: true
Cdf\BiCoreBundle\Entity\Operatori:
    -
        username: $admin
        usernameCanonical: $admin
        email: $adminemail
        emailCanonical: $adminemail
        enabled: true
        salt: null
        password: $admin
        lastLogin: null
        confirmationToken: null
        passwordRequestedAt: null
        roles:
            - ROLE_SUPER_ADMIN
        id: 1
        operatore: $admin
        ruoli_id: 1
    -
        username: userreadroles
        usernameCanonical: userreadroles
        email: userreadroles@userreadroles.it
        emailCanonical: userreadroles@userreadroles.it
        enabled: true
        salt: mNVzoSgms1k9JU2Eb/syehddayryUqNDh0LFzjggcCM
        password: 1yNtksQ2XpN+zj/Jk9IP0MpBZcaBxg0nltY+EML4Vlv3cJ8g6U6/YaejAP+tagemH2N2htTqP7tELs2ZhlRAHw==
        lastLogin: null
        confirmationToken: null
        passwordRequestedAt: null
        roles: {  }
        id: 2
        operatore: null
        ruoli_id: 3
    -
        username: usernoroles
        usernameCanonical: usernoroles
        email: usernoroles@usernoroles.it
        emailCanonical: usernoroles@usernoroles.it
        enabled: true
        salt: nczIukArDyAEH6vvjehM973qvfDjE.WGzkP24umtpfE
        password: Ce0FJ16dd5HfwJ8CbzocZB3UDZWzwvD9l/A3kyJJR1oHoisxGjF06qR4sSj/Nsk8J6aCI1GtgmHbJfeF7TS93w==
        lastLogin: null
        confirmationToken: null
        passwordRequestedAt: null
        roles: {  }
        id: 3
        operatore: null
        ruoli_id: 3                
Cdf\BiCoreBundle\Entity\Permessi:
    -
        id: 1
        modulo: Menuapplicazione
        crud: crud
        operatori_id: null
        ruoli_id: 1
    -
        id: 2
        modulo: Opzionitabella
        crud: crud
        operatori_id: null
        ruoli_id: 1
    -
        id: 3
        modulo: Tabelle
        crud: crud
        operatori_id: null
        ruoli_id: 1
    -
        id: 4
        modulo: Permessi
        crud: crud
        operatori_id: null
        ruoli_id: 1
    -
        id: 5
        modulo: Operatori
        crud: cru
        operatori_id: null
        ruoli_id: 1
    -
        id: 6
        modulo: Ruoli
        crud: crud
        operatori_id: null
        ruoli_id: 2
    -
        id: 7
        modulo: Cliente
        crud: crud
        operatori_id: null
        ruoli_id: 2
    -
        id: 8
        modulo: Fornitore
        crud: crud
        operatori_id: null
        ruoli_id: 2
    -
        id: 9
        modulo: Prodottofornitore
        crud: crud
        operatori_id: null
        ruoli_id: 2
    -
        id: 10
        modulo: Ordine
        crud: crud
        operatori_id: null
        ruoli_id: 2
    -
        id: 11
        modulo: Ordine
        crud: crud
        operatori_id: null
        ruoli_id: 2
    -
        id: 12
        modulo: Magazzino
        crud: r
        operatori_id: 2
        ruoli_id: null

Cdf\BiCoreBundle\Entity\Colonnetabelle:
  -
    id: 1
    nometabella: '*'
    nomecampo: null
    mostraindex: null
    ordineindex: null
    larghezzaindex: null
    etichettaindex: null
    operatori_id: null
    registrastorico: null
    editabile: null
  -
    id: 2
    nometabella: Permessi
    nomecampo: modulo
    mostraindex: true
    ordineindex: 20
    larghezzaindex: 20
    etichettaindex: Modulo
    operatori_id: null
    registrastorico: true
    editabile: true
  -
    id: 3
    nometabella: Permessi
    nomecampo: crud
    mostraindex: true
    ordineindex: 30
    larghezzaindex: 20
    etichettaindex: CRUD
    operatori_id: null
    registrastorico: true
    editabile: true
  -
    id: 4
    nometabella: Permessi
    nomecampo: ruoli
    mostraindex: true
    ordineindex: 50
    larghezzaindex: 20
    etichettaindex: Ruolo
    operatori_id: null
    registrastorico: true
    editabile: true
  -
    id: 5
    nometabella: Permessi
    nomecampo: operatori
    mostraindex: true
    ordineindex: 60
    larghezzaindex: 20
    etichettaindex: Operatore
    operatori_id: null
    registrastorico: true
    editabile: true

Cdf\BiCoreBundle\Entity\Opzionitabelle:
  -
    id: 1
    nometabella: '*'
    descrizione: null
    parametro: titolo
    valore: 'Elenco dati per %tabella%'
  -
    id: 2
    nometabella: '*'
    descrizione: 'Altezza Griglia'
    parametro: altezzagriglia
    valore: '400'

Cdf\BiCoreBundle\Entity\Menuapplicazione:
  -
    id: 1
    nome: Amministrazione
    percorso: null
    padre: null
    ordine: 20
    attivo: true
    target: null
    tag: Amministrazione
    notifiche: null
    autorizzazionerichiesta: true
    percorsonotifiche: null
  -
    id: 2
    nome: Operatori
    percorso: Operatori
    padre: 1
    ordine: 10
    attivo: true
    target: null
    tag: null
    notifiche: null
    autorizzazionerichiesta: null
    percorsonotifiche: null
  -
    id: 3
    nome: Ruoli
    percorso: Ruoli
    padre: 1
    ordine: 20
    attivo: true
    target: null
    tag: null
    notifiche: null
    autorizzazionerichiesta: null
    percorsonotifiche: null
  -
    id: 4
    nome: Permessi
    percorso: Permessi
    padre: 1
    ordine: 30
    attivo: true
    target: null
    tag: null
    notifiche: null
    autorizzazionerichiesta: null
    percorsonotifiche: null
  -
    id: 5
    nome: 'Gestione tabelle di sistema'
    percorso: null
    padre: 1
    ordine: 40
    attivo: true
    target: null
    tag: null
    notifiche: null
    autorizzazionerichiesta: null
    percorsonotifiche: null
  -
    id: 6
    nome: 'Colonne tabelle'
    percorso: Colonnetabelle
    padre: 5
    ordine: 10
    attivo: true
    target: null
    tag: null
    notifiche: null
    autorizzazionerichiesta: null
    percorsonotifiche: null
  -
    id: 7
    nome: 'Opzioni tabelle'
    percorso: Opzionitabelle
    padre: 5
    ordine: 20
    attivo: true
    target: null
    tag: null
    notifiche: null
    autorizzazionerichiesta: null
    percorsonotifiche: null
  -
    id: 8
    nome: 'Menu Applicazione'
    percorso: Menuapplicazione
    padre: 1
    ordine: 50
    attivo: true
    target: null
    tag: null
    notifiche: null
    autorizzazionerichiesta: null
    percorsonotifiche: null
  -
    id: 9
    nome: Utilità
    percorso: fi_pannello_amministrazione_homepage
    padre: 1
    ordine: 100
    attivo: true
    target: null
    tag: null
    notifiche: null
    autorizzazionerichiesta: null
    percorsonotifiche: null
EOF;
        $fs = new Filesystem();
        $fs->dumpFile($this->fixtureFile, $defaultData);
    }
}
