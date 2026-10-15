# Sutomo school operations: product and UX design brief

**Version:** 0.1 - stakeholder review
**Prepared:** 12 October 2026
**Audience:** school leadership, enrollment staff, teachers, Yayasan, HR/Finance,
product designer and engineering team
**Scope:** Enrollment, Supervision, Leave & Substitution, Unit Planning,
Timetable & Health, and Contract Management
**Implementation baseline:** `0d4d9e271c32a3247a68f252a41b53a39b83b7e0`

## 1. Purpose and how to use this brief

Design a coherent operations workspace that helps staff answer four questions:

1. What needs my attention?
2. What is the current state of this case?
3. What evidence or decision is needed next, and who owns it?
4. What will happen if I take this action?

This is a design and delivery brief, not a claim that every capability below
already exists. Use these labels throughout:

- **Baseline:** behavior evidenced by the current implementation and flow library.
- **Proposed:** recommended experience or requirement requiring implementation.
- **Decision required:** policy, ownership or scope that stakeholders must approve.

Screen names and navigation below are design proposals; they are not new routes.
Example targets are not delivery estimates or approved school policies.

The [Archify flow library](../.archify/README.md) contains 13 diagrams describing
the existing system. Use it alongside this brief: diagrams explain current
behavior; this brief defines how to make that behavior understandable and what
gaps require decisions before design or engineering changes.

Supervision was investigated separately for this brief; a supervision flow is
not yet part of the committed diagram library.

## 2. Product objectives and boundaries

### Objectives

- Reduce missed handoffs across staff, principal, teacher, Yayasan and HR/Finance.
- Make next actions and blockers visible without opening multiple records.
- Distinguish an intention, approval, assignment, payment confirmation and actual
  completion; never collapse them into a single reassuring status.
- Preserve evidence and decision history through the life of each case.
- Replace misleading placeholder controls and success messages with truthful UX.
- Give designers and developers a shared, testable definition of each screen.

### In scope

- Role-oriented work queues, record details, forms, reviews, handoffs and reports.
- Current enrollment tracks, observation review, cover search, unit content,
  scheduling and contract paths.
- Shared status language, notifications, documents, permissions and audit design.
- Empty, loading, error, partial-success, expired and concurrent-update states.
- Desktop-first operational design with responsive teacher-facing interactions.

### Not assumed or included without approval

- Automated bank settlement, payment gateway or bank virtual-account integration.
- A public parent application portal or a new student information system.
- A working Yayasan portal/API, HR system integration or refund execution.
- Realtime collaborative editing or presence based only on displayed avatars.
- A production delivery service simply because an email action exists.
- Automatic employment decisions based on observation scores or rankings.
- New attendance, payroll, finance or recruitment modules beyond necessary handoffs.

## 3. Users, responsibilities and access

The responsibilities below guide design. They are a proposed access model,
except for explicitly noted baseline checks. Do not infer authorization from
navigation visibility or a role label alone.

| Role / responsibility | Primary tasks | Proposed boundary |
|---|---|---|
| Enrollment operator | Intake, documents, placement scheduling, onboarding records | Access only assigned school/campus records; cannot invent approval or payment authority |
| Payment verifier / Finance | Verify payment evidence, resolve payment discrepancies | Explicit payment authority; corrections retain original evidence |
| Principal / vice principal | Review observations, leave/cover decisions, planning, contracts | Assigned organizational scope and distinct review permissions |
| Teacher / observee | Respond to cover invitations, sign own letters, view feedback | Own records only; no implied access to other teachers' private evaluations |
| Observer / peer observer | Record classroom evidence and follow-up recommendations | Assigned observations; cannot review their own observation |
| Yayasan decision owner | Decide contract renewal and provide contract documents | External recorded handoff until a separate authenticated surface is approved |
| HR / Finance handoff recipient | Employment documents, registration markers, refund follow-up | Confirm receipt/execution separately from a flag set by another role |
| Configuration administrator | Academic periods, criteria, limits, scheduling configuration | Scoped configuration access with change history |

**Baseline:** observation review permits principal, vice_principal, admin and
superadmin; only pending records may be reviewed and the observer cannot review
their own record. New observation creation overwrites observer_id with the
authenticated user, despite the form exposing an observer selector.

**Decision required:** approve the role/permission matrix, campus scope, delegate
authority and who is authorized to verify payments. A person may hold several
responsibilities, but separation-of-duties rules still need enforcement.

## 4. Shared information architecture

