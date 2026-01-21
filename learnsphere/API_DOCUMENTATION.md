# LEARNSPHERE GRADING API DOCUMENTATION

## Base URL
```
http://localhost/api
```

## Authentication
All endpoints require authentication via Laravel Sanctum or session-based auth.

---

## 📚 STUDENT ENDPOINTS (Read-Only)

### 1. Get Student Grade Report
**Endpoint:** `GET /api/student/grade-report`

**Description:** Returns complete grade report including CGPA, classification, and standing

**Authentication:** Required (Authenticated User)

**Response:**
```json
{
  "success": true,
  "data": {
    "student": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com"
    },
    "cgpa": 3.45,
    "classification": {
      "classification": "Credit",
      "class": null,
      "cgpa": 3.45
    },
    "standing": {
      "standing": "normal",
      "status": "Good Standing",
      "on_probation": false,
      "message": "Student is in good academic standing.",
      "cgpa": 3.45
    },
    "is_eligible_for_graduation": true,
    "can_continue_studies": true
  }
}
```

---

### 2. Get Student CGPA & Classification
**Endpoint:** `GET /api/students/{student_id}/cgpa`

**Description:** Returns CGPA, classification, and academic standing for a student

**Authentication:** Required (Student or Admin)

**Parameters:**
- `student_id` (path) - Student ID

**Response:**
```json
{
  "success": true,
  "data": {
    "cgpa": 3.45,
    "classification": {
      "classification": "Credit",
      "class": null,
      "cgpa": 3.45
    },
    "standing": {
      "standing": "normal",
      "status": "Good Standing",
      "on_probation": false,
      "message": "Student is in good academic standing.",
      "cgpa": 3.45
    }
  }
}
```

---

### 3. Get Enrollment Grades
**Endpoint:** `GET /api/enrollments/{enrollment_id}/grades`

**Description:** Returns grades for a specific enrollment (course enrollment)

**Authentication:** Required (Student or Instructor)

**Parameters:**
- `enrollment_id` (path) - Enrollment ID
- `semester` (query, optional) - Filter by semester (e.g., "2024-2025-1")

**Response:**
```json
{
  "success": true,
  "data": {
    "enrollment": {
      "id": 1,
      "user_id": 5,
      "course_id": 10,
      "program_level_id": 2,
      "student_number": "STU001"
    },
    "semester": "2024-2025-1",
    "gpa": 3.71,
    "course_results": [
      {
        "id": 1,
        "enrollment_id": 1,
        "course_id": 10,
        "final_mark": 85.5,
        "letter_grade": "A",
        "grade_point": 5.0,
        "grade_points_earned": 15.0,
        "credit_units": 3.0,
        "semester": "2024-2025-1",
        "is_retake": false,
        "was_capped": false
      }
    ],
    "total_credit_units": 12.0,
    "total_grade_points": 45.0
  }
}
```

---

### 4. Get Academic Policies
**Endpoint:** `GET /api/academic-policies`

**Description:** Returns all active NCHE academic policies

