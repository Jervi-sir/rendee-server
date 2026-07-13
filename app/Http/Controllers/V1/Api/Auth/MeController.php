<?php

namespace App\Http\Controllers\V1\Api\Auth;

use App\Http\Controllers\Controller;
use App\Services\V1\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MeController extends Controller
{
  protected UserService $userService;

  public function __construct(UserService $userService)
  {
    $this->userService = $userService;
  }

  public function __invoke(Request $request): JsonResponse
  {
    $user = $request->user();

    if ($user) {
      $this->userService->loadProfileRelations($user);
    }

    return response()->json([
      'user' => $user,
    ]);
  }
}
