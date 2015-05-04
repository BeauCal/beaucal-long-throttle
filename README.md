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
if ($throttle->takeLock('BiWeeklyReport', new DateTimeUnit(2, 'weeks'))) {
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