### Proposed navigation

- **Home / My work:** actionable queues grouped by due date and ownership.
- **Enrollment:** periods, applications, placement, onboarding.
- **Supervision:** observations, review queue, teacher goals, configuration.
- **Leave & Cover:** leave requests, cover searches, responses, duty handoff.
- **Planning:** Units & Topics, Timetable, Health, published versions.
- **Contracts:** probation, tier renewal, LOIs, continuation, follow-up.
- **Shared records:** people, documents and history only as permitted.

Academic year, campus and term selectors must visibly identify which module
they affect. Where the baseline is fixed to a year or campus, show that limitation
rather than presenting a selector that appears functional.

### Shared screen anatomy

Every operational record should contain:

1. **Identity header:** case number, person, campus, academic year/term.
2. **Status summary:** separate chips for independent state dimensions.
3. **Next action:** owner, blocker, deadline and primary action.
4. **Evidence:** documents, timestamps, recorded facts and verification state.
5. **History:** who changed what, when, why and from which prior state.
6. **Secondary actions:** export, correction, withdrawal, archive or escalation.

Use tabs for alternate views of the same record, not as implied progress steps.
Use a stepper only when stages genuinely have prerequisites.

## 5. Shared interaction and visual requirements

### Visual direction

- Calm, professional school administration interface; high information density
  without making every value compete for attention.
- Consistent typography, spacing, page headers, forms, tables and side panels
  across the Filament-based workspace.
- Color is supplementary: every state has readable text and, where useful, an
  icon. Approved and paid must not share ambiguous wording.
- Strong hierarchy for next action, blockers and deadlines; restrained decoration.
- Preserve familiar Indonesian school terms such as Yayasan, Buku Induk and PKWT.
  Agree primary UI language and translations before final copy.

### Interaction rules

- Confirm destructive or consequential actions with an explicit impact summary.
- Explain disabled actions beside the control: missing evidence, wrong owner,
  already reviewed, expired response or unmet prerequisite.
- Revalidate authoritative state on submit; handle stale records visibly.
- Separate "saved", "submitted", "delivered", "received" and "completed".
- Document uploads show allowed formats, configured size limit, progress,
  validation failure and replacement/version behavior.
- Explain partial success: an offer may be stored even when email delivery fails.
- Do not show unwired creation/attachment buttons as working features.
- Show "No records", "No matching records", "Not authorized" and "Unavailable"
  as different conditions.
- Retain search/filter context after record review and support deep links safely.

### Accessibility and responsive design

- Proposed target: WCAG 2.2 AA, subject to acceptance by the team.
- Keyboard access, visible focus, programmatic labels and field-level errors.
- Text contrast at least 4.5:1 for normal text and 3:1 for large text.
- Minimum 24-by-24 CSS pixel targets, with larger targets preferred on teacher
  mobile response/signing screens.
- Accessible alternative to drag-and-drop timetable placement.
- At narrow widths, offer readable lists rather than compressing a weekly grid
  into illegible cells. Do not rely on hover for critical information.
- Error summaries link to invalid fields; asynchronous status changes are
  announced without moving focus unexpectedly.

## 6. Enrollment

**Flow reference:** [Enrollment explorer](../.archify/workflow-enrollment-20261012-084142/index.html).

### Outcome and ownership

Move an applicant from recorded intake through verified payment and placement
to the correct onboarding track, with evidence-backed staff decisions.
Enrollment staff own the case; payment and admission authority must be agreed.

### Baseline to preserve

- Enrollment periods are managed by staff; intake and payment verification are
  manual, not bank callbacks.
- Placement defaults: score >=70 passes; 50-69 is waitlisted; below 50 fails.
  Configured thresholds, not hard-coded UI copy, should drive presentation.
- Passed applications already enter accepted onboarding visibility; an extra
  acceptance action is not universally required.
- Track A: TK, playgroup, pre-nursery and non-Plus SD; interview/test followed
  by combined registration/donation payment and application activation.
- Track B: development fee, books, class, at least five present observation
  marks, student ID/tuition VA, CCA plus e-books, then tuition and activation.
- Displayed stage locks are primarily UI progression; do not claim universal
  server-side enforcement.
- Activation updates an Application; these actions do not create a Student.
- Reinstatement retains previously completed metadata.

### Proposed screens

