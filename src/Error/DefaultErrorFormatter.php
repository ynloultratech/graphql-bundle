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
use Symfony\Component\HttpFoundation\Response;
use Ynlo\GraphQLBundle\Exception\ControlledErrorInterface;
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

        if (!isset($formattedError['tracking_id'])) {
            $originError = $error->getTrace()[0]['args'][0] ?? null;

            $trackingId = null;
            $errorCode = Response::HTTP_INTERNAL_SERVER_ERROR;
            if ($originError instanceof \Exception) {
                if ($originError instanceof ControlledErrorInterface) {
                    $errorCode = $originError->getCode();
                    if (!$originError->getMessage() && $this->errorManager->has($errorCode)) {
                        $formattedError['message'] = $this->errorManager->get($errorCode)->getMessage();
                    }

                } elseif ($originError instanceof ClientAware && $originError->isClientSafe()) {
                    $errorCode = null;
                }

                $trackingId = Uuid::createFromData(
                    [
                        'message' => $originError->getMessage(),
                        'code' => $originError->getCode(),
                        'file' => $originError->getFile(),
                        'line' => $originError->getLine(),
                    ]
                );

            } elseif ($originError instanceof FieldNode) {
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
        }

        return $formattedError;
    }
}
