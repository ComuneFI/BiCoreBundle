<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Entity\Cliente;
use App\Entity\Fornitore;
use App\Entity\Prodottofornitore;
use App\Entity\Ordine;
use App\Entity\Magazzino;
use Cdf\BiCoreBundle\Entity\Opzionitabelle;
use Cdf\BiCoreBundle\Entity\Colonnetabelle;
use Doctrine\ORM\EntityManagerInterface;

class LoadDefaultDataCommand extends Command
{
    protected function configure()
    {
        $this
                ->setName('bicoredemo:loaddefauldata')
                ->setDescription('Carica dei dati di default per il demo')
        ;
    }
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;

        // you *must* call the parent constructor
        parent::__construct();
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /* @var $em \Doctrine\ORM\EntityManager */
        $em = $this->em;

        $this->truncateTables($em, Magazzino::class);
        $this->truncateTables($em, Ordine::class);
        $this->truncateTables($em, Prodottofornitore::class);
        $this->truncateTables($em, Fornitore::class);
        $this->truncateTables($em, Cliente::class);

        $opzionitabella = new Opzionitabelle();
        $opzionitabella->setNometabella('Cliente');
        $em->persist($opzionitabella);
        $em->flush();

        $tabella = new Colonnetabelle();
        $tabella->setNometabella('Cliente');
        $tabella->setNomecampo('nominativo');
        $tabella->setMostraindex(true);
        $tabella->setOrdineindex(100);
        $tabella->setLarghezzaindex(30);
        $tabella->setEtichettaindex('Nominativo');
        $tabella->setRegistrastorico(true);
        $tabella->setEditabile(true);
        $em->persist($tabella);
        $em->flush();

        $clienti = array();
        $clienti['manzi'] = array('nominativo' => 'Andrea Manzi', 'datanascita' => '05/02/1980', 'attivo' => true, 'punti' => 1000, 'creditoresiduo' => 12.33, 'iscrittoil' => '01/01/1999 13:15');
        $clienti['picariello'] = array('nominativo' => 'Emidio Picariello', 'datanascita' => '20/08/1978', 'attivo' => true, 'punti' => 100, 'creditoresiduo' => 25.12, 'iscrittoil' => '01/01/2005 23:59');
        $clienti['costantino'] = array('nominativo' => 'Lorenzo Costantino', 'datanascita' => '08/02/1980', 'attivo' => false, 'punti' => 0, 'creditoresiduo' => 63.33, 'iscrittoil' => '31/12/2010 00:01');
        $clienti['pescinip'] = array('nominativo' => 'Paolo Pescini', 'datanascita' => '09/03/1963', 'attivo' => true, 'punti' => 0, 'creditoresiduo' => 0.01, 'iscrittoil' => '20/01/2008 07:01');
        $clienti['bianchi'] = array('nominativo' => 'Angela Bianchi', 'datanascita' => '03/07/1973', 'attivo' => true, 'punti' => 800, 'creditoresiduo' => 11.99, 'iscrittoil' => '01/01/2011 16:14');
        $clienti['pescinil'] = array('nominativo' => 'Lisa Pescini', 'datanascita' => '18/01/1973', 'attivo' => true, 'punti' => 200, 'creditoresiduo' => 99.13, 'iscrittoil' => '01/01/2007 13:12');
        $clienti['rossi'] = array('nominativo' => 'Giovanni Rossi', 'datanascita' => '13/06/1961', 'attivo' => true, 'punti' => 500, 'creditoresiduo' => 6.18, 'iscrittoil' => '01/01/2000 03:44');
        $clienti['rodota'] = array('nominativo' => 'Stefano Rodotà', 'datanascita' => '30/05/1933', 'attivo' => true, 'punti' => 1500, 'creditoresiduo' => 20.00, 'iscrittoil' => '01/01/1990 00:00');
        $clienti['degli'] = array('nominativo' => "Stefano Degl'Innocenti", 'datanascita' => '13/06/1963', 'attivo' => true, 'punti' => 500, 'creditoresiduo' => 6.18, 'iscrittoil' => '01/01/1990 23:59');
        $clienti['apiceaccentato'] = array('nominativo' => "Niccolò Degl'Innocenti", 'datanascita' => '13/06/1968', 'attivo' => true, 'punti' => 500, 'creditoresiduo' => 6.18, 'iscrittoil' => '01/01/1990 00:01');

