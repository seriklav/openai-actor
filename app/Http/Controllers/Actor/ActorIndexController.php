<?php

namespace App\Http\Controllers\Actor;

use App\Data\Actor\ActorData;
use App\Http\Requests\Actor\ActorIndexRequest;
use App\Services\Actor\ActorService;
use Illuminate\Routing\Controller;

class ActorIndexController extends Controller
{
    public function __construct(protected ActorService $actorService) {}

    public function __invoke(ActorIndexRequest $request)
    {
        /** @var ActorData $data */
        $data = $request->getDto();

        $data->userId = auth()->id();

        $actors = $this->actorService->getList($data);

        return view('actors.index', compact('actors'));
    }
}
