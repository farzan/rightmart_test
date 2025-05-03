<?php

declare(strict_types=1);

namespace App\Adapter\Primary\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
    #[Route(path: '/')]
    public function home(): Response
    {
        return $this->render('dashboard.html.twig');
    }
}
