<?php

namespace App\Http\Controllers;

use App\DTOs\Chart\ChartDto;
use App\Http\Requests\Chart\ChartRequest;
use App\Http\Services\ChartsService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ChartController extends Controller
{

    private ChartsService $chartsService;

    public function __construct(ChartsService $chartsService)
    {
        $this->chartsService = $chartsService;
    }

    public function getPerceptionTrendByGroup(ChartRequest $request): Collection
    {
        $requestDto = ChartDto::fromRequest($request);
        return $this->chartsService->getPerceptionTrendByGroup($requestDto);
    }

    public function getPerceptionTrend(ChartRequest $request): Collection
    {
        $requestDto = ChartDto::fromRequest($request);
        return $this->chartsService->getPerceptionTrend($requestDto);
    }

    public function getAverageByQuestionAndGroup(ChartRequest $request): Collection
    {
        $requestDto = ChartDto::fromRequest($request);
        return $this->chartsService->getAverageByQuestionAndGroup($requestDto);
    }

    public function getPerceptionByGroup(ChartRequest $request): Collection
    {
        $requestDto = ChartDto::fromRequest($request);
        return $this->chartsService->getPerceptionByGroup($requestDto);
    }

    public function getVolumeByGroup(ChartRequest $request): Collection
    {
        $requestDto = ChartDto::fromRequest($request);
        return $this->chartsService->getVolumeOfFeedbackByGroup($requestDto);
    }

    public function getVolumeTrendByGroup(ChartRequest $request): Collection
    {
        $requestDto = ChartDto::fromRequest($request);
        return $this->chartsService->getVolumeOfFeedbackTrendByGroup($requestDto);
    }

    public function getDistributionByGroup(ChartRequest $request): Collection
    {
        $requestDto = ChartDto::fromRequest($request);
        return $this->chartsService->getDistributionByGroupAndSatisfaction($requestDto);
    }

    public function getRankingOfGroup(ChartRequest $request): Collection
    {
        $requestDto = ChartDto::fromRequest($request);
        return $this->chartsService->getRankingOfGroup($requestDto);
    }

    public function getRespondentByGroup(ChartRequest $request): Collection
    {
        $requestDto = ChartDto::fromRequest($request);
        return $this->chartsService->getVolumeOfFeedbackByRespondentType($requestDto);
    }
}
