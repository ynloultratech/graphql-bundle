<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\GraphiQL;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;

/**
 * Base for authentication mechanisms to allow use secures API
 */
interface GraphiQLAuthenticationProviderInterface
{
    /**
     * The authentication require user enter some data,
     * e.g. username, password, etc
     *
     * @return bool
     */
    public function requireUserData(): bool;

    /**
     * @param FormBuilderInterface $builder
     *
     * @return mixed
     */
    public function buildUserForm(FormBuilderInterface $builder);

    /**
     * Process the entered used data to login
     *
     * @param null|FormInterface $form
     * @throws AuthenticationFailedException
     */
    public function login(?FormInterface $form = null);

    /**
     * Logout
     */
    public function logout();

    /**
     * @return bool
     */
    public function isAuthenticated(): bool;

    /**
     * @param GraphiQLRequest $request
     */
    public function prepareRequest(GraphiQLRequest $request);
}
