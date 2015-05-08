<?php

namespace BeaucalLongThrottle\Adapter;

use DateTime;
use BeaucalLongThrottle\Exception;

/**
 * Allows more than one specific lock,
 * as specified in 'regex_counts'.
 */
class DbMultiple extends Db {

    protected $lastInsertedIx;

    /**
     * @param string $key
     */
    public function clearExpiredLock($key = null) {
        parent::clearExpiredLock($key);
        if ($key) {
            $currDate = date($this->options->getDbDateTimeFormat());
            $delete = $this->gateway->getSql()->delete();
            $delete->where->lessThanOrEqualTo('end_datetime', $currDate)
            ->and->like('key', "%{$this->separator}{$key}");
            $this->gateway->deleteWith($delete);
        }
    }

    /**
     * @param string $key
     * @param DateTime $endDate
     * @return bool
     */
    public function setLock($key, DateTime $endDate) {
        $this->lastInsertedIx = null;

        /**
         * Try to grab a unique slot.
         */
        $count = $this->getLockCountAllowed($key);
        $tryIxs = [1];
        if ($count > 1) {
            $select = $this->gateway->getSql()->select();
            $select->where->like('key', "%{$this->separator}{$key}");
            $locks = [];
            foreach ($this->gateway->selectWith($select) as $lock) {
                $lockKey = current(explode($this->separator, $lock->key, 2));
                if ($lockKey) {
                    $locks[] = $lockKey;
                }
            }
            $tryIxs = array_diff(range(1, $count), $locks);
        }
        foreach ($tryIxs as $ix) {
            try {
                $insertResult = $this->gateway->insert([
                    'key' => "{$ix}{$this->separator}{$key}",
                    'end_datetime' => $endDate->format(
                    $this->options->getDbDateTimeFormat()
                    )
                ]);
                if ($insertResult) {
                    $this->lastInsertedIx = $ix;
                    return true;
                }
            } catch (\Exception $e) {

            }
        }
        $this->lastInsertedIx = null;
        return false;
    }

    /**
     * @param string $key
     * @return int
     */
    protected function getLockCountAllowed($key) {
        if (!$this->options->getRegexCounts()) {
            throw new Exception\OptionException(
            'If regex_counts not specified, you should use Adapter\Db instead'
            );
        }
        $count = 1;
        foreach ($this->options->getRegexCounts() as $regex => $currCount) {
            if ($currCount < 1) {
                throw new Exception\OptionException("regex_counts must be positive");
            }
            if (@preg_match($regex, null) === false) {
                throw new Exception\OptionException("regex_counts pattern {$regex} is invalid");
            }
            if (preg_match($regex, $key)) {
                $count = $currCount;
                break;
            }
        }
        return $count;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function verifyLock($key) {
        return (bool) $this->gateway->select([
            'key' => "{$this->lastInsertedIx}{$this->separator}{$key}"
        ])->count();
    }

}
