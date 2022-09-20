<?php

namespace App\Controller;

use ApiPlatform\Core\Validator\ValidatorInterface;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


class ResetPasswordAction
{
    public function __construct(
        private ValidatorInterface $validator,
        private UserPasswordHasherInterface $passwordHasher,
        private EntityManagerInterface $entityManager,
        private JWTTokenManagerInterface $tokenManager)
    {
    }

    public function __invoke(User $data): JsonResponse
    {
        $this->validator->validate($data);
        $data->setPassword(
            $this->passwordHasher->hashPassword(
                $data, $data->getNewPassword()
            )
        );
        $data->setPasswordChangeDate(time());
        $this->entityManager->flush();
        $token = $this->tokenManager->create($data);
        return new JsonResponse(['token' => $token]);
    }
}