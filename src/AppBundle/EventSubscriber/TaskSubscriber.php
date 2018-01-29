<?php

namespace AppBundle\EventSubscriber;

use AppBundle\Entity\Delivery;
use AppBundle\Entity\TaskEvent;
use AppBundle\Event\TaskAssignEvent;
use AppBundle\Event\TaskCreateEvent;
use AppBundle\Event\TaskDoneEvent;
use AppBundle\Service\DeliveryManager;
use Doctrine\Bundle\DoctrineBundle\Registry as DoctrineRegistry;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class TaskSubscriber implements EventSubscriberInterface
{
    private $doctrine;
    private $deliveryManager;

    public function __construct(DoctrineRegistry $doctrine, DeliveryManager $deliveryManager)
    {
        $this->doctrine = $doctrine;
        $this->deliveryManager = $deliveryManager;
    }

    public static function getSubscribedEvents()
    {
        return [
            TaskAssignEvent::NAME => 'onTaskAssign',
            TaskCreateEvent::NAME => 'onTaskCreate',
            TaskDoneEvent::NAME   => 'onTaskDone',
        ];
    }

    public function onTaskAssign(TaskAssignEvent $event)
    {
        $task = $event->getTask();
        $user = $event->getUser();

        $taskEvent = new TaskEvent($task, 'ASSIGN');

        $this->doctrine
            ->getManagerForClass(TaskEvent::class)
            ->persist($taskEvent);

        $this->doctrine
            ->getManagerForClass(TaskEvent::class)
            ->flush();

        if (null !== $task->getDelivery()) {
            $this->deliveryManager->dispatch($task->getDelivery(), $user);

            $this->doctrine
                ->getManagerForClass(Delivery::class)
                ->flush();
        }
    }

    public function onTaskCreate(TaskCreateEvent $event)
    {
        $task = $event->getTask();

        $taskEvent = new TaskEvent($task, 'CREATE');

        $this->doctrine
            ->getManagerForClass(TaskEvent::class)
            ->persist($taskEvent);

        $this->doctrine
            ->getManagerForClass(TaskEvent::class)
            ->flush();
    }

    public function onTaskDone(TaskDoneEvent $event)
    {
        $task = $event->getTask();

        $taskEvent = new TaskEvent($task, 'DONE');

        $this->doctrine
            ->getManagerForClass(TaskEvent::class)
            ->persist($taskEvent);

        $this->doctrine
            ->getManagerForClass(TaskEvent::class)
            ->flush();
    }
}
