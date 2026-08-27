<?php

namespace App\Http\Controllers;

use App\Http\Resources\CampaignResource;
use App\Services\CampaignService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CampaignController extends Controller
{
    public function __construct(private readonly CampaignService $campaigns) {}

    public function index(): AnonymousResourceCollection
    {
        return CampaignResource::collection($this->campaigns->list());
    }

    public function candidates(Request $request): array
    {
        $data = $request->validate([
            'months' => ['nullable', 'integer', 'min:1', 'max:36'],
            'q' => ['nullable', 'string'],
        ]);

        return $this->campaigns->candidates(
            (int) ($data['months'] ?? 6),
            $data['q'] ?? null,
        );
    }

    public function store(Request $request): CampaignResource
    {
        $data = $request->validate([
            'name' => ['nullable', 'string'],
            'months' => ['nullable', 'integer', 'min:1', 'max:36'],
            'customerIds' => ['required', 'array', 'min:1'],
            'customerIds.*' => ['integer'],
            'message' => ['nullable', 'string'],
        ]);

        $campaign = $this->campaigns->create(
            trim((string) ($data['name'] ?? '')),
            (int) ($data['months'] ?? 6),
            $data['customerIds'],
            $data['message'] ?? null,
        );

        return new CampaignResource($campaign);
    }
}
