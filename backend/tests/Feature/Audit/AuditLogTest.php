<?php

use App\Modules\Shared\Audit\Domain\Entities\AuditLog;

it('creates an audit log entry', function () {
    $log = AuditLog::create([
        'user_id' => null,
        'action' => 'created',
        'entity_type' => 'TestEntity',
        'entity_id' => '1',
        'old_values' => null,
        'new_values' => ['name' => 'Test'],
        'ip_address' => '127.0.0.1',
        'user_agent' => 'PHPUnit',
    ]);

    expect($log)->toBeInstanceOf(AuditLog::class)
        ->and($log->action)->toBe('created')
        ->and($log->entity_type)->toBe('TestEntity')
        ->and($log->new_values)->toBe(['name' => 'Test'])
        ->and($log->old_values)->toBeNull();
});

it('stores old and new values on update action', function () {
    $log = AuditLog::create([
        'action' => 'updated',
        'entity_type' => 'Student',
        'entity_id' => '42',
        'old_values' => ['name' => 'Joao'],
        'new_values' => ['name' => 'Joao Silva'],
    ]);

    expect($log->old_values)->toBe(['name' => 'Joao'])
        ->and($log->new_values)->toBe(['name' => 'Joao Silva']);
});

it('has a ulid as primary key', function () {
    $log = AuditLog::create([
        'action' => 'created',
        'entity_type' => 'School',
        'entity_id' => '1',
        'new_values' => ['name' => 'EMEB Teste'],
    ]);

    expect($log->id)->toBeString()
        ->and(strlen($log->id))->toBe(26);
});
