<?php

namespace App\Controller;

use App\Entity\DailyStat;
use App\Entity\Device;
use App\Entity\SessionStat;
use App\Entity\Subset;
use App\Entity\WeeklyStat;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Routing\Annotation\Route;

class MeasureController extends BaseController
{
    /**
     * @Route("/getLastMeasure", name="get_last_measure")
     * @IsGranted("ROLE_USER")
     */
    public function getLastMeasure(EntityManagerInterface $em)
    {
        //on récupère tous les devices en bdd
        $deviceRepo = $em->getRepository(Device::class);
        $devices = $deviceRepo->findAll();

        $measures = [];
        $client = HttpClient::create(['verify_peer' => false, 'auth_bearer' => $this->token]);

        //pour chaque device on récupère la dernière mesure reçue et le timestamp correspondant
        foreach ($devices as $device) {
            $sensorId = $device->getApiSensorId();
            $response = $client->request('GET', $this->apiUrl . '/services/sensors/' . $sensorId . '/measures', ['query' => ['size' => 1]]);

            $content = json_decode($response->getContent(), true);
            $measure = $content['data'][0]['val'];
            $ts = round($content['data'][0]['ts']/1000); //convertire le timestamp millisecondes en secondes

            $measures[$device->getHostname()]['device'] = $device->getHostname();
            $measures[$device->getHostname()]['client'] = $device->getClient()->getHostname();
            $measures[$device->getHostname()]['value'] = $measure;
            $measures[$device->getHostname()]['timestamp'] = $ts;
        }

        return $this->render('backend/lastMeasures.html.twig', ['measures' => $measures]);

    }

    /**
     * @Route("/weeklyStats", name="weekly_stats")
     * @IsGranted("ROLE_USER")
     */
    public function getWeeklyStats(EntityManagerInterface $em)
    {
        $userOrganization = $this->getUser()->getOrganization();
        $subsetUsed = $em->getRepository(Subset::class)->findBy(['organization' => $userOrganization]);

        $weeklyStatsRepo = $em->getRepository(WeeklyStat::class);
        $weeklyStats = $weeklyStatsRepo->findBy([], ['device' => 'DESC', 'week' => 'DESC']);

        $deviceRepo = $em->getRepository(Device::class);
        $devices = $deviceRepo->findBy(['subset' => $subsetUsed]);

        return $this->render('backend/weeklyStats.html.twig', ['weeklyStats' => $weeklyStats, 'devices' => $devices]);
    }

    /**
     * @Route("/admin/cron/weeklyStats", name="save_weeklyStats")
     */
    public function save_weeklyDistance(EntityManagerInterface $em)
    {
        //on récupère tous les devices en bdd
        $deviceRepo = $em->getRepository(Device::class);
        $devices = $deviceRepo->findAll();
        $client = HttpClient::create(['verify_peer' => false, 'auth_bearer' => $this->token]);

        $currentDate = new \DateTime();

        foreach ($devices as $device) {

            $sensorId = $device->getApiSensorId();

            //on supprime d'abord toutes les entrées présentes
            $weeklyStatRepo = $em->getRepository(WeeklyStat::class);
            $deviceId = $device->getId();
            $weeklyStatRepo->deleteWeeklyStatsOfOneDevice($deviceId);

            $currentWeek = $currentDate->format("W");
            $currentYear = $currentDate->format("Y");
            for ($i = 1; $i < 9; $i++)//on récupère et enregistre les infos sur les 8 dernières semaines (2 mois)
            {
                $currentWeek = $currentWeek - 1;
                //on vérifie que la semaine en question n'est pas l'année d'avant
                if ($currentWeek == 0) {
                    $year = $currentYear - 1;
                    $dt = new DateTime('December 28th, ' . $year .'');//le 28 décembre est toujours dans la dernière semaine de l'année. Donc on regarde si c'est dans la 52è ou 53è
                    $currentWeek = $dt->format('W'); //52 ou 53 selon l'année
                }
                else {
                    $year = $currentYear;
                }
                $response = $client->request('GET', $this->apiUrl . '/services/aggregates/activUpCumulDistanceDeviceByUnit:exec?type=week&period=' . $year . 'W' . $currentWeek . '&sensor_id=' . $sensorId);
                $response = json_decode($response->getContent(), true);

                if (empty($response['data']))//si aucune mesures pour cette période
                {
                    $distance = 0;
                    $duration = 0;
                    $speed = 0;
                }
                else {
                    $distance = $response['data'][0]['stats'][0]['totalDistance'];
                    $duration = $response['data'][0]['stats'][0]['duration'];
                    $speed = $response['data'][0]['stats'][0]['speed'];
                }

                $weeklyStat = new WeeklyStat();
                $weeklyStat->setWeek($year . 'W' . $currentWeek);
                $weeklyStat->setDistance($distance);
                $weeklyStat->setDuration($duration);
                $weeklyStat->setSpeed($speed);
                $weeklyStat->setDevice($device);
                $em->persist($weeklyStat);
            }
        }

        $em->flush();
        return $this->render('backend/home.html.twig');
    }