| ID | Screen | Required content and interactions |
|---|---|---|
| ENR-01 | Period workspace | Dates, open/closed state, applicant counts, applicable campus/stream; explicit open/close impact |
| ENR-02 | Application queue | Search, period/track/status/payment filters; owner, deadline, next action and document gaps |
| ENR-03 | Applicant detail | Applicant/guardian information, documents, payment ledger, placement result, track and history |
| ENR-04 | Payment verification | Evidence preview, amount/reference/date, discrepancy reason, verifier and confirmed timestamp |
| ENR-05 | Placement workspace | Eligible applicants, session assignment, score entry, effective thresholds and result review |
| ENR-06 | Onboarding workspace | Track-specific stepper; current stage, completed evidence and exact blocker on later stages |
| ENR-07 | Exception review | Waitlist, withdrawal and reinstatement with reason, retained progress and impact confirmation |

### Data and reporting

Period, Application, placement session/result and Application metadata are the
current operational anchors. Design proposed reporting around funnel counts by
period/track, payment-verification backlog, time in stage and activation counts.
Define denominators and date range; an activated application is not automatically
an enrolled student in reporting.

### Acceptance criteria

- **ENR-A1:** with default thresholds, 49 fails, 50 and 69 waitlist, and 70 passes;
  configured thresholds are displayed and tested separately.
- **ENR-A2:** Track A never shows books/observation/tuition as mandatory stages.
- **ENR-A3:** Track B cannot visually report observation complete with four
  present marks; absent/leave marks do not count toward five.
- **ENR-A4:** payment confirmation identifies verifier and evidence; the UI
  never represents manual verification as bank settlement.
- **ENR-A5:** reinstatement preserves completed stage evidence and shows the
  correct next incomplete stage.
- **ENR-A6:** activation wording states the affected Application record until
  student provisioning is implemented and verified.

**Decisions required:** student provisioning ownership; duplicate applicant
policy; payment correction/reversal policy; stage enforcement; waitlist priority;
guardian access; admission and payment retention periods.

## 7. Supervision and professional development

**Source anchors:** [observation resource](../app/Filament/Principal/Resources/TeacherObservationResource.php),
[observation model](../app/Models/TeacherObservation.php),
[configuration](../app/Filament/Principal/Pages/ObservationConfig.php),
[teacher goals](../app/Filament/Principal/Resources/TeacherGoalResource.php).

### Outcome and ownership

Capture credible teaching evidence, review it independently, communicate useful
feedback and track teacher development without converting incomplete data into
an employment decision.

### Baseline to preserve and clarify

- An observation records observee, observer, date/time, lesson subject/class,
  criterion scores/notes, strengths, action items and follow-up date.
- Active configurable criteria populate the form; fallback dimensions are
  Engagement, Clarity, Pacing and Classroom Management.
- Score selectors offer 1-5 but are not all mandatory.
- Average score is an unweighted mean of numeric stored dimensions, rounded to
  one decimal. Configuration weights are not used by this model calculation.
- Only pending observations may become approved/rejected; rejection notes are
  required by the review form. Observer self-review is blocked server-side.
- Review stores reviewer, timestamp and notes; audit writing is best-effort.
- Academic year, semester and observation type exist in the model/schema but
  are absent from the current observation entry form.
- Types include principal supervision, peer observation, external evaluator
  and self-reflection. Do not imply a usable type-selection flow already exists.
- Teacher goals track target date, year, status and 0-100 progress separately.
- Dashboard scheduled/completed metrics use SupervisiEvaluation, while pending
  review uses TeacherObservation. These are different record populations.
- Contract/probation observation counts are not equivalent to approved evidence:
  the inspected count queries do not filter to approved observations.

### Proposed workflow

Prepare observation context -> record classroom evidence -> submit for an
independent review -> approve or reject with feedback -> agree development goals
-> follow up. Preparation, submission conventions and follow-up closure need
explicit design/implementation; the current resource is not proof of this entire
end-to-end lifecycle.

### Proposed screens

| ID | Screen | Required content and interactions |
|---|---|---|
| SUP-01 | Supervision workspace | Distinct scheduled evaluations, observation review queue and overdue follow-ups; defined metric sources |
| SUP-02 | Observation entry | Teacher, authenticated observer, year/semester/type, lesson context, rubric, evidence notes and coverage completeness |
| SUP-03 | Review detail | Scores and narrative together; reviewer identity, approve/reject actions, self-review explanation |
| SUP-04 | Teacher development | Reviewed observations, strengths, agreed goals, target dates and follow-up history |
| SUP-05 | Criteria configuration | Stable key, label, description, order, active flag, scoring/version policy |
| SUP-06 | Comparison | Optional restricted view with period, data completeness and transparent metric definitions; no employment recommendation |

