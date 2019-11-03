<?php

namespace App\Controller;
use App\Entity\Device;
use App\Entity\Ping;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class PingController extends AbstractController
{
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * @Route("/admin/pings", name="pings_home")
     */
    public function ping_home(EntityManagerInterface $em)
    {
        $pingRepo = $em->getRepository(Ping::class);
        $pings = $pingRepo->findBy([], ['device' => 'DESC', 'date' => 'DESC']);

        $deviceRepo = $em->getRepository(Device::class);
        $devices = $deviceRepo->findAll();

        $pingsToday = [];
        //on récupère et calcule la valeur du jour en direct
        foreach ($devices as $device)
        {
                 date_default_timezone_set('Europe/Paris');

                $jourAConsiderer = date('Y-m-d', time());
                $timestamp = time();
                $date = new \DateTime();
                $date->setTimestamp($timestamp);

                $deviceName = $device->getHostname();
                $filename = "http://stats.activup.net/p/log/" . $jourAConsiderer . "/" . $deviceName . "-" . $jourAConsiderer . ".txt";
                if (!empty(@file_get_contents($filename))) {
                    $contenuFichier = explode("\n", file_get_contents($filename));
                    $nbrePings = count($contenuFichier);

                    $nbrePingsTheorique = floor((date('H', time()) * 60 + date('i', time()))/5);
                    $pourcentageConnexion = floor(($nbrePings/$nbrePingsTheorique)*100);

                    $pingsToday[$device->getHostname()] = $pourcentageConnexion;

                }
                else {
                    $pingsToday[$device->getHostname()] = 0;
                }
        }

        return $this->render('backend/pings.html.twig', ['pings' => $pings, 'pingsToday' => $pingsToday, 'devices' => $devices]);
    }

    /**
     * @Route("/admin/cron/pings", name="save_pings")
     */
    public function save_pings(EntityManagerInterface $em)
    {
        $deviceRepo = $em->getRepository(Device::class);
        $devices = $deviceRepo->findAll();

        $devicesDisconnected = "";
        $devicesWarning = "";
        foreach ($devices as $device)
        {
            //on supprime d'abord toutes les entrées présentes
            $pingRepo = $em->getRepository(Ping::class);
            $deviceId = $device->getId();
            $pingRepo->deletePingsOfOneDevice($deviceId);

            //on crée une entrée dans la table pings pour chaque jour depuis 30j
            for($i=1; $i<=31; $i++)
            {
                $timestamp = time() - $i * 86400;
                $jourAConsiderer = date('Y-m-d', $timestamp);
                $date = new \DateTime();
                $date->setTimestamp($timestamp);

                $deviceName = $device->getHostname();
                $filename = "http://stats.activup.net/p/log/" . $jourAConsiderer . "/" . $deviceName . "-" . $jourAConsiderer . ".txt";
                if (!empty(@file_get_contents($filename))) {
                    $contenuFichier = explode("\n", file_get_contents($filename));
                    $nbrePings = count($contenuFichier);
                    $pourcentageConnexion = floor(($nbrePings/288)*100);

                    $ping = new Ping();
                    $ping->setDevice($device);
                    $ping->setDate($date);
                    $ping->setValue($pourcentageConnexion);
                    $em->persist($ping);

                    if ($i == 1 && $pourcentageConnexion < 50) { $devicesWarning = $devicesWarning . "%%" . $deviceName; }
                }
                else {
                    $ping = new Ping();
                    $ping->setDevice($device);
                    $ping->setDate($date);
                    $ping->setValue(0);
                    $em->persist($ping);
                    if ($i == 1) { $devicesDisconnected = $devicesDisconnected . "" . $deviceName . "%%"; }
                }
            }
        }
        $em->flush();

        //on envoi l'e-mail de rapport de cron

        $timestamp = time() - 86400;
        $jourAConsiderer = date('d-m-Y', $timestamp);
        $devicesDisconnectedMail = "";
        $devicesDisconnected = explode('%%', $devicesDisconnected);
        if (count($devicesDisconnected) == 1) {
            $devicesDisconnectedMail = "Aucun compteur déconnecté !";
        }
        else {
            for ($i = 0; $i < count($devicesDisconnected); $i++) {
                if ($devicesDisconnected[$i] != "") {
                    $devicesDisconnectedMail .= "- " . $devicesDisconnected[$i] . "<br />";
                }
            }
        }

        $devicesWarningMail = "";
        $devicesWarning = explode('%%', $devicesWarning);
        if (count($devicesWarning) == 1) {
            $devicesWarningMail = "Aucun compteur à surveiller !";
        }
        else {
            for ($i = 0; $i < count($devicesWarning); $i++) {
                if ($devicesWarning[$i] != "") {
                    $devicesWarningMail .= "- " . $devicesWarning[$i] . "<br />";
                }
            }
        }

        $email = (new Email())
            ->from('quentinboinet@live.fr')
            ->to('quentin@activup.net')
            ->subject('BO ActivUP - Rapport de CRON, connexion des compteurs')
            ->html('<h3>Backoffice ActivUP</h3><p>Voici le rapport de la connectivité des compteurs pour le jour du ' . $jourAConsiderer . '. </p>
                <h5>Compteurs non connectés :</h5>
                ' . $devicesDisconnectedMail . '
                <h5>Compteurs à surveiller (<50% de connexion) :</h5>
                ' . $devicesWarningMail . '
                <br /><p><b>Quentin Boinet</b></p>');

        $this->mailer->send($email);

        return $this->render('backend/home.html.twig');
    }
}