    /**
     * @Route("/admin/cron/dailyStats", name="save_dailyStats")
     */
    public function save_dailyDistance(EntityManagerInterface $em)
    {
        ini_set('max_execution_time', 600);

        //on récupère tous les devices en bdd
        $deviceRepo = $em->getRepository(Device::class);
        $devices = $deviceRepo->findAll();
        $client = HttpClient::create(['verify_peer' => false, 'auth_bearer' => $this->token]);

        $currentDate = new \DateTime();

        foreach ($devices as $device) {

            $sensorId = $device->getApiSensorId();

            //on supprime d'abord toutes les entrées présentes
            $dailyStatRepo = $em->getRepository(DailyStat::class);
            $deviceId = $device->getId();
            $dailyStatRepo->deleteDailyStatsOfOneDevice($deviceId);

            $currentWeek = $currentDate->format("W");
            $currentYear = $currentDate->format("Y");
            for ($i = 1; $i < 31; $i++)//on récupère et enregistre les infos sur les 30 derniers jours (1 mois)
            {
                $timestamp = time() - $i * 86400;
                $date = new \DateTime();
                $date->setTimestamp($timestamp);

                $year = date('Y', $timestamp);
                $month = date('m', $timestamp);
                $day = date('d', $timestamp);

                $response = $client->request('GET', $this->apiUrl . '/services/aggregates/activUpCumulDistanceDeviceByUnit:exec?type=day&period=' . $year . '' . $month . '' . $day . '&sensor_id=' . $sensorId);
                $response = json_decode($response->getContent(), true);

                if (empty($response['data']))//si aucune mesures pour cette période
                {
                    $distance = 0;
                    $duration = 0;
                    $speed = 0;
                }
                else {
                    $distance = $response['data'][0]['stats'][0]['totalDistance'];
                    $duration = $response['data'][0]['stats'][0]['duration'];
                    $speed = $response['data'][0]['stats'][0]['speed'];
                }

                $dailyStat = new DailyStat();
                $dailyStat->setDay($date);
                $dailyStat->setDistance($distance);
                $dailyStat->setDuration($duration);
                $dailyStat->setSpeed($speed);
                $dailyStat->setDevice($device);
                $em->persist($dailyStat);
            }
        }

        $em->flush();
        return $this->render('backend/home.html.twig');
    }

