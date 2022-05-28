<?php declare(strict_types=1);

namespace Barryosull\TestingPain\DBSeeding;

class EventListener
{
    /** @var callable[]  */
    private array $listeners = [];

    public function listenTo(string $event_class, callable $callback)
    {
        $this->listeners[$event_class] = $this->listeners[$event_class]  ?? [];

        $this->listeners[$event_class][] = $callback;
    }

    public function broadcast($event)
    {
        $event_class = get_class($event);
        $listeners = $this->listeners[$event_class] ?? [];

        foreach ($listeners as $listener) {
            $listener($event);
        }
    }
}