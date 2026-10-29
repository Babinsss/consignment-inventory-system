<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Requisition &amp; Issue Slip - {{ $batch_id }}</title>
    <style>
        @page {
            size: 8.5in 11in portrait;
            margin: 0.35in;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 15px;
        }

        .no-print {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #1e293b;
            color: #fff;
            padding: 10px 15px;
            margin-bottom: 20px;
            border-radius: 8px;
        }

        .btn-print {
            background: #0284c7;
            color: #fff;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            font-size: 12px;
        }

        .btn-close {
            background: transparent;
            color: #cbd5e1;
            border: 1px solid #475569;
            padding: 6px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
        }

        .header {
            text-align: center;
            line-height: 1.3;
            margin-bottom: 12px;
        }

        .header h3 {
            margin: 0;
            font-size: 11px;
            font-weight: normal;
        }

        .header h2 {
            margin: 2px 0;
            font-size: 13px;
            font-weight: bold;
            letter-spacing: 0.5px;
        }

        .header h1 {
            margin: 6px 0 2px;
            font-size: 14px;
            font-weight: 900;
            text-decoration: underline;
            letter-spacing: 1px;
        }

        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .meta-table td {
            padding: 4px;
            vertical-align: top;
            font-size: 11px;
        }

        .box {
            border: 1px solid #000;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            border: 1.5px solid #000;
            margin-bottom: 12px;
        }

        .items-table th, .items-table td {
            border: 1px solid #000;
            padding: 5px 6px;
            font-size: 11px;
        }

        .items-table th {
            background: #f1f5f9;
            text-transform: uppercase;
            font-weight: bold;
            text-align: center;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-mono { font-family: monospace; }
        .font-bold { font-weight: bold; }

        .signatures-table {
            width: 100%;
            border-collapse: collapse;
            border: 1.5px solid #000;
            margin-top: 15px;
        }

        .signatures-table td {
            border: 1px solid #000;
            padding: 6px;
            vertical-align: top;
            font-size: 10px;
        }

        .sig-line {
            border-bottom: 1px solid #000;
            height: 35px;
            margin-bottom: 4px;
        }

        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
        }
    </style>