    /**
     * @Route("/dailyStats", name="daily_stats")
     * @IsGranted("ROLE_USER")
     */
    public function getDailyStats(EntityManagerInterface $em)
    {
        $userOrganization = $this->getUser()->getOrganization();
        $subsetUsed = $em->getRepository(Subset::class)->findBy(['organization' => $userOrganization]);
        $deviceRepo = $em->getRepository(Device::class);
        $devices = $deviceRepo->findBy(['subset' => $subsetUsed]);

        //on récupère toutes les données depuis la BDD
        $dailyStatsRepo = $em->getRepository(DailyStat::class);
        $dailyStats = $dailyStatsRepo->findBy([], ['device' => 'DESC', 'day' => 'DESC']);

        //puis on récupère les données du jour en direct
        $client = HttpClient::create(['verify_peer' => false, 'auth_bearer' => $this->token]);
        $todayStats = [];
        foreach ($devices as $device) {
            $sensorId = $device->getApiSensorId();

                $timestamp = time();
                $date = new \DateTime();
                $date->setTimestamp($timestamp);

                $year = date('Y', $timestamp);
                $month = date('m', $timestamp);
                $day = date('d', $timestamp);

                $response = $client->request('GET', $this->apiUrl . '/services/aggregates/activUpCumulDistanceDeviceByUnit:exec?type=day&period=' . $year . '' . $month . '' . $day . '&sensor_id=' . $sensorId);
                $response = json_decode($response->getContent(), true);

                if (empty($response['data']))//si aucune mesures pour cette période
                {
                    $distance = 0;
                    $duration = 0;
                    $speed = 0;
                }
                else {
                    $distance = $response['data'][0]['stats'][0]['totalDistance'];
                    $duration = round($response['data'][0]['stats'][0]['duration']);
                    $speed = $response['data'][0]['stats'][0]['speed'];
                }

            $todayStats[$device->getHostname()]['device'] = $device->getHostname();
            $todayStats[$device->getHostname()]['client'] = $device->getClient()->getName();
            $todayStats[$device->getHostname()]['distance'] = $distance;
            $todayStats[$device->getHostname()]['duration'] = $duration;
            $todayStats[$device->getHostname()]['speed'] = $speed;

        }

        return $this->render('backend/dailyStats.html.twig', ['dailyStats' => $dailyStats, 'todayStats' => $todayStats, 'devices' => $devices]);
    }

    /**
     * @Route("/admin/cron/sessionStats", name="save_sessionStats")
     * @IsGranted("ROLE_USER")
     */
    public function getSessionStats(EntityManagerInterface $em)
    {
        ini_set('max_execution_time', 600);

        //on récupère tous les devices en bdd
        $deviceRepo = $em->getRepository(Device::class);
        $devices = $deviceRepo->findAll();
        $client = HttpClient::create(['verify_peer' => false, 'auth_bearer' => $this->token]);

        $currentDate = new \DateTime();

        foreach ($devices as $device) {

            $sensorId = $device->getApiSensorId();

            //on supprime d'abord toutes les entrées présentes
            $sessionStatRepo = $em->getRepository(SessionStat::class);
            $deviceId = $device->getId();
            $sessionStatRepo->deleteSessionStatsOfOneDevice($deviceId);

            $currentWeek = $currentDate->format("W");
            $currentYear = $currentDate->format("Y");
            for ($i = 1; $i < 31; $i++)//on récupère et enregistre les infos sur les 30 derniers jours (1 mois)
            {
                $timestamp = time() - $i * 86400;
                $date = new \DateTime();
                $date->setTimestamp($timestamp);

                $year = date('Y', $timestamp);
                $month = date('m', $timestamp);
                $day = date('d', $timestamp);

                $response = $client->request('GET', $this->apiUrl . '/services/aggregates/activUpCumulDistanceDeviceByUnit:exec?type=day&period=' . $year . '' . $month . '' . $day . '&sensor_id=' . $sensorId);
                $response = json_decode($response->getContent(), true);

                if (empty($response['data']))//si aucune mesures pour cette période
                {
                    $distance = 0;
                    $duration = 0;
                    $speed = 0;
                }
                else {
                    $distance = $response['data'][0]['stats'][0]['totalDistance'];
                    $duration = $response['data'][0]['stats'][0]['duration'];
                    $speed = $response['data'][0]['stats'][0]['speed'];
                }

                $dailyStat = new DailyStat();
                $dailyStat->setDay($date);
                $dailyStat->setDistance($distance);
                $dailyStat->setDuration($duration);
                $dailyStat->setSpeed($speed);
                $dailyStat->setDevice($device);
                $em->persist($dailyStat);
            }
        }

        $em->flush();
        return $this->render('backend/home.html.twig');
    }
}