        for ($index = 0; $index < 200; ++$index) {
            $clienti['nominativo' . $index] = array('nominativo' => 'Cognome' . $index . ' Nome' . $index, 'datanascita' => '01/01/1950', 'attivo' => false, 'punti' => 0, 'creditoresiduo' => 0, 'iscrittoil' => '01/01/2018 00:00');
        }

        foreach ($clienti as $key => $cliente) {
            $clientecreato = $this->createCliente($em, $cliente);
            $clienti[$key]['id'] = $clientecreato->getId();
            $clienti[$key]['entity'] = $clientecreato;
        }

        $fornitori['barilla'] = array('ragionesociale' => 'Barilla', 'partitaiva' => '123456789012', 'capitalesociale' => 1000000.99);
        $fornitori['alcenero'] = array('ragionesociale' => 'Alce Nero', 'partitaiva' => '980987654321', 'capitalesociale' => 990000.01);
        $fornitori['mulinobianco'] = array('ragionesociale' => 'Mulino Bianco', 'partitaiva' => '456781239012', 'capitalesociale' => 800000.99);

        foreach ($fornitori as $key => $fornitore) {
            $fornitorecreato = $this->createFornitore($em, $fornitore);
            $fornitori[$key]['id'] = $fornitorecreato->getId();
            $fornitori[$key]['entity'] = $fornitorecreato;
        }

        $newpennebarilla = new \App\Entity\Prodottofornitore();
        $newpennebarilla->setFornitore($fornitori['barilla']['entity']);
        $newpennebarilla->setQuantitadisponibile(800);
        $newpennebarilla->setDescrizione('Penne');
        $newpennebarilla->setPrezzo(1.50);
        $em->persist($newpennebarilla);

        $newspaghettibarilla = new \App\Entity\Prodottofornitore();
        $newspaghettibarilla->setFornitore($fornitori['barilla']['entity']);
        $newspaghettibarilla->setQuantitadisponibile(2000);
        $newspaghettibarilla->setDescrizione('Spaghetti');
        $newspaghettibarilla->setPrezzo(1.60);
        $em->persist($newspaghettibarilla);

        $newfusillibarilla = new \App\Entity\Prodottofornitore();
        $newfusillibarilla->setFornitore($fornitori['barilla']['entity']);
        $newfusillibarilla->setQuantitadisponibile(0);
        $newfusillibarilla->setDescrizione('Fusilli');
        $newfusillibarilla->setPrezzo(1.80);
        $em->persist($newfusillibarilla);

        $newlasagnebarilla = new \App\Entity\Prodottofornitore();
        $newlasagnebarilla->setFornitore($fornitori['barilla']['entity']);
        $newlasagnebarilla->setQuantitadisponibile(0);
        $newlasagnebarilla->setDescrizione('Lasagne');
        $newlasagnebarilla->setPrezzo(2.10);
        $em->persist($newlasagnebarilla);

        $newconchigliebarilla = new \App\Entity\Prodottofornitore();
        $newconchigliebarilla->setFornitore($fornitori['barilla']['entity']);
        $newconchigliebarilla->setQuantitadisponibile(0);
        $newconchigliebarilla->setDescrizione('Conchiglie');
        $newconchigliebarilla->setPrezzo(1.10);
        $em->persist($newconchigliebarilla);

        $newrigatonibarilla = new \App\Entity\Prodottofornitore();
        $newrigatonibarilla->setFornitore($fornitori['barilla']['entity']);
        $newrigatonibarilla->setQuantitadisponibile(0);
        $newrigatonibarilla->setDescrizione('Rigatoni');
        $newrigatonibarilla->setPrezzo(0.80);
        $em->persist($newrigatonibarilla);

        $newlinguinebarilla = new \App\Entity\Prodottofornitore();
        $newlinguinebarilla->setFornitore($fornitori['barilla']['entity']);
        $newlinguinebarilla->setQuantitadisponibile(0);
        $newlinguinebarilla->setDescrizione('Linguine');
        $newlinguinebarilla->setPrezzo(1.40);
        $em->persist($newlinguinebarilla);

