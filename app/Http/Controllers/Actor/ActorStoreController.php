<?php

namespace App\Http\Controllers\Actor;

use App\Data\Actor\ActorStoreData;
use App\Http\Requests\Actor\ActorStoreRequest;
use App\Services\Actor\ActorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;

class ActorStoreController extends Controller
{
    public function __construct(protected ActorService $actorService) {}

    public function __invoke(ActorStoreRequest $request): RedirectResponse
    {
        /** @var ActorStoreData $data */
        $data = $request->getDto();

        $this->actorService->store($data);

        return redirect()->route('actors.index');
    }
}
