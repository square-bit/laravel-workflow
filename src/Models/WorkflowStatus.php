<?php

namespace Squarebit\Workflows\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $workflow_id
 * @property string $name
 * @property string $description
 */
class WorkflowStatus extends Model
{
    protected $table = 'workflows_statuses';

    protected $guarded = ['id'];

    public static function findWithName(string $name): ?WorkflowStatus
    {
        return self::where('name', $name)->first();
    }

    public function __toString()
    {
        return '('.$this->id.')'.$this->code;
    }
}
