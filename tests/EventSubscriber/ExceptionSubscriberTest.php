<?php

namespace Tests\lepiaf\SapientBundle\EventSubscriber;

use lepiaf\SapientBundle\EventSubscriber\ExceptionSubscriber;
use lepiaf\SapientBundle\Exception\NoKeyFoundForRequesterException;
use lepiaf\SapientBundle\Exception\RequesterHeaderMissingException;
use lepiaf\SapientBundle\Exception\SignerHeaderMissingException;
use lepiaf\SapientBundle\Exception\VerifyRequestException;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ExceptionSubscriberTest extends TestCase
{
    public function provideData()
    {
        return [
            [NoKeyFoundForRequesterException::class],
            [RequesterHeaderMissingException::class],
            [SignerHeaderMissingException::class],
            [VerifyRequestException::class],
        ];
    }

    /**
     * @dataProvider provideData
     */
    public function testChangeException($exceptionClassName)
    {
        $exception = $this->prophesize($exceptionClassName);

        $event = $this->prophesize(GetResponseForExceptionEvent::class);
        $event->getException()->shouldBeCalled()->willReturn($exception->reveal());
        $event->setException(Argument::type(BadRequestHttpException::class))->shouldBeCalled();
        $event->stopPropagation()->shouldBeCalled();

        $eventSubscriber = new ExceptionSubscriber();
        $eventSubscriber->handleException($event->reveal());
    }

    public function testExceptionNotHandled()
    {
        $exception = $this->prophesize(\Exception::class);

        $event = $this->prophesize(GetResponseForExceptionEvent::class);
        $event->getException()->shouldBeCalled()->willReturn($exception->reveal());
        $event->setException()->shouldNotBeCalled();
        $event->stopPropagation()->shouldNotBeCalled();

        $eventSubscriber = new ExceptionSubscriber();
        $eventSubscriber->handleException($event->reveal());
    }

    public function testSubscriberRegistration()
    {
        $this->assertSame([
            'kernel.exception' => 'handleException'
        ], ExceptionSubscriber::getSubscribedEvents());
    }
}
