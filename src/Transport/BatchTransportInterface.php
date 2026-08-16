<?php
declare(strict_types=1);
/**
 * Pop PHP Framework (https://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2027 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Mail\Transport;

use Pop\Mail\Message;

/**
 * Batch mail transport interface
 *
 * Optional companion to TransportInterface. A transport that can send many
 * messages via a provider's native bulk/batch API endpoint (instead of one
 * blocking call per message) should also implement this; Mailer prefers it
 * automatically when sending from a Queue or a directory of saved messages.
 *
 * @category   Pop
 * @package    Pop\Mail
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2027 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    5.0.0
 */
interface BatchTransportInterface
{

    /**
     * Send multiple messages in as few underlying calls as the transport supports
     *
     * @param  Message[] $messages
     * @return int number of messages sent
     */
    public function sendBatch(array $messages): int;

}
