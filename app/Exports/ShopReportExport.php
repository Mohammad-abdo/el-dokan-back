<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;

class ShopReportExport implements FromArray
{
    public function __construct(private array $report)
    {
    }

    public function array(): array
    {
        $rows = [];

        $period = $this->report['period'] ?? [];
        $shop = $this->report['shop'] ?? [];
        $sections = $this->report['sections'] ?? [];

        $rows[] = ['Shop Report'];
        $rows[] = ['Shop', $shop['name'] ?? '-'];
        $rows[] = ['Period', ($period['from'] ?? '-') . ' → ' . ($period['to'] ?? '-')];
        $rows[] = ['Generated At', $this->report['generated_at'] ?? '-'];
        $rows[] = [];

        $addSectionTitle = function (string $title) use (&$rows) {
            $rows[] = [$title];
        };

        $addKeyValueTable = function (array $data) use (&$rows) {
            $rows[] = ['Key', 'Value'];
            foreach ($data as $k => $v) {
                $rows[] = [(string) $k, is_scalar($v) || $v === null ? (string) ($v ?? '') : json_encode($v, JSON_UNESCAPED_UNICODE)];
            }
        };

        // Overview
        if (!empty($sections['overview'])) {
            $addSectionTitle('Overview');
            $overview = $sections['overview'] ?? [];
            $summary = $overview['summary'] ?? [];
            if (!empty($summary)) {
                $addKeyValueTable($summary);
            }
            $rows[] = [];
        }

        // Products
        if (!empty($sections['products'])) {
            $addSectionTitle('Products & Sales');
            $productsBlock = $sections['products'] ?? [];
            $totals = $productsBlock['totals'] ?? [];
            if (!empty($totals)) {
                $addKeyValueTable($totals);
                $rows[] = [];
            }
            $items = $productsBlock['items'] ?? [];
            $rows[] = [
                '#',
                'Order #',
                'Order Date',
                'Order Status',
                'Payment Method',
                'Payment Status',
                'Discount Amount',
                'Delivery Fee',
                'Delivery Address ID',
                'Customer',
                'Customer Phone',
                'Customer Email',
                'Product Name',
                'Category',
                'Qty',
                'Unit Price',
                'Total Price',
                'First Image URL',
            ];
            foreach ($items as $i => $p) {
                $rows[] = [
                    $i + 1,
                    $p['order_number'] ?? '-',
                    $p['order_created_at'] ?? '-',
                    $p['order_status'] ?? '-',
                    $p['payment_method'] ?? '-',
                    $p['payment_status'] ?? '-',
                    $p['discount_amount'] ?? 0,
                    $p['delivery_fee'] ?? 0,
                    $p['delivery_address_id'] ?? '',
                    $p['customer_name'] ?? '-',
                    $p['customer_phone'] ?? '',
                    $p['customer_email'] ?? '',
                    $p['product_name'] ?? ($p['product_name_ar'] ?? '-'),
                    $p['category'] ?? '-',
                    $p['quantity'] ?? 0,
                    $p['unit_price'] ?? 0,
                    $p['total_price'] ?? 0,
                    $p['first_image_url'] ?? null,
                ];
            }
            $rows[] = [];
        }

        // Wallet
        if (!empty($sections['wallet'])) {
            $addSectionTitle('Wallet & Transactions');
            $wallet = $sections['wallet'] ?? [];
            $summaryKeys = [
                'balance',
                'pending_balance',
                'total_revenue',
                'total_commission',
                'commission_rate',
                'commission_profit_share',
                'transactions_count',
            ];
            $summary = [];
            foreach ($summaryKeys as $k) {
                if (array_key_exists($k, $wallet)) $summary[$k] = $wallet[$k];
            }
            if (!empty($summary)) {
                $addKeyValueTable($summary);
                $rows[] = [];
            }

            $rows[] = ['#', 'Source', 'Type', 'Amount', 'Commission', 'Status', 'Order ID', 'Order Number', 'User ID', 'Description', 'Admin User ID', 'Created At'];
            $transactions = $wallet['transactions'] ?? [];
            foreach ($transactions as $i => $t) {
                $rows[] = [
                    $i + 1,
                    $t['source'] ?? '-',
                    $t['type'] ?? '-',
                    $t['amount'] ?? 0,
                    $t['commission'] ?? '',
                    $t['status'] ?? '',
                    $t['order_id'] ?? '',
                    $t['order_number'] ?? '',
                    $t['user_id'] ?? '',
                    $t['description'] ?? '',
                    $t['admin_user_id'] ?? '',
                    $t['created_at'] ?? '',
                ];
            }
            $rows[] = [];
        }

        // Orders from reps (customers = this shop) - output one row per item
        $ordersFromReps = $sections['ordersFromReps']['orders'] ?? null;
        if (is_array($ordersFromReps)) {
            $addSectionTitle('Orders From Reps (To This Shop)');
            $rows[] = [
                '#',
                'Order #',
                'Status',
                'Ordered At',
                'Representative',
                'Customer Type',
                'Customer',
                'Visit Date',
                'Doctor Confirmed At',
                'Order Notes',
                'Item SKU',
                'Item Product',
                'Item Type',
                'Qty',
                'Unit Price',
                'Total Price',
                'Item Image',
            ];

            $rowIndex = 0;
            foreach ($ordersFromReps as $i => $o) {
                $items = is_array($o['items'] ?? null) ? ($o['items'] ?? []) : [];

                $customerName =
                    $o['customerShop']['name'] ?? ($o['customerDoctor']['name'] ?? ($o['customer_id'] ?? '-'));
                $repName = $o['representative']['user']['username'] ?? ($o['representative']['user']['name'] ?? '-');
                $visit = $o['visit'] ?? [];

                if (empty($items)) {
                    $rowIndex++;
                    $rows[] = [
                        $rowIndex,
                        $o['order_number'] ?? $o['id'] ?? '-',
                        $o['status'] ?? '-',
                        $o['ordered_at'] ?? '',
                        $repName,
                        $o['customer_type'] ?? '-',
                        $customerName,
                        $visit['visit_date'] ?? '',
                        $visit['doctor_confirmed_at'] ?? '',
                        $o['notes'] ?? '',
                        '',
                        '',
                        '',
                        '',
                        '',
                        '',
                        '',
                    ];
                    continue;
                }

                foreach ($items as $item) {
                    $rowIndex++;
                    $cp = $item['companyProduct'] ?? [];
                    $rows[] = [
                        $rowIndex,
                        $o['order_number'] ?? $o['id'] ?? '-',
                        $o['status'] ?? '-',
                        $o['ordered_at'] ?? '',
                        $repName,
                        $o['customer_type'] ?? '-',
                        $customerName,
                        $visit['visit_date'] ?? '',
                        $visit['doctor_confirmed_at'] ?? '',
                        $o['notes'] ?? '',
                        $cp['sku'] ?? '-',
                        $cp['name'] ?? ($cp['name_ar'] ?? '-'),
                        $cp['product_type'] ?? '-',
                        $item['quantity'] ?? 0,
                        $item['unit_price'] ?? 0,
                        $item['total_price'] ?? 0,
                        $cp['first_image_url'] ?? '',
                    ];
                }
            }
            $rows[] = [];
        }

        // Visits
        if (!empty($sections['visits'])) {
            $addSectionTitle('Visits');
            $visits = $sections['visits']['visits'] ?? [];
            $rows[] = ['#', 'Representative', 'Phone', 'Visit Date', 'Time', 'Purpose', 'Status', 'Doctor Confirmed At', 'Notes', 'Rejection Reason', 'Files Count'];
            foreach ($visits as $i => $v) {
                $rep = $v['representative']['user'] ?? [];
                $rows[] = [
                    $i + 1,
                    $rep['username'] ?? '-',
                    $rep['phone'] ?? '-',
                    $v['visit_date'] ?? '',
                    $v['visit_time'] ?? '',
                    $v['purpose'] ?? '-',
                    $v['status'] ?? '-',
                    $v['doctor_confirmed_at'] ?? '',
                    $v['notes'] ?? ($v['rejection_reason'] ?? ''),
                    $v['rejection_reason'] ?? '',
                    is_array($v['files'] ?? null) ? count($v['files']) : 0,
                ];
            }
            $rows[] = [];
        }

        // Representatives
        if (!empty($sections['representatives'])) {
            $addSectionTitle('Representatives');
            $reps = $sections['representatives']['representatives'] ?? [];
            $rows[] = ['#', 'Employee ID', 'User', 'Phone', 'Territory', 'Status'];
            foreach ($reps as $i => $r) {
                $user = $r['user'] ?? [];
                $rows[] = [
                    $i + 1,
                    $r['employee_id'] ?? '',
                    $user['username'] ?? ($user['name'] ?? '-'),
                    $user['phone'] ?? '-',
                    $r['territory'] ?? '-',
                    $r['status'] ?? '-',
                ];
            }
            $rows[] = [];
        }

        // Company Orders
        $companyOrders = $sections['companyOrders']['orders'] ?? null;
        if (is_array($companyOrders)) {
            $addSectionTitle('Company Orders');
            $rows[] = [
                '#',
                'Order #',
                'Status',
                'Ordered At',
                'Representative',
                'Customer Type',
                'Customer',
                'Visit Date',
                'Doctor Confirmed At',
                'Order Notes',
                'Item SKU',
                'Item Product',
                'Item Type',
                'Qty',
                'Unit Price',
                'Total Price',
                'Item Image',
            ];

            $rowIndex = 0;
            foreach ($companyOrders as $i => $o) {
                $items = is_array($o['items'] ?? null) ? ($o['items'] ?? []) : [];
                $customerName =
                    $o['customerShop']['name'] ?? ($o['customerDoctor']['name'] ?? ($o['customer_id'] ?? '-'));
                $repName = $o['representative']['user']['username'] ?? ($o['representative']['user']['name'] ?? '-');
                $visit = $o['visit'] ?? [];

                if (empty($items)) {
                    $rowIndex++;
                    $rows[] = [
                        $rowIndex,
                        $o['order_number'] ?? $o['id'] ?? '-',
                        $o['status'] ?? '-',
                        $o['ordered_at'] ?? '',
                        $repName,
                        $o['customer_type'] ?? '-',
                        $customerName,
                        $visit['visit_date'] ?? '',
                        $visit['doctor_confirmed_at'] ?? '',
                        $o['notes'] ?? '',
                        '',
                        '',
                        '',
                        '',
                        '',
                        '',
                        '',
                    ];
                    continue;
                }

                foreach ($items as $item) {
                    $rowIndex++;
                    $cp = $item['companyProduct'] ?? [];
                    $rows[] = [
                        $rowIndex,
                        $o['order_number'] ?? $o['id'] ?? '-',
                        $o['status'] ?? '-',
                        $o['ordered_at'] ?? '',
                        $repName,
                        $o['customer_type'] ?? '-',
                        $customerName,
                        $visit['visit_date'] ?? '',
                        $visit['doctor_confirmed_at'] ?? '',
                        $o['notes'] ?? '',
                        $cp['sku'] ?? '-',
                        $cp['name'] ?? ($cp['name_ar'] ?? '-'),
                        $cp['product_type'] ?? '-',
                        $item['quantity'] ?? 0,
                        $item['unit_price'] ?? 0,
                        $item['total_price'] ?? 0,
                        $cp['first_image_url'] ?? '',
                    ];
                }
            }
            $rows[] = [];
        }

        // Branches
        if (!empty($sections['branches'])) {
            $addSectionTitle('Branches');
            $branches = $sections['branches']['branches'] ?? [];
            $rows[] = ['#', 'Name', 'Name (AR)', 'Address', 'Phone', 'Sort', 'Active'];
            foreach ($branches as $i => $b) {
                $rows[] = [
                    $i + 1,
                    $b['name'] ?? '-',
                    $b['name_ar'] ?? '',
                    $b['address'] ?? '-',
                    $b['phone'] ?? '',
                    $b['sort_order'] ?? '',
                    isset($b['is_active']) ? ($b['is_active'] ? 'Yes' : 'No') : '',
                ];
            }
            $rows[] = [];
        }

        // Documents
        if (!empty($sections['documents'])) {
            $addSectionTitle('Documents');
            $docs = $sections['documents']['documents'] ?? [];
            $rows[] = ['#', 'Type', 'Title', 'Title (AR)', 'Reference #', 'Issue Date', 'Expires At', 'Verified', 'File URL', 'Notes'];
            foreach ($docs as $i => $d) {
                $rows[] = [
                    $i + 1,
                    $d['type'] ?? '-',
                    $d['title'] ?? '-',
                    $d['title_ar'] ?? '',
                    $d['reference_number'] ?? '',
                    $d['issue_date'] ?? '',
                    $d['expires_at'] ?? '',
                    isset($d['is_verified']) ? ($d['is_verified'] ? 'Yes' : 'No') : '',
                    $d['file_url'] ?? '',
                    $d['notes'] ?? '',
                ];
            }
            $rows[] = [];
        }

        // Fallback: if no sections were selected.
        if (count($rows) <= 6) {
            $rows[] = ['No sections selected or no data found.'];
        }

        return $rows;
    }
}

