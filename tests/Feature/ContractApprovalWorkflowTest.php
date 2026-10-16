<?php

namespace Tests\Feature;

use App\Models\ContractApprovalRequest;
use App\Models\ContractDocumentVersion;
use App\Models\LetterOfIntent;
use App\Models\Teacher;
use App\Models\TeacherContract;
use App\Models\User;
use App\Services\ContractApprovalWorkflow;
use App\Services\StaleRequestException;
use App\Support\ContractRequestPresenter;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Guarantees of the redesigned approval workflow. Runs inside a transaction
 * against the configured database so nothing persists.
 */
class ContractApprovalWorkflowTest extends TestCase
{
    use DatabaseTransactions;

    private ContractApprovalWorkflow $wf;
    private User $principal;
    private User $otherPrincipal;
    private User $yayasan;
    private User $hr;
    private User $otherHr;
    private Teacher $teacher;
    private TeacherContract $contract;

    protected function setUp(): void
    {
        parent::setUp();
        $this->wf = app(ContractApprovalWorkflow::class);

        $mk = fn (string $role, ?string $campus = 'sma') => User::create([
            'name' => ucfirst($role) . ' ' . Str::random(4), 'email' => Str::random(8) . '@test.local',
            'password' => bcrypt('x'), 'role' => $role, 'campus' => $campus,
        ]);
        $this->principal      = $mk('principal');
        $this->otherPrincipal = $mk('principal', 'smp');
        $this->yayasan        = $mk('yayasan', null);
        $this->hr             = $mk('hr');
        $this->otherHr        = $mk('hr');

        $this->teacher  = Teacher::create(['code' => 'T-' . Str::random(5), 'name' => 'Test Teacher', 'status' => 'pkwt_1', 'campus' => 'sma']);
        $this->contract = TeacherContract::create([
            'teacher_id' => $this->teacher->id, 'type' => 'pkwt_1', 'academic_year' => '2026/2027',
            'starts_at' => now()->subMonths(9)->toDateString(), 'ends_at' => now()->addMonths(3)->toDateString(), 'status' => 'active',
        ]);
    }

    private function submitted(): ContractApprovalRequest
    {
        return $this->wf->submitFromContract($this->contract, $this->principal, [
            'recommendation' => 'renew', 'justification' => 'Strong classroom results across both semesters.',
            'proposed_type' => 'pkwt_2', 'proposed_starts_at' => now()->addMonths(3)->addDay()->toDateString(),
        ]);
    }

    private function approvedAndAssigned(): ContractApprovalRequest
    {
        $r = $this->submitted();
        $this->wf->approve($r, $this->yayasan, 'submitted');
        $this->wf->assignHr($r, $this->yayasan, $this->hr->id, 'Use the PKWT-II template.', now()->addWeek()->toDateString(), 'approved');
        return $r->fresh();
    }

    private function uploaded(ContractApprovalRequest $r, User $by): ContractDocumentVersion
    {
        return $this->wf->uploadDocument($r, $by, 'contract-documents/test.pdf', 'test.pdf', 123, 'application/pdf', hash('sha256', 'x'), 'note');
    }

    // ---------- Gating ----------

    public function test_hr_cannot_upload_before_approval_or_assignment(): void
    {
        $r = $this->submitted();
        $this->expectException(\RuntimeException::class);
        $this->uploaded($r, $this->hr);
    }

    public function test_only_the_assigned_hr_can_upload(): void
    {
        $r = $this->approvedAndAssigned();
        $this->expectExceptionMessage('is not the assigned HR');
        $this->uploaded($r, $this->otherHr);
    }

    public function test_principal_can_simulate_hr_actions_only_after_assignment(): void
    {
        $r = $this->submitted();
        $this->wf->approve($r, $this->yayasan, 'submitted');
        $this->expectExceptionMessage('expected one of');
        $this->uploaded($r->fresh(), $this->principal);
    }

    public function test_principal_sees_assigned_hr_work_in_principal_panel(): void
    {
        $this->approvedAndAssigned();

        $this->actingAs($this->principal)
            ->get('/principal/hr-my-tasks')
            ->assertOk()
            ->assertSee('Simulation workspace')
            ->assertSee('Test Teacher');
    }

