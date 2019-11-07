<?php

namespace Jtl\Connector\Example\Authentication;

use Jtl\Connector\Core\Authentication\ITokenValidator;

class TokenValidator implements ITokenValidator
{
    /**
     * @param string $token
     * @return bool
     */
    public function validate(string $token): bool
    {
        return $token === 'miesu5eicaech6ohy5aigh0aiz6toh7O';
    }
}
