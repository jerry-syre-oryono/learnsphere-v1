<?php

namespace Database\Seeders;

use App\Models\ProgramLevel;
use App\Models\GradingRule;
use App\Models\AcademicClassification;
use App\Models\AcademicPolicy;
use Illuminate\Database\Seeder;

class GradingSeeder extends Seeder
{
    /**
     * Run the database seeders.
     */
    public function run(): void
    {
        $this->seedProgramLevels();
        $this->seedGradingRules();
        $this->seedAcademicClassifications();
        $this->seedAcademicPolicies();
    }

    private function seedProgramLevels(): void
    {
        $programLevels = [
            [
                'name' => 'Diploma',
                'code' => 'DIPL',
                'description' => 'Diploma Program (2-3 years)',
                'is_active' => true,
                'require_cgpa_for_graduation' => true,
            ],
            [
                'name' => 'Degree',
                'code' => 'DEG',
                'description' => 'Bachelor Degree Program (3-4 years)',
                'is_active' => true,
                'require_cgpa_for_graduation' => true,
            ],
            [
                'name' => 'Certificate',
                'code' => 'CERT',
                'description' => 'Certificate Program (6-12 months)',
                'is_active' => true,
                'require_cgpa_for_graduation' => false,
            ],
        ];

        foreach ($programLevels as $level) {
            ProgramLevel::firstOrCreate(['code' => $level['code']], $level);
        }
    }

    private function seedGradingRules(): void
    {
        $degreeProgram = ProgramLevel::where('code', 'DEG')->first();
        $diplomaProgram = ProgramLevel::where('code', 'DIPL')->first();

        // NCHE-aligned default grading rules
        $defaultRules = [
            ['min' => 80, 'max' => 100, 'grade' => 'A', 'points' => 5.0],
            ['min' => 75, 'max' => 79, 'grade' => 'B+', 'points' => 4.5],
            ['min' => 70, 'max' => 74, 'grade' => 'B', 'points' => 4.0],
            ['min' => 65, 'max' => 69, 'grade' => 'C+', 'points' => 3.5],
            ['min' => 60, 'max' => 64, 'grade' => 'C', 'points' => 3.0],
            ['min' => 55, 'max' => 59, 'grade' => 'D+', 'points' => 2.5],
            ['min' => 50, 'max' => 54, 'grade' => 'D', 'points' => 2.0],
            ['min' => 0, 'max' => 49, 'grade' => 'F', 'points' => 0.0],
        ];

        // Apply same rules to both Degree and Diploma programs
        foreach ([$degreeProgram, $diplomaProgram] as $program) {
            if ($program) {
                foreach ($defaultRules as $rule) {
                    GradingRule::firstOrCreate(
                        [
                            'program_level_id' => $program->id,
                            'min_percentage' => $rule['min'],
                            'max_percentage' => $rule['max'],
                        ],
                        [
                            'letter_grade' => $rule['grade'],
                            'grade_point' => $rule['points'],
                        ]
                    );
                }
            }
        }
    }

    private function seedAcademicClassifications(): void
    {
        $degreeProgram = ProgramLevel::where('code', 'DEG')->first();
        $diplomaProgram = ProgramLevel::where('code', 'DIPL')->first();

        // Diploma Classifications
        if ($diplomaProgram) {
            $diplomaClassifications = [
                ['min_cgpa' => 4.00, 'max_cgpa' => 5.00, 'classification' => 'Distinction', 'order' => 1],
                ['min_cgpa' => 3.00, 'max_cgpa' => 3.99, 'classification' => 'Credit', 'order' => 2],
                ['min_cgpa' => 2.00, 'max_cgpa' => 2.99, 'classification' => 'Pass', 'order' => 3],
                ['min_cgpa' => 0.00, 'max_cgpa' => 1.99, 'classification' => 'Fail', 'order' => 4],
            ];

            foreach ($diplomaClassifications as $class) {
                AcademicClassification::firstOrCreate(
                    [
                        'program_level_id' => $diplomaProgram->id,
                        'min_cgpa' => $class['min_cgpa'],
                    ],
                    array_merge($class, ['program_level_id' => $diplomaProgram->id])
                );
            }
        }

        // Degree Classifications (Honours Classes)
        if ($degreeProgram) {
            $degreeClassifications = [
                ['min_cgpa' => 4.40, 'max_cgpa' => 5.00, 'class' => 'First Class Honours', 'order' => 1],
                ['min_cgpa' => 3.60, 'max_cgpa' => 4.39, 'class' => 'Second Class Upper', 'order' => 2],
                ['min_cgpa' => 2.80, 'max_cgpa' => 3.59, 'class' => 'Second Class Lower', 'order' => 3],
                ['min_cgpa' => 2.00, 'max_cgpa' => 2.79, 'class' => 'Pass', 'order' => 4],
                ['min_cgpa' => 0.00, 'max_cgpa' => 1.99, 'class' => 'Fail', 'order' => 5],
            ];

            foreach ($degreeClassifications as $class) {
                AcademicClassification::firstOrCreate(
                    [
                        'program_level_id' => $degreeProgram->id,
                        'min_cgpa' => $class['min_cgpa'],
                    ],
                    array_merge($class, ['program_level_id' => $degreeProgram->id])
                );
            }
        }
    }

    private function seedAcademicPolicies(): void
    {
        $policies = [
            [
                'policy_code' => 'PASS_MARK',
                'policy_name' => 'Pass Mark Requirement',
                'description' => 'Pass mark for all undergraduate courses shall be fifty percent (50%).',
                'value' => '50',
                'policy_type' => 'regulation',
                'order' => 1,
            ],
            [
                'policy_code' => 'RETAKE_CAP',
                'policy_name' => 'Retake Grade Cap',
                'description' => 'In accordance with institutional and NCHE regulations, the maximum grade attainable in a repeated course shall not exceed a Credit (C).',
                'value' => 'C',
                'policy_type' => 'regulation',
                'order' => 2,
            ],
            [
                'policy_code' => 'GRAD_CGPA',
                'policy_name' => 'Graduation CGPA Requirement',
                'description' => 'A candidate shall not graduate with a CGPA below 2.00.',
                'value' => '2.00',
                'policy_type' => 'regulation',
                'order' => 3,
            ],
            [
                'policy_code' => 'FAILED_COURSE',
                'policy_name' => 'Failed Course Handling',
                'description' => 'Students who fail a course must retake it in the next available offering. Grade will be capped at C.',
                'value' => null,
                'policy_type' => 'guideline',
                'order' => 4,
            ],
            [
                'policy_code' => 'PROBATION',
                'policy_name' => 'Academic Probation',
                'description' => 'Students with CGPA below 2.00 are placed on academic probation and must improve their performance in the next semester.',
                'value' => '2.00',
                'policy_type' => 'regulation',
                'order' => 5,
            ],
        ];

        foreach ($policies as $policy) {
            AcademicPolicy::firstOrCreate(['policy_code' => $policy['policy_code']], $policy);
        }
    }
}