    public function test_principal_panel_exposes_yayasan_approval_modules_for_simulation(): void
    {
        $r = $this->submitted();

        $this->actingAs($this->principal)
            ->get('/principal/approval-inbox')
            ->assertOk()
            ->assertSee('Approval Inbox')
            ->assertSee('Yayasan Dashboard')
            ->assertSee('Inbox')
            ->assertSee('Hiring Approvals')
            ->assertSee('HR Tasks Overview')
            ->assertSee('Decision History')
            ->assertSee('Needs decision')
            ->assertSee($r->teacher->name);

        $this->actingAs($this->principal)->get('/principal/hiring-approvals')->assertOk();
        $this->actingAs($this->principal)->get('/principal/hr-tasks-overview')->assertOk();
        $this->actingAs($this->principal)->get('/principal/history-page')->assertOk();
    }

    public function test_principal_gets_yayasan_decision_actions_in_simulation_inbox(): void
    {
        $request = $this->submitted();

        $actionKeys = ContractRequestPresenter::for($request)->availableActionsFor($this->principal)->pluck('key')->all();

        $this->assertContains('approve', $actionKeys);
        $this->assertContains('request_correction', $actionKeys);
        $this->assertContains('reject', $actionKeys);
    }

    public function test_my_submissions_hides_yayasan_actions_and_approval_deep_links(): void
    {
        $request = $this->submitted();
        $presenter = ContractRequestPresenter::for($request);
        $this->assertSame(['withdraw'], $presenter->availableActionsFor($this->principal, submitterOnly: true)->pluck('key')->all());
        $this->assertNull($presenter->primaryActionFor($this->principal, submitterOnly: true));

        $this->actingAs($this->principal)
            ->get('/principal/my-submissions?tab=progress')
            ->assertOk()
            ->assertDontSee('?sheet=approve', false);

        $this->actingAs($this->principal)
            ->get('/principal/my-submissions/' . $request->id . '?sheet=approve')
            ->assertOk()
            ->assertSee('Withdraw request')
            ->assertDontSee('Start review')
            ->assertDontSee('wire:click="openSheet(' . $request->id . ', \'approve\')"', false)
            ->assertDontSee('wire:click="openSheet(' . $request->id . ', \'reject\')"', false)
            ->assertDontSee('wire:click="openSheet(' . $request->id . ', \'request_correction\')"', false);
    }

    public function test_submission_detail_refuses_opening_a_yayasan_approval_sheet(): void
    {
        $request = $this->submitted();
        $this->actingAs($this->principal);
        $page = app(\App\Filament\Principal\Pages\MySubmissionDetail::class);
        $page->record = $request;

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $page->openSheet($request->id, 'approve');
    }

    public function test_submitter_actions_remain_separate_through_hr_and_document_review(): void
    {
        $request = $this->approvedAndAssigned();
        $this->assertTrue(ContractRequestPresenter::for($request)->availableActionsFor($this->principal, submitterOnly: true)->isEmpty());

        $this->uploaded($request, $this->hr);
        $this->assertSame(
            ['accept_document', 'request_doc_fix'],
            ContractRequestPresenter::for($request->fresh())->availableActionsFor($this->principal, submitterOnly: true)->pluck('key')->all()
        );
        $this->assertTrue(ContractRequestPresenter::for($request->fresh())->availableActionsFor($this->otherPrincipal, submitterOnly: true)->isEmpty());
    }

    public function test_principal_can_complete_assigned_hr_work_with_principal_audit_identity(): void
    {
        $r = $this->approvedAndAssigned();

        $actions = ContractRequestPresenter::for($r)->availableActionsFor($this->principal)->pluck('key')->all();
        $this->assertContains('mark_in_progress', $actions);
        $this->assertContains('upload_document', $actions);

        $document = $this->uploaded($r, $this->principal);
        $this->assertSame($this->principal->id, $document->uploaded_by);
        $this->assertSame('principal', $r->fresh()->events()->latest('id')->first()->actor_role);
    }

