const XLSX = require('xlsx');
const fs = require('fs');
const path = require('path');

// CLI Arguments
const args = process.argv.slice(2);
let filePath = null;
let importType = 'auto'; // 'auto', 'registry', 'deliveries', 'transactions'
let outputPath = null;

for (let i = 0; i < args.length; i++) {
  if (args[i] === '--file' && args[i + 1]) {
    filePath = args[i + 1];
    i++;
  } else if (args[i] === '--type' && args[i + 1]) {
    importType = args[i + 1];
    i++;
  } else if (args[i] === '--output' && args[i + 1]) {
    outputPath = args[i + 1];
    i++;
  }
}

if (!filePath || !fs.existsSync(filePath)) {
  const err = { success: false, error: 'File path is required and must exist: ' + filePath };
  console.error(JSON.stringify(err));
  process.exit(1);
}

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

function parseDate(rawVal, fallback = null) {
  if (!rawVal) return fallback || new Date().toISOString().split('T')[0];
  if (typeof rawVal === 'number') {
    // Excel serial date number
    const d = new Date(Math.round((rawVal - 25569) * 86400 * 1000));
    if (!isNaN(d.getTime())) {
      return d.toISOString().split('T')[0];
    }
  }
  const str = cleanString(rawVal);
  if (/^\d{4}-\d{2}-\d{2}$/.test(str)) return str;

  const parts = str.split(/[\/\-]/);
  if (parts.length === 3) {
    if (parts[0].length === 4) {
      return `${parts[0]}-${parts[1].padStart(2, '0')}-${parts[2].padStart(2, '0')}`;
    } else {
      let yr = parts[2];
      if (yr.length === 2) yr = '20' + yr;
      let m = parts[0].padStart(2, '0');
      let d = parts[1].padStart(2, '0');
      return `${yr}-${m}-${d}`;
    }
  }
  return fallback || new Date().toISOString().split('T')[0];
}

function cleanConsignorName(name) {
  let c = cleanString(name).toUpperCase();
  if (!c || c === 'NAME OF CONSIGNOR' || c === 'CONSIGNOR') return 'RMPH CONSIGNOR';
  if (c === 'MHJ') return 'MHJ MEDICAL AND LABORATORY SUPPLY';
  if (c === 'BEROVAN') return 'BEROVAN MARKETING INC.';
  if (c === 'TANN') return 'TANN PHARMA AND MEDICAL SUPPLIES DISTRIBUTION';
  if (c === 'JOSMEF') return 'JOSMEF ENTERPRISES';
  if (c === 'NEGROS') return 'NEGROS MEDICAL TRADING';
  if (c.includes('DOUBLE-Y') || c.includes('DOUBLE Y')) return 'DOUBLE-Y PHARMA MEDICAL AND DENTAL SUPPLIES TRADING';
  if (c.includes('A-Z') || c.includes('AZ-MARKET')) return 'A-Z MARKET ENTERPRISE DEVELOPERS INC.';
  if (c.includes('GREEN MED')) return 'GREEN MEDICS PHARMA DISTRIBUTION';
  if (c.includes('P.T CERNA') || c.includes('PT CERNA')) return 'P.T. CERNA CORPORATION';
  if (c.includes('CBROS')) return 'CBROS PHARMA AND MEDICAL SUPPLIES TRADING';
  return cleanString(name);
}

