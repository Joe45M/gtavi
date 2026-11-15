<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\CalculateDownloadRequest;
use App\Services\CalculatorService;
use Illuminate\Http\JsonResponse;

final readonly class CalculatorsController
{
    public function downloadEstimate(
        CalculateDownloadRequest $request,
        CalculatorService $calculator,
    ): JsonResponse {
        $validated = $request->validated();

        $result = $calculator->calculateDownloadTime(
            speedValue: (float) $validated['speed'],
            unit: (string) $validated['unit'],
            sizeGb: (float) $validated['size_gb'],
            overheadPercent: (float) ($validated['overhead_percent'] ?? 8.0),
            dataCapGb: isset($validated['data_cap_gb']) ? (int) $validated['data_cap_gb'] : null,
        );

        return response()->json([
            'data' => $result,
            'disclaimer' => 'Projected storage calculation only; GTAVI.PRO is not affiliated with Rockstar Games or Take-Two Interactive.',
        ]);
    }
}
