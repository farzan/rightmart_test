<?php

declare(strict_types=1);

namespace App\Adapter\Primary\Controllers;

use App\Application\Ports\Input\LogServiceInterface;
use DateTime;
use App\Domain\LogCounter\LogCountQuery;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ApiController extends AbstractController
{
    #[Route(path: '/count')]
    public function count(Request $request, LogServiceInterface $logService): Response
    {
        $query = $this->getCountQuery($request);
        
        try {
            $count = $logService->getCount($query);
            
            return $this->json([
                'counter' => $count->counter,
            ]);
        } catch (Exception) {
            return new Response(status: 400);
        }
    }
    
    /**
     * @param Request $request
     *
     * @return LogCountQuery
     */
    private function getCountQuery(Request $request): LogCountQuery
    {
        $serviceNames = null;
        $statusCode = null;
        $startDate = null;
        $endDate = null;
        
        if ($request->query->has('serviceNames')) {
            $serviceNames = $request->query->all()['serviceNames'];
        }
        if ($request->query->has('statusCode')) {
            $statusCode = (int) $request->query->get('statusCode');
        }
        if ($request->query->has('startDate')) {
            $startDate = new DateTime($request->query->get('startDate'));
        }
        if ($request->query->has('endDate')) {
            $endDate = new DateTime($request->query->get('endDate'));
        }
        
        return new LogCountQuery($serviceNames, $statusCode, $startDate, $endDate);
    }
}