### Acceptance criteria

- **SUP-A1:** the authenticated observer cannot approve/reject their own pending
  record, even through a direct action request.
- **SUP-A2:** a second review of an already-reviewed record returns a visible
  conflict instead of silently replacing the first decision.
- **SUP-A3:** scores 3 and 4 average to 3.5; missing scores are labeled incomplete,
  not counted as zero. Proposed rubric changes need a versioned calculation.
- **SUP-A4:** the observer field reflects authenticated creation behavior rather
  than allowing a selection that will silently be overwritten.
- **SUP-A5:** scheduling counts and review counts identify their populations;
  they are not presented as a single conversion funnel without a proven link.
- **SUP-A6:** goal progress and statuses remain distinct; proposed completion
  rules must not be inferred from a percentage alone.

**Decisions required:** rubric completeness, weighting, historical rubric versions,
editing reviewed records, approved-only contract evidence, scheduling linkage,
teacher feedback visibility and confidentiality of comparison/ranking.

## 8. Leave and substitution

**Flow reference:** [Leave & Substitution explorer](../.archify/workflow-leave-substitution-20261012-090931/index.html).

### Outcome and ownership

Make leave decisions and teaching-cover responsibility clear while reducing the
time staff spend finding suitable replacements.

### Baseline to preserve

- The principal's on-behalf submission has three paths: no-cover types and
  pre-picked substitutes can approve immediately; auto-search creates pending leave.
- Ranking considers preferences and suitability. Approved own leave, approved
  cover and absent/leave attendance are hard conflicts; duties are soft conflicts.
- Search defaults are 24 hours, 15 invitations and five interested responses.
- Accepting an invitation means interested, not assigned. Principal selection
  assigns a substitute but does not approve the leave.
- Cap/manual closure cancels pending offers but retains interested offers.
  Assignment cancels other pending/interested offers.
- Signed response routes and offer timestamps enforce expiry on response;
  a scheduled expiry cleanup job is not established by the flow.
- Mail can fail while an offer remains stored; default log mailer is not proof
  of teacher delivery. Assignment does not send the claimed cancellation emails.
- Leave approval may create a pending duty; rejection does not automatically
  close a search.
- The substitution timetable projects approved leave, substitute and duty data;
  it does not rewrite the base timetable.

### Proposed screens

| ID | Screen | Required content and interactions |
|---|---|---|
| LEV-01 | Leave queue | Teacher, dates/type, leave status, cover state, search state and pending decision |
| LEV-02 | Request detail | Eligibility evidence, affected lessons, documents, selected cover, decisions and history |
| LEV-03 | Cover search | Ranked candidates with rationale; hard/soft conflicts, invite count, round and expiry |
| LEV-04 | Teacher response | Mobile-first signed page; dates, lessons, response expiry, Interested/Decline, existing response |
| LEV-05 | Principal selection | Interested candidates, fresh availability check, assignment impact and non-approval warning |
| LEV-06 | Approval & duty | Separate leave decision and optional duty handoff; assignee and acknowledgment state |
| LEV-07 | Coverage roster | Base lesson, approved leave, cover teacher and duty status; explain gaps without altering base lessons |

### Acceptance criteria

- **LEV-A1:** an interested response never displays Assigned or Leave approved.
- **LEV-A2:** selection displays substitute assignment while preserving pending
  leave until an explicit approval action.
- **LEV-A3:** pending, interested, assigned, declined, cancelled and expired
  response conditions have distinct teacher-facing messages.
- **LEV-A4:** hard conflicts prevent invitation/assignment according to approved
  policy; soft duty conflicts are visible rather than hidden.
- **LEV-A5:** a mail failure produces an actionable delivery warning without
  pretending the saved offer was delivered.
- **LEV-A6:** selecting one substitute does not claim others received email
  unless actual delivery was recorded.
- **LEV-A7:** the roster explains approved cover separately from duty acceptance.

**Decisions required:** cap/window configurability; late responses; override
authority; selected-teacher revalidation; rejected-leave search closure;
cancellation notifications; expiry job; required versus optional duty acknowledgment.

## 9. Unit Planning

**Flow reference:** [Planning explorer](../.archify/workflow-planning-20261012-143425/index.html).

### Outcome and ownership

