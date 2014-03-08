<?php

namespace Beeblebrox3\Locker;

class LockerFacade extends \Illuminate\Support\Facades\Facade {
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor() { return 'locker'; }
}