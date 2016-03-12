# BeaucalLongThrottle

[![Build Status](https://travis-ci.org/BeauCal/beaucal-long-throttle.svg?branch=master)](https://travis-ci.org/BeauCal/beaucal-long-throttle)

**Now with 100% code coverage.**

Prevent an action for some amount of time.  Hours, day, months, years, anything.
Allows for multiple locks (e.g. 100/day) and clearing/releasing a lock just made.
And it works just like it should, every single lock lasts exactly how long you specify.

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


### Allow Multiple Locks
You can allow any number of locks e.g. 'lock1' => 5/hour, 'lock2' => 100/day.  Here's how:

```PHP
// copy beaucallongthrottle.global.php to your config/autoload/
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
    '/^do-stuff$/' => 3
];

// in controller
$throttle->takeLock('do-stuff', new DateTimeUnit(1, 'day')); // YES
$throttle->takeLock('do-stuff', new DateTimeUnit(1, 'day')); // YES
$throttle->takeLock('do-stuff', new DateTimeUnit(1, 'day')); // YES
$throttle->takeLock('do-stuff', new DateTimeUnit(1, 'day')); // FALSE
// ...
// A DAY LATER
$throttle->takeLock('do-stuff', new DateTimeUnit(1, 'day')); // YES
```

### Clearing Locks

```PHP
$handle = $throttle->takeLock('year-end', new DateTimeUnit(1, 'year')); // YES
$throttle->takeLock('year-end', new DateTimeUnit(1, 'year')); // FALSE
if ($whoopsBackingOut) {
    $throttle->clearLock($handle);
}
$throttle->takeLock('year-end', new DateTimeUnit(1, 'year')); // YES
```