</head>
<body>

    <!-- Non-printable top action bar -->
    <div class="no-print">
        <div>
            <strong>Requisition and Issue Slip (RIS)</strong> &bull; Control No: {{ $batch_id }}
        </div>
        <div>
            <button type="button" class="btn-print" onclick="window.print()">Print RIS Form</button>
            <button type="button" class="btn-close" onclick="window.close()">Close</button>
        </div>
    </div>

    <!-- Official Hospital Header -->
    <div class="header">
        <h3>Republic of the Philippines</h3>
        <h3>PROVINCE OF CAPIZ</h3>
        <h2>ROXAS MEMORIAL PROVINCIAL HOSPITAL</h2>
        <p style="margin: 0; font-size: 10px;">Arnaldo Boulevard, Roxas City, Capiz 5800</p>
        <h1>REQUISITION AND ISSUE SLIP (RIS)</h1>
    </div>

    <!-- Metadata Section -->
    <table class="meta-table box">
        <tr>
            <td style="width: 60%; border-right: 1px solid #000;">
                <strong>Division / Section:</strong> CLINICAL SERVICES / CSR<br>
                <strong>Office / Requesting Ward:</strong> <span style="font-size: 12px; font-weight: bold;">{{ $department_name }}</span>
            </td>
            <td style="width: 40%;">
                <strong>RIS Control No.:</strong> <span style="font-size: 12px; font-family: monospace; font-weight: bold;">{{ $batch_id }}</span><br>
                <strong>Date Requested:</strong> {{ \Carbon\Carbon::parse($created_at)->format('F d, Y - h:i A') }}<br>
                <strong>Status:</strong> <span style="text-transform: uppercase; font-weight: bold;">{{ $status }}</span>
                @if($is_emergency)
                    <strong style="color: #b91c1c; display: block; margin-top: 2px;">*** URGENT EMERGENCY REQUEST ***</strong>
                @endif
            </td>
        </tr>
    </table>

    <!-- Purpose Box -->
    <div style="border: 1px solid #000; border-top: none; padding: 6px; margin-bottom: 10px; font-size: 11px;">
        <strong>Purpose / Clinical Justification:</strong> {{ $purpose ?: 'Standard ward inventory replenishment and patient care.' }}
    </div>

    <!-- Requisition Items Table -->
    <table class="items-table">
        <thead>
            <tr>
                <th colspan="4" style="background: #e2e8f0; border-bottom: 1.5px solid #000;">Requisition</th>
                <th colspan="2" style="background: #e2e8f0; border-bottom: 1.5px solid #000;">Issuance</th>
            </tr>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 15%;">Stock / Item Code</th>
                <th>Item Description / Specification</th>
                <th style="width: 12%;">Qty Req.</th>
                <th style="width: 12%;">Qty Issued</th>
                <th style="width: 15%;">Unit of Measure</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $idx => $it)
            <tr>
                <td class="text-center font-mono">{{ $idx + 1 }}</td>
                <td class="text-center font-mono">{{ $it->product?->sku ?: 'CSG-'.str_pad($it->product_id, 4, '0', STR_PAD_LEFT) }}</td>
                <td>
                    <strong>{{ $it->product?->name }}</strong>
                    @if($it->product?->category)
                        <span style="font-size: 9px; color: #475569; display: block;">Category: {{ $it->product->category->name }}</span>
                    @endif
                </td>
                <td class="text-center font-bold font-mono">{{ number_format($it->quantity) }}</td>
                <td class="text-center font-bold font-mono">{{ $it->quantity_issued > 0 ? number_format($it->quantity_issued) : ($it->status === 'Approved' ? number_format($it->quantity) : '&mdash;') }}</td>
                <td class="text-center">{{ $it->product?->unit_of_measure ?: 'pcs' }}</td>
            </tr>
            @endforeach

            {{-- Empty filler rows to make it a full official slip if few items --}}
            @for($i = $items->count(); $i < 6; $i++)
            <tr style="color: #cbd5e1;">
                <td class="text-center font-mono">&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
            @endfor
        </tbody>
        <tfoot>
            <tr style="font-weight: bold; background: #f8fafc;">
                <td colspan="3" class="text-right" style="text-transform: uppercase;">Total Items Requested:</td>
                <td class="text-center font-mono font-bold">{{ number_format($items->sum('quantity')) }}</td>
                <td class="text-center font-mono font-bold">{{ number_format($items->sum(fn($i) => $i->quantity_issued > 0 ? $i->quantity_issued : ($i->status === 'Approved' ? $i->quantity : 0))) }}</td>
                <td class="text-center">&mdash;</td>
            </tr>
        </tfoot>
    </table>

    <!-- Official Signatures Table (Requisitioner, Approver, Issuer, Receiver) -->
    <table class="signatures-table">
        <thead>
            <tr style="background: #f1f5f9; text-transform: uppercase; font-weight: bold; text-align: center;">
                <td style="width: 25%;">Requested By:</td>
                <td style="width: 25%;">Approved By:</td>
                <td style="width: 25%;">Issued By:</td>
                <td style="width: 25%;">Received By:</td>
            </tr>
        </thead>
        <tbody>
            <tr>
                <!-- Requested By (Who requested) -->
                <td>
                    <div class="sig-line"></div>
                    <div style="font-weight: bold; text-align: center; text-transform: uppercase;">{{ $requested_by }}</div>
                    <div style="text-align: center; color: #475569; font-size: 9px;">Requesting Personnel / Ward</div>
                    <div style="text-align: center; font-size: 9px; margin-top: 4px;">Date: {{ \Carbon\Carbon::parse($created_at)->format('m/d/Y') }}</div>
                </td>

                <!-- Approved By -->
                <td>
                    <div class="sig-line"></div>
                    <div style="font-weight: bold; text-align: center; text-transform: uppercase;">LADY ORTALEZ LUCES</div>
                    <div style="text-align: center; color: #475569; font-size: 9px;">Head, Supply Section / Officer</div>
                    <div style="text-align: center; font-size: 9px; margin-top: 4px;">Date: {{ $issued_at ? \Carbon\Carbon::parse($issued_at)->format('m/d/Y') : date('m/d/Y') }}</div>
                </td>

                <!-- Issued By -->
                <td>
                    <div class="sig-line"></div>
                    <div style="font-weight: bold; text-align: center; text-transform: uppercase;">{{ $issuer?->name ?? 'SUPPLY CUSTODIAN' }}</div>
                    <div style="text-align: center; color: #475569; font-size: 9px;">Custodial Officer / Pharmacist</div>
                    <div style="text-align: center; font-size: 9px; margin-top: 4px;">Date: {{ $issued_at ? \Carbon\Carbon::parse($issued_at)->format('m/d/Y') : '________' }}</div>
                </td>

                <!-- Received By -->
                <td>
                    <div class="sig-line"></div>
                    <div style="font-weight: bold; text-align: center; text-transform: uppercase;">&nbsp;</div>
                    <div style="text-align: center; color: #475569; font-size: 9px;">Signature over Printed Name</div>
                    <div style="text-align: center; font-size: 9px; margin-top: 4px;">Date: _______________</div>
                </td>
            </tr>
        </tbody>
    </table>

</body>
</html>
