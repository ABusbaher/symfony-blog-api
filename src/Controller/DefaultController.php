<?php

namespace App\Controller;

use App\Security\UserConfirmationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    #[Route('/', name: 'default_index')]
    public function index(): Response
    {
        return $this->render('base.html.twig');
    }

    #[Route('/confirm-user/{token}', name: 'default_confirm_token')]
    public function confirmUser(string $token, UserConfirmationService $userConfirmationService): RedirectResponse
    {
        $userConfirmationService->confirmUser($token);
        return $this->redirectToRoute('default_index');
    }
}