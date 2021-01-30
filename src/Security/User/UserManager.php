<?php
/*
 * ******************************************************************************
 * This file is part of the GraphQL Bundle package.
 *
 * (c) YnloUltratech <support@ynloultratech.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *  *****************************************************************************
 */

namespace Ynlo\GraphQLBundle\Security\User;

use Ynlo\GraphQLBundle\Model\UserInterface;

/**
 * Abstract User Manager implementation which can be used as base class for your
 * concrete manager.
 */
abstract class UserManager implements UserManagerInterface
{
    private PasswordUpdaterInterface $passwordUpdater;

    public function __construct(PasswordUpdaterInterface $passwordUpdater)
    {
        $this->passwordUpdater = $passwordUpdater;
    }

    /**
     * {@inheritdoc}
     */
    public function createUser(): UserInterface
    {
        $class = $this->getClass();

        return new $class();
    }

    /**
     * {@inheritdoc}
     */
    public function findUserByEmail($email): UserInterface
    {
        return $this->findUserBy(['emailCanonical' => $this->canonicalize($email)]);
    }

    /**
     * {@inheritdoc}
     */
    public function findUserByUsername($username): UserInterface
    {
        return $this->findUserBy(['usernameCanonical' => $this->canonicalize($username)]);
    }

    /**
     * {@inheritdoc}
     */
    public function findUserByUsernameOrEmail($usernameOrEmail): UserInterface
    {
        if (preg_match('/^.+\@\S+\.\S+$/', $usernameOrEmail)) {
            $user = $this->findUserByEmail($usernameOrEmail);
            if (null !== $user) {
                return $user;
            }
        }

        return $this->findUserByUsername($usernameOrEmail);
    }

    /**
     * {@inheritdoc}
     */
    public function findUserByConfirmationToken($token): UserInterface
    {
        return $this->findUserBy(['confirmationToken' => $token]);
    }

    /**
     * {@inheritdoc}
     */
    public function updateCanonicalFields(UserInterface $user): void
    {
        $user->setEmailCanonical($this->canonicalize($user->getEmail()));
        $user->setUsernameCanonical($this->canonicalize($user->getUsername()));
    }

    /**
     * {@inheritdoc}
     */
    public function updatePassword(UserInterface $user): void
    {
        $this->passwordUpdater->hashPassword($user);
    }

    /**
     * @return PasswordUpdaterInterface
     */
    protected function getPasswordUpdater(): PasswordUpdaterInterface
    {
        return $this->passwordUpdater;
    }

    /**
     * @param $string
     *
     * @return string|null
     */
    protected function canonicalize($string): ?string
    {
        if (null === $string) {
            return null;
        }

        $encoding = mb_detect_encoding($string);

        return $encoding
            ? mb_convert_case($string, MB_CASE_LOWER, $encoding)
            : mb_convert_case($string, MB_CASE_LOWER);
    }
}