Provide a shared view of teaching intent, objectives, lesson sequence and materials
with a credible discussion trail. A full editable collaborative planner is a
proposed extension, not the current baseline.

### Baseline to preserve

- Existing units support status/search filters and Plan, Lessons and Resources
  views of stored sections.
- Comments are persisted and update last activity.
- New/Add objective/Add topic/Add lesson/Attach controls are not wired.
- "Auto-saved" hints do not demonstrate an editable auto-save feature.
- Author identity and the Mine filter use hard-coded Pak Dwi data.
- Collaborator badges do not establish realtime presence.
- Tabs do not transition status; unit content does not generate timetable demand.

### Proposed screens and interactions

- **PLN-01 Unit library:** year/grade/subject/owner/status filters; distinguish
  empty library from no search matches.
- **PLN-02 Unit detail:** objectives, topics, prior learning, assessment,
  weekly lessons, resources, owner and collaborators.
- **PLN-03 Discussion:** authenticated authors, timestamps, unit context and
  correction rules; maintain permission-aware visibility.
- **PLN-04 Editable planner, if approved:** draft creation, field validation,
  save feedback, version handling and status transitions.

### Acceptance criteria

- **PLN-A1:** only implemented controls look actionable; unavailable features
  are omitted or explicitly identified.
- **PLN-A2:** comments and Mine filtering identify the authenticated person once
  real-user collaboration is implemented.
- **PLN-A3:** switching Plan/Lessons/Resources does not advance unit completion.
- **PLN-A4:** saved versus unsaved changes are truthful; no auto-save claim
  appears without a successful persistence mechanism.
- **PLN-A5:** timetable linkage remains labeled absent until mapping and ownership
  rules are designed, implemented and tested.

**Decisions required:** editable-planner scope; unit ownership; collaborator
permissions; versioning; comment editing; approval/publishing lifecycle; mapping
unit lesson plans to subject-hour requirements.

## 10. Timetable and Health

### Outcome and ownership

Help schedule planners create viable teacher/class schedules, diagnose gaps,
understand generated partial results and capture published versions without
confusing a saved draft with an approved operational timetable.

### Baseline to preserve and clarify

- Builder uses demo teacher profiles and fixed TP 2025/2026, ALL definition.
- Generation removes unlocked lessons and preserves locked lessons; Reset Draft
  also removes unlocked lessons.
- Requirements, teacher class eligibility, loads, caps and availability constrain
  generated placement; breaks are excluded.
- Bounded backtracking can fall back to a partial greedy result with unsolved demand.
- Generation persists placed lessons and scans the whole sheet for conflicts.
- Manual editing stores a lesson even with conflicts, then rescans the lesson
  and related same-slot neighbours.
- Lock flags protect against generation/reset, not manual editing permissions.
- Teacher, class, room and master views project lesson records; the teacher picker
  does not assign a room even though room conflict checks exist.
- Health combines cached conflict flags, subject requirements for classes already
  present and load comparisons. Completely absent classes are not enumerated.
- The UI names unfilled_required, but the report does not emit that category.
- Publishing captures lesson payload and marks the definition published even
  with unresolved errors. It warns, rather than enforcing a clean-health gate.
- Published status does not prevent subsequent live editing/generation.
- Print/export uses live teacher sheets, not necessarily a published snapshot.

### Proposed screens

| ID | Screen | Required content and interactions |
|---|---|---|
| TTB-01 | Scheduling configuration | Explicit year/term/unit, teacher availability, subject requirements, caps and room rules |
| TTB-02 | Draft builder | Teacher/class/room/master views, accessible cell editor, lock meaning and draft state |
| TTB-03 | Generation result | Placed/kept/cleared/unsolved counts, constraint explanations and partial-result warning |
| TTB-04 | Health review | Defined error/warning/info categories, data freshness, counts and safe jump-to-fix |
| TTB-05 | Publication review | Snapshot scope, current issues, publisher, version label and policy-based action |
| TTB-06 | Published versions | Read-only captured payload, live-draft distinction and version-specific export if implemented |

### Acceptance criteria

- **TTB-A1:** generation/reset confirmation states unlocked lessons will be
  removed; locked lessons remain.
- **TTB-A2:** partial generation reports remaining class/subject demand instead
  of showing an unqualified success.
- **TTB-A3:** health and save views distinguish hard errors from advisory warnings;
  issue totals identify whether they count cells or individual issues.
- **TTB-A4:** the UI does not call Health complete when required classes are
  absent unless a new completeness check has been implemented.