    public function test_reassignment_removes_previous_assignee_access(): void
    {
        $r = $this->approvedAndAssigned();
        $this->wf->reassignHr($r, $this->yayasan, $this->otherHr->id, 'Workload balancing across the HR team.');
        $this->assertSame('hr_assigned', $r->fresh()->stage);

        $this->expectExceptionMessage('is not the assigned HR');
        $this->uploaded($r->fresh(), $this->hr);
    }

    public function test_presenter_offers_no_hr_actions_before_assignment(): void
    {
        $r = $this->submitted();
        $this->assertTrue(ContractRequestPresenter::for($r)->availableActionsFor($this->hr)->isEmpty());

        $this->wf->approve($r, $this->yayasan, 'submitted');
        $this->assertTrue(ContractRequestPresenter::for($r->fresh())->availableActionsFor($this->hr)->isEmpty());
    }

    public function test_principal_can_decide_in_simulation_but_hr_cannot(): void
    {
        $r = $this->submitted();
        $keys = fn (User $u) => ContractRequestPresenter::for($r)->availableActionsFor($u)->pluck('key')->all();
        $this->assertContains('approve', $keys($this->principal));
        $this->assertContains('reject', $keys($this->principal));
        $this->assertSame([], $keys($this->hr));
        $this->assertContains('approve', $keys($this->yayasan));
    }

    // ---------- Decisions ----------

    public function test_rejection_leaves_contract_and_teacher_unchanged(): void
    {
        $r = $this->submitted();
        $this->wf->reject($r, $this->yayasan, 'Evidence for the second semester is missing entirely.', 'submitted');

        $this->assertSame('rejected', $r->fresh()->stage);
        $this->assertSame('active', $this->contract->fresh()->status);
        $this->assertSame('pkwt_1', $this->teacher->fresh()->status);
        $this->assertSame(1, TeacherContract::where('teacher_id', $this->teacher->id)->count());
    }

    public function test_reasons_must_meet_minimum_length(): void
    {
        $r = $this->submitted();
        $this->expectException(\InvalidArgumentException::class);
        $this->wf->reject($r, $this->yayasan, 'No.', 'submitted');
    }

    public function test_stale_expected_stage_is_refused(): void
    {
        $r = $this->submitted();
        $this->wf->startReview($r, $this->yayasan, 'submitted');

        $this->expectException(StaleRequestException::class);
        $this->wf->approve($r->fresh(), $this->yayasan, 'submitted');
    }

    public function test_second_decision_on_same_request_is_refused(): void
    {
        $r = $this->submitted();
        $this->wf->approve($r, $this->yayasan, 'submitted');

        $this->expectException(\RuntimeException::class);
        $this->wf->reject($r->fresh(), $this->yayasan, 'Changing my mind after the fact should not work.', null);
    }

    public function test_withdraw_closes_request_without_touching_source(): void
    {
        $r = $this->submitted();
        $this->wf->withdraw($r, $this->principal, 'Submitted against the wrong academic year.');
        $this->assertSame('cancelled', $r->fresh()->stage);
        $this->assertSame('active', $this->contract->fresh()->status);
    }

    public function test_only_submitter_can_withdraw(): void
    {
        $r = $this->submitted();
        $this->expectExceptionMessage('Only the submitter');
        $this->wf->withdraw($r, $this->otherPrincipal, 'Trying to withdraw someone else\'s request.');
    }

    public function test_resubmission_creates_new_revision_and_keeps_history(): void
    {
        $r = $this->submitted();
        $this->wf->requestCorrection($r, $this->yayasan, 'Please attach the semester-two peer observations.', 'submitted');
        $this->wf->resubmit($r->fresh(), $this->principal, ['justification' => 'Attached the peer observation summary as requested.', 'proposed_type' => 'pkwt_2']);

        $r = $r->fresh();
        $this->assertSame('submitted', $r->stage);
        $this->assertSame(2, $r->revisions()->count());
        $this->assertSame(1, $r->events()->where('event_type', 'correction.requested')->count());
    }

    // ---------- Documents ----------

