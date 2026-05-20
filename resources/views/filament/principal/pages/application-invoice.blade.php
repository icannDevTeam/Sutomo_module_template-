<x-filament-panels::page>
    @php
        $r = $this->record;
        $methods = \App\Models\Application::PAYMENT_METHODS;
    @endphp

    <style>
        @media print {
            .no-print, .fi-topbar, .fi-sidebar, .fi-header, .fi-page-header { display:none !important; }
            body, .fi-body, .fi-main { background:#fff !important; }
            .invoice-wrap { box-shadow:none !important; border:0 !important; }
        }
    </style>

    <div class="no-print" style="display:flex;gap:.5rem;justify-content:flex-end;margin-bottom:1rem;">
        <button onclick="window.print()" style="display:inline-flex;align-items:center;gap:.4rem;background:#4338ca;color:#fff;border:0;padding:.55rem 1rem;border-radius:.6rem;font-weight:600;cursor:pointer;">
            🖨 Print Invoice
        </button>
        <a href="{{ \App\Filament\Principal\Resources\ApplicationResource::getUrl('view', ['record'=>$r->id]) }}"
           style="display:inline-flex;align-items:center;background:#f3f4f6;color:#374151;padding:.55rem 1rem;border-radius:.6rem;font-weight:600;text-decoration:none;">
            Back to Application
        </a>
    </div>

    <div class="invoice-wrap" style="max-width:820px;margin:0 auto;background:#fff;border:1px solid #e5e7eb;border-radius:1rem;padding:2.25rem 2.5rem;box-shadow:0 12px 30px -20px rgba(0,0,0,.15);color:#111827;">
        {{-- Header --}}
        <div style="display:flex;justify-content:space-between;align-items:flex-start;border-bottom:2px solid #4338ca;padding-bottom:1.25rem;margin-bottom:1.5rem;">
            <div>
                <div style="display:flex;align-items:center;gap:.75rem;">
                    <div style="width:48px;height:48px;border-radius:50%;background:linear-gradient(135deg,#4338ca,#f59e0b);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:18px;">S</div>
                    <div>
                        <div style="font-weight:800;font-size:1.05rem;letter-spacing:.04em;">YAYASAN PERGURUAN SUTOMO</div>
                        <div style="font-size:.78rem;color:#6b7280;">Jl. Letkol Martinus Lubis No.7, Medan · admissions@sutomo-mdn.sch.id</div>
                    </div>
                </div>
            </div>
            <div style="text-align:right;">
                <div style="font-size:1.65rem;font-weight:800;letter-spacing:.05em;color:#4338ca;">INVOICE</div>
                <div style="font-size:.85rem;color:#374151;margin-top:.15rem;"><b>No:</b> {{ $r->invoice_no }}</div>
                <div style="font-size:.8rem;color:#6b7280;"><b>Date:</b> {{ now()->format('d M Y') }}</div>
                <div style="margin-top:.4rem;">
                    <span style="display:inline-block;padding:.18rem .55rem;border-radius:9999px;font-size:.7rem;font-weight:700;letter-spacing:.04em;
                        {{ $r->payment_status === 'paid' ? 'background:#d1fae5;color:#065f46;' : 'background:#fef3c7;color:#92400e;' }}">
                        {{ strtoupper($r->payment_status) }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Bill To --}}
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:1.25rem;margin-bottom:1.75rem;">
            <div>
                <div style="font-size:.7rem;letter-spacing:.08em;color:#6b7280;text-transform:uppercase;font-weight:600;margin-bottom:.35rem;">Bill To</div>
                <div style="font-weight:700;font-size:1rem;">{{ $r->parent_name ?? '—' }}</div>
                <div style="font-size:.85rem;color:#374151;">{{ $r->parent_phone ?? '' }}</div>
                <div style="font-size:.85rem;color:#374151;">{{ $r->parent_email ?? '' }}</div>
            </div>
            <div>
                <div style="font-size:.7rem;letter-spacing:.08em;color:#6b7280;text-transform:uppercase;font-weight:600;margin-bottom:.35rem;">Applicant</div>
                <div style="font-weight:700;font-size:1rem;">{{ $r->name }}</div>
                <div style="font-size:.85rem;color:#374151;">Code: {{ $r->code }}</div>
                <div style="font-size:.85rem;color:#374151;">{{ strtoupper($r->campus) }} · Grade {{ $r->grade }}{{ $r->stream ? ' · '.strtoupper($r->stream) : '' }}</div>
            </div>
            <div>
                <div style="font-size:.7rem;letter-spacing:.08em;color:#6b7280;text-transform:uppercase;font-weight:600;margin-bottom:.35rem;">Payment</div>
                <div style="font-size:.85rem;color:#374151;"><b>Method:</b> {{ $methods[$r->payment_method] ?? '—' }}</div>
                <div style="font-size:.85rem;color:#374151;"><b>Paid:</b> {{ $r->payment_paid_at ? \Illuminate\Support\Carbon::parse($r->payment_paid_at)->format('d M Y H:i') : '—' }}</div>
            </div>
        </div>

        {{-- Line items --}}
        <table style="width:100%;border-collapse:collapse;font-size:.9rem;">
            <thead>
                <tr style="background:#f3f4f6;text-align:left;">
                    <th style="padding:.7rem .85rem;border-bottom:1px solid #e5e7eb;">Description</th>
                    <th style="padding:.7rem .85rem;border-bottom:1px solid #e5e7eb;text-align:center;">Qty</th>
                    <th style="padding:.7rem .85rem;border-bottom:1px solid #e5e7eb;text-align:right;">Unit Price</th>
                    <th style="padding:.7rem .85rem;border-bottom:1px solid #e5e7eb;text-align:right;">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="padding:.85rem;border-bottom:1px solid #f3f4f6;">
                        <div style="font-weight:600;">Pendaftaran Calon Siswa Baru</div>
                        <div style="font-size:.78rem;color:#6b7280;">{{ \App\Models\Application::APPLICANT_TYPES[$r->applicant_type] ?? $r->applicant_type }} · TA 2026/2027</div>
                    </td>
                    <td style="padding:.85rem;border-bottom:1px solid #f3f4f6;text-align:center;">1</td>
                    <td style="padding:.85rem;border-bottom:1px solid #f3f4f6;text-align:right;">Rp {{ number_format((float)$r->payment_amount, 0, ',', '.') }}</td>
                    <td style="padding:.85rem;border-bottom:1px solid #f3f4f6;text-align:right;">Rp {{ number_format((float)$r->payment_amount, 0, ',', '.') }}</td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" style="padding:.85rem;text-align:right;font-weight:700;">TOTAL</td>
                    <td style="padding:.85rem;text-align:right;font-weight:800;font-size:1.05rem;color:#4338ca;">Rp {{ number_format((float)$r->payment_amount, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>

        @if ($r->receipt_file)
            <div style="margin-top:1.5rem;padding:.85rem 1rem;background:#ecfdf5;border:1px solid #a7f3d0;border-radius:.6rem;display:flex;align-items:center;justify-content:space-between;font-size:.85rem;">
                <span style="color:#065f46;">✓ Bukti pembayaran telah diunggah oleh parent.</span>
                <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($r->receipt_file) }}" target="_blank"
                   style="color:#047857;font-weight:700;text-decoration:underline;">Open uploaded receipt</a>
            </div>
        @else
            <div style="margin-top:1.5rem;padding:.85rem 1rem;background:#fef3c7;border:1px solid #fde68a;border-radius:.6rem;color:#92400e;font-size:.85rem;">
                Parent has not uploaded payment proof yet.
            </div>
        @endif

        {{-- Footer --}}
        <div style="margin-top:2rem;padding-top:1rem;border-top:1px dashed #d1d5db;font-size:.75rem;color:#6b7280;text-align:center;">
            This is a system-generated invoice. For inquiries please contact <b>admissions@sutomo-mdn.sch.id</b> or WhatsApp <b>0815 6868 77895</b>.
        </div>
    </div>
</x-filament-panels::page>
