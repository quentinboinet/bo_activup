<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class BaseController extends AbstractController
{
    /**
     * @var string
     */
    protected $apiUrl;
    /**
     * @var string
     */
    protected $token;

    public function __construct()
    {
        $this->apiUrl = "https://api.activup.net";
        $this->token = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOiJhbm9ueW1vdXMiLCJraW5kIjoiQUNDT1VOVCIsImlhdCI6MTUzMzczNDEyNywidGlkIjoic3lzdGVtIiwianRpIjoiNWI2YWVjZWY0NmUwZmIwMDJkMWFhMjQ0In0.I3pphDQfhXKwxEL7wn0L-wxWIutOG4bR9m1QszcOnbg";
    }
}
