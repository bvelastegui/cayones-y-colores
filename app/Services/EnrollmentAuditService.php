<?php

namespace App\Services;

use App\Models\Enrollment;
use App\Models\EnrollmentAudit;
use App\Models\Student;
use App\Models\StudentRecordAccessLog;
use App\Models\User;

class EnrollmentAuditService
{
    /** @param array<string, mixed> $metadata */
    public function record(
        Enrollment $enrollment,
        ?User $user,
        string $event,
        array $metadata = [],
        ?string $ipAddress = null,
    ): EnrollmentAudit {
        return EnrollmentAudit::create([
            'enrollment_id' => $enrollment->id,
            'user_id' => $user?->id,
            'event' => $event,
            'metadata' => $metadata,
            'ip_address' => $ipAddress,
        ]);
    }

    public function recordAccess(
        Student $student,
        User $user,
        string $scope,
        ?string $reason = null,
        ?string $ipAddress = null,
    ): StudentRecordAccessLog {
        return StudentRecordAccessLog::create([
            'student_id' => $student->id,
            'user_id' => $user->id,
            'scope' => $scope,
            'reason' => $reason,
            'ip_address' => $ipAddress,
        ]);
    }
}