    public function test_new_upload_after_correction_requires_fresh_acceptance(): void
    {
        $r = $this->approvedAndAssigned();
        $v1 = $this->uploaded($r, $this->hr);
        $this->wf->requestDocumentCorrection($r->fresh(), $this->principal, 'End date on page 2 does not match the approved terms.', 'document_uploaded');
        $this->assertSame('document_correction', $r->fresh()->stage);

        $v2 = $this->uploaded($r->fresh(), $this->hr);
        $this->assertSame(2, $v2->version_number);
        $this->assertNull($v2->accepted_at);
        $this->assertSame('document_uploaded', $r->fresh()->stage);

        // Accepting against the superseded version id is refused as stale.
        $this->expectException(StaleRequestException::class);
        $this->wf->acceptDocument($r->fresh(), $this->principal, 'document_uploaded', $v1->id);
    }

    public function test_only_submitting_principal_can_accept_document(): void
    {
        $r = $this->approvedAndAssigned();
        $this->uploaded($r, $this->hr);
        $this->expectExceptionMessage('Only the submitting principal');
        $this->wf->acceptDocument($r->fresh(), $this->otherPrincipal, 'document_uploaded');
    }

    public function test_only_named_teacher_can_sign(): void
    {
        $r = $this->approvedAndAssigned();
        $this->uploaded($r, $this->hr);
        $this->wf->acceptDocument($r->fresh(), $this->principal, 'document_uploaded');

        $this->expectExceptionMessage('Only the named teacher');
        $this->wf->signDocument($r->fresh(), $this->principal, 'Principal Name', '127.0.0.1');
    }

    public function test_download_route_authorises_parties_only(): void
    {
        Storage::fake('local');
        config(['approvals.document_disk' => 'local']);
        Storage::disk('local')->put('contract-documents/test.pdf', '%PDF-1.4 test');

        $r = $this->approvedAndAssigned();
        $doc = $this->uploaded($r, $this->hr);

        $this->actingAs($this->otherPrincipal)->get(route('approvals.document.download', $doc))->assertForbidden();
        $this->actingAs($this->principal)->get(route('approvals.document.download', $doc))->assertOk()->assertHeader('Cache-Control', 'no-store, private');
        $this->actingAs($this->hr)->get(route('approvals.document.download', $doc))->assertOk();
        $this->actingAs($this->yayasan)->get(route('approvals.document.download', $doc))->assertOk();
    }

    public function test_contract_renewal_page_shows_linked_stage_and_downloadable_document(): void
    {
        Storage::fake('local');
        config(['approvals.document_disk' => 'local']);
        Storage::disk('local')->put('contract-documents/test.pdf', '%PDF-1.4 test');

        $this->contract->update(['renewal_window_starts_at' => now()->subMonth()->toDateString()]);
        $request = $this->approvedAndAssigned();
        $this->uploaded($request, $this->hr);

        $this->actingAs($this->principal)
            ->get('/principal/contract-renewal?ay=all')
            ->assertOk()
            ->assertSee('Uploaded — Ready for Your Review')
            ->assertSee('View submission progress')
            ->assertSee('Download latest contract');
    }

