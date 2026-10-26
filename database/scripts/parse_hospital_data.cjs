const XLSX = require('xlsx');
const fs = require('fs');
const path = require('path');

const file3 = 'C:/Users/Administrator/.gemini/antigravity/brain/889016a5-8de6-419c-adc6-f8b789b08341/.user_uploaded/media_1792981979366_ac15808c.xlsx';
const file1 = 'C:/Users/Administrator/.gemini/antigravity/brain/889016a5-8de6-419c-adc6-f8b789b08341/.user_uploaded/media_1792981979319_4c956248.xlsx';

const wb3 = XLSX.readFile(file3);
const wb1 = XLSX.readFile(file1);

const suppliersMap = new Map();
const productsMap = new Map();

function cleanConsignorName(name) {
  let c = (name || '').toString().trim();
  if (c === 'MHJ') return 'MHJ MEDICAL AND LABORATORY SUPPLY';
  if (c === 'BEROVAN') return 'BEROVAN MARKETING INC.';
  if (c === 'TANN') return 'TANN PHARMA AND MEDICAL SUPPLIES DISTRIBUTION';
  if (c === 'JOSMEF') return 'JOSMEF ENTERPRISES';
  if (c === 'NEGROS') return 'NEGROS MEDICAL TRADING';
  if (c === 'DOUBLE-YPHARMA' || c === 'DOUBLE Y PHARMA') return 'DOUBLE-Y PHARMA MEDICAL AND DENTAL SUPPLIES TRADING';
  if (c === 'AZ-MARKET' || c === 'A-Z MARKET') return 'A-Z MARKET ENTERPRISE DEVELOPERS INC.';
  if (c === 'GREEN MEDS' || c === 'GREEN MEDICS') return 'GREEN MEDICS PHARMA DISTRIBUTION';
  if (c === 'P.T CERNA') return 'P.T. CERNA CORPORATION';
  if (c.startsWith('CBROS')) return 'CBROS PHARMA AND MEDICAL SUPPLIES TRADING';
  return c;
}

function registerSupplier(consignor) {
  const clean = cleanConsignorName(consignor);
  if (clean && !suppliersMap.has(clean)) {
    const codeNum = suppliersMap.size + 1;
    suppliersMap.set(clean, {
      name: clean,
      code: 'SUP-HOSP-' + String(codeNum).padStart(3, '0'),
      payment_terms: 'Consignment',
      status: 'active'
    });
  }
  return clean;
}

function parseRegistry(rows, defaultCategory) {
  let start = false;
  for (let r of rows) {
    if (!r || r.length < 3) continue;
    if (r[0] === 'ItemCode') { start = true; continue; }
    if (!start) continue;
    const code = (r[0] || '').toString().trim();
    const name = (r[1] || '').toString().trim();
    let consignor = registerSupplier(r[2]);
    const cost = parseFloat(r[3]) || 0;
    const markup = parseFloat(r[4]) || 0;
    const sell = parseFloat(r[5]) || (cost + markup);

    if (code && name && !productsMap.has(code)) {
      productsMap.set(code, {
        code,
        name,
        consignor,
        cost,
        markup,
        sell,
        category: defaultCategory || (code.includes('HDU') ? 'Dialysis Supplies' : 'Consignment Medical Supplies')
      });
    }
  }
}

parseRegistry(XLSX.utils.sheet_to_json(wb1.Sheets['STOCK REGISTRY'], { header: 1 }), 'Dialysis Unit (HDU) Consignment');
parseRegistry(XLSX.utils.sheet_to_json(wb3.Sheets['STOCK REGISTRY'], { header: 1 }), 'Central Supply Room (CSR) Consignment');

