<?php

namespace App\DTOs\Chart;

use App\DTOs\DataTransferObject;

class ChartDto implements DataTransferObject
{

    public function __construct(
        public ?string $campus,
        public ?string $process,
        public ?string $service,
        public ?string $employee,
        public ?string $survey,
        public string $group_by,
        public string $time_frame,
        public string $start_date,
        public string $end_date,
    ) {
    }

    /**
     * @inheritDoc
     */
    public static function fromRequest($request): self
    {
        return new self(
            $request->validated('campus'),
            $request->validated('process'),
            $request->validated('service'),
            $request->validated('employee'),
            $request->validated('survey'),
            $request->validated('group_by'),
            $request->validated('time_frame'),
            $request->validated('start_date'),
            $request->validated('end_date')
        );
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [
            'campus' => $this->campus,
            'process' => $this->process,
            'service' => $this->service,
            'employee' => $this->employee,
            'survey' => $this->survey,
            'group_by' => $this->group_by,
            'time_frame' => $this->time_frame,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date
        ];
    }
}