    public function test_contract_workflow_tracks_continuation_from_loi_through_uploaded_contract(): void
    {
        Storage::fake('local');
        config(['approvals.document_disk' => 'local']);
        Storage::disk('local')->put('contract-documents/test.pdf', '%PDF-1.4 test');
        $this->teacher->update(['status' => 'guru_sk']);

        $loi = LetterOfIntent::create([
            'teacher_id' => $this->teacher->id,
            'principal_id' => $this->principal->id,
            'academic_year' => \App\Filament\Principal\Resources\LetterOfIntentResource::currentAcademicYear(),
            'body' => 'Annual continuation letter',
        ]);
        $loi->forceFill(['status' => 'signed', 'sent_at' => now(), 'signed_at' => now()])->save();

        $request = $this->wf->submitFromLoi($loi, $this->principal);
        $this->wf->approve($request, $this->yayasan, 'submitted');
        $this->wf->assignHr($request, $this->yayasan, $this->hr->id, 'Prepare the continuation contract.', now()->addWeek()->toDateString(), 'approved');
        $this->uploaded($request->fresh(), $this->hr);

        $this->actingAs($this->principal)
            ->get('/principal/my-submissions?tab=action&type=annual_continuation')
            ->assertOk()
            ->assertSee('Uploaded — Ready for Your Review')
            ->assertSee('Contract Workflow');

        $this->actingAs($this->principal)
            ->get('/principal/my-submissions/' . $request->id)
            ->assertOk()
            ->assertSee('Continuation progress')
            ->assertSee('Open LOI preview')
            ->assertSee('Preview contract')
            ->assertSee('Accept document');

        $this->wf->acceptDocument($request->fresh(), $this->principal, 'document_uploaded');
        $this->actingAs($this->principal)
            ->get('/principal/my-submissions/' . $request->id)
            ->assertOk()
            ->assertSee('Record Agreement handover')
            ->assertSee('not a legal teacher signature');

        $hrNotificationCount = $this->hr->notifications()->count();
        $detailPage = app(\App\Filament\Principal\Pages\MySubmissionDetail::class);
        $detailPage->record = $request->fresh();
        $detailPage->simulateAgreementSigning();
        $this->assertSame('hr_registration', $request->fresh()->stage);
        $this->assertSame('DEMO SIMULATION — Test Teacher', $loi->fresh()->agreement_signature_text);
        $this->assertGreaterThan($hrNotificationCount, $this->hr->notifications()->count());
        $simulationEvent = $request->fresh()->events()->where('event_type', 'signing.simulated')->firstOrFail();
        $this->assertSame($this->principal->id, $simulationEvent->actor_id);
        $this->assertSame('principal', $simulationEvent->actor_role);
        $this->actingAs($this->principal)
            ->get('/principal/my-submissions/' . $request->id)
            ->assertOk()
            ->assertSee('Record Buku Induk')
            ->assertSee('Buku Induk');

        $detailPage = app(\App\Filament\Principal\Pages\MySubmissionDetail::class);
        $detailPage->record = $request->fresh();
        $detailPage->recordBukuInduk();
        $this->assertSame('completed', $request->fresh()->stage);
        $this->assertNotNull($loi->fresh()->buku_induk_recorded_at);
        $this->assertNotNull($loi->fresh()->continuation_completed_at);
        $bukuIndukEvent = $request->fresh()->events()->where('event_type', 'buku_induk.recorded')->firstOrFail();
        $this->assertSame($this->principal->id, $bukuIndukEvent->actor_id);

        $this->actingAs($this->principal)
            ->get('/principal/my-submissions/' . $request->id)
            ->assertOk()
            ->assertSee('Completed')
            ->assertSee('LOI and contract preview');
    }

    public function test_contract_continuation_legacy_route_redirects_to_contract_workflow(): void
    {
        $response = $this->actingAs($this->principal)->get('/principal/contract-continuation');
        $response->assertRedirect(
            \App\Filament\Principal\Pages\MySubmissions::getUrl(
                ['type' => 'annual_continuation'],
                panel: 'principal'
            )
        );
    }

    public function test_signed_continuation_is_listed_in_ready_to_submit_tab(): void
    {
        $this->teacher->update(['status' => 'guru_sk']);
        $loi = LetterOfIntent::create([
            'teacher_id' => $this->teacher->id,
            'principal_id' => $this->principal->id,
            'academic_year' => \App\Filament\Principal\Resources\LetterOfIntentResource::currentAcademicYear(),
            'body' => 'Annual continuation letter',
        ]);
        $loi->forceFill(['status' => 'signed', 'sent_at' => now()->subDay(), 'signed_at' => now()])->save();

        $this->actingAs($this->principal)
            ->get('/principal/my-submissions?tab=ready&type=annual_continuation')
            ->assertOk()
            ->assertSee('Ready to submit')
            ->assertSee('Test Teacher')
            ->assertSee('Preview signed Letter of Intent')
            ->assertSee('Submit to Yayasan')
            ->assertSee(route('letter-of-intent.print', ['record' => $loi, 'workflow' => 1, 'embedded' => 1]));

        $this->actingAs($this->principal)
            ->get(route('letter-of-intent.print', ['record' => $loi, 'workflow' => 1, 'embedded' => 1]))
            ->assertOk()
            ->assertSee('Annual continuation letter')
            ->assertSee('Back to Contract Workflow')
            ->assertSee('target="_top"', false)
            ->assertSee(\App\Filament\Principal\Pages\MySubmissions::getUrl(['tab' => 'ready', 'type' => 'annual_continuation'], panel: 'principal'))
            ->assertDontSee('fi-sidebar');

        $page = app(\App\Filament\Principal\Pages\MySubmissions::class);
        $page->submitContinuation($loi->id);
        $this->assertDatabaseHas('contract_approval_requests', [
            'source_loi_id' => $loi->id,
            'submitted_by' => $this->principal->id,
            'stage' => 'submitted',
        ]);

        $request = $loi->latestApprovalRequest()->firstOrFail();
        $this->actingAs($this->principal)
            ->get(route('letter-of-intent.print', ['record' => $loi, 'workflow' => 1]))
            ->assertOk()
            ->assertSee('Back to Contract Workflow')
            ->assertSee('target="_self"', false)
            ->assertSee(\App\Filament\Principal\Pages\MySubmissionDetail::getUrl(['record' => $request->id], panel: 'principal'));

        $this->actingAs($this->principal)
            ->get(route('letter-of-intent.print', ['record' => $loi]))
            ->assertOk()
            ->assertSee('Back to LOI')
            ->assertDontSee('Back to Contract Workflow');
    }

