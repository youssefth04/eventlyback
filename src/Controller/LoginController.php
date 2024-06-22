<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Session;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/login', name: 'login', methods: ['POST'])]
class LoginController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher)
    {
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
    }

    #[Route('/login', name: 'login', methods: ['POST'])]
    public function signIn(Request $request): JsonResponse
    {
        // Decode JSON data from request body
        $requestData = json_decode($request->getContent(), true);
        $username = $requestData['username'];
        $password = $requestData['password'];
    
        // Find user by username in the database
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => $username]);
    
        // Check if user exists and password is valid
        if ($user && $this->passwordHasher->isPasswordValid($user, $password)) {
            // Generate a random session token
            $sessionToken = bin2hex(random_bytes(32));
            $expirationDate = new \DateTime();
            $expirationDate->modify("+360 minutes"); // Set expiration date
    
            // Create a new session entity and associate it with the user
            $session = new Session();
            $session->setUser($user);
            $session->setSessionToken($sessionToken);
            $session->setExpirationDate($expirationDate);
    
            // Persist the session entity to the database
            $this->entityManager->persist($session);
            $this->entityManager->flush();
    
            // Return a JSON response with the session token and user role
            return $this->json([
                'message' => 'Login successful',
                'sessionToken' => $sessionToken,
                'role' => $user->getRole(), // Assuming User entity has a getRole() method
            ]);
        }
    
        // Return an error response if authentication fails
        return $this->json(['error' => 'Invalid username or password'], 401);
    }

    #[Route('/checkcredential', name: 'check_credential', methods: ['POST'])]
    public function checkCredential(Request $request): JsonResponse
    {
        $requestData = json_decode($request->getContent(), true);
    
        if (!isset($requestData) || !is_array($requestData) || !isset($requestData['sessionToken'])) {
            return $this->json(['error' => 'Invalid request data'], 400);
        }
    
        $sessionToken = $requestData['sessionToken'];
    
        $session = $this->entityManager->getRepository(Session::class)->findOneBy(['sessionToken' => $sessionToken]);
    
        if ($session) {
            $currentDate = new \DateTime();
            if ($session->getExpirationDate() > $currentDate) {
                // Fetch user details without circular reference issues
                $user = $session->getUser();
                
                // Optionally, serialize the user object with Symfony's Serializer
                $userData = [
                    'id' => $user->getId(),
                    'username' => $user->getUsername(),
                    'email' => $user->getEmail(),
                    'role' => $user->getRole()
                    
                    // Add any other user fields you need to return
                ];
                
                return $this->json(['message' => 'Authenticated', 'user' => $userData]);
            } else {
                return $this->json(['error' => 'Session expired'], 401);
            }
        }
    
        return $this->json(['error' => 'Invalid session token'], 400);
    }
}