- **TTB-A5:** current publishing language explicitly permits remaining conflicts;
  a proposed stricter gate requires school approval and server-side enforcement.
- **TTB-A6:** editing the live draft never changes the displayed captured snapshot.
- **TTB-A7:** exported output states whether it came from a live draft or a named
  published version.

**Decisions required:** publication gate/override policy; real teacher master-data
mapping; configuration editing; room allocation; snapshot consumption by cover
search; post-publication edits; version restore; full health refresh; missing-class
checks and which version is operationally authoritative.

## 11. Contract Management

**Flow reference:** [Contract explorer](../.archify/workflow-contracts-20261012-131158/index.html).

### Outcome and ownership

Track probation, renewal and annual continuation with explicit Yayasan decisions,
principal document review, teacher signatures and HR/Finance handoffs.

### Baseline to preserve

- PKWT renewal is separate from permanent-teacher annual continuation.
- Principal recommendation precedes submission; recommendation file upload is
  supported but not required by the submission method.
- The principal records Yayasan's approved/rejected decision after submission;
  this is not an integrated Yayasan approval API.
- Approved plus renew creates the next permitted tier; rejected or not_renew
  marks contract and teacher not_renewed.
- PKWT-I -> II; II -> III or Guru SK; III -> Guru SK.
- Probation Continue records a decision only; it does not create PKWT-II.
  Terminate changes statuses and flags a refund, not an executed payment.
- Signed LOIs are submitted to Yayasan; upload fields/checks exist but no current
  application action writes the Yayasan contract upload.
- Principal accepts the uploaded document or requests revision with a note.
  This acceptance is not the same as Yayasan's renewal decision.
- Teacher agreement signing requires upload, principal acceptance and the bound
  teacher user; it automatically records Buku Induk/HR uploaded markers.
- Continuation completion checks signed LOI, submission, upload, accepted review,
  agreement signature and Buku Induk. It does not create a TeacherContract.
- Declined LOI follow-up records meeting/outcome/resignation evidence/HR flags;
  closing that case does not itself terminate employment or restore LOI signature.

### Proposed screens

| ID | Screen | Required content and interactions |
|---|---|---|
| CON-01 | Contract workspace | Separate probation, tier renewal, LOI, continuation and resignation queues |
| CON-02 | Renewal detail | Current tier/dates, observation coverage, recommendation and submission history |
| CON-03 | Yayasan decision record | Decision evidence, external decision owner, recorded-by identity, time and next-tier impact |
| CON-04 | LOI detail/signing | LOI state, named signer, deadline, typed name and distinct sign/decline paths |
| CON-05 | Continuation review | External upload dependency, document version, accept/revise notes and review state |
| CON-06 | Agreement & completion | Uploaded/accepted/signature/register markers, completion prerequisites and ownership |
| CON-07 | Follow-up handoff | Meeting notes, outcome, required resignation evidence, HR readiness and closure |

### Acceptance criteria

- **CON-A1:** no label conflates Yayasan approval with principal document acceptance.
- **CON-A2:** the next tier is valid for the current tier before renewal is
  presented as completed; partial failure is surfaced.
- **CON-A3:** a teacher cannot sign another person's LOI/agreement by changing
  the record URL.
- **CON-A4:** an uploaded but unaccepted agreement cannot be shown as ready to sign.
- **CON-A5:** probation Continue and LOI completion never claim a new contract
  was generated.
- **CON-A6:** refund and HR-upload timestamps are described as recorded markers,
  not proven payment execution or independently verified HR receipt.
- **CON-A7:** closing a resignation follow-up does not claim employment termination.

**Decisions required:** Yayasan access/upload owner; acceptable decision evidence;
recommendation-file policy; legally sufficient signature; revocation/correction;
HR receipt acknowledgment; refund execution; declined-LOI recovery and mandatory
approved observation coverage before renewal.

## 12. Cross-module relationships

| Connection | Baseline | Proposed design requirement |
|---|---|---|
| Supervision -> probation/renewal | Observation counts are shown; not filtered to approved | Identify year/type/status and data completeness; decide what qualifies as evidence |
| Leave -> substitute search | Leave and offer records are separate | Keep leave approval, search closure and selection independently visible |
| Substitution -> duty -> coverage | Approved cover plus duties project onto a roster | Display base lesson and handoff acknowledgment separately |
| Timetable -> cover availability | Scheduling context supports operational decisions | Decide whether live draft or published version is authoritative; document conflicts |
| Units -> timetable | No automatic conversion | Do not draw an automated dependency until lesson-to-demand mapping exists |
| Enrollment -> student records | Activation changes Application | Define explicit provisioning handoff before claiming student creation |
| Contracts -> HR/Finance | Flags and timestamps exist | Separate handoff readiness, receipt and actual execution |
| All modules -> notifications | Messages/actions do not prove delivery | Track intent, dispatch, delivery/failure and acknowledgment where required |

