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
```


### To Use

```PHP
// in controller
$throttle = $this->getServiceLocator()->get('BeaucalLongThrottle\Throttle');
if ($throttle->takeLock('MonthlyMailingUser12345', new DateTimeUnit(1, 'month'))) {
    // mail it out & lock is in place for another month
}
else {
    // locked from before, must skip & try again later
}
```
