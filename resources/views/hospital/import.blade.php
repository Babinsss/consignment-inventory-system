@extends('layouts.app')

@section('title', 'HECS Excel Data Importer - Roxas Memorial Provincial Hospital')

@section('content')
<div class="space-y-6" x-data="{
    importMode: 'auto',
    isDryRun: false,
    fileName: '',
    fileSize: '',
    sheetsDetected: [],
    loading: false,
    isDragging: false
}">
    <!-- Hospital Header Block -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center space-x-2 text-xs font-semibold text-purple-700 uppercase tracking-widest mb-1">
                    <span class="inline-block w-2 h-2 rounded-full bg-purple-600"></span>
                    <span>Republic of the Philippines • Capiz Provincial Government</span>
                </div>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">HOSPITAL EXTENSIVE CONSIGNMENT SYSTEM (HECS)</h1>
                <p class="text-sm font-medium text-slate-600">Roxas Memorial Provincial Hospital (RMPH) • Automated Excel Data Importer & Bulk Sync Hub</p>
            </div>
            <div class="flex items-center space-x-2">
                <a href="{{ route('hospital.stcg') }}" class="px-3.5 py-2 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition inline-flex items-center">
                    📋 StCG Statement
                </a>
                <a href="{{ route('hospital.sccg') }}" class="px-3.5 py-2 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition inline-flex items-center">
                    📑 ScCG Schedule
                </a>
                <a href="{{ route('hospital.stock-card') }}" class="px-3.5 py-2 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition inline-flex items-center">
                    🗃️ Gen. Form 18
                </a>
            </div>
        </div>

        <!-- Live Database Metrics Ribbon -->
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mt-6 pt-6 border-t border-slate-100">
            <div class="bg-purple-50/60 rounded-xl p-3 border border-purple-100/80">
                <div class="text-[11px] font-semibold text-purple-700 uppercase tracking-wide">Consignment Items</div>
                <div class="text-xl font-black text-purple-900 mt-0.5">{{ number_format($stats['total_products']) }}</div>
                <div class="text-[10px] text-purple-600 font-medium">In Master Registry</div>
            </div>

            <div class="bg-indigo-50/60 rounded-xl p-3 border border-indigo-100/80">
                <div class="text-[11px] font-semibold text-indigo-700 uppercase tracking-wide">Active Consignors</div>
                <div class="text-xl font-black text-indigo-900 mt-0.5">{{ number_format($stats['total_consignors']) }}</div>
                <div class="text-[10px] text-indigo-600 font-medium">MHJ, Berovan, Tann, etc.</div>
            </div>

            <div class="bg-sky-50/60 rounded-xl p-3 border border-sky-100/80">
                <div class="text-[11px] font-semibold text-sky-700 uppercase tracking-wide">Stock Deliveries</div>
                <div class="text-xl font-black text-sky-900 mt-0.5">{{ number_format($stats['total_deliveries']) }}</div>
                <div class="text-[10px] text-sky-600 font-medium">SPR / CO Inward Batches</div>
            </div>

            <div class="bg-emerald-50/60 rounded-xl p-3 border border-emerald-100/80">
                <div class="text-[11px] font-semibold text-emerald-700 uppercase tracking-wide">Active FEFO Batches</div>
                <div class="text-xl font-black text-emerald-900 mt-0.5">{{ number_format($stats['total_batches']) }}</div>
                <div class="text-[10px] text-emerald-600 font-medium">With Available Stock</div>
            </div>

            <div class="bg-amber-50/60 rounded-xl p-3 border border-amber-100/80 col-span-2 sm:col-span-1">
                <div class="text-[11px] font-semibold text-amber-700 uppercase tracking-wide">Daily Issuances</div>
                <div class="text-xl font-black text-amber-900 mt-0.5">{{ number_format($stats['total_sales']) }}</div>
                <div class="text-[10px] text-amber-600 font-medium">Patients & CSR Logged</div>
            </div>
        </div>
    </div>

    <!-- Import Summary / Results Card (If session contains summary) -->
    @if(session('import_summary'))
        @php $summary = session('import_summary'); @endphp
        <div class="bg-white rounded-2xl border {{ !empty($summary['dry_run']) ? 'border-amber-300 bg-amber-50/20' : 'border-emerald-300 bg-emerald-50/20' }} p-6 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-xl {{ !empty($summary['dry_run']) ? 'bg-amber-500' : 'bg-emerald-600' }} text-white flex items-center justify-center font-bold text-lg shadow-sm">
                        {{ !empty($summary['dry_run']) ? '🧪' : '✓' }}
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-900">
                            {{ !empty($summary['dry_run']) ? 'Dry Run Validation Results (No Database Changes Made)' : 'Database Import Completed Successfully' }}
                        </h2>
                        <p class="text-xs text-slate-600">Breakdown of parsed hospital records from the uploaded workbook</p>
                    </div>
                </div>
                <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ !empty($summary['dry_run']) ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' }}">
                    {{ !empty($summary['dry_run']) ? 'VALIDATION PASS' : 'INGESTION SUCCESS' }}
                </span>
            </div>

            @if(!empty($summary['dry_run']))
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 my-4">
                    <div class="bg-white rounded-xl p-3 border border-slate-200">
                        <div class="text-xs text-slate-500">Detected Consignors</div>
                        <div class="text-lg font-bold text-slate-900">{{ $summary['counts']['suppliers'] }}</div>
                    </div>
                    <div class="bg-white rounded-xl p-3 border border-slate-200">
                        <div class="text-xs text-slate-500">Detected Products</div>
                        <div class="text-lg font-bold text-slate-900">{{ $summary['counts']['products'] }}</div>
                    </div>
                    <div class="bg-white rounded-xl p-3 border border-slate-200">
                        <div class="text-xs text-slate-500">Detected Deliveries</div>
                        <div class="text-lg font-bold text-slate-900">{{ $summary['counts']['deliveries'] }}</div>
                    </div>
                    <div class="bg-white rounded-xl p-3 border border-slate-200">
                        <div class="text-xs text-slate-500">Daily Issuances</div>
                        <div class="text-lg font-bold text-slate-900">{{ $summary['counts']['transactions'] }}</div>
                    </div>
                </div>

                @if(!empty($summary['sample_products']))
                    <div class="mt-4">
                        <div class="text-xs font-bold text-slate-700 uppercase tracking-wide mb-2">Sample Parsed Products (First 5):</div>
                        <div class="overflow-x-auto border border-slate-200 rounded-xl bg-white">
                            <table class="min-w-full text-xs divide-y divide-slate-200">
                                <thead class="bg-slate-50 text-slate-700">
                                    <tr>
                                        <th class="px-3 py-2 text-left font-semibold">Item Code</th>
                                        <th class="px-3 py-2 text-left font-semibold">Description</th>
                                        <th class="px-3 py-2 text-left font-semibold">Consignor</th>
                                        <th class="px-3 py-2 text-right font-semibold">Cost (&#8369;)</th>
                                        <th class="px-3 py-2 text-right font-semibold">Markup (&#8369;)</th>
                                        <th class="px-3 py-2 text-right font-semibold">Selling Price (&#8369;)</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach($summary['sample_products'] as $sp)
                                        <tr>
                                            <td class="px-3 py-2 font-mono font-bold text-sky-700">{{ $sp['code'] }}</td>
                                            <td class="px-3 py-2 text-slate-800">{{ $sp['name'] }}</td>
                                            <td class="px-3 py-2 text-slate-600">{{ $sp['consignor'] }}</td>
                                            <td class="px-3 py-2 text-right font-mono">{{ number_format($sp['cost'], 2) }}</td>
                                            <td class="px-3 py-2 text-right font-mono text-emerald-600">+{{ number_format($sp['markup'], 2) }}</td>
                                            <td class="px-3 py-2 text-right font-mono font-bold">{{ number_format($sp['sell'], 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            @else
                <div class="grid grid-cols-2 sm:grid-cols-6 gap-3 my-4">
                    <div class="bg-white rounded-xl p-3 border border-slate-200">
                        <div class="text-xs text-slate-500">Consignors Added</div>
                        <div class="text-lg font-bold text-indigo-700">+{{ $summary['created_suppliers'] ?? 0 }}</div>
                    </div>
                    <div class="bg-white rounded-xl p-3 border border-slate-200">
                        <div class="text-xs text-slate-500">Products Created</div>
                        <div class="text-lg font-bold text-emerald-700">+{{ $summary['created_products'] ?? 0 }}</div>
                    </div>
                    <div class="bg-white rounded-xl p-3 border border-slate-200">
                        <div class="text-xs text-slate-500">Products Updated</div>
                        <div class="text-lg font-bold text-sky-700">{{ $summary['updated_products'] ?? 0 }}</div>
                    </div>
                    <div class="bg-white rounded-xl p-3 border border-slate-200">
                        <div class="text-xs text-slate-500">Deliveries Created</div>
                        <div class="text-lg font-bold text-purple-700">+{{ $summary['created_deliveries'] ?? 0 }}</div>
                    </div>
                    <div class="bg-white rounded-xl p-3 border border-slate-200">
                        <div class="text-xs text-slate-500">FEFO Batches Created</div>
                        <div class="text-lg font-bold text-teal-700">+{{ $summary['created_batches'] ?? 0 }}</div>
                    </div>
                    <div class="bg-white rounded-xl p-3 border border-slate-200">
                        <div class="text-xs text-slate-500">Daily Issuances Added</div>
                        <div class="text-lg font-bold text-amber-700">+{{ $summary['created_sales'] ?? 0 }}</div>
                    </div>
                </div>
            @endif
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Upload Form (2 Columns) -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs">
                <div class="flex items-center space-x-3 mb-6">
                    <div class="w-10 h-10 rounded-xl bg-purple-600 text-white flex items-center justify-center font-bold text-lg shadow-md shadow-purple-600/20">
                        📥
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">Upload Hospital Excel / CSV File</h2>
                        <p class="text-xs text-slate-500">Supports monthly consignment dumps (.xlsx, .xls, .csv up to 50MB)</p>
                    </div>
                </div>

                <form action="{{ route('hospital.import.process') }}" method="POST" enctype="multipart/form-data" @submit="loading = true">
                    @csrf

                    <!-- Import Mode Selector -->
                    <div class="mb-5">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-2">1. Select Import Mode</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                            <label class="flex items-start p-3 rounded-xl border cursor-pointer transition"
                                   :class="importMode === 'auto' ? 'border-purple-600 bg-purple-50/50 ring-1 ring-purple-600' : 'border-slate-200 hover:bg-slate-50'">
                                <input type="radio" name="import_type" value="auto" x-model="importMode" class="mt-0.5 text-purple-600 focus:ring-purple-500">
                                <div class="ml-2.5">
                                    <span class="block text-xs font-bold text-slate-900">🌟 Auto-Detect Master Workbook</span>
                                    <span class="block text-[11px] text-slate-500 mt-0.5">Scans all sheets for STOCK REGISTRY, STOCK DELIVERY, and DAILY TRANSACTIONS.</span>
                                </div>
                            </label>

                            <label class="flex items-start p-3 rounded-xl border cursor-pointer transition"
                                   :class="importMode === 'registry' ? 'border-purple-600 bg-purple-50/50 ring-1 ring-purple-600' : 'border-slate-200 hover:bg-slate-50'">
                                <input type="radio" name="import_type" value="registry" x-model="importMode" class="mt-0.5 text-purple-600 focus:ring-purple-500">
                                <div class="ml-2.5">
                                    <span class="block text-xs font-bold text-slate-900">📦 Stock Registry / Item Master</span>
                                    <span class="block text-[11px] text-slate-500 mt-0.5">Creates/updates medical supply items, consignor names, costs, markups, and prices.</span>
                                </div>
                            </label>

                            <label class="flex items-start p-3 rounded-xl border cursor-pointer transition"
                                   :class="importMode === 'deliveries' ? 'border-purple-600 bg-purple-50/50 ring-1 ring-purple-600' : 'border-slate-200 hover:bg-slate-50'">
                                <input type="radio" name="import_type" value="deliveries" x-model="importMode" class="mt-0.5 text-purple-600 focus:ring-purple-500">
                                <div class="ml-2.5">
                                    <span class="block text-xs font-bold text-slate-900">🚚 Stock Deliveries (SPR / CO)</span>
                                    <span class="block text-[11px] text-slate-500 mt-0.5">Inward delivery receipts with reference numbers, lot brands, quantities, and costs.</span>
                                </div>
                            </label>

                            <label class="flex items-start p-3 rounded-xl border cursor-pointer transition"
                                   :class="importMode === 'transactions' ? 'border-purple-600 bg-purple-50/50 ring-1 ring-purple-600' : 'border-slate-200 hover:bg-slate-50'">
                                <input type="radio" name="import_type" value="transactions" x-model="importMode" class="mt-0.5 text-purple-600 focus:ring-purple-500">
                                <div class="ml-2.5">
                                    <span class="block text-xs font-bold text-slate-900">📋 Daily Transactions / Issuance</span>
                                    <span class="block text-[11px] text-slate-500 mt-0.5">Patient dispensations, Charge vs Cash, OR numbers, charge slip references.</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- File Drop / Upload Box -->
                    <div class="mb-5">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-2">2. Choose Excel Spreadsheet or CSV File</label>
                        <div class="border-2 border-dashed rounded-2xl p-6 text-center transition"
                             :class="isDragging ? 'border-purple-600 bg-purple-50/70 ring-2 ring-purple-500/30' : 'border-slate-300 hover:border-purple-500 bg-slate-50/50'"
                             @dragover.prevent="isDragging = true"
                             @dragleave.prevent="isDragging = false"
                             @drop.prevent="
                                isDragging = false;
                                const f = $event.dataTransfer.files[0];
                                if(f) {
                                    $refs.fileInput.files = $event.dataTransfer.files;
                                    fileName = f.name;
                                    fileSize = (f.size / (1024 * 1024)).toFixed(2) + ' MB';
                                }
                             ">
                            <input type="file" name="excel_file" id="excel_file" required
                                   x-ref="fileInput"
                                   accept=".xlsx,.xls,.csv"
                                   @change="
                                       const f = $event.target.files[0];
                                       if(f) {
                                           fileName = f.name;
                                           fileSize = (f.size / (1024 * 1024)).toFixed(2) + ' MB';
                                       }
                                   "
                                   class="hidden">
                            
                            <label for="excel_file" class="cursor-pointer block">
                                <div class="w-12 h-12 rounded-full bg-purple-100 text-purple-600 mx-auto flex items-center justify-center mb-3">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                    </svg>
                                </div>
                                <div class="text-sm font-semibold text-slate-800">
                                    <span class="text-purple-700 hover:underline">Click to browse</span> or drag and drop your file here
                                </div>
                                <p class="text-xs text-slate-500 mt-1">Accepts Microsoft Excel (.xlsx, .xls) and Comma-Separated Values (.csv)</p>
                            </label>

                            <!-- File details preview -->
                            <div x-show="fileName" x-cloak class="mt-4 p-3 bg-white rounded-xl border border-slate-200 inline-flex items-center space-x-3 text-left">
                                <span class="text-2xl">📊</span>
                                <div>
                                    <div class="text-xs font-bold text-slate-900" x-text="fileName"></div>
                                    <div class="text-[10px] text-slate-500 font-medium" x-text="'File size: ' + fileSize"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Dry Run Toggle Option -->
                    <div class="mb-6 p-4 rounded-xl bg-slate-50 border border-slate-200 flex items-start space-x-3">
                        <input type="checkbox" name="dry_run" id="dry_run" value="1" x-model="isDryRun" class="mt-0.5 rounded text-purple-600 focus:ring-purple-500">
                        <label for="dry_run" class="cursor-pointer">
                            <span class="block text-xs font-bold text-slate-900">🧪 Enable Dry Run / Validation Mode</span>
                            <span class="block text-[11px] text-slate-500 mt-0.5">Parse, inspect, and validate the spreadsheet without creating or altering any database records. Highly recommended before performing full monthly imports.</span>
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                        <div class="text-xs text-slate-500">
                            Roxas Memorial Provincial Hospital • Verified Capiz HECS Format
                        </div>
                        <button type="submit"
                                :disabled="loading"
                                class="inline-flex items-center px-6 py-2.5 rounded-xl font-bold text-xs text-white transition shadow-sm"
                                :class="isDryRun ? 'bg-amber-600 hover:bg-amber-700 shadow-amber-600/20' : 'bg-purple-600 hover:bg-purple-700 shadow-purple-600/20'">
                            <svg x-show="loading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span x-text="loading ? 'Processing Spreadsheet...' : (isDryRun ? '🧪 Validate Spreadsheet (Dry Run)' : '🚀 Execute Database Import')"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Download Starter Templates & Guidelines (1 Column) -->
        <div class="space-y-6">
            <!-- Official Templates Download Card -->
            <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs">
                <div class="flex items-center space-x-2.5 mb-4">
                    <span class="text-xl">📥</span>
                    <h3 class="text-sm font-bold text-slate-900">Download Official HECS Templates</h3>
                </div>
                <p class="text-xs text-slate-500 mb-4">Pre-formatted templates formatted according to Capiz Provincial Government standards:</p>

                <div class="space-y-2.5">
                    <a href="{{ route('hospital.template.download', 'registry') }}" class="p-3 rounded-xl border border-slate-200 hover:border-purple-300 hover:bg-purple-50/50 flex items-center justify-between text-xs transition group">
                        <div class="flex items-center space-x-2.5">
                            <span class="text-base text-purple-600">📄</span>
                            <div>
                                <span class="font-bold text-slate-800 group-hover:text-purple-700 block">Stock Registry Template</span>
                                <span class="text-[10px] text-slate-500">Items, Consignors, Markup & Prices</span>
                            </div>
                        </div>
                        <span class="text-[10px] font-bold text-purple-700 bg-purple-100 px-2 py-0.5 rounded-lg">CSV</span>
                    </a>

                    <a href="{{ route('hospital.template.download', 'deliveries') }}" class="p-3 rounded-xl border border-slate-200 hover:border-purple-300 hover:bg-purple-50/50 flex items-center justify-between text-xs transition group">
                        <div class="flex items-center space-x-2.5">
                            <span class="text-base text-purple-600">🚚</span>
                            <div>
                                <span class="font-bold text-slate-800 group-hover:text-purple-700 block">Stock Deliveries Template</span>
                                <span class="text-[10px] text-slate-500">SPR/CO No., Batches, Inward Receipts</span>
                            </div>
                        </div>
                        <span class="text-[10px] font-bold text-purple-700 bg-purple-100 px-2 py-0.5 rounded-lg">CSV</span>
                    </a>

                    <a href="{{ route('hospital.template.download', 'transactions') }}" class="p-3 rounded-xl border border-slate-200 hover:border-purple-300 hover:bg-purple-50/50 flex items-center justify-between text-xs transition group">
                        <div class="flex items-center space-x-2.5">
                            <span class="text-base text-purple-600">📑</span>
                            <div>
                                <span class="font-bold text-slate-800 group-hover:text-purple-700 block">Daily Transactions Template</span>
                                <span class="text-[10px] text-slate-500">Patient Issuance, Cash/Charge, OR #</span>
                            </div>
                        </div>
                        <span class="text-[10px] font-bold text-purple-700 bg-purple-100 px-2 py-0.5 rounded-lg">CSV</span>
                    </a>
                </div>
            </div>

            <!-- Guidelines & Column Reference Card -->
            <div class="bg-gradient-to-br from-slate-900 to-slate-800 text-white rounded-2xl p-6 shadow-xs">
                <div class="flex items-center space-x-2 mb-3">
                    <span class="text-lg">💡</span>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-purple-300">Staff Ingestion Tips</h3>
                </div>
                <ul class="text-xs space-y-2 text-slate-300">
                    <li class="flex items-start space-x-1.5">
                        <span class="text-purple-400 font-bold">•</span>
                        <span><strong>Master Workbooks:</strong> Multi-sheet files with sheets named <code class="text-purple-200 bg-slate-700 px-1 rounded">STOCK REGISTRY</code>, <code class="text-purple-200 bg-slate-700 px-1 rounded">STOCK DELIVERY</code>, and <code class="text-purple-200 bg-slate-700 px-1 rounded">DAILY TRANSACTIONS</code> are auto-parsed simultaneously.</span>
                    </li>
                    <li class="flex items-start space-x-1.5">
                        <span class="text-purple-400 font-bold">•</span>
                        <span><strong>FEFO Allocation:</strong> Inward deliveries automatically create tracked batches with expiry dates calculated 24 months forward or per specified lot date.</span>
                    </li>
                    <li class="flex items-start space-x-1.5">
                        <span class="text-purple-400 font-bold">•</span>
                        <span><strong>Markup & Settlement:</strong> Hospital retains markup while supplier is credited with consignor unit cost times quantity consumed.</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Column Mapping Reference Table -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-xs">
        <h3 class="text-sm font-bold text-slate-900 mb-1">Roxas Memorial Provincial Hospital Excel Header Mapping Standard</h3>
        <p class="text-xs text-slate-500 mb-4">Official header definitions recognized across all Dialysis (HDU) and Central Supply Room (CSR) spreadsheets:</p>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
            <div class="border border-slate-200 rounded-xl p-4 bg-slate-50/50">
                <div class="font-bold text-purple-800 mb-2 flex items-center justify-between">
                    <span>STOCK REGISTRY</span>
                    <span class="text-[10px] bg-purple-100 text-purple-700 px-1.5 py-0.5 rounded">6 Columns</span>
                </div>
                <div class="space-y-1 font-mono text-[11px] text-slate-600">
                    <div>1. <span class="text-slate-900 font-bold">ItemCode</span> (e.g. HECSM-RMH-CU01)</div>
                    <div>2. <span class="text-slate-900 font-bold">Item Name</span></div>
                    <div>3. <span class="text-slate-900 font-bold">Name of Consignor</span> (e.g. MHJ)</div>
                    <div>4. <span class="text-slate-900 font-bold">CONSIGNOR'S UNIT PRICE</span></div>
                    <div>5. <span class="text-slate-900 font-bold">MARK UP</span></div>
                    <div>6. <span class="text-slate-900 font-bold">SELLING PRICE/UNIT</span></div>
                </div>
            </div>

            <div class="border border-slate-200 rounded-xl p-4 bg-slate-50/50">
                <div class="font-bold text-sky-800 mb-2 flex items-center justify-between">
                    <span>STOCK DELIVERY</span>
                    <span class="text-[10px] bg-sky-100 text-sky-700 px-1.5 py-0.5 rounded">10 Columns</span>
                </div>
                <div class="space-y-1 font-mono text-[11px] text-slate-600">
                    <div>1. <span class="text-slate-900 font-bold">SPR/CO No:</span> (e.g. CR-01-001)</div>
                    <div>2. <span class="text-slate-900 font-bold">Date of Delivery</span></div>
                    <div>3. <span class="text-slate-900 font-bold">ItemCode</span></div>
                    <div>4. <span class="text-slate-900 font-bold">Item Name and Description</span></div>
                    <div>5. <span class="text-slate-900 font-bold">Name of Consignor</span></div>
                    <div>6. <span class="text-slate-900 font-bold">Unit of Issue</span> / <span class="text-slate-900 font-bold">Brand</span></div>
                    <div>7. <span class="text-slate-900 font-bold">Qty Delivered</span> / <span class="text-slate-900 font-bold">Price</span></div>
                </div>
            </div>

            <div class="border border-slate-200 rounded-xl p-4 bg-slate-50/50">
                <div class="font-bold text-amber-800 mb-2 flex items-center justify-between">
                    <span>DAILY TRANSACTIONS</span>
                    <span class="text-[10px] bg-amber-100 text-amber-700 px-1.5 py-0.5 rounded">17 Columns</span>
                </div>
                <div class="space-y-1 font-mono text-[11px] text-slate-600">
                    <div>1. <span class="text-slate-900 font-bold">Date</span> & <span class="text-slate-900 font-bold">Type</span> (CHARGE / CASH)</div>
                    <div>2. <span class="text-slate-900 font-bold">ItemCode</span> & <span class="text-slate-900 font-bold">Item Name</span></div>
                    <div>3. <span class="text-slate-900 font-bold">Consignor's Price</span> & <span class="text-slate-900 font-bold">Markup</span></div>
                    <div>4. <span class="text-slate-900 font-bold">Quantity Issued</span> & <span class="text-slate-900 font-bold">Returned</span></div>
                    <div>5. <span class="text-slate-900 font-bold">Issued to</span> (Patient Name)</div>
                    <div>6. <span class="text-slate-900 font-bold">OR No.</span> / <span class="text-slate-900 font-bold">Charge Slip Ref</span></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
