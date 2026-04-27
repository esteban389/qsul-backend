<?php

namespace App\Http\Services;

use App\DTOs\Chart\ChartDto;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use App\DTOs\Auth\UserRole;

class ChartsService
{
    private array $validGroupings = [
        'campuses' => ['field' => 'c.id', 'label' => 'c.name', 'image' => 'c.icon'],
        'processes' => ['field' => 'p.id', 'label' => 'p.name', 'image' => 'p.icon'],
        'services' => ['field' => 's.id', 'label' => 's.name', 'image' => 's.icon'],
        'employees' => ['field' => 'e.id', 'label' => 'e.name', 'image' => 'e.avatar'],
    ];

    /**
     * Build an inclusive date range for chart queries.
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    private function getDateRange(ChartDto $request): array
    {
        return [
            Carbon::parse($request->start_date)->startOfDay(),
            Carbon::parse($request->end_date)->endOfDay(),
        ];
    }

    private function getTimeFormat(string $timeFrame): string
    {
        $driver = DB::connection()->getDriverName();
        $isSqlite = $driver === 'sqlite';

        return match ($timeFrame) {
            'month' => $isSqlite
                ? "strftime('%Y-%m', a.created_at)"
                : "DATE_FORMAT(a.created_at, '%Y-%m')",
            'year' => $isSqlite
                ? "strftime('%Y', a.created_at)"
                : "DATE_FORMAT(a.created_at, '%Y')",
            default => $isSqlite
                ? "strftime('%Y', a.created_at)"
                : "DATE_FORMAT(a.created_at, '%Y')",
        };
    }

    private function getBaseQuery(): Builder
    {
        $user = auth()->user();
        $query = DB::table('answers as a')
            ->join('employee_service as em_se', 'a.employee_service_id', '=', 'em_se.id')
            ->join('employees as e', 'em_se.employee_id', '=', 'e.id')
            ->join('campuses as c', 'e.campus_id', '=', 'c.id')
            ->join('services as s', 'em_se.service_id', '=', 's.id')
            ->join('processes as p', 's.process_id', '=', 'p.id')
            ->join('surveys as sv', 'a.survey_id', '=', 'sv.id')
            ->whereNull('a.deleted_at');

        if ($user->hasRole(UserRole::CampusCoordinator)) {
            $query->where('c.id', $user->campus_id);
        }

        if ($user->hasRole(UserRole::ProcessLeader)) {
            $query->where('p.id', $user->employee()->first()->process_id);
            $query->where('c.id', $user->campus_id);
        }

        return $query;
    }

    /**
     * Get the perception (average of the answer's average value) trend (over time) by group.
     * @param ChartDto $request
     * @return Collection
     */
    public function getPerceptionTrendByGroup(ChartDto $request): Collection
    {
        $group = $this->validGroupings[$request->group_by] ?? $this->validGroupings['campuses'];

        $timeFormat = $this->getTimeFormat($request->time_frame);

        $query = $this
            ->getBaseQuery()
            ->selectRaw("
                $timeFormat as period,
                {$group['field']} as group_id,
                {$group['label']} as group_name,
                ROUND(AVG(a.average),2) as average_perception
            ")
            ->groupByRaw("$timeFormat, {$group['field']}, {$group['label']}")
            ->orderBy('period')
            ->where('sv.id', $request->survey);

        if ($request->campus) {
            $query->where('c.id', $request->campus);
        }

        if ($request->process) {
            $query->where('p.id', $request->process);
        }

        if ($request->service && !$request->employee) {
            $query->where('s.id', $request->service);
        }

        if ($request->employee) {
            $query->where('e.id', $request->employee);
        }

        $results = collect($query
            ->whereBetween('a.created_at', $this->getDateRange($request))
            ->get());

        $data = $results->groupBy('period')->map(function ($group, $period) {
            $data = [
                'period' => $period,
            ];
            foreach ($group as $item) {
                $data[$item->group_name] = $item->average_perception;
            }
            return $data;
        })->values();
        $labels = $results->map(fn($item) => $item->group_name)->unique()->values();
        return collect([
            'labels' => $labels,
            'data' => $data,
        ]);
    }

    /**
     * Get the perception (average of the answer's average value) trend (over time).
     * @param ChartDto $request
     * @return Collection
     */
    public function getPerceptionTrend(ChartDto $request): Collection
    {
        $timeFormat = $this->getTimeFormat($request->time_frame);

        $query = $this
            ->getBaseQuery()
            ->selectRaw("
                $timeFormat as period,
                ROUND(AVG(a.average),2) as average_perception
            ")
            ->groupByRaw($timeFormat)
            ->orderBy('period')
            ->whereBetween('a.created_at', $this->getDateRange($request))
            ->where('sv.id', $request->survey);

        if ($request->campus) {
            $query->where('c.id', $request->campus);
        }

        if ($request->process) {
            $query->where('p.id', $request->process);
        }

        if ($request->service && !$request->employee) {
            $query->where('s.id', $request->service);
        }

        if ($request->employee) {
            $query->where('e.id', $request->employee);
        }

        return $query->get();
    }

    /**
     * Get the accumulated average by question and group.
     * @param ChartDto $request
     * @return Collection
     */
    public function getAverageByQuestionAndGroup(ChartDto $request): Collection
    {
        $group = $this->validGroupings[$request->group_by] ?? $this->validGroupings['campuses'];

        $query = $this
            ->getBaseQuery()
            ->join('answer_question as aq', 'a.id', '=', 'aq.answer_id')
            ->join('questions as q', 'aq.question_id', '=', 'q.id')
            ->selectRaw("
                {$group['field']} as group_id,
                {$group['label']} as group_name,
                ROUND(AVG(aq.answer),2) as average_answer,
                q.text as question_text
            ")
            ->groupBy('q.id', $group['field'], $group['label'], 'q.text')
            ->orderBy('group_name')
            ->whereBetween('a.created_at', $this->getDateRange($request))
            ->where('sv.id', $request->survey);

        if ($request->campus) {
            $query->where('c.id', $request->campus);
        }

        if ($request->process) {
            $query->where('p.id', $request->process);
        }

        if ($request->service && !$request->employee) {
            $query->where('s.id', $request->service);
        }

        if ($request->employee) {
            $query->where('e.id', $request->employee);
        }

        $results = collect($query
            ->get());
        $data = $results->groupBy('question_text')->map(function ($group) {
            $data = [];
            $data['question'] = $group[0]->question_text;
            foreach ($group as $item) {
                $data[$item->group_name] = $item->average_answer;
            }
            return $data;
        })->values();
        $labels = $results->map(fn($item) => $item->group_name)->unique()->values();
        return collect([
            'labels' => $labels,
            'data' => $data,
        ]);
    }

    /**
     * Get the perception (average of the answer's average value) by group.
     * @param ChartDto $request
     * @return Collection
     */
    public function getPerceptionByGroup(ChartDto $request): Collection
    {
        $group = $this->validGroupings[$request->group_by] ?? $this->validGroupings['campuses'];

        $query = $this
            ->getBaseQuery()
            ->selectRaw("
                {$group['field']} as id,
                {$group['label']} as name,
                ROUND(AVG(a.average),2) as average_perception
            ")
            ->groupBy($group['field'], $group['label'])
            ->orderBy('name')
            ->where('sv.id', $request->survey);

        if ($request->campus) {
            $query->where('c.id', $request->campus);
        }

        if ($request->process) {
            $query->where('p.id', $request->process);
        }

        if ($request->service && !$request->employee) {
            $query->where('s.id', $request->service);
        }

        if ($request->employee) {
            $query->where('e.id', $request->employee);
        }

        return $query
            ->whereBetween('a.created_at', $this->getDateRange($request))
            ->get();
    }

    /**
     * Get the volume (amount) of feedback by group.
     * @param ChartDto $request
     * @return Collection
     */
    public function getVolumeOfFeedbackByGroup(ChartDto $request): Collection
    {
        $group = $this->validGroupings[$request->group_by] ?? $this->validGroupings['campuses'];

        $query = $this
            ->getBaseQuery()
            ->selectRaw("
                {$group['field']} as id,
                {$group['label']} as name,
                COUNT(a.id) as feedback_count
            ")
            ->groupBy($group['field'], $group['label'])
            ->orderBy('name')
            ->where('sv.id', $request->survey);

        if ($request->campus) {
            $query->where('c.id', $request->campus);
        }

        if ($request->process) {
            $query->where('p.id', $request->process);
        }

        if ($request->service && !$request->employee) {
            $query->where('s.id', $request->service);
        }

        if ($request->employee) {
            $query->where('e.id', $request->employee);
        }

        return $query
            ->whereBetween('a.created_at', $this->getDateRange($request))
            ->get();
    }

    /**
     * Get the volume (amount) of feedback over time by group.
     * @param ChartDto $request
     * @return Collection
     */
    public function getVolumeOfFeedbackTrendByGroup(ChartDto $request): Collection
    {
        $group = $this->validGroupings[$request->group_by] ?? $this->validGroupings['campuses'];

        $timeFormat = $this->getTimeFormat($request->time_frame);

        $query = $this
            ->getBaseQuery()
            ->selectRaw("
                $timeFormat as period,
                {$group['field']} as group_id,
                {$group['label']} as group_name,
                COUNT(a.id) as feedback_count
            ")
            ->groupByRaw("$timeFormat, {$group['field']}, {$group['label']}")
            ->orderBy('period')
            ->where('sv.id', $request->survey);

        if ($request->campus) {
            $query->where('c.id', $request->campus);
        }

        if ($request->process) {
            $query->where('p.id', $request->process);
        }

        if ($request->service && !$request->employee) {
            $query->where('s.id', $request->service);
        }

        if ($request->employee) {
            $query->where('e.id', $request->employee);
        }

        $results = collect($query
            ->whereBetween('a.created_at', $this->getDateRange($request))
            ->get());

        $data = $results->groupBy('period')->map(function ($group, $period) {
            $data = [
                'period' => $period,
            ];
            foreach ($group as $item) {
                $data[$item->group_name] = $item->feedback_count;
            }
            return $data;
        })->values();
        $labels = $results->map(fn($item) => $item->group_name)->unique()->values();

        return collect([
            'labels' => $labels,
            'data' => $data,
        ]);
    }

    public function getVolumeOfFeedbackByRespondentType(ChartDto $request): Collection
    {
        $group = $this->validGroupings[$request->group_by] ?? $this->validGroupings['campuses'];
        $query = $this
            ->getBaseQuery()
            ->selectRaw('
                rt.id as id,
                rt.name as name,
                COUNT(a.id) as feedback_count
            ')
            ->join('respondent_types as rt', 'a.respondent_type_id', '=', 'rt.id')
            ->groupBy($group['field'], $group['label'])
            ->orderBy('name')
            ->where('sv.id', $request->survey)
            ->whereBetween('a.created_at', $this->getDateRange($request));

        if ($request->campus) {
            $query->where('c.id', $request->campus);
        }

        if ($request->process) {
            $query->where('p.id', $request->process);
        }

        if ($request->service && !$request->employee) {
            $query->where('s.id', $request->service);
        }

        if ($request->employee) {
            $query->where('e.id', $request->employee);
        }
        return collect($query->get());
    }

    public function getDistributionByGroupAndSatisfaction(ChartDto $request): Collection
    {
        $group = $this->validGroupings[$request->group_by] ?? $this->validGroupings['campuses'];
        $query =
            $this
                ->getBaseQuery()
                ->selectRaw(<<<SQL
                            CASE
                              WHEN average >= ? AND average < ? THEN ?
                              WHEN average >= ? AND average < ? THEN ?
                              WHEN average >= ? AND average <= ? THEN ?
                            END AS bucket
                        SQL,
                    [
                        1,
                        3,
                        'Insatisfecho',
                        3,
                        4,
                        'Satisfecho',
                        4,
                        5,
                        'Muy Satisfecho',
                    ])
                ->selectRaw("
                {$group['field']} as group_id,
                {$group['label']} as group_name,
                COUNT(*) AS total")
                ->groupByRaw("{$group['field']}, {$group['label']}, bucket")
                ->whereBetween('a.created_at', $this->getDateRange($request))
                ->where('sv.id', $request->survey);

        if ($request->campus) {
            $query->where('c.id', $request->campus);
        }

        if ($request->process) {
            $query->where('p.id', $request->process);
        }

        if ($request->service && !$request->employee) {
            $query->where('s.id', $request->service);
        }

        if ($request->employee) {
            $query->where('e.id', $request->employee);
        }

        $results = collect($query->get());
        $grouped = $results->groupBy('group_name')->map(function ($group) {
            $data = [];
            $data['group_name'] = $group[0]->group_name;
            foreach ($group as $item) {
                $data[$item->bucket] = $item->total;
            }
            return $data;
        })->values();

        $ranges = [
            'Insatisfecho',
            'Satisfecho',
            'Muy Satisfecho',
        ];
        return collect([
            'labels' => $ranges,
            'data' => $grouped,
        ]);
    }

    public function getRankingOfGroup(ChartDto $request): Collection
    {
        $group = $this->validGroupings[$request->group_by] ?? $this->validGroupings['campuses'];
        $query = $this
            ->getBaseQuery()
            ->selectRaw("
                {$group['field']} as id,
                {$group['label']} as name,
                {$group['image']} as image,
                ROUND(AVG(a.average),2) as average_perception,
                COUNT(a.id) as answer_count
            ")
            ->groupBy($group['field'], $group['label'], $group['image'])
            ->whereBetween('a.created_at', $this->getDateRange($request))
            ->orderBy('average_perception', 'desc')
            ->where('sv.id', $request->survey);

        if ($request->campus) {
            $query->where('c.id', $request->campus);
        }

        if ($request->process) {
            $query->where('p.id', $request->process);
        }

        if ($request->service && !$request->employee) {
            $query->where('s.id', $request->service);
        }

        if ($request->employee) {
            $query->where('e.id', $request->employee);
        }

        return $query->get()->map(function ($item) use ($request) {
            $imagePathSubfolder = $request->group_by === 'employees' ? 'avatars' : 'icons';
            $imagePath = $item->image ? asset("storage/$imagePathSubfolder/{$item->image}") : null;
            return [
                'id' => $item->id,
                'name' => $item->name,
                'image' => $imagePath,
                'average_perception' => $item->average_perception,
                'answer_count' => $item->answer_count
            ];
        });
    }
}