// 2. Extract Deliveries
const deliveries = [];
const delRows = XLSX.utils.sheet_to_json(wb3.Sheets['STOCK DELIVERY'], { header: 1 });
let startDel = false;
for (let r of delRows) {
  if (!r || r.length < 5) continue;
  if (r[0] && r[0].toString().includes('SPR/CO')) { startDel = true; continue; }
  if (!startDel) continue;
  const ref = (r[0] || '').toString().trim();
  let dateRaw = r[1];
  const itemCode = (r[2] || '').toString().trim();
  const itemName = (r[3] || '').toString().trim();
  let consignor = registerSupplier(r[4]);
  const uom = (r[5] || 'piece').toString().trim();
  const brand = (r[6] || '').toString().trim();
  const qty = parseInt(r[7]) || 0;
  const cost = parseFloat(r[8]) || 0;
  const amount = parseFloat(r[9]) || (qty * cost);

  if (ref && itemCode && qty > 0) {
    let dateStr = '2025-06-01';
    if (typeof dateRaw === 'number') {
      const d = new Date(Math.round((dateRaw - 25569) * 86400 * 1000));
      dateStr = d.toISOString().split('T')[0];
    } else if (typeof dateRaw === 'string') {
      const parts = dateRaw.split(/[\/\-]/);
      if (parts.length === 3) {
        if (parts[0].length === 4) {
          dateStr = dateRaw;
        } else {
          const yr = parts[2].length === 2 ? '20' + parts[2] : parts[2];
          dateStr = yr + '-' + parts[0].padStart(2, '0') + '-' + parts[1].padStart(2, '0');
        }
      }
    }

    if (!productsMap.has(itemCode)) {
      productsMap.set(itemCode, {
        code: itemCode,
        name: itemName,
        consignor,
        cost,
        markup: cost * 0.3,
        sell: cost * 1.3,
        category: 'Consignment Medical Supplies'
      });
    }

    deliveries.push({ ref, date: dateStr, itemCode, itemName, consignor, uom, brand, qty, cost, amount });
  }
}

// 3. Extract Transactions & register any products appearing in transactions
const transactions = [];
function parseTransactions(sheet) {
  if (!sheet) return;
  const txRows = XLSX.utils.sheet_to_json(sheet, { header: 1 });
  let startTx = false;
  for (let r of txRows) {
    if (!r || r.length < 5) continue;
    if (r[0] === 'Date' && r[2] === 'ItemCode') { startTx = true; continue; }
    if (!startTx) continue;
    let dateRaw = r[0];
    const type = (r[1] || 'CHARGE').toString().trim().toUpperCase();
    const itemCode = (r[2] || '').toString().trim();
    const itemName = (r[3] || '').toString().trim();
    let consignor = registerSupplier(r[4]);
    const cost = parseFloat(r[5]) || 0;
    const markup = parseFloat(r[6]) || 0;
    const price = parseFloat(r[7]) || (cost + markup);
    const qty = parseInt(r[8]) || 0;
    const qtyReturned = parseInt(r[9]) || 0;
    const actualQty = parseInt(r[10]) || (qty - qtyReturned);
    const totalSales = parseFloat(r[11]) || (actualQty * price);
    const patient = (r[12] || '').toString().trim();
    const discount = parseFloat(r[13]) || 0;
    const orAmount = parseFloat(r[14]) || 0;
    const orNumber = (r[15] || '').toString().trim();
    const totalConsignorPrice = parseFloat(r[16]) || (actualQty * cost);
    const refCode = (r[17] || r[18] || '').toString().trim();

    if (itemCode && actualQty > 0 && patient) {
      if (!productsMap.has(itemCode)) {
        productsMap.set(itemCode, {
          code: itemCode,
          name: itemName,
          consignor,
          cost,
          markup,
          sell: price,
          category: 'CSR Consignment Supplies'
        });
      }

      let dateStr = '2024-11-01';
      if (typeof dateRaw === 'number') {
        const d = new Date(Math.round((dateRaw - 25569) * 86400 * 1000));
        dateStr = d.toISOString().split('T')[0];
      } else if (typeof dateRaw === 'string') {
        const parts = dateRaw.split(/[\/\-]/);
        if (parts.length === 3) {
          if (parts[0].length === 4) {
            dateStr = dateRaw;
          } else {
            const yr = parts[2].length === 2 ? '20' + parts[2] : parts[2];
            dateStr = yr + '-' + parts[0].padStart(2, '0') + '-' + parts[1].padStart(2, '0');
          }
        }
      }
      transactions.push({
        date: dateStr,
        type,
        itemCode,
        itemName,
        consignor,
        cost,
        markup,
        price,
        qty: actualQty,
        qtyReturned,
        totalSales,
        patient,
        discount,
        orAmount,
        orNumber,
        totalConsignorPrice,
        refCode
      });
    }
  }
}

parseTransactions(wb1.Sheets['DAILY TRANSACTIONS']);
parseTransactions(wb3.Sheets['DAILY TRANSACTIONS']);

const output = {
  suppliers: Array.from(suppliersMap.values()),
  products: Array.from(productsMap.values()),
  deliveries: deliveries,
  transactions: transactions
};

const outDir = path.join(__dirname, '../data');
if (!fs.existsSync(outDir)) fs.mkdirSync(outDir, { recursive: true });

fs.writeFileSync(path.join(outDir, 'rmph_master.json'), JSON.stringify(output, null, 2));
console.log('Successfully generated complete rmph_master.json!');
console.log('Suppliers:', output.suppliers.length);
console.log('Products:', output.products.length);
console.log('Deliveries:', output.deliveries.length);
console.log('Transactions:', output.transactions.length);
