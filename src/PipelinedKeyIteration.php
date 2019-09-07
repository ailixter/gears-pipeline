<?php
/*
 * TODO Description
 * (C) 2019, AII (Alexey Ilyin)
 */

namespace Ailixter\Gears;

class PipelinedKeyIteration extends PipelinedIteration
{
    protected function getRowItem(\Iterator $data)
    {
        return [$data->key(), $data->current()];
    }
}

