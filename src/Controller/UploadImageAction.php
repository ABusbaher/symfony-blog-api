<?php

namespace App\Controller;

use ApiPlatform\Core\Validator\Exception\ValidationException;
use App\Entity\Image;
use App\Form\ImageType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormFactoryInterface;
use ApiPlatform\Core\Validator\ValidatorInterface;

class UploadImageAction
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private FormFactoryInterface $formFactory,
        private ValidatorInterface $validator)
    {
    }

    public function __invoke(Request $request): Image
    {
        $image = new Image();
        $form = $this->formFactory->create(ImageType::class, $image);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
            $this->entityManager->persist($image);
            $this->entityManager->flush();

            $image->setFile(null);

            return $image;
        }
        throw new ValidationException(
            $this->validator->validate($image)
        );
    }
}