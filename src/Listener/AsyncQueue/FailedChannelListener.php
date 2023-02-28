<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace App\Listener\AsyncQueue;

use Hyperf\AsyncQueue\Event\QueueLength;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;

#[Listener]
class FailedChannelListener implements ListenerInterface
{
    /**
     * @var string[]
     */
    protected $channels = [
        'failed',
    ];

    protected int $lengthCheckCount = 1000;

    public function __construct(protected StdoutLoggerInterface $logger)
    {
    }

    public function listen(): array
    {
        return [
            QueueLength::class,
        ];
    }

    /**
     * @param QueueLength $event
     */
    public function process(object $event): void
    {
        if (!$event instanceof QueueLength) {
            return;
        }

        if (!in_array($event->key, $this->channels)) {
            return;
        }

        if ($event->length < $this->lengthCheckCount) {
            return;
        }

        $event->driver->flush();
        $this->logger->info(sprintf('%s channel flush %d messages success.', $event->key, $event->length));

//        $event->driver->reload();
//        $this->logger->info(sprintf('%s channel reload %d messages to waiting channel success.', $event->key, $event->length));
    }
}