**Authentication:** Not Required

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "policy_code": "PASS_MARK",
      "policy_name": "Pass Mark Requirement",
      "description": "Pass mark for all undergraduate courses shall be fifty percent (50%).",
      "value": "50",
      "policy_type": "regulation",
      "is_active": true,
      "order": 1
    },
    {
      "id": 2,
      "policy_code": "RETAKE_CAP",
      "policy_name": "Retake Grade Cap",
      "description": "In accordance with institutional and NCHE regulations, the maximum grade attainable in a repeated course shall not exceed a Credit (C).",
      "value": "C",
      "policy_type": "regulation",
      "is_active": true,
      "order": 2
    },
    {
      "id": 3,
      "policy_code": "GRAD_CGPA",
      "policy_name": "Graduation CGPA Requirement",
      "description": "A candidate shall not graduate with a CGPA below 2.00.",
      "value": "2.00",
      "policy_type": "regulation",
      "is_active": true,
      "order": 3
    }
  ]
}
```

---

## 🔧 ADMIN ENDPOINTS (Grade Processing)

**Authorization:** Required (`role:instructor` or `role:admin`)

---

### 1. Process Single Grade
**Endpoint:** `POST /api/admin/grades/process`

**Description:** Process and store a single student grade

**Authentication:** Required (Instructor/Admin)

**Request Body:**
```json
{
  "enrollment_id": 1,
  "percentage_mark": 85.5,
  "credit_units": 3.0,
  "is_retake": false,
  "semester": "2024-2025-1"
}
```

**Parameters:**
- `enrollment_id` (required) - Enrollment ID
- `percentage_mark` (required, numeric) - Mark 0-100
- `credit_units` (optional, numeric) - Default: 3.0
- `is_retake` (optional, boolean) - Default: false
- `semester` (optional, string) - If not provided, current semester is used

**Response (Success):**
```json
{
  "success": true,
  "message": "Grade processed successfully",
  "data": {
    "id": 42,
    "enrollment_id": 1,
    "course_id": 10,
    "final_mark": 85.5,
    "letter_grade": "A",
    "grade_point": 5.0,
    "grade_points_earned": 15.0,
    "credit_units": 3.0,
    "semester": "2024-2025-1",
    "is_retake": false,
    "was_capped": false,
    "original_grade": null,
    "capped_grade": null,
    "calculated_at": "2024-01-21T10:30:00Z"
  }
}
```

**Response (Error):**
```json
{
  "success": false,
  "message": "Failed to process grade: Enrollment not found"
}
```

**HTTP Status:**
- `201` - Created successfully
- `422` - Validation error

---

### 2. Bulk Process Grades
**Endpoint:** `POST /api/admin/grades/bulk-process`

**Description:** Process multiple student grades in one request

**Authentication:** Required (Instructor/Admin)

**Request Body:**
```json
{
  "semester": "2024-2025-1",
  "grades": [
    {
      "enrollment_id": 1,
      "percentage_mark": 85.5,
      "credit_units": 3.0,
      "is_retake": false
    },
    {
      "enrollment_id": 2,
      "percentage_mark": 72.0,
      "credit_units": 4.0,
      "is_retake": false
    },
    {
      "enrollment_id": 3,
      "percentage_mark": 95.0,
      "credit_units": 3.0,
      "is_retake": true
    }
  ]
}
```

**Parameters:**
- `grades` (required, array) - Array of grade objects
- `semester` (optional, string) - Applied to all grades

**Response:**
```json
{
  "success": true,
  "message": "3 grades processed successfully",
  "processed_count": 3,
  "failed_count": 0,
  "results": [
    {
      "id": 40,
      "enrollment_id": 1,
      "final_mark": 85.5,
      "letter_grade": "A",
      "grade_point": 5.0
    },
    {
      "id": 41,
      "enrollment_id": 2,
      "final_mark": 72.0,
      "letter_grade": "B",
      "grade_point": 4.0
    },
    {
      "id": 42,
      "enrollment_id": 3,
      "final_mark": 95.0,
      "letter_grade": "C",
      "grade_point": 3.0,
      "was_capped": true,
      "original_grade": "A"
    }
  ],
  "failed": []
}
```

**HTTP Status:**
- `201` - Created successfully
- `422` - Validation error

---

### 3. Get Course Results
**Endpoint:** `GET /api/admin/courses/{course_id}/results`

**Description:** Get all grades for a specific course

**Authentication:** Required (Instructor/Admin)

**Parameters:**
- `course_id` (path) - Course ID
- `semester` (query, optional) - Filter by semester
- `page` (query, optional) - Pagination (default: 1)
- `per_page` (query, optional) - Results per page (default: 50)

**Response:**
```json
{
  "success": true,
  "data": {
    "data": [
      {
        "id": 40,
        "enrollment_id": 1,
        "course_id": 10,
        "final_mark": 85.5,
        "letter_grade": "A",
        "grade_point": 5.0,
        "grade_points_earned": 15.0,
        "credit_units": 3.0,
        "semester": "2024-2025-1",
        "is_retake": false,
        "enrollment": {
          "id": 1,
          "user": {
            "id": 5,
            "name": "John Doe",
            "email": "john@example.com"
          }
        }
      }
    ],
    "current_page": 1,
    "last_page": 1,
    "per_page": 50,
    "total": 1
  }
}
```

**HTTP Status:**
- `200` - Success
- `403` - Unauthorized

---

### 4. Update Grade
**Endpoint:** `PUT /api/admin/results/{result_id}`

**Description:** Update an existing grade (correction)

**Authentication:** Required (Instructor/Admin)

**Parameters:**
- `result_id` (path) - StudentCourseResult ID

**Request Body:**
```json
{
  "percentage_mark": 88.0,
  "is_retake": false,
  "notes": "Grade corrected due to calculation error"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Grade updated successfully",
  "data": {
    "id": 40,
    "enrollment_id": 1,
    "course_id": 10,
    "final_mark": 88.0,
    "letter_grade": "A",
    "grade_point": 5.0,
    "grade_points_earned": 15.0,
    "calculated_at": "2024-01-21T11:00:00Z"
  }
}
```

**HTTP Status:**
- `200` - Success
- `422` - Validation error
- `403` - Unauthorized

---

## 📊 EXAMPLE WORKFLOWS

### Workflow 1: Entry of Semester Grades

```bash
# Step 1: Get all course enrollments
curl -X GET "http://localhost/api/admin/courses/10/results?semester=2024-2025-1"

