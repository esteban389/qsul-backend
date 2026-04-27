<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

require_once __DIR__ . '/ChartTestSupport.php';

uses(RefreshDatabase::class);

it('includes the selected end date in the general perception trend chart', function () {
    $fixture = createInclusiveChartFixture();

    $this->actingAs($fixture['user']);

    $response = $this->postJson('/api/chart/perception-trend', [
        'survey' => $fixture['survey']->id,
        'start_date' => $fixture['selected_date'],
        'end_date' => $fixture['selected_date'],
        'group_by' => 'campuses',
        'time_frame' => 'month',
    ]);

    $response->assertOk();

    $rows = $response->json();

    $this->assertCount(1, $rows);
    $this->assertSame('2026-04', $rows[0]['period']);
    $this->assertSame(4.75, (float) $rows[0]['average_perception']);
});

it('includes the selected end date in the general perception ranking chart', function () {
    $fixture = createInclusiveChartFixture();

    $this->actingAs($fixture['user']);

    $response = $this->postJson('/api/chart/ranking', [
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
    $this->assertSame(4.75, (float) $rows[0]['average_perception']);
});
