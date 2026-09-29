export default function agreementProgress(initial = {}) {
    const keys = ['investment', 'tr1', 'tr2', 'tr3', 'revised'];
    return {
        saving: false,
        amounts: Object.fromEntries(keys.map(key => [key, {
            own: initial[key]?.own ?? '', loan: initial[key]?.loan ?? '', grant: initial[key]?.grant ?? '',
        }])),
        cents(key) {
            return ['own', 'loan', 'grant'].reduce((sum, source) => {
                const amount = Number(this.amounts[key][source]);
                return sum + (Number.isFinite(amount) && amount >= 0 ? Math.round(amount * 100) : 0);
            }, 0);
        },
        total(key) {
            return (this.cents(key) / 100).toLocaleString('en-LK', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },
        percentage(key, cumulative = false) {
            const budget = this.cents('investment');
            if (!budget) return null;
            const tranches = ['tr1', 'tr2', 'tr3'];
            const amount = cumulative && tranches.includes(key)
                ? tranches.slice(0, tranches.indexOf(key) + 1).reduce((sum, tranche) => sum + this.cents(tranche), 0)
                : this.cents(key);
            return amount / budget * 100;
        },
        percentageLabel(key, cumulative = false) {
            const percentage = this.percentage(key, cumulative);
            return percentage === null ? 'Set Total Investment first' : percentage.toFixed(2) + '%';
        },
        barWidth(key, cumulative = false) {
            return Math.min(100, Math.max(0, this.percentage(key, cumulative) ?? 0)) + '%';
        },
    };
}
