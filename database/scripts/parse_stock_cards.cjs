const XLSX = require('xlsx');
const fs = require('fs');
const path = require('path');

const filePath = 'C:/Users/Administrator/.gemini/antigravity/brain/889016a5-8de6-419c-adc6-f8b789b08341/.user_uploaded/media_1792981979319_ca049d3a.xlsx';

if (!fs.existsSync(filePath)) {
  console.error('File not found:', filePath);
  process.exit(1);
}

const wb = XLSX.readFile(filePath);
console.log('Opened workbook with', wb.SheetNames.length, 'sheets.');

function cleanString(val) {
  if (val === null || val === undefined) return '';
  return val.toString().trim();
}

function parseNumber(val, defaultVal = 0) {
  if (val === null || val === undefined) return defaultVal;
  if (typeof val === 'number') return isNaN(val) ? defaultVal : val;
  const cleaned = val.toString().replace(/,/g, '').replace(/[^\d.-]/g, '');
  const n = parseFloat(cleaned);
  return isNaN(n) ? defaultVal : n;
}

function parseDate(rawVal, fallbackYear = '2025') {
  if (!rawVal) return null;
  if (typeof rawVal === 'number') {
    // Excel serial date
    const d = new Date(Math.round((rawVal - 25569) * 86400 * 1000));
    if (!isNaN(d.getTime())) {
      return d.toISOString().split('T')[0];
    }
  }

  let str = cleanString(rawVal).replace(/\\/g, '/').replace(/'/g, '/');
  if (/^\d{4}-\d{2}-\d{2}$/.test(str)) return str;

  const parts = str.split(/[\/\-]/);
  if (parts.length === 3) {
    if (parts[0].length === 4) {
      // YYYY-MM-DD
      return `${parts[0]}-${parts[1].padStart(2, '0')}-${parts[2].padStart(2, '0')}`;
    } else {
      // M/D/YYYY or MM/DD/YY
      let yr = parts[2];
      if (yr.length === 2) yr = '20' + yr;
      if (yr.length === 5 && yr.startsWith('22')) yr = yr.substring(1); // fix typos like 4/21/22026 -> 2026
      if (yr.length > 4) yr = yr.substring(0, 4);
      let m = parts[0].padStart(2, '0');
      let d = parts[1].padStart(2, '0');
      return `${yr}-${m}-${d}`;
    }
  }
  return null;
}

function cleanSupplierName(raw) {
  let s = cleanString(raw).toUpperCase();
  if (!s || s === 'CSR' || s === 'DIALYSIS' || s === 'OPTHA' || s === 'OPTHAL DEPT' || s === 'OPTHA DEPT' || s === 'LANOT DIALYSIS' || s === 'GUGMA DIALYSIS') {
    return null;
  }
  if (s.includes('MHJ')) return 'MHJ MEDICAL AND LABORATORY SUPPLY';
  if (s.includes('BEROVAN')) return 'BEROVAN MARKETING INC.';
  if (s.includes('TANN')) return 'TANN PHARMA AND MEDICAL SUPPLIES DISTRIBUTION';
  if (s.includes('PINNACLE') || s.includes('PINACLE')) return 'PINNACLE MEDICAL TRADING';
  if (s.includes('JOSMEF')) return 'JOSMEF ENTERPRISES';
  if (s.includes('NEGROS')) return 'NEGROS MEDICAL TRADING';
  if (s.includes('GREEN MED')) return 'GREEN MEDICS PHARMA DISTRIBUTION';
  if (s.includes('EURO-MED') || s.includes('EURO MED')) return 'EURO-MED LABORATORIES PHILIPPINES INC.';
  if (s.includes('CBROS')) return 'CBROS PHARMA AND MEDICAL SUPPLIES TRADING';
  if (s.includes('B-SHINE') || s.includes('BSHINE')) return 'B-SHINE PHARMA AND MEDICAL TRADING';
  if (s.includes('MDRX')) return 'MDRX HEALTHCARE ENTERPRISES';
  if (s.includes('AGUA VISION') || s.includes('AGUA VISIONS')) return 'AGUA VISIONS MEDICAL SUPPLIES';
  if (s.includes('P.T CERNA') || s.includes('PT CERNA') || s.includes('P.T. CERNA')) return 'P.T. CERNA CORPORATION';
  if (s.includes('AZ-MARKET') || s.includes('A-Z MARKET')) return 'A-Z MARKET ENTERPRISE DEVELOPERS INC.';
  if (s.includes('DOUBLE-Y') || s.includes('DOUBLE Y')) return 'DOUBLE-Y PHARMA MEDICAL AND DENTAL SUPPLIES TRADING';
  return cleanString(raw);
}

const suppliersMap = new Map();
function registerSupplier(name) {
  const clean = cleanSupplierName(name);
  if (!clean) return null;
  if (!suppliersMap.has(clean)) {
    suppliersMap.set(clean, {
      name: clean,
      code: 'SUP-SC-' + String(suppliersMap.size + 1).padStart(3, '0'),
      payment_terms: 'Consignment',
      status: 'active'
    });
  }
  return clean;
}

const products = [];
const deliveries = [];
const sales = [];

wb.SheetNames.forEach((sheetName, sheetIndex) => {
  const sheet = wb.Sheets[sheetName];
  const rows = XLSX.utils.sheet_to_json(sheet, { header: 1 });
  if (rows.length < 8) return;

  // 1. Description & Unit
  let description = '';
  let unit = 'PIECE';
  for (let r = 3; r <= 6; r++) {
    if (!rows[r]) continue;
    for (let c = 0; c < rows[r].length; c++) {
      const cellText = cleanString(rows[r][c]).toLowerCase();
      if (cellText === 'description:') {
        description = cleanString(rows[r][c + 1]);
      }
      if (cellText === 'unit:') {
        unit = cleanString(rows[r][c + 1]);
      }
    }
  }

  if (!description) {
    const parts = sheetName.split('-');
    description = parts[0].trim();
  }

  // Determine supplier from sheet title
  let sheetSupplier = '';
  const dashParts = sheetName.split('-');
  if (dashParts.length > 1) {
    sheetSupplier = cleanSupplierName(dashParts[dashParts.length - 1]);
  }

  // 2. Scan ledger entries (row 8 downwards)
  let latestUnitCost = 0;
  let finalBalanceQty = 0;
  const sheetDeliveries = [];
  const sheetIssuances = [];

  for (let i = 8; i < rows.length; i++) {
    const r = rows[i];
    if (!r || r.length < 3) continue;

    const dateStr = parseDate(r[0]);
    const ref = cleanString(r[1]);
    const party = cleanString(r[2]);
    const recQty = parseInt(parseNumber(r[3], 0));
    const recCost = parseNumber(r[4], 0);
    const issQty = parseInt(parseNumber(r[6], 0));
    const balQty = parseInt(parseNumber(r[9], 0));
    const balCost = parseNumber(r[10], 0);

    if (balCost > 0) latestUnitCost = balCost;
    if (recCost > 0) latestUnitCost = recCost;
    if (r[9] !== undefined && r[9] !== '') finalBalanceQty = balQty;

    // Is it a receipt?
    if (recQty > 0) {
      const sup = cleanSupplierName(party) || sheetSupplier || 'MHJ MEDICAL AND LABORATORY SUPPLY';
      registerSupplier(sup);
      sheetDeliveries.push({
        date: dateStr || '2025-06-01',
        supplier: sup,
        qty: recQty,
        cost: recCost > 0 ? recCost : latestUnitCost,
        ref: ref || `SPR-${sheetIndex + 1}-${i}`
      });
    }

    // Is it an issuance?
    if (issQty > 0) {
      const dept = party || 'Central Supply Room (CSR)';
      sheetIssuances.push({
        date: dateStr || '2025-07-01',
        department: dept,
        qty: issQty,
        cost: latestUnitCost,
        ref: ref || `REQ-${sheetIndex + 1}-${i}`
      });
    }
  }

  const primarySupplier = sheetSupplier || (sheetDeliveries[0] ? sheetDeliveries[0].supplier : 'MHJ MEDICAL AND LABORATORY SUPPLY');
  registerSupplier(primarySupplier);

  const sku = `HECSM-SC-${String(sheetIndex + 1).padStart(4, '0')}`;
  const cost = latestUnitCost > 0 ? latestUnitCost : (sheetDeliveries[0]?.cost || 100);
  const markup = Math.round(cost * 0.3 * 100) / 100;
  const sellingPrice = Math.round((cost + markup) * 100) / 100;

  products.push({
    sku,
    name: description,
    sheetName: sheetName.trim(),
    supplier: primarySupplier,
    unit: unit || 'PIECE',
    cost,
    markup,
    sellingPrice,
    initialStock: finalBalanceQty,
    deliveriesCount: sheetDeliveries.length,
    issuancesCount: sheetIssuances.length,
    deliveries: sheetDeliveries,
    issuances: sheetIssuances
  });
});

console.log('Successfully parsed products:', products.length);
console.log('Total suppliers registered:', suppliersMap.size);

const outDir = path.join(__dirname, '../data');
if (!fs.existsSync(outDir)) fs.mkdirSync(outDir, { recursive: true });

const outData = {
  suppliers: Array.from(suppliersMap.values()),
  products: products
};

fs.writeFileSync(path.join(outDir, 'rmph_stock_cards.json'), JSON.stringify(outData, null, 2), 'utf8');
console.log('Saved parsed data to database/data/rmph_stock_cards.json');
