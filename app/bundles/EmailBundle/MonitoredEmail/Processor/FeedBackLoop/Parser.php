<?php

/*
 * @copyright   2017 Mautic Contributors. All rights reserved
 * @author      Mautic, Inc.
 *
 * @link        https://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace Mautic\EmailBundle\MonitoredEmail\Processor\FeedBackLoop;

use Mautic\EmailBundle\MonitoredEmail\Exception\FeedBackLoopNotFound;
use Mautic\EmailBundle\MonitoredEmail\Message;
use Mautic\EmailBundle\MonitoredEmail\Processor\Address;

class Parser
{
    /**
     * @var Message
     */
    protected $message;

    /**
     * Parser constructor.
     *
     * @param Message $message
     */
    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    /**
     * @return int|null|string
     *
     * @throws FeedBackLoopNotFound
     */
    public function parse()
    {
        if (null === $this->message->fblReport) {
            throw new FeedBackLoopNotFound();
        }

        if ($email = $this->searchMessage('Original-Rcpt-To: (.*)', $this->message->fblReport)) {
            return $email;
        }

        if ($email = $this->searchMessage('Received:.*for (.*);.*?', $this->message->textPlain)) {
            return $email;
        }

        throw new FeedBackLoopNotFound();
    }

    /**
     * @param $content
     * @param $pattern
     *
     * @return int|null|string
     */
    protected function searchMessage($pattern, $content)
    {
        if (preg_match('/'.$pattern.'/i', $content, $match)) {
            if ($parsedAddressList = Address::parseList($match[1])) {
                return key($parsedAddressList);
            }
        }
    }
}