# Step 2: Process grades in bulk
curl -X POST "http://localhost/api/admin/grades/bulk-process" \
  -H "Content-Type: application/json" \
  -d '{
    "semester": "2024-2025-1",
    "grades": [
      {"enrollment_id": 1, "percentage_mark": 85, "credit_units": 3, "is_retake": false},
      {"enrollment_id": 2, "percentage_mark": 72, "credit_units": 3, "is_retake": false}
    ]
  }'

# Step 3: View results
curl -X GET "http://localhost/api/admin/courses/10/results?semester=2024-2025-1"
```

### Workflow 2: Student Views Grades

```bash
# Step 1: Get complete report
curl -X GET "http://localhost/api/student/grade-report" \
  -H "Authorization: Bearer {token}"

# Step 2: Get specific enrollment grades
curl -X GET "http://localhost/api/enrollments/1/grades?semester=2024-2025-1" \
  -H "Authorization: Bearer {token}"

# Step 3: Get CGPA and classification
curl -X GET "http://localhost/api/students/5/cgpa" \
  -H "Authorization: Bearer {token}"
```

### Workflow 3: Retake Course (Capped Grade)

```bash
# First attempt: Student gets B+ (4.5)
curl -X POST "http://localhost/api/admin/grades/process" \
  -d '{"enrollment_id": 1, "percentage_mark": 76, "is_retake": false}'

# Retake: Even with 95%, capped at C (3.0)
curl -X POST "http://localhost/api/admin/grades/process" \
  -d '{
    "enrollment_id": 1, 
    "percentage_mark": 95, 
    "is_retake": true
  }'

# Result will have:
# - letter_grade: "C"
# - grade_point: 3.0
# - was_capped: true
# - original_grade: "A"
# - capped_grade: "C"
```

---

## 🔍 ERROR RESPONSES

### 401 Unauthorized
```json
{
  "message": "Unauthenticated."
}
```

### 403 Forbidden
```json
{
  "error": "Unauthorized",
  "message": "This action is unauthorized."
}
```

### 422 Unprocessable Entity
```json
{
  "success": false,
  "message": "Failed to process grade: ...",
  "errors": {
    "percentage_mark": ["The percentage_mark field is required."]
  }
}
```

### 404 Not Found
```json
{
  "error": "Not Found",
  "message": "Resource not found"
}
```

---

## 📝 NOTES

### Grade Point Scale
- A: 5.0
- B+: 4.5
- B: 4.0
- C+: 3.5
- C: 3.0
- D+: 2.5
- D: 2.0
- F: 0.0

### Retake Rule
- When `is_retake: true`, final grade is capped at C (3.0)
- Original grade is preserved for audit
- CGPA calculation uses capped grade

### Graduation Eligibility
- CGPA ≥ 2.00 required
- All registered courses count
- Failed courses contribute 0.0

### Academic Probation
- CGPA < 2.00 places student on probation
- Probation status prevents continuation if not resolved

---

## 🧪 TESTING API WITH CURL

```bash
# Get authentication token (adjust as needed)
TOKEN=$(curl -X POST "http://localhost/api/login" \
  -d "email=student@example.com&password=password" \
  -s | jq -r '.token')

# Use token in requests
curl -X GET "http://localhost/api/student/grade-report" \
  -H "Authorization: Bearer $TOKEN"
```

---

## 📚 Additional Resources

- Full Documentation: See `GRADING_SYSTEM.md`
- Implementation Guide: See `IMPLEMENTATION_SUMMARY.md`
- Source Code: `app/Services/Grading/`
- Tests: `tests/Unit/Services/Grading/` and `tests/Feature/Services/Grading/`

---

**Last Updated:** January 21, 2026
**API Version:** 1.0
**Status:** ✅ Ready for Use