        $newalcenerosuccoalbicocca = new \App\Entity\Prodottofornitore();
        $newalcenerosuccoalbicocca->setFornitore($fornitori['alcenero']['entity']);
        $newalcenerosuccoalbicocca->setQuantitadisponibile(1400);
        $newalcenerosuccoalbicocca->setDescrizione('Succo di frutta albicocca');
        $newalcenerosuccoalbicocca->setPrezzo(2.10);
        $em->persist($newalcenerosuccoalbicocca);

        $newalcenerosuccopera = new \App\Entity\Prodottofornitore();
        $newalcenerosuccopera->setFornitore($fornitori['alcenero']['entity']);
        $newalcenerosuccopera->setQuantitadisponibile(0);
        $newalcenerosuccopera->setDescrizione('Succo di frutta pera');
        $newalcenerosuccopera->setPrezzo(2.10);
        $em->persist($newalcenerosuccopera);

        $newalcenerosuccopesca = new \App\Entity\Prodottofornitore();
        $newalcenerosuccopesca->setFornitore($fornitori['alcenero']['entity']);
        $newalcenerosuccopesca->setQuantitadisponibile(700);
        $newalcenerosuccopesca->setDescrizione('Succo di frutta pesca');
        $newalcenerosuccopesca->setPrezzo(2.10);
        $em->persist($newalcenerosuccopesca);

        $newalceneroquinoa = new \App\Entity\Prodottofornitore();
        $newalceneroquinoa->setFornitore($fornitori['alcenero']['entity']);
        $newalceneroquinoa->setQuantitadisponibile(500);
        $newalceneroquinoa->setDescrizione('Quinoa');
        $newalceneroquinoa->setPrezzo(5.00);
        $em->persist($newalceneroquinoa);

        $em->flush();

        $newordinecostantino = new \App\Entity\Ordine();
        $newordinecostantino->setProdottofornitore($newalcenerosuccoalbicocca);
        $newordinecostantino->setCliente($clienti['costantino']['entity']);
        $newordinecostantino->setQuantita(10);
        $newordinecostantino->setData(new \DateTime());
        $em->persist($newordinecostantino);

        $newordinemanzi = new \App\Entity\Ordine();
        $newordinemanzi->setProdottofornitore($newalcenerosuccopera);
        $newordinemanzi->setCliente($clienti['manzi']['entity']);
        $newordinemanzi->setQuantita(10);
        $newordinemanzi->setData(new \DateTime());
        $em->persist($newordinemanzi);

        $newordinemanzi1 = new \App\Entity\Ordine();
        $newordinemanzi1->setProdottofornitore($newalceneroquinoa);
        $newordinemanzi1->setCliente($clienti['manzi']['entity']);
        $newordinemanzi1->setQuantita(20);
        $newordinemanzi1->setData(new \DateTime('first day of last month'));
        $em->persist($newordinemanzi1);

        $newordinepicarielloquinoa = new \App\Entity\Ordine();
        $newordinepicarielloquinoa->setProdottofornitore($newalceneroquinoa);
        $newordinepicarielloquinoa->setCliente($clienti['picariello']['entity']);
        $newordinepicarielloquinoa->setQuantita(5);
        $newordinepicarielloquinoa->setData(new \DateTime('first day of last month'));
        $em->persist($newordinepicarielloquinoa);

        $newordinelisaquinoa = new \App\Entity\Ordine();
        $newordinelisaquinoa->setProdottofornitore($newalceneroquinoa);
        $newordinelisaquinoa->setCliente($clienti['pescinil']['entity']);
        $newordinelisaquinoa->setQuantita(15);
        $newordinelisaquinoa->setData(new \DateTime('first day of last month'));
        $em->persist($newordinelisaquinoa);

        $newordinepaolopenne = new \App\Entity\Ordine();
        $newordinepaolopenne->setProdottofornitore($newpennebarilla);
        $newordinepaolopenne->setCliente($clienti['pescinip']['entity']);
        $newordinepaolopenne->setQuantita(35);
        $newordinepaolopenne->setData(new \DateTime('first day of last month'));
        $em->persist($newordinepaolopenne);

