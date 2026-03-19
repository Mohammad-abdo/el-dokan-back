<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <?php use Illuminate\Support\Str; ?>
    <meta charset="UTF-8">
    <title>Shop Report — <?php echo e($report['shop']['name'] ?? 'Shop'); ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { direction: rtl; unicode-bidi: bidi-override; }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            color: #1a202c;
            background: #ffffff;
            line-height: 1.5;
        }
        table, th, td { direction: rtl; text-align: right; }

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
            background: rgba(255,255,255,0.2);
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: 22px; font-weight: 900; color: #ffffff;
            border: 2px solid rgba(255,255,255,0.4);
        }
        .brand-name { font-size: 20px; font-weight: 700; letter-spacing: 0.5px; }
        .brand-sub  { font-size: 10px; opacity: 0.8; }
        .report-meta { text-align: right; font-size: 11px; opacity: 0.9; }
        .report-title { font-size: 16px; font-weight: 700; margin-bottom: 4px; }
        .report-period {
            background: rgba(255,255,255,0.15);
            border-radius: 20px;
            padding: 4px 14px;
            display: inline-block;
            font-size: 11px;
            border: 1px solid rgba(255,255,255,0.3);
        }
        .generated-at { font-size: 10px; opacity: 0.7; margin-top: 8px; }

        .shop-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-left: 5px solid #3b82f6;
            border-radius: 8px;
            padding: 18px 24px;
            margin: 20px 32px 0;
        }
        .shop-card-top { display: flex; gap: 16px; align-items: flex-start; }
        .shop-avatar {
            width: 56px; height: 56px;
            border-radius: 12px;
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            display: flex; align-items: center; justify-content: center;
            color: #fff;
            font-weight: 800;
            font-size: 22px;
            border: 2px solid rgba(59,130,246,0.2);
        }
        .shop-info { flex: 1; }
        .shop-name { font-size: 16px; font-weight: 800; color: #1e3a8a; margin-bottom: 6px; }
        .shop-meta { font-size: 11px; color: #64748b; }
        .shop-meta strong { color: #0f172a; }

        .section { margin: 0 32px 24px; }
        .section-header {
            display: flex; align-items: center; gap: 8px;
            padding: 10px 16px;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 6px 6px 0 0;
            border-bottom: 2px solid #3b82f6;
        }
        .section-title { font-size: 13px; font-weight: 700; color: #1e40af; }
        .section-count {
            margin-left: auto;
            background: #3b82f6;
            color: #fff;
            border-radius: 10px;
            padding: 1px 10px;
            font-size: 10px;
            font-weight: 600;
        }
        .section-body {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-top: none;
            border-radius: 0 0 6px 6px;
            padding: 16px;
        }

        .data-table { width: 100%; border-collapse: collapse; font-size: 10px; }
        .data-table thead tr { background: #1e40af; color: #ffffff; }
        .data-table thead th { padding: 7px 8px; text-align: left; font-weight: 600; font-size: 9px; }
        .data-table tbody td { padding: 6px 8px; color: #374151; border-bottom: 1px solid #f1f5f9; }
        .data-table tbody tr:last-child td { border-bottom: none; }
        .no-data { text-align: center; padding: 20px; color: #94a3b8; font-size: 11px; }

        .badge {
            display: inline-block; padding: 2px 8px;
            border-radius: 10px; font-size: 9px;
            font-weight: 600;
        }
        .badge-green  { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
        .badge-red    { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
        .badge-yellow { background: #fef9c3; color: #854d0e; border: 1px solid #fde68a; }
        .badge-blue   { background: #dbeafe; color: #1e40af; border: 1px solid #bfdbfe; }
        .badge-gray   { background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; }

        .kv-table { width: 100%; border-collapse: collapse; font-size: 10px; margin-bottom: 12px; }
        .kv-table td { padding: 8px 8px; border-bottom: 1px solid #f1f5f9; }
        .kv-table td:first-child { width: 45%; font-weight: 700; color: #64748b; }

        .img-thumb { width: 40px; height: 40px; object-fit: cover; border-radius: 8px; border: 1px solid #e2e8f0; }

        .footer {
            margin-top: 24px;
            border-top: 2px solid #e2e8f0;
            padding: 14px 32px;
            background: #f8fafc;
            display: flex; justify-content: space-between; align-items: center;
        }
        .footer-left  { font-size: 10px; color: #94a3b8; }
        .footer-right { font-size: 10px; color: #94a3b8; text-align: right; }
        .text-muted { color: #94a3b8; }
        .page-break { page-break-before: always; }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-top">
            <div class="logo-area">
                <div class="logo-box">E</div>
                <div>
                    <div class="brand-name">Eldokan</div>
                    <div class="brand-sub">Reporting Platform</div>
                </div>
            </div>
            <div class="report-meta">
                <div class="report-title">Shop Report</div>
                <div class="report-period">
                    <?php echo e(\Carbon\Carbon::parse($report['period']['from'])->format('M d, Y')); ?>

                    &mdash;
                    <?php echo e(\Carbon\Carbon::parse($report['period']['to'])->format('M d, Y')); ?>

                </div>
            </div>
        </div>
        <div class="generated-at">Generated: <?php echo e($report['generated_at']); ?></div>
    </div>

    <?php $shop = $report['shop'] ?? []; ?>
    <div class="shop-card">
        <div class="shop-card-top">
            <div class="shop-avatar">
                <?php echo e(mb_strtoupper(mb_substr($shop['name'] ?? 'S', 0, 1))); ?>

            </div>
            <div class="shop-info">
                <div class="shop-name"><?php echo e($shop['name'] ?? '-'); ?></div>
                <div class="shop-meta">
                    <div><strong>ID:</strong> <?php echo e($shop['id'] ?? '-'); ?></div>
                    <div><strong>Category:</strong> <?php echo e($shop['category'] ?? '-'); ?></div>
                    <div><strong>Phone:</strong> <?php echo e($shop['phone'] ?? '-'); ?></div>
                    <div><strong>Address:</strong> <?php echo e(Str::limit($shop['address'] ?? '-', 80)); ?></div>
                    <?php if(isset($shop['vendor_status'])): ?>
                        <div><strong>Status:</strong> <?php echo e($shop['vendor_status']); ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    
    <?php if(isset($report['sections']['overview'])): ?>
        <?php $ov = $report['sections']['overview']; ?>
        <div class="section page-break">
            <div class="section-header">
                <span class="section-title">Overview</span>
                <span class="section-count">Summary</span>
            </div>
            <div class="section-body">
                <?php if(!empty($ov['summary'])): ?>
                    <table class="kv-table">
                        <tbody>
                            <?php $__currentLoopData = $ov['summary']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td><?php echo e($k); ?></td>
                                    <td><?php echo e(is_scalar($v) || $v === null ? ($v ?? '-') : Str::limit(json_encode($v, JSON_UNESCAPED_UNICODE), 120)); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="no-data">No overview data.</p>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    
    <?php if(isset($report['sections']['products'])): ?>
        <?php $pblock = $report['sections']['products']; ?>
        <div class="section">
            <div class="section-header">
                <span class="section-title">Products &amp; Sales</span>
                <span class="section-count"><?php echo e(count($pblock['products'] ?? [])); ?> items</span>
            </div>
            <div class="section-body">
                <?php if(!empty($pblock['products'])): ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Image</th>
                                <th>Product</th>
                                <th>Category</th>
                                <th>Qty Sold</th>
                                <th>Total Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = ($pblock['products'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td><?php echo e($i + 1); ?></td>
                                    <td>
                                        <?php if(!empty($p['first_image_url'])): ?>
                                            <img class="img-thumb" src="<?php echo e($p['first_image_url']); ?>" alt="img" />
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo e($p['product_name'] ?? ($p['product_name_ar'] ?? '-')); ?></td>
                                    <td><?php echo e($p['category'] ?? '-'); ?></td>
                                    <td><?php echo e($p['total_quantity_sold'] ?? 0); ?></td>
                                    <td><?php echo e(number_format($p['total_revenue'] ?? 0, 2)); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="no-data">No products found for this period.</p>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    
    <?php if(isset($report['sections']['wallet'])): ?>
        <?php $w = $report['sections']['wallet']; ?>
        <div class="section">
            <div class="section-header">
                <span class="section-title">Wallet &amp; Transactions</span>
                <span class="section-count"><?php echo e($w['transactions_count'] ?? 0); ?> transactions</span>
            </div>
            <div class="section-body">
                <table class="kv-table">
                    <tbody>
                        <?php $__currentLoopData = [
                            'balance' => 'balance',
                            'pending_balance' => 'pending_balance',
                            'total_revenue' => 'total_revenue',
                            'total_commission' => 'total_commission',
                            'commission_rate' => 'commission_rate',
                        ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $label => $key): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td><?php echo e($label); ?></td>
                                <td><?php echo e(isset($w[$key]) ? $w[$key] : '-'); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>

                <?php if(!empty($w['transactions'])): ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Source</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Commission</th>
                                <th>Status</th>
                                <th>Reference</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = ($w['transactions'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php
                                    $badge = match(($t['status'] ?? '') ) {
                                        'completed' => 'badge-green',
                                        'pending' => 'badge-yellow',
                                        'failed' => 'badge-red',
                                        default => 'badge-gray',
                                    };
                                ?>
                                <tr>
                                    <td><?php echo e($i + 1); ?></td>
                                    <td><?php echo e($t['source'] ?? '-'); ?></td>
                                    <td><?php echo e($t['type'] ?? '-'); ?></td>
                                    <td><?php echo e(number_format($t['amount'] ?? 0, 2)); ?></td>
                                    <td><?php echo e($t['commission'] ?? '-'); ?></td>
                                    <td><span class="badge <?php echo e($badge); ?>"><?php echo e(ucfirst($t['status'] ?? '-')); ?></span></td>
                                    <td><?php echo e($t['order_number'] ?? ($t['description'] ?? '-')); ?></td>
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

    
    <?php if(!empty($report['sections']['ordersFromReps']['orders'])): ?>
        <?php $b = $report['sections']['ordersFromReps']; ?>
        <div class="section">
            <div class="section-header">
                <span class="section-title">Orders From Reps</span>
                <span class="section-count"><?php echo e($b['total'] ?? count($b['orders'] ?? [])); ?> orders</span>
            </div>
            <div class="section-body">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Order #</th>
                            <th>Status</th>
                            <th>Ordered At</th>
                            <th>Representative</th>
                            <th>Customer</th>
                            <th>Total Amount</th>
                            <th>Items</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = ($b['orders'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $o): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $st = $o['status'] ?? '';
                                $badge = match($st) {
                                    'confirmed', 'delivered' => 'badge-green',
                                    'pending' => 'badge-yellow',
                                    'cancelled' => 'badge-red',
                                    default => 'badge-gray',
                                };
                                $repName = $o['representative']['user']['username'] ?? ($o['representative']['user']['name'] ?? '-');
                                $customerName = $o['customerShop']['name'] ?? ($o['customerDoctor']['name'] ?? ($o['customer_id'] ?? '-'));
                            ?>
                            <tr>
                                <td><?php echo e($i + 1); ?></td>
                                <td><?php echo e($o['order_number'] ?? $o['id'] ?? '-'); ?></td>
                                <td><span class="badge <?php echo e($badge); ?>"><?php echo e(ucfirst($st ?: '-')); ?></span></td>
                                <td><?php echo e(isset($o['ordered_at']) ? \Carbon\Carbon::parse($o['ordered_at'])->format('M d, Y') : '-'); ?></td>
                                <td><?php echo e($repName); ?></td>
                                <td><?php echo e($customerName); ?></td>
                                <td><?php echo e(number_format($o['total_amount'] ?? 0, 2)); ?></td>
                                <td><?php echo e(count($o['items'] ?? [])); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    
    <?php if(!empty($report['sections']['visits']['visits'])): ?>
        <?php $b = $report['sections']['visits']; ?>
        <div class="section">
            <div class="section-header">
                <span class="section-title">Visits</span>
                <span class="section-count"><?php echo e($b['total'] ?? count($b['visits'] ?? [])); ?> visits</span>
            </div>
            <div class="section-body">
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
                        <?php $__currentLoopData = ($b['visits'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $v): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $st = $v['status'] ?? '';
                                $badge = match($st) {
                                    'completed', 'confirmed' => 'badge-green',
                                    'pending' => 'badge-yellow',
                                    'cancelled', 'rejected' => 'badge-red',
                                    default => 'badge-gray',
                                };
                                $repUser = $v['representative']['user'] ?? [];
                            ?>
                            <tr>
                                <td><?php echo e($i + 1); ?></td>
                                <td><?php echo e($repUser['username'] ?? '-'); ?></td>
                                <td><?php echo e($repUser['phone'] ?? '-'); ?></td>
                                <td><?php echo e(isset($v['visit_date']) ? \Carbon\Carbon::parse($v['visit_date'])->format('M d, Y') : '-'); ?></td>
                                <td><?php echo e($v['visit_time'] ?? '-'); ?></td>
                                <td><?php echo e(Str::limit($v['purpose'] ?? '-', 40)); ?></td>
                                <td><span class="badge <?php echo e($badge); ?>"><?php echo e(ucfirst($st ?: '-')); ?></span></td>
                                <td><?php echo e(!empty($v['doctor_confirmed_at']) ? '&#10003;' : '&mdash;'); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    
    <?php if(!empty($report['sections']['representatives']['representatives'])): ?>
        <?php $b = $report['sections']['representatives']; ?>
        <div class="section">
            <div class="section-header">
                <span class="section-title">Representatives</span>
                <span class="section-count"><?php echo e($b['total'] ?? count($b['representatives'] ?? [])); ?> reps</span>
            </div>
            <div class="section-body">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Employee ID</th>
                            <th>User</th>
                            <th>Phone</th>
                            <th>Territory</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = ($b['representatives'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $u = $r['user'] ?? [];
                            ?>
                            <tr>
                                <td><?php echo e($i + 1); ?></td>
                                <td><?php echo e($r['employee_id'] ?? '-'); ?></td>
                                <td><?php echo e($u['username'] ?? '-'); ?></td>
                                <td><?php echo e($u['phone'] ?? '-'); ?></td>
                                <td><?php echo e(Str::limit($r['territory'] ?? '-', 40)); ?></td>
                                <td><?php echo e(ucfirst($r['status'] ?? '-')); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    
    <?php if(!empty($report['sections']['companyOrders']['orders'])): ?>
        <?php $b = $report['sections']['companyOrders']; ?>
        <div class="section">
            <div class="section-header">
                <span class="section-title">Company Orders</span>
                <span class="section-count"><?php echo e($b['total'] ?? count($b['orders'] ?? [])); ?> orders</span>
            </div>
            <div class="section-body">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Order #</th>
                            <th>Status</th>
                            <th>Ordered At</th>
                            <th>Representative</th>
                            <th>Customer Type</th>
                            <th>Customer</th>
                            <th>Total Amount</th>
                            <th>Items</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = ($b['orders'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $o): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $st = $o['status'] ?? '';
                                $badge = match($st) {
                                    'confirmed', 'delivered' => 'badge-green',
                                    'pending' => 'badge-yellow',
                                    'cancelled' => 'badge-red',
                                    default => 'badge-gray',
                                };
                                $repName = $o['representative']['user']['username'] ?? ($o['representative']['user']['name'] ?? '-');
                                $customerName = $o['customerShop']['name'] ?? ($o['customerDoctor']['name'] ?? ($o['customer_id'] ?? '-'));
                            ?>
                            <tr>
                                <td><?php echo e($i + 1); ?></td>
                                <td><?php echo e($o['order_number'] ?? $o['id'] ?? '-'); ?></td>
                                <td><span class="badge <?php echo e($badge); ?>"><?php echo e(ucfirst($st ?: '-')); ?></span></td>
                                <td><?php echo e(isset($o['ordered_at']) ? \Carbon\Carbon::parse($o['ordered_at'])->format('M d, Y') : '-'); ?></td>
                                <td><?php echo e($repName); ?></td>
                                <td><?php echo e($o['customer_type'] ?? '-'); ?></td>
                                <td><?php echo e($customerName); ?></td>
                                <td><?php echo e(number_format($o['total_amount'] ?? 0, 2)); ?></td>
                                <td><?php echo e(count($o['items'] ?? [])); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    
    <?php if(!empty($report['sections']['branches']['branches'])): ?>
        <?php $b = $report['sections']['branches']; ?>
        <div class="section">
            <div class="section-header">
                <span class="section-title">Branches</span>
                <span class="section-count"><?php echo e(count($b['branches'] ?? [])); ?> branches</span>
            </div>
            <div class="section-body">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Name (AR)</th>
                            <th>Address</th>
                            <th>Phone</th>
                            <th>Active</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = ($b['branches'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $br): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td><?php echo e($i + 1); ?></td>
                                <td><?php echo e($br['name'] ?? '-'); ?></td>
                                <td><?php echo e($br['name_ar'] ?? '-'); ?></td>
                                <td><?php echo e(Str::limit($br['address'] ?? '-', 45)); ?></td>
                                <td><?php echo e($br['phone'] ?? '-'); ?></td>
                                <td><?php echo e(isset($br['is_active']) ? ($br['is_active'] ? '&#10003;' : '&times;') : '-'); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    
    <?php if(!empty($report['sections']['documents']['documents'])): ?>
        <?php $b = $report['sections']['documents']; ?>
        <div class="section">
            <div class="section-header">
                <span class="section-title">Documents</span>
                <span class="section-count"><?php echo e(count($b['documents'] ?? [])); ?> docs</span>
            </div>
            <div class="section-body">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Type</th>
                            <th>Title</th>
                            <th>Title (AR)</th>
                            <th>Reference #</th>
                            <th>Issue Date</th>
                            <th>Expires At</th>
                            <th>Verified</th>
                            <th>File</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = ($b['documents'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td><?php echo e($i + 1); ?></td>
                                <td><?php echo e($d['type'] ?? '-'); ?></td>
                                <td><?php echo e($d['title'] ?? '-'); ?></td>
                                <td><?php echo e($d['title_ar'] ?? '-'); ?></td>
                                <td><?php echo e($d['reference_number'] ?? '-'); ?></td>
                                <td><?php echo e(isset($d['issue_date']) ? \Carbon\Carbon::parse($d['issue_date'])->format('M d, Y') : '-'); ?></td>
                                <td><?php echo e(isset($d['expires_at']) ? \Carbon\Carbon::parse($d['expires_at'])->format('M d, Y') : '-'); ?></td>
                                <td><?php echo e(isset($d['is_verified']) ? ($d['is_verified'] ? '&#10003;' : '&times;') : '-'); ?></td>
                                <td><?php echo e(!empty($d['file_url']) ? Str::limit($d['file_url'], 28) : '-'); ?></td>
                                <td><?php echo e(Str::limit($d['notes'] ?? '-', 35)); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <div class="footer">
        <div class="footer-left">
            Eldokan Reporting Platform<br>
            Shop: <?php echo e($shop['name'] ?? '-'); ?> &bull; ID: <?php echo e($shop['id'] ?? '-'); ?>

        </div>
        <div class="footer-right">
            Period: <?php echo e($report['period']['from'] ?? '-'); ?> to <?php echo e($report['period']['to'] ?? '-'); ?><br>
            <?php echo e($report['generated_at'] ?? '-'); ?>

        </div>
    </div>
</body>
</html>

<?php /**PATH C:\Users\HP\Desktop\Eldokan\backend\resources\views/reports/shop-report.blade.php ENDPATH**/ ?>