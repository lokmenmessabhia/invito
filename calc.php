<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>3D Print Price Calculator</title>
  <style>
    :root {
      --bg: #f7f9fc;
      --panel: #ffffff;
      --text: #172033;
      --muted: #6b7280;
      --line: #e5e9f0;
      --primary: #0f766e;
      --primary-dark: #115e59;
      --accent: #f59e0b;
      --danger: #dc2626;
      --shadow: 0 24px 70px rgba(23, 32, 51, 0.12);
      --radius: 8px;
    }

    * {
      box-sizing: border-box;
    }

    body {
      margin: 0;
      min-height: 100vh;
      font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
      color: var(--text);
      background:
        radial-gradient(circle at top left, rgba(15, 118, 110, 0.08), transparent 34rem),
        linear-gradient(180deg, #ffffff 0%, var(--bg) 68%);
    }

    button,
    input {
      font: inherit;
    }

    .page {
      width: min(1120px, calc(100% - 32px));
      margin: 0 auto;
      padding: 38px 0;
    }

    .topbar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 20px;
      margin-bottom: 28px;
    }

    .brand {
      display: flex;
      align-items: center;
      gap: 12px;
      min-width: 0;
    }

    .brand-mark {
      display: grid;
      width: 44px;
      height: 44px;
      place-items: center;
      border-radius: var(--radius);
      color: #ffffff;
      background: linear-gradient(135deg, var(--primary), #0ea5a0);
      box-shadow: 0 14px 28px rgba(15, 118, 110, 0.24);
      flex: 0 0 auto;
    }

    .brand h1 {
      margin: 0;
      font-size: clamp(1.55rem, 2.8vw, 2.3rem);
      line-height: 1.08;
      letter-spacing: 0;
    }

    .brand p {
      margin: 5px 0 0;
      color: var(--muted);
      font-size: 0.96rem;
    }

    .currency-chip {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 10px 14px;
      border: 1px solid var(--line);
      border-radius: 999px;
      color: var(--primary-dark);
      background: #ffffff;
      font-weight: 700;
      box-shadow: 0 10px 28px rgba(23, 32, 51, 0.08);
      white-space: nowrap;
    }

    .layout {
      display: grid;
      grid-template-columns: minmax(0, 1.25fr) minmax(320px, 0.75fr);
      gap: 22px;
      align-items: start;
    }

    .calculator,
    .summary {
      background: var(--panel);
      border: 1px solid rgba(229, 233, 240, 0.9);
      border-radius: var(--radius);
      box-shadow: var(--shadow);
    }

    .calculator {
      padding: clamp(18px, 3vw, 30px);
    }

    .section-title {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 18px;
    }

    .section-title span {
      display: grid;
      width: 34px;
      height: 34px;
      place-items: center;
      border-radius: var(--radius);
      background: #ecfdf5;
      color: var(--primary-dark);
    }

    .section-title h2 {
      margin: 0;
      font-size: 1.08rem;
      letter-spacing: 0;
    }

    .form-grid {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 16px;
    }

    .field {
      display: grid;
      gap: 8px;
    }

    .field label {
      color: #384152;
      font-size: 0.92rem;
      font-weight: 700;
    }

    .input-wrap {
      position: relative;
    }

    .input-wrap input {
      width: 100%;
      min-height: 52px;
      padding: 13px 72px 13px 14px;
      border: 1px solid #d9dee8;
      border-radius: var(--radius);
      color: var(--text);
      background: #ffffff;
      outline: none;
      transition: border-color 0.18s ease, box-shadow 0.18s ease, transform 0.18s ease;
    }

    .input-wrap input:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 4px rgba(15, 118, 110, 0.13);
    }

    .input-wrap input:hover {
      transform: translateY(-1px);
    }

    .unit {
      position: absolute;
      top: 50%;
      right: 12px;
      transform: translateY(-50%);
      padding: 5px 8px;
      border-radius: 999px;
      color: #566174;
      background: #f1f5f9;
      font-size: 0.78rem;
      font-weight: 800;
      pointer-events: none;
      max-width: 58px;
      overflow: hidden;
      text-align: center;
      text-overflow: ellipsis;
      white-space: nowrap;
    }

    .actions {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      margin-top: 22px;
    }

    .btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      min-height: 46px;
      padding: 0 16px;
      border: 0;
      border-radius: var(--radius);
      cursor: pointer;
      font-weight: 800;
      transition: transform 0.18s ease, box-shadow 0.18s ease, background 0.18s ease;
    }

    .btn svg {
      width: 18px;
      height: 18px;
      flex: 0 0 auto;
    }

    .btn-primary {
      color: #ffffff;
      background: var(--primary);
      box-shadow: 0 14px 26px rgba(15, 118, 110, 0.22);
    }

    .btn-primary:hover {
      background: var(--primary-dark);
      transform: translateY(-1px);
    }

    .btn-ghost {
      color: #334155;
      background: #eef2f7;
    }

    .btn-ghost:hover {
      background: #e2e8f0;
      transform: translateY(-1px);
    }

    .summary {
      position: sticky;
      top: 22px;
      overflow: hidden;
    }

    .total {
      padding: 26px;
      color: #ffffff;
      background:
        linear-gradient(135deg, rgba(255, 255, 255, 0.12), transparent 32%),
        linear-gradient(135deg, #0f766e 0%, #0f4f4b 100%);
    }

    .total-label {
      margin: 0 0 8px;
      font-size: 0.92rem;
      font-weight: 800;
      opacity: 0.86;
    }

    .total-value {
      margin: 0;
      font-size: clamp(2rem, 5vw, 3rem);
      line-height: 1;
      font-weight: 900;
      letter-spacing: 0;
      overflow-wrap: anywhere;
    }

    .total-secondary {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 14px;
      margin-top: 18px;
      padding: 12px 14px;
      border: 1px solid rgba(255, 255, 255, 0.22);
      border-radius: var(--radius);
      background: rgba(255, 255, 255, 0.12);
    }

    .total-secondary span {
      color: rgba(255, 255, 255, 0.82);
      font-size: 0.88rem;
      font-weight: 800;
    }

    .total-secondary strong {
      color: #ffffff;
      text-align: right;
      overflow-wrap: anywhere;
    }

    .rounded-note {
      margin: 12px 0 0;
      opacity: 0.84;
      font-size: 0.9rem;
    }

    .breakdown {
      padding: 20px 22px 24px;
    }

    .breakdown h2 {
      margin: 0 0 14px;
      font-size: 1.04rem;
      letter-spacing: 0;
    }

    .line-item {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      padding: 12px 0;
      border-bottom: 1px solid var(--line);
      color: #485366;
    }

    .line-item:last-child {
      border-bottom: 0;
    }

    .line-item strong {
      color: var(--text);
      font-size: 0.96rem;
      text-align: right;
      overflow-wrap: anywhere;
    }

    .line-item.discount strong {
      color: var(--danger);
    }

    .mini-stats {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 10px;
      margin-top: 16px;
    }

    .stat {
      padding: 12px;
      border: 1px solid var(--line);
      border-radius: var(--radius);
      background: #fbfdff;
    }

    .stat span {
      display: block;
      margin-bottom: 5px;
      color: var(--muted);
      font-size: 0.76rem;
      font-weight: 800;
      text-transform: uppercase;
    }

    .stat strong {
      font-size: 1rem;
      overflow-wrap: anywhere;
    }

    .hint {
      display: flex;
      gap: 10px;
      margin-top: 18px;
      padding: 13px;
      border: 1px solid #fde68a;
      border-radius: var(--radius);
      color: #7c4a03;
      background: #fffbeb;
      font-size: 0.9rem;
      line-height: 1.45;
    }

    .hint svg {
      width: 18px;
      height: 18px;
      flex: 0 0 auto;
      margin-top: 1px;
    }

    @media (max-width: 860px) {
      .layout {
        grid-template-columns: 1fr;
      }

      .summary {
        position: static;
      }
    }

    @media (max-width: 620px) {
      .page {
        width: min(100% - 20px, 1120px);
        padding: 22px 0;
      }

      .topbar {
        align-items: flex-start;
        flex-direction: column;
      }

      .form-grid,
      .mini-stats {
        grid-template-columns: 1fr;
      }

      .calculator,
      .total,
      .breakdown {
        padding: 18px;
      }

      .actions {
        display: grid;
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>
  <main class="page">
    <header class="topbar">
      <div class="brand">
        <div class="brand-mark" aria-hidden="true">
          <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
            <path d="m3.3 7 8.7 5 8.7-5"></path>
            <path d="M12 22V12"></path>
          </svg>
        </div>
        <div>
          <h1>3D Print Price Calculator</h1>
          <p>Estimate material, machine, labor, failure rate, profit, discount, and delivery.</p>
        </div>
      </div>
      <div class="currency-chip">
        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <circle cx="12" cy="12" r="10"></circle>
          <path d="M16 8h-6a2 2 0 0 0 0 4h4a2 2 0 0 1 0 4H8"></path>
          <path d="M12 18V6"></path>
        </svg>
        DZD Pricing
      </div>
    </header>

    <section class="layout" aria-label="3D print price calculator">
      <form class="calculator" id="calculator">
        <div class="section-title">
          <span aria-hidden="true">
            <svg viewBox="0 0 24 24" width="19" height="19" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 2v20"></path>
              <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7H14a3.5 3.5 0 0 1 0 7H6"></path>
            </svg>
          </span>
          <h2>Project Inputs</h2>
        </div>

        <div class="form-grid">
          <div class="field">
            <label for="filKgPrice">Filament kg price</label>
            <div class="input-wrap">
              <input id="filKgPrice" name="filKgPrice" type="number" inputmode="decimal" min="0" step="0.01" value="2500">
              <span class="unit">DZD</span>
            </div>
          </div>

          <div class="field">
            <label for="usedFil">Used filament</label>
            <div class="input-wrap">
              <input id="usedFil" name="usedFil" type="number" inputmode="decimal" min="0" step="0.01" value="120">
              <span class="unit">g</span>
            </div>
          </div>

          <div class="field">
            <label for="printTimeM">Print time</label>
            <div class="input-wrap">
              <input id="printTimeM" name="printTimeM" type="number" inputmode="numeric" min="0" step="1" value="300">
              <span class="unit">min</span>
            </div>
          </div>

          <div class="field">
            <label for="machineHourlyRate">Machine hourly rate</label>
            <div class="input-wrap">
              <input id="machineHourlyRate" name="machineHourlyRate" type="number" inputmode="decimal" min="0" step="0.01" value="150">
              <span class="unit">DZD/h</span>
            </div>
          </div>

          <div class="field">
            <label for="laborTimeM">Labor time</label>
            <div class="input-wrap">
              <input id="laborTimeM" name="laborTimeM" type="number" inputmode="numeric" min="0" step="1" value="30">
              <span class="unit">min</span>
            </div>
          </div>

          <div class="field">
            <label for="laborHourlyRate">Labor hourly rate</label>
            <div class="input-wrap">
              <input id="laborHourlyRate" name="laborHourlyRate" type="number" inputmode="decimal" min="0" step="0.01" value="300">
              <span class="unit">DZD/h</span>
            </div>
          </div>

          <div class="field">
            <label for="profit">Profit</label>
            <div class="input-wrap">
              <input id="profit" name="profit" type="number" inputmode="numeric" min="0" step="1" value="30">
              <span class="unit">%</span>
            </div>
          </div>

          <div class="field">
            <label for="failureRate">Failure rate</label>
            <div class="input-wrap">
              <input id="failureRate" name="failureRate" type="number" inputmode="decimal" min="0" step="0.1" value="10">
              <span class="unit">%</span>
            </div>
          </div>

          <div class="field">
            <label for="discount">Discount</label>
            <div class="input-wrap">
              <input id="discount" name="discount" type="number" inputmode="decimal" min="0" step="0.01" value="0">
              <span class="unit">DZD</span>
            </div>
          </div>

          <div class="field">
            <label for="electricityPrice">Electricity Price (per 1 kWh)</label>
            <div class="input-wrap">
              <input id="electricityPrice" name="electricityPrice" type="number" inputmode="decimal" min="0" step="0.01" value="6">
              <span class="unit">DZD/kWh</span>
            </div>
          </div>

          <div class="field">
            <label for="printerPower">Printer Power</label>
            <div class="input-wrap">
              <input id="printerPower" name="printerPower" type="number" inputmode="numeric" min="0" step="1" value="250">
              <span class="unit">W</span>
            </div>
          </div>

          <div class="field">
            <label for="deliveryPrice">Delivery price</label>
            <div class="input-wrap">
              <input id="deliveryPrice" name="deliveryPrice" type="number" inputmode="decimal" min="0" step="0.01" value="0">
              <span class="unit">DZD</span>
            </div>
          </div>
        </div>

        <div class="actions">
          <button class="btn btn-primary" type="button" id="copyResult">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <rect width="14" height="14" x="8" y="8" rx="2" ry="2"></rect>
              <path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"></path>
            </svg>
            Copy Price
          </button>
          <button class="btn btn-ghost" type="reset" id="resetForm">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"></path>
              <path d="M3 3v5h5"></path>
            </svg>
            Reset
          </button>
        </div>
      </form>

      <aside class="summary" aria-live="polite">
        <div class="total">
          <p class="total-label">Total With Delivery</p>
          <p class="total-value" id="finalPrice">0 DZD</p>
          <div class="total-secondary">
            <span>Total without delivery</span>
            <strong id="totalWithoutDelivery">0 DZD</strong>
          </div>
          <p class="rounded-note">Both totals are rounded to the nearest 100 DZD.</p>
        </div>

        <div class="breakdown">
          <h2>Cost Breakdown</h2>
          <div class="line-item">
            <span>Material cost</span>
            <strong id="materialCost">0 DZD</strong>
          </div>
          <div class="line-item">
            <span>Machine cost</span>
            <strong id="machineCost">0 DZD</strong>
          </div>
          <div class="line-item">
            <span>Labor cost</span>
            <strong id="laborCost">0 DZD</strong>
          </div>
          <div class="line-item">
            <span>Electricity cost</span>
            <strong id="electricityCost">0 DZD</strong>
          </div>
          <div class="line-item">
            <span id="failureLabel">Failure buffer 10%</span>
            <strong id="failureCost">0 DZD</strong>
          </div>
          <div class="line-item">
            <span>Profit amount</span>
            <strong id="profitPrice">0 DZD</strong>
          </div>
          <div class="line-item discount">
            <span>Discount</span>
            <strong id="discountValue">0 DZD</strong>
          </div>
          <div class="line-item">
            <span>Delivery price</span>
            <strong id="deliveryCost">0 DZD</strong>
          </div>

          <div class="mini-stats">
            <div class="stat">
              <span>Per gram</span>
              <strong id="costG">0 DZD</strong>
            </div>
            <div class="stat">
              <span>Before rounding</span>
              <strong id="rawPrice">0 DZD</strong>
            </div>
            <div class="stat">
              <span>Energy used</span>
              <strong id="energyUsed">0 kWh</strong>
            </div>
          </div>

          <div class="hint">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <circle cx="12" cy="12" r="10"></circle>
              <path d="M12 16v-4"></path>
              <path d="M12 8h.01"></path>
            </svg>
            <span>Empty fields count as zero, so you can quickly test prices while adjusting one value at a time.</span>
          </div>
        </div>
      </aside>
    </section>
  </main>

  <script>
    const form = document.getElementById('calculator');
    const fields = Array.from(form.querySelectorAll('input'));
    const formatter = new Intl.NumberFormat('en-DZ', {
      maximumFractionDigits: 2
    });

    const values = {
      filKgPrice: document.getElementById('filKgPrice'),
      usedFil: document.getElementById('usedFil'),
      printTimeM: document.getElementById('printTimeM'),
      machineHourlyRate: document.getElementById('machineHourlyRate'),
      laborTimeM: document.getElementById('laborTimeM'),
      laborHourlyRate: document.getElementById('laborHourlyRate'),
      profit: document.getElementById('profit'),
      failureRate: document.getElementById('failureRate'),
      discount: document.getElementById('discount'),
      electricityPrice: document.getElementById('electricityPrice'),
      printerPower: document.getElementById('printerPower'),
      deliveryPrice: document.getElementById('deliveryPrice')
    };

    const output = {
      finalPrice: document.getElementById('finalPrice'),
      totalWithoutDelivery: document.getElementById('totalWithoutDelivery'),
      materialCost: document.getElementById('materialCost'),
      machineCost: document.getElementById('machineCost'),
      laborCost: document.getElementById('laborCost'),
      failureLabel: document.getElementById('failureLabel'),
      failureCost: document.getElementById('failureCost'),
      profitPrice: document.getElementById('profitPrice'),
      discountValue: document.getElementById('discountValue'),
      deliveryCost: document.getElementById('deliveryCost'),
      electricityCost: document.getElementById('electricityCost'),
      energyUsed: document.getElementById('energyUsed'),
      costG: document.getElementById('costG'),
      rawPrice: document.getElementById('rawPrice')
    };

    function readNumber(input) {
      return Number.parseFloat(input.value) || 0;
    }

    function money(amount) {
      return `${formatter.format(amount)} DZD`;
    }

    function roundToNearestHundred(amount) {
      return Math.round(amount / 100) * 100;
    }

    function calculate() {
      const filKgPrice = readNumber(values.filKgPrice);
      const usedFil = readNumber(values.usedFil);
      const printTimeM = readNumber(values.printTimeM);
      const machineHourlyRate = readNumber(values.machineHourlyRate);
      const laborTimeM = readNumber(values.laborTimeM);
      const laborHourlyRate = readNumber(values.laborHourlyRate);
      const profit = readNumber(values.profit);
      const failureRate = readNumber(values.failureRate);
      const discount = readNumber(values.discount);
      const electricityPrice = readNumber(values.electricityPrice);
      const printerPower = readNumber(values.printerPower);
      const deliveryPrice = readNumber(values.deliveryPrice);

      const costG = filKgPrice / 1000;
      const materialCost = usedFil * costG;
      const machineCost = (printTimeM / 60) * machineHourlyRate;
      const laborCost = (laborTimeM / 60) * laborHourlyRate;
      const printTimeH = printTimeM / 60;
      const energyUsedKwh = (printerPower * printTimeH) / 1000;
      const electricityCost = energyUsedKwh * electricityPrice;
      const prodCost = materialCost + machineCost + laborCost + electricityCost;
      const failureCost = prodCost * (failureRate / 100);
      const subtotal = prodCost + failureCost;
      const profitPrice = subtotal * (profit / 100);
      const priceWithoutDelivery = subtotal + profitPrice - discount;
      const priceWithDelivery = priceWithoutDelivery + deliveryPrice;
      const roundedWithoutDelivery = roundToNearestHundred(priceWithoutDelivery);
      const roundedWithDelivery = roundToNearestHundred(priceWithDelivery);

      output.finalPrice.textContent = money(roundedWithDelivery);
      output.totalWithoutDelivery.textContent = money(roundedWithoutDelivery);
      output.materialCost.textContent = money(materialCost);
      output.machineCost.textContent = money(machineCost);
      output.laborCost.textContent = money(laborCost);
      output.failureLabel.textContent = `Failure buffer ${formatter.format(failureRate)}%`;
      output.failureCost.textContent = money(failureCost);
      output.profitPrice.textContent = money(profitPrice);
      output.discountValue.textContent = `-${money(discount)}`;
      output.deliveryCost.textContent = money(deliveryPrice);
      output.electricityCost.textContent = money(electricityCost);
      output.energyUsed.textContent = `${formatter.format(energyUsedKwh)} kWh`;
      output.costG.textContent = money(costG);
      output.rawPrice.textContent = money(priceWithDelivery);

      return roundedWithDelivery;
    }

    fields.forEach((field) => {
      field.addEventListener('input', calculate);
    });

    form.addEventListener('reset', () => {
      window.setTimeout(calculate, 0);
    });

    document.getElementById('copyResult').addEventListener('click', async () => {
      const roundedFinal = calculate();
      const button = document.getElementById('copyResult');
      const original = button.innerHTML;

      try {
        await navigator.clipboard.writeText(`${roundedFinal} DZD`);
        button.textContent = 'Copied';
        window.setTimeout(() => {
          button.innerHTML = original;
        }, 1100);
      } catch (error) {
        button.textContent = 'Copy failed';
        window.setTimeout(() => {
          button.innerHTML = original;
        }, 1100);
      }
    });

    calculate();
  </script>
</body>
</html>
