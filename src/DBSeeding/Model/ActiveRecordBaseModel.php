<?php declare(strict_types=1);

namespace Barryosull\TestingPain\DBSeeding\Model;

class ActiveRecordBaseModel
{
    public function store()
    {
        // details omitted ...
        $this->recordStored();
    }

    protected function recordStored($dirtyData = null)
    {
        // details omitted ...
    }
}
