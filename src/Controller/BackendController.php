<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class BackendController extends AbstractController
{
    /**
     * @Route("/admin/", name="home")
     */
    public function home()
    {
        return $this->render('backend/home.html.twig');
    }
}
