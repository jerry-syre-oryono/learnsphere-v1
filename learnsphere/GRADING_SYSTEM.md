# LEARNSPHERE GRADING SYSTEM DOCUMENTATION

## Overview

This document describes the comprehensive grading and academic evaluation system implemented in LearnSphere according to Ugandan NCHE (National Council for Higher Education) standards.

## Architecture

### Service-Based Design

The grading system uses a service-based architecture with no grading logic in controllers:

```
app/Services/Grading/
├── GradeBoundaryResolver.php      # Maps percentage marks to grades
├── GradeCalculator.php             # Calculates grade and grade points
├── RetakeCapEnforcer.php           # Enforces retake grade caps (max C)
├── GPACalculator.php               # Calculates semester GPA
├── CGPACalculator.php              # Calculates cumulative GPA
├── ClassificationResolver.php      # Resolves degree classification
├── AcademicStandingResolver.php   # Determines academic standing
└── GradingService.php              # Main orchestrator service
```

### Database Schema

**New Tables:**
- `program_levels` - Diploma, Degree, Certificate programs
- `grading_rules` - Configurable grade boundaries
- `academic_classifications` - Classification ranges by program
- `academic_policies` - NCHE-aligned policies
- `student_course_results` - Final grades audit trail

**Modified Tables:**
- `enrollments` - Added `program_level_id` foreign key

## Grade Scale (NCHE Standard)

### Diploma & Degree Programs (Default)

| Min % | Max % | Grade | Points |
|-------|-------|-------|--------|
| 80    | 100   | A     | 5.0    |
| 75    | 79    | B+    | 4.5    |
| 70    | 74    | B     | 4.0    |
| 65    | 69    | C+    | 3.5    |
| 60    | 64    | C     | 3.0    |
| 55    | 59    | D+    | 2.5    |
| 50    | 54    | D     | 2.0    |
| 0     | 49    | F     | 0.0    |

## Grade Calculation Flow

### Step-by-Step Process

1. **Read Final Mark** - Get percentage mark (0-100)
2. **Resolve Grade Boundary** - Map percentage to letter grade and points based on program level
3. **Compute Grade Points** - letter_grade → grade_point (0.0 - 5.0)
4. **Apply Retake Cap** (if `is_retake = true`)
   - Cap grade point at 3.0 (C)
   - Set `was_capped = true`
   - Store original grade for audit
5. **Calculate Grade Points Earned** - `grade_point × credit_units`
6. **Persist Results** - Save to `student_course_results` table

### Example Calculation

```
Student: John Doe
Course: CS 101 (3 credit units)
Final Mark: 85%
Is Retake: No

1. Grade Boundary: 85% → A (5.0 points)
2. Grade Points Earned: 5.0 × 3.0 = 15.0
3. Result:
   - Letter Grade: A
   - Grade Point: 5.0
   - Grade Points Earned: 15.0
   - Was Capped: false
```

## GPA & CGPA Calculations

### Semester GPA

```
Semester GPA = Σ(grade_points_earned) / Σ(credit_units)
```

**Rules:**
- Only graded courses count
- Failed courses contribute 0.0 grade points
- All registered courses contribute credit units

### Cumulative GPA (CGPA)

```
CGPA = Σ(all grade_points_earned across all semesters) / Σ(all credit_units)
```

**Constraints:**
- Minimum: 0.0
- Maximum: 5.0
- Rounded to 2 decimal places

**NCHE Regulation:** A candidate shall not graduate with a CGPA below 2.00.

## Retake Grade Cap Policy

### NCHE Regulation

> "In accordance with institutional and NCHE regulations, the maximum grade attainable in a repeated course shall not exceed a Credit (C)."

### Implementation

- If `is_retake = true`, grade point is capped at 3.0 (C)
- Original grade is preserved for audit trail
- `was_capped = true` is recorded
- Capped grade is what contributes to GPA/CGPA calculation

