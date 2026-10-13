/**
 * Luxury Real Estate Mortgage & Investment / ROI Calculator Engine
 * Fully reactive financial model with responsive sliders, dual tab navigation,
 * real-time breakdown visualizations, and multi-currency integration.
 */

import { formatPrice, convertAmount, getActiveCurrency, CURRENCIES } from './currency';

export function initMortgageCalculator() {
    const container = document.getElementById('luxury-financial-calculator');
    if (!container) return;

    const basePriceETB = parseFloat(container.getAttribute('data-property-price')) || 0;
    const isRent = container.getAttribute('data-property-type') === 'rent';

    // Tabs
    const tabMortgageBtn = document.getElementById('calc-tab-mortgage');
    const tabRoiBtn = document.getElementById('calc-tab-roi');
    const panelMortgage = document.getElementById('calc-panel-mortgage');
    const panelRoi = document.getElementById('calc-panel-roi');

    if (tabMortgageBtn && tabRoiBtn && panelMortgage && panelRoi) {
        tabMortgageBtn.addEventListener('click', () => {
            tabMortgageBtn.className = 'flex-1 rounded-xl border border-[#1d3c34] bg-[#1d3c34] px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-white shadow-sm transition';
            tabRoiBtn.className = 'flex-1 rounded-xl border border-[#d9cab3] bg-[#fffaf2] px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-[#1d3c34] hover:bg-[#f4ebdc] transition';
            panelMortgage.classList.remove('hidden');
            panelRoi.classList.add('hidden');
        });

        tabRoiBtn.addEventListener('click', () => {
            tabRoiBtn.className = 'flex-1 rounded-xl border border-[#1d3c34] bg-[#1d3c34] px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-white shadow-sm transition';
            tabMortgageBtn.className = 'flex-1 rounded-xl border border-[#d9cab3] bg-[#fffaf2] px-4 py-2.5 text-xs font-bold uppercase tracking-wider text-[#1d3c34] hover:bg-[#f4ebdc] transition';
            panelRoi.classList.remove('hidden');
            panelMortgage.classList.add('hidden');
        });
    }

    // Mortgage Elements
    const downPercentSlider = document.getElementById('calc-down-slider');
    const downPercentValue = document.getElementById('calc-down-val');
    const downAmountDisplay = document.getElementById('calc-down-amount');
    const loanTermSelect = document.getElementById('calc-term-select');
    const interestSlider = document.getElementById('calc-interest-slider');
    const interestValue = document.getElementById('calc-interest-val');
    const taxToggle = document.getElementById('calc-tax-toggle');

    // Mortgage Output Elements
    const monthlyPaymentEl = document.getElementById('calc-monthly-total');
    const loanAmountEl = document.getElementById('calc-loan-amount');
    const totalInterestEl = document.getElementById('calc-total-interest');
    const totalCostEl = document.getElementById('calc-total-cost');
    const barPrincipalEl = document.getElementById('calc-bar-principal');
    const barTaxEl = document.getElementById('calc-bar-tax');
    const piAmountEl = document.getElementById('calc-pi-amount');
    const taxAmountEl = document.getElementById('calc-tax-amount');

    // ROI Elements
    const roiRentSlider = document.getElementById('calc-roi-rent-slider');
    const roiRentDisplay = document.getElementById('calc-roi-rent-display');
    const roiExpenseSlider = document.getElementById('calc-roi-expense-slider');
    const roiExpenseVal = document.getElementById('calc-roi-expense-val');
    const roiGrossYieldEl = document.getElementById('calc-roi-gross-yield');
    const roiNetAnnualEl = document.getElementById('calc-roi-net-annual');
    const roi5YearValEl = document.getElementById('calc-roi-5yr-val');
    const roiTotalReturnEl = document.getElementById('calc-roi-total-return');

    // Preset interest rate quick buttons
    document.querySelectorAll('[data-calc-rate]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const rate = parseFloat(btn.getAttribute('data-calc-rate'));
            if (interestSlider && !isNaN(rate)) {
                interestSlider.value = rate;
                updateCalculations();
            }
        });
    });

    function calculateMortgage() {
        const currentCurrency = getActiveCurrency();
        const currentPriceConverted = convertAmount(basePriceETB, currentCurrency);

        const downPercent = parseFloat(downPercentSlider?.value || 20);
        const termYears = parseInt(loanTermSelect?.value || 20, 10);
        const annualRate = parseFloat(interestSlider?.value || 14.5);
        const includeTax = taxToggle?.checked ?? true;

        const downAmount = currentPriceConverted * (downPercent / 100);
        const principal = Math.max(0, currentPriceConverted - downAmount);

        // Monthly Interest Rate & Total Months
        const monthlyRate = (annualRate / 100) / 12;
        const totalMonths = termYears * 12;

        let monthlyPI = 0;
        if (principal > 0 && totalMonths > 0) {
            if (monthlyRate === 0) {
                monthlyPI = principal / totalMonths;
            } else {
                monthlyPI = (principal * monthlyRate * Math.pow(1 + monthlyRate, totalMonths)) /
                    (Math.pow(1 + monthlyRate, totalMonths) - 1);
            }
        }

        // Estimated annual property tax & insurance ~0.8% of property value
        const monthlyTax = includeTax ? (currentPriceConverted * 0.008) / 12 : 0;
        const totalMonthly = monthlyPI + monthlyTax;

        const totalPaid = monthlyPI * totalMonths;
        const totalInterest = Math.max(0, totalPaid - principal);

        // UI Updates
        if (downPercentValue) downPercentValue.textContent = `${downPercent}%`;
        if (interestValue) interestValue.textContent = `${annualRate.toFixed(1)}%`;

        const symbol = CURRENCIES[currentCurrency].symbol;
        const formatWithSymbol = (num) => {
            const config = CURRENCIES[currentCurrency];
            const str = Math.round(num).toLocaleString();
            return config.symbolPlacement === 'before' ? `${symbol}${str}` : `${symbol} ${str}`;
        };

        if (downAmountDisplay) downAmountDisplay.textContent = formatWithSymbol(downAmount);
        if (monthlyPaymentEl) monthlyPaymentEl.textContent = formatWithSymbol(totalMonthly);
        if (loanAmountEl) loanAmountEl.textContent = formatWithSymbol(principal);
        if (totalInterestEl) totalInterestEl.textContent = formatWithSymbol(totalInterest);
        if (totalCostEl) totalCostEl.textContent = formatWithSymbol(totalPaid + (monthlyTax * totalMonths));

        if (piAmountEl) piAmountEl.textContent = formatWithSymbol(monthlyPI);
        if (taxAmountEl) taxAmountEl.textContent = formatWithSymbol(monthlyTax);

        // Visual Proportion Bar
        if (barPrincipalEl && barTaxEl) {
            const piPercent = totalMonthly > 0 ? (monthlyPI / totalMonthly) * 100 : 100;
            const taxPercent = totalMonthly > 0 ? (monthlyTax / totalMonthly) * 100 : 0;
            barPrincipalEl.style.width = `${piPercent}%`;
            barTaxEl.style.width = `${taxPercent}%`;
        }
    }

    function calculateROI() {
        const currentCurrency = getActiveCurrency();
        const currentPriceConverted = convertAmount(basePriceETB, currentCurrency);

        // Typical monthly rent is ~0.7% of property purchase price by default
        const defaultRentRatio = isRent ? 1 : 0.007;
        const rentMultiplier = parseFloat(roiRentSlider?.value || 100) / 100;
        const monthlyRent = (isRent ? currentPriceConverted : currentPriceConverted * defaultRentRatio) * rentMultiplier;

        const expensePercent = parseFloat(roiExpenseSlider?.value || 10);
        if (roiExpenseVal) roiExpenseVal.textContent = `${expensePercent}%`;

        const annualGrossRent = monthlyRent * 12;
        const grossYield = currentPriceConverted > 0 ? (annualGrossRent / currentPriceConverted) * 100 : 0;

        const annualNetRent = annualGrossRent * (1 - (expensePercent / 100));

        // 5-Year Capital Appreciation: 10% annualized appreciation compounded
        const futureValue5Yr = currentPriceConverted * Math.pow(1.10, 5);
        const total5YrCashFlow = annualNetRent * 5;
        const totalProfit5Yr = (futureValue5Yr - currentPriceConverted) + total5YrCashFlow;
        const totalROI5Yr = currentPriceConverted > 0 ? (totalProfit5Yr / currentPriceConverted) * 100 : 0;

        const symbol = CURRENCIES[currentCurrency].symbol;
        const formatWithSymbol = (num) => {
            const config = CURRENCIES[currentCurrency];
            const str = Math.round(num).toLocaleString();
            return config.symbolPlacement === 'before' ? `${symbol}${str}` : `${symbol} ${str}`;
        };

        if (roiRentDisplay) roiRentDisplay.textContent = `${formatWithSymbol(monthlyRent)} / mo`;
        if (roiGrossYieldEl) roiGrossYieldEl.textContent = `${grossYield.toFixed(2)}%`;
        if (roiNetAnnualEl) roiNetAnnualEl.textContent = formatWithSymbol(annualNetRent);
        if (roi5YearValEl) roi5YearValEl.textContent = formatWithSymbol(futureValue5Yr);
        if (roiTotalReturnEl) roiTotalReturnEl.textContent = `+${totalROI5Yr.toFixed(1)}%`;
    }

    function updateCalculations() {
        calculateMortgage();
        calculateROI();
    }

    // Event listeners
    [downPercentSlider, loanTermSelect, interestSlider, taxToggle].forEach((input) => {
        if (input) {
            input.addEventListener('input', calculateMortgage);
            input.addEventListener('change', calculateMortgage);
        }
    });

    [roiRentSlider, roiExpenseSlider].forEach((input) => {
        if (input) {
            input.addEventListener('input', calculateROI);
            input.addEventListener('change', calculateROI);
        }
    });

    // Listen to global currency changes
    window.addEventListener('currency-changed', () => {
        updateCalculations();
    });

    // Initial run
    updateCalculations();
}