        $newordineangelapenne = new \App\Entity\Ordine();
        $newordineangelapenne->setProdottofornitore($newpennebarilla);
        $newordineangelapenne->setCliente($clienti['bianchi']['entity']);
        $newordineangelapenne->setQuantita(25);
        $newordineangelapenne->setData(new \DateTime('first day of last month'));
        $em->persist($newordineangelapenne);

        $newordinegiovannipenne = new \App\Entity\Ordine();
        $newordinegiovannipenne->setProdottofornitore($newpennebarilla);
        $newordinegiovannipenne->setCliente($clienti['rossi']['entity']);
        $newordinegiovannipenne->setQuantita(12);
        $newordinegiovannipenne->setData(new \DateTime('first day of last month'));
        $em->persist($newordinegiovannipenne);

        $newordinepaolospaghetti = new \App\Entity\Ordine();
        $newordinepaolospaghetti->setProdottofornitore($newspaghettibarilla);
        $newordinepaolospaghetti->setCliente($clienti['pescinip']['entity']);
        $newordinepaolospaghetti->setQuantita(22);
        $newordinepaolospaghetti->setData(new \DateTime('first day of last month'));
        $em->persist($newordinepaolospaghetti);

        $newordinelorenzolinguine = new \App\Entity\Ordine();
        $newordinelorenzolinguine->setProdottofornitore($newlinguinebarilla);
        $newordinelorenzolinguine->setCliente($clienti['costantino']['entity']);
        $newordinelorenzolinguine->setQuantita(10);
        $newordinelorenzolinguine->setData(new \DateTime('first day of last month'));
        $em->persist($newordinelorenzolinguine);

        $newordineangelalasagne = new \App\Entity\Ordine();
        $newordineangelalasagne->setProdottofornitore($newlasagnebarilla);
        $newordineangelalasagne->setCliente($clienti['bianchi']['entity']);
        $newordineangelalasagne->setQuantita(10);
        $newordineangelalasagne->setData(new \DateTime('first day of last month'));
        $em->persist($newordineangelalasagne);

        $newordinelisasucco = new \App\Entity\Ordine();
        $newordinelisasucco->setProdottofornitore($newalcenerosuccopera);
        $newordinelisasucco->setCliente($clienti['pescinil']['entity']);
        $newordinelisasucco->setQuantita(10);
        $newordinelisasucco->setData(new \DateTime('first day of last month'));
        $em->persist($newordinelisasucco);

        $newordinegiovanniconchiglie = new \App\Entity\Ordine();
        $newordinegiovanniconchiglie->setProdottofornitore($newconchigliebarilla);
        $newordinegiovanniconchiglie->setCliente($clienti['rossi']['entity']);
        $newordinegiovanniconchiglie->setQuantita(1);
        $newordinegiovanniconchiglie->setData(new \DateTime('first day of last month'));
        $em->persist($newordinegiovanniconchiglie);

        $newordinemanzisucco = new \App\Entity\Ordine();
        $newordinemanzisucco->setProdottofornitore($newalcenerosuccopesca);
        $newordinemanzisucco->setCliente($clienti['manzi']['entity']);
        $newordinemanzisucco->setQuantita(8);
        $newordinemanzisucco->setData(new \DateTime('first day of last month'));
        $em->persist($newordinemanzisucco);

        $em->flush();

        $newmagazzino = new \App\Entity\Magazzino();
        $newmagazzino->setOrdine($newordinecostantino);
        $newmagazzino->setEvaso(false);
        $em->persist($newmagazzino);

        $newmagazzino = new \App\Entity\Magazzino();
        $newmagazzino->setOrdine($newordinepaolopenne);
        $newmagazzino->setEvaso(false);
        $em->persist($newmagazzino);

        $datamaga = \DateTime::createFromFormat('d/m/Y H:i', '30/10/2018 23:59');
        $newmagazzino = new \App\Entity\Magazzino();
        $newmagazzino->setOrdine($newordinemanzi);
        $newmagazzino->setEvaso(true);
        $newmagazzino->setDataspedizione($datamaga);
        $newmagazzino->setGiornodellasettimana($datamaga->format('w'));
        $em->persist($newmagazzino);

