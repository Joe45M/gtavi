<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\V1\GameEditionResource;
use App\Http\Resources\V1\PlatformSpecResource;
use App\Services\MicrositeDataService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final readonly class MicrositeDataController
{
    public function editions(MicrositeDataService $data): AnonymousResourceCollection
    {
        return GameEditionResource::collection($data->editions());
    }

    public function platforms(Request $request, MicrositeDataService $data): AnonymousResourceCollection
    {
        $family = $request->query('family');
        $familyFilter = in_array($family, ['console', 'pc'], true) ? $family : null;

        return PlatformSpecResource::collection($data->platformSpecs($familyFilter));
    }
}
