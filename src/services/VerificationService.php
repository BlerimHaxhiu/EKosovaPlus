<?php
declare(strict_types=1);

final class VerificationService
{
    public static function verify(array $student, array $scholarship): array
    {
        $checks = [];
        $passed = true;

        $isActiveStudent = (int) ($student['student_active'] ?? 1) === 1;
        self::addCheck(
            $checks,
            $passed,
            'Statusi studentor',
            $isActiveStudent,
            $isActiveStudent ? 'Student aktiv i verifikuar ne ' . ($student['academic_system'] ?? 'SEMS/SMU') : 'Nuk rezulton student aktiv'
        );

        if ($scholarship['min_grade'] !== null) {
            self::addCheck(
                $checks,
                $passed,
                'Nota mesatare minimale ' . $scholarship['min_grade'],
                (float) $student['average_grade'] >= (float) $scholarship['min_grade'],
                'Nota aktuale: ' . $student['average_grade']
            );
        }

        if (!empty($scholarship['required_university'])) {
            self::addCheck(
                $checks,
                $passed,
                'Universiteti',
                $student['university'] === $scholarship['required_university'],
                'Kerkohet: ' . $scholarship['required_university'] . '; studenti: ' . $student['university']
            );
        }

        if (!empty($scholarship['required_city'])) {
            self::addCheck(
                $checks,
                $passed,
                'Qyteti',
                $student['city'] === $scholarship['required_city'],
                'Kerkohet: ' . $scholarship['required_city'] . '; studenti: ' . $student['city']
            );
        }

        if (!empty($scholarship['required_social_status'])) {
            self::addCheck(
                $checks,
                $passed,
                'Statusi social',
                $student['social_status'] === $scholarship['required_social_status'],
                'Statusi i studentit: ' . $student['social_status']
            );
        }

        foreach ([
            'requires_veteran_child' => ['label' => 'Femije veterani', 'field' => 'is_veteran_child'],
            'requires_orphan' => ['label' => 'Jetim', 'field' => 'is_orphan'],
            'requires_social_assistance' => ['label' => 'Ndihme sociale', 'field' => 'receives_social_assistance'],
        ] as $criteriaField => $meta) {
            if ((int) $scholarship[$criteriaField] === 1) {
                self::addCheck(
                    $checks,
                    $passed,
                    $meta['label'],
                    (int) $student[$meta['field']] === 1,
                    (int) $student[$meta['field']] === 1 ? 'E konfirmuar' : 'Nuk rezulton ne databaze'
                );
            }
        }

        return [
            'status' => $passed ? 'approved' : 'rejected',
            'checks' => $checks,
            'fulfilled' => array_values(array_filter($checks, fn ($check) => $check['passed'])),
            'unfulfilled' => array_values(array_filter($checks, fn ($check) => !$check['passed'])),
        ];
    }

    private static function addCheck(array &$checks, bool &$passed, string $name, bool $result, string $details): void
    {
        $checks[] = [
            'name' => $name,
            'passed' => $result,
            'details' => $details,
            'institution' => self::institutionFor($name),
        ];

        if (!$result) {
            $passed = false;
        }
    }

    private static function institutionFor(string $name): string
    {
        if (strpos($name, 'Universitet') !== false || strpos($name, 'Nota') !== false || strpos($name, 'studentor') !== false) {
            return 'Universiteti';
        }

        if (strpos($name, 'Qyteti') !== false) {
            return 'Komuna';
        }

        return 'Qendra per Pune Sociale / Regjistrat Civil';
    }
}