### Example

```
Original Attempt: 85% → A (5.0)
Retake Attempt: 92% → A (5.0) → CAPPED → C (3.0)

Result stored as:
- letter_grade: C
- grade_point: 3.0
- original_grade: A
- capped_grade: C
- was_capped: true
```

## Academic Classification

### Diploma Programs

| CGPA   | Classification |
|--------|-----------------|
| ≥ 4.00 | Distinction     |
| 3.00-3.99 | Credit      |
| 2.00-2.99 | Pass        |
| < 2.00 | Fail            |

### Degree Programs (Bachelor's Honours)

| CGPA   | Class                   |
|--------|-------------------------|
| 4.40-5.00 | First Class Honours |
| 3.60-4.39 | Second Class Upper  |
| 2.80-3.59 | Second Class Lower  |
| 2.00-2.79 | Pass                |
| < 2.00 | Fail                    |

## Academic Standing

### Standing Types

**1. Normal Progress**
- CGPA ≥ 2.00
- Student can continue studies

**2. Academic Probation**
- CGPA < 2.00
- Student placed on probation
- Must improve performance in next semester

**3. Discontinuation**
- Same core course failed twice, OR
- On probation for consecutive semesters

## Using the Grading Service

### Basic Usage in Controller

```php
use App\Services\Grading\GradingService;

class GradeReportController extends Controller
{
    public function __construct(private GradingService $gradingService) {}

    public function show(User $student)
    {
        // Get complete report
        $report = $this->gradingService->getCompleteGradeReport($student);

        return response()->json($report);
    }
}
```

### Processing a Student Grade

```php
// Process a grade for a student in a course
$result = $this->gradingService->processStudentGrade(
    enrollment: $enrollment,
    percentageMark: 85.5,
    creditUnits: 3.0,
    isRetake: false,
    semester: '2024-2025-1'
);

// Returns StudentCourseResult model with calculated grades
```

### Calculating GPA/CGPA

```php
// Semester GPA
$semesterGPA = $this->gradingService->calculateSemesterGPA($enrollment);

// Cumulative GPA
$cgpa = $this->gradingService->calculateCGPA($student);

// Classification
$classification = $this->gradingService->getAcademicClassification($student);
// Returns: ['classification' => 'Credit', 'class' => null, 'cgpa' => 3.45]

// Academic Standing
$standing = $this->gradingService->getAcademicStanding($student);
// Returns: [
//   'standing' => 'normal',
//   'status' => 'Good Standing',
//   'on_probation' => false,
//   'message' => 'Student is in good academic standing.'
// ]
```

## API Endpoints

### Student Endpoints (Read-Only)

```
GET /api/student/grade-report
- Returns: Complete grade report including CGPA, classification, standing

GET /api/students/{student}/cgpa
- Returns: CGPA, classification, academic standing

GET /api/enrollments/{enrollment}/grades?semester=2024-2025-1
- Returns: Enrollment grade report for specific semester

GET /api/academic-policies
- Returns: Active NCHE academic policies
```

### Admin Endpoints (Grade Processing)

```
POST /api/admin/grades/process
Body: {
  "enrollment_id": 1,
  "percentage_mark": 85.5,
  "credit_units": 3.0,
  "is_retake": false,
  "semester": "2024-2025-1"
}
Returns: StudentCourseResult

POST /api/admin/grades/bulk-process
Body: {
  "semester": "2024-2025-1",
  "grades": [
    {"enrollment_id": 1, "percentage_mark": 85, ...},
    {"enrollment_id": 2, "percentage_mark": 72, ...}
  ]
}
Returns: {success, processed_count, failed_count, results, failed}

GET /api/admin/courses/{course}/results?semester=2024-2025-1
- Returns: Paginated course results

PUT /api/admin/results/{result}
Body: {
  "percentage_mark": 90,
  "is_retake": false
}
Returns: Updated StudentCourseResult
```

