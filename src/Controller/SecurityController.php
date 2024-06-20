<?php 
namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Entity\Session; // Assuming this is your session entity

class SecurityController extends AbstractController
{
    #[Route('/logout', name: 'logout', methods: ['POST'])]
    public function logout(SessionInterface $session, EntityManagerInterface $entityManager, Request $request): JsonResponse
    {
        // Invalidate the Symfony session
        $session->invalidate();

        // Get the session token from the request header or wherever it's stored
        $authHeader = $request->headers->get('Authorization');
        if ($authHeader && preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            $sessionToken = $matches[1];

            // Find the session entity and mark it as expired
            $sessionEntity = $entityManager->getRepository(Session::class)->findOneBy(['sessionToken' => $sessionToken]);

            if ($sessionEntity) {
                $sessionEntity->setExpirationDate(new \DateTime()); // Set to current date to expire immediately
                $entityManager->persist($sessionEntity);
                $entityManager->flush();
            }
        }

        return new JsonResponse(['message' => 'Logged out'], Response::HTTP_OK);
    }
}