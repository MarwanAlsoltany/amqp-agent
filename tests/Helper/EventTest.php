<?php

namespace MAKS\AmqpAgent\Tests\Helper;

use MAKS\AmqpAgent\Tests\TestCase;
use MAKS\AmqpAgent\Helper\Event;

class EventTest extends TestCase
{
    public function testEventDispatchAndListenMethods()
    {
        $this->assertEmpty(Event::getEvents());

        Event::dispatch('test.event.0', ['no', 'one', 'will', 'listen', 'for', 'this']);

        Event::listen('test.event.1', function ($string) {
            $this->assertEquals('var1', $string);
        });
        Event::listen('test.event.2', function ($string, $array) {
            $this->assertEquals('var2', $string);
            $this->assertArrayHasKey('var2A', $array);
            $this->assertContains(7, $array);
        });

        Event::dispatch('test.event.1', ['var1']);
        Event::dispatch('test.event.2', ['var2', ['var2A' => 7]]);

        $events = Event::getEvents();
        $this->assertNotEmpty($events);
        $this->assertArrayHasKey('test.event.1', $events);
        $this->assertArrayHasKey('test.event.2', $events);
    }
}
