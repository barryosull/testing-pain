<?php declare(strict_types=1);

namespace Barryosull\TestingPain\DBSeeding;

class EventListener
{
    /** @var callable[]  */
    private static array $listeners = [];

    public static function listenTo(string $event_class, callable $callback)
    {
        self::$listeners[$event_class] = self::$listeners[$event_class]  ?? [];

        self::$listeners[$event_class][] = $callback;
    }

    public static function handle($event)
    {
        $event_class = get_class($event);
        $listeners = self::$listeners[$event_class] ?? [];

        foreach ($listeners as $listener) {
            $listener($event);
        }
    }
}
