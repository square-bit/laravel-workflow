<?php

namespace Squarebit\Workflows\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $workflow_id
 * @property string $code
 * @property string $description
 */
class WorkflowStatus extends Model
{
    protected $table = 'workflow_statuses';

    public function __toString()
    {
        return '('.$this->id.')'.$this->code;
    }
}
