<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <?php use Illuminate\Support\Str; ?>
    <meta charset="UTF-8">
    <title>Doctor Report — <?php echo e($report['doctor']['name'] ?? 'Doctor'); ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body {
            direction: rtl;
            unicode-bidi: bidi-override; /* Helps Arabic layout/ordering in dompdf */
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            color: #1a202c;
            background: #ffffff;
            line-height: 1.5;
        }
        table, th, td {
            direction: rtl;
            text-align: right;
        }

        /* ── Header ── */
        .header {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            color: #ffffff;
            padding: 24px 32px;
            border-bottom: 4px solid #1d4ed8;
        }
        .header-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; }
        .logo-area { display: flex; align-items: center; gap: 10px; }
        .logo-box {
            width: 48px; height: 48px;
            background: rgba(255,255,255,0.2); border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: 22px; font-weight: 900; color: #ffffff;
            border: 2px solid rgba(255,255,255,0.4);
        }
        .brand-name { font-size: 20px; font-weight: 700; letter-spacing: 0.5px; }
        .brand-sub  { font-size: 10px; opacity: 0.8; }
        .report-meta { text-align: right; font-size: 11px; opacity: 0.9; }
        .report-meta .report-title { font-size: 16px; font-weight: 700; margin-bottom: 4px; }
        .report-period {
            background: rgba(255,255,255,0.15); border-radius: 20px;
            padding: 4px 14px; display: inline-block; font-size: 11px;
            border: 1px solid rgba(255,255,255,0.3);
        }
        .generated-at { font-size: 10px; opacity: 0.7; margin-top: 8px; }

        /* ── Doctor Info Card ── */
        .doctor-card {
            background: #f8fafc; border: 1px solid #e2e8f0;
            border-left: 5px solid #3b82f6; border-radius: 8px;
            padding: 20px 24px; margin: 20px 32px;
            display: table; width: calc(100% - 64px);
        }
        .doctor-card-inner { display: table-row; }
        .doctor-avatar-cell { display: table-cell; width: 70px; vertical-align: top; }
        .doctor-avatar {
            width: 56px; height: 56px; border-radius: 50%;
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: #fff; text-align: center; line-height: 56px;
            font-size: 22px; font-weight: 700;
        }
        .doctor-info-cell { display: table-cell; vertical-align: top; padding-left: 16px; }
        .doctor-name      { font-size: 16px; font-weight: 700; color: #1e3a8a; }
        .doctor-specialty { font-size: 12px; color: #3b82f6; font-weight: 600; margin-bottom: 8px; }
        .doctor-meta-grid { display: table; width: 100%; }
        .doctor-meta-row  { display: table-row; }
        .doctor-meta-label {
            display: table-cell; width: 32%;
            font-weight: 600; color: #64748b; font-size: 11px; padding: 2px 0;
        }
        .doctor-meta-value { display: table-cell; color: #1a202c; font-size: 11px; padding: 2px 0; }
        .status-badge {
            display: inline-block; padding: 2px 10px;
            border-radius: 12px; font-size: 10px; font-weight: 600;
        }
        .status-active    { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
        .status-suspended { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
        .status-inactive  { background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; }

        /* ── KPI Cards ── */
        .kpi-section { margin: 0 32px 20px; }
        .kpi-grid { display: table; width: 100%; border-spacing: 8px; }
        .kpi-row  { display: table-row; }
        .kpi-cell { display: table-cell; width: 25%; padding: 0 4px; }
        .kpi-card {
            background: #ffffff; border: 1px solid #e2e8f0;
            border-radius: 8px; padding: 14px 16px;
            text-align: center; border-top: 3px solid #3b82f6;
        }
        .kpi-value { font-size: 22px; font-weight: 800; color: #1e40af; }
        .kpi-label { font-size: 10px; color: #64748b; margin-top: 4px; font-weight: 500; }

        /* ── Section ── */
        .section { margin: 0 32px 24px; }
        .section-header {
            display: flex; align-items: center; gap: 8px;
            padding: 10px 16px;
            background: #eff6ff; border: 1px solid #bfdbfe;
            border-radius: 6px 6px 0 0; border-bottom: 2px solid #3b82f6;
        }
        .section-icon  { font-size: 14px; }
        .section-title { font-size: 13px; font-weight: 700; color: #1e40af; }
        .section-count {
            margin-left: auto; background: #3b82f6; color: #fff;
            border-radius: 10px; padding: 1px 10px;
            font-size: 10px; font-weight: 600;
        }
        .section-body {
            background: #ffffff; border: 1px solid #e2e8f0;
            border-top: none; border-radius: 0 0 6px 6px; padding: 16px;
        }

        /* ── Summary Stats ── */
        .summary-stats {
            display: table; width: 100%; margin-bottom: 14px;
            background: #f8fafc; border-radius: 6px; border: 1px solid #e2e8f0;
        }
        .summary-stats-row { display: table-row; }
        .summary-stat-cell {
            display: table-cell; padding: 10px 14px;
            border-right: 1px solid #e2e8f0; text-align: center;
        }
        .summary-stat-cell:last-child { border-right: none; }
        .stat-val   { font-size: 18px; font-weight: 700; color: #1e40af; }
        .stat-label { font-size: 10px; color: #64748b; font-weight: 500; }

        /* ── Table ── */
        .data-table { width: 100%; border-collapse: collapse; font-size: 10px; }
        .data-table thead tr { background: #1e40af; color: #ffffff; }
        .data-table thead th {
            padding: 7px 8px; text-align: left;
            font-weight: 600; font-size: 9px;
            letter-spacing: 0.3px; text-transform: uppercase;
        }
        .data-table tbody tr { border-bottom: 1px solid #f1f5f9; }
        .data-table tbody tr:nth-child(even) { background: #f8fafc; }
        .data-table tbody td { padding: 6px 8px; color: #374151; }
        .data-table tbody tr:last-child { border-bottom: none; }

        .no-data { text-align: center; padding: 20px; color: #94a3b8; font-size: 11px; }

        /* ── Badges ── */
        .badge {
            display: inline-block; padding: 2px 8px;
            border-radius: 10px; font-size: 9px; font-weight: 600;
        }
        .badge-green  { background: #dcfce7; color: #166534; }
        .badge-red    { background: #fee2e2; color: #991b1b; }
        .badge-yellow { background: #fef9c3; color: #854d0e; }
        .badge-blue   { background: #dbeafe; color: #1e40af; }
        .badge-gray   { background: #f1f5f9; color: #475569; }
        .badge-purple { background: #ede9fe; color: #5b21b6; }

        /* ── Wallet ── */
        .wallet-grid { display: table; width: 100%; }
        .wallet-row  { display: table-row; }
        .wallet-cell { display: table-cell; width: 25%; padding: 0 6px; vertical-align: top; }
        .wallet-card {
            background: #f8fafc; border: 1px solid #e2e8f0;
            border-radius: 8px; padding: 14px; text-align: center;
        }
        .wallet-val     { font-size: 18px; font-weight: 800; color: #065f46; }
        .wallet-label   { font-size: 10px; color: #64748b; margin-top: 4px; }
        .wallet-pending .wallet-val { color: #b45309; }

        /* ── Schedule ── */
        .schedule-grid { display: table; width: 100%; margin-bottom: 14px; }
        .schedule-row  { display: table-row; }
        .schedule-cell { display: table-cell; width: 33.33%; padding: 0 6px; vertical-align: top; }
        .schedule-card {
            background: #f8fafc; border: 1px solid #e2e8f0;
            border-radius: 8px; padding: 12px 14px;
        }
        .schedule-val   { font-size: 16px; font-weight: 700; color: #1e40af; }
        .schedule-label { font-size: 10px; color: #64748b; margin-top: 2px; }
        .day-tag {
            display: inline-block; margin: 2px;
            padding: 2px 8px; border-radius: 10px;
            background: #dbeafe; color: #1e40af;
            font-size: 10px; font-weight: 600;
        }

        /* ── Stars ── */
        .stars { color: #f59e0b; }

        /* ── Footer ── */
        .footer {
            margin-top: 24px; border-top: 2px solid #e2e8f0;
            padding: 14px 32px; background: #f8fafc;
            display: flex; justify-content: space-between; align-items: center;
        }
        .footer-left  { font-size: 10px; color: #94a3b8; }
        .footer-right { font-size: 10px; color: #94a3b8; text-align: right; }

        .page-break { page-break-before: always; }
        .mb-3 { margin-bottom: 12px; }
        .mt-2 { margin-top: 8px; }
        .text-muted { color: #94a3b8; font-size: 10px; }
        .items-sub-row { background: #f0f9ff !important; }
        .items-sub-row td { font-size: 9px; color: #475569; padding: 4px 8px 4px 20px !important; }
    </style>
</head>
<body>


<div class="header">
    <div class="header-top">
        <div class="logo-area">
            <div class="logo-box">E</div>
            <div>
                <div class="brand-name">Eldokan</div>
                <div class="brand-sub">Medical Platform</div>
            </div>
        </div>
        <div class="report-meta">
            <div class="report-title">Doctor Report</div>
            <div class="report-period">
                <?php echo e(\Carbon\Carbon::parse($report['period']['from'])->format('M d, Y')); ?>

                &mdash;
                <?php echo e(\Carbon\Carbon::parse($report['period']['to'])->format('M d, Y')); ?>

            </div>
        </div>
    </div>
    <div class="generated-at">Generated: <?php echo e($report['generated_at']); ?></div>
</div>


<div class="doctor-card">
    <div class="doctor-card-inner">
        <div class="doctor-avatar-cell">
            <div class="doctor-avatar"><?php echo e(mb_strtoupper(mb_substr($report['doctor']['name'] ?? 'D', 0, 1))); ?></div>
        </div>
        <div class="doctor-info-cell">
            <div class="doctor-name"><?php echo e($report['doctor']['name'] ?? '-'); ?></div>
            <div class="doctor-specialty"><?php echo e($report['doctor']['specialty'] ?? '-'); ?></div>
            <div class="doctor-meta-grid">
                <?php if(!empty($report['doctor']['name_ar'])): ?>
                <div class="doctor-meta-row">
                    <div class="doctor-meta-label">Arabic Name</div>
                    <div class="doctor-meta-value"><?php echo e($report['doctor']['name_ar']); ?></div>
                </div>
                <?php endif; ?>
                <?php if(!empty($report['doctor']['specialty_ar'])): ?>
                <div class="doctor-meta-row">
                    <div class="doctor-meta-label">Specialty (AR)</div>
                    <div class="doctor-meta-value"><?php echo e($report['doctor']['specialty_ar']); ?></div>
                </div>
                <?php endif; ?>
                <div class="doctor-meta-row">
                    <div class="doctor-meta-label">Location</div>
                    <div class="doctor-meta-value"><?php echo e($report['doctor']['location'] ?? '-'); ?></div>
                </div>
                <?php if(!empty($report['doctor']['user']['email'])): ?>
                <div class="doctor-meta-row">
                    <div class="doctor-meta-label">Email</div>
                    <div class="doctor-meta-value"><?php echo e($report['doctor']['user']['email']); ?></div>
                </div>
                <?php endif; ?>
                <?php if(!empty($report['doctor']['user']['phone'])): ?>
                <div class="doctor-meta-row">
                    <div class="doctor-meta-label">Phone</div>
                    <div class="doctor-meta-value"><?php echo e($report['doctor']['user']['phone']); ?></div>
                </div>
                <?php endif; ?>
                <div class="doctor-meta-row">
                    <div class="doctor-meta-label">Consultation</div>
                    <div class="doctor-meta-value">
                        EGP <?php echo e(number_format($report['doctor']['consultation_price'] ?? 0, 2)); ?>

                        <?php if(($report['doctor']['discount_percentage'] ?? 0) > 0): ?>
                            <span style="color:#dc2626; font-size:10px;">&nbsp;(-<?php echo e($report['doctor']['discount_percentage']); ?>%)</span>
                        <?php endif; ?>
                        <?php if(!empty($report['doctor']['consultation_duration'])): ?>
                            &bull; <?php echo e($report['doctor']['consultation_duration']); ?> min
                        <?php endif; ?>
                    </div>
                </div>
                <div class="doctor-meta-row">
                    <div class="doctor-meta-label">Status</div>
                    <div class="doctor-meta-value">
                        <?php $st = $report['doctor']['status'] ?? 'active'; ?>
                        <span class="status-badge status-<?php echo e($st); ?>"><?php echo e(ucfirst($st)); ?></span>
                        <?php if($st === 'suspended' && !empty($report['doctor']['suspension_reason'])): ?>
                            <span class="text-muted">&nbsp;— <?php echo e(Str::limit($report['doctor']['suspension_reason'], 50)); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="doctor-meta-row">
                    <div class="doctor-meta-label">Rating</div>
                    <div class="doctor-meta-value"><span class="stars">&#9733;</span> <?php echo e($report['doctor']['rating'] ?? '0.00'); ?> / 5.00</div>
                </div>
                <?php if(!empty($report['doctor']['created_at'])): ?>
                <div class="doctor-meta-row">
                    <div class="doctor-meta-label">Member Since</div>
                    <div class="doctor-meta-value"><?php echo e($report['doctor']['created_at']); ?></div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>


<div class="kpi-section">
    <div class="kpi-grid">
        <div class="kpi-row">
            <div class="kpi-cell">
                <div class="kpi-card">
                    <div class="kpi-value"><?php echo e($report['kpis']['bookings_this_month'] ?? 0); ?></div>
                    <div class="kpi-label">Bookings This Period</div>
                </div>
            </div>
            <div class="kpi-cell">
                <div class="kpi-card">
                    <div class="kpi-value"><?php echo e($report['kpis']['unique_patients'] ?? 0); ?></div>
                    <div class="kpi-label">Unique Patients</div>
                </div>
            </div>
            <div class="kpi-cell">
                <div class="kpi-card">
                    <div class="kpi-value"><?php echo e($report['kpis']['total_prescriptions'] ?? 0); ?></div>
                    <div class="kpi-label">Prescriptions Issued</div>
                </div>
            </div>
            <div class="kpi-cell">
                <div class="kpi-card">
                    <div class="kpi-value"><?php echo e($report['kpis']['prescription_purchase_rate'] ?? 0); ?>%</div>
                    <div class="kpi-label">Prescription Purchase Rate</div>
                </div>
            </div>
        </div>
        <div class="kpi-row" style="padding-top:8px;">
            <div class="kpi-cell">
                <div class="kpi-card">
                    <div class="kpi-value">EGP <?php echo e(number_format($report['kpis']['completed_revenue'] ?? 0, 0)); ?></div>
                    <div class="kpi-label">Revenue (Completed)</div>
                </div>
            </div>
            <div class="kpi-cell">
                <div class="kpi-card">
                    <div class="kpi-value"><?php echo e($report['kpis']['avg_rating'] ?? '0.00'); ?></div>
                    <div class="kpi-label">Average Rating</div>
                </div>
            </div>
            <div class="kpi-cell">
                <div class="kpi-card">
                    <div class="kpi-value"><?php echo e($report['kpis']['total_ratings'] ?? 0); ?></div>
                    <div class="kpi-label">Total Reviews</div>
                </div>
            </div>
            <div class="kpi-cell">
                <div class="kpi-card">
                    <div class="kpi-value"><?php echo e($report['kpis']['bookings_today'] ?? 0); ?></div>
                    <div class="kpi-label">Bookings Today</div>
                </div>
            </div>
        </div>
    </div>
</div>


<?php if(isset($report['sections']['schedule'])): ?>
<?php $sched = $report['sections']['schedule']; ?>
<div class="section">
    <div class="section-header">
        <span class="section-icon">&#128197;</span>
        <span class="section-title">Schedule &amp; Pricing</span>
    </div>
    <div class="section-body">
        <div class="schedule-grid mb-3">
            <div class="schedule-row">
                <div class="schedule-cell">
                    <div class="schedule-card">
                        <div class="schedule-val"><?php echo e($sched['available_hours_start'] ?? '-'); ?> – <?php echo e($sched['available_hours_end'] ?? '-'); ?></div>
                        <div class="schedule-label">Working Hours</div>
                    </div>
                </div>
                <div class="schedule-cell">
                    <div class="schedule-card">
                        <div class="schedule-val"><?php echo e($sched['consultation_duration'] ?? '-'); ?> min</div>
                        <div class="schedule-label">Consultation Duration</div>
                    </div>
                </div>
                <div class="schedule-cell">
                    <div class="schedule-card">
                        <div class="schedule-val">EGP <?php echo e(number_format($sched['effective_price'] ?? 0, 2)); ?></div>
                        <div class="schedule-label">
                            Effective Price
                            <?php if(($sched['discount_percentage'] ?? 0) > 0): ?>
                                <span style="color:#dc2626">&nbsp;(<?php echo e($sched['discount_percentage']); ?>% off)</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div>
            <strong style="font-size:11px; color:#64748b;">Working Days:</strong>&nbsp;
            <?php $__currentLoopData = (array)($sched['available_days'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <span class="day-tag"><?php echo e(ucfirst($day)); ?></span>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            <?php if(empty($sched['available_days'])): ?>
                <span class="text-muted">Not configured</span>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endif; ?>


<?php if(isset($report['sections']['prescriptions'])): ?>
<?php $presc = $report['sections']['prescriptions']; ?>
<div class="section">
    <div class="section-header">
        <span class="section-icon">&#128221;</span>
        <span class="section-title">Prescriptions</span>
        <span class="section-count"><?php echo e($presc['total']); ?> total</span>
    </div>
    <div class="section-body">
        <div class="summary-stats mb-3">
            <div class="summary-stats-row">
                <div class="summary-stat-cell">
                    <div class="stat-val"><?php echo e($presc['total']); ?></div>
                    <div class="stat-label">Total</div>
                </div>
                <div class="summary-stat-cell">
                    <div class="stat-val"><?php echo e($presc['shared']); ?></div>
                    <div class="stat-label">Shared</div>
                </div>
                <div class="summary-stat-cell">
                    <div class="stat-val"><?php echo e($presc['not_shared']); ?></div>
                    <div class="stat-label">Not Shared</div>
                </div>
                <div class="summary-stat-cell">
                    <div class="stat-val"><?php echo e($presc['templates_count']); ?></div>
                    <div class="stat-label">Templates</div>
                </div>
            </div>
        </div>
        <?php if(!empty($presc['recent'])): ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Prescription No.</th>
                    <th>Prescription Name</th>
                    <th>Patient</th>
                    <th>Phone</th>
                    <th>Items</th>
                    <th>Shared</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $presc['recent']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($i + 1); ?></td>
                    <td><?php echo e($p['prescription_number'] ?? '-'); ?></td>
                    <td><?php echo e($p['prescription_name'] ?? '-'); ?></td>
                    <td><?php echo e($p['patient_name'] ?? ($p['patient']['username'] ?? '-')); ?></td>
                    <td><?php echo e($p['patient_phone'] ?? ($p['patient']['phone'] ?? '-')); ?></td>
                    <td><?php echo e(count($p['items'] ?? [])); ?></td>
                    <td><span class="badge <?php echo e(($p['is_shared'] ?? false) ? 'badge-green' : 'badge-gray'); ?>"><?php echo e(($p['is_shared'] ?? false) ? 'Yes' : 'No'); ?></span></td>
                    <td><?php echo e(isset($p['created_at']) ? \Carbon\Carbon::parse($p['created_at'])->format('M d, Y') : '-'); ?></td>
                </tr>
                <?php if(!empty($p['items'])): ?>
                <tr class="items-sub-row">
                    <td colspan="8">
                        <table style="width:100%; font-size:9px;">
                            <thead><tr style="background:#e0f2fe;">
                                <th style="padding:3px 6px;">Medication</th>
                                <th style="padding:3px 6px;">Dosage</th>
                                <th style="padding:3px 6px;">Qty</th>
                                <th style="padding:3px 6px;">Duration</th>
                                <th style="padding:3px 6px;">Price</th>
                                <th style="padding:3px 6px;">Status</th>
                                <th style="padding:3px 6px;">Instructions</th>
                            </tr></thead>
                            <tbody>
                                <?php $__currentLoopData = $p['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td style="padding:3px 6px;"><?php echo e($item['medication_name'] ?? '-'); ?></td>
                                    <td style="padding:3px 6px;"><?php echo e($item['dosage'] ?? '-'); ?></td>
                                    <td style="padding:3px 6px;"><?php echo e($item['quantity'] ?? '-'); ?></td>
                                    <td style="padding:3px 6px;"><?php echo e($item['duration_days'] ? $item['duration_days'].' days' : '-'); ?></td>
                                    <td style="padding:3px 6px;"><?php echo e($item['price'] ? 'EGP '.number_format($item['price'], 2) : '-'); ?></td>
                                    <td style="padding:3px 6px;"><span class="badge badge-gray"><?php echo e(ucfirst($item['status'] ?? '-')); ?></span></td>
                                    <td style="padding:3px 6px;"><?php echo e(Str::limit($item['instructions'] ?? '-', 40)); ?></td>
                                </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </td>
                </tr>
                <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
        <?php else: ?>
        <p class="no-data">No prescriptions in this period.</p>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>


<?php if(isset($report['sections']['bookings'])): ?>
<?php $book = $report['sections']['bookings']; ?>
<div class="section">
    <div class="section-header">
        <span class="section-icon">&#128466;</span>
        <span class="section-title">Bookings</span>
        <span class="section-count"><?php echo e($book['total']); ?> total</span>
    </div>
    <div class="section-body">
        <div class="summary-stats mb-3">
            <div class="summary-stats-row">
                <div class="summary-stat-cell">
                    <div class="stat-val"><?php echo e($book['total']); ?></div>
                    <div class="stat-label">Total</div>
                </div>
                <?php $__currentLoopData = $book['by_status'] ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status => $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="summary-stat-cell">
                    <div class="stat-val"><?php echo e($d['count']); ?></div>
                    <div class="stat-label"><?php echo e(ucfirst($status)); ?></div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <div class="summary-stat-cell">
                    <div class="stat-val">EGP <?php echo e(number_format($book['total_revenue'] ?? 0, 0)); ?></div>
                    <div class="stat-label">Revenue</div>
                </div>
                <div class="summary-stat-cell">
                    <div class="stat-val">EGP <?php echo e(number_format($book['avg_booking_value'] ?? 0, 0)); ?></div>
                    <div class="stat-label">Avg Value</div>
                </div>
            </div>
        </div>
        <?php if(!empty($book['by_type']) || !empty($book['by_payment_method'])): ?>
        <div style="display:table; width:100%; margin-bottom:12px;">
            <div style="display:table-row;">
                <?php if(!empty($book['by_type'])): ?>
                <div style="display:table-cell; width:50%; padding-right:8px; vertical-align:top;">
                    <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:6px; padding:10px;">
                        <div style="font-size:10px; font-weight:700; color:#64748b; margin-bottom:6px;">By Type</div>
                        <?php $__currentLoopData = $book['by_type']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type => $count): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div style="display:flex; justify-content:space-between; font-size:10px; padding:2px 0;">
                            <span><?php echo e(ucfirst(str_replace('_', ' ', $type))); ?></span>
                            <strong><?php echo e($count); ?></strong>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
                <?php endif; ?>
                <?php if(!empty($book['by_payment_method'])): ?>
                <div style="display:table-cell; width:50%; padding-left:8px; vertical-align:top;">
                    <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:6px; padding:10px;">
                        <div style="font-size:10px; font-weight:700; color:#64748b; margin-bottom:6px;">By Payment Method</div>
                        <?php $__currentLoopData = $book['by_payment_method']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $method => $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div style="display:flex; justify-content:space-between; font-size:10px; padding:2px 0;">
                            <span><?php echo e(ucfirst(str_replace('_', ' ', $method))); ?></span>
                            <strong><?php echo e($d['count']); ?> &bull; EGP <?php echo e(number_format($d['total'], 0)); ?></strong>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
        <?php if(!empty($book['recent'])): ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Booking No.</th>
                    <th>Patient</th>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Amount</th>
                    <th>Payment</th>
                    <th>Rating</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $book['recent']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $statusColor = match($b['status'] ?? '') {
                        'completed'  => 'badge-green',
                        'cancelled'  => 'badge-red',
                        'in_progress' => 'badge-blue',
                        'upcoming'   => 'badge-yellow',
                        default      => 'badge-gray',
                    };
                ?>
                <tr>
                    <td><?php echo e($i + 1); ?></td>
                    <td><?php echo e($b['booking_number'] ?? '-'); ?></td>
                    <td><?php echo e($b['patient_name'] ?? ($b['user']['username'] ?? '-')); ?></td>
                    <td><?php echo e(isset($b['appointment_date']) ? \Carbon\Carbon::parse($b['appointment_date'])->format('M d, Y') : '-'); ?></td>
                    <td><?php echo e(ucfirst(str_replace('_', ' ', $b['booking_type'] ?? '-'))); ?></td>
                    <td><span class="badge <?php echo e($statusColor); ?>"><?php echo e(ucfirst($b['status'] ?? '-')); ?></span></td>
                    <td>EGP <?php echo e(number_format($b['total_amount'] ?? 0, 2)); ?></td>
                    <td><span class="badge badge-gray"><?php echo e(ucfirst(str_replace('_', ' ', $b['payment_status'] ?? '-'))); ?></span></td>
                    <td><?php echo e($b['rating'] ? str_repeat('★', (int) $b['rating']) : '-'); ?></td>
                </tr>
                <?php if(!empty($b['complaint'])): ?>
                <tr style="background:#fff7ed;">
                    <td></td>
                    <td colspan="8" style="color:#92400e; font-size:9px; padding:3px 8px;">
                        <strong>Complaint:</strong> <?php echo e(Str::limit($b['complaint'], 100)); ?>

                    </td>
                </tr>
                <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
        <?php else: ?>
        <p class="no-data">No bookings in this period.</p>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>


<?php if(isset($report['sections']['patients'])): ?>
<?php $pat = $report['sections']['patients']; ?>
<div class="section">
    <div class="section-header">
        <span class="section-icon">&#128101;</span>
        <span class="section-title">Patients</span>
        <span class="section-count"><?php echo e($pat['total']); ?> unique</span>
    </div>
    <div class="section-body">
        <?php if(!empty($pat['patients'])): ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Patient Name</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Total Visits</th>
                    <th>Total Spent</th>
                    <th>First Visit</th>
                    <th>Last Visit</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $pat['patients']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($i + 1); ?></td>
                    <td><?php echo e($p['patient_name'] ?? ($p['user']['username'] ?? '-')); ?></td>
                    <td><?php echo e($p['user']['phone'] ?? '-'); ?></td>
                    <td><?php echo e($p['user']['email'] ?? '-'); ?></td>
                    <td><?php echo e($p['visits_count'] ?? 0); ?></td>
                    <td>EGP <?php echo e(number_format($p['total_spent'] ?? 0, 2)); ?></td>
                    <td><?php echo e(isset($p['first_visit']) ? \Carbon\Carbon::parse($p['first_visit'])->format('M d, Y') : '-'); ?></td>
                    <td><?php echo e(isset($p['last_visit']) ? \Carbon\Carbon::parse($p['last_visit'])->format('M d, Y') : '-'); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
        <?php else: ?>
        <p class="no-data">No patients in this period.</p>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>


<?php if(isset($report['sections']['wallet'])): ?>
<?php $wall = $report['sections']['wallet']; ?>
<div class="section">
    <div class="section-header">
        <span class="section-icon">&#128181;</span>
        <span class="section-title">Wallet &amp; Revenue</span>
        <span class="section-count"><?php echo e($wall['transactions_count'] ?? 0); ?> transactions</span>
    </div>
    <div class="section-body">
        <div class="wallet-grid mb-3">
            <div class="wallet-row">
                <div class="wallet-cell">
                    <div class="wallet-card">
                        <div class="wallet-val">EGP <?php echo e(number_format($wall['balance'] ?? 0, 2)); ?></div>
                        <div class="wallet-label">Current Balance</div>
                    </div>
                </div>
                <div class="wallet-cell">
                    <div class="wallet-card wallet-pending">
                        <div class="wallet-val">EGP <?php echo e(number_format($wall['pending_balance'] ?? 0, 2)); ?></div>
                        <div class="wallet-label">Pending Balance</div>
                    </div>
                </div>
                <div class="wallet-cell">
                    <div class="wallet-card">
                        <div class="wallet-val">EGP <?php echo e(number_format($wall['total_earnings'] ?? 0, 2)); ?></div>
                        <div class="wallet-label">Total Earnings</div>
                    </div>
                </div>
                <div class="wallet-cell">
                    <div class="wallet-card">
                        <div class="wallet-val"><?php echo e($wall['commission_rate'] ?? 0); ?>%</div>
                        <div class="wallet-label">Commission Rate</div>
                    </div>
                </div>
            </div>
        </div>
        <?php if(!empty($wall['by_type'])): ?>
        <div style="margin-bottom:12px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:6px; padding:10px;">
            <div style="font-size:10px; font-weight:700; color:#64748b; margin-bottom:6px;">Breakdown by Type</div>
            <div style="display:table; width:100%;">
                <div style="display:table-row;">
                    <?php $__currentLoopData = $wall['by_type']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type => $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div style="display:table-cell; text-align:center; padding:4px 8px; border-right:1px solid #e2e8f0;">
                        <div style="font-size:14px; font-weight:700; color:#1e40af;"><?php echo e($d['count']); ?></div>
                        <div style="font-size:9px; color:#64748b;"><?php echo e(ucfirst(str_replace('_', ' ', $type))); ?></div>
                        <div style="font-size:9px; color:#059669;">EGP <?php echo e(number_format($d['total'], 0)); ?></div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <?php if(!empty($wall['transactions'])): ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Type</th>
                    <th>Amount</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Booking ID</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $wall['transactions']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $tColor = match($t['type'] ?? '') {
                        'commission', 'booking_payment' => 'badge-green',
                        'withdrawal' => 'badge-yellow',
                        'refund'     => 'badge-red',
                        'transfer'   => 'badge-purple',
                        default      => 'badge-gray',
                    };
                    $sColor = match($t['status'] ?? '') {
                        'completed'  => 'badge-green',
                        'pending'    => 'badge-yellow',
                        'failed'     => 'badge-red',
                        default      => 'badge-gray',
                    };
                ?>
                <tr>
                    <td><?php echo e($i + 1); ?></td>
                    <td><span class="badge <?php echo e($tColor); ?>"><?php echo e(ucfirst(str_replace('_', ' ', $t['type'] ?? '-'))); ?></span></td>
                    <td>EGP <?php echo e(number_format($t['amount'] ?? 0, 2)); ?></td>
                    <td><?php echo e(Str::limit($t['description'] ?? '-', 35)); ?></td>
                    <td><span class="badge <?php echo e($sColor); ?>"><?php echo e(ucfirst($t['status'] ?? '-')); ?></span></td>
                    <td><?php echo e($t['booking_id'] ?? '-'); ?></td>
                    <td><?php echo e(isset($t['created_at']) ? \Carbon\Carbon::parse($t['created_at'])->format('M d, Y') : '-'); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
        <?php else: ?>
        <p class="no-data">No transactions found.</p>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>


<?php if(isset($report['sections']['visits'])): ?>
<?php $vis = $report['sections']['visits']; ?>
<div class="section">
    <div class="section-header">
        <span class="section-icon">&#128205;</span>
        <span class="section-title">Representative Visits</span>
        <span class="section-count"><?php echo e($vis['total']); ?> total</span>
    </div>
    <div class="section-body">
        <?php if(!empty($vis['by_status'])): ?>
        <div class="summary-stats mb-3">
            <div class="summary-stats-row">
                <?php $__currentLoopData = $vis['by_status']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status => $count): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="summary-stat-cell">
                    <div class="stat-val"><?php echo e($count); ?></div>
                    <div class="stat-label"><?php echo e(ucfirst($status)); ?></div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
        <?php endif; ?>
        <?php if(!empty($vis['visits'])): ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Representative</th>
                    <th>Phone</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Purpose</th>
                    <th>Status</th>
                    <th>Confirmed</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $vis['visits']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $vColor = match($v['status'] ?? '') {
                        'completed', 'confirmed' => 'badge-green',
                        'cancelled', 'rejected'  => 'badge-red',
                        'pending'                => 'badge-yellow',
                        default                  => 'badge-gray',
                    };
                ?>
                <tr>
                    <td><?php echo e($i + 1); ?></td>
                    <td><?php echo e($v['representative']['user']['username'] ?? '-'); ?></td>
                    <td><?php echo e($v['representative']['user']['phone'] ?? '-'); ?></td>
                    <td><?php echo e(isset($v['visit_date']) ? \Carbon\Carbon::parse($v['visit_date'])->format('M d, Y') : '-'); ?></td>
                    <td><?php echo e($v['visit_time'] ?? '-'); ?></td>
                    <td><?php echo e(Str::limit($v['purpose'] ?? '-', 35)); ?></td>
                    <td><span class="badge <?php echo e($vColor); ?>"><?php echo e(ucfirst($v['status'] ?? '-')); ?></span></td>
                    <td><?php echo e($v['doctor_confirmed_at'] ? '&#10003;' : '&mdash;'); ?></td>
                </tr>
                <?php if(!empty($v['notes']) || !empty($v['rejection_reason'])): ?>
                <tr style="background:#f0fdf4;">
                    <td></td>
                    <td colspan="7" style="font-size:9px; color:#374151; padding:3px 8px;">
                        <?php if(!empty($v['notes'])): ?><strong>Notes:</strong> <?php echo e(Str::limit($v['notes'], 80)); ?>&nbsp;<?php endif; ?>
                        <?php if(!empty($v['rejection_reason'])): ?><strong style="color:#dc2626;">Rejection:</strong> <?php echo e(Str::limit($v['rejection_reason'], 60)); ?><?php endif; ?>
                    </td>
                </tr>
                <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
        <?php else: ?>
        <p class="no-data">No visits in this period.</p>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>


<?php if(isset($report['sections']['medical_centers'])): ?>
<?php $mc = $report['sections']['medical_centers']; ?>
<div class="section">
    <div class="section-header">
        <span class="section-icon">&#127973;</span>
        <span class="section-title">Medical Centers</span>
        <span class="section-count"><?php echo e($mc['total']); ?> centers</span>
    </div>
    <div class="section-body">
        <?php if(!empty($mc['primary'])): ?>
        <div style="background:#eff6ff; border:1px solid #bfdbfe; border-radius:6px; padding:10px 14px; margin-bottom:12px;">
            <div style="font-size:10px; font-weight:700; color:#1e40af; margin-bottom:4px;">&#9733; Primary Medical Center</div>
            <div style="font-size:12px; font-weight:600;"><?php echo e($mc['primary']['name'] ?? '-'); ?></div>
            <div style="font-size:10px; color:#64748b;"><?php echo e($mc['primary']['address'] ?? '-'); ?></div>
            <?php if(!empty($mc['primary']['phone'])): ?><div style="font-size:10px; color:#64748b;">&#128222; <?php echo e($mc['primary']['phone']); ?></div><?php endif; ?>
            <?php if(!empty($mc['primary']['email'])): ?><div style="font-size:10px; color:#64748b;">&#9993; <?php echo e($mc['primary']['email']); ?></div><?php endif; ?>
        </div>
        <?php endif; ?>
        <?php if(!empty($mc['centers'])): ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Address</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Active</th>
                    <th>Primary</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $mc['centers']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($i + 1); ?></td>
                    <td><?php echo e($c['name'] ?? '-'); ?></td>
                    <td><?php echo e(Str::limit($c['address'] ?? '-', 40)); ?></td>
                    <td><?php echo e($c['phone'] ?? '-'); ?></td>
                    <td><?php echo e($c['email'] ?? '-'); ?></td>
                    <td><?php echo e(isset($c['is_active']) ? ($c['is_active'] ? '&#10003;' : '&times;') : '-'); ?></td>
                    <td>
                        <?php if(isset($mc['primary']['id']) && $mc['primary']['id'] === $c['id']): ?>
                            <span class="badge badge-blue">Primary</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
        <?php else: ?>
        <p class="no-data">No medical centers found.</p>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>


<?php if(isset($report['sections']['treatments'])): ?>
<?php $tr = $report['sections']['treatments']; ?>
<div class="section">
    <div class="section-header">
        <span class="section-icon">&#128138;</span>
        <span class="section-title">Selected Treatments &amp; Medications</span>
        <span class="section-count"><?php echo e($tr['total']); ?> treatments</span>
    </div>
    <div class="section-body">
        <?php if(!empty($tr['treatments'])): ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Treatment / Medication Name</th>
                    <th>Company</th>
                    <th>Added Date</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $tr['treatments']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($i + 1); ?></td>
                    <td><?php echo e($t['name'] ?? '-'); ?></td>
                    <td><?php echo e($t['company'] ?? '-'); ?></td>
                    <td><?php echo e(isset($t['created_at']) ? \Carbon\Carbon::parse($t['created_at'])->format('M d, Y') : '-'); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
        <?php else: ?>
        <p class="no-data">No treatments selected.</p>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>


<?php if(isset($report['sections']['ratings'])): ?>
<?php $rat = $report['sections']['ratings']; ?>
<div class="section">
    <div class="section-header">
        <span class="section-icon">&#11088;</span>
        <span class="section-title">Ratings &amp; Reviews</span>
        <span class="section-count"><?php echo e($rat['total']); ?> reviews</span>
    </div>
    <div class="section-body">
        <div class="summary-stats mb-3">
            <div class="summary-stats-row">
                <div class="summary-stat-cell">
                    <div class="stat-val"><span class="stars">&#9733;</span> <?php echo e($rat['average']); ?></div>
                    <div class="stat-label">Average Rating</div>
                </div>
                <div class="summary-stat-cell">
                    <div class="stat-val"><?php echo e($rat['total']); ?></div>
                    <div class="stat-label">Total Reviews</div>
                </div>
                <div class="summary-stat-cell">
                    <div class="stat-val"><?php echo e($rat['approved']); ?></div>
                    <div class="stat-label">Approved</div>
                </div>
                <div class="summary-stat-cell">
                    <div class="stat-val"><?php echo e($rat['total'] - ($rat['approved'] ?? 0)); ?></div>
                    <div class="stat-label">Pending Approval</div>
                </div>
            </div>
        </div>
        <?php if(!empty($rat['distribution'])): ?>
        <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:6px; padding:10px 14px; margin-bottom:12px;">
            <div style="font-size:10px; font-weight:700; color:#64748b; margin-bottom:6px;">Rating Distribution</div>
            <?php $__currentLoopData = array_reverse(range(1, 5)); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $star): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php $cnt = $rat['distribution'][(string)$star] ?? 0; $pct = $rat['total'] > 0 ? round($cnt / $rat['total'] * 100) : 0; ?>
            <div style="display:table; width:100%; margin-bottom:3px;">
                <div style="display:table-cell; width:60px; font-size:10px; color:#f59e0b;"><?php echo e(str_repeat('★', $star)); ?></div>
                <div style="display:table-cell; width:200px; vertical-align:middle;">
                    <div style="background:#e2e8f0; border-radius:3px; height:8px; overflow:hidden;">
                        <div style="background:#f59e0b; height:8px; width:<?php echo e($pct); ?>%;"></div>
                    </div>
                </div>
                <div style="display:table-cell; font-size:10px; color:#64748b; padding-left:8px;"><?php echo e($cnt); ?> (<?php echo e($pct); ?>%)</div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        <?php endif; ?>
        <?php if(!empty($rat['ratings'])): ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Patient</th>
                    <th>Phone</th>
                    <th>Rating</th>
                    <th>Comment</th>
                    <th>Approved</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $rat['ratings']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($i + 1); ?></td>
                    <td><?php echo e($r['user']['username'] ?? '-'); ?></td>
                    <td><?php echo e($r['user']['phone'] ?? '-'); ?></td>
                    <td><span class="stars"><?php echo e(str_repeat('★', (int)($r['rating'] ?? 0))); ?></span> <?php echo e($r['rating']); ?></td>
                    <td><?php echo e(Str::limit($r['comment'] ?? '-', 50)); ?></td>
                    <td>
                        <span class="badge <?php echo e(($r['is_approved'] ?? false) ? 'badge-green' : 'badge-yellow'); ?>">
                            <?php echo e(($r['is_approved'] ?? false) ? 'Yes' : 'Pending'); ?>

                        </span>
                    </td>
                    <td><?php echo e(isset($r['created_at']) ? \Carbon\Carbon::parse($r['created_at'])->format('M d, Y') : '-'); ?></td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
        <?php else: ?>
        <p class="no-data">No reviews found.</p>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>


<div class="footer">
    <div class="footer-left">
        Eldokan Medical Platform &bull; Confidential Report<br>
        Doctor: <?php echo e($report['doctor']['name'] ?? '-'); ?> &bull; ID: <?php echo e($report['doctor']['id'] ?? '-'); ?>

    </div>
    <div class="footer-right">
        Period: <?php echo e($report['period']['from']); ?> to <?php echo e($report['period']['to']); ?><br>
        <?php echo e($report['generated_at']); ?>

    </div>
</div>

</body>
</html>
<?php /**PATH C:\Users\HP\Desktop\Eldokan\backend\resources\views/reports/doctor-report.blade.php ENDPATH**/ ?>