Academic year and identity must be consistent across handoffs. Explicitly map
teacher database IDs, user IDs and timetable teacher_ref values; do not assume
that demo reference strings identify the same entity.

## 13. Shared engineering and data requirements

These are proposed delivery requirements, not established guarantees.

- Authorization on every authoritative read/write; organizational scoping tested
  with permitted and denied cases.
- Server-side prerequisite validation for transitions designated mandatory.
- Transactions and stale-state/idempotency handling for multi-record decisions,
  assignment and contract creation.
- Explicit error notification with retained input; no success-shaped fallback
  when saving, sending, approving or handing off fails.
- Record actor, time, before/after state, reason and relevant evidence/version
  for consequential decisions.
- Document access controls and retention policy for applicant records, teacher
  evaluations, leave evidence and signed contracts.
- Non-public access for private files where approved by the data policy; storage
  behavior must be verified rather than inferred from an upload control.
- Notification retry rules, delivery observability and duplicate-send protection.
- Historical scoring and document integrity when configuration changes.
- Derived metrics identify source, period, freshness and completeness.
- Restore/recovery testing for database and document backups.
- Agree timezone, date boundaries and deadline interpretation per workflow.
- Agree performance targets after measuring dataset sizes and current queries;
  do not commit to unmeasured latency targets.

## 14. Design deliverables and handoff package

The designer should produce:

1. **Navigation and role map:** permissions, organizational scope and work queues.
2. **Shared component kit:** tables, filters, forms, evidence viewer, status chips,
   stepper, timeline, confirmation, notification and empty/error/loading states.
3. **Module screen inventory:** the screen IDs in this brief, consolidated where
   practical without hiding independent decisions.
4. **Annotated wireframes:** critical path plus exceptions for each module;
   each action names owner, prerequisites, mutation and failure behavior.
5. **Clickable prototypes:** enrollment tracks, observation review, substitute
   response/selection, generation/health/publish and contract continuation.
6. **Responsive variants:** especially teacher invitation and signature screens.
7. **Content specification:** labels, terminology, notifications and error copy.
8. **State/permission matrix:** UI visibility versus authoritative server rules.
9. **Developer handoff:** fields, validations, source data, event semantics,
   accessibility behavior and acceptance-criterion references.

Every proposed screen must include normal, empty, loading, validation error,
permission denied and stale-state behavior where applicable. Add expiry,
partial-success and document-version cases for relevant flows.

## 15. Delivery sequence

This is a dependency-based recommendation, not an estimate or approved schedule.

### Phase 1: agree facts and policies

- Stakeholders confirm baseline, responsibilities and the decision register.
- Resolve permission/scope, academic-year/identity and data-retention questions.
- Establish publication, payment, evidence and signature policies.

**Exit:** approved scope and policies; unresolved decisions are explicitly deferred.

### Phase 2: shared shell and components

- Define navigation, record anatomy, status vocabulary and accessibility patterns.
- Remove or relabel misleading placeholders in agreed designs.
- Define common audit, document and notification states.

**Exit:** reviewed component kit and role-oriented navigation prototype.

### Phase 3: operational critical paths

- Enrollment intake/payment/placement/track-specific onboarding.
- Supervision entry, independent review and goal follow-up.
- Leave requests, teacher response, principal selection and coverage handoff.
- Each workflow is tested against normal and exception scenarios before expanding.

**Exit:** approved prototypes and engineering-ready acceptance cases.

### Phase 4: scheduling and contract handoffs

- Scheduling inputs, draft generation, health and publication policy.
- Unit planning scope, editable features and collaboration boundaries.
- Yayasan decision/document handoffs, signatures and HR/Finance acknowledgment.

**Exit:** cross-module identities/version semantics agreed and end-to-end scenarios pass.

### Phase 5: pilot and hardening

- Representative users complete realistic tasks in a controlled pilot.
- Validate permissions, accessibility, concurrency, document privacy and failures.
- Measure completion time, rework and missed handoffs against a measured baseline.

**Exit:** pilot issues triaged; operational owner signs off on release readiness.

