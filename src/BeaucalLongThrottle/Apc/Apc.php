<?php

namespace BeaucalLongThrottle\Apc;

/**
 * APC functions once-removed so they can be mocked out.
 */
class Apc {

    public function add($key, $var, $ttl) {
        $method = $this->apcMethod('add');
        return $method($key, $var, $ttl);
    }

    public function fetch($key) {
        $method = $this->apcMethod('fetch');
        return $method($key);
    }

    public function delete($key) {
        $method = $this->apcMethod('delete');
        return $method($key);
    }

    protected function apcMethod($method) {
        static $map = [
            'apc' => [
                'add' => 'apc_add',
                'fetch' => 'apc_fetch',
                'delete' => 'apc_delete'
            ],
            'apcu' => [
                'add' => 'apcu_add',
                'fetch' => 'apcu_fetch',
                'delete' => 'apcu_delete'
            ],
        ];
        $ext = extension_loaded('apcu') ? 'apcu' : 'apc';
        return $map[$ext][$method];
    }

}