        $datamaga = \DateTime::createFromFormat('d/m/Y H:i', '31/12/2017 00:01');
        $newmagazzino = new \App\Entity\Magazzino();
        $newmagazzino->setOrdine($newordinemanzi1);
        $newmagazzino->setEvaso(true);
        $newmagazzino->setDataspedizione($datamaga);
        $newmagazzino->setGiornodellasettimana($datamaga->format('w'));
        $em->persist($newmagazzino);

        $datamaga = \DateTime::createFromFormat('d/m/Y H:i', '01/01/1999 00:01');
        $newmagazzino = new \App\Entity\Magazzino();
        $newmagazzino->setOrdine($newordineangelalasagne);
        $newmagazzino->setEvaso(true);
        $newmagazzino->setDataspedizione($datamaga);
        $newmagazzino->setGiornodellasettimana($datamaga->format('w'));
        $em->persist($newmagazzino);

        $datamaga = \DateTime::createFromFormat('d/m/Y H:i', '01/01/1980 00:01');
        $newmagazzino = new \App\Entity\Magazzino();
        $newmagazzino->setOrdine($newordineangelapenne);
        $newmagazzino->setEvaso(true);
        $newmagazzino->setDataspedizione($datamaga);
        $newmagazzino->setGiornodellasettimana($datamaga->format('w'));
        $em->persist($newmagazzino);

        $datamaga = \DateTime::createFromFormat('d/m/Y H:i', '10/10/2018 23:59');
        $newmagazzino = new \App\Entity\Magazzino();
        $newmagazzino->setOrdine($newordinegiovanniconchiglie);
        $newmagazzino->setEvaso(false);
        $newmagazzino->setDataspedizione($datamaga);
        $newmagazzino->setGiornodellasettimana($datamaga->format('w'));
        $em->persist($newmagazzino);

        $datamaga = \DateTime::createFromFormat('d/m/Y H:i', '10/09/2018 23:59');
        $newmagazzino = new \App\Entity\Magazzino();
        $newmagazzino->setOrdine($newordinelisasucco);
        $newmagazzino->setEvaso(false);
        $newmagazzino->setDataspedizione($datamaga);
        $newmagazzino->setGiornodellasettimana($datamaga->format('w'));
        $em->persist($newmagazzino);

        $datamaga = \DateTime::createFromFormat('d/m/Y H:i', '01/11/2018 23:59');
        $newmagazzino = new \App\Entity\Magazzino();
        $newmagazzino->setOrdine($newordinepicarielloquinoa);
        $newmagazzino->setEvaso(true);
        $newmagazzino->setDataspedizione($datamaga);
        $newmagazzino->setGiornodellasettimana($datamaga->format('w'));
        $em->persist($newmagazzino);

        $datamaga = \DateTime::createFromFormat('d/m/Y H:i', '30/11/2018 23:59');
        $newmagazzino = new \App\Entity\Magazzino();
        $newmagazzino->setOrdine($newordinepicarielloquinoa);
        $newmagazzino->setEvaso(true);
        $newmagazzino->setDataspedizione($datamaga);
        $newmagazzino->setGiornodellasettimana($datamaga->format('w'));
        $em->persist($newmagazzino);

        $datamaga = \DateTime::createFromFormat('d/m/Y H:i', '30/11/2018 00:00');
        $newmagazzino = new \App\Entity\Magazzino();
        $newmagazzino->setOrdine($newordinepaolospaghetti);
        $newmagazzino->setEvaso(true);
        $newmagazzino->setDataspedizione($datamaga);
        $newmagazzino->setGiornodellasettimana($datamaga->format('w'));
        $em->persist($newmagazzino);

        $em->flush();

        $newmenu = new \Cdf\BiCoreBundle\Entity\Menuapplicazione();
        $newmenu->setNome('Demo');
        $newmenu->setAttivo(true);
        $em->persist($newmenu);
        $em->flush();
        $idpadremenudemo = $newmenu->getId();