## 16. Success measures

Agree targets after baseline measurement. Avoid invented percentages.

| Measure | Definition to agree | Useful breakdown |
|---|---|---|
| Enrollment time in stage | Entry-to-completion time for each actual track stage | Period, campus, track |
| Review backlog | Pending observation count and age | Reviewer, year, observation type |
| Cover resolution time | Search start to principal selection; separately to approval | Leave type, campus, unresolved cases |
| Coverage acknowledgment | Accepted duties among duties actually created | Date and assignee |
| Timetable completeness | Required demand satisfied, including absent classes | Definition/version, class, subject |
| Publication quality | Unresolved errors/warnings at capture | Definition/version and override reason |
| Contract turnaround | Submission to decision; separately document review/signature | Tier renewal versus continuation |
| Handoff reliability | Ready cases with independently confirmed receipt/execution | HR, Finance, Yayasan |

Supplement with task success, user error rate, time-on-task, accessibility defects
and support requests. Observation scores and teacher rankings are not themselves
measures of product success.

## 17. Decision register for stakeholder review

| ID | Decision | Accountable responsibility | Affects |
|---|---|---|---|
| DEC-01 | Roles, campus scope, delegation and separation of duties | School leadership + product owner | All modules |
| DEC-02 | Primary language, school terminology and accessibility target | Product/design owner | Shared UX |
| DEC-03 | Academic year, term and identity mapping | Operations + engineering | All handoffs |
| DEC-04 | Payment verification/reversal and student provisioning | Enrollment + Finance | Enrollment |
| DEC-05 | Enrollment thresholds, track rules and server-side prerequisites | Admissions owner | Enrollment |
| DEC-06 | Rubric completeness, weighting/versioning and review corrections | Principal / supervision owner | Supervision |
| DEC-07 | Approved-only evidence and evaluation-scheduling linkage | Principal + HR | Supervision / contracts |
| DEC-08 | Cover overrides, expiry, cancellation and rejected-leave closure | Principal / leave owner | Leave / substitution |
| DEC-09 | Operational timetable version and publication gate | Scheduling owner + school leadership | Timetable / cover |
| DEC-10 | Editable units, collaborator authority and timetable linkage | Curriculum owner | Planning |
| DEC-11 | Yayasan upload/decision ownership and signature sufficiency | Yayasan + HR/legal responsibility | Contracts |
| DEC-12 | HR receipt, refund execution and employment-status transitions | HR + Finance | Contracts |
| DEC-13 | Private documents, retention, notifications and audit reliability | Data owner + engineering | All modules |

Do not begin high-fidelity screens for unresolved policy branches by inventing
the answer. Mark the branch as a decision and design the settled paths first.

## 18. Reference inventory

- [System architecture](../.archify/architecture-sutomo-hr-20261012-081500/sutomo-hr.html)
- [Enrollment flows](../.archify/workflow-enrollment-20261012-084142/index.html)
- [Leave and substitution flows](../.archify/workflow-leave-substitution-20261012-090931/index.html)
- [Contract flows](../.archify/workflow-contracts-20261012-131158/index.html)
- [Planning flows](../.archify/workflow-planning-20261012-143425/index.html)
- [Supervision entry/review](../app/Filament/Principal/Resources/TeacherObservationResource.php)
- [Authenticated observation creation](../app/Filament/Principal/Resources/TeacherObservationResource/Pages/CreateTeacherObservation.php)
- [Observation scoring](../app/Models/TeacherObservation.php)
- [Supervision metric sources](../app/Filament/Principal/Resources/TeacherObservationResource/Widgets/SupervisionStatsOverview.php)
- [Teacher goals](../app/Filament/Principal/Resources/TeacherGoalResource.php)
- [Observation configuration](../app/Filament/Principal/Pages/ObservationConfig.php)
- [Probation evidence counts](../app/Filament/Principal/Pages/ProbationWatch.php)
- [Renewal evidence counts and decisions](../app/Filament/Principal/Pages/ContractRenewal.php)
- [Unit planning actions](../app/Filament/Principal/Pages/UnitPlanning.php)
- [Timetable generation](../app/Services/Timetable/AutoGenerator.php)
- [Health calculations](../app/Services/Timetable/HealthService.php)
- [Snapshot publication](../app/Services/Timetable/PublishService.php)

**Review status:** draft. Proposed requirements, role assignments and policy
decisions need stakeholder approval. No application behavior is changed by this brief.
