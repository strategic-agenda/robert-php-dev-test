<?php

namespace Kirilmaz\Interview\Observers;

class Event implements \SplSubject {
    protected \SplObjectStorage $storage;

    public function __construct()
    {
        $this->storage = new \SplObjectStorage();
    }

    public function attach(\SplObserver $observer): void {
        $this->storage->attach($observer);
    }

    public function detach(\SplObserver $observer): void {
        if (!$this->storage->contains($observer)) {
            return;
        }

        $this->storage->detach($observer);
    }

    public function notify(): void {
        if (!count($this->storage)) {
            return;
        }

        foreach ($this->storage as $observer) {
            $observer->update($this);
        }
    }
}
