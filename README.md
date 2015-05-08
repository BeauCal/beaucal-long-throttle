# BeaucalLongThrottle
Prevent an action for some long amount of time.  Hours, day, months, years.

### Installation
1. In `application.config.php`, add as follows:

```PHP
'modules' => [..., 'BeaucalLongThrottle', ...];
```

2. Import into your database `data/beaucal_throttle.sql`:
```SQL
CREATE TABLE IF NOT EXISTS `beaucal_throttle` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `key` varchar(255) NOT NULL UNIQUE KEY,
  `end_datetime` DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
ALTER TABLE `beaucal_throttle` ADD INDEX (`end_datetime`);
```


### To Use

Either you get the lock or you don't.

```PHP
// in controller
$throttle = $this->getServiceLocator()->get('BeaucalLongThrottle');

// term?
$term = new DateTimeUnit(2, 'weeks'); // or
$term = new DateTimeEnd(new DateTime('+2 weeks'));

if ($throttle->takeLock('BiWeeklyReport', $term)) {
    // lock is taken atomically, made for 2 weeks: safe to do your work
}
else {
    // locked from before: leave it alone & perhaps try again later
}

/**
 * N.B. May throw \BeaucalLongThrottle\Exception\PhantomLockException, when
 * lock is reported to be set but upon verification step is actually not.
 * This is truly exceptional and shouldn't be just thrown aside.
 */
```


### Allow More Than One Lock
You can allow any number of locks e.g. 5/hour, 100/day.  Here's how:

```PHP
// in beaucallongthrottle.global.php
$throttle = [
// ...
    'adapter_class' => 'BeaucalLongThrottle\Adapter\DbMultiple', // was Adapter\Db
// ...
]
$regexCounts = [
    /**
     * E.g. You can create 3 'do-stuff' locks before the lock can't be taken.
     * Those not matching here are allowed the usual 1.
     */
    '/^do-stuff$/' => 2
];

// in controller
$throttle->takeLock('do-stuff', new DateTimeUnit(1, 'day')); // TRUE
$throttle->takeLock('do-stuff', new DateTimeUnit(1, 'day')); // TRUE
$throttle->takeLock('do-stuff', new DateTimeUnit(1, 'day')); // FALSE
// ...
// A DAY LATER
$throttle->takeLock('do-stuff', new DateTimeUnit(1, 'day')); // TRUE
```
