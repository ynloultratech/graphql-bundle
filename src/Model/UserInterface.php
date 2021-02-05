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

namespace Ynlo\GraphQLBundle\Model;

use Symfony\Component\Security\Core\User\UserInterface as SymfonyUserInterface;

interface UserInterface extends SymfonyUserInterface
{
    public const ROLE_DEFAULT = 'ROLE_USER';

    /**
     * Returns the user unique id.
     *
     * @return mixed
     */
    public function getId();

    /**
     * Sets the username.
     *
     * @param string $username
     *
     * @return static
     */
    public function setUsername(string $username): self;

    /**
     * Gets the canonical username in search and sort queries.
     *
     * @return string|null
     */
    public function getUsernameCanonical(): ?string;

    /**
     * Sets the canonical username.
     *
     * @param string $usernameCanonical
     *
     * @return static
     */
    public function setUsernameCanonical(string $usernameCanonical): self;

    /**
     * @param string|null $salt
     *
     * @return static
     */
    public function setSalt(?string $salt): self;

    /**
     * Gets email.
     *
     * @return string|null
     */
    public function getEmail(): ?string;

    /**
     * Sets the email.
     *
     * @param string $email
     *
     * @return static
     */
    public function setEmail(string $email): self;

    /**
     * Gets the canonical email in search and sort queries.
     *
     * @return string|null
     */
    public function getEmailCanonical(): ?string;

    /**
     * Sets the canonical email.
     *
     * @param string $emailCanonical
     *
     * @return static
     */
    public function setEmailCanonical(string $emailCanonical): self;

    /**
     * Gets the plain password.
     *
     * @return string|null
     */
    public function getPlainPassword(): ?string;

    /**
     * Sets the plain password.
     *
     * @param string|null $password
     *
     * @return static
     */
    public function setPlainPassword(?string $password): self;

    /**
     * Sets the hashed password.
     *
     * @param string|null $password
     *
     * @return static
     */
    public function setPassword(string $password): self;

    /**
     * @return bool
     */
    public function isEnabled(): bool;

    /**
     * @param bool $boolean
     *
     * @return static
     */
    public function setEnabled(bool $boolean): self;

    /**
     * Gets the confirmation token.
     *
     * @return string|null
     */
    public function getConfirmationToken(): ?string;

    /**
     * Sets the confirmation token.
     *
     * @param string|null $confirmationToken
     *
     * @return static
     */
    public function setConfirmationToken(?string $confirmationToken): self;

    /**
     * Sets the timestamp that the user requested a password reset.
     *
     * @param null|\DateTime $date
     *
     * @return static
     */
    public function setPasswordRequestedAt(?\DateTime $date = null): self;

    /**
     * Checks whether the password reset request has expired.
     *
     * @param int $ttl Requests older than this many seconds will be considered expired
     *
     * @return bool
     */
    public function isPasswordRequestNonExpired(int $ttl): bool;

    /**
     * Sets the last login time.
     *
     * @param \DateTime|null $time
     *
     * @return static
     */
    public function setLastLogin(?\DateTime $time = null): self;

    /**
     * Never use this to check if this user has access to anything!
     *
     * Use the AuthorizationChecker, or an implementation of AccessDecisionManager
     * instead, e.g.
     *
     *         $authorizationChecker->isGranted('ROLE_USER');
     *
     * @param string $role
     *
     * @return bool
     */
    public function hasRole(string $role): bool;

    /**
     * Sets the roles of the user.
     *
     * This overwrites any previous roles.
     *
     * @param array $roles
     *
     * @return static
     */
    public function setRoles(array $roles): self;

    /**
     * Adds a role to the user.
     *
     * @param string $role
     *
     * @return static
     */
    public function addRole(string $role): self;

    /**
     * Removes a role to the user.
     *
     * @param string $role
     *
     * @return static
     */
    public function removeRole(string $role): self;
}