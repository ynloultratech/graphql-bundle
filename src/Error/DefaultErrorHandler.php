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

use GraphQL\Language\AST\FieldNode;
use Psr\Log\LoggerInterface;

class DefaultErrorHandler implements ErrorHandlerInterface
{
    /**
     * @var LoggerInterface|null
     */
    protected $logger;

    /**
     * DefaultErrorHandler constructor.
     *
     * @param LoggerInterface $logger
     */
    public function __construct(?LoggerInterface $logger = null)
    {
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function handle(array $errors, ErrorFormatterInterface $errorFormatter, $debug = false): array
    {
        $formattedErrors = [];
        foreach ($errors as $error) {
            $formattedError = $errorFormatter->format($error, $debug);

            if (null !== $this->logger) {
                $originError = $error->getTrace()[0]['args'][0] ?? null;

                $context = [];
                if ($originError instanceof \Exception) {
                    $context = [
                        'file' => $originError->getFile(),
                        'line' => $originError->getLine(),
                        'error' => get_class($originError),
                        'trace' => $originError->getTraceAsString(),
                    ];
                }
                $message = $error->getMessage();
                if ($trackingId = $formattedError['tracking_id'] ?? null) {
                    $message = sprintf('(%s) %s', $trackingId, $message);
                }

                if ($originError instanceof FieldNode) {
                    $this->logger->debug($message, $context); //graphql error
                } elseif ($error->isClientSafe()) {
                    $this->logger->notice($message, $context); // client aware error
                } else {
                    $this->logger->critical($message, $context); //unhandled exception
                }
            }

            $formattedErrors[] = $formattedError;
        }

        return $formattedErrors;
    }
}
