<?php

namespace App\Filters;

class Filter extends QueryFilter
{
    /**
     * @param  string  $status
     */
    public function status(string $status)
    {
        $this->builder->where('status', strtolower($status));
    }

    /**
     * @param  string  $priority
     */
    public function priority(string $priority)
    {
        $this->builder->where('priority', strtolower($priority));
    }

    public function boardId(string $boardId): void
    {
        $this->builder->where('board_id', $boardId);
    }

    /**
     * @param  string  $field
     * @param  string  $direction
     */
    public function sort(string $field, string $direction = 'asc'): void
    {
        $allowedFields = ['created_at', 'due_date', 'position'];
        if (in_array($field, $allowedFields)) {
            $this->builder->orderBy($field, $this->direction($direction));
        }
    }

    public function direction(string $direction): string
    {
        return in_array(strtolower($direction), ['asc', 'desc']) ? strtolower($direction) : 'asc';
    }
}
