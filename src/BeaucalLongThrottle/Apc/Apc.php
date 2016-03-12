<?php

namespace BeaucalLongThrottle\Apc;

/**
 * APC functions once-removed so they can be mocked out.
 */
class Apc {

    public function add($key, $var, $ttl) {
        return apc_add($key, $var, $ttl);
    }

    public function fetch($key) {
        return apc_fetch($key);
    }

    public function delete($key) {
        return apc_delete($key);
    }

}