try {
  const workbook = XLSX.readFile(filePath, { cellDates: false });
  const sheetNames = workbook.SheetNames;

  const suppliersMap = new Map();
  const productsMap = new Map();
  const deliveriesList = [];
  const transactionsList = [];
  const warnings = [];

  function registerSupplier(consignor) {
    const clean = cleanConsignorName(consignor);
    if (clean && !suppliersMap.has(clean)) {
      suppliersMap.set(clean, {
        name: clean,
        code: 'SUP-IMP-' + String(suppliersMap.size + 1).padStart(3, '0'),
        payment_terms: 'Consignment',
        status: 'active'
      });
    }
    return clean;
  }

  // 1. Stock Registry parser
  function parseRegistrySheet(sheet, categoryName = 'Consignment Medical Supplies') {
    if (!sheet) return;
    const rows = XLSX.utils.sheet_to_json(sheet, { header: 1 });
    let headerIdx = -1;

    for (let i = 0; i < Math.min(25, rows.length); i++) {
      const r = rows[i] || [];
      const col0 = cleanString(r[0]).toLowerCase();
      if (col0 === 'itemcode' || col0 === 'item code' || col0 === 'code' || col0 === 'sku') {
        headerIdx = i;
        break;
      }
    }

    if (headerIdx === -1) {
      // Fallback search
      for (let i = 0; i < Math.min(25, rows.length); i++) {
        const r = rows[i] || [];
        const rowStr = r.map(x => cleanString(x).toLowerCase()).join('|');
        if (rowStr.includes('itemcode') || (rowStr.includes('code') && rowStr.includes('price'))) {
          headerIdx = i;
          break;
        }
      }
    }

    const header = headerIdx >= 0 ? rows[headerIdx] : [];
    let colMap = { code: 0, name: 1, consignor: 2, cost: 3, markup: 4, sell: 5, unit: -1 };

    header.forEach((col, idx) => {
      const c = cleanString(col).toLowerCase();
      if (c.includes('cost') || (c.includes('consignor') && c.includes('price')) || c.includes('unit price')) colMap.cost = idx;
      else if (c.includes('mark up') || c.includes('markup')) colMap.markup = idx;
      else if (c.includes('selling') || c.includes('retail')) colMap.sell = idx;
      else if (c === 'itemcode' || c === 'item code' || c === 'code' || c === 'sku') colMap.code = idx;
      else if (c.includes('item name') || c.includes('description')) colMap.name = idx;
      else if (c.includes('consignor') || c.includes('supplier')) colMap.consignor = idx;
      else if (c.includes('unit') || c.includes('uom')) colMap.unit = idx;
    });

    const startRow = headerIdx >= 0 ? headerIdx + 1 : 0;
    for (let i = startRow; i < rows.length; i++) {
      const r = rows[i];
      if (!r || r.length < 2) continue;
      const code = cleanString(r[colMap.code]);
      const name = cleanString(r[colMap.name]);
      if (!code && !name) continue;
      if (code.toLowerCase() === 'itemcode' || code.toLowerCase() === 'item code') continue;

      const consignor = registerSupplier(r[colMap.consignor] || 'RMPH CONSIGNOR');
      const cost = parseNumber(r[colMap.cost], 0);
      const markup = parseNumber(r[colMap.markup], cost * 0.3);
      const sell = parseNumber(r[colMap.sell], cost + markup);
      const unit = colMap.unit >= 0 ? cleanString(r[colMap.unit]) || 'piece' : 'piece';

      if (!productsMap.has(code)) {
        productsMap.set(code, {
          code: code || `GEN-${Date.now()}-${i}`,
          name: name || `Consignment Item ${code}`,
          consignor,
          cost,
          markup,
          sell,
          unit,
          category: categoryName
        });
      }
    }
  }

  // 2. Deliveries parser
  function parseDeliveriesSheet(sheet) {
    if (!sheet) return;
    const rows = XLSX.utils.sheet_to_json(sheet, { header: 1 });
    let headerIdx = -1;

    for (let i = 0; i < Math.min(25, rows.length); i++) {
      const r = rows[i] || [];
      const col0 = cleanString(r[0]).toLowerCase();
      if (col0.includes('spr/co') || col0.includes('delivery') || col0.includes('reference') || col0.includes('dr no')) {
        headerIdx = i;
        break;
      }
    }

    let colMap = { ref: 0, date: 1, code: 2, name: 3, consignor: 4, unit: 5, brand: 6, qty: 7, cost: 8, amount: 9 };
    const header = headerIdx >= 0 ? rows[headerIdx] : [];

    header.forEach((col, idx) => {
      const c = cleanString(col).toLowerCase();
      if (c.includes('date')) colMap.date = idx;
      else if (c.includes('spr') || c.includes('co no') || c.includes('spr/co') || c.includes('dr no') || c.includes('delivery no') || c.includes('reference') || c === 'co') colMap.ref = idx;
      else if (c.includes('consignor') && (c.includes('price') || c.includes('cost')) || c === 'cost' || c.includes('unit price')) colMap.cost = idx;
      else if (c.includes('amount') || c.includes('total')) colMap.amount = idx;
      else if (c.includes('qty') || c.includes('quantity')) colMap.qty = idx;
      else if (c.includes('brand')) colMap.brand = idx;
      else if (c.includes('unit') || c.includes('uom') || c.includes('issue')) colMap.unit = idx;
      else if (c === 'itemcode' || c === 'item code' || c === 'code') colMap.code = idx;
      else if (c.includes('item name') || c.includes('desc')) colMap.name = idx;
      else if (c.includes('consignor') || c.includes('supplier')) colMap.consignor = idx;
    });

    const startRow = headerIdx >= 0 ? headerIdx + 1 : 0;
    for (let i = startRow; i < rows.length; i++) {
      const r = rows[i];
      if (!r || r.length < 4) continue;
      const ref = cleanString(r[colMap.ref]);
      const code = cleanString(r[colMap.code]);
      const name = cleanString(r[colMap.name]);
      const qty = parseInt(parseNumber(r[colMap.qty], 0));

      if (!ref && !code) continue;
      if (ref.toLowerCase().includes('spr/co') || code.toLowerCase().includes('itemcode')) continue;
      if (qty <= 0) continue;

      const consignor = registerSupplier(r[colMap.consignor] || 'RMPH CONSIGNOR');
      const date = parseDate(r[colMap.date]);
      const cost = parseNumber(r[colMap.cost], 0);
      const amount = parseNumber(r[colMap.amount], qty * cost);
      const brand = cleanString(r[colMap.brand]);
      const unit = cleanString(r[colMap.unit]) || 'piece';

      if (!productsMap.has(code)) {
        productsMap.set(code, {
          code,
          name: name || code,
          consignor,
          cost,
          markup: cost * 0.3,
          sell: cost * 1.3,
          unit,
          category: 'Consignment Medical Supplies'
        });
      }

      deliveriesList.push({
        ref: ref || `DEL-${date}-${i}`,
        date,
        itemCode: code,
        itemName: name || code,
        consignor,
        unit,
        brand,
        qty,
        cost,
        amount
      });
    }
  }

  // 3. Transactions parser
  function parseTransactionsSheet(sheet) {
    if (!sheet) return;
    const rows = XLSX.utils.sheet_to_json(sheet, { header: 1 });
    let headerIdx = -1;

    for (let i = 0; i < Math.min(25, rows.length); i++) {
      const r = rows[i] || [];
      const col0 = cleanString(r[0]).toLowerCase();
      const col2 = cleanString(r[2]).toLowerCase();
      if (col0 === 'date' && (col2.includes('code') || col2.includes('item'))) {
        headerIdx = i;
        break;
      }
    }

    if (headerIdx === -1) {
      for (let i = 0; i < Math.min(25, rows.length); i++) {
        const r = rows[i] || [];
        const rowStr = r.map(x => cleanString(x).toLowerCase()).join('|');
        if (rowStr.includes('quantity') && (rowStr.includes('patient') || rowStr.includes('issued') || rowStr.includes('charge'))) {
          headerIdx = i;
          break;
        }
      }
    }

    let colMap = {
      date: 0,
      type: 1,
      code: 2,
      name: 3,
      consignor: 4,
      cost: 5,
      markup: 6,
      price: 7,
      qty: 8,
      qtyReturned: 9,
      actualQty: 10,
      totalSales: 11,
      patient: 12,
      discount: 13,
      orAmount: 14,
      orNumber: 15,
      totalCost: 16,
      refCode: 17
    };

    const header = headerIdx >= 0 ? rows[headerIdx] : [];
    header.forEach((col, idx) => {
      const c = cleanString(col).toLowerCase();
      if (c === 'date') colMap.date = idx;
      else if (c === 'type') colMap.type = idx;
      else if (c.includes("total consignor's price") || c.includes('total cost') || c.includes('total purchase')) colMap.totalCost = idx;
      else if (c.includes('total sales') || c.includes('total selling') || c.includes('total amount')) colMap.totalSales = idx;
      else if (c.includes('actual') && c.includes('qty')) colMap.actualQty = idx;
      else if (c.includes('returned')) colMap.qtyReturned = idx;
      else if (c.includes('quantity issued') || c === 'qty' || c === 'quantity') colMap.qty = idx;
      else if (c.includes('selling price')) colMap.price = idx;
      else if (c.includes('mark-up') || c.includes('markup')) colMap.markup = idx;
      else if (c.includes('consignor') && (c.includes('price') || c.includes('cost')) || c.includes('unit price')) colMap.cost = idx;
      else if (c === 'itemcode' || c === 'item code' || c === 'code') colMap.code = idx;
      else if (c.includes('item name') || c.includes('desc')) colMap.name = idx;
      else if (c.includes('consignor') || c.includes('supplier')) colMap.consignor = idx;
      else if (c.includes('issued to') || c.includes('patient') || c.includes('client')) colMap.patient = idx;
      else if (c.includes('discount')) colMap.discount = idx;
      else if (c.includes('receipt amount') || c.includes('or amount')) colMap.orAmount = idx;
      else if (c.includes('or no') || c.includes('or number') || c.includes('or #')) colMap.orNumber = idx;
      else if (c.includes('ref') || c.includes('slip') || c.includes('trans')) colMap.refCode = idx;
    });

    const startRow = headerIdx >= 0 ? headerIdx + 1 : 0;
    for (let i = startRow; i < rows.length; i++) {
      const r = rows[i];
      if (!r || r.length < 3) continue;
      const code = cleanString(r[colMap.code]);
      const name = cleanString(r[colMap.name]);
      const patient = cleanString(r[colMap.patient]) || 'Outpatient / Ward Dispense';
      let qty = parseInt(parseNumber(r[colMap.actualQty] !== undefined && r[colMap.actualQty] !== '' ? r[colMap.actualQty] : r[colMap.qty], 0));
      const qtyReturned = parseInt(parseNumber(r[colMap.qtyReturned], 0));

      if (!code && !name) continue;
      if (code.toLowerCase() === 'itemcode' || code.toLowerCase() === 'item code') continue;
      if (qty <= 0 && qtyReturned <= 0) continue;

      const date = parseDate(r[colMap.date]);
      const type = cleanString(r[colMap.type]).toUpperCase() || 'CHARGE';
      const consignor = registerSupplier(r[colMap.consignor] || 'RMPH CONSIGNOR');
      const cost = parseNumber(r[colMap.cost], 0);
      const markup = parseNumber(r[colMap.markup], cost * 0.3);
      const price = parseNumber(r[colMap.price], cost + markup);
      const totalSales = parseNumber(r[colMap.totalSales], qty * price);
      const totalCost = parseNumber(r[colMap.totalCost], qty * cost);
      const discount = parseNumber(r[colMap.discount], 0);
      const orNumber = cleanString(r[colMap.orNumber]);
      const refCode = cleanString(r[colMap.refCode]) || `CSG-${date}`;

      if (!productsMap.has(code)) {
        productsMap.set(code, {
          code,
          name: name || code,
          consignor,
          cost,
          markup,
          sell: price,
          unit: 'piece',
          category: 'CSR Consignment Supplies'
        });
      }

      transactionsList.push({
        date,
        type,
        itemCode: code,
        itemName: name || code,
        consignor,
        cost,
        markup,
        price,
        qty: Math.max(1, qty),
        qtyReturned,
        totalSales,
        totalConsignorPrice: totalCost,
        patient,
        discount,
        orNumber,
        refCode
      });
    }
  }

  // Route sheets
  if (importType === 'registry') {
    parseRegistrySheet(workbook.Sheets[sheetNames[0]]);
  } else if (importType === 'deliveries') {
    parseDeliveriesSheet(workbook.Sheets[sheetNames[0]]);
  } else if (importType === 'transactions') {
    parseTransactionsSheet(workbook.Sheets[sheetNames[0]]);
  } else {
    // Auto-detect
    sheetNames.forEach(sheetName => {
      const upper = sheetName.toUpperCase();
      if (upper === 'STOCK REGISTRY' || upper.includes('REGISTR')) {
        parseRegistrySheet(workbook.Sheets[sheetName], sheetName.includes('HDU') ? 'Dialysis Supplies' : 'Consignment Medical Supplies');
      }
      if (upper === 'STOCK DELIVERY' || upper.includes('DELIV')) {
        parseDeliveriesSheet(workbook.Sheets[sheetName]);
      }
      if (upper === 'DAILY TRANSACTIONS' || (upper.includes('DAILY') && upper.includes('TRANS'))) {
        parseTransactionsSheet(workbook.Sheets[sheetName]);
      }
    });

    // If none matched exact names:
    if (productsMap.size === 0 && deliveriesList.length === 0 && transactionsList.length === 0 && sheetNames.length > 0) {
      const firstSheet = workbook.Sheets[sheetNames[0]];
      const sampleRows = XLSX.utils.sheet_to_json(firstSheet, { header: 1 }).slice(0, 10);
      const sampleText = JSON.stringify(sampleRows).toLowerCase();

      if (sampleText.includes('spr') || sampleText.includes('delivery')) {
        parseDeliveriesSheet(firstSheet);
      } else if (sampleText.includes('patient') || sampleText.includes('issued to') || sampleText.includes('charge')) {
        parseTransactionsSheet(firstSheet);
      } else {
        parseRegistrySheet(firstSheet);
      }
    }
  }

  const result = {
    success: true,
    data: {
      suppliers: Array.from(suppliersMap.values()),
      products: Array.from(productsMap.values()),
      deliveries: deliveriesList,
      transactions: transactionsList
    },
    counts: {
      suppliers: suppliersMap.size,
      products: productsMap.size,
      deliveries: deliveriesList.length,
      transactions: transactionsList.length
    },
    warnings
  };

  const jsonStr = JSON.stringify(result, null, 2);

  if (outputPath) {
    fs.writeFileSync(outputPath, jsonStr, 'utf8');
  } else {
    console.log(jsonStr);
  }

} catch (err) {
  const errResult = {
    success: false,
    error: err.message,
    stack: err.stack
  };
  if (outputPath) {
    fs.writeFileSync(outputPath, JSON.stringify(errResult), 'utf8');
  } else {
    console.error(JSON.stringify(errResult));
  }
  process.exit(1);
}
