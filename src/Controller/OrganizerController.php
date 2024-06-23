<?php

namespace App\Controller;

use App\Entity\Organizer;
use App\Entity\Session;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class OrganizerController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private ValidatorInterface $validator;

    public function __construct(EntityManagerInterface $entityManager, ValidatorInterface $validator)
    {
        $this->entityManager = $entityManager;
        $this->validator = $validator;
    }

    #[Route('/event', name: 'create_organizer', methods: ['POST'])]
    public function createOrganizer(Request $request): JsonResponse
    {
        $authHeader = $request->headers->get('Authorization');

        if (!$authHeader) {
            return new JsonResponse(['error' => 'Authorization header not found'], 401);
        }

        if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return new JsonResponse(['error' => 'Invalid Authorization header format'], 401);
        }

        $sessionToken = $matches[1];

        $session = $this->entityManager->getRepository(Session::class)->findOneBy(['sessionToken' => $sessionToken]);

        if (!$session) {
            return new JsonResponse(['error' => 'Invalid session token'], 401);
        }

        if ($session->getExpirationDate() < new \DateTime()) {
            return new JsonResponse(['error' => 'Session expired'], 401);
        }

        $user = $session->getUser();

        if (!$user instanceof User) {
            return new JsonResponse(['error' => 'User not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (null === $data) {
            return new JsonResponse(['error' => 'Invalid JSON'], 400);
        }

        $organizer = new Organizer();
        $organizer->setEventName($data['eventname'] ?? '')
                  ->setNumberOfTickets($data['nbplace'] ?? '')
                  ->setPrice($data['price'] ?? '')
                  ->setEventDate(new \DateTime($data['date'] ?? 'now'))
                  ->setDescription($data['description'] ?? '')
                  ->setUser($user);

        $errors = $this->validator->validate($organizer);
        if (count($errors) > 0) {
            return new JsonResponse([
                'error' => 'Validation failed',
                'details' => (string) $errors,
            ], 400);
        }

        try {
            $this->entityManager->persist($organizer);
            $this->entityManager->flush();

            return new JsonResponse(['message' => 'Organizer registered successfully'], 200);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Organizer registration failed',
                'details' => $e->getMessage(),
            ], 500);
        }
    }
}