        $newmenu = new \Cdf\BiCoreBundle\Entity\Menuapplicazione();
        $newmenu->setNome('Cliente');
        $newmenu->setAttivo(true);
        $newmenu->setPadre($idpadremenudemo);
        $newmenu->setPercorso('Cliente_container');
        $newmenu->setOrdine(10);
        $em->persist($newmenu);

        $newmenu = new \Cdf\BiCoreBundle\Entity\Menuapplicazione();
        $newmenu->setNome('Fornitore');
        $newmenu->setAttivo(true);
        $newmenu->setPadre($idpadremenudemo);
        $newmenu->setPercorso('Fornitore_container');
        $newmenu->setOrdine(20);
        $em->persist($newmenu);

        $newmenu = new \Cdf\BiCoreBundle\Entity\Menuapplicazione();
        $newmenu->setNome('Prodottofornitore');
        $newmenu->setAttivo(true);
        $newmenu->setPadre($idpadremenudemo);
        $newmenu->setPercorso('Prodottofornitore_container');
        $newmenu->setOrdine(30);
        $em->persist($newmenu);

        $newmenu = new \Cdf\BiCoreBundle\Entity\Menuapplicazione();
        $newmenu->setNome('Ordine');
        $newmenu->setAttivo(true);
        $newmenu->setPadre($idpadremenudemo);
        $newmenu->setPercorso('Ordine_container');
        $newmenu->setOrdine(50);
        $em->persist($newmenu);

        $newmenu = new \Cdf\BiCoreBundle\Entity\Menuapplicazione();
        $newmenu->setNome('Magazzino');
        $newmenu->setAttivo(true);
        $newmenu->setPadre($idpadremenudemo);
        $newmenu->setPercorso('Magazzino_container');
        $newmenu->setOrdine(60);
        $em->persist($newmenu);

        $newmenu = new \Cdf\BiCoreBundle\Entity\Menuapplicazione();
        $newmenu->setNome('Report');
        $newmenu->setAttivo(true);
        $newmenu->setPadre($idpadremenudemo);
        $newmenu->setPercorso('Report_container');
        $newmenu->setOrdine(70);
        $em->persist($newmenu);

        $em->flush();

        $output->writeln('Done!');
        return 0;
    }
    private function createCliente($em, $cliente)
    {
        $newcliente = new Cliente();
        $newcliente->setNominativo($cliente['nominativo']);
        $newcliente->setDatanascita(\DateTime::createFromFormat('d/m/Y', $cliente['datanascita']));
        $newcliente->setAttivo($cliente['attivo']);
        $newcliente->setPunti($cliente['punti']);
        $newcliente->setCreditoresiduo($cliente['creditoresiduo']);
        $newcliente->setIscrittoil(\DateTime::createFromFormat('d/m/Y H:i', $cliente['iscrittoil']));
        $em->persist($newcliente);
        $em->flush();

        return $newcliente;
    }
    private function createFornitore($em, $fornitore)
    {
        $newfornitore = new Fornitore();
        $newfornitore->setRagionesociale($fornitore['ragionesociale']);
        $newfornitore->setCapitalesociale($fornitore['capitalesociale']);
        $newfornitore->setPartitaiva($fornitore['partitaiva']);
        $em->persist($newfornitore);
        $em->flush();

        return $newfornitore;
    }
    private function truncateTables($em, $className)
    {
        $cmd = $em->getClassMetadata($className);
        $connection = $em->getConnection();
        $dbPlatform = $connection->getDatabasePlatform();
        try {
            $connection->query('SET FOREIGN_KEY_CHECKS=0');
        } catch (\Exception $exc) {
            //echo $exc->getMessage();
        }
        $tablename = $cmd->getTableName();
        if ($cmd->getSchemaName()) {
            $tablename = $cmd->getSchemaName() . "." . $tablename;
        }
        $connection->query('DELETE FROM ' . $tablename);
        //$q = $dbPlatform->getTruncateTableSql($cmd->getTableName());
        //$connection->executeUpdate($q);
        try {
            $connection->query('SET FOREIGN_KEY_CHECKS=1');
        } catch (\Exception $exc) {
            //echo $exc->getMessage();
        }
    }
}
