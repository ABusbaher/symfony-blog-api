<?php

namespace App\Security;

use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\ExpiredTokenException;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authenticator\JWTAuthenticator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class TokenAuthenticator extends JWTAuthenticator
{
    public function doAuthenticate(Request $request): Passport|SelfValidatingPassport
    {
        $passport = parent::doAuthenticate($request);
        /** @var User $user */
        $user = $passport->getUser();
        if ($user->getPasswordChangeDate() &&
            $passport->getAttribute('payload')['iat'] < $user->getPasswordChangeDate()) {
            throw new ExpiredTokenException();
        }

        return $passport;
    }
}