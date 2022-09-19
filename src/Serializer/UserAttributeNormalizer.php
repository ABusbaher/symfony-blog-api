<?php

namespace App\Serializer;

use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerAwareTrait;

class UserAttributeNormalizer implements ContextAwareNormalizerInterface, SerializerAwareInterface
{
    const USER_ATTRIBUTE_NORMALIZER_ALREADY_CALLED = 'USER_ATTRIBUTE_NORMALIZER_ALREADY_CALLED';

    use SerializerAwareTrait;

    public function __construct(private TokenStorageInterface $tokenStorage)
    {
    }

    public function supportsNormalization(mixed $data, string $format = null, array $context = []): bool
    {
        if (isset($context[self::USER_ATTRIBUTE_NORMALIZER_ALREADY_CALLED])) {
            return false;
        }
        return $data instanceof User;
    }

    public function normalize(mixed $object, string $format = null, array $context = []): float|array|\ArrayObject|bool|int|string|null
    {
        if ($this->isUserHimself($object)) {
            $context['groups'][] = 'get-owner';
        }
        return $this->passOn($object, $format, $context);
    }

    private function isUserHimself(mixed $object): bool
    {
        return $object->getEmail() === $this->tokenStorage->getToken()->getUser()->getUserIdentifier();
    }

    private function passOn(mixed $object, ?string $format, array $context): string
    {
        if (!$this->serializer instanceof NormalizerInterface) {
            throw new \LogicException(sprintf('Can not normalize object "%s" because
            the injected serializer is not a normalizer.', $object));
        }
        $context[self::USER_ATTRIBUTE_NORMALIZER_ALREADY_CALLED] = true;
        return $this->serializer->serialize($object, $format, $context);
    }
}