    public function test_loi_contract_download_is_scoped_to_its_principal(): void
    {
        Storage::fake('local');
        config(['approvals.document_disk' => 'local']);
        Storage::disk('local')->put('continuation-contracts/test.pdf', '%PDF-1.4 test');
        $loi = LetterOfIntent::create([
            'teacher_id' => $this->teacher->id,
            'principal_id' => $this->principal->id,
            'academic_year' => '2026/2027',
            'body' => 'Annual continuation letter',
        ]);
        $loi->update(['yayasan_contract_path' => 'continuation-contracts/test.pdf']);

        $this->actingAs($this->otherPrincipal)
            ->get(route('contracts.loi.download', ['letter' => $loi]))
            ->assertForbidden();
        $this->actingAs($this->principal)
            ->get(route('contracts.loi.download', ['letter' => $loi]))
            ->assertOk();
    }

    // ---------- Presenter ----------

    public function test_stage_track_marks_failure_at_the_right_step(): void
    {
        $r = $this->submitted();
        $this->wf->startReview($r, $this->yayasan, 'submitted');
        $this->wf->reject($r->fresh(), $this->yayasan, 'Not enough evidence to support the renewal request.', 'under_review');

        $track = collect(ContractRequestPresenter::for($r->fresh()->load('events'))->stageTrack());
        $this->assertSame('done', $track->firstWhere('key', 'submitted')['state']);
        $this->assertSame('failed', $track->firstWhere('key', 'review')['state']);
        $this->assertSame('pending', $track->firstWhere('key', 'approved')['state']);
    }

    public function test_urgency_uses_config_thresholds(): void
    {
        config(['approvals.review_attention_days' => 2, 'approvals.review_overdue_days' => 4]);
        $r = $this->submitted();
        $r->events()->update(['occurred_at' => now()->subDays(3)]);
        $r->update(['submitted_at' => now()->subDays(3)]);
        $this->assertSame('attention', ContractRequestPresenter::for($r->fresh()->load('events'))->urgency());

        $r->events()->update(['occurred_at' => now()->subDays(5)]);
        $this->assertSame('overdue', ContractRequestPresenter::for($r->fresh()->load('events'))->urgency());
    }

    // ---------- Panel access ----------

    public function test_yayasan_user_cannot_open_principal_pages(): void
    {
        $this->actingAs($this->yayasan)->get('/principal/my-submissions')->assertForbidden();
        $this->actingAs($this->principal)->get('/yayasan/approval-inbox')->assertForbidden();
        $this->actingAs($this->principal)->get('/principal/hr-my-tasks')->assertOk();
    }

    public function test_hr_role_does_not_have_a_separate_principal_panel_login(): void
    {
        $this->actingAs($this->hr)->get('/principal/hr-my-tasks')->assertForbidden();
    }

    public function test_removed_principal_panel_yayasan_pages_are_gone(): void
    {
        $this->actingAs($this->principal)->get('/principal/yayasan-contract-approvals')->assertNotFound();
        $this->actingAs($this->principal)->get('/principal/yayasan-approval-hub')->assertNotFound();
    }
}
