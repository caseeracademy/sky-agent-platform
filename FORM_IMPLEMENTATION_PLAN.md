# Form Implementation Plan - Detailed Brainstorming

## 🔍 Current State Analysis

### What We Have Now

**Universities Form**:
- Name
- Location  
- is_active

**Programs Form**:
- university_id (dropdown)
- name
- degree_type (dropdown: Certificate, Diploma, Bachelor, Master, PhD) ⭐ Already exists!
- tuition_fee
- agent_commission
- system_commission
- is_active

**Applications Form**:
- student_id
- program_id
- agent_id
- payment proof
- documents
- notes

**Key Finding**: Programs already have `degree_type` field! We're converting it to `degree_id` relationship.

---

## 🎯 Implementation Approaches

### 1. UNIVERSITY FORM UPDATES

#### Approach A: Add Section to Existing Form (RECOMMENDED)

**What to add**:
```
NEW SECTION: "Scholarship Program Settings"

Fields:
1. scholarship_enabled (Toggle)
   - Default: No
   - Label: "Enable Scholarship Program"
   - Help: "Allow agents to earn free application scholarships"

2. scholarship_agent_threshold (Integer) [visible when enabled]
   - Label: "Students Required (Agent)"
   - Default: 5
   - Min: 1, Max: 20
   - Help: "Agents need this many approved students to earn 1 scholarship"

3. scholarship_admin_threshold (Integer) [visible when enabled]
   - Label: "Students Required (System Admin)"
   - Default: 4
   - Min: 1, Max: 20
   - Help: "You receive scholarship from university at this threshold"

4. margin_display (Placeholder, calculated) [visible when enabled]
   - Label: "Your Margin"
   - Shows: "1 scholarship per {lcm(agent_threshold, admin_threshold)} students"
   - Example: "1 scholarship per 20 students"
   - Color: Success/Green
   - Help: "This is your profit from the threshold difference"
```

