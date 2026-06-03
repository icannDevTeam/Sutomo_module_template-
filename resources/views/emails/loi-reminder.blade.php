@php
    use App\Filament\Principal\Pages\SignLetterOfIntent;
    use App\Filament\Principal\Resources\LetterOfIntentResource;

    $teacherName = optional($letter->teacher)->name ?? 'Teacher';
    $deadline = $letter->deadline_at ? $letter->deadline_at->format('d M Y H:i') : '—';
    $status = ucfirst((string) $letter->status);

    $message = match ($kind) {
        'pending'           => 'Please sign your Letter of Intent before the deadline.',
        'overdue'           => 'Your Letter of Intent is past its deadline. Please respond as soon as possible.',
        'follow_up_pending' => 'A declined Letter of Intent requires your follow-up.',
        default             => 'A Letter of Intent requires your attention.',
    };

    try {
        $principalUrl = LetterOfIntentResource::getUrl('view', ['record' => $letter->id], panel: 'principal');
    } catch (\Throwable $e) {
        $principalUrl = null;
    }

    try {
        $teacherUrl = SignLetterOfIntent::getUrl(['record' => $letter->id], panel: 'principal');
    } catch (\Throwable $e) {
        $teacherUrl = null;
    }

    $isPrincipalKind = $kind === 'follow_up_pending';
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Letter of Intent reminder</title>
</head>
<body style="font-family: Arial, sans-serif; color:#222; line-height:1.5;">
    <p>Hello {{ $teacherName }},</p>

    <p>{{ $message }}</p>

    <table cellpadding="6" cellspacing="0" style="border-collapse:collapse; margin:12px 0;">
        <tr>
            <td style="border:1px solid #ddd;"><strong>Academic year</strong></td>
            <td style="border:1px solid #ddd;">{{ $letter->academic_year ?? '—' }}</td>
        </tr>
        <tr>
            <td style="border:1px solid #ddd;"><strong>Deadline</strong></td>
            <td style="border:1px solid #ddd;">{{ $deadline }}</td>
        </tr>
        <tr>
            <td style="border:1px solid #ddd;"><strong>Status</strong></td>
            <td style="border:1px solid #ddd;">{{ $status }}</td>
        </tr>
    </table>

    @if ($isPrincipalKind && $principalUrl)
        <p><a href="{{ $principalUrl }}">Open Letter of Intent</a></p>
    @elseif (! $isPrincipalKind && $teacherUrl)
        <p><a href="{{ $teacherUrl }}">Open and sign your Letter of Intent</a></p>
    @elseif ($principalUrl)
        <p><a href="{{ $principalUrl }}">Open Letter of Intent</a></p>
    @endif

    <p style="color:#777; font-size:12px;">This is an automated reminder from the Sutomo system.</p>
</body>
</html>
