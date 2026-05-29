<?php

namespace App\Http\Controllers\Api;

use App\DTO\Board\CreateBoardData;
use App\DTO\Board\UpdateBoardData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Board\CreateBoardRequest;
use App\Http\Requests\Board\UpdateBoardRequest;
use App\Http\Resources\BoardResource;
use App\Models\Board;
use App\Models\User;
use App\Services\BoardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class BoardController extends Controller
{
    public function __construct(
        private readonly BoardService $boardService
    ) {
    }

    public function getBoards()
    {
        /** @var User $user */
        $user = Auth::user();

        $boards = $user->boards()
            ->withCount('tasks')
            ->paginate(10);

        return BoardResource::collection($boards);
    }

    public function createBoard(CreateBoardRequest $request): JsonResponse
    {
        /** @var CreateBoardData $boardData */
        $boardData = $request->getDTO();

        $board = $this->boardService->createBoard($boardData);

        return BoardResource::make($board)
            ->response()
            ->setStatusCode(201);
    }

    public function getBoard(Board $board): BoardResource
    {
        Gate::authorize('check-board', $board);

        $board->load([
            'tasks' => fn($query) => $query
                ->withCount('attachments')
                ->orderBy('position'),
        ]);

        return new BoardResource($board);
    }

    public function updateBoard(UpdateBoardRequest $request, Board $board): BoardResource
    {
        Gate::authorize('check-board', $board);

        /** @var UpdateBoardData $boardData */
        $boardData = $request->getDTO();

        $board = $this->boardService->updateBoard($board, $boardData);

        return new BoardResource($board);
    }

    public function deleteBoard(Board $board): JsonResponse
    {
        Gate::authorize('check-board', $board);

        $board->delete();

        return response()->json([
            'message' => 'Successfully deleted',
        ]);
    }
}