**Pros**:
- ✅ Simple addition
- ✅ Keeps existing form structure
- ✅ Collapsible section (doesn't clutter)

**Cons**:
- None really

**Validation Rules**:
- agent_threshold MUST be > admin_threshold
- Both required if scholarship_enabled

---

#### Approach B: Separate Scholarship Configuration Page

Create `UniversityScholarshipResource` for advanced configuration.

**Pros**:
- Clean separation
- More room for complex settings

**Cons**:
- ❌ More clicks
- ❌ Over-engineered for simple settings

**Verdict**: Approach A is better (simpler)

---

### 2. PROGRAM FORM UPDATES

#### Current Situation
Programs already have `degree_type` as enum/dropdown.

**Options**: Certificate, Diploma, Bachelor, Master, PhD

#### Approach A: Convert to Relationship (RECOMMENDED)

**What changes**:
```
OLD: degree_type (string/enum)
NEW: degree_id (foreign key to degrees table)
```

**Migration Steps**:
1. Create degrees table with standard degrees
2. Seed degrees
3. Add degree_id to programs (nullable initially)
4. Data migration: Map existing degree_type to degree_id
5. Make degree_id required
6. Drop degree_type column (or keep for backup)

**Benefits**:
- ✅ Consistent with scholarship tracking (uses same degree_id)
- ✅ Can add custom degrees later
- ✅ Better for reporting/filtering

---

#### Approach B: Keep degree_type, Add Parallel degree_id

Keep both fields, use degree_id for scholarships only.

**Cons**:
- ❌ Confusing (two fields for same thing)
- ❌ Data inconsistency risk

**Verdict**: Approach A (convert fully)

---

#### Program Form Structure (NEW)

**Option 1: Multi-Step Wizard**
```
Step 1: Select Degree
Step 2: Select University  
Step 3: Program Details + Commission
```

**Pros**:
- ✅ Clear progression
- ✅ Can show contextual info at each step
- ✅ Less overwhelming

**Cons**:
- Longer flow (3 pages vs 1)

---

**Option 2: Single Form with Better Organization**
```
Section 1: Classification
- Degree (dropdown - FIRST field)
- University (dropdown - filtered by active)

Section 2: Program Details
- Name
- Tuition fee
- Duration, etc.

Section 3: Commission Settings
- Commission type (Money or Scholarship radio)
- If Money: agent_commission, system_commission fields
- If Scholarship: Show university's thresholds (read-only)
```

**Pros**:
- ✅ All on one page
- ✅ Faster for experienced users

**Cons**:
- Can be overwhelming

**My Recommendation**: Start with Option 2 (single form), can add wizard later if users request it.

---

#### Commission Type Field (NEW)

**Current**: Programs have agent_commission and system_commission (money only)

**NEW**: commission_type field

```
Field: commission_type (Radio/Select)

Options:
( ) Money Commission
    Shows: agent_commission, system_commission fields
    
( ) Scholarship Commission  
    Shows: Info box with university's scholarship thresholds
    Hides: agent_commission, system_commission

Reactive: When changed, show/hide relevant fields
```

**What happens to existing programs?**
- All set to "Money Commission" by default
- agent_commission and system_commission still used
- No disruption to existing system

---

### 3. APPLICATION FORM UPDATES

#### Agent Side Changes

**Current Flow**:
```
Select student → Select program → Upload documents → Payment proof → Submit
```

**NEW Flow**:
```
Select student
  ↓
Select university
  ↓
[System checks for scholarships]
  ↓
Select degree (shows degrees at that university)
  ↓
[System validates scholarship match]
  ↓
Select program (filtered by university + degree)
  ↓
[If scholarship available & matches]
  ↓
Show "Use Scholarship?" checkbox ✅
  ↓
If checked: Hide payment proof fields
If not checked: Show payment proof (normal)
  ↓
Upload documents
  ↓
Submit
```

**New Fields to Add**:
```
1. university_id (before program selection)
   - Helps with scholarship detection
   - Cascades to degree and program

2. degree_id (after university)
   - Filters programs
   - Validates scholarship

3. use_scholarship (Checkbox) [conditional]
   - Only visible if agent has matching scholarship
   - Default: checked (suggested)
   - If checked: is_scholarship_application = true

4. scholarship_commission_id (Hidden)
   - Auto-filled when use_scholarship checked
   - Links to which scholarship is being used
```

**Visual Changes**:
```
When scholarship available:

┌────────────────────────────────────┐
│ 🎉 SCHOLARSHIP AVAILABLE!          │
│                                    │
│ You have 1 Harvard Master's        │
│ scholarship available!             │
│                                    │
│ ☑️ Use this scholarship (FREE)     │
│                                    │
│ Save $50,000 in tuition!           │
└────────────────────────────────────┘

[Payment Proof section hidden when checked]
```

---

#### Admin Side Changes (Quick Review)

**NEW Status**: "Pending Quick Review"

**When Application Submitted**:
```
OLD: Status → "Submitted", visible to all admins

NEW: Status → "Pending Quick Review", visible ONLY to super admin
```

**Quick Review Page/Modal**:
```
Show application summary
  ↓
Show program's commission settings
  ↓
Show agent's scholarship progress (if scholarship program)
  ↓
SUPER ADMIN MUST CHOOSE:
( ) Money Commission
( ) Scholarship Commission
  ↓
Optional: Assign to team member
  ↓
Optional: Add review notes
  ↓
Click "Approve & Send to Team"
  ↓
Status → "Submitted"
commission_type field set
super_admin_reviewed = true
```

**New Fields on Application**:
```
1. commission_type (Enum: money, scholarship, not_applicable)
   - Set during quick review
   - Determines what happens on approval

2. is_scholarship_application (Boolean)
   - True if agent used their scholarship
   - Means no payment proof required

3. scholarship_commission_id (Foreign key, nullable)
   - Which scholarship agent used (if any)
   - Links to scholarship_commissions table

4. super_admin_reviewed (Boolean)
   - False when first submitted
   - True after quick review
   - Gates visibility to admin staff

5. super_admin_review_notes (Text, nullable)
   - Notes from quick review
   - Visible to assigned admin

6. super_admin_reviewed_at (Timestamp)
   - When quick review happened

7. super_admin_reviewed_by (Foreign key)
   - Which super admin did the review
```

---

## 🎓 Degree Management Question

### Option 1: Fixed Seeded List (RECOMMENDED) ⭐

**Approach**:
- Create degrees table
- Seed with standard degrees
- NO admin interface for creating/editing
- Degrees are system-defined constants

**Degrees to Seed**:
1. Certificate
2. Diploma
3. Associate Degree
4. Bachelor's Degree
5. Master's Degree
6. Doctoral Degree (PhD)
7. Professional Degree (MD, JD, etc.)

**Pros**:
- ✅ Simple
- ✅ Consistent
- ✅ Standard terminology
- ✅ No user error
- ✅ Less management overhead

**Cons**:
- Limited flexibility (but do we need more?)

**Database**:
```
degrees table:
- id (1, 2, 3...)
- name ("Bachelor's Degree")
- slug ("bachelors-degree")
- order (for sorting: 1=Certificate, 6=PhD)
- is_active (always true)
```

**In Forms**:
```php
Select::make('degree_id')
    ->options(Degree::orderBy('order')->pluck('name', 'id'))
    ->required()
```

---

### Option 2: Full Degree Management

**Approach**:
- Create DegreeResource
- Super admin can create/edit/delete degrees
- Full CRUD interface

**Pros**:
- Maximum flexibility
- Can add custom degrees

**Cons**:
- ❌ Over-engineered
- ❌ Risk of inconsistency
- ❌ More maintenance
- ❌ Scholarship logic breaks if degrees deleted

**Example Issues**:
- Admin creates "Masters" and "Master's Degree" (duplicates)
- Admin deletes "Bachelor's" but programs still reference it
- Agents confused by non-standard degree names

**Verdict**: NOT recommended

---

### Option 3: Hybrid (Seeded + Super Admin Can Add)

**Approach**:
- Seed standard degrees
- Lock standard ones (can't edit/delete)
- Super admin can add custom degrees

**Pros**:
- Flexibility + consistency

**Cons**:
- Still complex
- Rarely needed

**When useful**:
- Unique programs (Executive MBA = separate from MBA?)
- Country-specific degrees

**Verdict**: Start with Option 1, add this later if needed

---

## 📝 Detailed Form Mockups

### 1. University Form (Updated)

```
CREATE / EDIT UNIVERSITY

Basic Information
─────────────────────────────────────
Name: * [Harvard University]
Location: [Cambridge, Massachusetts, USA]
Is Active: ☑️

Scholarship Program Settings (Collapsible)
─────────────────────────────────────
Enable Scholarship Program: ☑️

[When enabled, shows:]

Agent Scholarship Threshold: *
[5] students = 1 scholarship
(Min: 1, Max: 20)
Help: "Agents need to get this many approved students (same degree level) to earn 1 scholarship"

System Admin Scholarship Threshold: *
[4] students = 1 scholarship  
(Min: 1, Max: 20)
Must be LESS than agent threshold!
Help: "You receive scholarship from university at this threshold"

💡 Your Margin Calculation:
════════════════════════════════════
If 20 students approved:
- You receive: 5 scholarships (20/4)
- Agents earn: 4 scholarships (20/5)
- Your profit: 1 scholarship ✅

[Save University]
```

**Validation**:
- admin_threshold < agent_threshold (required)
- Both thresholds between 1-20
- If scholarship_enabled = true, both thresholds required

---

### 2. Program Form (Complete Redesign)

**Current**: Single form, university first

**NEW**: Degree first, clearer commission settings

```
CREATE / EDIT PROGRAM

Classification
─────────────────────────────────────
Degree Level: *
[Master's Degree ▼]

Options:
- Certificate
- Diploma
- Associate Degree
- Bachelor's Degree
- Master's Degree
- Doctoral Degree (PhD)
- Professional Degree

University: *
[Harvard University ▼]
[Search universities...]

💡 Scholarship Info:
Harvard offers scholarship program:
- Agent threshold: 5 students
- System threshold: 4 students
✅ Scholarships enabled

Program Details
─────────────────────────────────────
Program Name: *
[MBA in Business Administration]

Program Code:
[MBA-2025]

Tuition Fee: *
[$50,000]

Duration:
[2] years

Description:
[Full-time MBA program...]

Commission Settings
─────────────────────────────────────
This program will offer: *

( ) Money Commission Only
    Agent Commission: [$5,000]
    System Commission: [$1,000]
    
(•) Scholarship Points Only
    💡 Uses Harvard's scholarship program:
    - Agents: 5 students = 1 scholarship
    - System: 4 students = 1 scholarship
    - Your margin: 1 per 20 students
    
    Note: agent_commission and system_commission
    fields are disabled when scholarship selected

Status
─────────────────────────────────────
Is Active: ☑️

[Save Program]
```

**Technical Implementation**:
```php
// Reactive commission_type
->reactive()
->afterStateUpdated(function ($state, callable $set) {
    if ($state === 'scholarship') {
        $set('agent_commission', null);
        $set('system_commission', null);
    }
})

// Conditional field visibility
TextInput::make('agent_commission')
    ->visible(fn (callable $get) => $get('commission_type') === 'money')
    ->required(fn (callable $get) => $get('commission_type') === 'money')
```

**Migration Strategy**:
```
Existing programs:
- All have agent_commission and system_commission values
- Default commission_type = 'money'
- No disruption to existing data
```

---

### 3. APPLICATION FORM UPDATES

#### Agent Application Creation

**Current**:
```
student_id → program_id → documents → payment → submit
```

**NEW Order**:
```
student_id → university_id → degree_id → program_id → scholarship? → documents → payment → submit
```

**Form Layout**:
```
CREATE APPLICATION

Student Information
─────────────────────────────────────
Student: *
[John Smith ▼]
[Search students...]

Program Selection
─────────────────────────────────────
University: *
[Harvard University ▼]

[If agent has ANY Harvard scholarships, show info icon]
💡 You have 1 Harvard scholarship available!

Degree Level: *
[Master's Degree ▼]

[If scholarship exists for Harvard + Master's, show banner]
┌────────────────────────────────────┐
│ 🎉 SCHOLARSHIP AVAILABLE!          │
│                                    │
│ You have 1 Harvard Master's Degree │
│ scholarship available!             │
│                                    │
│ ☑️ Use this scholarship (FREE)     │
│                                    │
│ Savings: $50,000 in fees!          │
└────────────────────────────────────┘

Program: *
[MBA in Business Administration ▼]
[Filtered to show only Harvard Master's programs]

Documents
─────────────────────────────────────
[Upload documents...]

Payment Information [HIDDEN if scholarship used]
─────────────────────────────────────
Payment Proof: * [Upload]

Additional Notes
─────────────────────────────────────
[Text area...]

[Submit Application]
```

**Backend Logic**:
```php
// On university + degree selection
->afterStateUpdated(function ($state, callable $get, callable $set) {
    $universityId = $get('university_id');
    $degreeId = $get('degree_id');
    $agentId = auth()->id();
    
    // Check for available scholarship
    $scholarship = ScholarshipCommission::where('agent_id', $agentId)
        ->where('university_id', $universityId)
        ->where('degree_id', $degreeId)
        ->where('status', 'available')
        ->first();
    
    if ($scholarship) {
        $set('has_scholarship_available', true);
        $set('available_scholarship_id', $scholarship->id);
        $set('use_scholarship', true); // Default to using it
    }
})

// Hide payment when scholarship used
->visible(fn (callable $get) => !$get('use_scholarship'))
```

---

#### Super Admin Quick Review

**NEW Page/Modal**: `QuickReview`

**When Opened**:
```
QUICK APPLICATION REVIEW
APP-2025-XYZ123

Application Summary
─────────────────────────────────────
Student: John Smith (22 years old)
Agent: Sarah Johnson
Submitted: 2 hours ago

Program Details
─────────────────────────────────────
University: Istanbul University
Degree: Master's Degree  
Program: MBA in Business Administration
Tuition: $45,000

Is Scholarship Application: No ❌
(Agent didn't use scholarship)
Payment Proof: ✅ Uploaded and verified

Istanbul Master's Scholarship Program
─────────────────────────────────────
✅ Scholarships enabled at this university

Thresholds:
- Agents need: 5 students = 1 scholarship
- System needs: 4 students = 1 scholarship

Agent Sarah's Progress:
- Istanbul Master's: 3/5 students
- If you choose scholarship: Will become 4/5

Your Inventory Status:
- Current: 2.3 scholarships
- If you choose scholarship: +0.25

Commission Type Decision *
─────────────────────────────────────
For THIS application, choose:

( ) Money Commission
    Agent gets: $5,000 cash
    System gets: $1,000 cash
    Total cost: $6,000
    
(•) Scholarship Point [Recommended for inventory growth]
    Agent gets: +1 point (4/5 progress)
    System gets: +0.25 scholarship to inventory
    Total cost: $0 (deferred to scholarship)
    
( ) Not Applicable
    No commission (special case)

Quick Validation Checks
─────────────────────────────────────
☑️ All documents uploaded
☑️ Student information complete
☑️ Agent authorized and active
☐ Payment proof verified
☑️ Program active and available

Team Assignment
─────────────────────────────────────
Assign to: [John (Admin Staff) ▼]
Or leave blank to handle yourself

Review Notes
─────────────────────────────────────
[Standard MBA application, all docs look good...]

[Approve & Forward] [Reject] [Need More Info]
```

**What this creates**:
- Sets `commission_type` field
- Sets `super_admin_reviewed` = true
- Sets `super_admin_reviewed_at` = now
- Sets `super_admin_reviewed_by` = your user_id
- Sets `assigned_admin_id` if team member selected
- Status changes: "Pending Quick Review" → "Submitted"

---

## 🎯 Degree Management: My Recommendation

### Recommended Approach: Seeded + Locked ⭐

**Implementation**:
```
1. Create degrees table
2. Create seeder with standard degrees
3. Add 'is_system' field (true for seeded, false for custom)
4. NO DegreeResource for now
5. If custom degrees needed later, add with restrictions
```

**Seeded Degrees**:
```php
[
    ['name' => 'Certificate', 'slug' => 'certificate', 'order' => 1, 'is_system' => true],
    ['name' => 'Diploma', 'slug' => 'diploma', 'order' => 2, 'is_system' => true],
    ['name' => 'Associate Degree', 'slug' => 'associate-degree', 'order' => 3, 'is_system' => true],
    ['name' => "Bachelor's Degree", 'slug' => 'bachelors-degree', 'order' => 4, 'is_system' => true],
    ['name' => "Master's Degree", 'slug' => 'masters-degree', 'order' => 5, 'is_system' => true],
    ['name' => 'Doctoral Degree (PhD)', 'slug' => 'phd', 'order' => 6, 'is_system' => true],
    ['name' => 'Professional Degree', 'slug' => 'professional', 'order' => 7, 'is_system' => true],
]
```

**Why This Works**:
- ✅ Covers 99% of use cases
- ✅ Standard terminology
- ✅ No management overhead
- ✅ Can't be accidentally deleted
- ✅ Consistent across system

**If You Ever Need Custom Degrees**:
- Add DegreeResource later
- Only super admin access
- System degrees can't be edited/deleted
- Custom degrees can be added
- Use case: Country-specific qualifications

**My Strong Recommendation**: Start with fixed seeded list. You likely won't need custom degrees.

---

## 🔄 Data Migration Strategy

### Migrating Existing degree_type to degree_id

**Current programs table**:
- degree_type: "Certificate", "Diploma", "Bachelor", "Master", "PhD"

**Migration Steps**:
```
1. Create degrees table
2. Seed with standard degrees
3. Add degree_id to programs (nullable)
4. Run data migration:
   - "Certificate" → degree_id = 1
   - "Diploma" → degree_id = 2
   - "Bachelor" → degree_id = 4
   - "Master" → degree_id = 5
   - "PhD" → degree_id = 6
5. Make degree_id required
6. Keep degree_type for backup (or drop after verification)
```

**Migration Code** (pseudo):
```php
$degreeMap = [
    'Certificate' => Degree::where('slug', 'certificate')->first()->id,
    'Diploma' => Degree::where('slug', 'diploma')->first()->id,
    'Bachelor' => Degree::where('slug', 'bachelors-degree')->first()->id,
    'Master' => Degree::where('slug', 'masters-degree')->first()->id,
    'PhD' => Degree::where('slug', 'phd')->first()->id,
];

Program::chunk(100, function ($programs) use ($degreeMap) {
    foreach ($programs as $program) {
        $program->degree_id = $degreeMap[$program->degree_type] ?? null;
        $program->save();
    }
});
```

**Safe & Reversible!**

---

## 🎨 Form Update Priority

### Phase 1: University Form (Week 1)
- Add scholarship_enabled toggle
- Add scholarship_agent_threshold
- Add scholarship_admin_threshold
- Add margin calculator (read-only display)
- Validation rules
- Test with dummy data

### Phase 2: Degree System (Week 1)
- Create degrees table
- Create Degree model
- Seed standard degrees
- NO admin interface (fixed list)
- Test relationship

### Phase 3: Program Form (Week 2)
- Migrate degree_type → degree_id
- Update form to use degree_id dropdown (keep in same position)
- Add commission_type field (radio: money or scholarship)
- Make agent/system_commission conditional
- Show scholarship info when scholarship selected
- Test cascading (degree filters programs)

### Phase 4: Application Form - Agent (Week 3)
- Add university_id selection (before program)
- Add degree_id selection (after university)
- Add scholarship detection logic
- Add "use_scholarship" checkbox
- Conditional payment fields
- Test scholarship redemption flow

### Phase 5: Quick Review (Week 4)
- Create QuickReview page/modal
- Add "Pending Quick Review" status
- Build review interface
- Commission type selection
- Team assignment
- Update application status workflow
- Test review process

---

## ❓ Questions for Final Confirmation

### Q1: Degree Dropdown Position

**In program form, where should degree be?**

Option A: FIRST field (before university)
```
Degree → University → Program Name → Details
```

Option B: After university
```
University → Degree → Program Name → Details
```

**Current form has**: University → Name → degree_type

**My recommendation**: Keep it simple - put degree after university (Option B) to minimize disruption.

### Q2: Commission Type - Keep Money Fields?

**Current programs have agent_commission and system_commission with values**

Option A: Keep fields, make conditional
- If commission_type = 'money': use these fields
- If commission_type = 'scholarship': ignore these fields

Option B: Migrate to new structure
- Create separate commission_settings JSON field
- More flexible but more complex

**Recommendation**: Option A (simpler, backwards compatible)

### Q3: Quick Review - Page or Modal?

**Option A**: Dedicated page (better for complex review)
**Option B**: Modal popup (faster, less context switching)

**Recommendation**: Start with Modal (faster), can convert to page if too cramped

### Q4: Scholarship Detection - When?

**In application creation, when do we check for scholarships?**

Option A: On university selection (might be multiple degrees)
Option B: On university + degree selection (precise)

**Recommendation**: Option B (wait for both university and degree to be selected)

---

## 💡 My Implementation Recommendations

### 1. Degrees: Fixed Seeded List ⭐
- No management interface
- Seed 7 standard degrees
- Simple, consistent, zero overhead

### 2. University: Add Collapsible Section
- Scholarship settings optional
- Clear margin calculator
- Validation: admin < agent threshold

### 3. Program: Minimal Changes
- Convert degree_type → degree_id
- Add commission_type radio
- Conditional fields for money settings
- Keep same form layout otherwise

### 4. Application: Smart Scholarship Detection
- Add university + degree selection before program
- Auto-detect scholarships
- Suggest usage (default checked)
- Hide payment if used

### 5. Quick Review: Modal First
- Fast workflow
- All info visible
- Can upgrade to page later if needed

---

## 🚧 Potential Challenges & Solutions

### Challenge 1: Form Complexity
**Issue**: Too many fields, users confused

**Solutions**:
- Use collapsible sections
- Show/hide conditional fields
- Help text everywhere
- Visual margin calculator
- Field dependencies (reactive)

### Challenge 2: Data Migration
**Issue**: Existing programs use degree_type

**Solutions**:
- Careful migration with backup
- Keep degree_type temporarily
- Verify all mappings
- Rollback plan

### Challenge 3: Quick Review Workload
**Issue**: Super admin must review every application

**Solutions**:
- Fast review interface (one-click for standard cases)
- Bulk review option
- Auto-suggestions based on program settings
- Can delegate to trusted admin staff later

### Challenge 4: Scholarship Detection Edge Cases
**Issue**: Agent has multiple scholarships for different degrees

**Solutions**:
- Clear UI showing which scholarship matches
- Prevent using wrong scholarship
- Validation before submission
- Error messages if mismatch

---

## ✅ Final Implementation Plan

### University Form
- ✅ Add scholarship section (collapsible)
- ✅ 3 new fields + calculated margin display
- ✅ Simple validation
- ✅ Backwards compatible (all fields optional)

### Degree Management
- ✅ Create table + model
- ✅ Seed with 7 standard degrees
- ✅ NO admin interface (fixed list)
- ✅ Can add management later if needed

### Program Form
- ✅ Convert degree_type → degree_id (keep same position)
- ✅ Add commission_type radio (money or scholarship)
- ✅ Conditional money commission fields
- ✅ Show scholarship info when relevant
- ✅ Migration strategy for existing data

### Application Form (Agent)
- ✅ Add university dropdown (before program)
- ✅ Add degree dropdown (cascades to program)
- ✅ Auto-detect scholarships
- ✅ "Use scholarship" checkbox (default: yes)
- ✅ Conditional payment fields

### Application (Super Admin)
- ✅ Add Quick Review status
- ✅ Build review modal/interface
- ✅ Commission type selector
- ✅ Team assignment
- ✅ 7 new tracking fields on applications table

---

**Ready to implement after your confirmation of these approaches!** 🚀

Are these approaches good? Any changes you'd like to the forms?