## UI Components

### Student Components (Livewire)

1. **StudentGradeReport** - Complete grade report with GPA, CGPA, classification, standing
2. **GradeSummary** - Quick summary card (GPA, CGPA, classification badge)
3. **CourseGradeCard** - Individual course grade display with retake indicator

### Admin Components (Livewire)

1. **GradeBook** - Course-wide grade book with search and sorting
2. **AcademicPoliciesDisplay** - Display all active NCHE policies

## Testing

### Unit Tests

```bash
php artisan test tests/Unit/Services/Grading/
```

Tests cover:
- Grade boundary resolution
- Grade calculations with edge cases (49.9, 50, 79.99, 80)
- Retake grade capping
- GPA calculations with mixed credit units
- CGPA calculations
- Classification logic
- Academic standing determination

### Feature Tests

```bash
php artisan test tests/Feature/Services/Grading/
```

Tests cover:
- End-to-end grading workflow
- Multiple courses with different grades
- Retake scenarios
- Different program levels
- Complete grade reporting

## NCHE Policy Wording

The following NCHE-aligned policies are embedded in the system:

### 1. Pass Mark Requirement
> "Pass mark for all undergraduate courses shall be fifty percent (50%)."

### 2. Retake Grade Cap
> "In accordance with institutional and NCHE regulations, the maximum grade attainable in a repeated course shall not exceed a Credit (C)."

### 3. Graduation Requirement
> "A candidate shall not graduate with a CGPA below 2.00."

### 4. Academic Probation
> "Students with CGPA below 2.00 are placed on academic probation and must improve their performance in the next semester."

## Configuration & Customization

### Custom Grade Boundaries

Edit the default boundaries in `GradeBoundaryResolver.php` or seed custom rules via `GradingSeeder.php`:

```php
GradingRule::create([
    'program_level_id' => $programLevel->id,
    'min_percentage' => 80,
    'max_percentage' => 100,
    'letter_grade' => 'A',
    'grade_point' => 5.0,
]);
```

### Custom Policies

Add policies to `academic_policies` table:

```php
AcademicPolicy::create([
    'policy_code' => 'CUSTOM_POLICY',
    'policy_name' => 'Custom Policy Name',
    'description' => 'Full policy text...',
    'value' => '50',
    'policy_type' => 'regulation',
    'order' => 10,
    'is_active' => true,
]);
```

## Future Enhancements

- [ ] Weighted grade components (coursework, exam, participation)
- [ ] Appeal mechanism for grade disputes
- [ ] Transcript generation
- [ ] Grade trend analysis
- [ ] Performance predictions
- [ ] Support for pass/fail courses
- [ ] Support for transferable credits
- [ ] Historical grade tracking and comparisons

## Important Notes

⚠️ **No Recalculation of Historical Records**
- Grade recalculation requires explicit admin trigger
- All grade changes are audited
- Original marks remain unchanged

⚠️ **Auditability**
- All grade components stored (original, capped, computed)
- Calculation timestamp recorded
- Retake history preserved

⚠️ **Constraints**
- No grading logic in controllers
- No hardcoded grade boundaries
- All rules configurable via database
- Full NCHE compliance enforced

## Troubleshooting

### Grades Not Calculating

1. Verify `program_level_id` is set on enrollment
2. Check `GradingRule` records exist for that program level
3. Ensure grading service is properly injected
4. Verify permissions for grade access

### Wrong Classification

1. Check academic_classifications table has records for program level
2. Verify CGPA calculation is correct
3. Check if program requires CGPA for graduation

### Retake Not Capped

1. Verify `is_retake = true` on StudentCourseResult
2. Check original grade point calculation
3. Confirm RetakeCapEnforcer is being called

## Support & References

- NCHE Uganda: https://www.nche.ac.ug/
- Implementation Contact: [Your Team]
- Last Updated: January 2026
