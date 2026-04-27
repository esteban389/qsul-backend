<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

require_once __DIR__ . '/ChartTestSupport.php';

uses(RefreshDatabase::class);

it('includes the selected end date in the general volume chart', function () {
    $fixture = createInclusiveChartFixture();

    $this->actingAs($fixture['user']);

    $response = $this->postJson('/api/chart/volume', [
        'survey' => $fixture['survey']->id,
        'start_date' => $fixture['selected_date'],
        'end_date' => $fixture['selected_date'],
        'group_by' => 'campuses',
        'time_frame' => 'month',
    ]);

    $response->assertOk();

    $rows = $response->json();

    $this->assertCount(1, $rows);
    $this->assertSame($fixture['campus']->id, $rows[0]['id']);
    $this->assertSame(1, (int) $rows[0]['feedback_count']);
});
