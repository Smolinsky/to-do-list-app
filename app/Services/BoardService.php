<?php

namespace App\Services;

use App\DTO\Board\CreateBoardData;
use App\DTO\Board\UpdateBoardData;
use App\Models\Board;

class BoardService
{
    public function createBoard(CreateBoardData $boardData): Board
    {
        return $boardData->user->boards()->create($boardData->toAttributes());
    }

    public function updateBoard(Board $board, UpdateBoardData $boardData): Board
    {
        $board->update($boardData->toAttributes());

        return $board->refresh();
    }
}
