<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\SerializerInterface;

class UserController extends AbstractController
{
    private $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    #[Route('/user', name: 'get_user_info', methods: ['GET'])]
    public function getUserInfo(\Symfony\Bundle\SecurityBundle\Security $security): Response
    {
        // Get the current user
        $user = $security->getUser();

        // Check if a user is authenticated
        if (!$user) {
            return $this->json(['error' => 'User not authenticated'], Response::HTTP_UNAUTHORIZED);
        }

        // Serialize the user object to JSON and return it
        $userData = $this->serializer->serialize($user, 'json');

        return new Response($userData, Response::HTTP_OK, ['Content-Type' => 'application/json']);
    }
}