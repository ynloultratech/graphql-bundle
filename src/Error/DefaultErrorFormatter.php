<?php
/*******************************************************************************
 *  This file is part of the GraphQL Bundle package.
 *
 *  (c) YnloUltratech <support@ynloultratech.com>
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 ******************************************************************************/

namespace Ynlo\GraphQLBundle\Error;

use GraphQL\Error\ClientAware;
use GraphQL\Error\Error;
use GraphQL\Error\FormattedError;
use GraphQL\Language\AST\FieldNode;
use GraphQL\Type\Schema;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Ynlo\GraphQLBundle\Exception\Controlled\ValidationError;
use Ynlo\GraphQLBundle\Exception\ControlledErrorInterface;
use Ynlo\GraphQLBundle\Exception\ControlledErrorWithPropertiesInterface;
use Ynlo\GraphQLBundle\Util\Uuid;

class DefaultErrorFormatter implements ErrorFormatterInterface
{
    /**
     * @var ControlledErrorManager
     */
    protected $errorManager;

    /**
     * DefaultErrorFormatter constructor.
     *
     * @param ControlledErrorManager $errorManager
     */
    public function __construct(ControlledErrorManager $errorManager)
    {
        $this->errorManager = $errorManager;
    }

    /**
     * @inheritDoc
     */
    public function format(Error $error, $debug = false): array
    {
        $formattedError = FormattedError::createFromException($error, $debug);

        $originError = $error->getPrevious();

        $trackingId = null;
        $errorCode = Response::HTTP_INTERNAL_SERVER_ERROR;
        if ($originError instanceof \Throwable) {
            if ($originError instanceof ControlledErrorInterface) {
                $errorCode = $originError->getCode();
                if (!$originError->getMessage() && $this->errorManager->has($errorCode)) {
                    $formattedError['message'] = $this->errorManager->get($errorCode)->getMessage();
                }

            } elseif ($originError instanceof ClientAware && $originError->isClientSafe()) {
                $errorCode = 400;
            } elseif ($originError instanceof HttpException) {
                $errorCode = $originError->getStatusCode();
                $formattedError['message'] = Response::$statusTexts[$errorCode] ?? $formattedError['message'];
                $formattedError['debugMessage'] = $originError->getMessage() ?: $formattedError['message'];
            }

            if ($originError instanceof ControlledErrorWithPropertiesInterface) {
                foreach ($originError->getProperties() as $name => $value) {
                    if (!in_array($name, ['message', 'debugMessage', 'code', 'tracking_id'])) {
                        $formattedError[$name] = $value;
                    }
                }
            }

            $trackingId = Uuid::createFromData(
                [
                    'message' => $originError->getMessage(),
                    'code' => $originError->getCode(),
                    'file' => $originError->getFile(),
                    'line' => $originError->getLine(),
                ]
            );

        } elseif ($originError instanceof FieldNode || $originError instanceof Schema) {
            $errorCode = Response::HTTP_BAD_REQUEST;
            $trackingId = Uuid::createFromData($formattedError['message'] ?? null);
        }

        $formattedError = array_merge(
            [
                'code' => $errorCode,
                'tracking_id' => $trackingId,
            ],
            $formattedError
        );

        return $formattedError;
    }
}
