<?php

namespace App\Security;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserConfirmationService
{
    public function __construct(
        private UserRepository $userRepository,
        private EntityManagerInterface $entityManager)
    {
    }

    public function confirmUser(string $confirmationToken)
    {
        $user = $this->userRepository->findOneBy(
            ['confirmationToken' => $confirmationToken]
        );

        if (!$user) {
            throw new NotFoundHttpException();
        }

        $user->setEnabled(true);
        $user->setConfirmationToken(null);
        $this->entityManager->flush();
    }
}